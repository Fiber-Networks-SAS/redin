<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Storage;

use View;
use Validator;
use Yajra\Datatables\Datatables;
// use Intervention\Image\ImageManagerStatic as Image;
// use Carbon\Carbon;

use App\Servicio;
use App\ServicioUsuario;


class ServiceController extends Controller
{
    

    public function __construct()
    {
        $this->tipo = ['Internet', 'Telefonía', 'Televisión'];
    }

    public function index()
    {
        return View::make('servicio.list');
    }

    public function getList()
    {
        
        $servicios = Servicio::all();

        // $movimientos = Movimiento::orderBy('id','DESC')->get();


        foreach ($servicios as $servicio) {
            
            $servicio->tipo  =  $this->tipo[$servicio->tipo];

            $servicio->abono_mensual = number_format($servicio->abono_mensual, 2); 
            $servicio->abono_proporcional = number_format($servicio->abono_proporcional, 2); 
            $servicio->costo_instalacion = number_format($servicio->costo_instalacion, 2); 
        }
        
        return Datatables::of($servicios)->make(true);
    }

    public function create()
    {

        return View::make('servicio.create');
    }

    public function store(Request $request)
    {
        
        // return $request->all();      

        //-- VALIDATOR START --//
        $rules = array(
            'nombre'             => 'required|min:3|max:100|unique:servicios,nombre',
            'tipo'               => 'required',
            'abono_mensual'      => ['required', 'regex:/^-?(?:0|[1-9]\d{0,2}(?:,?\d{3})*)(?:\.\d+)?$/'],
            'abono_proporcional' => ['regex:/^-?(?:0|[1-9]\d{0,2}(?:,?\d{3})*)(?:\.\d+)?$/'],
            'costo_instalacion'  => ['regex:/^-?(?:0|[1-9]\d{0,2}(?:,?\d{3})*)(?:\.\d+)?$/'],
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//

        
        $servicio = new Servicio;
        $servicio->nombre  = $request->nombre;
        $servicio->tipo  = $request->tipo;
        $servicio->abono_mensual  = $this->floatvalue($request->abono_mensual);
        $servicio->abono_proporcional  = $this->floatvalue($request->abono_proporcional);
        $servicio->costo_instalacion  = $this->floatvalue($request->costo_instalacion);
        $servicio->detalle  = $request->detalle;
        $servicio->status = $request->has('status') ? 1 : 0;

        if ($servicio->save()) {
            
            return redirect('/admin/services')->with(['status' => 'success', 'message' => 'El Servicio fué creado.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/services')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }


    public function edit($id)
    {
        
        $servicio = Servicio::find($id);
        // return $servicio;

        $servicio->abono_mensual = number_format($servicio->abono_mensual, 2); 
        $servicio->abono_proporcional = number_format($servicio->abono_proporcional, 2); 
        $servicio->costo_instalacion = number_format($servicio->costo_instalacion, 2); 

        return View::make('servicio.edit')->with(['servicio' => $servicio]);

    }

    public function update(Request $request, $id)
    {
        
        //-- VALIDATOR START --//
        $rules = array(
            'nombre'             => 'required|min:3|max:100|unique:servicios,nombre,'.$id,
            'tipo'               => 'required',
            'abono_mensual'      => ['regex:/^-?(?:0|[1-9]\d{0,2}(?:,?\d{3})*)(?:\.\d+)?$/'],
            'abono_proporcional' => ['regex:/^-?(?:0|[1-9]\d{0,2}(?:,?\d{3})*)(?:\.\d+)?$/'],
            'costo_instalacion'  => ['regex:/^-?(?:0|[1-9]\d{0,2}(?:,?\d{3})*)(?:\.\d+)?$/'],
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        $servicio = Servicio::find($id);
        $servicio->nombre  = $request->nombre;
        $servicio->tipo  = $request->tipo;
        $servicio->abono_mensual  = $this->floatvalue($request->abono_mensual);
        $servicio->abono_proporcional  = $this->floatvalue($request->abono_proporcional);
        $servicio->costo_instalacion  = $this->floatvalue($request->costo_instalacion);
        $servicio->detalle  = $request->detalle;
        $servicio->status = $request->has('status') ? 1 : 0;
        
        if ($servicio->save()) {

            // update client services table
            ServicioUsuario::where('servicio_id', $id)
                           ->where('pp_flag', 0)
                           ->update(['abono_mensual' => $servicio->abono_mensual, 
                                     'abono_proporcional' => $servicio->abono_proporcional, 
                                     'costo_instalacion' => $servicio->costo_instalacion
                                    ]);

            return redirect('/admin/services')->with(['status' => 'success', 'message' => 'El Servicio fué modificado.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/services')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }

    }    

    public function updateStatus(Request $request, $id)
    {
        
        $servicio = Servicio::find($id);
        $servicio->status = $request->status == 'true' ? 1 : 0;
        
        if ($servicio->save()) {

            // update client servvices table
            ServicioUsuario::where('servicio_id', $id)
                           ->update(['status' => $servicio->status]);


            return ['status' => 'success', 'message' => 'El Servicio fué modificado.', 'icon' => 'fa-smile-o'];

        }else{
            
            return ['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o'];
        
        }
    }

    public function getTable()
    {
        // render view
        return View::make('servicio.table');
    }

   
    public function floatvalue($val){
        $val = str_replace(",",".",$val);
        $val = preg_replace('/\.(?=.*\.)/', '', $val);
        return floatval($val);
    }
}
