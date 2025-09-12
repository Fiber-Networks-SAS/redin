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

class UserController extends Controller
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
        
        return View::make('user.list');
    }

    public function getList_admin()
    {

        // get admins - alternative approach to avoid Laravel 5.3 compact() bug
        $users = User::with('roles')
                     ->get()
                     ->filter(function ($user) {
                         return $user->roles->contains('name', 'admin');
                     });

        return Datatables::of($users)->make(true);
    }

    public function view_admin($id)
    {
        $user = User::find($id);

        if ($user->hasRole('admin')) {

            return View::make('user.view')->with(['user' => $user]);
        
        }else{
            
            return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    public function create_admin()
    {

        return View::make('user.create');
    }

    public function store_admin(Request $request)
    {
        
        // return $request->all();

        //-- VALIDATOR START --//
        $rules = array(
            'firstname'         => 'required|min:3|max:50',
            'lastname'          => 'required|min:3|max:50',
            'email'             => 'required|email|unique:users',  // unique - verifica que no exista en la DB el email
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

        
        $user = new User;
        $user->firstname  = $request->firstname;
        $user->lastname  = $request->lastname;
        $user->email = $request->email;
        $user->calle = $request->calle;
        $user->tel1 = $request->tel1;
        $user->status = $request->has('status') ? 1 : 0;
        $user->password = Hash::make($request->password);

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
            
            // attach role to user 2:admin
            $user->attachRole(2);

            return redirect('/admin/users')->with(['status' => 'success', 'message' => 'El Usuario fué creado.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/admin/users')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    public function profile_admin()
    {
        
        return View::make('user.profile');
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

        if (!$user->hasRole('owner')) {

            return View::make('user.edit')->with(['user' => $user]);
        
        }else{
            
            return back()->withInput()->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }

    public function updateUser_admin(Request $request, $id)
    {

        return $this->update_admin($request, $id, '/admin/users');
    }

    public function update_admin($request, $id, $route = null)
    {
        //-- VALIDATOR START --//
        $rules = array(
            'firstname'         => 'required|min:3|max:50',
            'lastname'          => 'required|min:3|max:50',
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
        $user->firstname  = $request->firstname ? $request->firstname : '';
        $user->lastname  = $request->lastname;
        $user->email = $request->email;
        $user->calle = $request->calle;
        $user->tel1 = $request->tel1;
        
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

            if (!is_null($route)) {
            
                return redirect($route)->with(['status' => 'success', 'message' => 'El Usuario fué modificado.', 'icon' => 'fa-smile-o']);
            
            }else{

                return back()->withInput()->with(['status' => 'success', 'message' => 'El Usuario fué modificado.', 'icon' => 'fa-smile-o']);
                
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

            return ['status' => 'success', 'message' => 'El Usuario fué modificado.', 'icon' => 'fa-smile-o'];

        }else{
            
            return ['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o'];
        
        }
    }



    // --------------------------------------------------------------------------------------------

}
