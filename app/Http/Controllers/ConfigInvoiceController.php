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

use App\Talonario;
// use App\ServicioUsuario;


class ConfigInvoiceController extends Controller
{
    

    public function __construct()
    {
        $this->tipo = ['Internet', 'Telefonía', 'Televisión'];
    }

    public function index()
    {
        return View::make('config_invoice.list');
    }

    public function getList()
    {
        
        $talonarios = Talonario::all();


        foreach ($talonarios as $talonario) {
            
            $talonario->nro_punto_vta  =  $this->zerofill($talonario->nro_punto_vta, 4);
            $talonario->nro_inicial  =  $this->zerofill($talonario->nro_inicial, 8);
            $talonario->nro_cai  =  $this->zerofill($talonario->nro_cai, 14);

        }
        
        return Datatables::of($talonarios)->make(true);
    }

    public function create()
    {

        return View::make('config_invoice.create');
    }

    public function store(Request $request)
    {
        
        // return $request->all();      

        //-- VALIDATOR START --//
        $rules = array(
            'letra'            => 'required|min:1|max:1|unique:talonarios,letra',
            'nombre'           => 'required|min:3|max:100|unique:talonarios,nombre',
            'nro_punto_vta'    => 'required|numeric|min:1',
            'nro_inicial'      => 'required|numeric|min:1',
            'nro_cai'          => 'required|numeric|min:1',
            'nro_cai_fecha_vto'    => 'required|date_format:d/m/Y',
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//

       
        $talonario = new Talonario;
        $talonario->letra  = strtoupper($request->letra);
        $talonario->nombre  = $request->nombre;
        $talonario->nro_punto_vta  = $request->nro_punto_vta;
        $talonario->nro_inicial  = $request->nro_inicial;
        $talonario->nro_cai  = $request->nro_cai;
        $talonario->nro_cai_fecha_vto  = Carbon::createFromFormat('d/m/Y', $request->nro_cai_fecha_vto);


        if ($talonario->save()) {
            
            return redirect('/admin/config/invoice')->with(['status' => 'success', 'message' => 'El Talonario fué creado.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/config/invoice')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }


    public function edit($id)
    {
        
        $talonario = Talonario::find($id);
        // return $talonario;

        $talonario->nro_punto_vta  =  $this->zerofill($talonario->nro_punto_vta, 4);
        $talonario->nro_inicial  =  $this->zerofill($talonario->nro_inicial, 8);
        $talonario->nro_cai  =  $this->zerofill($talonario->nro_cai, 14);
        $talonario->nro_cai_fecha_vto = Carbon::parse($talonario->nro_cai_fecha_vto)->format('d/m/Y');

        return View::make('config_invoice.edit')->with(['talonario' => $talonario]);

    }

    public function update(Request $request, $id)
    {
        
        //-- VALIDATOR START --//
        $rules = array(
            'letra'            => 'required|min:1|max:1|unique:talonarios,letra,'.$id,
            'nombre'           => 'required|min:3|max:100|unique:talonarios,nombre,'.$id,
            'nro_punto_vta'    => 'required|numeric|min:1',
            'nro_inicial'      => 'required|numeric|min:1',
            'nro_cai'          => 'required|numeric|min:1',
            'nro_cai_fecha_vto'    => 'required|date_format:d/m/Y',
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        $talonario = Talonario::find($id);
        $talonario->letra  = strtoupper($request->letra);
        $talonario->nombre  = $request->nombre;
        $talonario->nro_punto_vta  = $request->nro_punto_vta;
        $talonario->nro_inicial  = $request->nro_inicial;
        $talonario->nro_cai  = $request->nro_cai;
        $talonario->nro_cai_fecha_vto  = Carbon::createFromFormat('d/m/Y', $request->nro_cai_fecha_vto);
        
        if ($talonario->save()) {

            return redirect('/admin/config/invoice')->with(['status' => 'success', 'message' => 'El Talonario fué modificado.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/config/invoice')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }

    }    

    public function zerofill($num, $zerofill = 8)
    {
        return str_pad($num, $zerofill, '0', STR_PAD_LEFT);
    }

}