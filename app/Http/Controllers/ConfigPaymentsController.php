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

use App\PagosConfig;

class ConfigPaymentsController extends Controller
{
    

    public function __construct()
    {
        // $this->tipo = ['Internet', 'Telefonía', 'Televisión'];
    }

    public function create()
    {
        
        $pagosConfig = PagosConfig::find(1);
        // return $pagosConfig;

        return View::make('config_payments.edit')->with(['pagosConfig' => $pagosConfig]);

    }

    public function store(Request $request)
    {
        
        //-- VALIDATOR START --//
        $rules = array(
            'max_cuotas' => 'required|numeric|min:1|max:999',
            'tasa'       => ['regex:/^-?(?:0|[1-9]\d{0,2}(?:,?\d{3})*)(?:\.\d+)?$/'],
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        $pagosConfig = PagosConfig::find(1) ? PagosConfig::find(1) : new PagosConfig;
        $pagosConfig->max_cuotas  = $request->max_cuotas;
        $pagosConfig->tasa = $this->floatvalue($request->tasa);
        
        if ($pagosConfig->save()) {

            return redirect('/admin/config/payments')->with(['status' => 'success', 'message' => 'El Plan de Pagos fué modificado.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/config/payments')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }

    }

   
    public function floatvalue($val){
        $val = str_replace(",",".",$val);
        $val = preg_replace('/\.(?=.*\.)/', '', $val);
        return floatval($val);
    }
}
