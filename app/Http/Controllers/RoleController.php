<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Storage;

use View;
use Validator;
// use Intervention\Image\ImageManagerStatic as Image;
use Yajra\Datatables\Datatables;

// use App\User;
use App\Role;
use App\Permission;
// use Entrust;

use Carbon\Carbon;

class RoleController extends Controller
{
    

    public function __construct()
    {

    }

    public function index()
    {
        return View::make('role.list');
    }

    public function getList()
    {
        
        $roles = Role::where('name', '<>', 'owner')->get();

        foreach ($roles as $role) {
            $role->perms;
        }

        return Datatables::of($roles)->make(true);
    }

    public function create()
    {
        
        $perms = Permission::all();

        return View::make('role.create')->with(['perms' => $perms]);
    }

    public function store(Request $request)
    {
        
        // return $request->all();

        //-- VALIDATOR START --//
        $rules = array(
            'display_name'      => 'required|min:3|max:50',
            'description'       => 'required|min:5|max:255',
            );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//

        
        $role = new Role;
        $role->name = strtolower( str_replace(' ', '_', $request->display_name) );
        $role->display_name  = $request->display_name;
        $role->description = $request->description;
        
        if ($role->save()) {
            
            // SAVE PERMS
            if($request->perms != null){
                $role->perms()->sync(array_keys($request->perms));
            }

            return redirect('/roles')->with(['status' => 'success', 'message' => 'El Rol fué creado.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/roles')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }
    }


    public function edit($id)
    {
        
        $role = Role::find($id);
        // $role->perms;

        // get all permissions
        $perms = Permission::all();

        // get ONLY permissions of role
        if($role){
             $role_perms = [];
             foreach ($role->perms()->get() as $perm) {
                $role_perms[] = $perm->id;
             }
             $role->perms = $role_perms;
        }

        // return $role;

        return View::make('role.edit')->with(['role' => $role, 'perms' => $perms]);

    }

    public function update(Request $request, $id)
    {
        
        //-- VALIDATOR START --//
        $rules = array(
            'display_name'      => 'required|min:3|max:50',
            'description'       => 'required|min:5|max:255',
            );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        
        $role = Role::find($id);
        $role->name = strtolower( str_replace(' ', '_', $request->display_name) );
        $role->display_name  = $request->display_name;
        $role->description = $request->description;
        
        if ($role->save()) {
            
            // SAVE PERMS
            if($request->perms != null){
            
                $role->perms()->sync(array_keys($request->perms));
            
            }else{
                // si el usuario actual es admin y desactiva todos sus permisos, deja activo el permiso de acceso al admin
                $currentUserRole = Auth::user()->roles;
                $currentUserRole = $currentUserRole[0];

                if($id == $currentUserRole->id){
                    $role->perms()->sync(array(2));
                }else{
                    $role->perms()->sync(array());
                }
            }

            return redirect('/roles')->with(['status' => 'success', 'message' => 'El Rol fué modificado.', 'icon' => 'fa-smile-o']);

        }else{
            
            return redirect('/roles')->with(['status' => 'danger', 'message' => 'Ha ocurrido un error.', 'icon' => 'fa-frown-o']);
        
        }

    }
    
}
