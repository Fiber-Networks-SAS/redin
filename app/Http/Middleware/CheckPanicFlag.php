<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\File;

class CheckPanicFlag
{
    public function handle($request, Closure $next)
    {
        $allowedPaths = [
            'POST:api/panic/toggle',  // Fix: Agregado prefijo 'api/' para coincidir con la ruta real
            'GET:api/panic/status'    // Fix: Agregado prefijo 'api/' para coincidir con la ruta real
        ];

        $signature = $request->method() . ':' . ltrim($request->path(), '/');
        if (in_array($signature, $allowedPaths)) {
            return $next($request);
        }

        if (File::exists(base_path('panic.flag'))) {
            // Devolver el contenido de la vista desde public
            $viewContent = File::get(public_path('panic.blade.php'));
            return response($viewContent, 402)->header('Content-Type', 'text/html');
        }

        return $next($request);
    }
}
