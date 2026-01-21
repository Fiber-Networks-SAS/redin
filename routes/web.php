<?php
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

// Route::get('/phpinfo', 'HomeController@phpinfo');

// Auth::routes();


// Fill tables - Initial Migration
Route::get('migrate', 'MigrateController@migrate');


// landing --------------------------------------------------------------------

// landing
Route::get('/', 'HomeController@landing');

// contact us
Route::post('contact', 'HomeController@contactUs');

// clients --------------------------------------------------------------------

// login
Route::get('login', 'HomeController@index');
Route::post('login', 'HomeController@login');
Route::get('logout', 'HomeController@logout');

// register
Route::post('register', 'ClientController@register');

// Activate Account
Route::get('/account/activate/{token?}', 'ClientController@accountActivate');

// Forgot password
Route::get('forgot_password', 'ClientController@forgotPassword');
Route::post('forgot_password', 'ClientController@forgotPasswordPost');

// Reset Password
Route::get('/reset/password/{token?}', 'ClientController@resetPassword');
Route::post('/reset/password', 'ClientController@updatePassword');

// ver factura del email
Route::get('/invoice/{token?}', 'ClientController@getEmailInvoiceDownload');

Route::group(['middleware' => ['role:client']], function() {

	// dashboard
	Route::get('dashboard', 'DashboardController@dashboard');

	// profile
	Route::get('profile', 'ClientController@profile');
	Route::post('profile', 'ClientController@updateProfile');

	// mis facturas
	Route::get('/my-invoice', 'ClientController@myInvoice');
	Route::get('/my-invoice/list', 'ClientController@getMyInvoiceList'); 						// request for fill Table (via Ajax)
	Route::get('/my-invoice/detail/{id}', 'ClientController@getInvoiceDetail');
	
	Route::get('/my-invoice/download/{id}', 'ClientController@getInvoiceDownload');
	Route::get('/my-invoice/update/{id}', 'ClientController@getInvoiceUpdate');
	Route::post('/my-invoice/update/{id}', 'BillController@getBillUpdatePost');

	Route::get('/my-invoice/pay/{id}', 'ClientController@pay');
	Route::post('/my-invoice/process-payment/{id}', 'ClientController@processPayment');
	
	// Informar pago por CBU/Transferencia
	Route::get('/my-invoice/inform-payment/{id}', 'ClientController@informPayment');
	Route::post('/my-invoice/inform-payment/{id}', 'ClientController@storeInformedPayment');
	Route::get('/my-claims', 'ClientController@myClaims');
	Route::get('/my-claims/list', 'ClientController@getMyClaimsList'); 						// request for fill Table (via Ajax)
	Route::get('/my-claims/detail/{id}', 'ClientController@getClaimsDetail');
	
	Route::get('/my-claims/create', 'ClientController@MyClaimsCreate');
	Route::post('/my-claims/create', 'ClientController@MyClaimsStore');
	Route::get('/my-claims/reply/{id}', 'ClientController@MyClaimsReply');
	Route::post('/my-claims/reply/{id}', 'ClientController@MyClaimsReplyPost');	
	Route::get('/my-claims/listUnread', 'ClientController@getListUnread');
	Route::get('/my-claims/close/{id}', 'ClientController@MyClaimsClose');	
});


// admin --------------------------------------------------------------------

Route::group(['prefix' => 'admin'], function() {
	
	// home
	Route::get('/', 'HomeController@index_admin');
	Route::get('/login', 'HomeController@index_admin');
	Route::post('/login', 'HomeController@login_admin');

	Route::group(['middleware' => ['role:owner|admin']], function() {

		// phpinfo
		Route::get('/phpinfo', 'HomeController@phpinfo');	

		// logout
		Route::get('/logout', 'HomeController@logout_admin');

		// dashboard
		Route::get('/dashboard', 'DashboardController@dashboard_admin');

		// profile
		Route::get('/profile', 'UserController@profile_admin');
		Route::post('/profile', 'UserController@updateProfile_admin');

		// services
		Route::get('/services', 'ServiceController@index');
		Route::get('/services/list', 'ServiceController@getList'); 						// request for fill Table (via Ajax)
		Route::get('/services/create', 'ServiceController@create');
		Route::post('/services/create', 'ServiceController@store');
		Route::get('/services/edit/{id}', 'ServiceController@edit');
		Route::post('/services/edit/{id}', 'ServiceController@update');
		Route::post('/services/updatestatus/{id}', 'ServiceController@updateStatus');

		// users
		Route::get('/users', 'UserController@index_admin');
		Route::get('/users/list', 'UserController@getList_admin'); 									// request for fill Table (via Ajax)
		Route::get('/users/create', 'UserController@create_admin');
		Route::post('/users/create', 'UserController@store_admin');
		Route::get('/users/view/{id}', 'UserController@view_admin');
		Route::get('/users/edit/{id}', 'UserController@edit_admin');
		Route::post('/users/edit/{id}', 'UserController@updateUser_admin');
		Route::post('/users/updatestatus/{id}', 'UserController@updateStatus_admin');

		// staff
		Route::get('/staff', 'StaffController@index_admin');
		Route::get('/staff/list', 'StaffController@getList_admin'); 								// request for fill Table (via Ajax)
		Route::get('/staff/create', 'StaffController@create_admin');
		Route::post('/staff/create', 'StaffController@store_admin');
		Route::get('/staff/view/{id}', 'StaffController@view_admin');
		Route::get('/staff/edit/{id}', 'StaffController@edit_admin');
		Route::post('/staff/edit/{id}', 'StaffController@updateUser_admin');
		Route::post('/staff/updatestatus/{id}', 'StaffController@updateStatus_admin');

		// clients
		Route::get('/clients', 'ClientController@index_admin');
		Route::get('/clients/list', 'ClientController@getList_admin'); 								// request for fill Table (via Ajax)
		Route::get('/clients/create', 'ClientController@create_admin');
		Route::post('/clients/create', 'ClientController@store_admin');
		Route::get('/clients/view/{id}', 'ClientController@view_admin');
		Route::get('/clients/edit/{id}', 'ClientController@edit_admin');
		Route::post('/clients/edit/{id}', 'ClientController@updateClient_admin');
		Route::post('/clients/updatestatus/{id}', 'ClientController@updateStatus_admin');			// clients
		Route::post('/clients/stafflist', 'ClientController@getStaffList'); 						// AUTOCOMPLETE - request for fill Table (via Ajax)
		

		// clients services
		Route::get('/clients/services/create', 'ClientController@services_admin');
		Route::post('/clients/services/create', 'ClientController@services_add_admin');					// SAVE
		Route::get('/clients/services/{id}', 'ClientController@services_list_admin');
		Route::get('/clients/services/edit/{id}', 'ClientController@services_edit_admin');
		Route::post('/clients/services/edit/{id}', 'ClientController@services_update_admin');
		Route::post('/clients/services/updatestatus/{id}', 'ClientController@updateClientServiceStatus_admin');		// clients
		Route::post('/clients/clientlist', 'ClientController@getClientList'); 						// AUTOCOMPLETE - request for fill Table (via Ajax)
		Route::post('/clients/servicelist', 'ClientController@getServiceList');						// AUTOCOMPLETE - request for fill Table (via Ajax)
		Route::post('/clients/services/detail', 'ClientController@getServiceDetail');				// GET IMPORTES FOR INPUTS
		Route::get('/clients/services/list/{user_id}', 'ClientController@getClientServiceList'); 	// request for fill Table (via Ajax)
		Route::get('/clients/services/create/{id}', 'ClientController@create_service_admin');
		// Route::post('/clients/services/list', 'ClientController@getClientServiceList'); 			// request for fill Table (via Ajax)

		// clients plan de pagos
		Route::get('/clients/payment_plan/create', 'ClientController@payment_plan_admin');
		Route::post('/clients/payment_plan/create', 'ClientController@payment_plan_add_admin');					// SAVE
		Route::get('/clients/payment_plan/{id}', 'ClientController@payment_plan_list_admin');
		Route::get('/clients/payment_plan/edit/{id}', 'ClientController@payment_plan_edit_admin');
		Route::post('/clients/payment_plan/edit/{id}', 'ClientController@payment_plan_update_admin');
		Route::post('/clients/payment_plan/updatestatus/{id}', 'ClientController@update_payment_plan_status_admin');		// clients
		Route::get('/clients/payment_plan/list/{user_id}', 'ClientController@getClientPaymentPlanList'); 	// request for fill Table (via Ajax)
		Route::get('/clients/payment_plan/create/{id}', 'ClientController@create_payment_plan_admin');

		
		// clients bills
		Route::get('/clients/bills/{id}', 'ClientController@bills_list_admin');
		Route::get('/clients/bills/list/{user_id}', 'ClientController@getClientBillsList'); 	// request for fill Table (via Ajax)

		// config. - talonarios
		Route::get('/config/invoice', 'ConfigInvoiceController@index');
		Route::get('/config/invoice/list', 'ConfigInvoiceController@getList'); 						// request for fill Table (via Ajax)
		Route::get('/config/invoice/create', 'ConfigInvoiceController@create');
		Route::post('/config/invoice/create', 'ConfigInvoiceController@store');
		Route::get('/config/invoice/edit/{id}', 'ConfigInvoiceController@edit');
		Route::post('/config/invoice/edit/{id}', 'ConfigInvoiceController@update');
		Route::post('/config/invoice/updatestatus/{id}', 'ConfigInvoiceController@updateStatus');		

		// config - Intereses
		Route::get('/config/interests', 'ConfigInterestController@create');
		Route::post('/config/interests', 'ConfigInterestController@store');

		// config. - cuotas
		Route::get('/config/dues', 'ConfigDuesController@index');
		Route::get('/config/dues/list', 'ConfigDuesController@getList'); 						// request for fill Table (via Ajax)
		Route::get('/config/dues/create', 'ConfigDuesController@create');
		Route::post('/config/dues/create', 'ConfigDuesController@store');
		Route::get('/config/dues/edit/{id}', 'ConfigDuesController@edit');
		Route::post('/config/dues/edit/{id}', 'ConfigDuesController@update');
		Route::post('/config/dues/updatestatus/{id}', 'ConfigDuesController@updateStatus');	

		// config - Pagos
		Route::get('/config/payments', 'ConfigPaymentsController@create');
		Route::post('/config/payments', 'ConfigPaymentsController@store');

		// bonificaciones de servicios
		Route::get('/bonificaciones', 'BonificacionServicioController@index');
		Route::get('/bonificaciones/create', 'BonificacionServicioController@create');
		Route::post('/bonificaciones/create', 'BonificacionServicioController@store');
		Route::get('/bonificaciones/view/{id}', 'BonificacionServicioController@show');
		Route::get('/bonificaciones/edit/{id}', 'BonificacionServicioController@edit');
		Route::post('/bonificaciones/edit/{id}', 'BonificacionServicioController@update');
		Route::post('/bonificaciones/delete/{id}', 'BonificacionServicioController@destroy');
		Route::post('/bonificaciones/toggle/{id}', 'BonificacionServicioController@toggleActive');

		// home contents (CMS)
		Route::get('/home-contents', 'HomeContentController@index');
		Route::get('/home-contents/list', 'HomeContentController@getList');
		Route::get('/home-contents/create', 'HomeContentController@create');
		Route::post('/home-contents/create', 'HomeContentController@store');
		Route::get('/home-contents/view/{id}', 'HomeContentController@show');
		Route::get('/home-contents/edit/{id}', 'HomeContentController@edit');
		Route::post('/home-contents/edit/{id}', 'HomeContentController@update');
		Route::post('/home-contents/delete/{id}', 'HomeContentController@destroy');
		Route::post('/home-contents/toggle/{id}', 'HomeContentController@toggleActive');

        // home settings (CMS global)
        Route::get('/home-settings', 'Admin\HomeSettingsController@edit');
        Route::post('/home-settings', 'Admin\HomeSettingsController@update');

		// periodos
		Route::get('/period', 'BillController@index');
		Route::get('/period/list', 'BillController@getList'); 								// request for fill Table (via Ajax)
		Route::get('/period/create', 'BillController@create');
		Route::post('/period/create', 'BillController@store');
		Route::get('/period/view/{mes}/{ano}', 'BillController@view');
		Route::get('/period/view/list/{mes}/{ano}', 'BillController@getBillPeriodList'); 	// request for fill Table (via Ajax)
		Route::get('/period/send/{mes}/{ano}', 'BillController@sendEmailFacturasPeriodo');
		
		// anular período completo
		Route::get('/period/cancel/{mes}/{ano}', 'BillController@cancelPeriod');
		Route::post('/period/cancel/{mes}/{ano}', 'BillController@cancelPeriodPost');
		
		Route::get('/period/bill/{id}', 'BillController@getBillDetail');
		Route::post('/period/bill-edit/{id}', 'BillController@getBillEditPost');

		Route::get('/period/bill-improve/{id}', 'BillController@getBillImprove');
		Route::post('/period/bill-improve/{id}', 'BillController@getBillImprovePost');
		Route::get('/period/bill-ampliar/{id}', 'BillController@getBillAmpliar');
		Route::post('/period/bill-ampliar/{id}', 'BillController@getBillAmpliarPost');
		Route::get('/period/bill-update/{id}', 'BillController@getBillUpdate');
		Route::post('/period/bill-update/{id}', 'BillController@getBillUpdatePost');
		Route::get('/period/bill-pay/{id}', 'BillController@getBillPay');
		Route::post('/period/bill-pay/{id}', 'BillController@getBillPayPost');
		Route::get('/period/bill-send/{id}', 'BillController@getBillSend');
		Route::get('/period/download/pmc/{mes}/{ano}', 'BillController@setPeriodoPMC');
		
		// generar PDFs del período
		Route::get('/period/generate-pdf', 'BillController@periodPDFHandler');

		// listar facturas sin PDF del período
		Route::get('/period/missing-pdfs', 'BillController@listMissingPeriodPDFs');

		// cancelar pago factura
		Route::get('/period/bill-pay-cancel/{id}', 'BillController@getBillPayCancel');
		Route::post('/period/bill-pay-cancel/{id}', 'BillController@getBillPayCancelPost');
		
		// gestión de pagos informados
		Route::get('/payments/informed', 'BillController@getInformedPayments');
		Route::get('/payments/informed/{id}', 'BillController@getInformedPaymentDetail');
		Route::post('/payments/informed/{id}/approve', 'BillController@approveInformedPayment');
		Route::post('/payments/informed/{id}/reject', 'BillController@rejectInformedPayment');		

		// search bills
		Route::get('/bills/', 'BillController@billSearch');
		Route::get('/bills/list', 'BillController@getBillSearchList'); 	// request for fill Table (via Ajax)


		// single bill
		Route::get('/bills/single', 'BillController@billSingle');
		Route::post('/bills/single', 'BillController@billSingleStore');
		Route::post('/clients/clientlistnotbill', 'ClientController@getClientListNotBill'); 						// AUTOCOMPLETE - request for fill Table (via Ajax)

		// mensajes
		// Route::get('/msgs/', 'BillController@create');
		// Route::post('/msgs/', 'ConfigInterestController@store');

		// reclamos
		Route::get('/claims', 'ClaimController@index');
		Route::get('/claims/list', 'ClaimController@getList');
		Route::get('/claims/reply/{id}', 'ClaimController@reply');
		Route::post('/claims/reply/{id}', 'ClaimController@replyPost');
		Route::get('/claims/listUnread', 'ClaimController@getListUnread');
		Route::get('/claims/close/{id}', 'ClaimController@close');	

		// auditoria
		Route::get('/audit', 'AuditController@index');
		Route::get('/audit/list', 'AuditController@getList'); 						// request for fill Table (via Ajax)
		
		// backup
		Route::get('/backup', 'BackupController@index');
		Route::get('/backup/list', 'BackupController@getList'); 						// request for fill Table (via Ajax)
		Route::get('/backup/create', 'BackupController@create');
		Route::get('/backup/download/{name}', 'BackupController@getBackupFile');

		// cobroexpress
		Route::get('/cobroexpress', 'CobroexpressController@index');
		Route::post('/cobroexpress', 'CobroexpressController@import');
		Route::post('/cobroexpress/search', 'CobroexpressController@cobroexpressSearch');
		Route::get('/cobroexpress/comprobante-pdf', 'CobroexpressController@getCobroexpressPDF');
		Route::get('/cobroexpress/comprobante-xls', 'CobroexpressController@getCobroexpressXLS');

		// balance
		Route::get('/balance/general', 'BillController@balance');
		Route::post('/balance/general/search', 'BillController@balanceSearch');
		Route::get('/balance/general/comprobante-pdf', 'BillController@getBalancePDF');
		Route::get('/balance/general/comprobante-xls', 'BillController@getBalanceXLS');

		// balance detalle
		Route::get('/balance/detail', 'BillController@balanceDetalle');
		Route::post('/balance/detail/search', 'BillController@balanceDetalleSearch');
		Route::get('/balance/detail/comprobante-pdf', 'BillController@getBalanceDetallePDF');
		Route::get('/balance/detail/comprobante-xls', 'BillController@getBalanceDetalleXLS');


		// regenerar PDF factura individual
		Route::post('/bill/regenerate-pdf/{id}', 'BillController@regenerateBillPDF');
		// regenerar PDF múltiples facturas
		Route::post('/bill/regenerate-pdf', 'BillController@regenerateBillPDF');

		// temp --------------------------------------------------------------------------------------

		// facturas pdf
		Route::get('/period/facturas', 'BillController@tempFacturasPDF');
		
		// factura email
		Route::get('/factura_email', 'BillController@tempFacturasEmail');

		Route::get('/merge_pdf', 'BillController@tempMergePDF');

	});

});

// Payment routes - MercadoPago callbacks
Route::get('/payment/success', 'PaymentController@paymentSuccess')->name('payment.success');
Route::get('/payment/failure', 'PaymentController@paymentFailure')->name('payment.failure'); 
Route::get('/payment/pending', 'PaymentController@paymentPending')->name('payment.pending');

// Webhook for MercadoPago notifications
Route::post('/webhooks/mercadopago', 'PaymentController@mercadoPagoWebhook')->name('payment.webhook');

// API route for checking payment status
Route::get('/api/payment/preference/{id}/status', 'PaymentController@checkPaymentStatus')->name('payment.status');

// Diagn�stico AFIP - Certificados
Route::get('afip-cert-debug', function () {
    $details = [];
    $success = false;
    $message = '';
    
    try {
        // Paths esperados para certificados AFIP
        $certPaths = [
            'storage_app' => storage_path('app'),
            'storage_public' => storage_path('app/public'),
            'public_storage' => public_path('storage')
        ];
        
        $details['paths'] = $certPaths;
        
        // Buscar archivos de certificados comunes
        $certExtensions = ['crt', 'pem', 'p12', 'pfx', 'cer'];
        $foundCerts = [];
        
        foreach ($certPaths as $pathName => $path) {
            if (is_dir($path)) {
                $details['path_status'][$pathName] = 'exists';
                
                // Buscar certificados en este directorio
                $files = glob($path . '/*');
                foreach ($files as $file) {
                    $info = pathinfo($file);
                    if (isset($info['extension']) && in_array(strtolower($info['extension']), $certExtensions)) {
                        $foundCerts[$pathName][] = [
                            'file' => basename($file),
                            'full_path' => $file,
                            'size' => filesize($file),
                            'readable' => is_readable($file),
                            'modified' => date('Y-m-d H:i:s', filemtime($file)),
                            'extension' => strtolower($info['extension'])
                        ];
                    }
                }
            } else {
                $details['path_status'][$pathName] = 'not_exists';
            }
        }
        
        $details['certificates_found'] = $foundCerts;
        
        // Verificar variables de entorno relacionadas con AFIP
        $afipEnvVars = [
            'AFIP_CUIT',
            'AFIP_CERT', 
            'AFIP_KEY',
            'AFIP_ACCESS_TOKEN',
            'AFIP_PRODUCTION'
        ];
        
        $envValues = [];
        foreach ($afipEnvVars as $var) {
            $value = env($var);
            $envValues[$var] = [
                'value' => $value ? (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value) : 'NOT SET',
                'is_set' => !is_null($value),
                'length' => $value ? strlen($value) : 0
            ];
        }
        $details['environment_variables'] = $envValues;
        
        // Verificar archivos .env
        $envFile = base_path('.env');
        $details['env_file'] = [
            'exists' => file_exists($envFile),
            'readable' => file_exists($envFile) ? is_readable($envFile) : false,
            'size' => file_exists($envFile) ? filesize($envFile) : 0,
            'modified' => file_exists($envFile) ? date('Y-m-d H:i:s', filemtime($envFile)) : 'N/A'
        ];
        
        // Test de lectura de certificado si encontramos alguno
        $certTestResults = [];
        foreach ($foundCerts as $pathName => $certs) {
            foreach ($certs as $cert) {
                if ($cert['readable'] && in_array($cert['extension'], ['pem', 'crt'])) {
                    $content = file_get_contents($cert['full_path']);
                    $certTestResults[] = [
                        'file' => $cert['file'],
                        'path' => $pathName,
                        'size' => strlen($content),
                        'starts_with' => substr($content, 0, 50),
                        'ends_with' => substr($content, -50),
                        'has_begin_cert' => strpos($content, '-----BEGIN CERTIFICATE-----') !== false,
                        'has_end_cert' => strpos($content, '-----END CERTIFICATE-----') !== false,
                        'has_begin_private' => strpos($content, '-----BEGIN PRIVATE KEY-----') !== false,
                        'line_endings' => [
                            'unix_lf' => substr_count($content, "\n"),
                            'windows_crlf' => substr_count($content, "\r\n"),
                            'mac_cr' => substr_count($content, "\r") - substr_count($content, "\r\n")
                        ]
                    ];
                    
                    // Solo testear los primeros 3 certificados para evitar timeout
                    if (count($certTestResults) >= 3) break 2;
                }
            }
        }
        $details['certificate_tests'] = $certTestResults;
        
        // Informaci�n del sistema
        $details['system_info'] = [
            'php_version' => phpversion(),
            'os' => PHP_OS,
            'openssl_version' => defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'No disponible',
            'curl_version' => function_exists('curl_version') ? curl_version()['version'] : 'No disponible',
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'No disponible'
        ];
        
        $message = 'Diagnostico completado - ' . count($foundCerts) . ' ubicaciones con certificados encontradas';
        $success = true;
        
    } catch (\Exception $e) {
        $success = false;
        $message = 'Error en diagn�stico: ' . $e->getMessage();
        $details['exception'] = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }
    
    // Respuesta JSON o HTML
    $format = request()->get('format', 'html');
    if ($format === 'json' || request()->ajax() || request()->wantsJson()) {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'details' => $details
        ]);
    }
    
    // Output HTML
    $output = "AFIP CERTIFICADOS - DIAGNOSTICO\n";
    $output .= "================================\n\n";
    $output .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
    $output .= "Estado: " . ($success ? 'EXITO' : 'ERROR') . "\n";
    $output .= "Mensaje: " . $message . "\n\n";
    
    $output .= "=== RUTAS VERIFICADAS ===\n";
    foreach ($details['paths'] as $name => $path) {
        $status = $details['path_status'][$name] ?? 'unknown';
        $output .= sprintf("%-15s: %s [%s]\n", $name, $path, $status);
    }
    
    $output .= "\n=== CERTIFICADOS ENCONTRADOS ===\n";
    $totalCerts = 0;
    foreach ($details['certificates_found'] as $pathName => $certs) {
        if (!empty($certs)) {
            $output .= "En {$pathName}:\n";
            foreach ($certs as $cert) {
                $output .= sprintf("  - %s (%s bytes, %s, %s)\n", 
                    $cert['file'], 
                    number_format($cert['size']), 
                    $cert['extension'],
                    $cert['readable'] ? 'legible' : 'no legible'
                );
                $totalCerts++;
            }
            $output .= "\n";
        }
    }
    
    if ($totalCerts === 0) {
        $output .= "No se encontraron certificados (.crt, .pem, .p12, .pfx, .cer)\n\n";
    }
    
    $output .= "=== VARIABLES DE ENTORNO ===\n";
    foreach ($details['environment_variables'] as $var => $info) {
        $status = $info['is_set'] ? 'SET' : 'NOT SET';
        $output .= sprintf("%-20s: %s [%s]\n", $var, $info['value'], $status);
    }
    
    $output .= "\n=== ARCHIVO .ENV ===\n";
    $envInfo = $details['env_file'];
    $output .= "Existe: " . ($envInfo['exists'] ? 'SI' : 'NO') . "\n";
    if ($envInfo['exists']) {
        $output .= "Legible: " . ($envInfo['readable'] ? 'SI' : 'NO') . "\n";
        $output .= "Tama�o: " . number_format($envInfo['size']) . " bytes\n";
        $output .= "Modificado: " . $envInfo['modified'] . "\n";
    }
    
    if (isset($details['certificate_tests']) && !empty($details['certificate_tests'])) {
        $output .= "\n=== ANALISIS DE CERTIFICADOS ===\n";
        foreach ($details['certificate_tests'] as $test) {
            $output .= "Archivo: {$test['file']} (en {$test['path']})\n";
            $output .= "  Tama�o contenido: " . number_format($test['size']) . " bytes\n";
            $output .= "  Formato valido: " . ($test['has_begin_cert'] && $test['has_end_cert'] ? 'SI' : 'NO') . "\n";
            $output .= "  Contiene clave privada: " . ($test['has_begin_private'] ? 'SI' : 'NO') . "\n";
            $output .= "  Saltos de linea: Unix({$test['line_endings']['unix_lf']}) Win({$test['line_endings']['windows_crlf']}) Mac({$test['line_endings']['mac_cr']})\n";
            $output .= "\n";
        }
    }
    
    $statusColor = $success ? 'green' : 'red';
    
    return response(
        '<html><head><meta charset="UTF-8"><title>AFIP Certificados - Diagnostico</title></head><body>' .
        '<h1>AFIP Certificados - Diagnostico</h1>' .
        '<div style="margin:10px 0;"><strong>Estado:</strong> <span style="color:' . $statusColor . '">' . ($success ? 'EXITO' : 'ERROR') . '</span></div>' .
        '<pre style="background:#f0f0f0; padding:15px; border:1px solid #ccc; font-family:monospace; white-space:pre-wrap;">' . 
        htmlspecialchars($output) . 
        '</pre>' .
        '<div style="margin:15px 0;">' .
        '<a href="?format=json" style="background:#007cba;color:white;padding:8px 16px;text-decoration:none;margin-right:10px;">Ver JSON</a>' .
        '<a href="/storage-link" style="background:#28a745;color:white;padding:8px 16px;text-decoration:none;margin-right:10px;">Storage Link</a>' .
        '<a href="javascript:history.back()" style="background:#666;color:white;padding:8px 16px;text-decoration:none;">? Volver</a>' .
        '</div>' .
        '</body></html>',
        200,
        ['Content-Type' => 'text/html; charset=UTF-8']
    );
});

// Corregir certificados AFIP
Route::get('afip-fix', function () {
    $success = false;
    $message = '';
    $details = [];
    $fixes_applied = [];
    
    try {
        // Verificar si existen los certificados
        $certPath = storage_path('app/afip.crt');
        $keyPath = storage_path('app/afip.pem');
        
        $details['files_found'] = [
            'cert' => file_exists($certPath),
            'key' => file_exists($keyPath)
        ];
        
        if (!file_exists($certPath) || !file_exists($keyPath)) {
            throw new \Exception('Certificados AFIP no encontrados en storage/app/');
        }
        
        // Leer contenido actual
        $certContent = file_get_contents($certPath);
        $keyContent = file_get_contents($keyPath);
        
        $details['original_sizes'] = [
            'cert' => strlen($certContent),
            'key' => strlen($keyContent)
        ];
        
        // Corregir saltos de l�nea en clave privada (convertir CRLF a LF)
        $originalKeyLines = [
            'unix_lf' => substr_count($keyContent, "\n"),
            'windows_crlf' => substr_count($keyContent, "\r\n"),
            'mac_cr' => substr_count($keyContent, "\r") - substr_count($keyContent, "\r\n")
        ];
        
        // Normalizar saltos de l�nea
        $fixedKeyContent = str_replace(["\r\n", "\r"], "\n", $keyContent);
        
        $fixedKeyLines = [
            'unix_lf' => substr_count($fixedKeyContent, "\n"),
            'windows_crlf' => substr_count($fixedKeyContent, "\r\n"),
            'mac_cr' => substr_count($fixedKeyContent, "\r") - substr_count($fixedKeyContent, "\r\n")
        ];
        
        $details['line_endings'] = [
            'original' => $originalKeyLines,
            'fixed' => $fixedKeyLines
        ];
        
        // Escribir archivo corregido si hay cambios
        if ($keyContent !== $fixedKeyContent) {
            file_put_contents($keyPath, $fixedKeyContent);
            $fixes_applied[] = 'Saltos de linea normalizados en afip.pem';
        }
        
        // Crear/actualizar archivo .env con configuraci�n AFIP
        $envPath = base_path('.env');
        $envContent = file_exists($envPath) ? file_get_contents($envPath) : '';
        
        $envUpdates = [];
        $afipVars = [
            'AFIP_CERT' => storage_path('app/afip.crt'),
            'AFIP_KEY' => storage_path('app/afip.pem')
        ];
        
        foreach ($afipVars as $var => $value) {
            if (!env($var)) {
                $envUpdates[$var] = $value;
                
                // Agregar al .env si no existe
                if (strpos($envContent, $var . '=') === false) {
                    $envContent .= "\n" . $var . '=' . $value;
                    $fixes_applied[] = "Variable $var agregada a .env";
                }
            }
        }
        
        // Escribir .env actualizado si hay cambios
        if (!empty($envUpdates)) {
            file_put_contents($envPath, $envContent);
        }
        
        $details['env_updates'] = $envUpdates;
        
        // Verificar integridad de certificados
        $certVerification = [
            'cert_valid' => (strpos($certContent, '-----BEGIN CERTIFICATE-----') !== false && 
                            strpos($certContent, '-----END CERTIFICATE-----') !== false),
            'key_valid' => (strpos($fixedKeyContent, '-----BEGIN PRIVATE KEY-----') !== false && 
                           strpos($fixedKeyContent, '-----END PRIVATE KEY-----') !== false)
        ];
        
        $details['certificate_verification'] = $certVerification;
        
        // Test de conexi�n AFIP (simulado)
        $testResult = [
            'files_accessible' => is_readable($certPath) && is_readable($keyPath),
            'env_vars_set' => !empty($envUpdates) || (env('AFIP_CERT') && env('AFIP_KEY')),
            'format_correct' => $certVerification['cert_valid'] && $certVerification['key_valid']
        ];
        
        $details['test_result'] = $testResult;
        
        if (empty($fixes_applied)) {
            $message = 'Certificados ya estan correctamente configurados';
        } else {
            $message = 'Certificados AFIP corregidos: ' . count($fixes_applied) . ' fixes aplicados';
        }
        
        $details['fixes_applied'] = $fixes_applied;
        $success = true;
        
    } catch (\Exception $e) {
        $success = false;
        $message = 'Error al corregir certificados: ' . $e->getMessage();
        $details['exception'] = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }
    
    // Respuesta JSON o HTML
    $format = request()->get('format', 'html');
    if ($format === 'json' || request()->ajax() || request()->wantsJson()) {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'fixes_applied' => $fixes_applied,
            'details' => $details
        ]);
    }
    
    // Output HTML
    $output = "AFIP CERTIFICADOS - CORRECCION\n";
    $output .= "==============================\n\n";
    $output .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
    $output .= "Estado: " . ($success ? 'EXITO' : 'ERROR') . "\n";
    $output .= "Mensaje: " . $message . "\n\n";
    
    if (!empty($fixes_applied)) {
        $output .= "=== FIXES APLICADOS ===\n";
        foreach ($fixes_applied as $fix) {
            $output .= "- " . $fix . "\n";
        }
        $output .= "\n";
    }
    
    if (isset($details['files_found'])) {
        $output .= "=== ARCHIVOS ENCONTRADOS ===\n";
        $output .= "afip.crt: " . ($details['files_found']['cert'] ? 'SI' : 'NO') . "\n";
        $output .= "afip.pem: " . ($details['files_found']['key'] ? 'SI' : 'NO') . "\n\n";
    }
    
    if (isset($details['certificate_verification'])) {
        $output .= "=== VERIFICACION CERTIFICADOS ===\n";
        $output .= "Certificado valido: " . ($details['certificate_verification']['cert_valid'] ? 'SI' : 'NO') . "\n";
        $output .= "Clave privada valida: " . ($details['certificate_verification']['key_valid'] ? 'SI' : 'NO') . "\n\n";
    }
    
    if (isset($details['env_updates']) && !empty($details['env_updates'])) {
        $output .= "=== VARIABLES .ENV AGREGADAS ===\n";
        foreach ($details['env_updates'] as $var => $value) {
            $output .= "$var = $value\n";
        }
        $output .= "\n";
    }
    
    if (isset($details['test_result'])) {
        $output .= "=== RESULTADO TEST ===\n";
        $test = $details['test_result'];
        $output .= "Archivos accesibles: " . ($test['files_accessible'] ? 'SI' : 'NO') . "\n";
        $output .= "Variables configuradas: " . ($test['env_vars_set'] ? 'SI' : 'NO') . "\n";
        $output .= "Formato correcto: " . ($test['format_correct'] ? 'SI' : 'NO') . "\n";
    }
    
    $statusColor = $success ? 'green' : 'red';
    
    return response(
        '<html><head><meta charset="UTF-8"><title>AFIP - Correccion Certificados</title></head><body>' .
        '<h1>AFIP - Correccion de Certificados</h1>' .
        '<div style="margin:10px 0;"><strong>Estado:</strong> <span style="color:' . $statusColor . '">' . ($success ? 'EXITO' : 'ERROR') . '</span></div>' .
        '<pre style="background:#f0f0f0; padding:15px; border:1px solid #ccc; font-family:monospace; white-space:pre-wrap;">' . 
        htmlspecialchars($output) . 
        '</pre>' .
        '<div style="margin:15px 0;">' .
        '<a href="?format=json" style="background:#007cba;color:white;padding:8px 16px;text-decoration:none;margin-right:10px;">Ver JSON</a>' .
        '<a href="/afip-cert-debug" style="background:#28a745;color:white;padding:8px 16px;text-decoration:none;margin-right:10px;">Diagnostico</a>' .
        '<a href="/storage-link" style="background:#6c757d;color:white;padding:8px 16px;text-decoration:none;margin-right:10px;">Storage Link</a>' .
        '<a href="javascript:history.back()" style="background:#666;color:white;padding:8px 16px;text-decoration:none;">? Volver</a>' .
        '</div>' .
        '</body></html>',
        200,
        ['Content-Type' => 'text/html; charset=UTF-8']
    );
});

Route::get('afip-test', function () {
    $success = false;
    $message = '';
    $details = [];
    $tests_results = [];
    
    try {
        // Inicializar servicio AFIP
        $afipService = new \App\Services\AfipService();
        
        $details['initialization'] = [
            'status' => 'success',
            'message' => 'Servicio AFIP inicializado correctamente'
        ];
        
        // Test 1: Estado del servidor AFIP
        try {
            $serverStatus = $afipService->getServerStatus();
            $tests_results['server_status'] = [
                'success' => true,
                'data' => $serverStatus,
                'message' => 'Conexi�n con servidor AFIP exitosa'
            ];
        } catch (\Exception $e) {
            $tests_results['server_status'] = [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Error al conectar con servidor AFIP'
            ];
        }
        
        // Test 1.5: Obtener puntos de venta habilitados
        try {
            $salesPoints = $afipService->getSalesPoints();
            
            // Convertir datos para formato uniforme y debugging
            $salesPointsData = [];
            $validSalesPoint = 1; // Default fallback
            
            if (is_array($salesPoints) && count($salesPoints) > 0) {
                foreach ($salesPoints as $index => $point) {
                    $pointData = [];
                    
                    // Intentar diferentes propiedades que puede tener AFIP
                    if (isset($point->PtoVta)) {
                        $pointData['PtoVta'] = $point->PtoVta;
                    } elseif (isset($point->ptoVta)) {
                        $pointData['PtoVta'] = $point->ptoVta;
                    } elseif (isset($point->numero)) {
                        $pointData['PtoVta'] = $point->numero;
                    } elseif (is_numeric($point)) {
                        $pointData['PtoVta'] = $point;
                    }
                    
                    if (isset($point->Bloqueado)) {
                        $pointData['Bloqueado'] = $point->Bloqueado;
                    }
                    if (isset($point->FchBaja)) {
                        $pointData['FchBaja'] = $point->FchBaja;
                    }
                    
                    $salesPointsData[] = $pointData;
                    
                    // Usar el primer punto de venta v�lido encontrado
                    if ($index === 0 && isset($pointData['PtoVta'])) {
                        $validSalesPoint = 4;
                    }
                }
            }
            
            $tests_results['sales_points'] = [
                'success' => true,
                'count' => count($salesPoints),
                'data' => $salesPointsData,
                'raw_data' => $salesPoints, // Para debugging
                'valid_point_used' => $validSalesPoint,
                'message' => count($salesPoints) > 0 
                    ? "Puntos de venta habilitados obtenidos: " . count($salesPoints) . " puntos"
                    : 'No se encontraron puntos de venta habilitados'
            ];
            
        } catch (\Exception $e) {
            $tests_results['sales_points'] = [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Error al obtener puntos de venta'
            ];
            $validSalesPoint = 1; // Fallback al punto 1
        }
        
        // Test 2: Obtener tipos de comprobantes
        try {
            $voucherTypes = $afipService->getVoucherTypes();
            $tests_results['voucher_types'] = [
                'success' => true,
                'count' => count($voucherTypes),
                'data' => array_slice($voucherTypes, 0, 10), // Primeros 10 para muestra
                'message' => 'Tipos de comprobantes obtenidos'
            ];
        } catch (\Exception $e) {
            $tests_results['voucher_types'] = [
                'success' => false,
                'error' => mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8'),
                'message' => 'Error al obtener tipos de comprobantes'
            ];
        }
        
        // Test 3: Obtener tipos de documentos
        try {
            $documentTypes = $afipService->getDocumentTypes();
            $tests_results['document_types'] = [
                'success' => true,
                'count' => count($documentTypes),
                'data' => array_slice($documentTypes, 0, 5), // Primeros 5 para muestra
                'message' => 'Tipos de documentos obtenidos'
            ];
        } catch (\Exception $e) {
            $tests_results['document_types'] = [
                'success' => false,
                'error' => mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8'),
                'message' => 'Error al obtener tipos de documentos'
            ];
        }
        
        // Test 4: Obtener tipos de IVA
        try {
            $aliquotTypes = $afipService->getAliquotTypes();
            $tests_results['aliquot_types'] = [
                'success' => true,
                'count' => count($aliquotTypes),
                'data' => $aliquotTypes, // Todos los tipos de IVA
                'message' => 'Tipos de al�cuotas IVA obtenidos'
            ];
        } catch (\Exception $e) {
            $tests_results['aliquot_types'] = [
                'success' => false,
                'error' => mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8'),
                'message' => 'Error al obtener tipos de al�cuotas'
            ];
        }
        
        // Test 5: Obtener tipos de monedas
        try {
            $currencyTypes = $afipService->getCurrencyTypes();
            $tests_results['currency_types'] = [
                'success' => true,
                'count' => count($currencyTypes),
                'data' => array_slice($currencyTypes, 0, 5), // Primeras 5 monedas
                'message' => 'Tipos de monedas obtenidos'
            ];
        } catch (\Exception $e) {
            $tests_results['currency_types'] = [
                'success' => false,
                'error' => mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8'),
                'message' => 'Error al obtener tipos de monedas'
            ];
        }
        
        // Test 6: Obtener �ltimo n�mero de factura B (usando punto de venta v�lido)
        try {
            $lastVoucherB = $afipService->getLastVoucher(4, 6);
            $tests_results['last_voucher_b'] = [
                'success' => true,
                'data' => $lastVoucherB,
                'message' => "�ltimo n�mero de Factura B obtenido (PtoVta {$validSalesPoint})"
            ];
        } catch (\Exception $e) {
            $tests_results['last_voucher_b'] = [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Error al obtener �ltimo voucher B'
            ];
        }
        
        // Test 7: Obtener �ltimo n�mero de factura A (usando punto de venta v�lido)
        try {
            $lastVoucherA = $afipService->getLastVoucher(4, 1);
            $tests_results['last_voucher_a'] = [
                'success' => true,
                'data' => $lastVoucherA,
                'message' => "�ltimo n�mero de Factura A obtenido (PtoVta {$validSalesPoint})"
            ];
        } catch (\Exception $e) {
            $tests_results['last_voucher_a'] = [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Error al obtener �ltimo voucher A'
            ];
        }
        
        // Test 8: Informaci�n de certificado y configuraci�n
        $certPath = storage_path('app/afip.crt');
        $keyPath = storage_path('app/afip.pem');
        $tests_results['certificate_info'] = [
            'success' => true,
            'data' => [
                'cert_exists' => file_exists($certPath),
                'key_exists' => file_exists($keyPath),
                'cert_size' => file_exists($certPath) ? filesize($certPath) : 0,
                'key_size' => file_exists($keyPath) ? filesize($keyPath) : 0,
                'cuit' => env('AFIP_CUIT', 'NOT_SET'),
                'production' => env('AFIP_PRODUCTION', false) ? 'SI' : 'NO',
            ],
            'message' => 'Informaci�n de certificados y configuraci�n'
        ];
        
        // Test 9: Crear factura de prueba muy peque�a (solo si NO estamos en producci�n)
        $isProduction = env('AFIP_PRODUCTION', false);
        if (!$isProduction) {
            try {
                // Factura B de $1 para consumidor final usando punto de venta v�lido
                $testInvoice = $afipService->facturaB($validSalesPoint, 1.00);
                $tests_results['test_invoice_creation'] = [
                    'success' => true,
                    'data' => $testInvoice,
                    'message' => "Factura de prueba creada exitosamente (\$1.00, PtoVta {$validSalesPoint})"
                ];
            } catch (\Exception $e) {
                $tests_results['test_invoice_creation'] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'message' => 'Error al crear factura de prueba'
                ];
            }
        } else {
            $tests_results['test_invoice_creation'] = [
                'success' => false,
                'skipped' => true,
                'message' => 'Test omitido - Configuraci�n en PRODUCCI�N'
            ];
        }
        
        // Test 10: Obtener informaci�n de voucher espec�fico (si existe)
        if (isset($lastVoucherB) && $lastVoucherB > 0 && !empty($validSalesPoints)) {
            try {
                $voucherInfo = $afipService->getVoucherInfo($lastVoucherB, $validSalesPoints[0], 6);
                $tests_results['voucher_info'] = [
                    'success' => true,
                    'message' => "Informaci�n del voucher #{$lastVoucherB} obtenida correctamente para punto de venta {$validSalesPoints[0]}"
                ];
            } catch (\Exception $e) {
                $tests_results['voucher_info'] = [
                    'success' => false,
                    'error' => mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8'),
                    'message' => 'Error al obtener informaci�n de voucher'
                ];
            }
        } else {
            $tests_results['voucher_info'] = [
                'success' => false,
                'skipped' => true,
                'message' => 'Test omitido - No hay vouchers previos o puntos de venta v�lidos para consultar'
            ];
        }
        
        // Test 11: Obtener tipos de conceptos
        try {
            $conceptTypes = $afipService->getConceptTypes();
            $tests_results['concept_types'] = [
                'success' => true,
                'count' => count($conceptTypes),
                'data' => $conceptTypes, // Todos los tipos de conceptos
                'message' => 'Tipos de conceptos obtenidos'
            ];
        } catch (\Exception $e) {
            $tests_results['concept_types'] = [
                'success' => false,
                'error' => mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8'),
                'message' => 'Error al obtener tipos de conceptos'
            ];
        }
        
        // Test 12: Obtener tipos de tributos
        try {
            $taxTypes = $afipService->getTaxTypes();
            $tests_results['tax_types'] = [
                'success' => true,
                'count' => count($taxTypes),
                'data' => array_slice($taxTypes, 0, 3), // Primeros 3 tributos
                'message' => 'Tipos de tributos obtenidos'
            ];
        } catch (\Exception $e) {
            $tests_results['tax_types'] = [
                'success' => false,
                'error' => mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8'),
                'message' => 'Error al obtener tipos de tributos'
            ];
        }
        
        // Resumen general
        $successTests = array_filter($tests_results, function($test) {
            return isset($test['success']) && $test['success'] === true;
        });
        $failedTests = array_filter($tests_results, function($test) {
            return isset($test['success']) && $test['success'] === false && !isset($test['skipped']);
        });
        $skippedTests = array_filter($tests_results, function($test) {
            return isset($test['skipped']) && $test['skipped'] === true;
        });
        
        $details['summary'] = [
            'total_tests' => count($tests_results),
            'successful' => count($successTests),
            'failed' => count($failedTests),
            'skipped' => count($skippedTests),
            'success_rate' => count($tests_results) > 0 ? round((count($successTests) / count($tests_results)) * 100, 2) : 0
        ];
        
        $message = sprintf('Tests completados: %d exitosos, %d fallidos, %d omitidos', 
            count($successTests), count($failedTests), count($skippedTests));
        $success = count($failedTests) === 0;
        
    } catch (\Exception $e) {
        $success = false;
        $message = 'Error cr�tico al inicializar AFIP: ' . $e->getMessage();
        $details['initialization'] = [
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }
    

    // Respuesta JSON o HTML
    $format = request()->get('format', 'html');
    if ($format === 'json' || request()->ajax() || request()->wantsJson()) {
        
        // Crear versi�n simplificada para JSON (sin datos complejos de AFIP)
        $simpleResults = [];
        foreach ($tests_results as $testName => $result) {
            $simpleResults[$testName] = [
                'success' => $result['success'] ?? false,
                'message' => isset($result['message']) ? mb_convert_encoding($result['message'], 'UTF-8', 'UTF-8') : '',
                'count' => $result['count'] ?? null,
                'skipped' => $result['skipped'] ?? false,
                'error' => isset($result['error']) ? mb_convert_encoding($result['error'], 'UTF-8', 'UTF-8') : null
            ];
        }
        
        $cleanedData = [
            'success' => $success,
            'message' => mb_convert_encoding($message, 'UTF-8', 'UTF-8'),
            'timestamp' => date('Y-m-d H:i:s'),
            'tests_results' => $simpleResults,
            'summary' => $details['summary'] ?? []
        ];
        
        return response()->json($cleanedData, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    }
    
    // Output HTML
    $output = "AFIP SDK - PRUEBAS DE INTEGRACION\n";
    $output .= "==================================\n\n";
    $output .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
    $output .= "Estado General: " . ($success ? 'EXITO' : 'ERROR') . "\n";
    $output .= "Mensaje: " . $message . "\n\n";
    
    if (isset($details['summary'])) {
        $summary = $details['summary'];
        $output .= "=== RESUMEN ===\n";
        $output .= "Total de pruebas: " . $summary['total_tests'] . "\n";
        $output .= "Exitosas: " . $summary['successful'] . "\n";
        $output .= "Fallidas: " . $summary['failed'] . "\n";
        $output .= "Omitidas: " . $summary['skipped'] . "\n";
        $output .= "Tasa de �xito: " . $summary['success_rate'] . "%\n\n";
    }
    
    $output .= "=== RESULTADOS DETALLADOS ===\n";
    foreach ($tests_results as $testName => $result) {
        $testTitle = strtoupper(str_replace('_', ' ', $testName));
        $status = 'DESCONOCIDO';
        
        if (isset($result['skipped']) && $result['skipped']) {
            $status = 'OMITIDO';
        } elseif (isset($result['success'])) {
            $status = $result['success'] ? 'EXITO' : 'ERROR';
        }
        
        $output .= "\n{$testTitle}: [{$status}]\n";
        $output .= "  Mensaje: " . ($result['message'] ?? 'Sin mensaje') . "\n";
        
        if (isset($result['count'])) {
            $output .= "  Elementos: " . $result['count'] . "\n";
        }
        
        if (isset($result['error'])) {
            $output .= "  Error: " . $result['error'] . "\n";
        }
        
        if (isset($result['data']) && is_scalar($result['data'])) {
            $output .= "  Datos: " . $result['data'] . "\n";
        } elseif (isset($result['data']) && is_array($result['data']) && count($result['data']) <= 5) {
            $output .= "  Datos: " . json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
    
    $statusColor = $success ? 'green' : 'red';
    
    return response(
        '<html><head><meta charset="UTF-8"><title>AFIP SDK - Pruebas de Integraci�n</title></head><body>' .
        '<h1>AFIP SDK - Pruebas de Integraci�n</h1>' .
        '<div style="margin:10px 0;"><strong>Estado:</strong> <span style="color:' . $statusColor . '">' . ($success ? 'EXITO' : 'ERROR') . '</span></div>' .
        '<pre style="background:#f0f0f0; padding:15px; border:1px solid #ccc; font-family:monospace; white-space:pre-wrap;">' . 
        htmlspecialchars($output) . 
        '</pre>' .
        '<div style="margin:15px 0;">' .
        '<a href="?format=json" style="background:#007cba;color:white;padding:8px 16px;text-decoration:none;margin-right:10px;">Ver JSON</a>' .
        '<a href="/afip-cert-debug" style="background:#28a745;color:white;padding:8px 16px;text-decoration:none;margin-right:10px;">Diagn�stico Cert.</a>' .
        '<a href="/afip-fix" style="background:#ffc107;color:black;padding:8px 16px;text-decoration:none;margin-right:10px;">Corregir Cert.</a>' .
        '<a href="/storage-link" style="background:#6c757d;color:white;padding:8px 16px;text-decoration:none;margin-right:10px;">Storage Link</a>' .
        '<a href="javascript:history.back()" style="background:#666;color:white;padding:8px 16px;text-decoration:none;">? Volver</a>' .
        '</div>' .
        '</body></html>',
        200,
        ['Content-Type' => 'text/html; charset=UTF-8']
    );
});
