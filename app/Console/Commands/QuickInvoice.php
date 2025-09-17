<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\Factura;

class QuickInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:quick 
                            {user_id : ID del cliente} 
                            {amount : Importe de la factura}
                            {--desc=Servicio de Internet : Descripción del servicio}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a quick invoice with PDF and QR codes automatically';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $amount = $this->argument('amount');
        $description = $this->option('desc');
        
        // Verificar usuario
        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario con ID {$userId} no encontrado");
            return 1;
        }
        
        $this->info("Generando factura rápida...");
        $this->info("Cliente: {$user->firstname} {$user->lastname}");
        $this->info("Importe: \${$amount}");
        $this->info("Descripción: {$description}");
        
        // Llamar al comando principal con parámetros automáticos
        $this->call('invoice:generate-single', [
            'user_id' => $userId,
            '--amount' => $amount,
            '--service' => $description,
            '--pdf' => true,
            '--qr' => true,
            '--period' => date('Y-m')
        ]);
        
        return 0;
    }
}