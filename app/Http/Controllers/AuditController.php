<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Storage;

use View;
use Validator;
use Yajra\Datatables\Datatables;
// use Intervention\Image\ImageManagerStatic as Image;
use Carbon\Carbon;

use App\Audit;


class AuditController extends Controller
{
    

    public function __construct()
    {

    }

    public function index()
    {
        return View::make('audit.list');
    }

    public function getList()
    {
        
        $auditorias = Audit::all();
        
        foreach ($auditorias as $auditoria) {
            $auditoria->user;

            switch ($auditoria->event) {
                case 'created':
                    $auditoria->event = 'Creado';
                    break;
                
                case 'updated':
                    $auditoria->event = 'Modificado';
                    break;

                case 'deleted':
                    $auditoria->event = 'Eliminado';
                    break;
                
                default:
                    $auditoria->event;
                    break;
            }

            $auditoria->auditable_type = str_replace('App', '', $auditoria->auditable_type);
            $auditoria->auditable_type = substr($auditoria->auditable_type, 1);

            $auditoria->date = Carbon::parse($auditoria->date)->format('d/m/Y');
            
            // format response
            if ($auditoria->old_values != '[]') {
                $auditoria->old_values = str_replace('{', '', $auditoria->old_values);
                $auditoria->old_values = str_replace('}', '', $auditoria->old_values);
                $auditoria->old_values = str_replace(',', '<br><strong>', $auditoria->old_values);
                $auditoria->old_values = str_replace('":', '</strong>: ', $auditoria->old_values);
                $auditoria->old_values = str_replace('<strong>"', '<strong>', $auditoria->old_values);
                $auditoria->old_values = '<strong>' . substr($auditoria->old_values, 1);
                $auditoria->old_values = str_replace('_', ' ', $auditoria->old_values);
            }

            // format response
            if ($auditoria->new_values != '[]') {
                $auditoria->new_values = str_replace('{', '', $auditoria->new_values);
                $auditoria->new_values = str_replace('}', '', $auditoria->new_values);
                $auditoria->new_values = str_replace(',', '<br><strong>', $auditoria->new_values);
                $auditoria->new_values = str_replace('":', '</strong>: ', $auditoria->new_values);
                $auditoria->new_values = str_replace('<strong>"', '<strong>', $auditoria->new_values);
                $auditoria->new_values = '<strong>' . substr($auditoria->new_values, 1);
                $auditoria->new_values = str_replace('_', ' ', $auditoria->new_values);
            }

        }
        // return $auditorias;
        return Datatables::of($auditorias)->make(true);
    }
    
}
