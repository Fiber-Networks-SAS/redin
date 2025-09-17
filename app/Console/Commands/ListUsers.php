<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;

class ListUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:list {--search= : Buscar por nombre o email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List available users for invoice generation';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $search = $this->option('search');
        
        $query = User::query();
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->select(['id', 'firstname', 'lastname', 'email', 'created_at'])
                      ->orderBy('firstname')
                      ->limit(50)
                      ->get();
        
        if ($users->isEmpty()) {
            $this->info('No se encontraron usuarios');
            return 0;
        }
        
        $this->info("Usuarios disponibles:" . ($search ? " (búsqueda: '{$search}')" : ""));
        $this->info(str_repeat('-', 80));
        
        $headers = ['ID', 'Nombre', 'Apellido', 'Email', 'Registrado'];
        $rows = [];
        
        foreach ($users as $user) {
            $rows[] = [
                $user->id,
                $user->firstname ?: 'N/A',
                $user->lastname ?: 'N/A', 
                $user->email ?: 'N/A',
                $user->created_at ? $user->created_at->format('Y-m-d') : 'N/A'
            ];
        }
        
        $this->table($headers, $rows);
        
        if ($users->count() === 50) {
            $this->info("\nSe muestran los primeros 50 resultados. Use --search para filtrar.");
        }
        
        $this->info("\nEjemplos de uso:");
        $this->info("• Factura rápida: php74 artisan invoice:quick {usuario_id} {importe}");
        $this->info("• Factura completa: php74 artisan invoice:generate-single {usuario_id} --amount={importe}");
        
        return 0;
    }
}