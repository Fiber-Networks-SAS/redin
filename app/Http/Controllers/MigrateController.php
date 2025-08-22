<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Carbon\Carbon;

use App\User;
use App\Role;
use App\Permission;
use App\ServiceType;

class MigrateController extends Controller
{
    
    public function migrate()
    {    

      $user = User::find(1);

      // get current datetime
      $now = Carbon::now();

        if (!$user) {

            // SET main roles
            $role_owner = new Role();
            $role_owner->name         = 'owner';
            $role_owner->display_name = 'Programador';
            $role_owner->description  = 'Programador del sistema';
            $role_owner->save();

            $role_admin = new Role();
            $role_admin->name         = 'admin';
            $role_admin->display_name = 'Administrador';
            $role_admin->description  = 'Administrador del sistema';
            $role_admin->save();

            $role_user = new Role();
            $role_user->name         = 'client';
            $role_user->display_name = 'Cliente';
            $role_user->description  = 'Cliente de la empresa';
            $role_user->save();

            $role_staff = new Role();
            $role_staff->name         = 'staff';
            $role_staff->display_name = 'Personal';
            $role_staff->description  = 'Personal de la empresa';
            $role_staff->save();

            //------------------------------------------------------------------------------------//
            
            // SET main permissions
            $perm_admin = new Permission();
            $perm_admin->name         = 'admin';
            $perm_admin->display_name = 'Administrar Sistema';
            $perm_admin->description  = 'Administrar Sistema';
            $perm_admin->save();

            $perm_client = new Permission();
            $perm_client->name         = 'client';
            $perm_client->display_name = 'Utilizar Sistema';
            $perm_client->description  = 'Utilizar Sistema';
            $perm_client->save();

            //------------------------------------------------------------------------------------//
            
            // attach Permission
            $role_owner = Role::where('name', '=', 'owner')->first();
            $role_owner->attachPermissions(array($perm_admin, $perm_client));

            $role_admin = Role::where('name', '=', 'admin')->first();
            $role_admin->attachPermissions(array($perm_admin, $perm_client));

            $role_user = Role::where('name', '=', 'client')->first();
            $role_user->attachPermissions(array($perm_client));

            //------------------------------------------------------------------------------------//

            // SET user OWNER
            $user = new User();
            $user->email = 'cabenitez83@gmail.com';
            $user->password = Hash::make('zaq1!QAZ');
            $user->firstname  = 'Carlos Alberto';
            $user->lastname  = 'Benitez';
            $user->status  = 1;
            $user->save();  

            // attach role to user
            $user = User::where('email', '=', 'cabenitez83@gmail.com')->first();
            $user->attachRole(1); // parameter can be an Role object, array, or id

            //------------------------------------------------------------------------------------//

            // SET user ADMIN
            $user = new User();
            $user->email = 'admin@admin.com';
            $user->password = Hash::make('12345678');
            $user->firstname  = 'Administrador';
            $user->lastname  = 'Sistema';        
            $user->status  = 1;
            $user->save();      

            // attach role to user
            $user = User::where('email', '=', 'admin@admin.com')->first();
            $user->attachRole(2); // parameter can be an Role object, array, or id

            //------------------------------------------------------------------------------------//

            // SET user CLIENT
            // $user = new User();
            // $user->nro_cliente  = '3';
            // $user->dni    = '12345678';
            // $user->email = 'cliente@cliente.com';
            // $user->password = Hash::make('12345678');
            // $user->firstname  = 'Cliente Uno';
            // $user->lastname  = 'Demo';            
            // $user->fecha_registro = $now;            
            // $user->status  = 1;
            // $user->save();      

            // attach role to user
            // $user = User::where('dni', '=', '12345678')->first();
            // $user->attachRole(3); // parameter can be an Role object, array, or id

            //------------------------------------------------------------------------------------//
 
            // SET user STAFF
            // $user = new User();
            // $user->dni    = '23456789';
            // $user->email = 'personal@personal.com';
            // $user->password = Hash::make('23456789');
            // $user->firstname  = 'Personal';
            // $user->lastname  = 'Demo';            
            // $user->calle  = 'Calle falsa 123';            
            // $user->status  = 1;
            // $user->save();      

            // attach role to user
            // $user = User::where('dni', '=', '23456789')->first();
            // $user->attachRole(4); // parameter can be an Role object, array, or id






            return 'The migration process was successfully executed.';

        }else{


            // attach role to clients imported
            $user = User::find(3);
            if ($user) {
                  $users = User::where('id', '>', '2')->get();
                  foreach ($users as $user) {
                        $user->attachRole(3); // parameter can be an Role object, array, or id
                  }
                  return 'attached role for users.';
            }


            return 'The migration process has already been executed.';
        }

        // return View::make('dashboard');
    }

}
