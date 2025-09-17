<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MercadoPagoService;

class TestMercadoPago extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mercadopago:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test MercadoPago configuration and create a sample preference';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Testing MercadoPago configuration...');
        
        // Verificar configuración
        $accessToken = env('MERCADOPAGO_ACCESS_TOKEN');
        $publicKey = env('MERCADOPAGO_PUBLIC_KEY');
        $baseUrl = env('MERCADOPAGO_BASE_URL', config('app.url'));
        
        $this->info('Access Token: ' . ($accessToken ? 'Configured' : 'Missing'));
        $this->info('Public Key: ' . ($publicKey ? 'Configured' : 'Missing'));
        $this->info('Base URL: ' . $baseUrl);
        
        if (!$accessToken || !$publicKey) {
            $this->error('MercadoPago credentials are missing in .env file');
            return 1;
        }
        
        // Probar creación de preferencia
        $mercadoPagoService = new MercadoPagoService();
        
        $testData = [
            'title' => 'Test Payment',
            'amount' => 100.00,
            'description' => 'Test payment for configuration',
            'external_reference' => 'TEST_' . time(),
            'payer' => [
                'name' => 'Test',
                'surname' => 'User',
                'email' => 'test@example.com'
            ]
        ];
        
        $this->info('Creating test preference...');
        $result = $mercadoPagoService->createPaymentPreference($testData);
        
        if ($result['success']) {
            $this->info('✓ Test preference created successfully!');
            $this->info('Preference ID: ' . $result['preference_id']);
            $this->info('Init Point: ' . $result['init_point']);
        } else {
            $this->error('✗ Failed to create test preference');
            $this->error('Error: ' . $result['error']);
            return 1;
        }
        
        return 0;
    }
}