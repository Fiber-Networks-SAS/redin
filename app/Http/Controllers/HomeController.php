<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

use View;
use Validator;
use Config;

use Illuminate\Support\Facades\Hash;
use App\User;
use App\Usuario;
use App\Role;
use App\Permission;


class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function phpinfo()
    {
        phpinfo();
    }

    public function landing()
    {

        // return Hash::make('34691');

        // landimng page under construction
        // return View::make('construction');

        // landimng page final
        return View::make('landing');


        // send Mail
        // Mail::send('landing', [],function ($message)
        //  {
        //     $message->from(config('constants.account_no_reply'), config('constants.title'))
        //             ->to('cabenitez83@gmail.com')
        //             ->subject('test con gmail');
        // });
        
        // return 1;
        
    }

    public function index()
    {
        if (Auth::check() && Auth::user()->status && Auth::user()->hasRole('client') && Auth::user()->fecha_registro != null) {
        
            return redirect('dashboard');
        
        } else {
        
            return View::make('home');
        
        }   
    }

    public function login(Request $request)
    {
        
        // return $request->all();

        //-- VALIDATOR START --//
        $rules = array(
            'email'       => 'required',  // unique - verifica que no exista en la DB el email
            'password'    => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return redirect::back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        // get POST data
        $userdata = array(
            'email' => $request->email,
            'password' => $request->password,
        );


        if (Auth::attempt($userdata) && Auth::user()->status && Auth::user()->hasRole('client') && Auth::user()->fecha_registro != null) {
        
            return redirect('dashboard');
        
        } else {
        
            return back()->withInput()->with('login_errors', true);
        
        }

    }   

    public function logout()
    {
        Auth::logout();
        return redirect('/')->with('logout', true);
    }  

    public function contactUs(Request $request)
    {
        //-- VALIDATOR START --//
        $rules = array(
            'name'    => 'required',
            'phone'   => 'required',
            'message' => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return back()->withInput()->withErrors($validator);
          // return Redirect::to(URL::previous())->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        // For work edit file : \vendor\zizaco\entrust\src\Entrust\Traits\EntrustRoleTrait.php #52
        $adminRole = Role::where('name', 'admin')->first();
        $users = $adminRole ? $adminRole->users()->get() : collect([]);

        // get emails
        $emails = [];
        foreach ($users as $user) {
            $emails[] = $user->email;
        }

        try {

            // send Mail
            Mail::send('email.contactus', ['url' => $request->root(), 'request' => $request], function ($message) use ($emails)
             {
                $message->from(config('constants.account_no_reply'), config('constants.title'))
                        // ->to($emails)
                        ->to(config('constants.account_no_reply'))
                        // ->to('cabenitez83@gmail.com')
                        ->subject('Formulario de Contacto');
            });
            
        } catch (\Exception $e) {
            
            $errorMessage = $e;
        }

    }

    
    // admin --------------------------------------------------------------------


    public function index_admin()
    {
        
        if (Auth::check() && Auth::user()->status && (Auth::user()->hasRole('owner') || Auth::user()->hasRole('admin')) ) {

            return redirect('/admin/dashboard');
        
        } else {
        
            return View::make('home_admin');
        
        }   
    }

    public function login_admin(Request $request)
    {
        
        // return Input::all();

        //-- VALIDATOR START --//
        $rules = array(
            'email'       => 'required',  // unique - verifica que no exista en la DB el email
            'password'    => 'required',
        );

        $validator = Validator::make($request->all(), $rules);
        
        if($validator->fails())
        {       
          return redirect::back()->withInput()->withErrors($validator);
        }
        //-- VALIDATOR END --//


        // get POST data
        $userdata = array(
            'email' => $request->email,
            'password' => $request->password,
        );

        if (Auth::attempt($userdata) && Auth::user()->status && (Auth::user()->hasRole('owner') || Auth::user()->hasRole('admin')) ) {
        
            return redirect('/admin/dashboard');
        
        } else {
        
            return back()->withInput()->with('login_errors', true);
        
        }

    }   

    public function logout_admin()
    {
        Auth::logout();
        return redirect('/admin')->with('logout', true);
    }  



}
