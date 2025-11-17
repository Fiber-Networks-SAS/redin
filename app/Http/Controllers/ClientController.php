<?php

namespace App\Http\Controllers;

// set_time_limit(600); //60 seconds = 1 minute (for migrate proccess)

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\Encryption\DecryptException;

use View;
use Validator;
use DateTime;
use Intervention\Image\ImageManagerStatic as Image;
use Yajra\Datatables\Datatables;

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
use App\Reclamo;
use App\Cuota;
use App\PagosConfig;
use App\PaymentPreference;
use App\PagoInformado;

// use Entrust;
use PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\MercadoPagoService;





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


class ClientController extends Controller
{
    

    public function __construct()
    {
        // $this->middleware('auth');

        // Check user permission 
        // $this->middleware('permission:crud-users');

        $this->tipo = ['Internet', 'Telefonía', 'Televisión'];
        $this->drop = ['En Pilar', 'En Domicilio', 'Sin Drop'];
        $this->forma_pago = [1 => 'Efectivo', 2 => 'Pago Mis Cuentas', 3 => 'Cobro Express', 4 => 'Mercado Pago', 6 => 'CBU/Transferencia']; // 4 => 'Tarjeta de Crédito', 5 => 'Depósito'

    }

    // clients --------------------------------------------------------------------------------------------------------

    public function register(Request $request)
    {
        //-- VALIDATOR START --//
        $rules = array(
            'dni'             => 'required|min:7|exists:users,dni',  // unique - verifica que no exista en la DB el dni
            // 'email'           => 'required|email|exists:users,email',  // unique - verifica que no exista en la DB el email
            'email'           => 'required|email|unique:users,email',  // unique - verifica que no exista en la DB el email
            // 'password'        => 'required|min:8',
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          // return back()->withInput()->withErrors($validator);
          return Redirect::to(URL::previous() . "#signup")->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//

        
        // cretate new user
        $user = User::where('dni', $request->dni)
                    // ->where('email', $request->email)
                    ->where('status', 1)
                    ->where('fecha_registro', null)
                    ->first();
                    // ->get();
                    // ->toSql();
    
        if ($user) {
    
            $user->password = Hash::make($request->dni);
            $user->remember_token = str_random(60);
            $user->email = $request->email;
            $user->remember_token = null;
            $user->fecha_registro = Carbon::now();


            // Save user data
            if ($user->save()) {

                // Create Token
                $remember_token_url = $request->root() . '/account/activate/' . $user->remember_token;
                
                try {

                    // send Mail
                    //Mail::send('email.account_activate', ['remember_token_url' => $remember_token_url], function ($message) use ($user)
                    // {
                    //    $message->from(config('constants.account_no_reply'), config('constants.title'))
                    //            ->to($user->email)
                    //            ->subject('Activa tu cuenta');
                    // });
		
                    
                } catch (\Exception $e) {
                    
                    $errorMessage = $e;
                }

                 return Redirect::to(URL::previous() . "#signup")->with(['status' => 'success', 'message' => 'Perfecto! verificá tu correo para activar tu cuenta.', 'icon' => 'fa-smile-o']);

            }else{
                
                return Redirect::to(URL::previous() . "#signup")->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
            
            }

        }else{
                
            return Redirect::to(URL::previous() . "#signup")->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    // show activate account template
    public function accountActivate(Request $request, $token = null)
    {
        
        // return $token;

        // get current datetime
        $now = Carbon::now();

        if ($token) {

            $user = User::where('remember_token', $token)->first();
            
            if ($user && $user->status == 1 && $user->fecha_registro == null) {

                if ($now->diffInHours($user->updated_at) <= config('constants.tokenTimeout')) {
                            
                    // $password = random_int(10000000, 99999999);  // random numerico por rango
                    // $password = mt_rand();                          // random numerico
                    $password = str_random(8);                   // random alfanumerico
                    $passwordHash = Hash::make($password);

                    // $user->status = 1;
                    $user->password = $passwordHash;
                    $user->remember_token = null;
                    $user->fecha_registro = $now;

                    if ($user->save()) {

                        return View::make('client.account_activate')->with(['rToken' => $token, 'password' => $password]);

                    }else{
                        
                        return View::make('errors.invalid_activation_code');
                    
                    }                    

                }else{

                    // token expired
                    return View::make('errors.expired_activation_code');
                }


            }else{

                // token invalid
                return View::make('errors.invalid_activation_code');
            }
        
        }else{
            
            // token missing
            return View::make('errors.404');
        
        }

    }

    public function profile()
    {

        $user = Auth::user();
        $user->firma_contrato = Carbon::parse($user->firma_contrato)->format('d/m/Y');
        $user->ont_funcionando = Carbon::parse($user->ont_funcionando)->format('d/m/Y');

        $user->provincia = $user->provincia ? $user->detalleProvincia->nombre : '';
        $user->localidad = $user->localidad ? $user->detalleLocalidad->nombre : '';

        return View::make('client.profile')->with(['user' => $user]);
    }    

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        return $this->update($request, $user->id);
    }

    public function update($request, $id, $route = null)
    {
        //-- VALIDATOR START --//
        $rules = array(
            // 'dni'               => 'required|numeric|min:7',
            // 'firstname'         => 'required|min:3|max:50',
            // 'lastname'          => 'required|min:3|max:50',
            'email'             => 'required|email|unique:users,email,'.$id,  // unique - verifica que no exista en la DB el email
            'password'          => 'min:8',
            'password_confirm'  => 'min:8|same:password',
            'picture'           => 'max:2000|mimes:jpg,jpeg,png,gif,bmp',
            );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        
        $user = User::find($id);
        // $user->dni  = $request->dni;
        // $user->firstname  = $request->firstname;
        // $user->lastname  = $request->lastname;
        $user->email = $request->email;
        
        // if($request->calle != ''){$user->calle = $request->calle;}
        // if($request->altura != ''){$user->altura = $request->altura;}
        // if($request->manzana != ''){$user->manzana = $request->manzana;}
        // if($request->provincia != ''){$user->provincia = $request->provincia;}
        // if($request->localidad != ''){$user->localidad = $request->localidad;}
        // if($request->cp != ''){$user->cp = $request->cp;}
        $user->tel1 = $request->tel1;
        $user->tel2 = $request->tel2;
        $user->autorizado_nombre = $request->autorizado_nombre;
        $user->autorizado_tel = $request->autorizado_tel;
        
        // password
        if($request->password != ''){
            $user->password = Hash::make($request->password);
        }

        // set filename to foto field
        if ($request->hasFile('picture')) {
            $imageName = time().'.'.$request->picture->getClientOriginalExtension();
            $img = Image::make($request->picture->getRealPath())
                    ->resize(150, 150)
                    ->save(public_path('pictures').'/'.$imageName);

            if ($img) {
                // delete old picture
                if ($user->picture) {
                    Storage::disk('public')->delete('pictures/'.$user->picture); // Storage::disk('public')->exists('..')
                }                

                // update field 
                $user->picture = $imageName;
            }
        }

        if ($user->save()) {

            if (!is_null($route)) {
            
                return redirect($route)->with(['status' => 'success', 'message' => 'Perfil modificado.', 'icon' => 'fa-smile-o']);
            
            }else{

                return back()->withInput()->with(['status' => 'success', 'message' => 'Perfil modificado.', 'icon' => 'fa-smile-o']);
                
            }

        }else{
            
            return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }


    public function forgotPassword()
    {

        return View::make('client.forgotPassword');
        
    }

    public function forgotPasswordPost(Request $request)
    {

        //-- VALIDATOR START --//
        $rules = array(
            'email'             => 'required|email|exists:users,email',  // unique - verifica que no exista en la DB el email
            );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        $currentDate = new DateTime();
        $errorMessage = '';

        // only client - avoid Laravel 5.3 compact() bug
        $user = User::with('roles')
                        ->where('email', '=', $request->email)
                        ->get()
                        ->filter(function ($user) {
                            return $user->roles->contains('name', 'client');
                        })
                        ->first();


        if (!$user) {
            return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        }


            
        $user->remember_token = str_random(60);

        // Save user data
        if ($user->save()) {

            $remember_token_url = $request->root() . '/reset/password/' .$user->remember_token;

            Mail::send('email.reset_password', ['remember_token_url' => $remember_token_url], function ($message) use ($user)
             {
                $message->from(config('constants.account_no_reply'), config('constants.title'))
                        ->to($user->email)
                        ->subject('Instrucciones para recuperar tu Contraseña');
            });


            return back()->withInput()->with(['status' => 'success', 'message' => 'Te hemos enviado un correo con las instrucciones', 'icon' => 'fa-smile-o']);

        } else {

            return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error y la contraseña no puede ser reestablecida.', 'icon' => 'fa-frown-o']);
        }

    }

    // show reset Password form
    public function resetPassword(Request $request, $token = null)
    {
     
        // return $token;

        // get current datetime
        $now = Carbon::now();

        if ($token) {

            // $users = User::where('remember_token', $token)->first();

            // only client - avoid Laravel 5.3 compact() bug
            $user = User::with('roles')
                            ->where('remember_token', '=', $token)
                            ->get()
                            ->filter(function ($user) {
                                return $user->roles->contains('name', 'client');
                            })
                            ->first();

            if ($user) {

                if ($now->diffInHours($user->updated_at) <= 1) {

                    return View::make('client.reset_password')->with(['rToken' => $token]);

                }else{

                    // token expired
                    return View::make('errors.expired_activation_code');
                }


            }else{

                // token invalid
                return View::make('errors.invalid_activation_code');
            }
        
        }else{
            
            // token missing
            return View::make('errors.404');
        
        }

    }

    public function updatePassword(Request $request)
    {
        //-- VALIDATOR START --//
        $rules = array(
            'etoken'            => 'required',
            'password'          => 'required|min:8',
            'password_confirm'  => 'required|min:8|same:password',
            );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        
        // $user = User::where('remember_token', $request->etoken)->first();

        $user = User::with('roles')
                        ->where('remember_token', '=', $request->etoken)
                        ->get()
                        ->filter(function ($user) {
                            return $user->roles->contains('name', 'client');
                        })
                        ->first();

        if ($user) {
            $user->password = Hash::make($request->password);
            $user->remember_token = null;

            if ($user->save()) {

                return View::make('client.changed_password');

            }else{
                
                return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
            
            }

        }else{
                
            return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    // mis facturas --------------------------------//
    public function myInvoice()
    {

        $user = Auth::user();

        return View::make('client.list_facturas')->with(['user' => $user]);
    }

    // lista de facturas de un periodo dado - AJAX
    public function getMyInvoiceList(Request $request)
    {

        $fecha_actual = Carbon::today();
        
        $user = Auth::user();
        $facturas = Factura::where('user_id', $user->id)->get();

        foreach ($facturas as $factura) {

            $factura->talonario;
            $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);
            
            $factura->nro_factura = $this->zerofill($factura->nro_factura);
            $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);

            $factura->cliente;
            $factura->cliente->nombre_apellido = $factura->cliente->firstname.' '.$factura->cliente->lastname;


            // verifico si puede mostrarse el boton de bonificacion
            $factura->fecha_actual = $fecha_actual;
            $factura->btn_bonificacion = $fecha_actual->lt(Carbon::parse($factura->primer_vto_fecha));
            $factura->btn_actualizar = $fecha_actual->gt(Carbon::parse($factura->segundo_vto_fecha));


            $factura->fecha_emision = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
            $factura->primer_vto_fecha = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
            $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');

            $factura->importe_subtotal = $factura->importe_subtotal; 
            $factura->importe_bonificacion = $factura->importe_bonificacion; 
            $factura->importe_total = $factura->importe_total; 

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
    
    public function getInvoiceDetail(Request $request, $id)
    {

        $user = Auth::user();        
        $factura = Factura::find($id);

        if ($factura->user_id == $user->id) {

            $factura->talonario;
            $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);
            
            $factura->nro_factura = $this->zerofill($factura->nro_factura);
            $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);

            $factura->cliente;        

            $factura->fecha_emision = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
            $factura->primer_vto_fecha = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
            $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');
            $factura->tercer_vto_fecha = Carbon::parse($factura->tercer_vto_fecha)->format('d/m/Y');

            $factura->importe_subtotal = $factura->importe_subtotal; 
            $factura->importe_bonificacion = $factura->importe_bonificacion; 
            $factura->importe_total = $factura->importe_total; 
            $factura->segundo_vto_importe = $factura->segundo_vto_importe; 
            $factura->tercer_vto_importe = $factura->tercer_vto_importe; 

            $factura->fecha_pago = $factura->fecha_pago ? Carbon::parse($factura->fecha_pago)->format('d/m/Y') : null;
            $factura->importe_pago = $factura->importe_pago; 
            $current_forma_pago = $factura->forma_pago;
            $factura->forma_pago = '';
            if ($factura->fecha_pago && isset($this->forma_pago[$current_forma_pago])) {
                $factura->forma_pago = $this->forma_pago[$current_forma_pago];
            }
            $factura->mail_date = $factura->mail_date ? Carbon::parse($factura->mail_date)->format('d/m/Y') : null;

            $detalles =  $factura->detalle;

            foreach ($detalles as $detalle) {
                $detalle->servicio;
            }            


            // genero los PDF's de las facturas individuales
            $filename = $factura->talonario->nro_punto_vta.'-'.$factura->nro_factura;
            $factura->pdf = $request->root().'/'.config('constants.folder_facturas') . 'factura-'.$filename.'.pdf';

            // Cargar notas de crédito y débito
            $notasCredito = $factura->notaCredito;
            $notasDebito = $factura->notaDebito;

            // return $factura;

            return View::make('client.view_factura')->with([
                'factura' => $factura,
                'notasCredito' => $notasCredito,
                'notasDebito' => $notasDebito
            ]);

        }else{
            
            return back()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    
    }

    public function getInvoiceDownload(Request $request, $id)
    {
        if (!is_null($id) && !empty($id)) {


            $user = Auth::user();        
            $factura = Factura::find($id);

            if ($factura->user_id == $user->id) {

                try {

                    // formateo los campos
                    $factura->talonario;        
                    $factura->talonario->nro_punto_vta  = $this->zerofill($factura->talonario->nro_punto_vta, 4);
                    $factura->nro_factura               = $this->zerofill($factura->nro_factura);
                    
                    // compongo el nombre 
                    $filename = $factura->talonario->nro_punto_vta.'-'.$factura->nro_factura;
                    $filename = config('constants.folder_facturas') . 'factura-'.$filename.'.pdf';

                    // Return PDF file to download
                    // return response()->download($filename);
                    
                    // Return PDF file 
                    return response()->file($filename);


                } catch(\Exception $e) {
                      
                    return View::make('errors.404');
                
                }

            }else{
            
                return back()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
            
            }

        }else{
    
            return null;

        }

    }

    // actualizacion de factura
    public function getInvoiceUpdate(Request $request, $id)
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

        return View::make('client.view_factura_actualizar')->with(['factura' => $factura]);
    
    }

    public function getEmailInvoiceDownload(Request $request, $token = null)
    {
        if (!is_null($token) && !empty($token)) {


            try {
    
                $id = decrypt($token);

                $factura = Factura::find($id);

                if ($factura) {

                    // formateo los campos
                    $factura->talonario;        
                    $factura->talonario->nro_punto_vta  = $this->zerofill($factura->talonario->nro_punto_vta, 4);
                    $factura->nro_factura               = $this->zerofill($factura->nro_factura);
                    
                    // compongo el nombre 
                    $filename = $factura->talonario->nro_punto_vta.'-'.$factura->nro_factura;
                    $filename = config('constants.folder_facturas') . 'factura-'.$filename.'.pdf';

                    // Return PDF file to download
                    // return response()->download($filename);
                    
                    // Return PDF file 
                    return response()->file($filename);

                }else{
                
                    return View::make('errors.404');
                
                }

            } catch(\Exception $e) {
                  
                return View::make('errors.404');
            
            }


        }else{
    
            return null;

        }

    }

    // mis reclamos --------------------------------//
    public function myClaims()
    {

        $user = Auth::user();

        return View::make('client.list_reclamos')->with(['user' => $user]);
    }

    // lista de facturas de un periodo dado - AJAX
    public function getMyClaimsList(Request $request)
    {

        $user = Auth::user();
        $reclamos = Reclamo::where('user_from', $user->id)
                           ->where('parent_id', 0)
                           ->get();

        // obtengo los datos del usuario
        foreach ($reclamos as $reclamo) {
            $reclamo->usuario;
            $reclamo->servicio;
            $reclamo->fecha = Carbon::parse($reclamo->created_at)->format('d/m/Y');
        }

        // return $reclamos;
        return Datatables::of($reclamos)->make(true);
    }

    public function MyClaimsCreate()
    {

        $user = Auth::user();

        foreach ($user->servicios as $servicio) {
            $servicio->servicio;
        }

        // return $user;
        return View::make('client.create_reclamo')->with(['user' => $user]);
    }

    public function MyClaimsStore(Request $request)
    {
        
        // return $request->all();      

        //-- VALIDATOR START --//
        $rules = array(
            'titulo'       => 'required',
            'servicio_id'  => 'required',
            'mensaje'      => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        // get current user
        $user = Auth::user();

        $reclamo = new Reclamo;
        $reclamo->user_from    = $user->id;
        $reclamo->user_to      = 0;
        $reclamo->servicio_id  = $request->servicio_id;
        $reclamo->titulo       = $request->titulo;
        $reclamo->mensaje      = $request->mensaje;
        $reclamo->leido_client = 1;
        $reclamo->leido_admin  = 0;

        if ($reclamo->save()) {
            
            return redirect('/my-claims')->with(['status' => 'success', 'message' => 'El Reclamo fué creado. En breve estaremos en contacto.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/my-claims')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    public function MyClaimsReply($id)
    {
        
        // obtengo el reclamo principal
        $reclamo = Reclamo::find($id);

        // marco como leido 
        $reclamo->leido_client  = 1;
        $reclamo->save();

        // obtengo los campos        
        $reclamo->usuario;
        $reclamo->servicio;
        $reclamo->fecha = Carbon::parse($reclamo->created_at)->format('d/m/Y H:i');

        // obtengo las respuestas
        $replysArray = [];
        if ($reclamo->replys) {
            $replys = explode(',', rtrim($reclamo->replys,','));
            foreach ($replys as $reply_id) {
                $reply = Reclamo::find($reply_id);
                $reply->usuario;
                $reply->servicio;
                $reply->fecha = Carbon::parse($reply->created_at)->format('d/m/Y H:i');
                
                $replysArray[] = $reply;
            }
        }
        $reclamo->replys = $replysArray;

        // return $reclamo;
        return View::make('client.reply_reclamos')->with(['reclamo' => $reclamo]);

    }

    public function MyClaimsReplyPost(Request $request, $id)
    {
        
        //-- VALIDATOR START --//
        $rules = array(
            'mensaje' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        // get current user
        $user = Auth::user();
        
        // obtengo el reclamo principal
        $reclamoMain = Reclamo::find($id);

        $reclamo = new Reclamo;
        $reclamo->user_from   = $user->id;
        $reclamo->user_to     = $reclamoMain->user_to;
        $reclamo->servicio_id = $reclamoMain->servicio_id;
        $reclamo->titulo      = '';
        $reclamo->mensaje     = $request->mensaje;
        $reclamo->parent_id   = $reclamoMain->id;
        // $reclamo->leido_client = 0;
        // $reclamo->leido_admin  = 0;


        if ($reclamo->save()) {

            // actualizo los campos del reclamo principal
            $reclamoMain->leido_client  = 1;
            $reclamoMain->replys  = $reclamoMain->replys . $reclamo->id.',';
            $reclamoMain->leido_admin  = 0;
            $reclamoMain->status  = 0; // lo marco como abierto
            $reclamoMain->save();
            
            return redirect('/my-claims')->with(['status' => 'success', 'message' => 'El Reclamo Nro. '.$reclamoMain->id.' fué respondido.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/my-claims')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }

    } 

    public function getListUnread()
    {
        $user = Auth::user();

        $reclamos = Reclamo::where('user_from', $user->id)
                           ->where('parent_id', 0)
                           ->where('leido_client', 0)
                           ->get();

        // obtengo los datos del usuario
        foreach ($reclamos as $reclamo) {

            // obtengo el nombre del ultimo admin que respondio
            $replys = explode(',', rtrim($reclamo->replys,','));
            $reply_id = end($replys);
            $reply = Reclamo::find($reply_id);

            $reclamo->usuario = $reply->usuario;
            $reclamo->fecha = Carbon::parse($reclamo->created_at)->format('d/m/Y H:i');
        }

        return $reclamos;
    }

    public function MyClaimsClose($id)
    {
        
        $reclamo = Reclamo::find($id);
        $reclamo->status  = 1;

        if ($reclamo->save()) {

            return redirect('/my-claims')->with(['status' => 'success', 'message' => 'El Reclamo Nro. '.$reclamo->id.' fué cerrado.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/my-claims')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }

    }


    // admin ----------------------------------------------------------------------------------------------------------

    public function index_admin()
    {
        


        return View::make('client_admin.list');
    }

    public function getList_admin()
    {

        // get clients - alternative approach to avoid Laravel 5.3 compact() bug
        $users = User::with('roles')
                     ->get()
                     ->filter(function ($user) {
                         return $user->roles->contains('name', 'client');
                     });

        foreach ($users as $user) {
            $user->drop = $user->drop != null ? $this->drop[$user->drop] : '';
            $user->total_facturas = count($user->facturas);
            $user->ont_funcionando = Carbon::parse($user->ont_funcionando)->format('d/m/Y');

        }                              

        return Datatables::of($users)->make(true);
    }

    public function view_admin($id)
    {
        
        $user = User::find($id);
        
        // $provincias = Provincia::all();
        // $localidades = Localidad::all();
        // $provincias = Provincia::where('id', 19)->get(); // 19 -> misiones
        // $localidades = Localidad::where('id', 17051)->get(); // 17051 -> POSADAS
        $user->firma_contrato = $user->firma_contrato != '' ? Carbon::parse($user->firma_contrato)->format('d/m/Y') : '';
        $user->ont_instalado = $user->ont_instalado != '' ? Carbon::parse($user->ont_instalado)->format('d/m/Y') : '';
        $user->ont_funcionando = $user->ont_funcionando != '' ? Carbon::parse($user->ont_funcionando)->format('d/m/Y') : '';
        $user->fecha_registro = $user->fecha_registro != '' ? Carbon::parse($user->fecha_registro)->format('d/m/Y') : '';
        $user->fecha_baja = $user->fecha_baja != '' ? Carbon::parse($user->fecha_baja)->format('d/m/Y') : '';


        $user->provincia = $user->provincia ? $user->detalleProvincia->nombre : '';
        $user->localidad = $user->localidad ? $user->detalleLocalidad->nombre : '';
        $user->talonario = $user->talonario_id ? $user->detalleTalonario->nombre : '';
        $user->drop_detalle = $this->drop[$user->drop];

        // get personal
        $user->instalador = User::find($user->instalador_id);


        // return $user;

        if ($user->hasRole('client')) {

            return View::make('client_admin.view')->with(['user' => $user]);
        
        }else{
            
            return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    public function create_admin()
    {

        // $provincias = Provincia::all();
        // $localidades = Localidad::all();
        $provincias = Provincia::where('id', 19)->get(); // 19 -> misiones
        $localidades = Localidad::where('id', 17051)->get(); // 17051 -> POSADAS
        $talonarios = Talonario::all();

        $nro_cliente  =  null;

        return View::make('client_admin.create')->with(['provincias' => $provincias, 'localidades' => $localidades, 'talonarios' => $talonarios, 'nro_cliente' => $nro_cliente]);
    }

    public function store_admin(Request $request)
    {

        
        // return $request->all();

        //-- VALIDATOR START --//
        $rules = array(
            'nro_cliente'       => 'required|unique:users',  // unique - verifica que no exista en la DB
            'dni'               => 'required|numeric|min:7',
            'firstname'         => 'required|min:3|max:50',
            'lastname'          => 'required|min:3|max:50',
            // 'email'             => 'required|email|unique:users',  // unique - verifica que no exista en la DB el email
            'email'             => 'email|unique:users',  // unique - verifica que no exista en la DB el email
            'password'          => 'min:8',
            'password_confirm'  => 'min:8|same:password',
            'picture'           => 'max:2000|mimes:jpg,jpeg,png,gif,bmp',
            );

        // Si el talonario es A, cambiar la regla de dni a 11 dígitos
        if ($request->talonario_id) {
            $talonario = Talonario::find($request->talonario_id);
            if ($talonario && $talonario->letra == 'A') {
                $rules['dni'] = 'required|numeric|digits:11';
            }
        }

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//



        $user = new User;
        // $user->nro_cliente  =  $this->getNroCliente('next');
        $user->nro_cliente  =  $request->nro_cliente;
        $user->dni  = $request->dni;
        $user->firstname  = $request->firstname;
        $user->lastname  = $request->lastname;
        // $user->email = $request->email;
        if($request->email != ''){$user->email = $request->email;}
        
        if($request->barrio != ''){$user->barrio = $request->barrio;}
        if($request->calle != ''){$user->calle = $request->calle;}
        if($request->altura != ''){$user->altura = $request->altura;}
        if($request->manzana != ''){$user->manzana = $request->manzana;}
        if($request->provincia != ''){$user->provincia = $request->provincia;}
        if($request->localidad != ''){$user->localidad = $request->localidad;}
        if($request->cp != ''){$user->cp = $request->cp;}
        if($request->tel1 != ''){$user->tel1 = $request->tel1;}
        if($request->tel2 != ''){$user->tel2 = $request->tel2;}
        if($request->autorizado_nombre != ''){$user->autorizado_nombre = $request->autorizado_nombre;}
        if($request->autorizado_tel != ''){$user->autorizado_tel = $request->autorizado_tel;}
        if($request->instalador_id != ''){$user->instalador_id = $request->instalador_id;}
        if($request->talonario_id != ''){$user->talonario_id = $request->talonario_id;}
        if($request->comentario != ''){$user->comentario = $request->comentario;}

        // $user->password = Hash::make($request->password);
        $user->password = Hash::make($request->dni);
        $user->remember_token = str_random(60);
        $user->status = $request->has('status') ? 1 : 0;
        
        // Activar automáticamente el cliente al crearlo desde el admin
        $user->fecha_registro = Carbon::now();

        // datos tecnicos
        if($request->drop != ''){$user->drop = $request->drop;}
        if($request->firma_contrato != ''){$user->firma_contrato = Carbon::createFromFormat('d/m/Y', $request->firma_contrato);}
        // if($request->ont_instalado != ''){$user->ont_instalado = $request->ont_instalado;}
        if($request->ont_instalado != ''){$user->ont_instalado = Carbon::createFromFormat('d/m/Y', $request->ont_instalado);}
        if($request->ont_funcionando != ''){$user->ont_funcionando = Carbon::createFromFormat('d/m/Y', $request->ont_funcionando);}
        if($request->ont_serie1 != ''){$user->ont_serie1 = $request->ont_serie1;}
        if($request->ont_serie2 != ''){$user->ont_serie2 = $request->ont_serie2;}
        if($request->spliter_serie != ''){$user->spliter_serie = $request->spliter_serie;}

        // set filename to foto field
        if ($request->hasFile('picture')) {
            $imageName = time().'.'.$request->picture->getClientOriginalExtension();
            $img = Image::make($request->picture->getRealPath())
                    ->resize(150, 150)
                    ->save(public_path('pictures').'/'.$imageName);

            if ($img) {
                // delete old picture
                if ($user->picture) {
                    Storage::disk('public')->delete('pictures/'.$user->picture); // Storage::disk('public')->exists('..')
                }                

                // update field 
                $user->picture = $imageName;
            }
        }

        if ($user->save()) {
            
            // attach role to user 3:client
            $user->attachRole(3);

            // Create Token
            $remember_token_url = $request->root() . '/account/activate/' . $user->remember_token;
            
            // si el estado es activo le envia un mail al cliente
            if ($user->status == 1) {

                
                //  send mail if user have a email address.
                if($user->email != ''){
                
                    try {

                        // send Mail
                        Mail::send('email.account_activate', ['remember_token_url' => $remember_token_url], function ($message) use ($user)
                         {
                            $message->from(config('constants.account_no_reply'), config('constants.title'))
                                    ->to($user->email)
                                    ->subject('Activa tu cuenta');
                        });
                        
                    } catch (\Exception $e) {
                        
                        $errorMessage = $e;
                    }

                }

            }

            // si se define el campo firma del contrato se dirige a la carga del servicio
            $route_redirect = $request->firma_contrato != '' ? '/admin/clients/services/' . $user->id : '/admin/clients';

            return redirect($route_redirect)->with(['status' => 'success', 'message' => 'El Cliente fué creado.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/clients')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    public function profile_admin()
    {

        return View::make('client_admin.profile');
    }

    public function updateProfile_admin(Request $request)
    {
        
        $user = Auth::user();
        return $this->update_admin($request, $user->id);
    }
    
    public function edit_admin($id)
    {
        $user = User::find($id);
        // return $user;

        // $provincias = Provincia::all();
        // $localidades = Localidad::all();
        $provincias = Provincia::where('id', 19)->get(); // 19 -> misiones
        $localidades = Localidad::where('id', 17051)->get(); // 17051 -> POSADAS
        $talonarios = Talonario::all();

        $user->firma_contrato = $user->firma_contrato != '' ? Carbon::parse($user->firma_contrato)->format('d/m/Y') : '';
        $user->ont_instalado = $user->ont_instalado != '' ? Carbon::parse($user->ont_instalado)->format('d/m/Y') : '';
        $user->ont_funcionando = $user->ont_funcionando != '' ? Carbon::parse($user->ont_funcionando)->format('d/m/Y') : '';
        // $user->fecha_registro = $user->fecha_registro != '' ? Carbon::parse($user->fecha_registro)->format('d/m/Y') : '';

        // get personal
        $user->instalador = User::find($user->instalador_id);

        if (!$user->hasRole('owner')) {

            return View::make('client_admin.edit')->with(['user' => $user, 'provincias' => $provincias, 'localidades' => $localidades, 'talonarios' => $talonarios]);
        
        }else{
            
            return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }

    }

    public function updateClient_admin(Request $request, $id)
    {
        
        return $this->update_admin($request, $id, '/admin/clients');
    }

    public function update_admin($request, $id, $route = null)
    {
        //-- VALIDATOR START --//
        $rules = array(
            'nro_cliente'       => 'required|unique:users,nro_cliente,'.$id,  // unique - verifica que no exista en la DB el email
            'dni'               => 'required|numeric|min:7',
            'firstname'         => 'required|min:3|max:50',
            'lastname'          => 'required|min:3|max:50',
            // 'email'             => 'required|email|unique:users,email,'.$id,  // unique - verifica que no exista en la DB el email
            'email'             => 'email|unique:users,email,'.$id,  // unique - verifica que no exista en la DB el email
            'password'          => 'min:8',
            'password_confirm'  => 'min:8|same:password',
            'picture'           => 'max:2000|mimes:jpg,jpeg,png,gif,bmp',
            );

        // Si el talonario es A, cambiar la regla de dni a 11 dígitos
        if ($request->talonario_id) {
            $talonario = Talonario::find($request->talonario_id);
            if ($talonario && $talonario->letra == 'A') {
                $rules['dni'] = 'required|numeric|digits:11';
            }
        }

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//

        $user = User::find($id);
        $user->dni  = $request->dni;
        $user->firstname  = $request->firstname;
        $user->lastname  = $request->lastname;
	$user->nro_cliente = $request->nro_cliente;
        
        // verifico si se actualizo el mail para notificarlo nuevamente 
        $send_email = $request->email != '' && $user->email != $request->email ? true : false;

        $user->email = $request->email;

        if($request->password != ''){
            $user->password = Hash::make($request->password);
        }


        $user->barrio = $request->barrio;
        $user->calle = $request->calle;
        $user->altura = $request->altura;
        $user->manzana = $request->manzana;
        $user->provincia = $request->provincia;
        $user->localidad = $request->localidad;
        if($request->cp != ''){$user->cp = $request->cp;}
        $user->tel1 = $request->tel1;
        $user->tel2 = $request->tel2;
        $user->autorizado_nombre = $request->autorizado_nombre;
        $user->autorizado_tel = $request->autorizado_tel;
        $user->talonario_id = $request->talonario_id;
        $user->comentario = $request->comentario;
        
        if($request->instalador_id != ''){$user->instalador_id = $request->instalador_id;}

        // can only update admins
        
        if (Auth::user()->hasRole('owner') || Auth::user()->hasRole('admin')) {

            // datos tecnicos
            if($request->drop != ''){$user->drop = $request->drop;}
            if($request->firma_contrato != ''){$user->firma_contrato = Carbon::createFromFormat('d/m/Y', $request->firma_contrato);}
            if($request->ont_instalado != ''){$user->ont_instalado = Carbon::createFromFormat('d/m/Y', $request->ont_instalado);}
            if($request->ont_funcionando != ''){$user->ont_funcionando = Carbon::createFromFormat('d/m/Y', $request->ont_funcionando);}
            $user->ont_serie1 = $request->ont_serie1;
            $user->ont_serie2 = $request->ont_serie2;
            $user->spliter_serie = $request->spliter_serie;
            // if($request->ont_serie != ''){$user->ont_serie = $request->ont_serie;}
            // if($request->spliter_serie != ''){$user->spliter_serie = $request->spliter_serie;}            

        }

        
        if ($id != Auth::user()->id) {
            $user->status = $request->has('status') ? 1 : 0;
        }
        
        if($request->password != ''){
            $user->password = Hash::make($request->password);
        }

        // set filename to foto field
        if ($request->hasFile('picture')) {
            $imageName = time().'.'.$request->picture->getClientOriginalExtension();
            $img = Image::make($request->picture->getRealPath())
                    ->resize(150, 150)
                    ->save(public_path('pictures').'/'.$imageName);

            if ($img) {
                // delete old picture
                if ($user->picture) {
                    Storage::disk('public')->delete('pictures/'.$user->picture); // Storage::disk('public')->exists('..')
                }                

                // update field 
                $user->picture = $imageName;
            }
        }

        if ($user->save()) {


            // attach role to user 3:client
            // $user->attachRole(3);

            // Create Token
            $remember_token_url = $request->root() . '/account/activate/' . $user->remember_token;
            
            // si el estado es activo le envia un mail al cliente
            if ($user->status == 1) {

                
                //  send mail if user have a email address.
                if($send_email){
                
                    try {

                        // send Mail
                        Mail::send('email.account_activate', ['remember_token_url' => $remember_token_url], function ($message) use ($user)
                         {
                            $message->from(config('constants.account_no_reply'), config('constants.title'))
                                    ->to($user->email)
                                    ->subject('Activa tu cuenta');
                        });
                        
                    } catch (\Exception $e) {
                        
                        $errorMessage = $e;
                    }

                }

            }


            if (!is_null($route)) {
            
                return redirect($route)->with(['status' => 'success', 'message' => 'El Cliente fué modificado.', 'icon' => 'fa-smile-o']);
            
            }else{

                return back()->withInput()->with(['status' => 'success', 'message' => 'El Cliente fué modificado.', 'icon' => 'fa-smile-o']);
                
            }

        }else{
            
            return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    public function updateStatus_admin(Request $request, $id)
    {
        // get current datetime
        $now = Carbon::now();

        $user = User::find($id);
        if ($id != Auth::user()->id) {
            $user->status     = $request->status == 'true' ? 1 : 0;
            $user->fecha_baja = $request->status == 'true' ? NULL : $now;
        }
        
        if ($user->save()) {

            return ['status' => 'success', 'message' => 'El Usuario fué modificado.', 'icon' => 'fa-smile-o'];

        }else{
            
            return ['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o'];
        
        }
    }

    public function getNroCliente($action = null)
    {

        /*$nro_cliente = User::with('roles')
                              ->get()
                              ->filter(function ($user) {
                                  return $user->roles->contains('name', 'client');
                              })
                              ->max('nro_cliente');*/

        //$nro_cliente = $action != null && $action == 'next' ? $nro_cliente + 1 : $nro_cliente + 0;

        return null;
    
    }

    public function getStaffList()
    {
        
        // For work edit file : \vendor\zizaco\entrust\src\Entrust\Traits\EntrustRoleTrait.php #52
        $staffRole = Role::where('name', 'staff')->first();
        $users = $staffRole ? $staffRole->users()->get() : collect([]);
        
        if (count($users)) {

            foreach ($users as $user) {

                if ($user->status == 1) {

                    $staff[] = ['data' => $user->id, 'value' => $user->firstname .' '.$user->lastname];

                }

            }

        }else{

            $staff = 'null';

        }

        return $staff;
    }

    // services for clients ----------------------------------
    public function services_admin()
    {
        

        $cuotas = Cuota::all();
        
        return View::make('client_admin.create_services')->with(['cuotas' => $cuotas]);


    }

    public function services_add_admin(Request $request)
    {
       
        // return $request->all();

        //-- VALIDATOR START --//
        $rules = array(
            'servicio_id'       => 'required|numeric|exists:servicios,id|unique_with:servicios_usuarios,user_id',
            'user_id'           => 'required|numeric|exists:users,id',
            'contrato_nro'      => 'required|numeric|unique:servicios_usuarios,contrato_nro',
            'contrato_fecha'    => 'required|date_format:d/m/Y',
            'alta_servicio'     => 'required|date_format:d/m/Y',
            // 'mes_alta'          => 'required',
            'abono_mensual'     => 'required|numeric',
            'abono_proporcional' => 'nullable|numeric',
            'costo_instalacion' => 'nullable|numeric',           
            );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//



        $servicio = new ServicioUsuario;
        $servicio->user_id  = $request->user_id;
        $servicio->servicio_id  = $request->servicio_id;
        $servicio->contrato_nro  = $request->contrato_nro;        
        $servicio->contrato_fecha  = Carbon::createFromFormat('d/m/Y', $request->contrato_fecha);
        $servicio->alta_servicio  = Carbon::createFromFormat('d/m/Y', $request->alta_servicio);
        // if($request->mes_alta != ''){$servicio->mes_alta = $request->mes_alta;}
        $servicio->abono_mensual  = $this->floatvalue($request->abono_mensual);
        $servicio->abono_proporcional  = $this->floatvalue($request->abono_proporcional);

        // guardo el costo de instalacion base
        $servicio_base = Servicio::find($request->servicio_id);
        $servicio->costo_instalacion_base  = $this->floatvalue($servicio_base->costo_instalacion);

        $servicio->costo_instalacion  = $this->floatvalue($request->costo_instalacion);
        $servicio->plan_pago  = $request->plan_pago;
        $servicio->comentario  = $request->comentario;
        $servicio->status = 1;

        if ($servicio->save()) {

            return redirect('/admin/clients/services/'.$request->user_id)->with(['status' => 'success', 'message' => 'El Servicio fué asignado al Cliente.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/clients/services/'.$request->user_id)->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    public function getClientList()
    {
        
        // For work edit file : \vendor\zizaco\entrust\src\Entrust\Traits\EntrustRoleTrait.php #52
        $clientRole = Role::where('name', 'client')->first();
        $users = $clientRole ? $clientRole->users()->get() : collect([]);
        
        // Inicializar el array de clientes
        $clients = [];
        
        if (count($users)) {

            foreach ($users as $user) {

                if ($user->status == 1) {

                    $clients[] = ['data' => $user->id, 'value' => $user->firstname .' '.$user->lastname];

                }

            }

        }
        
        // Si no hay clientes, retornar 'null' como string para el JavaScript
        if (empty($clients)) {
            return 'null';
        }

        return $clients;
    }

    public function getClientListNotBill()
    {
        

        // For work edit file : \vendor\zizaco\entrust\src\Entrust\Traits\EntrustRoleTrait.php #52
        $clientRole = Role::where('name', 'client')->first();
        $users = $clientRole ? $clientRole->users()->get() : collect([]);
        
        // Inicializar arrays
        $user_fact = [];
        $clients = [];
        
        // get last periodo facturado
        $lastFactura = Factura::orderBy('id', 'DESC')->first();
        if (!$lastFactura) {
            return 'null';
        }
        
        $last_periodo = $lastFactura->periodo;

        // get all users facturados
        $users_facturados = Factura::where('periodo', $last_periodo)->get(['user_id']);
        foreach ($users_facturados as $user_facturado) {
            $user_fact[] = $user_facturado->user_id;
        }

        if (count($users)) {

            $clients = [];

            foreach ($users as $user) :

                $add = 0;

                if ($user->status == 1) {

                    // add only users not facturados
                    if (!in_array($user->id, $user_fact)) {

                        // virify if user has services
                        if (count($user->servicios)) {

                            foreach ($user->servicios as $servicio) :

                                if ($servicio->status == 1) {

                                    if ($servicio->pp_flag == 1) {
                                    
                                        $add = 1;
                                    
                                    }else{

                                        // verify if es billable
                                        $alta_servicio_periodo = Carbon::parse($servicio->alta_servicio)->format('m/Y');

                                        $ifBillable = $this->getIfBillable($last_periodo, $alta_servicio_periodo);
                                    
                                        $add = $ifBillable ? 1 : 0;
                                    
                                    }

                                }

                            endforeach;

                        }

                    }

                }

                if ($add) {

                    $clients[] = ['data' => $user->id, 'value' => $user->firstname .' '.$user->lastname];
                
                }

            endforeach;

        }
        
        // Si no hay clientes, retornar 'null' como string para el JavaScript
        if (empty($clients)) {
            return 'null';
        }

        return $clients;
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

    public function getServiceList()
    {
        
        // For work edit file : \vendor\zizaco\entrust\src\Entrust\Traits\EntrustRoleTrait.php #52
        $services = Servicio::where('status', '1')->get();
        
        if (count($services)) {

            foreach ($services as $service) {

                $servicios[] = ['data' => $service->id, 'value' => $service->nombre];

            }

        }else{

            $servicios = 'null';

        }

        return $servicios;
    }

    public function getServiceDetail(Request $request)
    {
        
        $servicio = 'null';
        
        if ($request->servicio_id) {
            
            $servicio = Servicio::findOrFail($request->servicio_id);

            $servicio->abono_mensual = number_format($servicio->abono_mensual, 2); 
            $servicio->abono_proporcional = number_format($servicio->abono_proporcional, 2); 
            $servicio->costo_instalacion = number_format($servicio->costo_instalacion, 2); 

        }
        
        return  $servicio;
    }

    public function services_list_admin($id)
    {
        $user = User::find($id);
        // return $user;

        if (!$user->hasRole('owner')) {

            return View::make('client_admin.list_service')->with(['user' => $user]);
        
        }else{
            
            return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }

    }

    public function getClientServiceList(Request $request, $id)
    {


        $id = $request->method() == 'POST' && $request->user_id != null ? $request->user_id : $id;

        // get servicios
        $servicios = ServicioUsuario::where('user_id', '=', $id)
                                      ->where('pp_flag', '=', '0')
                                      ->get();
                                    // ->toSql();

        foreach ($servicios as $servicio) {
            
            $servicio->tipo  =  $this->tipo[$servicio->servicio->tipo];
            $servicio->nombre = $servicio->servicio->nombre;

            $servicio->abono_mensual = number_format($servicio->abono_mensual, 2); 
            $servicio->abono_proporcional = number_format($servicio->abono_proporcional, 2); 
            $servicio->costo_instalacion = number_format($servicio->costo_instalacion, 2); 

            $servicio->plan_pago = $this->planPagoText($servicio->plan_pago);

        }


        return Datatables::of($servicios)->make(true);
    }

    public function updateClientServiceStatus_admin(Request $request, $id)
    {
        
        $servicio = ServicioUsuario::find($id);
        $servicio->status = $request->status == 'true' ? 1 : 0;
        
        if ($servicio->save()) {

            return ['status' => 'success', 'message' => 'El Servicio fué modificado.', 'icon' => 'fa-smile-o'];

        }else{
            
            return ['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o'];
        
        }
    }

    public function services_edit_admin($id)
    {
        $servicio = ServicioUsuario::find($id);

        $servicio->contrato_fecha = $servicio->contrato_fecha != '' ? Carbon::parse($servicio->contrato_fecha)->format('d/m/Y') : '';
        $servicio->alta_servicio = $servicio->alta_servicio != '' ? Carbon::parse($servicio->alta_servicio)->format('d/m/Y') : '';

        $servicio->abono_mensual = number_format($servicio->abono_mensual, 2); 
        $servicio->abono_proporcional = number_format($servicio->abono_proporcional, 2); 
        $servicio->costo_instalacion = number_format($servicio->costo_instalacion, 2); 

        $servicio->nombre = $servicio->servicio->nombre;
        $servicio->usuario;

        $user = User::find($servicio->user_id);

        $cuotas = Cuota::all();
        // return $servicio;
        
        return View::make('client_admin.edit_service')->with(['user' => $user, 'servicio' => $servicio, 'cuotas' => $cuotas]);

    }

    public function services_update_admin(Request $request, $id)
    {
       
        // return $request->all();

        //-- VALIDATOR START --//
        $rules = array(
            'servicio_id'       => 'required|numeric|exists:servicios,id|unique_with:servicios_usuarios,user_id,'.$id,
            'user_id'           => 'required|numeric|exists:users,id',
            'contrato_nro'      => 'required|numeric|unique:servicios_usuarios,contrato_nro,'.$id,
            'contrato_fecha'    => 'required|date_format:d/m/Y',
            'alta_servicio'     => 'required|date_format:d/m/Y',
            // 'mes_alta'          => 'required',
            'abono_mensual'     => ['required', 'regex:/^-?(?:0|[1-9]\d{0,2}(?:\.\d{3})*)(?:,\d+)?$/'],
            'abono_proporcional'=> ['regex:/^-?(?:0|[1-9]\d{0,2}(?:\.\d{3})*)(?:,\d+)?$/'],
            'costo_instalacion' => ['regex:/^-?(?:0|[1-9]\d{0,2}(?:\.\d{3})*)(?:,\d+)?$/'],           
            );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//



        $servicio = ServicioUsuario::find($id);
        $servicio->user_id  = $request->user_id;
        $servicio->servicio_id  = $request->servicio_id;
        $servicio->contrato_nro  = $request->contrato_nro;        
        $servicio->contrato_fecha  = Carbon::createFromFormat('d/m/Y', $request->contrato_fecha);
        $servicio->alta_servicio  = Carbon::createFromFormat('d/m/Y', $request->alta_servicio);
        // if($request->mes_alta != ''){$servicio->mes_alta = $request->mes_alta;}
        $servicio->abono_mensual  = $this->floatvalue($request->abono_mensual);
        $servicio->abono_proporcional  = $this->floatvalue($request->abono_proporcional);
        
        // guardo el costo de instalacion base
        // $servicio = Servicio::find($request->servicio_id);
        // $servicio->costo_instalacion_base  = $this->floatvalue($servicio->costo_instalacion);

        $servicio->costo_instalacion  = $this->floatvalue($request->costo_instalacion);
        $servicio->plan_pago  = $request->plan_pago;
        $servicio->comentario  = $request->comentario;
        $servicio->status = $request->has('status') ? 1 : 0;

        if ($servicio->save()) {

            return redirect('/admin/clients/services/'.$request->user_id)->with(['status' => 'success', 'message' => 'El Servicio fué modificado.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/clients/services/'.$request->user_id)->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    public function create_service_admin($id)
    {

        $user   = User::find($id);
        $cuotas = Cuota::all();
        // return $user;
        
        return View::make('client_admin.create_service')->with(['user' => $user, 'cuotas' => $cuotas]);

    }

    public function planPagoText($plan_pago)
    {
        
        // get cuota object
        $cuota = Cuota::find($plan_pago);

        switch ($plan_pago) {
            case '0':
                $plan_pago = 'Mensual';
                break;

            case '1':
                $plan_pago = $cuota->numero . ' Cuota';
                break;
            
            default:
                $plan_pago = $cuota->numero . ' Cuotas';
                break;
        }

        return $plan_pago;
    }




    // Plan de pagos for clients ----------------------------------
    public function payment_plan_admin()
    {
        

        $cuotas = Cuota::all();
        
        return View::make('client_admin.create_services')->with(['cuotas' => $cuotas]);


    }

    public function payment_plan_add_admin(Request $request)
    {
       
        // return $request->all();

        //-- VALIDATOR START --//
        $rules = array(
            'servicio_id'         => 'required|numeric|exists:servicios,id',
            'user_id'             => 'required|numeric|exists:users,id',
            'pp_cuotas_adeudadas' => 'required|numeric',
            'pp_plan_pago'        => 'required|numeric',
            'pp_tasa'             => 'required|numeric',
            'abono_mensual'       => ['required', 'regex:/^-?(?:0|[1-9]\d{0,2}(?:\.\d{3})*)(?:,\d+)?$/'],
            'abono_mensual_pagar' => ['required', 'regex:/^-?(?:0|[1-9]\d{0,2}(?:\.\d{3})*)(?:,\d+)?$/'],
            );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//



        $servicio = new ServicioUsuario;
        $servicio->user_id  = $request->user_id;
        $servicio->servicio_id  = $request->servicio_id;
        $servicio->abono_mensual  = $this->floatvalue($request->abono_mensual_pagar);
        $servicio->plan_pago  = $request->pp_plan_pago;
        $servicio->comentario  = $request->comentario;
        $servicio->pp_flag = 1;
        $servicio->pp_cuotas_adeudadas = $request->pp_cuotas_adeudadas;
        $servicio->pp_instalacion_adeudado = $request->deuda_instalacion;
        $servicio->pp_tasa = $request->pp_tasa;
        $servicio->pp_importe_total_adeudado = $request->hidden_total_deuda;
        $servicio->pp_abono_mensual_inicial  = $this->floatvalue($request->abono_mensual);
        $servicio->status = 1;

        if ($servicio->save()) {

            return redirect('/admin/clients/payment_plan/'.$request->user_id)->with(['status' => 'success', 'message' => 'El Plan de Pago fué asignado al Cliente.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/clients/payment_plan/'.$request->user_id)->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    public function payment_plan_list_admin($id)
    {
        $user = User::find($id);
        // return $user;

        if (!$user->hasRole('owner')) {

            return View::make('client_admin.list_payment_plan')->with(['user' => $user]);
        
        }else{
            
            return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }

    }

    public function getClientPaymentPlanList(Request $request, $id)
    {


        $id = $request->method() == 'POST' && $request->user_id != null ? $request->user_id : $id;

        // get servicios
        $servicios = ServicioUsuario::where('user_id', $id)
                                    ->where('pp_flag', '=', '1')
                                    ->get();

        foreach ($servicios as $servicio) {
            
            // $servicio->tipo  =  $this->tipo[$servicio->servicio->tipo];
            $servicio->nombre = $servicio->servicio->nombre;
            $servicio->abono_mensual_pagar = number_format($servicio->abono_mensual, 2); 
            $servicio->abono_mensual = number_format($servicio->servicio->abono_mensual, 2); 
            $servicio->pp_importe_total_adeudado = number_format($servicio->pp_importe_total_adeudado, 2); 
            $servicio->plan_pago = $this->planPagoDeudaText($servicio->plan_pago);

        }


        return Datatables::of($servicios)->make(true);
    }

    public function create_payment_plan_admin($id)
    {

        $user   = User::find($id);
        $pagosConfig = PagosConfig::find(1);
        // return $user;
        
        return View::make('client_admin.create_payment_plan')->with(['user' => $user, 'pagosConfig' => $pagosConfig]);

    }

    public function update_payment_plan_status_admin(Request $request, $id)
    {
        
        $servicio = ServicioUsuario::find($id);
        $servicio->status = $request->status == 'true' ? 1 : 0;
        
        if ($servicio->save()) {

            return ['status' => 'success', 'message' => 'El Plan de pago fué modificado.', 'icon' => 'fa-smile-o'];

        }else{
            
            return ['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o'];
        
        }
    }

    public function planPagoDeudaText($plan_pago)
    {
        
        switch ($plan_pago) {
            case '0':
                $plan_pago = 'Mensual';
                break;

            case '1':
                $plan_pago = $plan_pago . ' Cuota';
                break;
            
            default:
                $plan_pago = $plan_pago . ' Cuotas';
                break;
        }

        return $plan_pago;
    }

    public function payment_plan_edit_admin($id)
    {
        $servicio = ServicioUsuario::find($id);

        $servicio->contrato_fecha = $servicio->contrato_fecha != '' ? Carbon::parse($servicio->contrato_fecha)->format('d/m/Y') : '';
        $servicio->alta_servicio = $servicio->alta_servicio != '' ? Carbon::parse($servicio->alta_servicio)->format('d/m/Y') : '';

        $servicio->abono_mensual = number_format($servicio->abono_mensual, 2); 
        $servicio->pp_abono_mensual_inicial = number_format($servicio->pp_abono_mensual_inicial, 2); 
        // $servicio->pp_importe_total_adeudado = number_format($servicio->pp_importe_total_adeudado, 2, ',' , '.'); 

        $servicio->nombre = $servicio->servicio->nombre;
        $servicio->usuario;

        $user = User::find($servicio->user_id);

        $cuotas = Cuota::all();
        // return $servicio;
        
        $pagosConfig = PagosConfig::find(1);

        return View::make('client_admin.edit_payment_plan')->with(['user' => $user, 'servicio' => $servicio, 'pagosConfig' => $pagosConfig]);

    }

    public function payment_plan_update_admin(Request $request, $id)
    {
       
        // return $request->all();

        //-- VALIDATOR START --//
        $rules = array(
            'servicio_id'         => 'required|numeric|exists:servicios,id',
            'user_id'             => 'required|numeric|exists:users,id',
            'pp_cuotas_adeudadas' => 'required|numeric',
            'pp_plan_pago'        => 'required|numeric',
            'pp_tasa'             => 'required|numeric',
            'abono_mensual'       => ['required', 'regex:/^-?(?:0|[1-9]\d{0,2}(?:\.\d{3})*)(?:,\d+)?$/'],
            'abono_mensual_pagar' => ['required', 'regex:/^-?(?:0|[1-9]\d{0,2}(?:\.\d{3})*)(?:,\d+)?$/'],
            );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//

        $servicio = ServicioUsuario::find($id);
        $servicio->user_id  = $request->user_id;
        $servicio->servicio_id  = $request->servicio_id;
        $servicio->abono_mensual  = $this->floatvalue($request->abono_mensual_pagar);
        $servicio->plan_pago  = $request->pp_plan_pago;
        $servicio->comentario  = $request->comentario;
        $servicio->pp_flag = 1;
        $servicio->pp_cuotas_adeudadas = $request->pp_cuotas_adeudadas;
        $servicio->pp_instalacion_adeudado = $request->deuda_instalacion;
        $servicio->pp_tasa = $request->pp_tasa;
        $servicio->pp_importe_total_adeudado = $request->hidden_total_deuda;
        $servicio->pp_abono_mensual_inicial  = $this->floatvalue($request->abono_mensual);

        if ($servicio->save()) {

            return redirect('/admin/clients/payment_plan/'.$request->user_id)->with(['status' => 'success', 'message' => 'El Plan de Pago fué modificado.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/clients/payment_plan/'.$request->user_id)->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    // funciones comunes ----------------------------------

    public function floatvalue($val){
        $val = str_replace(",",".",$val);
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

    // facturas de clientes
    public function bills_list_admin($id)
    {
        $user = User::find($id);
        // return $user;

        if (!$user->hasRole('owner')) {

            $user->nro_cliente = $this->zerofill($user->nro_cliente, 5);

            return View::make('client_admin.list_facturas')->with(['user' => $user]);
        
        }else{
            
            return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }

    }

    // lista de facturas de un periodo dado - AJAX
    public function getClientBillsList(Request $request, $id)
    {

        $fecha_actual = Carbon::today();
        // $periodo = $mes.'/'.$ano;
        $facturas = Factura::where('user_id', $id)->get();

        foreach ($facturas as $factura) {

            $factura->talonario;
            $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);
            
            $factura->nro_factura = $this->zerofill($factura->nro_factura);
            $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);

            $factura->cliente;
            $factura->cliente->nombre_apellido = $factura->cliente->firstname.' '.$factura->cliente->lastname;


            // verifico si puede mostrarse el boton de bonificacion
            $factura->fecha_actual = $fecha_actual;
            $factura->btn_bonificacion = $fecha_actual->lt(Carbon::parse($factura->primer_vto_fecha));
            $factura->btn_actualizar = $fecha_actual->gt(Carbon::parse($factura->segundo_vto_fecha));


            $factura->fecha_emision = Carbon::parse($factura->fecha_emision)->format('d/m/Y');
            $factura->primer_vto_fecha = Carbon::parse($factura->primer_vto_fecha)->format('d/m/Y');
            $factura->segundo_vto_fecha = Carbon::parse($factura->segundo_vto_fecha)->format('d/m/Y');

            $factura->importe_subtotal = number_format($factura->importe_subtotal, 2); 
            $factura->importe_bonificacion = number_format($factura->importe_bonificacion, 2); 
            $factura->importe_total = number_format($factura->importe_total, 2); 

            $factura->fecha_pago = $factura->fecha_pago ? Carbon::parse($factura->fecha_pago)->format('d/m/Y') : null;
            $factura->forma_pago = $factura->fecha_pago ? $this->forma_pago[$factura->forma_pago] : '';

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


    // --------------------------------------------------------------------------------------------
    
    public function import()
    {
        
        $usuarios = Usuario::all();  
        
        foreach ($usuarios as $usuario) {

            $fullname = explode(', ',$usuario->nombre);
            $firstname = count($fullname) > 1 ? $fullname[0] : '';
            $lastname = $fullname[0];

            $user = User::updateOrCreate([  'dni' => $usuario->dni, 'password' => Hash::make($usuario->clave), 'firstname' => $firstname, 'lastname' => $lastname, 'usuario_id' => $usuario->id, 'status' => 1], ['usuario_id' => $usuario->usuario_id]);

            // attach role to user
            $user->attachRole(3); // parameter can be an Role object, array, or id

        }

        return 'The migration process was successfully executed.';
    } 

    public function billPayMp(Request $request, $id)
    {
        // L�gica para procesar el pago con Mercado Pago
        $factura = Factura::find($id);

        if (!$factura) {
            return response()->json(['error' => 'Factura no encontrada'], 404);
        }

        // Aqu� ir�a la l�gica para procesar el pago con Mercado Pago
        $now = Carbon::now();

        if ($now->lte(Carbon::parse($factura->primer_vto_fecha))) {
            $vencimiento = 'primer';
            $importe = $factura->importe_total;
        } elseif ($now->lte(Carbon::parse($factura->segundo_vto_fecha))) {
            $vencimiento = 'segundo';
            $importe = $factura->segundo_vto_importe;
        } else {
            // Si todas las fechas están vencidas, usar el segundo vencimiento
            $vencimiento = 'segundo';
            $importe = $factura->segundo_vto_importe;
        }
        $paymentPreference = PaymentPreference::where('factura_id', $factura->id)
            ->where('vencimiento_tipo', $vencimiento)
            ->first();

        if ($paymentPreference && $paymentPreference->init_point) {
            return redirect($paymentPreference->init_point);
        } else {
            return response()->json(['error' => 'Enlace de pago no encontrado o factura vencida'], 404);
        }
    }

    /**
     * Método para iniciar el pago de una factura desde el dashboard del cliente
     * Calcula intereses si es necesario y muestra cartel de advertencia
     */
    public function pay($facturaId)
    {
        $user = Auth::user();
        $factura = Factura::find($facturaId);

        // Verificar que la factura pertenece al usuario y no está pagada
        if (!$factura || $factura->user_id != $user->id || $factura->fecha_pago) {
            return redirect('/my-invoice')->with(['status' => 'danger', 'message' => 'Factura no encontrada o ya pagada.', 'icon' => 'fa-frown-o']);
        }

        $fechaActual = Carbon::now();
        $tipoVencimiento = $this->determinarTipoVencimiento($factura, $fechaActual);

        // Calcular importe correspondiente
        $importeTotal = $this->calcularImporteCorrespondienteCliente($factura, $fechaActual->format('Y-m-d'));
        
        // Calcular intereses y días de mora
        $intereses = 0;
        $diasMora = 0;
        $tasaInteres = 0;
        
        if ($tipoVencimiento == 'tercer') {
            $segundoVtoFecha = Carbon::parse($factura->segundo_vto_fecha);
            $diasMora = $segundoVtoFecha->diffInDays($fechaActual) + 1;
            $interes = Interes::find(1);
            $tasaInteres = $interes ? $interes->tercer_vto_tasa : 0;
            $intereses = round(($factura->segundo_vto_importe * $diasMora * $tasaInteres / 100), 2);
        }

        // Formatear datos para la vista
        $factura->talonario;
        $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);
        $factura->nro_factura = $this->zerofill($factura->nro_factura);
        $factura->cliente;

        return \View::make('client.pay_invoice', compact(
            'factura',
            'tipoVencimiento',
            'importeTotal',
            'intereses',
            'diasMora',
            'tasaInteres'
        ));
    }

    /**
     * Calcular el importe correspondiente según la fecha de pago (versión para cliente)
     */
    protected function calcularImporteCorrespondienteCliente(Factura $factura, $fechaPago)
    {
        $fechaPagoCarbon = Carbon::parse($fechaPago);

        // Obtener fechas de vencimiento
        $primerVtoFecha = Carbon::parse($factura->primer_vto_fecha);
        $segundoVtoFecha = Carbon::parse($factura->segundo_vto_fecha);

        // Si paga en o antes del primer vencimiento → importe original
        if ($fechaPagoCarbon->lte($primerVtoFecha)) {
            return $factura->importe_total;
        }

        // Si paga en o antes del segundo vencimiento → importe con recargo del segundo vencimiento
        if ($fechaPagoCarbon->lte($segundoVtoFecha)) {
            return $factura->segundo_vto_importe;
        }

        // Si paga después del segundo vencimiento → calcular interés diario
        $interes = Interes::find(1);
        if (!$interes || !$interes->tercer_vto_tasa) {
            return $factura->segundo_vto_importe;
        }

        // Calcular días transcurridos desde el segundo vencimiento
        $diasExcedentes = $segundoVtoFecha->diffInDays($fechaPagoCarbon) + 1;

        // Calcular tasa acumulada (tasa diaria * días)
        $tasaAcumulada = $diasExcedentes * $interes->tercer_vto_tasa;

        // Calcular importe final con interés diario sobre el importe del segundo vencimiento
        $importeConInteres = round(($factura->segundo_vto_importe * $tasaAcumulada / 100) + $factura->segundo_vto_importe, 2);

        return $importeConInteres;
    }

    /**
     * Procesar el pago confirmado por el cliente (crear preference de MercadoPago)
     */
    public function processPayment(Request $request, $facturaId)
    {
        $user = Auth::user();
        $factura = Factura::find($facturaId);

        // Verificar que la factura pertenece al usuario y no está pagada
        if (!$factura || $factura->user_id != $user->id || $factura->fecha_pago) {
            return redirect('/my-invoice')->with(['status' => 'danger', 'message' => 'Factura no encontrada o ya pagada.', 'icon' => 'fa-frown-o']);
        }

        $fechaActual = Carbon::now();
        $tipoVencimiento = $this->determinarTipoVencimiento($factura, $fechaActual);

        // Calcular el importe correspondiente
        $importeCalculado = $this->calcularImporteCorrespondienteCliente($factura, $fechaActual->format('Y-m-d'));

        // Preparar datos para MercadoPago (igual que en PaymentQRService)
        $puntoVenta = $factura->talonario ? $factura->talonario->nro_punto_vta : '0001';
        $clienteName = $factura->cliente ? ($factura->cliente->firstname . ' ' . $factura->cliente->lastname) : 'Cliente';

        $baseTitle = "Factura {$puntoVenta}-{$factura->nro_factura}";
        $baseDescription = "Pago de factura periodo {$factura->periodo} - Cliente: {$clienteName}";

        $paymentData = [
            'title' => $baseTitle . ' - ' . ucfirst($tipoVencimiento) . ' Vencimiento',
            'description' => $baseDescription . ' - ' . ucfirst($tipoVencimiento) . ' vencimiento',
            'amount' => $importeCalculado,
            'external_reference' => $factura->id . '_' . $tipoVencimiento . '_' . time(),
            'due_date' => $tipoVencimiento === 'primer' ? $factura->primer_vto_fecha :
                         ($tipoVencimiento === 'segundo' ? $factura->segundo_vto_fecha : $factura->tercer_vto_fecha),
            'payer' => [
                'name' => $factura->cliente ? $factura->cliente->firstname : 'Cliente',
                'surname' => $factura->cliente ? $factura->cliente->lastname : 'Cliente',
                'email' => ($factura->cliente && $factura->cliente->email) ? $factura->cliente->email : 'administracion@redin.com.ar',
                'identification' => [
                    'type' => 'DNI',
                    'number' => ($factura->cliente && $factura->cliente->dni) ? $factura->cliente->dni : '00000000'
                ]
            ]
        ];

        // Usar MercadoPagoService para crear la preferencia real
        $mercadoPagoService = app(MercadoPagoService::class);
        $preferenceResult = $mercadoPagoService->createPaymentPreference($paymentData);

        if (!$preferenceResult['success']) {
            return redirect('/my-invoice')->with(['status' => 'danger', 'message' => 'Error al crear el enlace de pago. Intente nuevamente.', 'icon' => 'fa-frown-o']);
        }

        // Crear PaymentPreference en la base de datos
        $paymentPreference = new PaymentPreference();
        $paymentPreference->factura_id = $factura->id;
        $paymentPreference->amount = $importeCalculado;
        $paymentPreference->vencimiento_tipo = $tipoVencimiento;
        $paymentPreference->preference_id = $preferenceResult['preference_id'];
        $paymentPreference->init_point = $preferenceResult['init_point'];
        $paymentPreference->external_reference = $paymentData['external_reference'];
        $paymentPreference->status = 'pending';
        $paymentPreference->save();

        return redirect($preferenceResult['init_point']);
    }

    /**
     * Determinar el tipo de vencimiento según la fecha actual
     */
    protected function determinarTipoVencimiento(Factura $factura, Carbon $fechaActual)
    {
        if ($fechaActual->lte(Carbon::parse($factura->primer_vto_fecha))) {
            return 'primer';
        } elseif ($fechaActual->lte(Carbon::parse($factura->segundo_vto_fecha))) {
            return 'segundo';
        } else {
            return 'tercer';
        }
    }

    /**
     * Mostrar formulario para informar pago por CBU/Transferencia
     */
    public function informPayment($facturaId)
    {
        $user = Auth::user();
        $factura = Factura::find($facturaId);

        // Verificar que la factura pertenece al usuario y no está pagada
        if (!$factura || $factura->user_id != $user->id || $factura->fecha_pago) {
            return redirect('/my-invoice')->with(['status' => 'danger', 'message' => 'Factura no encontrada o ya pagada.', 'icon' => 'fa-frown-o']);
        }

        // Verificar si ya hay un pago informado pendiente o aprobado
        $pagoExistente = $factura->pagosInformados()->whereIn('estado', ['pendiente', 'aprobado'])->first();
        if ($pagoExistente) {
            $mensaje = $pagoExistente->estado == 'pendiente' 
                ? 'Ya hay un pago informado pendiente de validación para esta factura.'
                : 'Esta factura ya tiene un pago aprobado.';
            return redirect('/my-invoice/detail/' . $factura->id)->with(['status' => 'warning', 'message' => $mensaje, 'icon' => 'fa-exclamation-triangle']);
        }

        // Formatear datos de la factura
        $factura->talonario;
        $factura->talonario->nro_punto_vta = $this->zerofill($factura->talonario->nro_punto_vta, 4);
        $factura->nro_factura = $this->zerofill($factura->nro_factura);
        $factura->nro_cliente = $this->zerofill($factura->nro_cliente, 5);
        $factura->cliente;

        // Calcular importe según vencimiento actual
        $fechaActual = Carbon::now();
        $importeCorrespondiente = $this->calcularImporteCorrespondienteCliente($factura, $fechaActual->format('Y-m-d'));

        return View::make('client.inform_payment')->with([
            'factura' => $factura,
            'importe_correspondiente' => $importeCorrespondiente
        ]);
    }

    /**
     * Procesar el pago informado por CBU/Transferencia
     */
    public function storeInformedPayment(Request $request, $facturaId)
    {
        $user = Auth::user();
        $factura = Factura::find($facturaId);

        // Verificar que la factura pertenece al usuario y no está pagada
        if (!$factura || $factura->user_id != $user->id || $factura->fecha_pago) {
            return redirect('/my-invoice')->with(['status' => 'danger', 'message' => 'Factura no encontrada o ya pagada.', 'icon' => 'fa-frown-o']);
        }

        // Validación
        $rules = [
            'importe_informado' => ['required', 'regex:/^-?(?:0|[1-9]\d{0,2}(?:\.\d{3})*)(?:,\d+)?$/'],
            'fecha_pago_informado' => 'required|date_format:d/m/Y|before_or_equal:today',
            'tipo_transferencia' => 'required|in:CBU,TRANSFERENCIA,DEPOSITO',
            'banco_origen' => 'required|max:100',
            'numero_operacion' => 'required|max:50',
            'cbu_origen' => 'nullable|max:50',
            'titular_cuenta' => 'required|max:100',
            'comprobante' => 'required|mimes:jpeg,png,jpg,gif,pdf|max:2048'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        // Verificar si ya hay un pago informado pendiente o aprobado (doble verificación)
        $pagoExistente = $factura->pagosInformados()->whereIn('estado', ['pendiente', 'aprobado'])->first();
        if ($pagoExistente) {
            $mensaje = $pagoExistente->estado == 'pendiente' 
                ? 'Ya hay un pago informado pendiente de validación para esta factura.'
                : 'Esta factura ya tiene un pago aprobado.';
            return back()->withInput()->with(['status' => 'warning', 'message' => $mensaje, 'icon' => 'fa-exclamation-triangle']);
        }

        // Procesar archivo de comprobante si existe
        $comprobantePath = null;
        if ($request->hasFile('comprobante')) {
            $file = $request->file('comprobante');
            $filename = 'comprobante_factura_' . $factura->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $comprobantePath = $file->storeAs('comprobantes_pagos', $filename, 'public');
        }

        // Convertir importe de formato argentino a decimal
        $importeInformado = $this->floatvalue($request->importe_informado);

        // Crear registro de pago informado
        $pagoInformado = new PagoInformado();
        $pagoInformado->factura_id = $factura->id;
        $pagoInformado->user_id = $user->id;
        $pagoInformado->importe_informado = $importeInformado;
        $pagoInformado->fecha_pago_informado = Carbon::createFromFormat('d/m/Y', $request->fecha_pago_informado);
        $pagoInformado->tipo_transferencia = $request->tipo_transferencia;
        $pagoInformado->banco_origen = $request->banco_origen;
        $pagoInformado->numero_operacion = $request->numero_operacion;
        $pagoInformado->cbu_origen = $request->cbu_origen;
        $pagoInformado->titular_cuenta = $request->titular_cuenta;
        $pagoInformado->estado = 'pendiente';
        $pagoInformado->comprobante_path = $comprobantePath;
        $pagoInformado->save();

        // Enviar correo de confirmación al cliente
        try {
            Mail::send('email.pago_informado_pendiente', ['pagoInformado' => $pagoInformado], function ($message) use ($pagoInformado) {
                $message->to($pagoInformado->usuario->email, $pagoInformado->usuario->firstname . ' ' . $pagoInformado->usuario->lastname);
                $message->subject('REDIN - Pago Informado - Pendiente de Validación');
            });
        } catch (\Exception $e) {
            // Log del error pero no interrumpir el flujo
            Log::error('Error enviando correo de pago informado: ' . $e->getMessage());
        }

        return redirect('/my-invoice/detail/' . $factura->id)->with([
            'status' => 'warning', 
            'message' => 'Pago informado correctamente. Su factura permanecerá PENDIENTE hasta que nuestro equipo valide la información en las próximas 24-48 horas. Se ha enviado un correo de confirmación a su email.', 
            'icon' => 'fa-clock-o'
        ]);
    }
}
