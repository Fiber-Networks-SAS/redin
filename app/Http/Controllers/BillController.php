<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 600); //10 minutes
// set_time_limit(600); //60 seconds = 1 minute (for migrate proccess)

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;

use View;
use Validator;
use DateTime;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic as Image;
use Yajra\Datatables\Datatables;
use iio\libmergepdf\Merger;

use App\User;
use App\Role;
use App\BonificacionServicio;
use App\Pais;
use App\Provincia;
use App\Localidad;
use App\Servicio;
use App\ServicioUsuario;
use App\Talonario;
use App\Factura;
use App\FacturaDetalle;
use App\BonificacionPuntual;
use App\Interes;
use App\Cuota;
use App\PagosConfig;
use App\ImportCe;
use App\PaymentPreference;
use App\PagoInformado;
use App\NotaCredito;
use App\SaldoFavor;
use App\Services\AfipService;
use App\Services\PaymentQRService;

// use Entrust;
use PDF;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

/*
    --- FOR SEND EMAIL ---
  
    Edit \vendor\swiftmailer\lib\classes\Swift\Transport\StreamBuffer.php
    line 259 ish. comment out the $options = array(); and add the below.

    //$options = array();
    $options['ssl'] = array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true);

*/

/*

    --- CALCULO DEL PROPORCIONAL DEL SERVICIO ---

    $dt = Carbon::now();
    $dt = Carbon::parse('2017-11-11 08:19:10');

    $dias_total  = $dt->daysInMonth;    // total de dias del mes (SE PUEDE TOMAR COMO UN GENERICO DE 30 DIAS)
    $dias_actual = $dt->day;            // dia del mes

    $proporcional = ($dias_actual * $abono_mensual) / $dias_total;


    return $proporcional;

*/

/* 

    --- ZEROFILL ---
    return $this->zerofill(100, 4);


*/


class BillController extends Controller
{
    protected $paymentQRService;
    protected $afipService;

    public function __construct(PaymentQRService $paymentQRService, AfipService $afipService)
    {
        // $this->middleware('auth');

        // Check user permission 
        // $this->middleware('permission:crud-users');

        $this->paymentQRService = $paymentQRService;
        $this->afipService = $afipService;

        $this->tipo = ['Internet', 'Telefon�a', 'Televisi�n'];
        $this->drop = ['En Pilar', 'En Domicilio', 'Sin Drop'];
        $this->forma_pago = [1 => 'Efectivo', 2 => 'Pago Mis Cuentas', 3 => 'Cobro Express', 4 => 'Mercado Pago', 5 => 'Saldo a Favor', 6 => 'CBU/Transferencia']; // 4 => 'Tarjeta de Cr�dito', 5 => 'Dep�sito'
        $this->meses = [
            '01' => 'ENERO',
            '02' => 'FEBRERO',
            '03' => 'MARZO',
            '04' => 'ABRIL',
            '05' => 'MAYO',
            '06' => 'JUNIO',
            '07' => 'JULIO',
            '08' => 'AGOSTO',
            '09' => 'SEPTIEMBRE',
            '10' => 'OCTUBRE',
            '11' => 'NOVIEMBRE',
            '12' => 'DICIEMBRE',
        ];
    }

    public function index()
    {

        return View::make('period.list_periodo');
    }

    // lista de los periodos - AJAX
    public function getList(Request $request)
    {
        $periodos = [];
        // Filtrar solo facturas activas (no anuladas)
        $facturas = Factura::select(['id', 'periodo', 'fecha_emision', 'fecha_pago', 'nro_cliente', 'importe_total', 'mail_date'])
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('periodo');
        // return $facturas;

        foreach ($facturas as $periodo => $factura) {

            $pagos = 0;
            $mails = 0;
            foreach ($factura as $f) {

                // obtengo las facturas pagas
                if ($f->fecha_pago != '') {
                    $pagos++;
                }

                // obtengo las facturas que fueron notificadas por mail
                if (!empty($f->mail_date)) {
                    $mails++;
                }
            }

            // path del pdf
            $filename = str_replace('/', '-', $periodo);
            $filename = $request->root() . '/' . config('constants.folder_periodos') . 'periodo-' . $filename . '.pdf';

            $periodos[$periodo]['id'] = $factura[0]->id;
            $periodos[$periodo]['periodo'] = $periodo;
            $periodos[$periodo]['fecha_emision'] = Carbon::parse($factura[0]->fecha_emision)->format('d/m/Y');
            $periodos[$periodo]['total'] = count($factura);
            $periodos[$periodo]['pagas'] = $pagos;
            $periodos[$periodo]['mails'] = $mails;
            $periodos[$periodo]['pdf'] = $filename;
        }


        // convert Array to Collection
        $periodos = collect($periodos);

        return Datatables::of($periodos)->make(true);
    }

    // vista de facturas de un periodo dado
    public function view(Request $request, $mes, $ano)
    {

        if ($mes != '' && $ano != '') {
            // reconstruyo el periodo

            $periodo = $mes . '/' . $ano;

            return View::make('period.list_periodo_facturas')->with(['periodo' => $periodo]);
        }
    }

    // lista de facturas de un periodo dado - AJAX
    public function getBillPeriodList(Request $request, $mes, $ano)
    {

        $fecha_actual = Carbon::today();
        $periodo = $mes . '/' . $ano;
        // Filtrar solo facturas activas (no anuladas)
        $facturas = Factura::where('periodo', $periodo)->whereNull('deleted_at')->get();

        foreach ($facturas as $factura) {

            $factura->talonario;
            $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);

            $factura->nro_factura = $this->zerofill($factura->nro_factura);
            $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);

            $factura->cliente;
            $factura->cliente->nombre_apellido = $factura->cliente->firstname . ' ' . $factura->cliente->lastname;


            // verifico si puede mostrarse el boton de bonificacion
            $factura->fecha_actual = $fecha_actual;
            // $factura->btn_bonificacion = $fecha_actual->lt(Carbon::parse($factura->primer_vto_fecha));
            $factura->btn_bonificacion = true; // cambio pedido por Orne el dia 03-05-2018
            $factura->btn_actualizar = $fecha_actual->gt(Carbon::parse($factura->segundo_vto_fecha));


            $factura->fecha_emision = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
            $factura->primer_vto_fecha = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
            $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');

            // $factura->importe_subtotal = number_format($factura->importe_subtotal, 2);
            // $factura->importe_bonificacion = number_format($factura->importe_bonificacion, 2);
            // $factura->importe_total = number_format($factura->importe_total, 2);
            $factura->fecha_pago = $factura->fecha_pago ? Carbon::parse($factura->fecha_pago)->format('d/m/Y') : null;

            $detalles =  $factura->detalle;

            foreach ($detalles as $detalle) {
                $detalle->servicio;
            }


            // genero los PDF's de las facturas individuales
            $filename = $factura->talonario->nro_punto_vta . '-' . $factura->nro_factura;
            $factura->pdf = $request->root() . '/' . config('constants.folder_facturas') . 'factura-' . $filename . '.pdf';
        }

        // return $facturas;
        return Datatables::of($facturas)->make(true);
    }

    // detalle de factura
    public function getBillDetail(Request $request, $id)
    {

        $factura = Factura::find($id);

        if ($factura) {
            $factura->talonario;
            $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);

            $factura->nro_factura = $this->zerofill($factura->nro_factura);
            $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);

            $factura->cliente;

            $factura->fecha_emision = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
            $factura->primer_vto_fecha = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
            $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');
            $factura->tercer_vto_fecha = Carbon::parse($factura->tercer_vto_fecha)->format('d/m/Y');

            // $factura->importe_subtotal = number_format($factura->importe_subtotal, 2);
            // $factura->importe_bonificacion = number_format($factura->importe_bonificacion, 2);
            // $factura->importe_total = number_format($factura->importe_total, 2);
            // $factura->segundo_vto_importe = number_format($factura->segundo_vto_importe, 2);
            // $factura->tercer_vto_importe = number_format($factura->tercer_vto_importe, 2);

            $factura->fecha_pago = $factura->fecha_pago ? Carbon::parse($factura->fecha_pago)->format('d/m/Y') : null;
            // $factura->importe_pago = number_format($factura->importe_pago, 2);
            $factura->forma_pago = $factura->fecha_pago ? $this->forma_pago[$factura->forma_pago] : '';
            $factura->mail_date = $factura->mail_date ? Carbon::parse($factura->mail_date)->format('d/m/Y') : null;

            $detalles =  $factura->detalle;

            foreach ($detalles as $detalle) {
                $detalle->servicio;
            }


            // obtengo el PDF's de la factura individual
            $filename = $factura->talonario->nro_punto_vta . '-' . $factura->nro_factura;
            $factura->pdf = $request->root() . '/' . config('constants.folder_facturas') . 'factura-' . $filename . '.pdf';

            // Cargar notas de crédito y débito
            $notasCredito = $factura->notaCredito;
            $notasDebito = $factura->notaDebito;

            // return $factura;

            return View::make('period.view_factura')->with([
                'factura' => $factura,
                'notasCredito' => $notasCredito,
                'notasDebito' => $notasDebito
            ]);
        } else {

            return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error, la factura no existe.', 'icon' => 'fa-frown-o']);
        }
    }

    // bonificacion de factura POST
    public function getBillEditPost(Request $request, $id)
    {


        //-- VALIDATOR START --//
        // $rules = array(
        //     'importe_bonificacion'      => ['required', 'regex:/^-?(?:0|[1-9]\d{0,2}(?:,?\d{3})*)(?:\.\d+)?$/'],
        // );

        // $validator = Validator::make($request->all(), $rules);

        // if($validator->fails())
        // {       
        //   return back()->withInput()->withErrors($validator);
        // }
        //-- VALIDATOR END --//
        // return $request->all();

        $factura = Factura::find($id);

        // obtengo el nro de punto de venta y el nro de factura
        $factura->talonario;
        $factura_nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);
        $factura_nro_factura = $this->zerofill($factura->nro_factura);


        if ($factura) {

            // params
            $request->importe_subtotal = str_replace(",", "", $request->importe_subtotal);
            $request->importe_total = str_replace("$", "", $request->importe_total);
            $request->importe_total = str_replace(",", "", $request->importe_total);


            // obtengo la conf. de intereses 
            $interes = Interes::find(1);

            // obtengo la fecha actual
            $fecha_actual = Carbon::now();

            $factura->fecha_emision         = $fecha_actual; // actualizo la fecha de emision.
            $factura->importe_bonificacion  = $this->floatvalue($request->importe_bonificacion);
            $factura->importe_subtotal      = $this->floatvalue($request->importe_subtotal);
            $factura->importe_total         = $this->floatvalue($request->importe_total);
            $factura->primer_vto_codigo     = $this->getCodigoPago($factura->importe_total, $factura->primer_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);
            $factura->segundo_vto_importe   = $this->getImporteConTasaInteres($factura->importe_total, $interes->segundo_vto_tasa);
            $factura->segundo_vto_codigo    = $this->getCodigoPago($factura->segundo_vto_importe, $factura->segundo_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);


            // ACTUALIZACION TERCER VENCIMIENTO ------------------
            // PEDIDO DE ORNE: NO GENERAR EL 3ER CODIGO DE BARRAS (en caso de volver atras descomentar las siguientes lineas)

            // $fecha_actual                = $factura->tercer_vto_fecha != '' ? $factura->tercer_vto_fecha : $fecha_actual; // verifico si tiene una fecha establecida
            // $response                    = $this->getImporteConTasaInteresTercerVto($factura->segundo_vto_importe, $factura->segundo_vto_fecha, $interes->tercer_vto_tasa, Carbon::parse($fecha_actual)->format('d/m/Y'));
            // $factura->tercer_vto_fecha   = $fecha_actual;
            // $factura->tercer_vto_tasa    = $response['tasa'];
            // $factura->tercer_vto_importe = $response['importe'];
            // $factura->tercer_vto_codigo  = $this->getCodigoPago($factura->tercer_vto_importe, $factura->tercer_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);

            $factura->tercer_vto_fecha   = NULL;
            $factura->tercer_vto_tasa    = 0;
            $factura->tercer_vto_importe = 0;
            $factura->tercer_vto_codigo  = '';

            if ($factura->save()) {

                // actualizo el detalle de la factura
                if ($request->field_type != 'importe_bonificacion') {

                    $factura_detalle = FacturaDetalle::find($request->id);

                    switch ($request->field_type) {

                        case 'importe_fila':
                            if ($request->proporcional == 1) {
                                $factura_detalle->abono_proporcional = $request->value;
                            } else {
                                $factura_detalle->abono_mensual = $request->value;
                            }
                            break;

                        case 'instalacion_fila':
                            $factura_detalle->costo_instalacion = $request->value;
                            break;
                    }

                    $factura_detalle->save();
                }

                // actualizo el PDF del periodo e individual
                $this->setFacturasPeriodoPDF($factura->periodo, $factura->id);
                $filename = $this->getFacturaPDFPath($request, $factura);

                return 1;
                // return $response;
                // return redirect($request->previousUrl)->with(['status' => 'success', 'message' => 'Se ha bonificado la factura '.$factura->talonario->letra.' '.$factura->talonario->nro_punto_vta.' - '.$factura->nro_factura.'.' , 'icon' => 'fa-smile-o', 'filename' => $filename, 'factura_id' => $factura->id]);

            } else {

                return 0;

                // return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);

            }
        }
    }

    // bonificacion de factura
    public function getBillImprove(Request $request, $id)
    {

        $factura = Factura::find($id);

        $factura->talonario;
        $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);

        $factura->nro_factura = $this->zerofill($factura->nro_factura);
        // $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);

        $factura->cliente;

        $factura->fecha_emision = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
        $factura->primer_vto_fecha = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
        $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');

        // $factura->importe_subtotal = $factura->importe_subtotal;
        // $factura->importe_bonificacion = $factura->importe_bonificacion;
        // $factura->importe_total = $factura->importe_total;
        // $factura->segundo_vto_importe = $factura->segundo_vto_importe;

        $factura->fecha_pago = $factura->fecha_pago ? Carbon::parse($factura->fecha_pago)->format('d/m/Y') : null;

        $detalles =  $factura->detalle;

        foreach ($detalles as $detalle) {
            $detalle->servicio;
        }

        $notasCredito = $factura->notaCredito;

        // // genero los PDF's de las facturas individuales
        // $filename = $factura->talonario->nro_punto_vta.'-'.$factura->nro_factura;
        // $factura->pdf = $request->root().'/'.config('constants.folder_facturas') . 'factura-'.$filename.'.pdf';

        // return $factura;

        return View::make('period.view_factura_bonificar')->with(['factura' => $factura, 'detalles' => $detalles, 'notasCredito' => $notasCredito]);
    }

    // bonificacion de factura POST
    public function getBillImprovePost(Request $request, $id)
    {


        //-- VALIDATOR START --//
        $rules = array(
            'importe_bonificacion'      => ['required', 'numeric'],
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        $factura = Factura::find($id);

        // obtengo el nro de punto de venta y el nro de factura
        $factura->talonario;
        $factura_nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);
        $factura_nro_factura = $this->zerofill($factura->nro_factura);

        if ($factura) {

            // obtengo la conf. de intereses 
            $interes = Interes::find(1);

            // obtengo la fecha actual
            $fecha_actual = Carbon::now();

            $factura->fecha_emision         = $fecha_actual; // actualizo la fecha de emision.
            $factura->importe_bonificacion  += $this->floatvalue($request->importe_bonificacion);
            $factura->importe_total         = $this->floatvalue($factura->importe_subtotal - $factura->importe_bonificacion);
            $factura->primer_vto_codigo     = $this->getCodigoPago($factura->importe_total, $factura->primer_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);
            $factura->segundo_vto_importe   = $this->getImporteConTasaInteres($factura->importe_total, $interes->segundo_vto_tasa);
            $factura->segundo_vto_codigo    = $this->getCodigoPago($factura->segundo_vto_importe, $factura->segundo_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);

            // ACTUALIZACION TERCER VENCIMIENTO ------------------
            // PEDIDO DE ORNE: NO GENERAR EL 3ER CODIGO DE BARRAS (en caso de volver atras descomentar las siguientes lineas)

            // $fecha_actual                = $factura->tercer_vto_fecha != '' ? $factura->tercer_vto_fecha : $fecha_actual; // verifico si tiene una fecha establecida
            // $response                    = $this->getImporteConTasaInteresTercerVto($factura->segundo_vto_importe, $factura->segundo_vto_fecha, $interes->tercer_vto_tasa, Carbon::parse($fecha_actual)->format('d/m/Y'));
            // $factura->tercer_vto_fecha   = $fecha_actual;
            // $factura->tercer_vto_tasa    = $response['tasa'];
            // $factura->tercer_vto_importe = $response['importe'];
            // $factura->tercer_vto_codigo  = $this->getCodigoPago($factura->tercer_vto_importe, $factura->tercer_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);

            $factura->tercer_vto_fecha   = NULL;
            $factura->tercer_vto_tasa    = 0;
            $factura->tercer_vto_importe = 0;
            $factura->tercer_vto_codigo  = '';

            if ($factura->save()) {

                // Regenerar c�digos QR con el nuevo importe
                $this->generatePaymentQRCodes($factura);

                // Emitir nota de cr�dito en AFIP
                try {
                    $cbteTipo = $factura->talonario->letra == 'A' ? 3 : 8;
                    $lastVoucher = $this->afipService->getLastVoucher($factura->talonario->nro_punto_vta, $cbteTipo);

                    if ($factura->talonario->letra == 'A') {
                        $afipResponse = $this->afipService->notaCreditoA(
                            $factura->talonario->nro_punto_vta,
                            $factura->cliente->dni,
                            $this->floatvalue($request->importe_bonificacion),
                            $factura->nro_factura
                        );
                    } else {
                        $afipResponse = $this->afipService->notaCreditoB(
                            $factura->talonario->nro_punto_vta,
                            $this->floatvalue($request->importe_bonificacion),
                            $factura->nro_factura
                        );
                    }

                    Log::info('Respuesta AFIP nota de cr�dito', $afipResponse);

                    // Verificar si la respuesta indica �xito
                    if (isset($afipResponse['CbteDesde']) && !empty($afipResponse['CbteDesde'])) {
                        Log::info('CbteDesde encontrado, creando nota de cr�dito', ['CbteDesde' => $afipResponse['CbteDesde']]);
                        $nota = new NotaCredito();
                        $nota->factura_id = $factura->id;
                        $nota->talonario_id = $factura->talonario_id;
                        $nota->nro_nota_credito = $afipResponse['CbteDesde'];
                        $nota->importe_bonificacion = $this->floatvalue($request->importe_bonificacion);
                        $nota->importe_iva = $this->floatvalue($request->importe_bonificacion) * 0.21;
                        $nota->importe_total = $this->floatvalue($request->importe_bonificacion) * 1.21;
                        $nota->cae = isset($afipResponse['CAE']) ? $afipResponse['CAE'] : null;
                        try {
                            if (isset($afipResponse['CAEFchVto'])) {
                                $caeVto = $afipResponse['CAEFchVto'];
                                // AFIP puede devolver en formato Ymd (20260121) o Y-m-d (2026-01-21)
                                if (strlen($caeVto) == 8 && is_numeric($caeVto)) {
                                    $nota->cae_vto = Carbon::createFromFormat('Ymd', $caeVto);
                                } else {
                                    $nota->cae_vto = Carbon::parse($caeVto);
                                }
                            } else {
                                $nota->cae_vto = null;
                            }
                        } catch (\Exception $e) {
                            Log::error('Error parsing CAEFchVto: ' . $e->getMessage(), ['cae_vto' => $afipResponse['CAEFchVto'] ?? 'N/A']);
                            $nota->cae_vto = null;
                        }
                        $nota->fecha_emision = Carbon::now();
                        $nota->motivo = 'Bonificacion';
                        $nota->nro_cliente = $factura->cliente->nro_cliente;
                        $nota->periodo = $factura->periodo;

                        Log::info('Datos de nota de cr�dito a guardar', $nota->toArray());

                        try {
                            $saved = $nota->save();
                            if ($saved) {
                                Log::info('Nota de cr�dito creada exitosamente', ['nota_id' => $nota->id]);
                            } else {
                                Log::error('Error al guardar nota de cr�dito: save() retorn� false');
                                Log::error('Atributos de la nota:', $nota->getAttributes());
                            }
                        } catch (\Exception $e) {
                            Log::error('Excepci�n al guardar nota de cr�dito: ' . $e->getMessage());
                            Log::error('Stack trace: ' . $e->getTraceAsString());
                        }
                    } elseif (isset($afipResponse['CAE']) && !empty($afipResponse['CAE'])) {
                        // AFIP devolvi� CAE pero no CbteDesde - intentar obtener el n�mero asignado
                        Log::warning('AFIP devolvi� CAE pero no CbteDesde - intentando obtener n�mero de voucher asignado');
                        Log::warning('Respuesta AFIP completa:', $afipResponse);

                        // Intentar obtener el �ltimo voucher despu�s de la creaci�n para ver si fue asignado
                        try {
                            $newLastVoucher = $this->afipService->getLastVoucher($factura->talonario->nro_punto_vta, $cbteTipo);
                            Log::info('AFIP - �ltimo voucher despu�s de creaci�n', ['new_last_voucher' => $newLastVoucher]);

                            if ($newLastVoucher > $lastVoucher) {
                                // El voucher fue asignado, usar el nuevo n�mero
                                Log::info('AFIP - Voucher asignado detectado, usando n�mero: ' . $newLastVoucher);
                                $afipResponse['CbteDesde'] = $newLastVoucher;
                                $nota = new NotaCredito();
                                $nota->factura_id = $factura->id;
                                $nota->talonario_id = $factura->talonario_id;
                                $nota->nro_nota_credito = $newLastVoucher;
                                $nota->importe_bonificacion = $this->floatvalue($request->importe_bonificacion);
                                $nota->importe_iva = $this->floatvalue($request->importe_bonificacion) * 0.21;
                                $nota->importe_total = $this->floatvalue($request->importe_bonificacion) * 1.21;
                                $nota->cae = isset($afipResponse['CAE']) ? $afipResponse['CAE'] : null;
                                try {
                                    if (isset($afipResponse['CAEFchVto'])) {
                                        $caeVto = $afipResponse['CAEFchVto'];
                                        // AFIP puede devolver en formato Ymd (20260121) o Y-m-d (2026-01-21)
                                        if (strlen($caeVto) == 8 && is_numeric($caeVto)) {
                                            $nota->cae_vto = Carbon::createFromFormat('Ymd', $caeVto);
                                        } else {
                                            $nota->cae_vto = Carbon::parse($caeVto);
                                        }
                                    } else {
                                        $nota->cae_vto = null;
                                    }
                                } catch (\Exception $e) {
                                    Log::error('Error parsing CAEFchVto: ' . $e->getMessage(), ['cae_vto' => $afipResponse['CAEFchVto'] ?? 'N/A']);
                                    $nota->cae_vto = null;
                                }
                                $nota->fecha_emision = Carbon::now();
                                $nota->motivo = 'Bonificacion';
                                $nota->nro_cliente = $factura->cliente->nro_cliente;
                                $nota->periodo = $factura->periodo;

                                if ($nota->save()) {
                                    Log::info('Nota de cr�dito creada exitosamente con workaround', ['nota_id' => $nota->id, 'nro_nota_credito' => $newLastVoucher]);
                                } else {
                                    Log::error('Error al guardar nota de cr�dito con workaround');
                                    $nota = null;
                                }
                            } else {
                                Log::warning('AFIP - No se detect� nuevo voucher asignado, no se crea nota de cr�dito');
                                $nota = null;
                            }
                        } catch (\Exception $e) {
                            Log::error('Error al intentar obtener �ltimo voucher despu�s de creaci�n: ' . $e->getMessage());
                            $nota = null;
                        }
                    } else {
                        $nota = null;
                        Log::error('AFIP no devolvi� CbteDesde ni CAE v�lido, no se crea la nota de cr�dito');
                        Log::error('Respuesta completa de AFIP:', $afipResponse);
                    }

                    // Crear o actualizar bonificaci�n puntual
                    $existingBonificacion = BonificacionPuntual::where('factura_id', $factura->id)->first();
                    if ($existingBonificacion) {
                        $existingBonificacion->importe += $this->floatvalue($request->importe_bonificacion);
                        $existingBonificacion->afip_response = $afipResponse; // actualizar respuesta AFIP
                        $existingBonificacion->nota_credito_id = $nota ? $nota->id : $existingBonificacion->nota_credito_id;
                        if ($existingBonificacion->save()) {
                            Log::info('Bonificaci�n puntual actualizada exitosamente', ['bonificacion_id' => $existingBonificacion->id]);
                        } else {
                            Log::error('Error al actualizar bonificaci�n puntual: save() retorn� false');
                        }
                    } else {
                        $bonificacion = new BonificacionPuntual();
                        $bonificacion->factura_id = $factura->id;
                        $bonificacion->importe = $this->floatvalue($request->importe_bonificacion);
                        $bonificacion->descripcion = 'Bonificacion aplicada por la empresa';
                        $bonificacion->afip_response = $afipResponse;
                        $bonificacion->nota_credito_id = $nota ? $nota->id : null;
                        if ($bonificacion->save()) {
                            Log::info('Bonificaci�n puntual creada exitosamente', ['bonificacion_id' => $bonificacion->id]);
                        } else {
                            Log::error('Error al guardar bonificaci�n puntual: save() retorn� false');
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error al emitir nota de cr�dito: ' . $e->getMessage());
                    dd($e);
                }

                // actualizo el PDF del periodo e individual
                $this->setFacturasPeriodoPDF($factura->periodo, $factura->id);
                $filename = $this->getFacturaPDFPath($request, $factura);


                return redirect($request->previousUrl)->with(['status' => 'success', 'message' => 'Se ha bonificado la factura ' . $factura->talonario->letra . ' ' . $factura->talonario->nro_punto_vta . ' - ' . $factura->nro_factura . '.', 'icon' => 'fa-smile-o', 'filename' => $filename, 'factura_id' => $factura->id]);
            } else {

                return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
            }
        }
    }

    // actualizacion de factura
    public function getBillUpdate(Request $request, $id)
    {

        $factura = Factura::find($id);

        $factura->talonario;
        $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);

        $factura->nro_factura = $this->zerofill($factura->nro_factura);
        // $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);

        $factura->cliente;

        // $factura->fecha_emision = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
        // $factura->primer_vto_fecha = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
        // $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');

        $factura->importe_subtotal = number_format($factura->importe_subtotal, 2, ',', '.');
        // $factura->importe_bonificacion = number_format($factura->importe_bonificacion, 2); 
        // $factura->importe_total = number_format($factura->importe_total, 2); 
        // $factura->segundo_vto_importe = number_format($factura->segundo_vto_importe, 2); 

        // $factura->fecha_pago = $factura->fecha_pago ? Carbon::parse($factura->fecha_pago)->format('d/m/Y') : null;

        // $detalles =  $factura->detalle;

        // foreach ($detalles as $detalle) {
        //     $detalle->servicio;
        // }            


        // // genero los PDF's de las facturas individuales
        // $filename = $factura->talonario->nro_punto_vta.'-'.$factura->nro_factura;
        // $factura->pdf = $request->root().'/'.config('constants.folder_facturas') . 'factura-'.$filename.'.pdf';

        // return $factura;

        return View::make('period.view_factura_actualizar')->with(['factura' => $factura]);
    }

    // actualizacion de factura POST
    public function getBillUpdatePost(Request $request, $id)
    {


        //-- VALIDATOR START --//
        $rules = array(
            'tercer_vto_fecha'    => 'required|date_format:d/m/Y',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        $factura = Factura::find($id);

        // obtengo el nro de punto de venta y el nro de factura
        $factura->talonario;
        $factura_nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);
        $factura_nro_factura = $this->zerofill($factura->nro_factura);

        if ($factura) {

            // obtengo la conf. de intereses 
            $interes = Interes::find(1);

            // obtengo la fecha actual
            $fecha_actual = Carbon::now();

            $factura->fecha_emision         = $fecha_actual; // actualizo la fecha de emision.

            // ACTUALIZACION TERCER VENCIMIENTO ------------------
            $response                    = $this->getImporteConTasaInteresTercerVto($factura->segundo_vto_importe, $factura->segundo_vto_fecha, $interes->tercer_vto_tasa, $request->tercer_vto_fecha);
            $factura->tercer_vto_fecha   = Carbon::createFromFormat('d/m/Y', $request->tercer_vto_fecha);
            $factura->tercer_vto_tasa    = $response['tasa'];
            $factura->tercer_vto_importe = $response['importe'];
            $factura->tercer_vto_codigo  = $this->getCodigoPago($factura->tercer_vto_importe, $factura->tercer_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);


            // ----------------------------------------------------

            if ($factura->save()) {

                // actualizo el PDF del periodo e individual
                $this->setFacturasPeriodoPDF($factura->periodo, $factura->id);
                $filename = $this->getFacturaPDFPath($request, $factura);

                return redirect($request->previousUrl)->with(['status' => 'success', 'message' => 'Se ha actualizado la factura ' . $factura->talonario->letra . ' ' . $factura->talonario->nro_punto_vta . ' - ' . $factura->nro_factura . '.', 'icon' => 'fa-smile-o', 'filename' => $filename, 'factura_id' => $factura->id]);
            } else {

                return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
            }
        }
    }

    // pago de factura
    public function getBillPay(Request $request, $id)
    {

        $factura = Factura::find($id);

        $factura->talonario;
        $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);

        $factura->nro_factura = $this->zerofill($factura->nro_factura);
        $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);

        $factura->cliente;

        // $factura->fecha_emision = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
        $factura->primer_vto_fecha = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
        $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');
        $factura->tercer_vto_fecha = Carbon::parse($factura->tercer_vto_fecha)->format('d/m/Y');

        // $factura->importe_subtotal = number_format($factura->importe_subtotal, 2); 
        // $factura->importe_bonificacion = number_format($factura->importe_bonificacion, 2); 
        $factura->importe_total = number_format($factura->importe_total, 2);
        $factura->segundo_vto_importe = number_format($factura->segundo_vto_importe, 2);
        $factura->tercer_vto_importe = number_format($factura->tercer_vto_importe, 2);

        // $factura->fecha_pago = $factura->fecha_pago ? Carbon::parse($factura->fecha_pago)->format('d/m/Y') : null;

        // $detalles =  $factura->detalle;

        // foreach ($detalles as $detalle) {
        // $detalle->servicio;
        // }             


        // // genero los PDF's de las facturas individuales
        // $filename = $factura->talonario->nro_punto_vta.'-'.$factura->nro_factura;
        // $factura->pdf = $request->root().'/'.config('constants.folder_facturas') . 'factura-'.$filename.'.pdf';

        // return $factura;

        return View::make('period.view_factura_pagar')->with(['factura' => $factura, 'forma_pago' => $this->forma_pago]);
    }

    // actualizacion de factura POST
    public function getBillPayPost(Request $request, $id)
    {

        //-- VALIDATOR START --//
        $rules = array(
            'fecha_pago'    => 'required|date_format:d/m/Y',
            'importe_pago' => ['required', 'regex:/^-?(?:0|[1-9]\d{0,2}(?:\.\d{3})*)(?:,\d+)?$/'],
            'forma_pago'    => 'required',

        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        $factura = Factura::find($id);

        if ($factura) {

            // campos del pago 
            $factura->fecha_pago     = Carbon::createFromFormat('d/m/Y', $request->fecha_pago);
            $factura->importe_pago   = $this->floatvalue($request->importe_pago);
            $factura->forma_pago     = $request->forma_pago;

            // ----------------------------------------------------

            if ($factura->save()) {

                $factura->talonario;
                $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);

                $factura->nro_factura = $this->zerofill($factura->nro_factura);

                return redirect($request->previousUrl)->with(['status' => 'success', 'message' => 'Se ha imputado el Pago a la factura ' . $factura->talonario->letra . ' ' . $factura->talonario->nro_punto_vta . ' - ' . $factura->nro_factura . '.', 'icon' => 'fa-smile-o']);
            } else {

                return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
            }
        }
    }

    // pago de factura
    public function getBillPayCancel(Request $request, $id)
    {

        $factura = Factura::find($id);
        $factura->talonario;
        $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);
        $factura->nro_factura = $this->zerofill($factura->nro_factura);
        $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);
        $factura->cliente;
        $factura->fecha_pago = $factura->fecha_pago ? Carbon::parse($factura->fecha_pago)->format('d/m/Y') : null;
        $factura->importe_pago = number_format($factura->importe_pago, 2);
        $factura->forma_pago = $this->forma_pago[$factura->forma_pago];
        // return $factura;

        return View::make('period.view_factura_pagar_cancelar')->with(['factura' => $factura, 'forma_pago' => $this->forma_pago]);
    }

    // actualizacion de factura POST
    public function getBillPayCancelPost(Request $request, $id)
    {

        $factura = Factura::find($id);

        if ($factura) {

            // obtengo laos valores actuales
            $current_forma_pago = $factura->forma_pago;
            $current_lote       = $factura->lote;

            // campos del pago 
            $factura->fecha_pago    = NULL;
            $factura->importe_pago  = NULL;
            $factura->forma_pago    = NULL;
            $factura->lote          = NULL;

            if ($factura->save()) {

                // opero en las otras tablas
                if ($current_lote != null && $current_lote != '') {

                    // si la forma de pago es Cobro Express elimino el registro de la tabla import_ce
                    if ($current_forma_pago == 3) {
                        $import_ce = ImportCe::destroy($current_lote);
                    }
                }

                // ----------------------------------------------------

                $factura->talonario;
                $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);

                $factura->nro_factura = $this->zerofill($factura->nro_factura);

                return redirect($request->previousUrl)->with(['status' => 'success', 'message' => 'Se ha Cancelado el Pago a la factura ' . $factura->talonario->letra . ' ' . $factura->talonario->nro_punto_vta . ' - ' . $factura->nro_factura . '.', 'icon' => 'fa-smile-o']);
            } else {

                return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
            }
        }
    }

    // creacion de un periodo
    public function create()
    {

        // verifico que hay servicios asociados a los clientes
        $servicios = ServicioUsuario::all();

        // verifico que existan talonarios
        $talonarios = Talonario::all();

        // verifico que existan intereses
        $interes = Interes::all();

        // verifico que existan clientes activos

        // For work edit file : \vendor\zizaco\entrust\src\Entrust\Traits\EntrustRoleTrait.php #52
        $clientRole = Role::where('name', 'client')->first();
        $clients = 0;

        if ($clientRole) {
            $users = $clientRole->users()->get();

            if (count($users)) {
                foreach ($users as $user) {
                    if ($user->status == 1) {
                        $clients++;
                    }
                }
            }
        }

        // obtengo el siguiente mes a facturar 
        $factura    = Factura::whereNull('deleted_at')->orderBy('id', 'desc')->first();

        if ($factura && $factura->periodo && preg_match('/^\d{1,2}\/\d{4}$/', $factura->periodo)) {
            try {
                $periodo_actual    = Carbon::createFromFormat('m/Y', $factura->periodo);
                $periodo_siguiente = $periodo_actual->addMonth();
                $periodo_siguiente = Carbon::parse($periodo_siguiente)->format('m/Y');
            } catch (Exception $e) {
                $periodo_siguiente = date('m/Y');
            }
        } else {

            $periodo_siguiente = date('m/Y');
        }

        return View::make('period.create')->with(['servicios' => $servicios, 'talonarios' => $talonarios, 'interes' => $interes, 'clients' => $clients, 'periodo_siguiente' => $periodo_siguiente]);
    }

    // facturo el periodo
    public function store(Request $request)
    {

        // ver
        // https://laravel.io/forum/09-16-2014-validator-greater-than-other-field

        // return $request->all();

        //-- VALIDATOR START --//
        $rules = array(
            'fecha_emision' => 'required|date_format:d/m/Y',
            'periodo'       => 'required|unique:facturas,periodo,NULL,id,deleted_at,NULL|date_format:m/Y',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        // For work edit file : \vendor\zizaco\entrust\src\Entrust\Traits\EntrustRoleTrait.php #52
        $clientRole = Role::where('name', 'client')->first();
        $users = $clientRole ? $clientRole->users()->get() : collect([]);

        // Obtengo los servicios activos de los clientes activos
        if (count($users)) {

            // obtengo la conf. de intereses 
            $interes = Interes::find(1);
            $dt = Carbon::now();

            // ---------------------------------------------------------------------------------------------------------------           

            // foreach ($users as $user) {
            //     $user->servicios;
            //     foreach ($user->servicios as $servicio) {
            //         $servicio->alta_servicio_periodo     = Carbon::parse($servicio->alta_servicio)->format('m/Y');
            //         $servicio->periodo = $request->periodo;
            //         // $servicio->alta_servicio_periodo_facturado_date = Carbon::parse(Carbon::createFromFormat('m/Y', $request->periodo))->format('m/Y');
            //         $servicio->getIfBillable = $this->getIfBillable($request->periodo, $servicio->alta_servicio_periodo);

            //     }
            // }

            // return $users;


            foreach ($users as $user) {

                // if ($user->status == 1 && $user->nro_cliente == 375) {
                if ($user->status == 1) {

                    foreach ($user->servicios as $servicio) {

                        // verify if es billable
                        $alta_servicio_periodo = Carbon::parse($servicio->alta_servicio)->format('m/Y');

                        // control para saber si es un servicio o un plan de pago
                        if ($servicio->pp_flag == 1) {

                            // verifico si el plan de pago sige vigente 
                            $ifBillable = $this->getIfBillablePlanPago($user, $servicio);
                        } else {
                            $ifBillable = $this->getIfBillable($request->periodo, $alta_servicio_periodo);
                            $servicio->plan_pago = $this->getPlanPagoValue($servicio->plan_pago);
                        }

                        // flag for get if Is Billable
                        $servicio->ifBillable = $ifBillable;

                        // echo $ifBillable ? 'si,' : 'no,';

                        // facturo solo los servicios activos y que fueron contratados a partir del periodo dado
                        // if ($servicio->status == 1 && $alta_servicio_periodo == $request->periodo) {
                        if ($servicio->status == 1 && $ifBillable) {

                            // costo de instalacion ---------------------------------------------------------
                            if ($servicio->pp_flag == 1) {

                                $servicio->costo_abono_pagar = 0;
                                $servicio->costo_instalacion_cuotas_pagas = $this->getNroCuotasInstalacionPlanPago($user->id, $servicio->servicio_id);
                                $servicio->costo_instalacion_importe_pagar = $servicio->abono_mensual;
                            } else {

                                // proporcional  ---------------------------------------------------------------
                                $proporcional = $this->getProporcional($user->id, $request->periodo, $servicio->alta_servicio, $servicio->abono_proporcional);
                                $servicio->costo_proporcional_importe = $proporcional['importe'];
                                $servicio->costo_proporcional_dias = $proporcional['dias'];

                                if ($servicio->costo_proporcional_importe > 0) {
                                    $servicio->costo_abono_pagar = 0;
                                } else {
                                    $servicio->costo_abono_pagar = $servicio->abono_mensual;
                                }

                                // obtengo la cantidad de cuotas pagas del servicio
                                // $servicio->costo_instalacion_cuotas_pagas = $this->getNroCuotasInstalacion($user->id, $servicio->servicio_id, $request->periodo, $servicio->alta_servicio, $this->getPlanPagoValue($servicio->plan_pago));
                                $servicio->costo_instalacion_cuotas_pagas = $this->getNroCuotasInstalacion($request->periodo, $servicio->alta_servicio);

                                // asigno el importe a pagar de instalacion si aun se deben cuptas
                                if ($servicio->costo_instalacion_cuotas_pagas < $servicio->plan_pago) {
                                    $servicio->costo_instalacion_importe_pagar = $servicio->costo_instalacion / $servicio->plan_pago;
                                } else {
                                    $servicio->costo_instalacion_importe_pagar = 0;
                                }
                            }

                            // asigno las variables ---------------------------------------------------------
                            $items[$user->id]['cliente'] = $user;
                            $items[$user->id]['servicios_activos'][] = $servicio;

                            // agrego los atributos de calle y altura para el ordenamiento
                            $items[$user->id]['domicilio'] = strtolower($user->calle . ' ' . $user->altura);
                        }
                    }
                }
            }

            // ---------------------------------------------------------------------------------------------------------------           

            // ordeno los items por el atributo "domicilio"
            usort($items, array($this, "cmp_obj"));
            $afip = new AfipService();

            // debug
            // return $items;

            // Genero las Facturas
            foreach ($items as $item) {

                // calculo de importes con bonificaciones aplicadas
                $subtotal = 0;
                $bonificacion_total = 0;
                $fecha_facturacion = Carbon::createFromFormat('d/m/Y', $request->fecha_emision);

                foreach ($item['servicios_activos'] as $servicio) {
                    $importe_servicio = $servicio->costo_proporcional_importe + $servicio->costo_abono_pagar + $servicio->costo_instalacion_importe_pagar;

                    // Verificar si existe bonificaci�n vigente para este servicio
                    $bonificacion = BonificacionServicio::where('service_id', $servicio->servicio_id)
                        ->where('activo', true)
                        ->whereRaw('fecha_inicio <= ?', [$fecha_facturacion])
                        ->whereRaw('DATE_ADD(fecha_inicio, INTERVAL periodos_bonificacion MONTH) > ?', [$fecha_facturacion])
                        ->first();
                    if ($bonificacion) {
                        $descuento_servicio = $bonificacion->calcularBonificacion($importe_servicio);
                        $bonificacion_total += $descuento_servicio;
                        // Guardar informaci�n de bonificaci�n en el servicio para el detalle
                        $servicio->bonificacion_aplicada = $descuento_servicio;
                        $servicio->bonificacion_id = $bonificacion->id;
                        $servicio->bonificacion_detalle = $bonificacion->descripcion ? $bonificacion->descripcion : 'Bonificacion aplicada al servicio';
                        $servicio->bonificacion_porcentaje = $bonificacion->porcentaje_bonificacion;
                        $servicio->iva_bonificacion = $descuento_servicio * 0.21;
                    } else {
                        $servicio->bonificacion_aplicada = 0;
                        $servicio->bonificacion_id = null;
                        $servicio->bonificacion_porcentaje = 0;
                    }

                    $subtotal += $importe_servicio;
                }
                //Calculo de IVA
                $iva_subtotal                      = ($subtotal) * 0.21;
                $iva_bonificacion                  = ($bonificacion_total) * 0.21;
                $iva                               = ($subtotal - $bonificacion_total) * 0.21;
                // Cabecera
                $factura = new Factura;
                $factura->user_id               = $item['cliente']->id;
                $factura->nro_cliente           = $item['cliente']->nro_cliente;
                $factura->talonario_id          = $item['cliente']->talonario_id;
                $factura->nro_factura           = $this->getNroFactura($item['cliente']->talonario_id);
                $factura->periodo               = $request->periodo;
                $factura->fecha_emision         = Carbon::createFromFormat('d/m/Y', $request->fecha_emision);
                $factura->importe_subtotal         = $this->floatvalue(number_format($subtotal, 2));
                $factura->importe_subtotal_iva     = $this->floatvalue(number_format($iva_subtotal, 2));
                $factura->importe_bonificacion     = $this->floatvalue(number_format($bonificacion_total, 2));
                $factura->importe_bonificacion_iva = $this->floatvalue(number_format($iva_bonificacion, 2));
                $factura->importe_total            = $this->floatvalue(number_format($subtotal - $bonificacion_total, 2));
                $factura->importe_iva              = $this->floatvalue(number_format($iva, 2));

                $mes_periodo = substr($request->periodo, 0, 2);
                $ano_periodo = substr($request->periodo, 3, 4);

                // obtengo la fecha de vencimiento del mes siguiente ya que se factura a mes atrasado
                $periodo_actual    = Carbon::createFromFormat('m/Y', $factura->periodo);
                $periodo_siguiente = $periodo_actual->addMonthNoOverflow(1);
                $ano_periodo_siguiente = substr($periodo_siguiente, 0, 4);
                $mes_periodo_siguiente = substr($periodo_siguiente, 5, 2);
                // return $factura->periodo.'<br>'.$periodo_actual.'<br>'.$periodo_siguiente.'<br>'.$mes_periodo_siguiente.'<br>'.$ano_periodo_siguiente;


                // obtengo el nro de punto de venta y el nro de factura
                $talonario = Talonario::find($factura->talonario_id);
                $factura_nro_punto_vta  =  $this->zerofill($talonario->nro_punto_vta, 4);
                $factura_nro_factura = $this->zerofill($factura->nro_factura);


                // ----------------------------------------------------
                $factura->primer_vto_fecha      = Carbon::createFromFormat('d/m/Y', $interes->primer_vto_dia . '/' . $mes_periodo_siguiente . '/' . $ano_periodo_siguiente);
                $factura->primer_vto_codigo     = $this->getCodigoPago($factura->importe_total, $factura->primer_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);

                // ----------------------------------------------------                
                $factura->segundo_vto_fecha     = Carbon::createFromFormat('d/m/Y', $interes->segundo_vto_dia . '/' . $mes_periodo_siguiente . '/' . $ano_periodo_siguiente);
                $factura->segundo_vto_tasa      = $interes->segundo_vto_tasa;
                $factura->segundo_vto_importe   = $this->getImporteConTasaInteres($factura->importe_total, $interes->segundo_vto_tasa);
                $factura->segundo_vto_codigo    = $this->getCodigoPago($factura->segundo_vto_importe, $factura->segundo_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);
                // ----------------------------------------------------

                $factura->tercer_vto_tasa       = $interes->tercer_vto_tasa;

                // guardo la factura
                if ($factura->save()) {

                    // **APLICAR SALDO A FAVOR SI EXISTE PARA ESTE PERÍODO**
                    try {
                        $periodoFactura = $factura->periodo; // Formato MM/YYYY
                        $saldoDisponible = $this->getTotalSaldoFavorCliente($factura->user_id, $periodoFactura);
                        
                        Log::info('=== VERIFICANDO SALDO A FAVOR ===', [
                            'user_id' => $factura->user_id,
                            'factura_id' => $factura->id,
                            'periodo' => $periodoFactura,
                            'saldo_disponible' => $saldoDisponible,
                            'importe_factura' => $factura->importe_total
                        ]);
                        
                        if ($saldoDisponible > 0) {
                            Log::info('APLICANDO saldo a favor del período', [
                                'user_id' => $factura->user_id,
                                'periodo' => $periodoFactura,
                                'saldo' => $saldoDisponible
                            ]);
                            
                            $resultado = $this->aplicarSaldoFavorAFactura($factura->user_id, $factura->id, $factura->importe_total, $periodoFactura);
                            $importeDescontado = $resultado['importe_descontado'];
                            
                            Log::info('RESULTADO aplicación saldo', [
                                'importe_descontado' => $importeDescontado,
                                'saldos_aplicados_count' => count($resultado['saldos_aplicados'])
                            ]);
                            
                            if ($importeDescontado > 0) {
                                $descripcion = 'Saldo a favor aplicado';
                                $caesOriginales = [];
                                
                                if (count($resultado['saldos_aplicados']) > 0) {
                                    $periodos = [];
                                    foreach ($resultado['saldos_aplicados'] as $saldoAplicado) {
                                        // Cargar saldo con factura anulada (incluyendo soft deleted)
                                        $saldo = SaldoFavor::with(['facturaAnulada' => function($query) {
                                            $query->withTrashed();
                                        }])->find($saldoAplicado['saldo_id']);
                                        
                                        Log::info('DEBUG Saldo recuperado', [
                                            'saldo_id' => $saldoAplicado['saldo_id'],
                                            'saldo_existe' => !is_null($saldo),
                                            'factura_anulada_id' => $saldo ? $saldo->factura_anulada_id : null,
                                            'tiene_factura_anulada' => $saldo && $saldo->facturaAnulada ? true : false,
                                            'cae_factura_anulada' => $saldo && $saldo->facturaAnulada ? $saldo->facturaAnulada->cae : null
                                        ]);
                                        
                                        if ($saldo) {
                                            $periodos[] = $saldo->periodo;
                                            
                                            // **RECUPERAR CAE DE LA FACTURA ORIGINAL**
                                            if ($saldo->facturaAnulada && $saldo->facturaAnulada->cae) {
                                                $caesOriginales[] = [
                                                    'factura_id' => $saldo->facturaAnulada->id,
                                                    'nro_factura' => $saldo->facturaAnulada->nro_factura,
                                                    'cae' => $saldo->facturaAnulada->cae,
                                                    'cae_vto' => $saldo->facturaAnulada->cae_vto,
                                                    'periodo' => $saldo->periodo,
                                                    'importe_aplicado' => $saldoAplicado['importe_aplicado']
                                                ];
                                            }
                                        }
                                    }
                                    $periodos = array_unique($periodos);
                                    $descripcion .= ' de período(s): ' . implode(', ', $periodos);
                                }
                                
                                // **REGISTRAR CAEs ORIGINALES EN LOG Y ASIGNAR A LA FACTURA**
                                if (!empty($caesOriginales)) {
                                    Log::info('CAEs DE FACTURAS ORIGINALES APLICADAS', [
                                        'factura_nueva_id' => $factura->id,
                                        'nro_factura_nueva' => $factura->nro_factura,
                                        'periodo_nueva' => $factura->periodo,
                                        'caes_originales' => $caesOriginales,
                                        'total_aplicado' => $importeDescontado
                                    ]);
                                    
                                    // **ASIGNAR EL CAE ORIGINAL A LA NUEVA FACTURA**
                                    // Si hay un único CAE original, usarlo directamente
                                    if (count($caesOriginales) == 1) {
                                        $factura->cae = $caesOriginales[0]['cae'];
                                        $factura->cae_vto = $caesOriginales[0]['cae_vto'] ?? null;
                                        
                                        Log::info('CAE ORIGINAL ASIGNADO A FACTURA NUEVA', [
                                            'factura_nueva_id' => $factura->id,
                                            'cae_original' => $factura->cae,
                                            'factura_original_id' => $caesOriginales[0]['factura_id']
                                        ]);
                                    }
                                }
                                
                                Log::info('CREANDO bonificación puntual', [
                                    'factura_id' => $factura->id,
                                    'importe' => $importeDescontado,
                                    'descripcion' => $descripcion
                                ]);
                                
                                $bonificacion = $this->crearBonificacionPorSaldoFavor($factura->id, $importeDescontado, $descripcion);
                                
                                Log::info('BONIFICACIÓN CREADA', [
                                    'bonificacion_id' => $bonificacion->id,
                                    'factura_id' => $factura->id,
                                    'importe' => $bonificacion->importe
                                ]);
                                
                                $factura->importe_bonificacion += $importeDescontado;
                                $factura->importe_total -= $importeDescontado;
                                if ($factura->importe_total < 0) $factura->importe_total = 0;
                                
                                // **RECALCULAR VENCIMIENTOS CON EL NUEVO IMPORTE_TOTAL**
                                $factura->primer_vto_codigo = $this->getCodigoPago($factura->importe_total, $factura->primer_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);
                                $factura->segundo_vto_importe = $this->getImporteConTasaInteres($factura->importe_total, $interes->segundo_vto_tasa);
                                $factura->segundo_vto_codigo = $this->getCodigoPago($factura->segundo_vto_importe, $factura->segundo_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);
                                
                                $factura->save();
                                
                                Log::info('FACTURA ACTUALIZADA con saldo', [
                                    'factura_id' => $factura->id,
                                    'importe_bonificacion' => $factura->importe_bonificacion,
                                    'importe_total' => $factura->importe_total,
                                    'primer_vto_codigo' => $factura->primer_vto_codigo,
                                    'segundo_vto_importe' => $factura->segundo_vto_importe,
                                    'segundo_vto_codigo' => $factura->segundo_vto_codigo
                                ]);
                                
                                // **SI EL SALDO CUBRIÓ TODO, MARCAR COMO PAGADA**
                                if ($factura->importe_total == 0) {
                                    $factura->importe_pago = 0;
                                    $factura->forma_pago = 5; // Saldo a favor
                                    $factura->fecha_pago = \Carbon\Carbon::now();
                                    $factura->save();
                                    
                                    Log::info('FACTURA MARCADA COMO PAGADA', [
                                        'factura_id' => $factura->id,
                                    ]);
                                }
                            } else {
                                Log::warning('SALDO NO APLICADO - importe descontado es 0');
                            }
                        } else {
                            Log::info('No hay saldo disponible para este cliente en este período', [
                                'periodo' => $periodoFactura
                            ]);
                        }
                    } catch (\Exception $eSaldo) {
                        Log::error('ERROR aplicando saldo a favor', [
                            'mensaje' => $eSaldo->getMessage(),
                            'archivo' => $eSaldo->getFile(),
                            'linea' => $eSaldo->getLine(),
                            'trace' => $eSaldo->getTraceAsString()
                        ]);
                    }

                    // **EMITIR FACTURA EN AFIP SOLO SI NO ESTÁ CUBIERTA POR SALDO A FAVOR**
                    // Si importe_total = 0, significa que el saldo cubrió todo y permanece la factura original
                    $debeEmitirEnAfip = $factura->importe_total > 0;
                    
                    if ($debeEmitirEnAfip) {
                        // Emitir factura en AFIP
                        try {
                            if ($talonario->letra == 'A') {
                                $afipResponse = $this->afipService->facturaA(
                                    $talonario->nro_punto_vta,
                                    $item['cliente']->dni,
                                    $factura->importe_total
                                );
                            } else {
                                $afipResponse = $this->afipService->facturaB(
                                    $talonario->nro_punto_vta,
                                    $factura->importe_total
                                );
                            }
                        

                        Log::info('Respuesta AFIP factura peri�dica', $afipResponse);

                        // Actualizar factura con datos de AFIP
                        if (isset($afipResponse['CAE']) && !empty($afipResponse['CAE'])) {
                            $factura->cae = $afipResponse['CAE'];
                            try {
                                if (isset($afipResponse['CAEFchVto'])) {
                                    $caeVto = $afipResponse['CAEFchVto'];
                                    
                                    Log::info('DEBUG CAEFchVto', [
                                        'valor' => $caeVto,
                                        'tipo' => gettype($caeVto),
                                        'longitud' => strlen($caeVto),
                                        'es_numerico' => is_numeric($caeVto)
                                    ]);
                                    
                                    // AFIP puede devolver en formato Ymd (20260121) o Y-m-d (2026-01-23)
                                    if (strlen($caeVto) == 8 && is_numeric($caeVto)) {
                                        Log::info('Usando formato Ymd');
                                        $factura->cae_vto = Carbon::createFromFormat('Ymd', $caeVto);
                                    } else {
                                        Log::info('Usando Carbon::parse para formato: ' . $caeVto);
                                        // Asegurar que sea string
                                        $caeVtoStr = (string)$caeVto;
                                        $factura->cae_vto = Carbon::parse($caeVtoStr);
                                    }
                                    
                                    Log::info('CAEFchVto parseado correctamente', ['resultado' => $factura->cae_vto]);
                                } else {
                                    $factura->cae_vto = null;
                                }
                            } catch (\Exception $e) {
                                Log::error('Error parsing CAEFchVto en factura periódica', [
                                    'mensaje' => $e->getMessage(),
                                    'cae_vto_original' => $afipResponse['CAEFchVto'] ?? 'N/A',
                                    'tipo' => gettype($afipResponse['CAEFchVto'] ?? null),
                                    'trace' => $e->getTraceAsString()
                                ]);
                                $factura->cae_vto = null;
                            }
                            $factura->save();
                            Log::info('Factura peri�dica actualizada con datos AFIP', ['factura_id' => $factura->id, 'cae' => $factura->cae]);
                        } else {
                            Log::warning('AFIP no devolvi� CAE v�lido para factura peri�dica', ['factura_id' => $factura->id, 'afip_response' => $afipResponse]);
                        }
                        } 
                        catch (\Exception $e) {
                            Log::error('Error al emitir factura peri�dica en AFIP: ' . $e->getMessage());
                            // No interrumpe el proceso, la factura se guarda sin CAE
                        }
                    } else {
                        // Factura cubierta completamente por saldo a favor - NO se emite en AFIP
                        Log::info('Factura NO emitida en AFIP - Cubierta por saldo a favor', [
                            'factura_id' => $factura->id,
                            'nro_factura' => $factura->nro_factura,
                            'periodo' => $factura->periodo,
                            'importe_total' => $factura->importe_total,
                            'importe_bonificacion' => $factura->importe_bonificacion,
                            'forma_pago' => $factura->forma_pago
                        ]);
                    }

                    // Detalle
                    foreach ($item['servicios_activos'] as $servicio) {

                        $factura_detalle = new FacturaDetalle;
                        $factura_detalle->factura_id = $factura->id;
                        $factura_detalle->servicio_id = $servicio->servicio_id;
                        $factura_detalle->abono_mensual = $servicio->abono_mensual;

                        // proporcional 
                        $factura_detalle->abono_proporcional = $servicio->costo_proporcional_importe > 0 ? $servicio->costo_proporcional_importe : null;
                        $factura_detalle->dias_proporcional = $servicio->costo_proporcional_dias > 0 ? $servicio->costo_proporcional_dias : null;

                        $factura_detalle->instalacion_plan_pago = $servicio->plan_pago;

                        if ($servicio->pp_flag == 1) {
                            $factura_detalle->costo_instalacion = $servicio->abono_mensual;
                            $factura_detalle->instalacion_cuota = $servicio->costo_instalacion_cuotas_pagas + 1;
                        } else {
                            $factura_detalle->costo_instalacion = $servicio->costo_instalacion_importe_pagar > 0 ? $servicio->costo_instalacion_importe_pagar : null;
                            $factura_detalle->instalacion_cuota = $servicio->costo_instalacion_importe_pagar > 0 ? $servicio->costo_instalacion_cuotas_pagas + 1 : null;
                        }
                        $factura_detalle->pp_flag = $servicio->pp_flag;
                        if ($servicio->bonificacion_id != null) {
                            $factura_detalle->iva_bonificacion = $servicio->iva_bonificacion;
                            $factura_detalle->importe_bonificacion = $servicio->bonificacion_aplicada;
                            $factura_detalle->bonificacion_detalle = $servicio->bonificacion_detalle;
                        }

                        // Calcular el monto de IVA para este servicio
                        $importe_servicio = 0;
                        if ($servicio->costo_proporcional_importe > 0) {
                            $importe_servicio += $servicio->costo_proporcional_importe;
                        }
                        if ($servicio->costo_abono_pagar > 0) {
                            $importe_servicio += $servicio->costo_abono_pagar;
                        }
                        if ($servicio->costo_instalacion_importe_pagar > 0) {
                            $importe_servicio += $servicio->costo_instalacion_importe_pagar;
                        }
                        // El IVA se calcula solo si el importe es mayor a cero
                        $factura_detalle->importe_iva = $importe_servicio > 0 ? round($importe_servicio * 0.21, 2) : 0;

                        // guardo el detalle de la factura
                        $factura_detalle->save();
                    }
                }

                // agrego las facturas al array general
                $facturas[] = $factura;
            }
            //registrar y obtener datos de AFIP
            // debug
            // return $facturas;
            // return $facturas = Factura::where('periodo', $request->periodo)->get();

            // Env�o autom�tico de emails para todas las facturas creadas
            foreach ($facturas as $factura) {
                try {
                    $this->sendEmailFactura($request, $factura);
                    Log::info("Email enviado autom�ticamente para factura ID: {$factura->id}, cliente: {$factura->cliente->email}");
                } catch (Exception $e) {
                    // Log del error pero no interrumpir el proceso
                    Log::error("Error enviando email autom�tico para factura ID: {$factura->id}. Error: " . $e->getMessage());
                }
            }
            // genero los pdf's: s�lo del periodo y factura creada (paso el id para evitar regenerar todo)
            $this->setFacturasPeriodoPDF($request->periodo); // antes regeneraba todo el periodo
            $filename = $this->getFacturasPeriodoPDFPath($request);

            return redirect('/admin/period')->with(['status' => 'success', 'message' => 'El per�odo ' . $request->periodo . ' fu� facturado.', 'icon' => 'fa-smile-o', 'filename' => $filename]);
        } else {

            return redirect('/admin/period')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        }
    }

    // funcion para ordenar el array de facturas
    static function cmp_obj($a, $b)
    {
        $al = strtolower($a['domicilio']);
        $bl = strtolower($b['domicilio']);
        if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;
    }

    public function getIfBillable($periodo, $alta_servicio)
    {
        $array_alta_servicio = explode('/', $alta_servicio);
        $array_periodo       = explode('/', $periodo);

        if (($array_alta_servicio[1] < $array_periodo[1]) ||
            ($array_alta_servicio[1] == $array_periodo[1] && $array_alta_servicio[0] <= $array_periodo[0])
        ) {

            $response = true;
        } else {

            $response = false;
        }

        return $response;
    }

    public function getIfBillablePlanPago($user, $servicio)
    {
        $response = true;

        // get factura from user
        $factura = Factura::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->first();

        if ($factura) {

            // get detalle factura
            $factura->detalle;

            foreach ($factura->detalle as $servicio_fac) {

                if ($servicio_fac->servicio_id == $servicio->servicio_id && $servicio_fac->pp_flag == 1) {

                    if ($servicio_fac->instalacion_cuota != null && $servicio_fac->instalacion_cuota < $servicio_fac->instalacion_plan_pago) {

                        $response = true;
                    } else {

                        $response = false;
                    }
                }
            }
        }

        return $response;
    }

    public function floatvalue($val)
    {

        $val = str_replace(",", ".", $val);
        $val = preg_replace('/\.(?=.*\.)/', '', $val);
        return floatval($val);
    }

    public function zerofill($num, $zerofill = 8)
    {
        // ?? SOLUCI�N: Verificar si es num�rico antes de aplicar zerofill
        if (is_numeric($num)) {
            return str_pad($num, $zerofill, '0', STR_PAD_LEFT);
        }

        // Si es alfanum�rico, devolver tal como est�
        return $num;
    }

    public function getImporteConTasaInteres($importe, $tasa)
    {

        return $this->floatvalue(number_format(($importe * $tasa / 100) + $importe, 2)); // 2 decimales

    }

    public function getImporteConTasaInteresTercerVto($importe, $segundo_vto_fecha, $tasa, $fecha_pago)
    {

        $response          = [];
        $segundo_vto_fecha = Carbon::parse($segundo_vto_fecha);
        $fecha_pago        = Carbon::createFromFormat('d/m/Y', $fecha_pago);

        // calculo los dias excedentes
        $total_dias = $segundo_vto_fecha->diffInDays($fecha_pago) + 1;

        // calculo la tasa de interes multiplicando los dias por el % punitorio
        $response['tasa'] = $total_dias * $tasa;

        // calculo el importe final con la tasa final
        $response['importe'] = $this->floatvalue(number_format(($importe * $response['tasa'] / 100) + $importe, 2)); // 2 decimales

        $response['total_dias'] = $total_dias;
        $response['segundo_vto_fecha'] = $segundo_vto_fecha;
        $response['fecha_pago'] = $fecha_pago;

        // response
        return $response;
    }

    public function getNroFactura($talonario_id)
    {

        // Usar una transacci�n con lockForUpdate para evitar condiciones de carrera
        return DB::transaction(function () use ($talonario_id) {
            // obtengo el m�ximo nro_factura con bloqueo para esta transacci�n
            $nro_factura = DB::table('facturas')
                ->where('talonario_id', $talonario_id)
                ->lockForUpdate()
                ->max('nro_factura');

            // si no existen facturas busco el nro inicial del talonario
            if (!$nro_factura) {
                $talonario = Talonario::find($talonario_id);
                return $talonario ? $talonario->nro_inicial : 1;
            }

            return $nro_factura + 1;
        });
    }

    // public function getNroCuotasInstalacion($user_id, $servicio_id, $periodo, $alta_servicio, $plan_pago)
    public function getNroCuotasInstalacion($periodo, $alta_servicio)
    {

        // formateo la fecha 
        $fecha_periodo = Carbon::createFromFormat('m/Y', $periodo);

        // formateo la fecha 
        $fecha_alta_servicio = Carbon::createFromFormat('m/Y', Carbon::parse($alta_servicio)->format('m/Y'));

        // response
        return $fecha_periodo->diffInMonths($fecha_alta_servicio);




        // $alta_servicio_periodo = Carbon::parse($alta_servicio_mas_plan_pago)->format('m/Y');

        // $array_alta_servicio_mas_plan_pago = explode('/',$alta_servicio_periodo);
        // $array_periodo                     = explode('/',$periodo);

        // if (($array_alta_servicio_mas_plan_pago[1] < $array_periodo[1]) ||  
        //     ($array_alta_servicio_mas_plan_pago[1] == $array_periodo[1] && $array_alta_servicio_mas_plan_pago[0] < $array_periodo[0]) ) {

        //     $instalacion_cuota = $plan_pago;

        // }else{

        //     $instalacion_cuota = 0;

        //     // obtengo las facturas del cliente
        //     $facturas = Factura::where('user_id', $user_id)->get();
        //     foreach ($facturas as $factura) {

        //         // obtengo el detalle de cada factura
        //         foreach ($factura->detalle as $detalle) {

        //             // verifico el nro de cuota de instalacion que se pago
        //             if ($detalle->servicio_id == $servicio_id && $detalle->instalacion_cuota != null) {

        //                 // $instalacion_cuota = $instalacion_cuota + $detalle->instalacion_cuota;
        //                 $instalacion_cuota++;

        //             }

        //         }        
        //     }
        // }


        // return $instalacion_cuota;

    }
    function getNroCuotasInstalacionPlanPago($user_id, $servicio_id)
    {

        $instalacion_cuota = 0;

        // obtengo las facturas del cliente
        $facturas = Factura::where('user_id', $user_id)
            ->whereNull('deleted_at')
            ->get();

        foreach ($facturas as $factura) {

            // obtengo el detalle de cada factura
            foreach ($factura->detalle as $detalle) {

                // verifico el nro de cuota de instalacion que se pago
                if ($detalle->servicio_id == $servicio_id && $detalle->pp_flag == 1 && $detalle->instalacion_cuota != null) {

                    // $instalacion_cuota = $instalacion_cuota + $detalle->instalacion_cuota;
                    $instalacion_cuota++;
                }
            }
        }

        return $instalacion_cuota;
    }

    public function getProporcional($user_id, $periodo, $alta_servicio, $abono_proporcional)
    {

        // $dt = Carbon::now();
        $alta_servicio = Carbon::parse($alta_servicio);
        $fecha_actual = Carbon::today();
        $proporcional = array('importe' => 0, 'dias' => 0);

        // obtengo las facturas del cliente
        $facturas = Factura::where('user_id', $user_id)
            // ->where('periodo', $periodo)
            ->whereNull('deleted_at')
            ->get();

        if (count($facturas) == 0) {

            $mes_total_dias  = $alta_servicio->daysInMonth;    // total de dias del mes
            $alta_servicio_dia = $alta_servicio->day;            // dia del mes

            $fecha_periodo = Carbon::createFromFormat('m/Y', $periodo);

            if ($fecha_periodo->year == $alta_servicio->year && $fecha_periodo->month == $alta_servicio->month) {

                // tiene que haber al menos 1 dia de diferencia, es decir si se activa el dia 01 se cobra todo el mes.
                if ($alta_servicio_dia > 1) {

                    // calculo del proporcional
                    $proporcional['importe'] = ($mes_total_dias - $alta_servicio_dia + 1) * $abono_proporcional;
                    $proporcional['dias'] = $mes_total_dias - $alta_servicio_dia + 1;
                }
            }
        }

        return $proporcional;
    }

    public function getCodigoPago($importe, $fecha_vto, $cod_cliente, $nro_punto_vta, $nro_factura)
    {

        // // cuit de la empresa
        // $cuit = config('constants.company_cuit');

        // // importe
        // // $importe  = number_format($importe, 2); // 2 decimales
        // $importe  = explode('.', $importe);

        // $importe_entero  = str_pad($importe[0], 6, '0', STR_PAD_LEFT);
        // $importe_decimal = count($importe) == 2 ? str_pad($importe[1], 2, '0', STR_PAD_RIGHT) : '00';

        // // codigo de cliente
        // $cod_cliente = str_pad($cod_cliente, 5, '0', STR_PAD_LEFT);

        // // fecha de vencimiento
        // $dt = Carbon::parse($fecha_vto);
        // $fecha_vto = $dt->year . str_pad($dt->month, 2, '0', STR_PAD_LEFT)  . str_pad($dt->day, 2, '0', STR_PAD_LEFT);

        // // genero el codigo
        // $codigo = $cuit . $importe_entero . $importe_decimal . $fecha_vto . $cod_cliente . $nro_punto_vta . $nro_factura;

        // // obtengo el ultimo digito
        // $digito = $this->getCodigoPagoDigito($codigo);
        // $codigo = $codigo . $digito;


        return '';
    }

    public function getCodigoPagoDigito($codigo)
    {
        // $cadena = $codigo;
        // $sumaP = 0;
        // $sumaI = 0;
        // $j = 0;
        // for ($i = 0; $i < strlen($cadena); $i++) {
        //     if ($j == 0) {

        //         $sumaI = $sumaI + $cadena[$i];
        //         $j = 1;
        //     } else {

        //         $sumaP = $sumaP + $cadena[$i];
        //         $j = 0;
        //     }
        // }

        // $sumaI = $sumaI * 3;
        // $total = $sumaI + $sumaP;

        // $digito = $total % 10;

        return 0;
    }

    // generate facturas PDF
    public function setFacturasPeriodoPDF($periodo, $factura_id = null)
    {

        $facturas = Factura::with(['notaCredito', 'talonario', 'cliente', 'detalle.servicio', 'bonificacionesPuntuales'])->where('periodo', $periodo)->get();
        foreach ($facturas as $factura) {

            // formateo los campos
            $factura->talonario;
            $factura->talonario->nro_punto_vta  = $this->zerofill($factura->talonario->nro_punto_vta, 4);
            $factura->nro_factura               = $this->zerofill($factura->nro_factura);
            $factura->nro_cliente               = $this->zerofill($factura->nro_cliente, 5);

            $factura->fecha_emision     = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
            $factura->primer_vto_fecha  = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
            $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');
            $factura->tercer_vto_fecha  = Carbon::parse($factura->tercer_vto_fecha)->format('d/m/Y');

            // $factura->importe_subtotal      = number_format($factura->importe_subtotal, 2);
            // $factura->importe_bonificacion  = number_format($factura->importe_bonificacion, 2);
            // $factura->importe_total         = number_format($factura->importe_total, 2);
            // $factura->segundo_vto_importe   = number_format($factura->segundo_vto_importe, 2);
            // $factura->tercer_vto_importe    = number_format($factura->tercer_vto_importe, 2);

            // genero el path del pdf de la factura
            $factura->filename = $factura->talonario->nro_punto_vta . '-' . $factura->nro_factura;
            $factura->filePath = public_path(config('constants.folder_facturas') . 'factura-' . $factura->filename . '.pdf');

            // datos del cliente
            $factura->cliente;

            // datos del detalle
            $detalles =  $factura->detalle;
            foreach ($detalles as $detalle) {
                $detalle->servicio;
            }
            // genero los PDF's de las facturas individuales
            if ($factura_id == null || $factura_id == $factura->id) {

                // Primero generar c�digos QR de MercadoPago para cada vencimiento
                $this->generatePaymentQRCodes($factura);

                // Crear PDF (los c�digos QR se obtienen desde la vista usando el m�todo del modelo)
                $pdf = PDF::loadView('pdf.facturas', ['facturas' => [$factura]]);
                $pdf->save($factura->filePath);
            }
        }
        $this->setPeriodoPDF($periodo, $facturas);

        // genero los PDF's de las facturas del periodo
        // $filename = str_replace('/', '-', $periodo);
        // $pdf = PDF::loadView('pdf.facturas', ['facturas' => $facturas]);
        // $pdf->save(config('constants.folder_periodos') . 'periodo-'.$filename.'.pdf');

        // return $facturas;
        return true;
    }

    /**
     * Generar c�digos QR de MercadoPago para una factura
     */
    protected function generatePaymentQRCodes(Factura $factura)
    {
        try {
            Log::info('BillController: Generando QR codes para factura', ['factura_id' => $factura->id]);

            // Cargar relaci�n del cliente si no est� cargada
            if (!$factura->cliente) {
                $factura->load('cliente');
            }

            // Normalizar fechas de vencimiento a objetos Carbon cuando estén como strings (d/m/Y o Y-m-d)
            $normalizeDate = function ($value) use ($factura) {
                if (empty($value)) {
                    return null;
                }
                if ($value instanceof \Carbon\Carbon) {
                    return $value;
                }
                // Intentar parsear "d/m/Y" primero, si falla usar parse()
                try {
                    return Carbon::createFromFormat('d/m/Y', $value);
                } catch (\Exception $e) {
                    try {
                        return Carbon::parse($value);
                    } catch (\Exception $e2) {
                        Log::warning('No se pudo normalizar la fecha', ['value' => $value, 'factura_id' => $factura->id]);
                        return null;
                    }
                }
            };

            if (isset($factura->primer_vto_fecha)) {
                $factura->primer_vto_fecha = $normalizeDate($factura->primer_vto_fecha);
            }
            if (isset($factura->segundo_vto_fecha)) {
                $factura->segundo_vto_fecha = $normalizeDate($factura->segundo_vto_fecha);
            }
            if (isset($factura->tercer_vto_fecha)) {
                $factura->tercer_vto_fecha = $normalizeDate($factura->tercer_vto_fecha);
            }

            // Generar QR para primer vencimiento
            Log::info('BillController: Generando QR primer vencimiento', ['factura_id' => $factura->id]);
            $result1 = $this->paymentQRService->createPaymentQR($factura, 'primer');
            Log::info('BillController: Resultado QR primer vencimiento', [
                'factura_id' => $factura->id,
                'success' => $result1 !== null,
                'type' => $result1 ? get_class($result1) : 'null'
            ]);

            // Generar QR para segundo vencimiento
            Log::info('BillController: Generando QR segundo vencimiento', ['factura_id' => $factura->id]);
            $result2 = $this->paymentQRService->createPaymentQR($factura, 'segundo');
            Log::info('BillController: Resultado QR segundo vencimiento', [
                'factura_id' => $factura->id,
                'success' => $result2 !== null,
                'type' => $result2 ? get_class($result2) : 'null'
            ]);

            // Generar QR para tercer vencimiento (si existe)
            if (!empty($factura->tercer_vto_importe) && $factura->tercer_vto_importe > 0) {
                Log::info('BillController: Generando QR tercer vencimiento', ['factura_id' => $factura->id]);
                $result3 = $this->paymentQRService->createPaymentQR($factura, 'tercer');
                Log::info('BillController: Resultado QR tercer vencimiento', [
                    'factura_id' => $factura->id,
                    'success' => $result3 !== null,
                    'type' => $result3 ? get_class($result3) : 'null'
                ]);
            }

            Log::info('BillController: QR codes generation completed', ['factura_id' => $factura->id]);
        } catch (Exception $e) {
            // Log del error pero no interrumpe el proceso de facturaci�n
            Log::error('Error generando c�digos QR para factura ' . $factura->id . ': ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    // merge PDF facturas
    public function setPeriodoPDF($periodo, $facturas)
    {
        foreach ($facturas as $factura) {
            $filenames[] = $factura->filePath;
        }
        // return $filenames;

        // merge pdf's
        $merger = new Merger;
        $merger->addIterator($filenames);
        $createdPdf = $merger->merge();

        // compongo el nombre
        $filename = str_replace('/', '-', $periodo);
        $filePath = config('constants.folder_periodos') . 'periodo-' . $filename . '.pdf';

        // store pdf
        Storage::disk('public')->put($filePath, $createdPdf);

        return true;
    }

    // Genero el archivo de Pago Mis Cuentas
    public function setPeriodoPMC(Request $request, $mes, $ano)
    {

        // variables
        $i  = 1;
        $ln = "\n";
        $periodo                = $mes . '/' . $ano;
        $facturas               = Factura::where('periodo', $periodo)->whereNull('deleted_at')->get();
        $facturasTotalCantidad  = 0;
        $facturasTotalImporte   = 0;
        $codEmpresa             = '8699';
        $registroDetalle        = '5';
        $pantalla               = '';
        $moneda                 = '0';
        $detalle                = '';

        // get facturas
        foreach ($facturas as $factura) {

            // obtengo el nro de punto de venta y el nro de factura
            $factura->talonario;
            $factura_nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);
            $factura_nro_factura = $this->zerofill($factura->nro_factura);


            // totales
            $facturasTotalCantidad++;
            $facturasTotalImporte = $facturasTotalImporte + $factura->importe_total;

            // nro de cliente 
            $nro_cliente = str_pad($factura->nro_cliente, 5, '0', STR_PAD_LEFT);
            $nro_cliente = str_pad($nro_cliente, 19, ' ', STR_PAD_RIGHT);

            // nro factura 
            $nro_factura = str_pad($factura->nro_factura, 20, '0', STR_PAD_LEFT);

            // fecha de vencimiento
            $dt = Carbon::parse($factura->primer_vto_fecha);
            $primer_vto_fecha = $dt->year . str_pad($dt->month, 2, '0', STR_PAD_LEFT)  . str_pad($dt->day, 2, '0', STR_PAD_LEFT);

            // format importes
            $factura_importe = number_format($factura->importe_total, 2, '.', '');
            $factura_importe = explode('.', $factura_importe);
            $factura_importe_entero  = str_pad($factura_importe[0], 9, '0', STR_PAD_LEFT);
            $factura_importe_decimal = str_pad($factura_importe[1], 2, '0', STR_PAD_RIGHT);

            // complemento
            $ceros   = str_pad(0, 38, '0', STR_PAD_RIGHT);
            $filer1  = str_pad(0, 19, '0', STR_PAD_RIGHT);

            //  mensaje
            $mensaje = 'ABONO MES DE ' . $this->meses[$mes];
            $mensaje = str_pad($mensaje, 40, ' ', STR_PAD_RIGHT);

            // pantalla
            $pantalla = str_pad($pantalla, 15, ' ', STR_PAD_RIGHT);

            // codigo
            $codigo   = $this->getCodigoPago($factura->importe_total, $factura->primer_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);
            $codigo   = str_pad($codigo, 60, ' ', STR_PAD_RIGHT);
            // $codigo   = str_pad($codigo,48,' ',STR_PAD_RIGHT); //60 - 12 que se agregaron

            // ultimo filler
            $filler2  = str_pad(0, 29, '0', STR_PAD_RIGHT);

            $detalle .= $ln . $registroDetalle . $nro_cliente . $nro_factura . $moneda . $primer_vto_fecha .
                $factura_importe_entero . $factura_importe_decimal . $ceros . $filer1 .
                $nro_cliente . $mensaje . $pantalla . $codigo . $filler2;
        }

        // format importes
        $facturasTotalImporte = number_format($facturasTotalImporte, 2, '.', '');
        $facturasTotalImporte = explode('.', $facturasTotalImporte);


        // file header ----------------------------------------------------------------------
        $registroHeader = '0';
        $prismaHeader   = '400';
        $fechaHeader    = date('Ymd');
        $fillerHeader   = str_pad(0, 264, '0', STR_PAD_LEFT);
        $fileHeader     = $registroHeader . $prismaHeader . $codEmpresa . $fechaHeader . $fillerHeader;

        // file footer ----------------------------------------------------------------------
        $registroFooter         = '94008699';
        $facturasTotalCantidad  = str_pad($facturasTotalCantidad, 7, '0', STR_PAD_LEFT);
        $fillerFooter1          = str_pad(0, 7, '0', STR_PAD_RIGHT);
        $importeEntero          = str_pad($facturasTotalImporte[0], 14, '0', STR_PAD_LEFT);
        $importeEnteroDecimal   = str_pad($facturasTotalImporte[1], 2, '0', STR_PAD_RIGHT);
        $fillerFooter2          = str_pad(0, 234, '0', STR_PAD_RIGHT);
        $fileFooter             = $ln . $registroFooter . $fechaHeader . $facturasTotalCantidad . $fillerFooter1 . $importeEntero . $importeEnteroDecimal . $fillerFooter2;

        // file content ----------------------------------------------------------------------
        $fileContent        = $detalle;


        // output
        $output = $fileHeader . $fileContent . $fileFooter;

        // compongo el nombre
        $filename = 'FAC' . $codEmpresa . '.' . date('dmy');
        $filePath = config('constants.folder_pmc') . $filename;

        // store pdf
        Storage::disk('public')->put($filePath, $output);

        // return
        return response()->download($filePath);
    }

    public function getFacturasPeriodoPDFPath($request)
    {
        // path del pdf
        $filename = str_replace('/', '-', $request->periodo);
        $filename = $request->root() . '/' . config('constants.folder_periodos') . 'periodo-' . $filename . '.pdf';

        // retorno el path del pdf
        return $filename;
    }

    public function getFacturaPDFPath($request, $factura)
    {

        // formateo los campos
        $factura->talonario;
        $factura->talonario->nro_punto_vta  = $this->zerofill($factura->talonario->nro_punto_vta, 4);
        $factura->nro_factura               = $this->zerofill($factura->nro_factura);

        // compongo el nombre 
        $filename = $factura->talonario->nro_punto_vta . '-' . $factura->nro_factura;

        // genero el path del pdf
        $filename = $request->root() . '/' . config('constants.folder_facturas') . 'factura-' . $filename . '.pdf';

        // retorno el path del pdf
        return $filename;
    }

    public function getBillSend(Request $request, $id)
    {

        $factura = Factura::find($id);

        if ($factura) {

            // send Email
            $this->sendEmailFactura($request, $factura);
        }

        // return $facturas;
        return back()->with(['status' => 'success', 'message' => 'Se ha enviado la factura al correo electr�nico ' . $factura->cliente->email . '.', 'icon' => 'fa-smile-o']);
    }

    public function sendEmailFacturasPeriodo(Request $request, $mes, $ano)
    {

        $periodo = $mes . '/' . $ano;
        $facturas = Factura::where('periodo', $periodo)->whereNull('deleted_at')->get();

        foreach ($facturas as $factura) {

            // send Email
            $this->sendEmailFactura($request, $factura);
        }

        // return $facturas;
        return redirect('/admin/period')->with(['status' => 'success', 'message' => 'Se han enviado las facturas del per�odo ' . $periodo . '.', 'icon' => 'fa-smile-o']);
    }

    public function sendEmailFactura($request, $factura)
    {

        if ($factura->cliente->email != null && $factura->cliente->email != '') {

            // fecha actual
            $fecha_actual = Carbon::now();

            // formateo los campos
            $factura->talonario;
            $factura->talonario->nro_punto_vta  = $this->zerofill($factura->talonario->nro_punto_vta, 4);
            $factura->nro_factura               = $this->zerofill($factura->nro_factura);
            $factura->nro_cliente               = $this->zerofill($factura->nro_cliente, 5);

            $factura->fecha_emision     = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
            $factura->primer_vto_fecha  = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
            $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');
            $factura->tercer_vto_fecha  = Carbon::parse($factura->tercer_vto_fecha)->format('d/m/Y');

            // $factura->importe_subtotal      = number_format($factura->importe_subtotal, 2);
            // $factura->importe_bonificacion  = number_format($factura->importe_bonificacion, 2);
            // $factura->importe_total         = number_format($factura->importe_total, 2);
            // $factura->segundo_vto_importe   = number_format($factura->segundo_vto_importe, 2);
            // $factura->tercer_vto_importe    = number_format($factura->tercer_vto_importe, 2);

            // datos del cliente
            $factura->cliente;

            // datos del detalle
            $detalles =  $factura->detalle;
            foreach ($detalles as $detalle) {
                $detalle->servicio;
            }

            // Create Token
            $encrypted_id = encrypt($factura->id);
            $factura->download_path = $request->root() . '/invoice/' . $encrypted_id;

            // send Mail
            Mail::send('email.factura', ['factura' => $factura], function ($message) use ($factura) {
                $message->from(config('constants.account_no_reply'), config('constants.title'))
                    ->to($factura->cliente->email)
                    ->subject('Te acercamos tu factura');
            });

            // actualizo los campos del envio de mail
            $factura = Factura::find($factura->id);
            $factura->mail_to   = $factura->cliente->email;
            $factura->mail_date = $fecha_actual;
            $factura->save();
        }
    }

    // vista de facturas / buscar
    public function billSearch(Request $request)
    {

        return View::make('period.list_buscar_facturas');
    }

    // lista de facturas / buscar - AJAX
    public function getBillSearchList(Request $request)
    {

        $fecha_actual = Carbon::today();
        $facturas = Factura::with(['talonario', 'cliente', 'detalle.servicio'])->whereNull('deleted_at')->get();

        foreach ($facturas as $factura) {

            $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);

            $factura->nro_factura = $this->zerofill($factura->nro_factura);
            $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);

            $factura->cliente->nombre_apellido = $factura->cliente->firstname . ' ' . $factura->cliente->lastname;


            // verifico si puede mostrarse el boton de bonificacion
            $factura->fecha_actual = $fecha_actual;
            // $factura->btn_bonificacion = $fecha_actual->lt(Carbon::parse($factura->primer_vto_fecha));
            $factura->btn_bonificacion = true; // cambio pedido por Orne el dia 03-05-2018
            $factura->btn_actualizar = $fecha_actual->gt(Carbon::parse($factura->segundo_vto_fecha));


            $factura->fecha_emision = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
            $factura->primer_vto_fecha = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
            $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');

            $factura->importe_subtotal = number_format($factura->importe_subtotal, 2);
            $factura->importe_bonificacion = number_format($factura->importe_bonificacion, 2);
            $factura->importe_total = number_format($factura->importe_total, 2);

            $factura->fecha_pago = $factura->fecha_pago ? Carbon::parse($factura->fecha_pago)->format('d/m/Y') : null;

            $detalles =  $factura->detalle;

            foreach ($detalles as $detalle) {
                $detalle->servicio;
            }


            // genero los PDF's de las facturas individuales
            $filename = $factura->talonario->nro_punto_vta . '-' . $factura->nro_factura;
            $factura->pdf = $request->root() . '/' . config('constants.folder_facturas') . 'factura-' . $filename . '.pdf';
        }

        // return $facturas;
        return Datatables::of($facturas)->make(true);
    }



    //-------------------------------------BALANCE GENERAL ---------------------------------------------------------//
    public function balance()
    {

        $facturas = Factura::whereNull('deleted_at')->orderBy('id', 'desc')->get();

        $periodos = [];
        foreach ($facturas as $factura) {

            if (!in_array($factura->periodo, $periodos)) {
                $periodos[] = $factura->periodo;
            }
        }

        // return $periodos;

        return View::make('balance.list')->with(['periodos' => $periodos]);
    }

    public function balanceSearch(Request $request)
    {

        // Guardar filtros en sesi�n para usar en descargas posteriores
        $filters = [
            'periodo' => $request->periodo,
            'user_id' => $request->user_id
        ];
        session(['balance_filters' => $filters]);

        // get balance
        $response = $this->getBalance($request);

        if (!is_null($response) && !empty($response)) {

            // generate PDF
            $this->generateBalancePDF($response);

            // generate XLS 
            $this->generateBalanceXLS($response, $filters);

            // return balance
            return $response;
        } else {

            return 'null';
        }
    }

    public function getBalance($request)
    {

        // get facturas
        $facturas = Factura::whereNull('deleted_at')->orderBy('id', 'ASC');
        if ($request->periodo != '') {
            $facturas = $facturas->where('periodo', $request->periodo);
        }

        $cliente_label = 'Todos';
        if ($request->user_id != '') {
            $facturas = $facturas->where('user_id', $request->user_id);
            $cliente = User::find($request->user_id);
            $cliente_label = $cliente->firstname . ' ' . $cliente->lastname;
        }
        $facturas = $facturas->get();
        // ->toSql();

        // return $facturas;


        if (count($facturas)) {

            $facturasArray = [];

            // compongo el resultado
            foreach ($facturas as $factura) {

                $facturasArray[$factura->periodo]['cliente'] = $cliente_label;

                // Facturas | Facturas Pagadas | Facturas Adeudadas | Importe Facturado | Importe Pagado
                $facturasArray[$factura->periodo]['periodo'] = $factura->periodo;
                $facturasArray[$factura->periodo]['facturas'][] = $factura->id;
                $facturasArray[$factura->periodo]['facturas_total'] = count($facturasArray[$factura->periodo]['facturas']);
                $facturasArray[$factura->periodo]['importe_facturado'] = array_key_exists('importe_facturado', $facturasArray[$factura->periodo]) ? $facturasArray[$factura->periodo]['importe_facturado'] + $factura->importe_total : $factura->importe_total;

                if ($factura->fecha_pago != null && $factura->fecha_pago != '') {
                    $facturasArray[$factura->periodo]['facturas_pagadas'] = array_key_exists('facturas_pagadas', $facturasArray[$factura->periodo]) ? $facturasArray[$factura->periodo]['facturas_pagadas'] + 1 : 1;
                    $facturasArray[$factura->periodo]['facturas_adeudadas'] = array_key_exists('facturas_adeudadas', $facturasArray[$factura->periodo]) ? $facturasArray[$factura->periodo]['facturas_adeudadas'] : 0;
                    $facturasArray[$factura->periodo]['importe_pagado'] = array_key_exists('importe_pagado', $facturasArray[$factura->periodo]) ? $facturasArray[$factura->periodo]['importe_pagado'] + $factura->importe_pago : $factura->importe_pago;
                } else {
                    $facturasArray[$factura->periodo]['facturas_pagadas'] = array_key_exists('facturas_pagadas', $facturasArray[$factura->periodo]) ? $facturasArray[$factura->periodo]['facturas_pagadas'] : 0;
                    $facturasArray[$factura->periodo]['facturas_adeudadas'] = array_key_exists('facturas_adeudadas', $facturasArray[$factura->periodo]) ? $facturasArray[$factura->periodo]['facturas_adeudadas'] + 1 : 1;
                    $facturasArray[$factura->periodo]['importe_pagado'] = array_key_exists('importe_pagado', $facturasArray[$factura->periodo]) ? $facturasArray[$factura->periodo]['importe_pagado'] : 0;
                }
            }

            return $facturasArray;
        } else {

            return null;
        }
    }

    public function generateBalancePDF($response)
    {

        if (!is_null($response) && !empty($response)) {

            try {

                $filename = 'Balance de pagos ReDin';

                $pdf = PDF::loadView('pdf.balance', ['response' => $response]);
                $pdf->save(public_path(config('constants.folder_balance_pdf') . $filename . '.pdf'));
                // return $pdf->stream(config('constants.folder_balance_pdf') . $filename . '.pdf');

                return $filename;
            } catch (\Exception $e) {

                return $e;
            }
        } else {

            return null;
        }
    }

    public function getBalancePDF(Request $request)
    {

        try {

            $filename = 'Balance de pagos ReDin';

            $filename = config('constants.folder_balance_pdf') . $filename . ".pdf";

            return response()->file($filename);
        } catch (\Exception $e) {

            return View::make('errors.404');
        }
    }

    public function generateBalanceXLS($response, $filters = [])
    {
        if (!is_null($response) && !empty($response)) {

            try {
                // Crear nombre descriptivo del archivo
                $cliente = '';
                if (!empty($filters) && isset($filters['user_id']) && !empty($filters['user_id'])) {
                    $user = User::find($filters['user_id']);
                    $cliente = $user ? '_' . str_replace(' ', '_', $user->firstname . '_' . $user->lastname) : '';
                }

                $periodo = '';
                if (!empty($filters) && isset($filters['periodo']) && !empty($filters['periodo'])) {
                    $periodo = '_periodo_' . str_replace('/', '_', $filters['periodo']);
                }

                $fecha = date('Y-m-d_H-i-s');
                $filename = 'balance_general' . $cliente . $periodo . '_' . $fecha . '.csv';

                // Asegurar que el directorio existe
                $directory = public_path(config('constants.folder_balance_xls'));
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                // Generar archivo CSV en lugar de Excel
                $filePath = $directory . '/' . $filename;
                $handle = fopen($filePath, 'w');
                
                if ($handle === false) {
                    throw new \Exception('No se pudo crear el archivo CSV');
                }
                
                // Añadir BOM UTF-8 para Excel
                fwrite($handle, "\xEF\xBB\xBF");

                $footer_facturas_total = 0;
                $footer_facturas_pagadas = 0;
                $footer_facturas_adeudadas = 0;
                $footer_importe_facturado = 0;
                $footer_importe_pagado = 0;

                // Encabezados
                fputcsv($handle, [
                    'Período',
                    'Total Facturas',
                    'Facturas Pagadas',
                    'Facturas Adeudadas',
                    'Importe Facturado',
                    'Importe Pagado'
                ], ';');

                // Datos
                foreach ($response as $key => $periodo) {
                    // totales
                    $footer_facturas_total += (int)($periodo['facturas_total'] ?? 0);
                    $footer_facturas_pagadas += (int)($periodo['facturas_pagadas'] ?? 0);
                    $footer_facturas_adeudadas += (int)($periodo['facturas_adeudadas'] ?? 0);
                    $footer_importe_facturado += (float)($periodo['importe_facturado'] ?? 0);
                    $footer_importe_pagado += (float)($periodo['importe_pagado'] ?? 0);

                    fputcsv($handle, [
                        $periodo['periodo'] ?? '',
                        $periodo['facturas_total'] ?? '0',
                        $periodo['facturas_pagadas'] ?? '0',
                        $periodo['facturas_adeudadas'] ?? '0',
                        number_format($periodo['importe_facturado'] ?? 0, 2, ',', '.'),
                        number_format($periodo['importe_pagado'] ?? 0, 2, ',', '.')
                    ], ';');
                }

                // Totales
                fputcsv($handle, [
                    'Totales',
                    $footer_facturas_total,
                    $footer_facturas_pagadas,
                    $footer_facturas_adeudadas,
                    number_format($footer_importe_facturado, 2, ',', '.'),
                    number_format($footer_importe_pagado, 2, ',', '.')
                ], ';');

                fclose($handle);

                // Retornar el nombre del archivo
                return $filename;
            } catch (\Exception $e) {
                \Log::error('Error generando CSV: ' . $e->getMessage());
                return null;
            }
        }
        return null;
    }

    public function getBalanceXLS(Request $request)
    {
        try {
            // Usar filtros de la sesi�n si existen, sino generar balance general
            $filters = session('balance_filters', []);

            if (!empty($filters)) {
                // Crear request con filtros de sesi�n manteniendo el per�odo seleccionado
                $searchRequest = new Request([
                    'periodo' => isset($filters['periodo']) ? $filters['periodo'] : '',
                    'user_id' => isset($filters['user_id']) ? $filters['user_id'] : ''
                ]);
                $response = $this->getBalance($searchRequest);

                if (!is_null($response) && !empty($response)) {
                    $filename = $this->generateBalanceXLS($response, $filters);
                }
            } else {
                // Si no hay filtros en sesi�n, generar balance general de todos los per�odos
                $searchRequest = new Request(['periodo' => '', 'user_id' => '']);
                $response = $this->getBalance($searchRequest);

                if (!is_null($response) && !empty($response)) {
                    $filename = $this->generateBalanceXLS($response);
                }
            }

            if (!isset($filename) || !$filename) {
                \Log::error('No se pudo generar el archivo Excel de balance');
                return View::make('errors.404');
            }

            $filePath = public_path(config('constants.folder_balance_xls') . $filename);

            // Verificar que el archivo existe antes de intentar descargarlo
            if (!file_exists($filePath)) {
                \Log::error('Archivo CSV no encontrado: ' . $filePath);
                return View::make('errors.404');
            }

            // Establecer headers para CSV
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . basename($filename) . '"',
            ];

            return response()->download($filePath, basename($filename), $headers)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error('Error en getBalanceXLS: ' . $e->getMessage());
            return View::make('errors.404');
        }
    }

    //-------------------------------------BALANCE DETALLE ---------------------------------------------------------//
    public function balanceDetalle()
    {

        return View::make('balance.detalle');
    }


    public function balanceDetalleSearch(Request $request)
    {

        // get balance
        $response = $this->getBalanceDetalle($request);

        if (!is_null($response) && !empty($response)) {

            // generate PDF
            $this->generateBalanceDetallePDF($response);

            // generate XLS 
            $this->generateBalanceDetalleXLS($response);

            // return balance
            return $response;
        } else {

            return 'null';
        }
    }

    public function getBalanceDetalle($request)
    {

        // set default date
        $date_from_parsed  = $request->date_from != '' ? $request->date_from : '01/01/1900';
        $date_to_parsed    = $request->date_to != '' ? $request->date_to : date('d/m/Y');

        // parse date
        $date_from = Carbon::createFromFormat('d/m/Y H:i:s', $date_from_parsed . '00:00:00');
        $date_to = Carbon::createFromFormat('d/m/Y H:i:s', $date_to_parsed . '23:59:59');

        // get facturas
        $facturas = Factura::whereNull('deleted_at')->whereBetween('fecha_emision', [$date_from, $date_to]);

        if ($request->user_id != '') {
            $facturas = $facturas->where('user_id', $request->user_id);
        }

        $facturas = $facturas->orderBy('user_id', 'ASC')
            ->orderBy('id', 'DESC')
            ->get();
        // ->toSql();

        if (count($facturas)) {

            $facturasArray = [];

            // compongo el resultado
            foreach ($facturas as $factura) {

                // get talonario
                $factura->talonario;
                $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);

                // fill values
                $factura->nro_factura = $this->zerofill($factura->nro_factura);
                $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);

                // get cliente
                $factura->cliente;
                $factura->cliente->nombre_apellido = $factura->cliente->firstname . ' ' . $factura->cliente->lastname;

                // parse fecha de emision
                $factura->fecha_emision = Carbon::parse($factura->fecha_emision)->format('d/m/Y');

                // parse importe
                $factura->importe_total = number_format($factura->importe_total, 2);

                // pago
                $factura->fecha_pago = $factura->fecha_pago ? Carbon::parse($factura->fecha_pago)->format('d/m/Y') : '';
                $factura->importe_pago = $factura->importe_pago ? number_format($factura->importe_pago, 2) : '';
                $factura->forma_pago = $factura->fecha_pago ? $this->forma_pago[$factura->forma_pago] : '';

                $facturasArray[$factura->nro_cliente][] = $factura;
            }

            return $facturasArray;
        } else {

            return null;
        }
    }

    public function generateBalanceDetallePDF($response)
    {

        if (!is_null($response) && !empty($response)) {

            try {

                $filename = 'balance_detalle';

                $pdf = PDF::loadView('pdf.balance_detalle', ['response' => $response]);
                $pdf->save(config('constants.folder_balance_detalle_pdf') . $filename . '.pdf');
                // $pdf->stream(config('constants.folder_balance_detalle_pdf') . $filename . '.pdf');

                return $filename;
            } catch (\Exception $e) {

                return $e;
            }
        } else {

            return null;
        }
    }

    public function generateBalanceDetalleXLS($response)
    {

        if (!is_null($response) && !empty($response)) {

            try {

                $filename = 'balance_detalle';

                \Excel::create($filename, function ($excel) use ($response) {

                    $excel->sheet('Balance Detalle', function ($sheet) use ($response) {

                        // add headers
                        $sheet->appendRow(array(
                            'Cod. Cliente',
                            'Nombre y Apellido',
                            'Per�odo',
                            'Factura',
                            'Fecha de Emisi�n',
                            'Importe Facturado',
                            'Fecha de Pago',
                            'Importe Pagado',
                            'Medio de Pago',
                            'Importe Adeudado'
                        ));

                        // ksort($response);
                        foreach ($response as $key => $users):

                            $result = '';
                            $total_importe_facturado = 0;
                            $total_importe_pagado = 0;
                            $total_importe_adeudado = 0;

                            $user = $users[0];

                            foreach ($users as $key => $factura):

                                if ($factura['importe_pago'] != '') {

                                    $class_tr =  '';
                                    $importe_pago = $factura['importe_pago'];
                                    $importe_adeudado = 0;
                                } else {

                                    $class_tr =  'debe';
                                    $importe_pago = 0;
                                    $total_importe_adeudado = (float)$total_importe_adeudado + (float)$factura['importe_total'];
                                    $importe_adeudado = $factura['importe_total'];
                                }

                                $factura['importe_total'] = str_replace(',', '', $factura['importe_total']);
                                $factura['importe_pago'] = str_replace(',', '', $factura['importe_pago']);
                                $importe_adeudado = str_replace(',', '', $importe_adeudado);

                                // totalizo las facturas y los pagos
                                $total_importe_facturado = (float)$total_importe_facturado + (float)$factura['importe_total'];
                                $total_importe_pagado = (float)$total_importe_pagado + (float)$importe_pago;

                                // agrego la linea al xls
                                $sheet->appendRow(array(
                                    $user['nro_cliente'],
                                    $user['cliente']['nombre_apellido'],
                                    $factura['periodo'],
                                    $factura['talonario']['letra'] . ' ' . $factura['talonario']['nro_punto_vta'] . ' - ' . $factura['nro_factura'],
                                    $factura['fecha_emision'],
                                    $factura['importe_total'],
                                    $factura['fecha_pago'],
                                    $factura['importe_pago'],
                                    $factura['forma_pago'],
                                    $importe_adeudado > 0 ? $importe_adeudado : '',
                                ));

                            endforeach;

                        // add total de pagos del cliente
                        // $sheet->appendRow(array(
                        //     'Total',
                        //     '',
                        //     '',
                        //     '',
                        //     '',
                        //     $total_importe_facturado,
                        //     '',
                        //     $total_importe_pagado,
                        //     '',
                        //     $total_importe_adeudado
                        // )); 

                        endforeach;
                    });
                })
                    ->store('xls', config('constants.folder_balance_detalle_xls'));
                // ->export('xls');

                return $filename;
            } catch (\Exception $e) {

                return $e;
            }
        }
    }

    public function getBalanceDetallePDF(Request $request)
    {

        try {

            $filename = 'balance_detalle';

            $filename = config('constants.folder_balance_detalle_pdf') . $filename . ".pdf";

            return response()->file($filename);
        } catch (\Exception $e) {

            return View::make('errors.404');
        }
    }

    public function getBalanceDetalleXLS(Request $request)
    {
        try {
            $filename = 'balance_detalle';
            $filePath = public_path(config('constants.folder_balance_detalle_xls') . $filename . ".xls");

            // Verificar que el archivo existe antes de intentar descargarlo
            if (!file_exists($filePath)) {
                \Log::error('Archivo Excel de balance detalle no encontrado: ' . $filePath);
                return View::make('errors.404');
            }

            return response()->download($filePath);
        } catch (\Exception $e) {
            \Log::error('Error en getBalanceDetalleXLS: ' . $e->getMessage());
            return View::make('errors.404');
        }
    }


    //-------------------------------------FACTURA SIMPLE ---------------------------------------------------------//
    public function billSingle()
    {

        // Obtengo todos los periodos facturados (sin duplicados) ordenados desc
        $periodos = Factura::orderBy('periodo', 'desc')->pluck('periodo')->unique()->values();

        return View::make('bill_single.create')->with(['periodo' => $periodos]);
    }

    // facturo el periodo
    public function billSingleStore(Request $request)
    {
        // ver
        // https://laravel.io/forum/09-16-2014-validator-greater-than-other-field

        // return $request->all();

        //-- VALIDATOR START --//
        $rules = array(
            'fecha_emision' => 'required|date_format:d/m/Y',
            'periodo'       => 'required|date_format:m/Y',
            'user_id'       => 'required|numeric|exists:users,id',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        // For work edit file : \vendor\zizaco\entrust\src\Entrust\Traits\EntrustRoleTrait.php #52
        // Obtener el usuario seleccionado (aseguramos procesar solo uno)
        $user = User::find($request->user_id);
        if (!$user) {
            return back()->withInput()->with(['status' => 'danger', 'message' => 'Cliente no encontrado.', 'icon' => 'fa-frown-o']);
        }

        $users = collect([$user]);
        // Obtengo los servicios activos del cliente
        if (count($users)) {
            // inicializo el array de items para evitar que contenga datos previos
            $items = [];

            // obtengo la conf. de intereses 
            $interes = Interes::find(1);
            $dt = Carbon::now();

            // ---------------------------------------------------------------------------------------------------------------           

            // foreach ($users as $user) {
            //     $user->servicios;
            //     foreach ($user->servicios as $servicio) {
            //         $servicio->alta_servicio_periodo     = Carbon::parse($servicio->alta_servicio)->format('m/Y');
            //         $servicio->periodo = $request->periodo;
            //         // $servicio->alta_servicio_periodo_facturado_date = Carbon::parse(Carbon::createFromFormat('m/Y', $request->periodo))->format('m/Y');
            //         $servicio->getIfBillable = $this->getIfBillable($request->periodo, $servicio->alta_servicio_periodo);

            //     }
            // }

            // return $users;

            foreach ($users as $user) {

                if ($user->status == 1) {

                    foreach ($user->servicios as $servicio) {

                        // verify if es billable
                        $alta_servicio_periodo = Carbon::parse($servicio->alta_servicio)->format('m/Y');

                        // control para saber si es un servicio o un plan de pago
                        if ($servicio->pp_flag == 1) {
                            $ifBillable = 1;
                            $servicio->plan_pago = $servicio->plan_pago;
                        } else {
                            $ifBillable = $this->getIfBillable($request->periodo, $alta_servicio_periodo);
                            $servicio->plan_pago = $this->getPlanPagoValue($servicio->plan_pago);
                        }
                        // return $ifBillable ? 'si,' : 'no,';

                        // facturo solo los servicios activos y que fueron contratados a partir del periodo dado
                        // if ($servicio->status == 1 && $alta_servicio_periodo == $request->periodo) {
                        if ($servicio->status == 1 && $ifBillable) {

                            // costo de instalacion ---------------------------------------------------------
                            if ($servicio->pp_flag == 1) {

                                $servicio->costo_abono_pagar = 0;
                                $servicio->costo_instalacion_cuotas_pagas = $this->getNroCuotasInstalacionPlanPago($user->id, $servicio->servicio_id);
                                $servicio->costo_instalacion_importe_pagar = $servicio->abono_mensual;
                            } else {

                                // proporcional  ---------------------------------------------------------------
                                $proporcional = $this->getProporcional($user->id, $request->periodo, $servicio->alta_servicio, $servicio->abono_proporcional);
                                $servicio->costo_proporcional_importe = $proporcional['importe'];
                                $servicio->costo_proporcional_dias = $proporcional['dias'];

                                if ($servicio->costo_proporcional_importe > 0) {
                                    $servicio->costo_abono_pagar = 0;
                                } else {
                                    $servicio->costo_abono_pagar = $servicio->abono_mensual;
                                }

                                // obtengo la cantidad de cuotas pagas del servicio
                                // $servicio->costo_instalacion_cuotas_pagas = $this->getNroCuotasInstalacion($user->id, $servicio->servicio_id, $request->periodo, $servicio->alta_servicio, $this->getPlanPagoValue($servicio->plan_pago));
                                $servicio->costo_instalacion_cuotas_pagas = $this->getNroCuotasInstalacion($request->periodo, $servicio->alta_servicio);

                                // asigno el importe a pagar de instalacion si aun se deben cuptas
                                if ($servicio->costo_instalacion_cuotas_pagas < $servicio->plan_pago) {
                                    $servicio->costo_instalacion_importe_pagar = $servicio->costo_instalacion / $servicio->plan_pago;
                                } else {
                                    $servicio->costo_instalacion_importe_pagar = 0;
                                }
                            }

                            // asigno las variables ---------------------------------------------------------
                            $items[$user->id]['cliente'] = $user;
                            $items[$user->id]['servicios_activos'][] = $servicio;

                            // agrego los atributos de calle y altura para el ordenamiento
                            $items[$user->id]['domicilio'] = strtolower($user->calle . ' ' . $user->altura);
                        }
                    }
                }
            }
            // if (count($items['cliente']) == 1) {
            //     $this->singleBillPDF($items);
            // }
            // ---------------------------------------------------------------------------------------------------------------           
            // return $items;
            // ordeno los items por el atributo "domicilio"
            usort($items, array($this, "cmp_obj"));

            // Genero las Facturas
            foreach ($items as $item) {

                // calculo de importes con bonificaciones aplicadas
                $subtotal = 0;
                $bonificacion_total = 0;
                $fecha_facturacion = Carbon::createFromFormat('d/m/Y', $request->fecha_emision);

                foreach ($item['servicios_activos'] as $servicio) {
                    $importe_servicio = $servicio->costo_proporcional_importe + $servicio->costo_abono_pagar + $servicio->costo_instalacion_importe_pagar;

                    // Verificar si existe bonificaci�n vigente para este servicio
                    $bonificacion = BonificacionServicio::where('service_id', $servicio->servicio_id)
                        ->where('activo', true)
                        ->whereRaw('fecha_inicio <= ?', [$fecha_facturacion])
                        ->whereRaw('DATE_ADD(fecha_inicio, INTERVAL periodos_bonificacion MONTH) > ?', [$fecha_facturacion])
                        ->first();

                    if ($bonificacion) {
                        $descuento_servicio = $bonificacion->calcularBonificacion($importe_servicio);
                        $bonificacion_total += $descuento_servicio;
                        // Guardar informaci�n de bonificaci�n en el servicio para el detalle
                        $servicio->bonificacion_aplicada = $descuento_servicio;
                        $servicio->bonificacion_id = $bonificacion->id;
                        $servicio->bonificacion_detalle = $bonificacion->descripcion ? $bonificacion->descripcion : 'Bonificacion aplicada al servicio';
                        $servicio->bonificacion_porcentaje = $bonificacion->porcentaje_bonificacion;
                        $servicio->iva_bonificacion = $descuento_servicio * 0.21;
                    } else {
                        $servicio->bonificacion_aplicada = 0;
                        $servicio->bonificacion_id = null;
                        $servicio->bonificacion_porcentaje = 0;
                    }

                    $subtotal += $importe_servicio;
                }
                //Calculo de IVA
                $iva_subtotal                      = ($subtotal) * 0.21;
                $iva_bonificacion                  = ($bonificacion_total) * 0.21;
                $iva                               = ($subtotal - $bonificacion_total) * 0.21;
                // Cabecera
                $factura = new Factura;
                $factura->user_id                  = $item['cliente']->id;
                $factura->nro_cliente              = $item['cliente']->nro_cliente;
                $factura->talonario_id             = $item['cliente']->talonario_id;
                $factura->nro_factura              = $this->getNroFactura($item['cliente']->talonario_id);
                $factura->periodo                  = $request->periodo;
                $factura->fecha_emision            = Carbon::createFromFormat('d/m/Y', $request->fecha_emision);
                $factura->importe_subtotal         = $this->floatvalue(number_format($subtotal, 2));
                $factura->importe_subtotal_iva     = $this->floatvalue(number_format($iva_subtotal, 2));
                $factura->importe_bonificacion     = $this->floatvalue(number_format($bonificacion_total, 2));
                $factura->importe_bonificacion_iva = $this->floatvalue(number_format($iva_bonificacion, 2));
                $factura->importe_total            = $this->floatvalue(number_format($subtotal - $bonificacion_total, 2));
                $factura->importe_iva              = $this->floatvalue(number_format($iva, 2));


                $mes_periodo = substr($request->periodo, 0, 2);
                $ano_periodo = substr($request->periodo, 3, 4);

                // obtengo la fecha de vencimiento del mes siguiente ya que se factura a mes atrasado
                $periodo_actual    = Carbon::createFromFormat('m/Y', $factura->periodo);
                $periodo_siguiente = $periodo_actual->addMonth();
                $ano_periodo_siguiente = substr($periodo_siguiente, 0, 4);
                $mes_periodo_siguiente = substr($periodo_siguiente, 5, 2);
                // return $periodo_siguiente.'<br>'.$mes_periodo_siguiente.'<br>'.$ano_periodo_siguiente;


                // obtengo el nro de punto de venta y el nro de factura
                $talonario = Talonario::find($factura->talonario_id);
                $factura_nro_punto_vta  =  $this->zerofill($talonario->nro_punto_vta, 4);
                $factura_nro_factura = $this->zerofill($factura->nro_factura);


                // ----------------------------------------------------
                $factura->primer_vto_fecha      = Carbon::createFromFormat('d/m/Y', $interes->primer_vto_dia . '/' . $mes_periodo_siguiente . '/' . $ano_periodo_siguiente);
                $factura->primer_vto_codigo     = $this->getCodigoPago($factura->importe_total, $factura->primer_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);

                // ----------------------------------------------------                
                $factura->segundo_vto_fecha     = Carbon::createFromFormat('d/m/Y', $interes->segundo_vto_dia . '/' . $mes_periodo_siguiente . '/' . $ano_periodo_siguiente);
                $factura->segundo_vto_tasa      = $interes->segundo_vto_tasa;
                $factura->segundo_vto_importe   = $this->getImporteConTasaInteres($factura->importe_total, $interes->segundo_vto_tasa);
                $factura->segundo_vto_codigo    = $this->getCodigoPago($factura->segundo_vto_importe, $factura->segundo_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);
                // ----------------------------------------------------

                $factura->tercer_vto_tasa       = $interes->tercer_vto_tasa;

                // guardo la factura
                if ($factura->save()) {

                    // Emitir factura en AFIP
                    try {
                        if ($talonario->letra == 'A') {
                            $afipResponse = $this->afipService->facturaA(
                                $talonario->nro_punto_vta,
                                $item['cliente']->dni,
                                $factura->importe_total
                            );
                        } else {
                            $afipResponse = $this->afipService->facturaB(
                                $talonario->nro_punto_vta,
                                $factura->importe_total
                            );
                        }

                        Log::info('Respuesta AFIP factura simple', $afipResponse);

                        // Actualizar factura con datos de AFIP
                        if (isset($afipResponse['CAE']) && !empty($afipResponse['CAE'])) {
                            $factura->cae = $afipResponse['CAE'];
                            try {
                                if (isset($afipResponse['CAEFchVto'])) {
                                    $caeVto = $afipResponse['CAEFchVto'];
                                    // AFIP puede devolver en formato Ymd (20260121) o Y-m-d (2026-01-23)
                                    if (strlen($caeVto) == 8 && is_numeric($caeVto)) {
                                        $factura->cae_vto = Carbon::createFromFormat('Ymd', $caeVto);
                                    } else {
                                        $factura->cae_vto = Carbon::parse($caeVto);
                                    }
                                } else {
                                    $factura->cae_vto = null;
                                }
                            } catch (\Exception $e) {
                                Log::error('Error parsing CAEFchVto en factura simple: ' . $e->getMessage(), ['cae_vto' => $afipResponse['CAEFchVto'] ?? 'N/A']);
                                $factura->cae_vto = null;
                            }
                            $factura->save();
                            Log::info('Factura simple actualizada con datos AFIP', ['factura_id' => $factura->id, 'cae' => $factura->cae]);
                        } else {
                            Log::warning('AFIP no devolvi� CAE v�lido para factura simple', ['factura_id' => $factura->id, 'afip_response' => $afipResponse]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error al emitir factura simple en AFIP: ' . $e->getMessage());
                        // No interrumpe el proceso, la factura se guarda sin CAE
                    }

                    // Generar c�digos QR con el nuevo importe
                    $this->generatePaymentQRCodes($factura);

                    // Detalle
                    foreach ($item['servicios_activos'] as $servicio) {

                        $factura_detalle = new FacturaDetalle;
                        $factura_detalle->factura_id = $factura->id;
                        $factura_detalle->servicio_id = $servicio->servicio_id;
                        $factura_detalle->abono_mensual = $servicio->abono_mensual;

                        // proporcional 
                        $factura_detalle->abono_proporcional = $servicio->costo_proporcional_importe > 0 ? $servicio->costo_proporcional_importe : null;
                        $factura_detalle->dias_proporcional = $servicio->costo_proporcional_dias > 0 ? $servicio->costo_proporcional_dias : null;

                        $factura_detalle->instalacion_plan_pago = $servicio->plan_pago;
                        $factura_detalle->pp_flag = $servicio->pp_flag;

                        if ($servicio->pp_flag == 1) {
                            $factura_detalle->costo_instalacion = $servicio->abono_mensual;
                            $factura_detalle->instalacion_cuota = $servicio->costo_instalacion_cuotas_pagas + 1;
                        } else {
                            $factura_detalle->costo_instalacion = $servicio->costo_instalacion_importe_pagar > 0 ? $servicio->costo_instalacion_importe_pagar : null;
                            $factura_detalle->instalacion_cuota = $servicio->costo_instalacion_importe_pagar > 0 ? $servicio->costo_instalacion_cuotas_pagas + 1 : null;
                        }

                        if ($servicio->bonificacion_id != null) {
                            $factura_detalle->iva_bonificacion = $servicio->iva_bonificacion;
                            $factura_detalle->importe_bonificacion = $servicio->bonificacion_aplicada;
                            $factura_detalle->bonificacion_detalle = $servicio->bonificacion_detalle;
                        }
                        // Calcular el monto de IVA para este servicio
                        $importe_servicio = 0;
                        if ($servicio->costo_proporcional_importe > 0) {
                            $importe_servicio += $servicio->costo_proporcional_importe;
                        }
                        if ($servicio->costo_abono_pagar > 0) {
                            $importe_servicio += $servicio->costo_abono_pagar;
                        }
                        if ($servicio->costo_instalacion_importe_pagar > 0) {
                            $importe_servicio += $servicio->costo_instalacion_importe_pagar;
                        }
                        // Restar bonificaci�n aplicada si existe
                        if (isset($servicio->bonificacion_aplicada) && $servicio->bonificacion_aplicada > 0) {
                            $importe_servicio -= $servicio->bonificacion_aplicada;
                        }
                        // El IVA se calcula solo si el importe es mayor a cero
                        $factura_detalle->importe_iva = $importe_servicio > 0 ? round($importe_servicio * 0.21, 2) : 0;

                        // guardo el detalle de la factura
                        $factura_detalle->save();
                    }
                }

                // agrego las facturas al array general
                $facturas[] = $factura;
            }

            // debug
            // return $facturas;
            // return $facturas = Factura::where('periodo', $request->periodo)->get();
            $this->singleBillPDF($factura);
            // Env�o autom�tico de emails para todas las facturas creadas
            foreach ($facturas as $factura) {
                try {
                    $this->sendEmailFactura($request, $factura);
                    Log::info("Email enviado autom�ticamente para factura individual ID: {$factura->id}, cliente: {$factura->cliente->email}");
                } catch (Exception $e) {
                    // Log del error pero no interrumpir el proceso
                    Log::error("Error enviando email autom�tico para factura individual ID: {$factura->id}. Error: " . $e->getMessage());
                }
            }

            // genero los pdf's del periodo facturados
            //$this->setFacturasPeriodoPDF($request->periodo); // SEGUIR SEGUIR SEGUIR SEGUIR (PASAR $request)
            $filename = $this->getFacturaPDFPath($request, $factura);
            // return redirect('/admin/period')->with(['status' => 'success', 'message' => 'El per�odo '.$request->periodo.' fu� facturado.', 'icon' => 'fa-smile-o', 'filename' => $filename]);

            return redirect('/admin/bills/single')->with(['status' => 'success', 'message' => 'Se ha generado la factura ' . $factura->talonario->letra . ' ' . $factura->talonario->nro_punto_vta . ' - ' . $factura->nro_factura . '.', 'icon' => 'fa-smile-o', 'filename' => $filename, 'factura_id' => $factura->id]);
        } else {

            return redirect('/admin/period')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        }
    }


    private function singleBillPDF($factura)
    {
        // formateo los campos
        $factura->talonario;
        $factura->talonario->nro_punto_vta  = $this->zerofill($factura->talonario->nro_punto_vta, 4);
        $factura->nro_factura               = $this->zerofill($factura->nro_factura);
        $factura->nro_cliente               = $this->zerofill($factura->nro_cliente, 5);

        $factura->fecha_emision     = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
        $factura->primer_vto_fecha  = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
        $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');
        $factura->tercer_vto_fecha  = Carbon::parse($factura->tercer_vto_fecha)->format('d/m/Y');

        // $factura->importe_subtotal      = number_format($factura->importe_subtotal, 2);
        // $factura->importe_bonificacion  = number_format($factura->importe_bonificacion, 2);
        // $factura->importe_total         = number_format($factura->importe_total, 2);
        // $factura->segundo_vto_importe   = number_format($factura->segundo_vto_importe, 2);
        // $factura->tercer_vto_importe    = number_format($factura->tercer_vto_importe, 2);

        // genero el path del pdf de la factura
        $factura->filename = $factura->talonario->nro_punto_vta . '-' . $factura->nro_factura;
        $factura->filePath = public_path(config('constants.folder_facturas') . 'factura-' . $factura->filename . '.pdf');

        // datos del cliente
        $factura->cliente;

        // datos del detalle
        $detalles =  $factura->detalle;
        foreach ($detalles as $detalle) {
            $detalle->servicio;
        }


        // Primero generar c�digos QR de MercadoPago para cada vencimiento
        $this->generatePaymentQRCodes($factura);

        // Crear PDF (los c�digos QR se obtienen desde la vista usando el m�todo del modelo)
        $pdf = PDF::loadView('pdf.facturas', ['facturas' => [$factura]]);
        $pdf->save($factura->filePath);

        //$this->setPeriodoPDF($factura->periodo, collect($factura));

        // genero los PDF's de las facturas del periodo
        // $filename = str_replace('/', '-', $periodo);
        // $pdf = PDF::loadView('pdf.facturas', ['facturas' => $facturas]);
        // $pdf->save(config('constants.folder_periodos') . 'periodo-'.$filename.'.pdf');

        // return $facturas;
    }

    //----------------------------------------------------------------------------------------------//

    // email temp
    public function tempFacturasEmail(Request $request)
    {

        $factura = Factura::find(45);

        // formateo los campos
        $factura->talonario;
        $factura->talonario->nro_punto_vta  = $this->zerofill($factura->talonario->nro_punto_vta, 4);
        $factura->nro_factura               = $this->zerofill($factura->nro_factura);
        $factura->nro_cliente               = $this->zerofill($factura->nro_cliente, 5);

        $factura->fecha_emision     = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
        $factura->primer_vto_fecha  = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
        $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');
        $factura->tercer_vto_fecha  = Carbon::parse($factura->tercer_vto_fecha)->format('d/m/Y');

        $factura->importe_subtotal      = number_format($factura->importe_subtotal, 2);
        $factura->importe_bonificacion  = number_format($factura->importe_bonificacion, 2);
        $factura->importe_total         = number_format($factura->importe_total, 2);
        $factura->segundo_vto_importe   = number_format($factura->segundo_vto_importe, 2);
        $factura->tercer_vto_importe    = number_format($factura->tercer_vto_importe, 2);

        // datos del cliente
        $factura->cliente;

        // datos del detalle
        $detalles =  $factura->detalle;
        foreach ($detalles as $detalle) {
            $detalle->servicio;
        }


        // Create Token
        $encrypted_id = encrypt($factura->id);
        $factura->download_path = $request->root() . '/invoice/' . $encrypted_id;

        return View::make('email.factura')->with(['factura' => $factura]);
    }

    // pdf temp
    public function tempFacturasPDF(Request $request)
    {

        $facturas = Factura::with(['talonario', 'cliente', 'detalle.servicio'])->get();
        foreach ($facturas as $factura) {
            $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);

            $factura->nro_factura = $this->zerofill($factura->nro_factura);
            $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);

            $factura->fecha_emision = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
            $factura->primer_vto_fecha = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
            $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');

            $factura->importe_subtotal = number_format($factura->importe_subtotal, 2);
            $factura->importe_bonificacion = number_format($factura->importe_bonificacion, 2);
            $factura->importe_total = number_format($factura->importe_total, 2);

            $detalles =  $factura->detalle;

            foreach ($detalles as $detalle) {
                $detalle->servicio;
            }
        }
        // return $facturas;

        // return View::make('pdf.facturas')->with(['facturas' => $facturas]);

        $pdf = PDF::loadView('pdf.facturas', ['facturas' => $facturas]);
        return $pdf->stream();

        // $pdf->save(config('constants.folderFacturas') . 'facturas.pdf');
        // return true;

    }

    // merge pdf temp
    function tempMergePDF(Request $request)
    {

        $periodo = '03/2018';
        // $facturas = Factura::where('periodo', $periodo)->get();

        // foreach ($facturas as $factura) {
        //     // $filenames[] = $this->getFacturaPDFPath($request, $factura);

        //     $factura->talonario;        
        //     $factura->talonario->nro_punto_vta  = $this->zerofill($factura->talonario->nro_punto_vta, 4);
        //     $factura->nro_factura               = $this->zerofill($factura->nro_factura);

        //     // compongo el nombre 
        //     $filename = $factura->talonario->nro_punto_vta.'-'.$factura->nro_factura;

        //     // genero el path del pdf
        //     $filenames[] = config('constants.folder_facturas') . 'factura-'.$filename.'.pdf';



        // }

        // return $filenames;

        $filenames = ["pdf/factura-0001-00000001.pdf", "pdf/factura-0001-00000002.pdf"];

        $merger = new Merger;
        $merger->addIterator($filenames);
        $createdPdf = $merger->merge();

        $filename = str_replace('/', '-', $periodo);
        $filePath = config('constants.folder_periodos') . 'periodo-' . $filename . '.pdf';
        Storage::disk('public')->put($filePath, $createdPdf);

        return 1;
    }

    public function getPlanPagoValue($id)
    {

        // get cuota object
        $cuota = Cuota::find($id);
        return $cuota->numero;
    }

    /**
     * Aplicar bonificaciones a una factura existente
     * 
     * @param Factura $factura
     * @param Carbon $fecha_facturacion
     * @return array
     */
    private function aplicarBonificacionesFactura($factura, $fecha_facturacion = null)
    {
        if (!$fecha_facturacion) {
            $fecha_facturacion = Carbon::now();
        }

        $bonificacion_total = 0;
        $detalles_bonificados = [];

        // Obtener los detalles de la factura
        $detalles = $factura->detalle;

        foreach ($detalles as $detalle) {
            // Buscar bonificaci�n vigente para este servicio
            $bonificacion = BonificacionServicio::where('service_id', $detalle->servicio_id)
                ->vigentes($fecha_facturacion)
                ->first();

            if ($bonificacion) {
                // Calcular el importe del servicio
                $importe_servicio = 0;

                if ($detalle->abono_proporcional) {
                    $importe_servicio += $detalle->abono_proporcional;
                } else {
                    $importe_servicio += $detalle->abono_mensual;
                }

                if ($detalle->costo_instalacion) {
                    $importe_servicio += $detalle->costo_instalacion;
                }

                // Aplicar bonificaci�n
                $descuento = $bonificacion->calcularBonificacion($importe_servicio);
                $bonificacion_total += $descuento;

                $detalles_bonificados[] = [
                    'detalle_id' => $detalle->id,
                    'servicio_id' => $detalle->servicio_id,
                    'bonificacion_id' => $bonificacion->id,
                    'importe_original' => $importe_servicio,
                    'descuento_aplicado' => $descuento,
                    'porcentaje' => $bonificacion->porcentaje_bonificacion
                ];
            }
        }

        return [
            'bonificacion_total' => $bonificacion_total,
            'detalles' => $detalles_bonificados
        ];
    }

    /**
     * Regenerar PDF de facturas - admite ID individual o array de IDs
     * Endpoint para uso sin SSH - regenera el PDF tal como lo haría el proceso normal
     * 
     * @param Request $request
     * @param int|null $id ID de la factura (opcional si se envía en el body)
     * @return \Illuminate\Http\JsonResponse
     */
    public function regenerateBillPDF(Request $request, $id = null)
    {
        try {
            // Obtener IDs desde la URL o desde el body del request
            $facturaIds = [];
            
            if ($id !== null) {
                // ID desde la URL
                $facturaIds = [$id];
            } elseif ($request->has('factura_ids')) {
                // Array de IDs desde el body
                $facturaIds = $request->input('factura_ids');
                if (!is_array($facturaIds)) {
                    $facturaIds = [$facturaIds];
                }
            } elseif ($request->has('factura_id')) {
                // ID individual desde el body
                $facturaIds = [$request->input('factura_id')];
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de factura requerido.',
                    'error' => 'Debe proporcionar factura_id o factura_ids en el request.'
                ], 400);
            }

            // Validar que todos los IDs sean numéricos
            foreach ($facturaIds as $facturaId) {
                if (!is_numeric($facturaId)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'ID de factura inválido.',
                        'error' => "El ID '{$facturaId}' debe ser un número válido."
                    ], 400);
                }
            }

            // Limitar cantidad para evitar timeouts
            if (count($facturaIds) > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Demasiadas facturas solicitadas.',
                    'error' => 'Máximo 100 facturas por request. Total solicitadas: ' . count($facturaIds)
                ], 400);
            }

            $resultados = [];
            $exitosas = 0;
            $fallidas = 0;
            $startTime = microtime(true);

            Log::info("Iniciando regeneración masiva de PDFs", [
                'total_facturas' => count($facturaIds),
                'facturas_ids' => $facturaIds
            ]);

            foreach ($facturaIds as $facturaId) {
                try {
                    $resultado = $this->procesarFacturaIndividual($request, $facturaId);
                    $resultados[] = $resultado;
                    
                    if ($resultado['success']) {
                        $exitosas++;
                    } else {
                        $fallidas++;
                    }
                    
                } catch (Exception $e) {
                    $fallidas++;
                    $resultados[] = [
                        'success' => false,
                        'factura_id' => $facturaId,
                        'message' => 'Error inesperado al procesar factura.',
                        'error' => $e->getMessage()
                    ];
                    
                    Log::error("Error inesperado procesando factura ID: {$facturaId}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            $endTime = microtime(true);
            $totalTime = round($endTime - $startTime, 2);

            Log::info("Regeneración masiva completada", [
                'total_facturas' => count($facturaIds),
                'exitosas' => $exitosas,
                'fallidas' => $fallidas,
                'tiempo_total' => $totalTime . ' segundos'
            ]);

            // Respuesta individual vs masiva
            if (count($facturaIds) === 1) {
                // Para una sola factura, retornar el formato original
                return response()->json($resultados[0], $resultados[0]['success'] ? 200 : ($resultados[0]['status_code'] ?? 500));
            } else {
                // Para múltiples facturas, retornar resumen + detalles
                return response()->json([
                    'success' => $fallidas === 0,
                    'message' => "Procesamiento completado: {$exitosas} exitosas, {$fallidas} fallidas de " . count($facturaIds) . " facturas.",
                    'summary' => [
                        'total_requested' => count($facturaIds),
                        'successful' => $exitosas,
                        'failed' => $fallidas,
                        'success_rate' => count($facturaIds) > 0 ? round(($exitosas / count($facturaIds)) * 100, 2) : 0,
                        'processing_time' => $totalTime . ' segundos',
                        'processed_at' => date('Y-m-d H:i:s')
                    ],
                    'results' => $resultados
                ], 200);
            }

        } catch (Exception $e) {
            Log::error("Error crítico en regeneración masiva de PDFs", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error crítico en el procesamiento.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesar una factura individual para regeneración de PDF
     * 
     * @param Request $request
     * @param int $id
     * @return array
     */
    private function procesarFacturaIndividual(Request $request, $id)
    {
        try {
            // Buscar la factura con todas sus relaciones necesarias
            $factura = Factura::with([
                'talonario', 
                'cliente', 
                'detalle.servicio', 
                'notaCredito', 
                'bonificacionesPuntuales'
            ])->find($id);

            if (!$factura) {
                return [
                    'success' => false,
                    'factura_id' => $id,
                    'message' => 'Factura no encontrada.',
                    'error' => "No se encontró una factura con ID: {$id}",
                    'status_code' => 404
                ];
            }

            // Formatear los campos necesarios
            $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);
            $factura->nro_factura = $this->zerofill($factura->nro_factura);
            $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);

            // Formatear fechas
            $factura->fecha_emision = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
            $factura->primer_vto_fecha = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
            $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');
            $factura->tercer_vto_fecha = $factura->tercer_vto_fecha ? Carbon::parse($factura->tercer_vto_fecha)->format('d/m/Y') : '';

            // Generar nombre de archivo y path
            $factura->filename = $factura->talonario->nro_punto_vta . '-' . $factura->nro_factura;
            $factura->filePath = public_path(config('constants.folder_facturas') . 'factura-' . $factura->filename . '.pdf');
            
            // URL pública del PDF
            $publicUrl = $request->root() . '/' . config('constants.folder_facturas') . 'factura-' . $factura->filename . '.pdf';

            // Asegurar que el directorio existe
            $directory = dirname($factura->filePath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Procesar detalles de la factura
            foreach ($factura->detalle as $detalle) {
                $detalle->servicio; // Cargar relación servicio
            }

            // Regenerar códigos QR de MercadoPago para cada vencimiento
            try {
                $this->generatePaymentQRCodes($factura);
            } catch (Exception $e) {
                // Log del error pero continuar con la generación del PDF
                Log::warning("Error generando códigos QR para factura ID: {$id}. Error: " . $e->getMessage());
            }

            // Crear y guardar el PDF
            $pdf = PDF::loadView('pdf.facturas', ['facturas' => [$factura]]);
            $pdf->save($factura->filePath);

            // Verificar que el archivo se creó correctamente
            if (!file_exists($factura->filePath)) {
                throw new Exception('El archivo PDF no se pudo crear en la ruta especificada.');
            }

            $fileSize = filesize($factura->filePath);

            return [
                'success' => true,
                'factura_id' => $factura->id,
                'message' => 'PDF regenerado exitosamente.',
                'data' => [
                    'factura_id' => $factura->id,
                    'numero_factura' => $factura->talonario->letra . ' ' . $factura->talonario->nro_punto_vta . '-' . $factura->nro_factura,
                    'cliente' => $factura->cliente->firstname . ' ' . $factura->cliente->lastname,
                    'periodo' => $factura->periodo,
                    'filename' => $factura->filename . '.pdf',
                    'file_path' => $factura->filePath,
                    'file_size' => $fileSize,
                    'public_url' => $publicUrl,
                    'generated_at' => date('Y-m-d H:i:s')
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'factura_id' => $id,
                'message' => 'Error al regenerar PDF.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Listar pagos informados pendientes de validación
     */
    public function getInformedPayments(Request $request)
    {
        $pagosInformados = PagoInformado::with(['factura.talonario', 'factura.cliente', 'usuario'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($pagosInformados as $pago) {
            $pago->factura->talonario->nro_punto_vta = $this->zerofill($pago->factura->talonario->nro_punto_vta, 4);
            $pago->factura->nro_factura = $this->zerofill($pago->factura->nro_factura);
        }

        return View::make('admin.informed_payments')->with(['pagos_informados' => $pagosInformados]);
    }

    /**
     * Ver detalle de un pago informado
     */
    public function getInformedPaymentDetail(Request $request, $id)
    {
        $pagoInformado = PagoInformado::with(['factura.talonario', 'factura.cliente', 'usuario', 'validadoPor'])
            ->find($id);

        if (!$pagoInformado) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Pago informado no encontrado.', 'icon' => 'fa-frown-o']);
        }

        $pagoInformado->factura->talonario->nro_punto_vta = $this->zerofill($pagoInformado->factura->talonario->nro_punto_vta, 4);
        $pagoInformado->factura->nro_factura = $this->zerofill($pagoInformado->factura->nro_factura);

        return View::make('admin.informed_payment_detail')->with(['pago_informado' => $pagoInformado]);
    }

    /**
     * Aprobar un pago informado
     */
    public function approveInformedPayment(Request $request, $id)
    {
        $pagoInformado = PagoInformado::find($id);

        if (!$pagoInformado) {
            return response()->json(['error' => 'Pago informado no encontrado'], 404);
        }

        if ($pagoInformado->estado !== 'pendiente') {
            return response()->json(['error' => 'El pago ya fue procesado'], 400);
        }

        $factura = $pagoInformado->factura;

        // Verificar que la factura no esté ya pagada
        if ($factura->fecha_pago) {
            return response()->json(['error' => 'La factura ya está pagada'], 400);
        }

        try {
            DB::beginTransaction();

            // Aprobar el pago informado
            $pagoInformado->aprobar(Auth::id(), $request->observaciones);

            // Marcar la factura como pagada
            $factura->fecha_pago = $pagoInformado->fecha_pago_informado;
            $factura->importe_pago = $pagoInformado->importe_informado;
            $factura->forma_pago = 6; // CBU/Transferencia
            $factura->save();

            DB::commit();

            // Enviar correo de aprobación al cliente
            try {
                Mail::send('email.pago_informado_aprobado', ['pagoInformado' => $pagoInformado], function ($message) use ($pagoInformado) {
                    $message->to($pagoInformado->usuario->email, $pagoInformado->usuario->firstname . ' ' . $pagoInformado->usuario->lastname);
                    $message->subject('REDIN - ¡Pago Aprobado! - Factura Pagada');
                });
            } catch (\Exception $e) {
                Log::error('Error enviando correo de pago aprobado: ' . $e->getMessage());
            }

            return response()->json([
                'success' => 'Pago aprobado correctamente. La factura ha sido marcada como pagada y se ha notificado al cliente por email.',
                'factura_numero' => $factura->talonario->letra . ' ' . $factura->talonario->nro_punto_vta . '-' . $factura->nro_factura
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error al aprobar el pago: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Rechazar un pago informado
     */
    public function rejectInformedPayment(Request $request, $id)
    {
        $pagoInformado = PagoInformado::find($id);

        if (!$pagoInformado) {
            return response()->json(['error' => 'Pago informado no encontrado'], 404);
        }

        if ($pagoInformado->estado !== 'pendiente') {
            return response()->json(['error' => 'El pago ya fue procesado'], 400);
        }

        $observaciones = $request->observaciones;
        if (empty($observaciones)) {
            return response()->json(['error' => 'Las observaciones son obligatorias para rechazar un pago'], 400);
        }

        try {
            $pagoInformado->rechazar(Auth::id(), $observaciones);

            // Enviar correo de rechazo al cliente
            try {
                Mail::send('email.pago_informado_rechazado', ['pagoInformado' => $pagoInformado], function ($message) use ($pagoInformado) {
                    $message->to($pagoInformado->usuario->email, $pagoInformado->usuario->firstname . ' ' . $pagoInformado->usuario->lastname);
                    $message->subject('REDIN - Pago Rechazado - Información Importante');
                });
            } catch (\Exception $e) {
                Log::error('Error enviando correo de pago rechazado: ' . $e->getMessage());
            }

            return response()->json([
                'success' => 'Pago rechazado correctamente. Se ha notificado al cliente por email con el motivo del rechazo.',
                'observaciones' => $observaciones
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => 'Error al rechazar el pago: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mostrar formulario de confirmación para anular un período completo
     */
    public function cancelPeriod($mes, $ano)
    {
        $periodo = $mes . '/' . $ano;
        
        // Obtener facturas del período (solo activas)
        $facturas = Factura::where('periodo', $periodo)->whereNull('deleted_at')->get();
        
        if ($facturas->isEmpty()) {
            return redirect('/admin/period')->with([
                'status' => 'danger',
                'icon' => 'fa-times-circle',
                'message' => 'No se encontraron facturas activas para el período ' . $periodo
            ]);
        }

        // Calcular estadísticas del período
        $stats = [
            'total_facturas' => $facturas->count(),
            'facturas_pagadas' => $facturas->where('importe_pago', '>', 0)->count(),
            'facturas_enviadas' => $facturas->whereNotNull('mail_date')->count(),
            'facturas_con_cae' => $facturas->whereNotNull('cae')->count(),
            'importe_total' => $facturas->sum('importe_total'),
        ];

        return View::make('period.cancel_periodo')->with([
            'periodo' => $periodo,
            'mes' => $mes,
            'ano' => $ano,
            'stats' => $stats
        ]);
    }

    /**
     * Procesar la anulación de un período completo
     */
    public function cancelPeriodPost(Request $request, $mes, $ano)
    {

        $periodo = $mes . '/' . $ano;
        $userId = Auth::id();
        
        // SIN TRANSACCIÓN - Cada NC y anulación se guarda inmediatamente
        
        try {
            // Obtener todas las facturas del período (solo no anuladas)
            $facturas = Factura::where('periodo', $periodo)->whereNull('deleted_at')->get();
            
            if ($facturas->isEmpty()) {
                return redirect('/admin/period')->with([
                    'status' => 'danger',
                    'icon' => 'fa-times-circle',
                    'message' => 'No se encontraron facturas activas para el período ' . $periodo
                ]);
            }

            $contadores = [
                'facturas_anuladas' => 0,
                'notas_credito_emitidas' => 0,
                'notas_credito_fallidas' => 0,
                'saldos_favor_creados' => 0,
                'errores' => []
            ];

            foreach ($facturas as $factura) {
                try {
                    $factura->talonario; // Cargar relación
                    $factura->cliente; // Cargar cliente
                    
                    // Verificar si la factura estaba pagada
                    $estabaPagada = !empty($factura->fecha_pago) && !empty($factura->importe_pago);
                    
                    // **NO EMITIR NC EN AFIP SI LA FACTURA YA ESTABA PAGADA**
                    // Esto mantiene las facturas en los períodos fiscales donde ingresó el dinero
                    $debeEmitirNC = !$estabaPagada;
                    
                    Log::info('Verificación emisión NC', [
                        'factura_id' => $factura->id,
                        'estaba_pagada' => $estabaPagada,
                        'debe_emitir_nc' => $debeEmitirNC,
                        'fecha_pago' => $factura->fecha_pago,
                        'forma_pago' => $factura->forma_pago
                    ]);
                    
                    // Determinar tipo de comprobante según letra del talonario
                    $cbteTipo = $factura->talonario->letra == 'A' ? 3 : 8; // 3=NC A, 8=NC B
                    $ptoVta = $factura->talonario->nro_punto_vta;
                    
                    // Emitir Nota de Crédito en AFIP SOLO SI LA FACTURA NO ESTABA PAGADA
                    if ($debeEmitirNC) {
                        try {
                        if ($factura->talonario->letra == 'A') {
                            // Nota de Crédito A
                            $afipResponse = $this->afipService->notaCreditoA(
                                $ptoVta,
                                $factura->cliente->dni,
                                $factura->importe_total,
                                $factura->nro_factura
                            );
                        } else {
                            // Nota de Crédito B
                            $afipResponse = $this->afipService->notaCreditoB(
                                $ptoVta,
                                $factura->importe_total,
                                $factura->nro_factura
                            );
                        }

                        Log::info('AFIP - Respuesta NC para anulación de período', [
                            'factura_id' => $factura->id,
                            'periodo' => $periodo,
                            'response' => $afipResponse
                        ]);

                        // Verificar respuesta de AFIP
                        if (isset($afipResponse['CAE']) && !empty($afipResponse['CAE'])) {
                            // Crear registro de Nota de Crédito
                            $notaCredito = new NotaCredito();
                            $notaCredito->factura_id = $factura->id;
                            $notaCredito->talonario_id = $factura->talonario_id;
                            // Usar CbteDesde si existe, sino CbteHasta, sino null
                            $notaCredito->nro_nota_credito = $afipResponse['CbteDesde'] ?? ($afipResponse['CbteHasta'] ?? null);
                            // nro_cliente es INTEGER, extraer solo números si es alfanumérico
                            $notaCredito->nro_cliente = is_numeric($factura->nro_cliente) 
                                ? $factura->nro_cliente 
                                : (int) preg_replace('/[^0-9]/', '', $factura->nro_cliente);
                            $notaCredito->periodo = $factura->periodo;
                            $notaCredito->fecha_emision = Carbon::now();
                            $notaCredito->motivo = 'ANULACIÓN DE PERÍODO ' . $periodo . ' - Generado por: ' . Auth::user()->name;
                            
                            // Importes de la nota de crédito
                            $notaCredito->importe_bonificacion = $factura->importe_subtotal;
                            $notaCredito->importe_iva = $factura->importe_subtotal_iva ?? ($factura->importe_total - $factura->importe_subtotal);
                            $notaCredito->importe_total = $factura->importe_total;
                            
                            // Datos de AFIP
                            $notaCredito->cae = $afipResponse['CAE'];
                            
                            try {
                                if (isset($afipResponse['CAEFchVto'])) {
                                    $caeVto = $afipResponse['CAEFchVto'];
                                    // AFIP puede devolver en formato Ymd (20260121) o Y-m-d (2026-01-21)
                                    if (strlen($caeVto) == 8 && is_numeric($caeVto)) {
                                        // Formato Ymd
                                        $notaCredito->cae_vto = Carbon::createFromFormat('Ymd', $caeVto);
                                    } else {
                                        // Formato Y-m-d u otro formato con guiones/barras
                                        $notaCredito->cae_vto = Carbon::parse($caeVto);
                                    }
                                } else {
                                    $notaCredito->cae_vto = null;
                                }
                            } catch (\Exception $e) {
                                Log::error('Error parsing CAEFchVto: ' . $e->getMessage(), ['cae_vto' => $afipResponse['CAEFchVto'] ?? 'N/A']);
                                $notaCredito->cae_vto = null;
                            }
                            
                            $notaCredito->save();
                            $contadores['notas_credito_emitidas']++;
                            
                            Log::info('NC creada exitosamente', [
                                'nc_id' => $notaCredito->id,
                                'nro_nc' => $notaCredito->nro_nota_credito,
                                'cae' => $notaCredito->cae
                            ]);
                            
                        } else {
                            // AFIP no devolvió CAE
                            $errorMsg = 'AFIP no devolvió CAE para factura ' . $factura->nro_factura;
                            if (isset($afipResponse['Observaciones'])) {
                                $errorMsg .= ': ' . print_r($afipResponse['Observaciones'], true);
                            }
                            $contadores['errores'][] = $errorMsg;
                            $contadores['notas_credito_fallidas']++;
                            Log::error($errorMsg, ['afip_response' => $afipResponse]);
                            continue; // No anular la factura si falla la NC
                        }

                    } catch (Exception $afipException) {
                        $errorMsg = 'Error AFIP en factura ' . $factura->nro_factura . ': ' . $afipException->getMessage();
                        $contadores['errores'][] = $errorMsg;
                        $contadores['notas_credito_fallidas']++;
                        Log::error($errorMsg);
                        continue; // No anular la factura si falla la NC
                    }
                    } else {
                        // Factura estaba pagada - No se emite NC en AFIP
                        Log::info('NC NO emitida en AFIP - Factura ya estaba pagada', [
                            'factura_id' => $factura->id,
                            'nro_factura' => $factura->nro_factura,
                            'fecha_pago' => $factura->fecha_pago,
                            'forma_pago' => $factura->forma_pago,
                            'importe_pago' => $factura->importe_pago
                        ]);
                    }
                    

                    // Si llegamos aquí, la NC se emitió correctamente
                    // Si la factura estaba pagada, crear registro de saldo a favor
                    if ($estabaPagada && $factura->importe_pago > 0) {
                        try {
                            $saldoFavor = new SaldoFavor();
                            $saldoFavor->user_id = $factura->user_id;
                            $saldoFavor->factura_anulada_id = $factura->id;
                            $saldoFavor->nota_credito_id = $notaCredito->id ?? null;
                            $saldoFavor->periodo = $factura->periodo;
                            $saldoFavor->importe_pagado = $factura->importe_pago;
                            $saldoFavor->importe_utilizado = 0;
                            $saldoFavor->importe_disponible = $factura->importe_pago;
                            $saldoFavor->estado = 'pendiente';
                            $saldoFavor->observaciones = 'Saldo a favor generado por anulación de período ' . $periodo . 
                                                         ' - Factura #' . $factura->nro_factura . 
                                                         ' pagada el ' . Carbon::parse($factura->fecha_pago)->format('d/m/Y');
                            $saldoFavor->save();
                            
                            $contadores['saldos_favor_creados']++;
                            
                            Log::info('Saldo a favor creado', [
                                'saldo_id' => $saldoFavor->id,
                                'user_id' => $factura->user_id,
                                'importe' => $factura->importe_pago,
                                'factura_anulada' => $factura->id
                            ]);
                        } catch (Exception $saldoException) {
                            Log::error('Error al crear saldo a favor para factura ' . $factura->id . ': ' . $saldoException->getMessage());
                            // Continuar aunque falle el saldo a favor
                        }
                    }
                    
                    // Realizar soft delete de la factura
                    $factura->motivo_anulacion = 'ANULACIÓN MASIVA DE PERÍODO ' . $periodo;
                    $factura->anulado_por = $userId;
                    $factura->fecha_anulacion = Carbon::now();
                    $factura->save();
                    $factura->delete(); // Soft delete
                    
                    $contadores['facturas_anuladas']++;

                } catch (Exception $e) {
                    $contadores['errores'][] = 'Factura #' . $factura->id . ': ' . $e->getMessage();
                    Log::error('Error procesando factura ' . $factura->id . ': ' . $e->getMessage());
                }
            }

            // Todo se guardó inmediatamente - no hay commit necesario

            $mensaje = "Período {$periodo} procesado. ";
            $mensaje .= "Facturas anuladas: {$contadores['facturas_anuladas']}. ";
            $mensaje .= "NC emitidas en AFIP: {$contadores['notas_credito_emitidas']}. ";
            
            if ($contadores['saldos_favor_creados'] > 0) {
                $mensaje .= "Saldos a favor creados: {$contadores['saldos_favor_creados']}. ";
            }
            
            if ($contadores['notas_credito_fallidas'] > 0) {
                $mensaje .= "NC fallidas: {$contadores['notas_credito_fallidas']}. ";
            }
            
            if (!empty($contadores['errores'])) {
                $mensaje .= " Errores: " . implode(' | ', array_slice($contadores['errores'], 0, 3));
                if (count($contadores['errores']) > 3) {
                    $mensaje .= ' (y ' . (count($contadores['errores']) - 3) . ' más)';
                }
            }

            $status = $contadores['notas_credito_fallidas'] > 0 ? 'warning' : 'success';
            $icon = $contadores['notas_credito_fallidas'] > 0 ? 'fa-exclamation-triangle' : 'fa-check-circle';

            return redirect('/admin/period')->with([
                'status' => $status,
                'icon' => $icon,
                'message' => $mensaje
            ]);

        } catch (Exception $e) {
            Log::error('Error en cancelPeriodPost: ' . $e->getMessage());
            
            return redirect()->back()->with([
                'status' => 'danger',
                'icon' => 'fa-times-circle',
                'message' => 'Error al anular el período: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener saldos a favor disponibles de un cliente para un período
     * 
     * @param int $userId ID del usuario/cliente
     * @param string $periodo Período en formato MM/YYYY (opcional, si se especifica solo busca saldos de ese período)
     * @return Collection Colección de saldos a favor disponibles
     */
    public function getSaldosFavorCliente($userId, $periodo = null)
    {
        $query = SaldoFavor::where('user_id', $userId)
            ->where('importe_disponible', '>', 0)
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->orderBy('created_at', 'asc'); // FIFO: primero los más antiguos
        
        if ($periodo) {
            $query->where('periodo', $periodo);
        }
        
        return $query->get();
    }

    /**
     * Calcular el total de saldo a favor disponible de un cliente
     * 
     * @param int $userId ID del usuario/cliente
     * @param string $periodo Período en formato MM/YYYY (opcional)
     * @return float Total de saldo disponible
     */
    public function getTotalSaldoFavorCliente($userId, $periodo = null)
    {
        $query = SaldoFavor::where('user_id', $userId)
            ->where('importe_disponible', '>', 0)
            ->whereIn('estado', ['pendiente', 'parcial']);
        
        if ($periodo) {
            $query->where('periodo', $periodo);
        }
        
        // DEBUG: Log de la consulta
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        Log::info('DEBUG getTotalSaldoFavorCliente - SQL', [
            'sql' => $sql,
            'bindings' => $bindings,
            'user_id' => $userId,
            'periodo' => $periodo
        ]);
        
        $saldos = $query->get();
        Log::info('DEBUG getTotalSaldoFavorCliente - Registros', [
            'count' => $saldos->count(),
            'saldos' => $saldos->map(function($s) {
                return [
                    'id' => $s->id,
                    'periodo' => $s->periodo,
                    'importe_disponible' => $s->importe_disponible,
                    'tipo' => gettype($s->importe_disponible)
                ];
            })->toArray()
        ]);
        
        $total = $saldos->sum('importe_disponible');
        Log::info('DEBUG getTotalSaldoFavorCliente - Total', [
            'total' => $total,
            'tipo' => gettype($total)
        ]);
        
        return $total;
    }

    /**
     * Aplicar saldo a favor a una factura nueva
     * Esto se debe llamar al generar una nueva factura para un cliente que tiene saldo
     * IMPORTANTE: Solo aplica saldos del mismo período que la factura
     * 
     * @param int $userId ID del usuario/cliente
     * @param int $facturaId ID de la nueva factura
     * @param float $importeFactura Importe total de la factura
     * @param string $periodo Período en formato MM/YYYY
     * @return array ['importe_descontado' => float, 'saldos_aplicados' => array]
     */
    public function aplicarSaldoFavorAFactura($userId, $facturaId, $importeFactura, $periodo)
    {
        // Solo obtener saldos del mismo período
        $saldos = $this->getSaldosFavorCliente($userId, $periodo);
        
        Log::info('DEBUG aplicarSaldoFavorAFactura - Saldos obtenidos', [
            'user_id' => $userId,
            'periodo' => $periodo,
            'count' => $saldos->count(),
            'saldos' => $saldos->map(function($s) {
                return [
                    'id' => $s->id,
                    'importe_disponible' => $s->importe_disponible,
                    'estado' => $s->estado
                ];
            })->toArray()
        ]);
        
        $importeRestante = $importeFactura;
        $importeDescontado = 0;
        $saldosAplicados = [];

        foreach ($saldos as $saldo) {
            Log::info('DEBUG aplicarSaldoFavorAFactura - Procesando saldo', [
                'saldo_id' => $saldo->id,
                'importe_disponible' => $saldo->importe_disponible,
                'importe_restante' => $importeRestante
            ]);
            
            if ($importeRestante <= 0) {
                Log::info('DEBUG aplicarSaldoFavorAFactura - Importe restante es 0, saliendo del loop');
                break;
            }

            $importeAAplicar = min($saldo->importe_disponible, $importeRestante);
            
            Log::info('DEBUG aplicarSaldoFavorAFactura - Antes de aplicar', [
                'importe_a_aplicar' => $importeAAplicar,
                'tiene_metodo_aplicarSaldo' => method_exists($saldo, 'aplicarSaldo')
            ]);
            
            $saldo->aplicarSaldo($importeAAplicar, $facturaId);
            
            $importeRestante -= $importeAAplicar;
            $importeDescontado += $importeAAplicar;
            
            $saldosAplicados[] = [
                'saldo_id' => $saldo->id,
                'importe_aplicado' => $importeAAplicar,
                'factura_anulada_original' => $saldo->factura_anulada_id
            ];

            Log::info('Saldo a favor aplicado', [
                'saldo_id' => $saldo->id,
                'factura_nueva_id' => $facturaId,
                'importe_aplicado' => $importeAAplicar,
                'saldo_restante' => $saldo->importe_disponible
            ]);
        }

        return [
            'importe_descontado' => $importeDescontado,
            'saldos_aplicados' => $saldosAplicados
        ];
    }

    /**
     * Crear bonificación puntual por saldo a favor en una factura
     * Esto se llama después de generar la factura para aplicar el descuento
     * 
     * @param int $facturaId ID de la factura
     * @param float $importe Importe del saldo a favor a aplicar
     * @param string $descripcion Descripción de la bonificación
     * @return BonificacionPuntual
     */
    public function crearBonificacionPorSaldoFavor($facturaId, $importe, $descripcion = null)
    {
        $bonificacion = new BonificacionPuntual();
        $bonificacion->factura_id = $facturaId;
        $bonificacion->importe = $importe;
        $bonificacion->descripcion = $descripcion ?? 'Saldo a favor por período anulado';
        $bonificacion->save();

        return $bonificacion;
    }
}
