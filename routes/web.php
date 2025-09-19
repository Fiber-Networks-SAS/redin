<?php

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

	// mis reclamos
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

		// periodos
		Route::get('/period', 'BillController@index');
		Route::get('/period/list', 'BillController@getList'); 								// request for fill Table (via Ajax)
		Route::get('/period/create', 'BillController@create');
		Route::post('/period/create', 'BillController@store');
		Route::get('/period/view/{mes}/{ano}', 'BillController@view');
		Route::get('/period/view/list/{mes}/{ano}', 'BillController@getBillPeriodList'); 	// request for fill Table (via Ajax)
		Route::get('/period/send/{mes}/{ano}', 'BillController@sendEmailFacturasPeriodo');
		
		Route::get('/period/bill/{id}', 'BillController@getBillDetail');
		Route::post('/period/bill-edit/{id}', 'BillController@getBillEditPost');

		Route::get('/period/bill-improve/{id}', 'BillController@getBillImprove');
		Route::post('/period/bill-improve/{id}', 'BillController@getBillImprovePost');
		Route::get('/period/bill-update/{id}', 'BillController@getBillUpdate');
		Route::post('/period/bill-update/{id}', 'BillController@getBillUpdatePost');
		Route::get('/period/bill-pay/{id}', 'BillController@getBillPay');
		Route::post('/period/bill-pay/{id}', 'BillController@getBillPayPost');
		Route::get('/period/bill-send/{id}', 'BillController@getBillSend');
		Route::get('/period/download/pmc/{mes}/{ano}', 'BillController@setPeriodoPMC');

		// cancelar pago factura
		Route::get('/period/bill-pay-cancel/{id}', 'BillController@getBillPayCancel');
		Route::post('/period/bill-pay-cancel/{id}', 'BillController@getBillPayCancelPost');		

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