<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Storage;

use View;
use Validator;
use Yajra\Datatables\Datatables;
// use Intervention\Image\ImageManagerStatic as Image;
use Carbon\Carbon;

use App\User;
use App\Reclamo;


class ClaimController extends Controller
{
    

    public function __construct()
    {
        // $this->leido = ['Internet', 'Telefonía', 'Televisión'];
    }

    public function index()
    {

        return View::make('reclamo.list');

    }

    public function getList()
    {
        
        $reclamos = Reclamo::where('user_to', 0)
                           ->where('parent_id', 0)
                           ->get();

        // obtengo los datos del usuario
        foreach ($reclamos as $reclamo) {
            $reclamo->usuario;
            $reclamo->servicio;
            $reclamo->fecha = Carbon::parse($reclamo->created_at)->format('d/m/Y');
        }
        
        return Datatables::of($reclamos)->make(true);
    }

    public function reply($id)
    {

        // obtengo el reclamo principal
        $reclamo = Reclamo::find($id);

        // marco como leido 
        $reclamo->leido_admin  = 1;
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
        return View::make('reclamo.reply')->with(['reclamo' => $reclamo]);

    }

    public function replyPost(Request $request, $id)
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
        // return $reclamoMain->usuario;


        $reclamo = new Reclamo;
        $reclamo->user_from   = $user->id;
        $reclamo->user_to     = $reclamoMain->user_from;
        $reclamo->servicio_id = $reclamoMain->servicio_id;
        $reclamo->titulo      = '';
        $reclamo->mensaje     = $request->mensaje;
        $reclamo->parent_id   = $reclamoMain->id;
        // $reclamo->leido_client = 0;
        // $reclamo->leido_admin  = 0;


        if ($reclamo->save()) {

            // actualizo los campos del reclamo principal
            $reclamoMain->leido_admin  = 1;
            $reclamoMain->replys  = $reclamoMain->replys . $reclamo->id.',';
            $reclamoMain->leido_client  = 0;
            $reclamoMain->status  = 0; // lo marco como abierto
            $reclamoMain->save();

            // send Mail
            $reclamoMain->response_path = $request->root() . '/login';
            Mail::send('email.reclamo', ['reclamoMain' => $reclamoMain], function ($message) use ($reclamoMain)
             {
                $message->from(config('constants.account_no_reply'), config('constants.title'))
                        ->to($reclamoMain->usuario->email)
                        ->subject('Tu reclamo fué respondido');
            });


            return redirect('/admin/claims')->with(['status' => 'success', 'message' => 'El Reclamo Nro. '.$reclamoMain->id.' fué respondido.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/claims')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }

    }    


    public function getListUnread()
    {
        
        $reclamos = Reclamo::where('user_to', 0)
                           ->where('parent_id', 0)
                           ->where('leido_admin', 0)
                           ->get();

        // obtengo los datos del usuario
        foreach ($reclamos as $reclamo) {
            $reclamo->usuario;
            $reclamo->fecha = Carbon::parse($reclamo->created_at)->format('d/m/Y H:i');
        }

        return $reclamos;
    }

    public function close($id)
    {
        
        $reclamo = Reclamo::find($id);
        $reclamo->status  = 1;

        if ($reclamo->save()) {

            return redirect('/admin/claims')->with(['status' => 'success', 'message' => 'El Reclamo Nro. '.$reclamo->id.' fué cerrado.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/claims')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }

    }


}
