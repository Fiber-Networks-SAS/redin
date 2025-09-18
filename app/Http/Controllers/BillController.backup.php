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
use Intervention\Image\ImageManagerStatic as Image;
use Yajra\Datatables\Datatables;
use iio\libmergepdf\Merger;

use App\User;
use App\Role;
use App\Pais;
use App\Provincia;
use App\Localidad;
use App\Servicio;
use App\ServicioUsuario;
use App\Talonario;
use App\Factura;
use App\FacturaDetalle;
use App\Interes;

// use Entrust;
use PDF;
use Carbon\Carbon;

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
    

    public function __construct()
    {
        // $this->middleware('auth');

        // Check user permission 
        // $this->middleware('permission:crud-users');

        $this->tipo = ['Internet', 'Telefonía', 'Televisión'];
        $this->drop = ['En Pilar', 'En Domicilio', 'Sin Drop'];
        $this->forma_pago = [1 => 'Efectivo', 2 => 'Pago Mis Cuentas', 3 => 'Cobro Express',]; // 4 => 'Tarjeta de Crédito', 5 => 'Depósito'
        $this->meses = ['01' => 'ENERO', 
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
        $facturas = Factura::all()->groupBy('periodo');
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
                if ($f->mail_date != null && $f->mail_date != '') {
                    $mails++;
                }

            }

            // path del pdf
            $filename = str_replace('/', '-', $periodo);
            $filename = $request->root().'/'.config('constants.folder_periodos').'periodo-'.$filename.'.pdf';

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
    
            $periodo = $mes.'/'.$ano;

            return View::make('period.list_periodo_facturas')->with(['periodo' => $periodo]);
    
        }
    
    }

    // lista de facturas de un periodo dado - AJAX
    public function getBillPeriodList(Request $request, $mes, $ano)
    {

        $fecha_actual = Carbon::today();
        $periodo = $mes.'/'.$ano;
        $facturas = Factura::where('periodo', $periodo)->get();

        foreach ($facturas as $factura) {

            $factura->talonario;
            $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);
            
            $factura->nro_factura = $this->zerofill($factura->nro_factura);
            $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);

            $factura->cliente;
            $factura->cliente->nombre_apellido = $factura->cliente->firstname.' '.$factura->cliente->lastname;


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
            $filename = $factura->talonario->nro_punto_vta.'-'.$factura->nro_factura;
            $factura->pdf = $request->root().'/'.config('constants.folder_facturas') . 'factura-'.$filename.'.pdf';



        }

        // return $facturas;
        return Datatables::of($facturas)->make(true);
    }

    // detalle de factura
    public function getBillDetail(Request $request, $id)
    {
        
        $factura = Factura::find($id);

        $factura->talonario;
        $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);
        
        $factura->nro_factura = $this->zerofill($factura->nro_factura);
        $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);

        $factura->cliente;        

        $factura->fecha_emision = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
        $factura->primer_vto_fecha = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
        $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');
        $factura->tercer_vto_fecha = Carbon::parse($factura->tercer_vto_fecha)->format('d/m/Y');

        $factura->importe_subtotal = number_format($factura->importe_subtotal, 2); 
        $factura->importe_bonificacion = number_format($factura->importe_bonificacion, 2); 
        $factura->importe_total = number_format($factura->importe_total, 2); 
        $factura->segundo_vto_importe = number_format($factura->segundo_vto_importe, 2); 
        $factura->tercer_vto_importe = number_format($factura->tercer_vto_importe, 2); 

        $factura->fecha_pago = $factura->fecha_pago ? Carbon::parse($factura->fecha_pago)->format('d/m/Y') : null;
        $factura->importe_pago = number_format($factura->importe_pago, 2); 
        $factura->forma_pago = $factura->fecha_pago ? $this->forma_pago[$factura->forma_pago] : '';
        $factura->mail_date = $factura->mail_date ? Carbon::parse($factura->mail_date)->format('d/m/Y') : null;

        $detalles =  $factura->detalle;

        foreach ($detalles as $detalle) {
            $detalle->servicio;
        }            

                    
        // genero los PDF's de las facturas individuales
        $filename = $factura->talonario->nro_punto_vta.'-'.$factura->nro_factura;
        $factura->pdf = $request->root().'/'.config('constants.folder_facturas') . 'factura-'.$filename.'.pdf';

        // return $factura;

        return View::make('period.view_factura')->with(['factura' => $factura]);
    
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
                $request->importe_subtotal = str_replace(",","",$request->importe_subtotal);
                $request->importe_total = str_replace("$","",$request->importe_total);
                $request->importe_total = str_replace(",","",$request->importe_total);


                // obtengo la conf. de intereses 
                $interes = Interes::find(1);

                // obtengo la fecha actual
                $fecha_actual = Carbon::now();

                $factura->fecha_emision         = $fecha_actual; // actualizo la fecha de emision.
                $factura->importe_bonificacion  = $this->floatvalue(number_format($request->importe_bonificacion, 2));
                $factura->importe_subtotal      = $this->floatvalue(number_format($request->importe_subtotal, 2));
                $factura->importe_total         = $this->floatvalue(number_format($request->importe_total, 2));
                $factura->primer_vto_codigo     = $this->getCodigoPago($factura->importe_total, $factura->primer_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);
                $factura->segundo_vto_importe   = $this->getImporteConTasaInteres($factura->importe_total, $interes->segundo_vto_tasa);
                $factura->segundo_vto_codigo    = $this->getCodigoPago($factura->segundo_vto_importe, $factura->segundo_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);


                if($factura->save()){
                                
                    // actualizo el detalle de la factura
                    if($request->field_type != 'importe_bonificacion'){

                        $factura_detalle = FacturaDetalle::find($request->id);

                        switch ($request->field_type) {

                            case 'importe_fila':
                                if ($request->proporcional == 1) {
                                    $factura_detalle->abono_proporcional = $request->value;
                                }else{
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
                    // return redirect($request->previousUrl)->with(['status' => 'success', 'message' => 'Se ha bonificado la factura '.$factura->talonario->letra.' '.$factura->talonario->nro_punto_vta.' - '.$factura->nro_factura.'.' , 'icon' => 'fa-smile-o', 'filename' => $filename, 'factura_id' => $factura->id]);

                }else{
            
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

        // $factura->fecha_emision = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
        // $factura->primer_vto_fecha = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
        // $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');

        $factura->importe_subtotal = number_format($factura->importe_subtotal, 2); 
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

        return View::make('period.view_factura_bonificar')->with(['factura' => $factura]);
    
    }

    // bonificacion de factura POST
    public function getBillImprovePost(Request $request, $id)
    {

        
        //-- VALIDATOR START --//
        $rules = array(
            'importe_bonificacion'      => ['required', 'regex:/^-?(?:0|[1-9]\d{0,2}(?:,?\d{3})*)(?:\.\d+)?$/'],
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
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
                $factura->importe_bonificacion  = $this->floatvalue(number_format($request->importe_bonificacion, 2));
                $factura->importe_total         = $this->floatvalue(number_format($factura->importe_subtotal - $factura->importe_bonificacion, 2));

                
                // ----------------------------------------------------
                $factura->primer_vto_codigo     = $this->getCodigoPago($factura->importe_total, $factura->primer_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);

                // ----------------------------------------------------                
                $factura->segundo_vto_importe   = $this->getImporteConTasaInteres($factura->importe_total, $interes->segundo_vto_tasa);
                $factura->segundo_vto_codigo    = $this->getCodigoPago($factura->segundo_vto_importe, $factura->segundo_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);
                // ----------------------------------------------------
                
                if($factura->save()){
                                
                    // actualizo el PDF del periodo e individual
                    $this->setFacturasPeriodoPDF($factura->periodo, $factura->id);
                    $filename = $this->getFacturaPDFPath($request, $factura);


                    return redirect($request->previousUrl)->with(['status' => 'success', 'message' => 'Se ha bonificado la factura '.$factura->talonario->letra.' '.$factura->talonario->nro_punto_vta.' - '.$factura->nro_factura.'.' , 'icon' => 'fa-smile-o', 'filename' => $filename, 'factura_id' => $factura->id]);

                }else{
            
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

        $factura->importe_subtotal = number_format($factura->importe_subtotal, 2); 
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
        
        if($validator->fails())
        {       
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

                // $factura->tercer_vto_importe   = $this->getImporteConTasaInteres($factura->importe_total, $interes->segundo_vto_tasa + $interes->tercer_vto_tasa);
                $response  = $this->getImporteConTasaInteresTercerVto($factura->segundo_vto_importe, $factura->segundo_vto_fecha, $interes->tercer_vto_tasa, $request->tercer_vto_fecha);

                // campos del tercer vto 
                $factura->tercer_vto_fecha   = Carbon::createFromFormat('d/m/Y', $request->tercer_vto_fecha);
                $factura->tercer_vto_tasa    = $response['tasa'];
                $factura->tercer_vto_importe = $response['importe'];
                $factura->tercer_vto_codigo  = $this->getCodigoPago($factura->tercer_vto_importe, $factura->tercer_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);

                
                // ----------------------------------------------------
                
                if($factura->save()){

                    // actualizo el PDF del periodo e individual
                    $this->setFacturasPeriodoPDF($factura->periodo, $factura->id);
                    $filename = $this->getFacturaPDFPath($request, $factura);
                    
                    return redirect($request->previousUrl)->with(['status' => 'success', 'message' => 'Se ha actualizado la factura '.$factura->talonario->letra.' '.$factura->talonario->nro_punto_vta.' - '.$factura->nro_factura.'.' , 'icon' => 'fa-smile-o', 'filename' => $filename, 'factura_id' => $factura->id]);

                }else{
            
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
            'importe_pago' => ['required', 'regex:/^-?(?:0|[1-9]\d{0,2}(?:,?\d{3})*)(?:\.\d+)?$/'],
            'forma_pago'    => 'required',

        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
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
                
                if($factura->save()){

                    $factura->talonario;
                    $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);
                    
                    $factura->nro_factura = $this->zerofill($factura->nro_factura);

                    return redirect($request->previousUrl)->with(['status' => 'success', 'message' => 'Se ha imputado el Pago a la factura '.$factura->talonario->letra.' '.$factura->talonario->nro_punto_vta.' - '.$factura->nro_factura.'.' , 'icon' => 'fa-smile-o']);

                }else{
            
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
        $users = Role::where('name', 'client')->first()->users()->get();
        $clients = 0;

        if (count($users)) {
            foreach ($users as $user) {
                if ($user->status == 1) {
                    $clients++;
                }
            }
        }

        // obtengo el siguiente mes a facturar 
        $factura    = Factura::orderBy('id', 'desc')->first();
        
        if($factura){
            
            $periodo_actual    = Carbon::createFromFormat('m/Y', $factura->periodo);
            $periodo_siguiente = $periodo_actual->addMonth();
            $periodo_siguiente = Carbon::parse($periodo_siguiente)->format('m/Y');
            
        }else{

            $periodo_siguiente = date('m/Y');
        
        }

        return View::make('period.create')->with(['servicios'=> $servicios, 'talonarios'=> $talonarios , 'interes'=> $interes , 'clients'=> $clients, 'periodo_siguiente' => $periodo_siguiente]);
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
            'periodo'       => 'required|unique:facturas,periodo|date_format:m/Y',
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        // For work edit file : \vendor\zizaco\entrust\src\Entrust\Traits\EntrustRoleTrait.php #52
        $users = Role::where('name', 'client')->first()->users()->get();
        
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
                
                if ($user->status == 1) {
                    
                    foreach ($user->servicios as $servicio) {
                     
                        // verify if es billable
                        $alta_servicio_periodo = Carbon::parse($servicio->alta_servicio)->format('m/Y');
                        $ifBillable = $this->getIfBillable($request->periodo, $alta_servicio_periodo);
                        
                        
                        // facturo solo los servicios activos y que fueron contratados a partir del periodo dado
                        // if ($servicio->status == 1 && $alta_servicio_periodo == $request->periodo) {
                        if ($servicio->status == 1 && $ifBillable) {
                        
                            // proporcional  ---------------------------------------------------------------
                            $proporcional = $this->getProporcional($user->id, $request->periodo, $servicio->alta_servicio, $servicio->abono_proporcional);
                            $servicio->costo_proporcional_importe = $proporcional['importe'];
                            $servicio->costo_proporcional_dias = $proporcional['dias'];

                            if($servicio->costo_proporcional_importe > 0){
                                $servicio->costo_abono_pagar = 0;
                            }else{
                                $servicio->costo_abono_pagar = $servicio->abono_mensual;
                            }

                            // costo de instalacion ---------------------------------------------------------
                            // obtengo la cantidad de cuotas pagas del servicio
                            // $servicio->costo_instalacion_cuotas_pagas = $this->getNroCuotasInstalacion($user->id, $servicio->servicio_id, $request->periodo, $servicio->alta_servicio, $servicio->plan_pago);
                            $servicio->costo_instalacion_cuotas_pagas = $this->getNroCuotasInstalacion($request->periodo, $servicio->alta_servicio);
                            
                            // asigno el importe a pagar de instalacion si aun se deben cuptas
                            if($servicio->costo_instalacion_cuotas_pagas < $servicio->plan_pago){
                                $servicio->costo_instalacion_importe_pagar = $servicio->costo_instalacion / $servicio->plan_pago;
                            }else{
                                $servicio->costo_instalacion_importe_pagar = 0;
                            }
                            

                            // asigno las variables ---------------------------------------------------------
                            $items[$user->id]['cliente'] = $user;
                            $items[$user->id]['servicios_activos'][] = $servicio;

                        }
                    
                    }
            
                }
            
            }

            // ---------------------------------------------------------------------------------------------------------------           

            // return $items;

            // Genero las Facturas
            foreach ($items as $item) {
                
                // calculo de importes
                $subtotal = 0;
                foreach ($item['servicios_activos'] as $servicio) {
                    $subtotal +=  $servicio->costo_proporcional_importe + $servicio->costo_abono_pagar + $servicio->costo_instalacion_importe_pagar;
                }

                // Cabecera
                $factura = new Factura;
                $factura->user_id               = $item['cliente']->id;
                $factura->nro_cliente           = $item['cliente']->nro_cliente;
                $factura->talonario_id          = $item['cliente']->talonario_id;
                $factura->nro_factura           = $this->getNroFactura($item['cliente']->talonario_id);
                $factura->periodo               = $request->periodo;
                $factura->fecha_emision         = Carbon::createFromFormat('d/m/Y', $request->fecha_emision);
                $factura->importe_subtotal      = $this->floatvalue(number_format($subtotal, 2));
                $factura->importe_bonificacion  = $this->floatvalue(number_format(0, 2));
                $factura->importe_total         = $this->floatvalue(number_format($subtotal, 2));
                
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
                $factura->primer_vto_fecha      = Carbon::createFromFormat('d/m/Y', $interes->primer_vto_dia.'/'.$mes_periodo_siguiente.'/'.$ano_periodo_siguiente);
                $factura->primer_vto_codigo     = $this->getCodigoPago($factura->importe_total, $factura->primer_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);

                // ----------------------------------------------------                
                $factura->segundo_vto_fecha     = Carbon::createFromFormat('d/m/Y', $interes->segundo_vto_dia.'/'.$mes_periodo_siguiente.'/'.$ano_periodo_siguiente);
                $factura->segundo_vto_tasa      = $interes->segundo_vto_tasa;
                $factura->segundo_vto_importe   = $this->getImporteConTasaInteres($factura->importe_total, $interes->segundo_vto_tasa);
                $factura->segundo_vto_codigo    = $this->getCodigoPago($factura->segundo_vto_importe, $factura->segundo_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);
                // ----------------------------------------------------
                
                $factura->tercer_vto_tasa       = $interes->tercer_vto_tasa;
                // debug
                // $factura->servicios_activos          = $item['servicios_activos'];
                // $facturas[] = $factura;

                if ($factura->save()) {
                
                    // Detalle
                    foreach ($item['servicios_activos'] as $servicio) {
            
                        $factura_detalle = new FacturaDetalle;
                        $factura_detalle->factura_id = $factura->id;
                        $factura_detalle->servicio_id = $servicio->servicio_id;
                        $factura_detalle->abono_mensual = $servicio->abono_mensual;
                        
                        // proporcional 
                        $factura_detalle->abono_proporcional = $servicio->costo_proporcional_importe > 0 ? $servicio->costo_proporcional_importe : null;
                        $factura_detalle->dias_proporcional = $servicio->costo_proporcional_dias > 0 ? $servicio->costo_proporcional_dias : null;
                        
                        $factura_detalle->costo_instalacion = $servicio->costo_instalacion_importe_pagar > 0 ? $servicio->costo_instalacion_importe_pagar : null;
                        $factura_detalle->instalacion_cuota = $servicio->costo_instalacion_importe_pagar > 0 ? $servicio->costo_instalacion_cuotas_pagas + 1 : null;
                        $factura_detalle->instalacion_plan_pago = $servicio->plan_pago;

                        $factura_detalle->save();
                    }
                
                }

            }

            // debug
            // return $facturas;
            // return $facturas = Factura::where('periodo', $request->periodo)->get();
            
            // genero los pdf's del periodo facturados
            $this->setFacturasPeriodoPDF($request->periodo); // SEGUIR SEGUIR SEGUIR SEGUIR (PASAR $request)
            $filename = $this->getFacturasPeriodoPDFPath($request);

            return redirect('/admin/period')->with(['status' => 'success', 'message' => 'El período '.$request->periodo.' fué facturado.', 'icon' => 'fa-smile-o', 'filename' => $filename]);


        }else{
            
            return redirect('/admin/period')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    public function getIfBillable($periodo, $alta_servicio)
    {
        $array_alta_servicio = explode('/',$alta_servicio);
        $array_periodo       = explode('/',$periodo);

        if (($array_alta_servicio[1] < $array_periodo[1]) ||  
            ($array_alta_servicio[1] == $array_periodo[1] && $array_alta_servicio[0] <= $array_periodo[0]) ) {
            
            $response = true;

        }else{
        
            $response = false;
        
        }

        return $response;
    }

    public function floatvalue($val)
    {

        $val = str_replace(",",".",$val);
        $val = preg_replace('/\.(?=.*\.)/', '', $val);
        return floatval($val);

    }

    public function zerofill($num, $zerofill = 8)
    {

        return str_pad($num, $zerofill, '0', STR_PAD_LEFT);

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
        $total_dias = $segundo_vto_fecha->diffInDays($fecha_pago);

        // calculo la tasa de interes multiplicando los dias por el % punitorio
        $response['tasa'] = $total_dias * $tasa;

        // calculo el importe final con la tasa final
        $response['importe'] = $this->floatvalue(number_format(($importe * $response['tasa'] / 100) + $importe, 2)); // 2 decimales

        // response
        return $response;

    }

    public function getNroFactura($talonario_id)
    {
        
        // busco si existen facturas hechas con el talonario indicado
        $nro_factura = Factura::where('talonario_id', $talonario_id)->max('nro_factura');

        // si no existen facturas busco el nro inicial del talonario
        if (!$nro_factura) {
            $nro_factura = Talonario::find($talonario_id)->nro_inicial;
        }else{

            $nro_factura++;
        }

        return $nro_factura;

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

    public function getProporcional($user_id, $periodo, $alta_servicio, $abono_proporcional)
    {

        // $dt = Carbon::now();
        $alta_servicio = Carbon::parse($alta_servicio);
        $fecha_actual = Carbon::today();
        $proporcional = array('importe' => 0, 'dias' => 0);

        // obtengo las facturas del cliente
        $facturas = Factura::where('user_id', $user_id)
                             // ->where('periodo', $periodo)
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

        // cuit de la empresa
        $cuit = config('constants.company_cuit');

        // importe
        // $importe  = number_format($importe, 2); // 2 decimales
        $importe  = explode('.',$importe);

        $importe_entero  = str_pad($importe[0],6,'0',STR_PAD_LEFT);  
        $importe_decimal = count($importe) == 2 ? str_pad($importe[1],2,'0',STR_PAD_RIGHT) : '00'; 

        // codigo de cliente
        $cod_cliente = str_pad($cod_cliente,5,'0',STR_PAD_LEFT);

        // fecha de vencimiento
        $dt = Carbon::parse($fecha_vto);
        $fecha_vto = $dt->year . str_pad($dt->month,2,'0',STR_PAD_LEFT)  . str_pad($dt->day,2,'0',STR_PAD_LEFT);

        // genero el codigo
        $codigo = $cuit . $importe_entero . $importe_decimal . $fecha_vto . $cod_cliente . $nro_punto_vta . $nro_factura;

        // obtengo el ultimo digito
        $digito = $this->getCodigoPagoDigito($codigo);
        $codigo = $codigo . $digito;


        return $codigo;

    }

    public function getCodigoPagoDigito($codigo)
    {
       $cadena=$codigo;
       $sumaP=0;
       $sumaI=0;
       $j=0;
       for($i=0;$i<strlen($cadena);$i++)
       {     
          if($j==0){
         
            $sumaI=$sumaI + $cadena[$i]; 
            $j=1;
         
          }else{
         
            $sumaP=$sumaP + $cadena[$i];   
            $j=0;
         
          } 
       }
       
       $sumaI=$sumaI*3;
       $total=$sumaI+$sumaP;

       $digito=$total%10;

        return $digito;
    }

    // generate facturas PDF
    public function setFacturasPeriodoPDF($periodo, $factura_id = null)
    {

        $facturas = Factura::where('periodo', $periodo)->get();

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

            $factura->importe_subtotal      = number_format($factura->importe_subtotal, 2); 
            $factura->importe_bonificacion  = number_format($factura->importe_bonificacion, 2); 
            $factura->importe_total         = number_format($factura->importe_total, 2); 
            $factura->segundo_vto_importe   = number_format($factura->segundo_vto_importe, 2); 
            $factura->tercer_vto_importe    = number_format($factura->tercer_vto_importe, 2); 
            
            // genero el path del pdf de la factura
            $factura->filename = $factura->talonario->nro_punto_vta.'-'.$factura->nro_factura;
            $factura->filePath = config('constants.folder_facturas') . 'factura-'.$factura->filename.'.pdf';

            // datos del cliente
            $factura->cliente;

            // datos del detalle
            $detalles =  $factura->detalle;
            foreach ($detalles as $detalle) {
                $detalle->servicio;
            }
            
            // genero los PDF's de las facturas individuales
            if ($factura_id == null || $factura_id == $factura->id) {

                $pdf = PDF::loadView('pdf.facturas', ['facturas' => [$factura]]); // envio como parametro un array con la factura
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

    // merge PDF facturas
    public function setPeriodoPDF($periodo, $facturas){
        
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
        $filePath = config('constants.folder_periodos') . 'periodo-'.$filename.'.pdf';
        
        // store pdf
        Storage::disk('public')->put($filePath, $createdPdf);

        return true;

    }

    // Genero el archivo de Pago Mis Cuentas
    public function setPeriodoPMC(Request $request, $mes, $ano){

        // variables
        $i  = 1;
        $ln = "\n";
        $periodo                = $mes.'/'.$ano;
        $facturas               = Factura::where('periodo', $periodo)->get();
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
            $nro_cliente = str_pad($factura->nro_cliente,5,'0',STR_PAD_LEFT);
            $nro_cliente = str_pad($nro_cliente,19,' ',STR_PAD_RIGHT);

            // nro factura 
            $nro_factura = str_pad($factura->nro_factura,20,'0',STR_PAD_LEFT);
            
            // fecha de vencimiento
            $dt = Carbon::parse($factura->primer_vto_fecha);
            $primer_vto_fecha = $dt->year . str_pad($dt->month,2,'0',STR_PAD_LEFT)  . str_pad($dt->day,2,'0',STR_PAD_LEFT);
            
            // format importes
            $factura_importe = number_format($factura->importe_total, 2, '.', '');
            $factura_importe = explode('.', $factura_importe);
            $factura_importe_entero  = str_pad($factura_importe[0],9,'0',STR_PAD_LEFT);
            $factura_importe_decimal = str_pad($factura_importe[1],2,'0',STR_PAD_RIGHT);

            // complemento
            $ceros   = str_pad(0,38,'0',STR_PAD_RIGHT);
            $filer1  = str_pad(0,19,'0',STR_PAD_RIGHT);

            //  mensaje
            $mensaje = 'ABONO MES DE ' . $this->meses[$mes];
            $mensaje = str_pad($mensaje,40,' ',STR_PAD_RIGHT);
        
            // pantalla
            $pantalla = str_pad($pantalla,15,' ',STR_PAD_RIGHT);
            
            // codigo
            $codigo   = $this->getCodigoPago($factura->importe_total, $factura->primer_vto_fecha, $factura->nro_cliente, $factura_nro_punto_vta, $factura_nro_factura);
            $codigo   = str_pad($codigo,60,' ',STR_PAD_RIGHT);
            // $codigo   = str_pad($codigo,48,' ',STR_PAD_RIGHT); //60 - 12 que se agregaron

            // ultimo filler
            $filler2  = str_pad(0,29,'0',STR_PAD_RIGHT);
            
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
        $fillerHeader   = str_pad(0,264,'0',STR_PAD_LEFT);
        $fileHeader     = $registroHeader . $prismaHeader . $codEmpresa . $fechaHeader . $fillerHeader;

        // file footer ----------------------------------------------------------------------
        $registroFooter         = '94008699';
        $facturasTotalCantidad  = str_pad($facturasTotalCantidad,7,'0',STR_PAD_LEFT);
        $fillerFooter1          = str_pad(0,7,'0',STR_PAD_RIGHT);
        $importeEntero          = str_pad($facturasTotalImporte[0],14,'0',STR_PAD_LEFT);
        $importeEnteroDecimal   = str_pad($facturasTotalImporte[1],2,'0',STR_PAD_RIGHT);
        $fillerFooter2          = str_pad(0,234,'0',STR_PAD_RIGHT);
        $fileFooter             = $ln . $registroFooter . $fechaHeader . $facturasTotalCantidad . $fillerFooter1 . $importeEntero . $importeEnteroDecimal . $fillerFooter2;

        // file content ----------------------------------------------------------------------
        $fileContent        = $detalle;


        // output
        $output = $fileHeader . $fileContent . $fileFooter;

        // compongo el nombre
        $filename = 'FAC'. $codEmpresa . '.' . date('dmy');
        $filePath = config('constants.folder_pmc') .$filename;
        
        // store pdf
        Storage::disk('public')->put($filePath, $output);

        // return
        return response()->download($filePath);

    }

    public function getFacturasPeriodoPDFPath($request)
    {
            // path del pdf
            $filename = str_replace('/', '-', $request->periodo);
            $filename = $request->root().'/'.config('constants.folder_periodos').'periodo-'.$filename.'.pdf';

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
        $filename = $factura->talonario->nro_punto_vta.'-'.$factura->nro_factura;
        
        // genero el path del pdf
        $filename = $request->root().'/'.config('constants.folder_facturas') . 'factura-'.$filename.'.pdf';

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
        return back()->with(['status' => 'success', 'message' => 'Se ha enviado la factura al correo electrónico '.$factura->cliente->email.'.', 'icon' => 'fa-smile-o']);
    }

    public function sendEmailFacturasPeriodo(Request $request, $mes, $ano)
    {

        $periodo = $mes.'/'.$ano;
        $facturas = Factura::where('periodo', $periodo)->get();

        foreach ($facturas as $factura) {

            // send Email
            $this->sendEmailFactura($request, $factura);
        }
        
        // return $facturas;
        return redirect('/admin/period')->with(['status' => 'success', 'message' => 'Se han enviado las facturas del período '.$periodo.'.', 'icon' => 'fa-smile-o']);
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

            // send Mail
            Mail::send('email.factura', ['factura' => $factura], function ($message) use ($factura)
             {
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
        $facturas = Factura::all();

        foreach ($facturas as $factura) {

            $factura->talonario;
            $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);
            
            $factura->nro_factura = $this->zerofill($factura->nro_factura);
            $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);

            $factura->cliente;
            $factura->cliente->nombre_apellido = $factura->cliente->firstname.' '.$factura->cliente->lastname;


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
            $filename = $factura->talonario->nro_punto_vta.'-'.$factura->nro_factura;
            $factura->pdf = $request->root().'/'.config('constants.folder_facturas') . 'factura-'.$filename.'.pdf';



        }

        // return $facturas;
        return Datatables::of($facturas)->make(true);
    }



    //----------------------------------------------------------------------------------------------//
    public function balance()
    {
        
        $facturas = Factura::orderBy('id', 'desc')->get();

        $periodos = [];
        foreach ($facturas as $factura) {

            if(!in_array($factura->periodo, $periodos)){
                $periodos[] = $factura->periodo;
            }

        }

        // return $periodos;

        return View::make('balance.list')->with(['periodos' => $periodos]);

    }

    public function balanceSearch(Request $request)
    {
        
        // get balance
        $response = $this->getBalance($request);

        if (!is_null($response) && !empty($response)) {
            
            // generate PDF
            $this->generateBalancePDF($response);

            // return balance
            return $response;
            
        }else{

            return 'null';
        
        }

    }

    public function getBalance($request)
    {

        // get facturas
        $facturas = Factura::orderBy('id', 'ASC');
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
                }else{
                    $facturasArray[$factura->periodo]['facturas_pagadas'] = array_key_exists('facturas_pagadas', $facturasArray[$factura->periodo]) ? $facturasArray[$factura->periodo]['facturas_pagadas'] : 0;
                    $facturasArray[$factura->periodo]['facturas_adeudadas'] = array_key_exists('facturas_adeudadas', $facturasArray[$factura->periodo]) ? $facturasArray[$factura->periodo]['facturas_adeudadas'] + 1 : 1;
                    $facturasArray[$factura->periodo]['importe_pagado'] = array_key_exists('importe_pagado', $facturasArray[$factura->periodo]) ? $facturasArray[$factura->periodo]['importe_pagado'] : 0;
                }
                
            }
            
            return $facturasArray;

        }else{

            return null;

        }
    }

    public function generateBalancePDF($response)
    {

        if (!is_null($response) && !empty($response)) {
            
            try {
                
                $filename = 'Balance de pagos ReDin';
                
                $pdf = PDF::loadView('pdf.balance', ['response' => $response]);
                $pdf->save(config('constants.folder_balance') . $filename . '.pdf');
                // return $pdf->stream(config('constants.folder_balance') . $filename . '.pdf');

                return $filename;

            } catch(\Exception $e) {
                  
              return $e;
             
            }
        
        }else{
    
            return null;

        }

    }

    public function getBalancePDF(Request $request)
    {

        try {

            $filename = 'Balance de pagos ReDin';

            $filename = config('constants.folder_balance') . $filename . ".pdf";

            return response()->file($filename);

        } catch(\Exception $e) {
              
            return View::make('errors.404');
        
        }


    }


    //----------------------------------------------------------------------------------------------//

    // email temp
    public function tempFacturasEmail(Request $request){

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

        $facturas = Factura::all();
        foreach ($facturas as $factura) {
            $factura->talonario;
            $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);
            
            $factura->nro_factura = $this->zerofill($factura->nro_factura);
            $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);

            $factura->cliente;

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
    function tempMergePDF(Request $request){

        $periodo = '01/2018';
        $facturas = Factura::where('periodo', $periodo)->get();
        
        foreach ($facturas as $factura) {
            // $filenames[] = $this->getFacturaPDFPath($request, $factura);

            $factura->talonario;        
            $factura->talonario->nro_punto_vta  = $this->zerofill($factura->talonario->nro_punto_vta, 4);
            $factura->nro_factura               = $this->zerofill($factura->nro_factura);
            
            // compongo el nombre 
            $filename = $factura->talonario->nro_punto_vta.'-'.$factura->nro_factura;
            
            // genero el path del pdf
            $filenames[] = config('constants.folder_facturas') . 'factura-'.$filename.'.pdf';


            
        }

        // return $filenames;
        
        $merger = new Merger;
        $merger->addIterator($filenames);
        $createdPdf = $merger->merge();

        $filename = str_replace('/', '-', $periodo);
        $filePath = config('constants.folder_periodos') . 'periodo-'.$filename.'.pdf';
        Storage::disk('public')->put($filePath, $createdPdf);

        return 1;

    }

}
