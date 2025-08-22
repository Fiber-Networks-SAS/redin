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
// use Carbon\Carbon;
use Storage;
use Artisan;

class BackupController extends Controller
{
    

    public function __construct()
    {

    }

    public function index()
    {
        $disk = Storage::disk('backup');


        $files = $disk->files();
        $backups = [];
        // make an array of backup files, with their filesize and creation date
        foreach ($files as $k => $f) {
            // only take the zip files into account
            if (substr($f, -4) == '.zip' && $disk->exists($f)) {
                $backups[] = [
                    'file_name' => $f,
                    'file_size' => ceil($disk->size($f) / 1024) . ' KB',
                    'last_modified' => date('d/m/Y H:i:s', $disk->lastModified($f)),
                ];
            }
        }
        // reverse the backups, so the newest one would be on top
        $backups = array_reverse($backups);

        return view("backup.list")->with(['backups' => $backups]);
    }

    public function create()
    {
        try {
            // start the backup process
            Artisan::call('backup:clean');
            Artisan::call('backup:run', ['--only-db' => true]);
            
            $output = Artisan::output();
            $output = explode('Copying ', $output);
            if(count($output) > 0){
                $output = explode(' ', $output[1]);
                $filename = $output[0]; 
            }
            
            return redirect('/admin/backup')->with(['status' => 'success', 'message' => 'El Backup fué creado.', 'icon' => 'fa-smile-o', 'filename' => $filename]);
        
        } catch (Exception $e) {
            
            
            return redirect('/admin/backup')->with(['status' => 'danger', 'message' => 'El Backup no pudo ser creado', 'icon' => 'fa-frown-o']);

        
        }
    }

    public function getBackupFile(Request $request, $filename)
    {

        try {

            $file = storage_path('app/backup') .'/'. $filename;

            return response()->download($file);

        } catch(\Exception $e) {
              
            return View::make('errors.404');
        
        }


    }    
    
}
