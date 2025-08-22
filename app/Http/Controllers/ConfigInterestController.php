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

use App\Interes;

class ConfigInterestController extends Controller
{
    

    public function __construct()
    {
        $this->tipo = ['Internet', 'Telefonía', 'Televisión'];
    }

    public function create()
    {
        
        $interes = Interes::find(1);
        // return $interes;

        return View::make('config_interest.edit')->with(['interes' => $interes]);

    }

    public function store(Request $request)
    {
        
        //-- VALIDATOR START --//
        $rules = array(
            'primer_vto_dia'     => 'required|numeric|min:1|max:31',
            // 'primer_vto_tasa'    => ['required', 'regex:/^-?(?:0|[1-9]\d{0,2}(?:,?\d{3})*)(?:\.\d+)?$/'],
            'segundo_vto_dia'    => 'numeric|min:1|max:31',
            'segundo_vto_tasa'   => ['regex:/^-?(?:0|[1-9]\d{0,2}(?:,?\d{3})*)(?:\.\d+)?$/'],
            'tercer_vto_tasa'    => ['regex:/^-?(?:0|[1-9]\d{0,2}(?:,?\d{3})*)(?:\.\d+)?$/'],
        

        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        $interes = Interes::find(1) ? Interes::find(1) : new Interes;
        $interes->primer_vto_dia  = $request->primer_vto_dia;
        // $interes->primer_vto_tasa  = $this->floatvalue($request->primer_vto_tasa);
        if($request->segundo_vto_dia != ''){$interes->segundo_vto_dia = $request->segundo_vto_dia;}
        if($request->segundo_vto_tasa != ''){$interes->segundo_vto_tasa = $this->floatvalue($request->segundo_vto_tasa);}
        if($request->tercer_vto_tasa != ''){$interes->tercer_vto_tasa = $this->floatvalue($request->tercer_vto_tasa);}
        
        if ($interes->save()) {

            return redirect('/admin/config/interests')->with(['status' => 'success', 'message' => 'Los Intereses fueron modificados.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/config/interests')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }

    }

   
    public function floatvalue($val){
        $val = str_replace(",",".",$val);
        $val = preg_replace('/\.(?=.*\.)/', '', $val);
        return floatval($val);
    }
}
