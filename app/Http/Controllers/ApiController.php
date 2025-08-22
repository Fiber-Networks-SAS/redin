<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

use Validator;
use DateTime;

use Intervention\Image\ImageManagerStatic as Image;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

use Carbon\Carbon;

use App\User;
use App\Usuario;
use App\Poliza;
use App\Document;
use App\ItemGeneral;
use App\Hijo;

// use App\Pais;
// use App\Provincia;
// use App\Departamento;
// use App\Localidad;

// use App\Seccion;
// use App\Moneda;
// use App\Vehiculo;
// use App\Cuota;

// use only for decrypt process
// use Illuminate\Contracts\Encryption\DecryptException;


/*
--- FOR SEND EMAIL ---
Edit \vendor\swiftmailer\lib\classes\Swift\Transport\StreamBuffer.php
line 259 ish. comment out the $options = array(); and add the below.

//$options = array();
$options['ssl'] = array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true);
*/


class ApiController extends Controller
{

    public function __construct()
    {
        $this->seccionAutos = ['073', '074'];
        $this->cc = ['V' => 'Visa',
                     'M' => 'MasterCard',
                     'D' => 'Discover',
                     'A' => 'American Express',
                    ];

        $this->documents = ['category' => ['1' => 'Circulacion', 
                                           '2' => 'Viajes',
                                        ],
                             'type'    => ['1' => 'Registro',
                                           '2' => 'Cedula Verde',
                                           '3' => 'Cedula Azul',
                                           '4' => 'VTV',
                                           '5' => 'Patente',
                                           '6' => 'Pasaporte',
                                        ],           
                            ];

    }

    public function login(Request $request)
    {
        // return $request->all();

        // get POST data
        $credentials = array(
            'dni' => $request->dni,
            'password' => $request->password,
        );

        try {

            // Attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {

                return response()->json([
                    'result' => [
                        'success' => false,
                        'errorCode' => '401',
                        'errorMessage' => 'Invalid credentials'],
                    'data' => ''], 401);
            }
        } catch (JWTException $e) {

            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '500',
                    'errorMessage' => 'Could not create token'],
                'data' => ''], 500);
        }

        // Testing whether the user answered the questions correctly.
        $user = User::where('dni', '=', $request->dni)->first();

        if ($user->status == 1) {
                
            // update path of picture field
            $user->picture = $request->root().'/pictures/'.$user->picture;
            
            // update path of personal files
            $user->file_dni = $user->file_dni != '' ? $request->root().'/file_dni/'.$user->file_dni : 0;
            $user->file_lc = $user->file_lc != '' ? $request->root().'/file_lc/'.$user->file_lc : 0;

            
            // get info from Usuarios table
            $user->detalleUsuario;
            $user->detalleUsuario->nombre = str_replace(',', '',ucwords(strtolower($user->detalleUsuario->nombre)));

            // get CC info
            $user->cctipo = $this->cc[$user->cctipo];

            // get hijos
            $user->hijos;            

            //Authenticate and send the user token
            return response()->json([
                'result' => [
                    'success' => true,
                    'errorCode' => '',
                    'errorMessage' => ''],
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    ],
            ], 200);
        
        }else{

            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '402',
                    'errorMessage' => 'El Usuario esta deshabilitado'],
                'data' => ''], 401);

        }    

    }

    
    public function create(Request $request)
    {

        //-- VALIDATOR START --//
        $rules = array(
            'dni'             => 'required|min:7|unique:users',  // unique - verifica que no exista en la DB el dni
            'email'           => 'required|email|unique:users',  // unique - verifica que no exista en la DB el email
            // 'password'        => 'required|min:8',
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '402',
                    'errorMessage' => $validator->messages()],
                'data' => ''], 402);

        }
        //-- VALIDATOR END --//


        $currentDate = new DateTime();
        $errorMessage = '';

        // $user = User::where('email', '=', $request->email)->first();

        // // verify email registered
        // if ($user) {

        //     return response()->json([
        //         'result' => [
        //             'success' => false,
        //             'errorCode' => '402',
        //             'errorMessage' => 'The email already exists'],
        //         'data' => ''], 402);

        // }else{

            // cretate new user
            $user = new User;
            $user->dni = $request->dni;
            $user->email = $request->email;
            $user->status = 0;
            $user->password = Hash::make($request->dni);
            $user->remember_token = str_random(60);


            
            try {
                
                // Save user data
                $user->save();
                
                // attach role to user
                $user->attachRole(3); // role 3 => api

                // Create Token based on user
                $token = JWTAuth::fromUser($user);             

                $remember_token_url = $request->root() . '/account/activate/' . $user->remember_token;
                
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

                return response()->json([
                    'result' => [
                        'success' => true,
                        'errorCode' => '',
                        'errorMessage' => $errorMessage],
                    'data' => [
                        'userId' => $user->id,
                        'token' => $token,
                        'activate_url' => $remember_token_url,
                        ],
                ], 200);


            } catch (\Exception $e) {
                
           
                // response for error on db store
                return response()->json([
                    'result' => [
                        'success' => false,
                        'errorCode' => '405',
                        'errorMessage' => 'The user cannot be stored in database, review parameters'],
                    'data' => ''], 405);
            }

        // }

    }

    public function update(Request $request)
    {
        
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                // return response()->json(['user_not_found'], 404);

                return response()->json([
                    'result' => [
                        'success' => false,
                        'errorCode' => '404',
                        'errorMessage' => 'User not found'],
                    'data' => ''], 404);
            }

        } catch (TokenExpiredException $e) {

            // return response()->json(['token_expired'], $e->getStatusCode());
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '405',
                    'errorMessage' => 'Token expired'],
                'data' => ''], 405);            

        } catch (TokenInvalidException $e) {

            // return response()->json(['token_invalid'], $e->getStatusCode());
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '406',
                    'errorMessage' => 'Token invalid'],
                'data' => ''], 406); 

        } catch (JWTException $e) {

            // return response()->json(['token_absent'], $e->getStatusCode());
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '408',
                    'errorMessage' => 'Token absent'],
                'data' => ''], 408);             

        }


        //-- VALIDATOR START --//
        $rules = [];
        if ($request->dni && $request->dni != '' && $request->dni != $user->dni) {
            $rules['dni'] = 'min:8|max:11|unique:users,dni,'.$user->id;
        }

        if ($request->email && $request->email != '' && $request->email != $user->email) {
            $rules['email'] = 'email|unique:users,email,'.$user->id;
        }

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '402',
                    'errorMessage' => $validator->messages()],
                'data' => ''], 402);

        }
        
        //-- VALIDATOR END --//

        // $currentDate = new DateTime();
        $errorMessage = '';

        if ($user->status == 1) {

            // update fields for users table
            if ($request->has('dni'))      { $user->dni = $request->dni; }
            if ($request->has('clave')) { $user->password = Hash::make($request->clave); }
            if ($request->has('email'))    { $user->email = $request->email; }
            
            if ($request->has('pais'))    { $user->pais = $request->pais; }
            if ($request->has('provincia'))    { $user->provincia = $request->provincia; }
            if ($request->has('localidad'))    { $user->localidad = $request->localidad; }
            if ($request->has('departamento'))    { $user->departamento = $request->departamento; }
            if ($request->has('direccion'))    { $user->direccion = $request->direccion; }
            if ($request->has('estadocivil'))    { $user->estadocivil = $request->estadocivil; }
            // if ($request->has('edades'))    { $user->edades = $request->edades; }
            if ($request->has('cctipo'))    { $user->cctipo = $request->cctipo; }
            if ($request->has('ccnumero'))    { $user->ccnumero = $request->ccnumero; }
            if ($request->has('ccvencimiento'))    { $user->ccvencimiento = $request->ccvencimiento; }
            if ($request->has('csc'))    { $user->csc = $request->csc; }
            if ($request->has('cbu'))    { $user->cbu = $request->cbu; }

            
            // update fields for usuarios table
            $usuario = Usuario::find($user->usuario_id);
            if ($request->has('dni'))       { $usuario->dni       = $request->dni; }
            if ($request->has('email'))     { $usuario->email     = $request->email; }
            if ($request->has('nombre'))  { $usuario->nombre    = $request->nombre; }
            // if ($request->has('nombre'))    { $usuario->nombre    = $usuario->nombre .', '. $request->nombre; }
            if ($request->has('productor')) { $usuario->productor = $request->productor; }
            // if ($request->has('fnac'))      { $usuario->fnac      = Carbon::createFromFormat('d/m/Y', $request->fnac); }
            if ($request->has('fnac'))      { $usuario->fnac      = $request->fnac; }
            if ($request->has('sexo'))      { $usuario->sexo      = $request->sexo; }
            if ($request->has('telefono'))  { $usuario->telefono  = $request->telefono; }


            // update hijos
            if ($request->has('hijos')){
                
                // delete all hijos
                Hijo::where('user_id', '=', $user->id)->delete();

                // add new hijos
                foreach ($request->hijos as $hijoNuevo) {

                    $hijo = new Hijo;
                    $hijo->user_id = $user->id;
                    $hijo->nombre = $hijoNuevo['nombre'];
                    $hijo->apellido = $hijoNuevo['apellido'];
                    $hijo->fecha_nac = $hijoNuevo['fecha_nac'];
                    $hijo->save();

                }
            }

            // Store picture
            if($request->has('picture') && $request->picture != ''){                    

                $imageCode = str_replace(' ', '+', $request->picture);
                $type = explode('/', substr($imageCode, 0, strpos($imageCode, ';')))[1];
                
                $imageName = time().'.'.$type;
                $img = Image::make($imageCode)
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

            try {

                // save user
                $user->save();
                
                // save usuario
                $usuario->save();
                
                // Create Token based on user
                $token = JWTAuth::fromUser($user);             

                return response()->json([
                    'result' => [
                        'success' => true,
                        'errorCode' => '',
                        'errorMessage' => $errorMessage],
                    'data' => [
                        'userId' => $user->id,
                        'token' => $token
                        ],
                ], 200);

                
            } catch (\Exception $e) {
                
                // response for error on db store
                return response()->json([
                    'result' => [
                        'success' => false,
                        'errorCode' => '405',
                        'errorMessage' => 'The user cannot be stored in database, review parameters'],
                    'data' => ''], 405);
            }

        }else{

            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '402',
                    'errorMessage' => 'User disabled or pending to approve'],
                'data' => ''], 401);

        }

    }

    public function getPolizas(Request $request)
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                // return response()->json(['user_not_found'], 404);

                return response()->json([
                    'result' => [
                        'success' => false,
                        'errorCode' => '404',
                        'errorMessage' => 'User not found'],
                    'data' => ''], 404);
            }

        } catch (TokenExpiredException $e) {

            // return response()->json(['token_expired'], $e->getStatusCode());
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '405',
                    'errorMessage' => 'Token expired'],
                'data' => ''], 405);            

        } catch (TokenInvalidException $e) {

            // return response()->json(['token_invalid'], $e->getStatusCode());
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '406',
                    'errorMessage' => 'Token invalid'],
                'data' => ''], 406); 

        } catch (JWTException $e) {

            // return response()->json(['token_absent'], $e->getStatusCode());
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '408',
                    'errorMessage' => 'Token absent'],
                'data' => ''], 408);             

        }

        // add Relationships
        // $user->vehiculos = Vehiculo::all();
        // $user->secciones = Seccion::all();
        // $user->monedas = Moneda::all();
        // $user->detalleUsuario;
        // $user->detalleUsuario->nombre = ucwords(strtolower($user->detalleUsuario->nombre));

        // $user->polizas;
        
        $polizas = [];
        
        foreach ($user->polizas as $poliza) {
            
            // $operaciones = $poliza->operacion;

            foreach ($poliza->operacion as $operacion){

                if ($operacion->tipo == 'SG' || $operacion->tipo == 'RN') {
                
                    $poliza->detalleSeccion;
                    $poliza->detalleMoneda;
                    $poliza->detalleCuotas;

                    // assign seccion field
                    $seccionPoliza = $poliza->seccion;

                    // get item General
                    $poliza->itemGeneral;

                    // get item Auto
                    foreach ($poliza->itemGeneral as $itemGeneral) {

                        // get coberturas
                        $itemGeneral->itemCobertura;

                        foreach ($itemGeneral->itemCobertura as $itemCobertura) {

                            $itemCobertura->detalleCobertura;

                            // if (in_array($seccionPoliza, $this->seccionAutos)) {
                            
                                foreach ($itemGeneral->itemAuto as $itemAuto) {

                                    // get detalle Vehiculo
                                    $itemAuto->detalleVehiculo;
                                
                                }
                            
                            // }
                        }

                        // get documents
                        $itemGeneral->documents;

                        foreach ($itemGeneral->documents as $document) {

                            $document->document = $request->root().'/userfiles/' . $user->id . '/' .$document->document;
                            if ($document->document) {
                                $document->category_name = $this->documents['category'][$document->category];
                                $document->type_name = $this->documents['type'][$document->type];
                            }

                        }

                    }

                    $polizas[] = $poliza;
                
                }
            }


        }

        // clear user info
        $response = User::find($user->id);
        
        // get full path of picture
        $response->picture = $request->root().'/pictures/'.$response->picture;

        // update path of personal files
        $response->file_dni = $response->file_dni != '' ? $request->root().'/file_dni/'.$response->file_dni : 0;
        $response->file_lc = $response->file_lc != '' ? $request->root().'/file_lc/'.$response->file_lc : 0;

        // add polizas
        $response['polizas'] = $polizas;

        return $response;

    }


    public function uploadFile(Request $request)
    {
        $errorMessage = '';

        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                // return response()->json(['user_not_found'], 404);

                return response()->json([
                    'result' => [
                        'success' => false,
                        'errorCode' => '404',
                        'errorMessage' => 'User not found'],
                    'data' => ''], 404);
            }

        } catch (TokenExpiredException $e) {

            // return response()->json(['token_expired'], $e->getStatusCode());
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '405',
                    'errorMessage' => 'Token expired'],
                'data' => ''], 405);            

        } catch (TokenInvalidException $e) {

            // return response()->json(['token_invalid'], $e->getStatusCode());
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '406',
                    'errorMessage' => 'Token invalid'],
                'data' => ''], 406); 

        } catch (JWTException $e) {

            // return response()->json(['token_absent'], $e->getStatusCode());
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '408',
                    'errorMessage' => 'Token absent'],
                'data' => ''], 408);             

        }

        //-- VALIDATOR START --//
        $rules = array(
            'category'  => 'required',
            'type'      => 'required',            
            'file'      => 'required',
            'item_id'   => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '402',
                    'errorMessage' => $validator->messages()],
                'data' => ''], 402);

        }
        //-- VALIDATOR END --//

        if ($user->status == 1){

            $item = itemGeneral::find($request->item_id);
            
            if ($item) {

                // upload file
                $fileCode = str_replace(' ', '+', $request->file);
                $fileType = explode('/', substr($fileCode, 0, strpos($fileCode, ';')))[1];
                $fileName = time().'.'.$fileType;
                $filePath = '/userfiles/' . $user->id . '/' . $fileName;
                $fileContent = file_get_contents($fileCode);

                Storage::disk('public')->put($filePath, $fileContent);   


                // save document to DB 
                $document = new Document;
                $document->user_id  = $user->id;
                $document->category = $request->category;
                $document->type = $request->type;
                $document->document = $fileName;
                $document->item_id  = $request->item_id;
                
                if ($request->has('comment'))    { $document->comment   = $request->comment; }
                if ($request->has('expiration')) { $document->expiration = Carbon::createFromFormat('d/m/Y', $request->expiration); }

                $document->save();

                return response()->json([
                    'result' => [
                        'success' => true,
                        'errorCode' => '',
                        'errorMessage' => $errorMessage],
                    'data' => [
                        'filePath' => $request->root() . $filePath,
                        'fileId' => $document->id,
                        ],
                ], 200);        
                    
            }else{

                return response()->json([
                    'result' => [
                        'success' => false,
                        'errorCode' => '402',
                        'errorMessage' => 'El Item no existe'],
                    'data' => ''], 401);

            } 

        }else{

            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '402',
                    'errorMessage' => 'El Usuario esta deshabilitado'],
                'data' => ''], 401);

        } 

    }

    public function deleteFile(Request $request)
    {
        $errorMessage = '';

        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                // return response()->json(['user_not_found'], 404);

                return response()->json([
                    'result' => [
                        'success' => false,
                        'errorCode' => '404',
                        'errorMessage' => 'User not found'],
                    'data' => ''], 404);
            }

        } catch (TokenExpiredException $e) {

            // return response()->json(['token_expired'], $e->getStatusCode());
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '405',
                    'errorMessage' => 'Token expired'],
                'data' => ''], 405);            

        } catch (TokenInvalidException $e) {

            // return response()->json(['token_invalid'], $e->getStatusCode());
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '406',
                    'errorMessage' => 'Token invalid'],
                'data' => ''], 406); 

        } catch (JWTException $e) {

            // return response()->json(['token_absent'], $e->getStatusCode());
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '408',
                    'errorMessage' => 'Token absent'],
                'data' => ''], 408);             

        }

        //-- VALIDATOR START --//
        $rules = array(
            'id'  => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '402',
                    'errorMessage' => $validator->messages()],
                'data' => ''], 402);

        }
        //-- VALIDATOR END --//

        if ($user->status == 1){

            // change status for document to DB 
            $document = Document::find($request->id);
            $document->status = 0;

            $document->save();

            return response()->json([
                'result' => [
                    'success' => true,
                    'errorCode' => '',
                    'errorMessage' => $errorMessage],
                'data' => [
                    // 'filePath' => $request->root() . $filePath,
                    // 'fileId' => $document->id,
                    ],
            ], 200);        
                

        }else{

            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '402',
                    'errorMessage' => 'El Usuario esta deshabilitado'],
                'data' => ''], 401);

        } 

    }


    public function uploadPersonalFile(Request $request)
    {
        
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                // return response()->json(['user_not_found'], 404);

                return response()->json([
                    'result' => [
                        'success' => false,
                        'errorCode' => '404',
                        'errorMessage' => 'User not found'],
                    'data' => ''], 404);
            }

        } catch (TokenExpiredException $e) {

            // return response()->json(['token_expired'], $e->getStatusCode());
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '405',
                    'errorMessage' => 'Token expired'],
                'data' => ''], 405);            

        } catch (TokenInvalidException $e) {

            // return response()->json(['token_invalid'], $e->getStatusCode());
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '406',
                    'errorMessage' => 'Token invalid'],
                'data' => ''], 406); 

        } catch (JWTException $e) {

            // return response()->json(['token_absent'], $e->getStatusCode());
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '408',
                    'errorMessage' => 'Token absent'],
                'data' => ''], 408);             

        }


        // $currentDate = new DateTime();
        $errorMessage = '';

        if ($user->status == 1) {

            // Store file_dni
            if($request->has('file_dni') && $request->file_dni != ''){                    

                // upload file_dni
                $fileCode = str_replace(' ', '+', $request->file_dni);
                $fileType = explode('/', substr($fileCode, 0, strpos($fileCode, ';')))[1];
                $fileName = time().'.'.$fileType;
                $filePathDNI = '/file_dni/' . $fileName;
                $fileContent = file_get_contents($fileCode);

                // upload file_dni
                $img = Storage::disk('public')->put($filePathDNI, $fileContent);  

                if ($img) {
                    // delete old file_dni
                    if ($user->file_dni) {
                        Storage::disk('public')->delete('file_dni/'.$user->file_dni); // Storage::disk('public')->exists('..')
                    }                

                    // update field 
                    $user->file_dni = $fileName;
                }
                
            }

            // Store file_lc
            if($request->has('file_lc') && $request->file_lc != ''){                    

                // upload file_lc
                $fileCode = str_replace(' ', '+', $request->file_lc);
                $fileType = explode('/', substr($fileCode, 0, strpos($fileCode, ';')))[1];
                $fileName = time().'.'.$fileType;
                $filePathLC = '/file_lc/' . $fileName;
                $fileContent = file_get_contents($fileCode);

                // upload file_lc
                $img = Storage::disk('public')->put($filePathLC, $fileContent);  

                if ($img) {
                    // delete old file_lc
                    if ($user->file_lc) {
                        Storage::disk('public')->delete('file_lc/'.$user->file_lc); // Storage::disk('public')->exists('..')
                    }                

                    // update field 
                    $user->file_lc = $fileName;
                }
                
            }

            try {

                // save user
                $user->save();
                
                // Create Token based on user
                $token = JWTAuth::fromUser($user);             

                return response()->json([
                    'result' => [
                        'success' => true,
                        'errorCode' => '',
                        'errorMessage' => $errorMessage],
                    'data' => [
                        'userId' => $user->id,
                        'status' => 1,
                        // 'file_dni_path' => $request->root() . $filePathDNI,
                        // 'file_lc_path' => $request->root() . $filePathLC,
                        ],
                ], 200);

                
            } catch (\Exception $e) {
                
                // response for error on db store
                return response()->json([
                    'result' => [
                        'success' => false,
                        'errorCode' => '405',
                        'errorMessage' => 'The user cannot be stored in database, review parameters'],
                    'data' => ''], 405);
            }

        }else{

            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '402',
                    'errorMessage' => 'User disabled or pending to approve'],
                'data' => ''], 401);

        }

    }

    public function forgotPassword(Request $request)
    {

        // return User::find(1)->cc;
        // return User::find(1)->promocode;

        $currentDate = new DateTime();
        $errorMessage = '';

        $user = User::where('dni', '=', $request->dni)->first();
        // $user = User::where('email', '=', $request->email)->first();

        if (!$user) {
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '402',
                    'errorMessage' => 'The User does not exists'],
                'data' => ''], 402);
        }

        if ( $request->dni ){
        // if ( $request->email ){
            
            $user->remember_token = str_random(60);

            // Save user data
            if ($user->save()) {
                

                $remember_token_url = $request->root() . '/reset/password/' .$user->remember_token;

                Mail::send('email.reset_password', ['remember_token_url' => $remember_token_url], function ($message) use ($user)
                 {
                    $message->from(config('constants.account_no_reply'), config('constants.title'))
                            ->to($user->email)
                            ->subject('Password reset instructions');
                });


                return response()->json([
                    'result' => [
                        'success' => true,
                        'errorCode' => '',
                        'errorMessage' => $errorMessage],
                    'data' => [
                        'userId' => $user->id,
                        'token' => $user->remember_token,
                        ],
                ], 200);

            } else {

                // response for error on db store
                return response()->json([
                    'result' => [
                        'success' => false,
                        'errorCode' => '405',
                        'errorMessage' => 'The password cannot be restored'],
                    'data' => ''], 405);
            }

        } else {

            // response for error on missing params
            return response()->json([
                'result' => [
                    'success' => false,
                    'errorCode' => '403',
                    'errorMessage' => 'Missing params'],
                'data' => ''], 403);
        }
    }

}
