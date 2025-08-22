<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

use View;
use Validator;
use Yajra\Datatables\Datatables;
// use Intervention\Image\ImageManagerStatic as Image;

use PDF;
use Carbon\Carbon;

use App\User;
use App\ImportCe;
use App\UserOld;
use App\Factura;
// use App\ServicioUsuario;


class CobroexpressController extends Controller
{
    

    public function __construct()
    {
        // $this->tipo = ['Internet', 'Telefonía', 'Televisión'];
    }

    public function index()
    {



        return View::make('cobroexpress.list');
    }

    public function import(Request $request)
    {

        $files = $request->file('files');
        $row_imported = 0;

        if ($files) {

            // echo '<pre>';
            foreach($files as $file)
            {

                // print_r($file);
                // echo $filename = $file->getClientOriginalName() . '<br>';
                // echo $file->getClientOriginalName();
                // echo $file->getRealPath();
                // echo $file->getClientOriginalExtension();
                // echo $file->getSize();
                // echo $file->getMimeType();
               
                // 2017
                // 485 000000000 0000 00000334 000000301280002400063632018011019410001 
                // 485 000000000 0000 00000070 000000550000002060041632017081210590001

                // 2018
                //     cod. cli | nro factura | importe   |            | fecha        | sec.
                // 485 000000389 0001 00001708 00000068351 000240006363 2018 07 30 1611 0001
                // 485 000000308 0001 00001629 00000071549 000240006363 2018 07 30 1912 0002
                // 485 000000389 0001 00002115 00000097952 000240006363 2018 07 30 1611 0003
                // 485 000000154             0 00000056121 000240006363 2018 05 02 0831 0001
                // 485 000000154             0 00000056121 000240006363 2018 05 02 0831 0001

                // obtengo el nombre del archivo
                $filename = $file->getClientOriginalName();

                // verifico la extension del archivo y el contenido
                if ($file->getMimeType() == 'text/plain' && $file->getClientOriginalExtension() == 'txt') {

                    // obtengo el contenido del archivo
                    $rows = file($file->getRealPath());

                    // recorro cada linea del archivo
                    foreach ($rows as $row) {

                        // clear row
                        $row = trim($row);

                        // reemplazo cualquier caracter distinto a numerico con 0
                        $row = preg_replace('/[^0-9]/','0',$row);

                        // set user false to get info after
                        $user = false;

                        // verifico la longitud de la cadena
                        if (strlen($row) == 63) { //  control para permitir solo numeros ctype_digit($row)

                            // verifico si esa linea ya se encuentra importada
                            $linea = ImportCe::where('codigo', $row)->first();
                            
                            if (!$linea) {

                                $cod_empresa     = substr($row, 0, 3);
                                // $cod_cliente_old = substr($row, 3, 21);
                                $importe         = substr($row, 25, 8).'.'.substr($row, 33, 2);
                                $fecha           = substr($row, 47, 12);
                                $nro_sucursal    = 0;
                                $nro_factura     = 0;
                                $cod_cliente_old = 0;

                                // descomposicion de la fecha
                                $fecha_ano = substr($fecha, 0, 4);
                                $fecha_mes = substr($fecha, 4, 2);
                                $fecha_dia = substr($fecha, 6, 2);

                                // echo $fecha_dia.'-'.$fecha_mes.'-'.$fecha_ano.'<br>';

                                // verifico a que tipo de archivo pertenece
                                if ( ($fecha_ano < 2018) || ($fecha_ano == 2018 && $fecha_mes == 01) ) {
                                    
                                    // obtengo el codigo del cliente antiguo
                                    $cod_cliente_old = substr($row, 3, 21);

                                    // obtengo el user antiguo
                                    $userOld = UserOld::where('nro_cliente', $cod_cliente_old)->first();
                                
                                    // verifico si existe el nro de cliente en la tabla de usuarios antiguos
                                    if($userOld){
                                        $user = User::where('dni', $userOld->dni)->first();
                                    }

                                }else{

                                    if ($fecha_ano == 2018 && $fecha_mes >= 02 && $fecha_mes <= 04) {
                                    
                                        // obtengo los datos del codigo del cliente
                                        $cod_cliente = substr($row, 3, 21);

                                        // verifico si existe el nro de cliente en la tabla de usuarios
                                        $user = User::where('nro_cliente', $cod_cliente)->first();

                                    } elseif ( ($fecha_ano >= 2018 && $fecha_mes >= 05) || $fecha_ano > 2018 ) {

                                        // obtengo los datos del codigo del cliente
                                        $cod_cliente = substr($row, 3, 9);

                                        // verifico si existe el nro de cliente en la tabla de usuarios
                                        $user = User::where('nro_cliente', $cod_cliente)->first();

                                        $nro_sucursal = substr($row, 12, 4);
                                        $nro_factura  = substr($row, 16, 8);

                                        // fix para archivos defectuosos donde el nro de factura esta incompleto
                                        // $nro_sucursal = $nro_sucursal != '' ? $nro_sucursal : 0;
                                        // $nro_factura  = $nro_factura != '' ? $nro_factura : 0;

                                    }
                                }

                                // verifico si existe el dni del usuario antiguo en la tabla de usuarios actuales
                                if($user){

                                    $import = new ImportCe;
                                    $import->filename        = $filename;
                                    $import->codigo          = $row;
                                    $import->cod_empresa     = $cod_empresa;
                                    $import->nro_sucursal    = $nro_sucursal;
                                    $import->nro_factura     = $nro_factura;
                                    $import->cod_cliente     = $user->nro_cliente;
                                    $import->cod_cliente_old = $cod_cliente_old;
                                    $import->importe         = $importe;
                                    $import->fecha           = Carbon::createFromFormat('YmdHi', $fecha);
                                    $import->save();

                                    // incremento el contador
                                    $row_imported++;

                                    $this->setPagoFacturaCE($import);

                                }

                            }

                        }

                    }

                }

            }

            return back()->with(['status' => 'success', 'message' => 'Se imputaron '.$row_imported.' pagos.', 'icon' => 'fa-smile-o']);

        }else{

            return redirect('/admin/cobroexpress')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);

        }
        
    }

    public function cobroexpressSearch(Request $request)
    {
        
        // set limit to execute a long request
        set_time_limit(10000);

        // get balance
        $response = $this->getCobroexpress($request);

        if (!is_null($response) && !empty($response)) {
            
            // generate PDF
            $this->generateCobroexpressPDF($response);
            
            // generate XLS 
            $this->generateCobroexpressXLS($response);

            // return balance
            return $response;
            
        }else{

            return 'null';
        
        }

    }

    public function getCobroexpress($request)
    {

        // ordeno los resultados
        $imports = ImportCe::orderBy('fecha', 'DESC');

        // get registros
        if ($request->user_id != '') {
            $cliente = User::find($request->user_id);
            $imports = $imports->where('cod_cliente', $cliente->nro_cliente);
        }
        $imports = $imports->get();
                        // ->toSql();

        // return $imports;

        if (count($imports)) {

            $importArray = [];
            
            // compongo el resultado
            foreach ($imports as $import) {
                
                // fill zero
                $import->nro_factura = $this->zerofill($import->nro_factura);
                $import->nro_sucursal = $this->zerofill($import->nro_sucursal, 4);
                $import->cod_cliente = $this->zerofill($import->cod_cliente, 5);

                $importArray[$import->cod_cliente]['cod_cliente'] = $import->cod_cliente;
                $importArray[$import->cod_cliente]['detalle_cliente'] = $request->user_id != '' ? $cliente : User::where('nro_cliente', $import->cod_cliente)->first();
                
                // $importArray[$import->cod_cliente]['detalle_pagos'][$import->fecha]['id'] = $import->id;
                $importArray[$import->cod_cliente]['detalle_pagos'][$import->fecha][$import->id]['nro_sucursal'] = $import->nro_sucursal;
                $importArray[$import->cod_cliente]['detalle_pagos'][$import->fecha][$import->id]['nro_factura'] = $import->nro_factura;
                $importArray[$import->cod_cliente]['detalle_pagos'][$import->fecha][$import->id]['importe'] = $import->importe;
                $importArray[$import->cod_cliente]['detalle_pagos'][$import->fecha][$import->id]['fecha'] = Carbon::parse($import->fecha)->format('d/m/Y');
                
                $importArray[$import->cod_cliente]['total_pagos'] = array_key_exists('total_pagos', $importArray[$import->cod_cliente]) ? $importArray[$import->cod_cliente]['total_pagos'] + $import->importe : $import->importe;
            
            }
            
            // return $importArray;

            // ordeno los pagos por fecha descendente
            foreach ($importArray as $itemCliente) {
                krsort($itemCliente['detalle_pagos']);
            }
            
            return $importArray;

        }else{

            return null;

        }
    }

    public function generateCobroexpressPDF($response)
    {

        if (!is_null($response) && !empty($response)) {
            
            try {
                
                $filename = 'cobroexpress';
                
                $pdf = PDF::loadView('pdf.cobroexpress', ['response' => $response]);
                $pdf->save(config('constants.folder_cobroexpress_pdf') . $filename . '.pdf');
                // return $pdf->stream(config('constants.folder_cobroexpress_pdf') . $filename . '.pdf');

                return $filename;

            } catch(\Exception $e) {
                  
              return $e;
             
            }
        
        }else{
    
            return null;

        }

    }

    public function generateCobroexpressXLS($response)
    {   

        if (!is_null($response) && !empty($response)) {
            
            try {

                $filename = 'cobroexpress';
                
                \Excel::create($filename, function($excel) use($response) {

                    $excel->sheet('Cobro Express', function($sheet) use($response) {

                        $total_general = 0;
                        
                        // add headers
                        $sheet->appendRow(array(
                            'Cod. Cliente',
                            'Nombre y Apellido',
                            'Fecha de Pago',
                            'Factura',
                            'Importe'
                        ));

                        // ksort($response);
                        
                        foreach ($response as $key => $registro):

                            // add pagos
                            foreach ($registro['detalle_pagos'] as $key => $fechaPago):
                                
                                foreach ($fechaPago as $key => $pago):

                                    // formateo los campos
                                    $cod_cliente = $this->zerofill($registro['cod_cliente'], 5);                                
                                    $factura = '(SIN ESPECIFICAR)';

                                    if ($pago['nro_factura'] > 0 && $pago['nro_sucursal'] > 0){
                                        
                                        $nro_factura = $this->zerofill($pago['nro_factura']);
                                        $nro_sucursal = $this->zerofill($pago['nro_sucursal'], 4);

                                        $factura = $nro_sucursal.' - '.$nro_factura;

                                    }

                                    // agrego la linea al xls
                                    $sheet->appendRow(array(
                                        $cod_cliente, 
                                        $registro['detalle_cliente']['firstname'] . ' ' . $registro['detalle_cliente']['lastname'],
                                        $pago['fecha'],
                                        $factura, 
                                        $pago['importe']
                                    ));
                            
                                endforeach;

                            endforeach;

                            // add total de pagos del cliente
                            $sheet->appendRow(array(
                                'Total',
                                '',
                                '',
                                '',
                                $registro['total_pagos']
                            )); 

                            $total_general = $total_general + $registro['total_pagos'];

                        endforeach;

                        // add total general
                        $sheet->appendRow(array(
                            'Total General',
                            '',
                            '',
                            '',
                            $total_general
                        )); 
                    });

                })
                ->store('xls', config('constants.folder_cobroexpress_xls'));
                // ->export('xls');
            
                return $filename;

            } catch(\Exception $e) {
                  
              return $e;
             
            } 
        }               

    }

    public function getCobroexpressPDF(Request $request)
    {

        try {

            $filename = 'cobroexpress';

            $filename = config('constants.folder_cobroexpress_pdf') . $filename . ".pdf";

            return response()->file($filename);

        } catch(\Exception $e) {
              
            return View::make('errors.404');
        
        }


    }

    public function getCobroexpressXLS(Request $request)
    {

        try {

            $filename = 'cobroexpress';

            $filename = config('constants.folder_cobroexpress_xls') . $filename . ".xls";

            return response()->download($filename);

        } catch(\Exception $e) {
              
            return View::make('errors.404');
        
        }


    }

    public function setPagoFacturaCE($pago){

        $forma_pago = 3; // 3 -> Cobro Express

        if ($pago->nro_factura > 0 && $pago->nro_sucursal > 0) {

            $factura = Factura::where('nro_factura', $pago->nro_factura)
                              ->where('talonario_id', $pago->nro_sucursal)->first();

            if ($factura) {
                    
                $factura->fecha_pago   = $pago->fecha;
                $factura->importe_pago = $pago->importe;
                $factura->forma_pago   = $forma_pago; 
                $factura->lote         = $pago->id; 
                $factura->save();

            }
        }

        return 1;

    }

    public function zerofill($num, $zerofill = 8)
    {

        return str_pad($num, $zerofill, '0', STR_PAD_LEFT);

    }

    public function floatvalue($val){
        $val = str_replace(",",".",$val);
        $val = preg_replace('/\.(?=.*\.)/', '', $val);
        return floatval($val);
    }
}
