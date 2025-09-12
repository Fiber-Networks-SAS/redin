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

use View;
use Validator;
use Intervention\Image\ImageManagerStatic as Image;
use Yajra\Datatables\Datatables;

use App\User;
use App\Role;

// use Entrust;
use PDF;
use Carbon\Carbon;

class StaffController extends Controller
{
    

    public function __construct()
    {
        // $this->middleware('auth');

        // Check user permission 
        // $this->middleware('permission:crud-users');
    }

    // admin --------------------------------------------------------------------

    public function index_admin()
    {
        
        return View::make('staff.list');
    }

    public function getList_admin()
    {

        // get staff - alternative approach to avoid Laravel 5.3 compact() bug
        $users = User::with('roles')
                     ->get()
                     ->filter(function ($user) {
                         return $user->roles->contains('name', 'staff');
                     });

        return Datatables::of($users)->make(true);
    }

    public function view_admin($id)
    {
        $user = User::find($id);

        if ($user->hasRole('staff')) {

            return View::make('staff.view')->with(['user' => $user]);
        
        }else{
            
            return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    public function create_admin()
    {

        return View::make('staff.create');
    }

    public function store_admin(Request $request)
    {
        
        // return $request->all();

        //-- VALIDATOR START --//
        $rules = array(
            'dni'               => 'required|numeric|min:7',
            'firstname'         => 'required|min:3|max:50',
            'lastname'          => 'required|min:3|max:50',
            'email'             => 'email|unique:users',  // unique - verifica que no exista en la DB el email
            'picture'           => 'max:2000|mimes:jpg,jpeg,png,gif,bmp',
            );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//

        
        $user = new User;
        $user->dni  = $request->dni;
        $user->firstname  = $request->firstname;
        $user->lastname  = $request->lastname;
        $user->email = $request->email;
        $user->calle = $request->calle;
        $user->tel1 = $request->tel1;
        $user->tel2 = $request->tel2;
        $user->comentario = $request->comentario;
        $user->status = $request->has('status') ? 1 : 0;
        $user->password = Hash::make($request->firstname);

        if ($user->save()) {
            
            // attach role to user 2:admin
            $user->attachRole(4);

            return redirect('/admin/staff')->with(['status' => 'success', 'message' => 'El Personal fué creado.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/staff')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

   
    public function edit_admin($id)
    {
        $user = User::find($id);
        // return $user;

        if (!$user->hasRole('owner')) {

            return View::make('staff.edit')->with(['user' => $user]);
        
        }else{
            
            return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    public function updateUser_admin(Request $request, $id)
    {

        return $this->update_admin($request, $id, '/admin/staff');
    }

    public function update_admin($request, $id, $route = null)
    {
        //-- VALIDATOR START --//
        $rules = array(
            'dni'               => 'required|numeric|min:7',
            'firstname'         => 'required|min:3|max:50',
            'lastname'          => 'required|min:3|max:50',
            'email'             => 'email|unique:users,email,'.$id,  // unique - verifica que no exista en la DB el email
            'picture'           => 'max:2000|mimes:jpg,jpeg,png,gif,bmp',
            );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        
        $user = User::find($id);
        $user->dni  = $request->dni;
        $user->firstname  = $request->firstname ? $request->firstname : '';
        $user->lastname  = $request->lastname;
        $user->email = $request->email;
        $user->calle = $request->calle;
        $user->tel1 = $request->tel1;
        $user->tel2 = $request->tel2;
        $user->comentario = $request->comentario;
        
        if ($id != Auth::user()->id) {
            $user->status = $request->has('status') ? 1 : 0;
        }


        if ($user->save()) {

            if (!is_null($route)) {
            
                return redirect($route)->with(['status' => 'success', 'message' => 'El Personal fué modificado.', 'icon' => 'fa-smile-o']);
            
            }else{

                return back()->withInput()->with(['status' => 'success', 'message' => 'El Personal fué modificado.', 'icon' => 'fa-smile-o']);
                
            }

        }else{
            
            return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    public function updateStatus_admin(Request $request, $id)
    {
        
        $user = User::find($id);
        if ($id != Auth::user()->id) {
            $user->status = $request->status == 'true' ? 1 : 0;
        }
        
        if ($user->save()) {

            return ['status' => 'success', 'message' => 'El Personal fué modificado.', 'icon' => 'fa-smile-o'];

        }else{
            
            return ['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o'];
        
        }
    }



    // --------------------------------------------------------------------------------------------

}
