<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use View;

class DashboardController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {

        return View::make('dashboard');
    }

    public function dashboard_admin()
    {

        return View::make('dashboard_admin');
    }

}
