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
use Carbon\Carbon;

use App\Cuota;
// use App\ServicioUsuario;


class ConfigDuesController extends Controller
{
    

    public function __construct()
    {
        // $this->tipo = ['Internet', 'Telefonía', 'Televisión'];
    }

    public function index()
    {
        return View::make('config_dues.list');
    }

    public function getList()
    {
        
        $cuotas = Cuota::all();

        return Datatables::of($cuotas)->make(true);
    }

    public function create()
    {

        return View::make('config_dues.create');
    }

    public function store(Request $request)
    {
        
        // return $request->all();      

        //-- VALIDATOR START --//
        $rules = array(
            // 'numero'  => 'required|numeric|min:1|max:99|unique:cuotas,numero',
            'numero'  => 'required|numeric|min:1|max:99',
            'interes' => ['regex:/^-?(?:0|[1-9]\d{0,2}(?:,?\d{3})*)(?:\.\d+)?$/'],
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//

       
        $cuota = new Cuota;
        $cuota->numero  = $request->numero;
        $cuota->interes  = $request->interes;

        if ($cuota->save()) {
            
            return redirect('/admin/config/dues')->with(['status' => 'success', 'message' => 'El Número de Cuota fué creado.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/config/dues')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }


    public function edit($id)
    {
        
        $cuota = Cuota::find($id);
        // return $cuota;

        return View::make('config_dues.edit')->with(['cuota' => $cuota]);

    }

    public function update(Request $request, $id)
    {
        
        //-- VALIDATOR START --//
        $rules = array(
            // 'numero'  => 'required|numeric|min:1|max:99|unique:cuotas,numero,'.$id,
            'numero'  => 'required|numeric|min:1|max:99',
            'interes' => ['regex:/^-?(?:0|[1-9]\d{0,2}(?:,?\d{3})*)(?:\.\d+)?$/'],
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        $cuota = Cuota::find($id);
        $cuota->numero  = $request->numero;
        $cuota->interes  = $request->interes;
        
        if ($cuota->save()) {

            return redirect('/admin/config/dues')->with(['status' => 'success', 'message' => 'El Número de Cuota fué modificado.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/config/dues')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }

    }    

    public function zerofill($num, $zerofill = 8)
    {
        return str_pad($num, $zerofill, '0', STR_PAD_LEFT);
    }

}