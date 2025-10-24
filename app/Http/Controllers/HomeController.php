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

        // Obtener servicios activos para mostrar en la landing (excluyendo Internet)
        $servicios = \App\Servicio::where('status', 1)->where('tipo', '!=', 0)->orderBy('nombre')->get();
        
        // Obtener servicios de Internet (tipo 0) para mostrar en sección separada
        $serviciosInternet = \App\Servicio::where('status', 1)->where('tipo', 0)->orderBy('nombre')->get();
        
        // Agregar iconos según el tipo de servicio
        foreach ($servicios as $servicio) {
            $servicio->icono = $this->getServiceIcon($servicio->tipo);
            $servicio->tipo_nombre = $this->getServiceTypeName($servicio->tipo);
        }

        // Agregar iconos a los servicios de Internet
        foreach ($serviciosInternet as $servicio) {
            $servicio->icono = $this->getServiceIcon($servicio->tipo);
            $servicio->tipo_nombre = $this->getServiceTypeName($servicio->tipo);
        }

        // landimng page final
        return View::make('landing')->with(['servicios' => $servicios, 'serviciosInternet' => $serviciosInternet]);


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

    /**
     * Obtiene el icono FontAwesome según el tipo de servicio
     * @param string $tipo
     * @return string
     */
    private function getServiceIcon($tipo)
    {
        switch ($tipo) {
            case '0': // Internet
                return 'fa-wifi';
            case '1': // Teléfono
                return 'fa-phone';
            case '2': // TV
                return 'fa-desktop';
            default:
                return 'fa-cog';
        }
    }

    /**
     * Obtiene el nombre del tipo de servicio
     * @param string $tipo
     * @return string
     */
    private function getServiceTypeName($tipo)
    {
        switch ($tipo) {
            case '0':
                return 'Internet';
            case '1':
                return 'Teléfono';
            case '2':
                return 'Televisión';
            default:
                return 'Otro';
        }
    }

}
