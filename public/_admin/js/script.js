$(document).ready(function () {

    var interval = 60000; // 10000 = 10 segundos

    // muestro los reclamos no leidos al cargar la pagina
    getReclamosNoLeidos();

    // muestro los reclamos no leidos cada cierto intervalo
    // setInterval(function(){
        // getReclamosNoLeidos();
    // }, interval);


    // Numeric values only allowed (With Decimal Point)
    $(".allownumericwithdecimal").on("keypress keyup blur",function (event) {
        
        //this.value = this.value.replace(/[^0-9\.]/g,'');
        $(this).val($(this).val().replace(/[^0-9,]/g,''));
        if ((event.which != 44 || $(this).val().indexOf(',') != -1) && (event.which < 48 || event.which > 57)) {
            event.preventDefault();
        }
    });
    
    // Numeric values only allowed (Without Decimal Point)
    $(".allownumericwithoutdecimal").on("keypress keyup blur",function (event) {    
        
        $(this).val($(this).val().replace(/[^\d].+/, ""));
        if ((event.which < 48 || event.which > 57)) {
            event.preventDefault();
        }
    });

    // disable modal cache
    $('#modalDivisasISO').on('hide.bs.modal', function (e) {
        $(this).removeData('bs.modal');
    });
    
    $(":input").inputmask();
    
    // select color set background on edit
    if ($("#color").size()) {
        var clasS = $("#color").find('option:selected').attr('class');
        $("#color").attr('class', clasS);

        if ($("#color").val() != '') {
            $('#picture').prop('disabled', true);
        }else{
            $('#picture').prop('disabled', false);
        }
    }

    // select color change event
    $("#color").on('change', function (){
        var clasS = $(this).find('option:selected').attr('class');
        $(this).attr('class', clasS);

        if ($(this).val() != '') {
            $('#picture').prop('disabled', true);
        }else{
            $('#picture').prop('disabled', false);
        }
    });

    // show cotizaciones from google (event on blur in code input - CRUD divisas)
    if ($("#code").size()) {

        $("#code").on('blur', function (){

            getCotizacionFromGoogleLabel($('#current-price'), $(this).val(), '', 1);

        });

    }

    // action for get divisas - CRUD movimientos
    if ($('select[name="cuenta"]').size() && $('select[name="divisa"]').size()) {

        // element defined in movimientos edit
        if ($('#preventDefaultBehabior').size() == 0){

            selectDivisaDefault($('select[name="divisa"]'), $('select[name="cuenta"]').find(':selected').attr('divisa'));
            
        }

        $('select[name="cuenta"]').on('change', function (){
            
            selectDivisaDefault($('select[name="divisa"]'), $('select[name="cuenta"]').find(':selected').attr('divisa'));
            
            if ($('select[name="divisa"]').size() && $('#price').size()) {
            
                selectPriceDefault($('#price'), $('select[name="divisa"]').find(':selected').attr('price'), true);
            
            }
        });

    }

    // action for get cotizaciones from google - CRUD movimientos
    if ($('select[name="divisa"]').size() && $('#price').size()) {
        
        // element defined in movimientos edit
        if ($('#preventDefaultBehabior').size() == 0){

            selectPriceDefault($('#price'), $('select[name="divisa"]').find(':selected').attr('price'), true);
        
        }else{

            getCotizacionFromGoogleLabel($('#current-price'), $('select[name="divisa"]').find(':selected').attr('code'), '', 1);
        }
        
        $('select[name="divisa"]').on('change', function (){

            selectPriceDefault($('#price'), $('select[name="divisa"]').find(':selected').attr('price'), true);
        
        });

    }
    


    // GET personal for autocomplete list
    if($('#autocomplete-user-name').length){

        $('#autocomplete-user-name').keyup(function() {
            if ($(this).val() == '') {
                $('#instalador_id').val('');
            }
        })

        getAutocompleteData("/admin/clients/stafflist", $('#autocomplete-user-name'), $('#instalador_id'));

    }

    // GET clients for autocomplete list
    if($('#autocomplete-client-name').length){

        $('#autocomplete-client-name').keyup(function() {
            if ($(this).val() == '') {
                $('#user_id').val('');
            }
        })

        getAutocompleteData("/admin/clients/clientlist", $('#autocomplete-client-name'), $('#user_id'));

    }

    // GET clients for autocomplete list (solo clientes que no fueron facturados del periodo)
    if($('#autocomplete-client-name-not-bill').length){

        $('#autocomplete-client-name-not-bill').keyup(function() {
            if ($(this).val() == '') {
                $('#user_id').val('');
            }
        })

        getAutocompleteData("/admin/clients/clientlistnotbill", $('#autocomplete-client-name-not-bill'), $('#user_id'));

    }

    $("#autocomplete-client-name").on('blur', function (){

        getClientServices();

    });

    // GET services for autocomplete list
    if($('#autocomplete-service-name').length){

        $('#autocomplete-service-name').keyup(function() {
            if ($(this).val() == '') {
                $('#servicio_id').val('');
            }
        })

        getAutocompleteData("/admin/clients/servicelist", $('#autocomplete-service-name'), $('#servicio_id'));
    }

    // get cuenta from user 
    $('#autocomplete-service-name').blur(function(){
        if ($(this).val() != '' && $('#servicio_id').val() != '') {
            getPrecioFromServicio();
        }
    });

    $('#contrato_nro').focus(function(){
        if ($('#autocomplete-service-name').val() != '' && $('#servicio_id').val() != '') {
            getPrecioFromServicio();
        }
    });


    // GET abono proporcional sugerido
    if($('#abono_mensual').length){

        getAbonoProporcional($('#abono_mensual').val());
        
        $('#abono_mensual').keyup(function() {
            
            getAbonoProporcional($(this).val());
            
            // if ($(this).val() != '') {

            //     importe = $.number(parseFloat($(this).val() / 30), 2);

            //     $('#abono_proporcional_sugerido').html('<a href="#" class="abono_proporcional_sugerido_importe">'+importe+'</a>');
            // }
        })

    }
    
    // click on abono proporcional sugerido
    $(document).on('click', '.abono_proporcional_sugerido_importe' , function (){
        $('#abono_proporcional').val($('.abono_proporcional_sugerido_importe').html());
    });


    // ----------------------- balance form ----------------------- //

    // Balance "Todos" cuentas select
    $("#periodo_all").change( function(){
        $("select[name='periodo']").attr("disabled", $(this).is(':checked')); 
    }); 



    // Balance "Todos" cliente input
    $("#client_all").change( function(){
        $('.container-name ul').remove();
        $("#autocomplete-client-name").attr("disabled", $(this).is(':checked')); 
        $('#autocomplete-client-name').removeClass('parsley-error');
        $('#autocomplete-client-name').val('');
        $('#user_id').val('');

    });   

    $("#balanceSearchForm").submit(function (e){

        e.preventDefault();

        getBalanceScreen();

    });

    $("#balanceDetalleSearchForm").submit(function (e){

        e.preventDefault();

        getBalanceDetalleScreen();

    });

    $("#cobroexpressSearchForm").submit(function (e){

        e.preventDefault();

        getCobroexpressScreen();

    });

    // plan de pago select
    $("select[name='plan_pago']").change( function(){
        setInteresCostoInstalacion();
    }); 


    // edit service form
    if($('#form_edit_service').size()){
        setInteresCostoInstalacion();
    }



    // ----------------------- resumen form ----------------------- //


    // $("#resumenSearchForm").submit(function (e){

    //     e.preventDefault();

    //     getResumenScreen();

    // });


    // ----------------------- comisiones form ----------------------- //

    // $("#comisionesSearchForm").submit(function (e){

    //     e.preventDefault();

    //     getComisionesScreen();

    // });


    // ----------------------- get saldo inicial from movimiento ----------------------- //

    if ($('#saldo_inicial').size()) {

        if ($('#showSaldoInicial').size()) { //movimiento edit form
            getSaldoInicialFromUser();
        }
        $("#autocomplete-user-name").on("blur", function(){
            getSaldoInicialFromUser();
        });
        $("select[name='cuenta']").on("blur", function(){
            getSaldoInicialFromUser();
        });
        $("select[name='divisa']").on("blur", function(){
            getSaldoInicialFromUser();
        });
        $("#price").on("blur", function(){
            getSaldoInicialFromUser();
        });
        $("#amount").on("blur", function(){
            getSaldoInicialFromUser();
        });
        $("input[name='type']").on("ifClicked", function(){
            getSaldoInicialFromUser();
        });
        $("#date").on("blur", function(){
            getSaldoInicialFromUser();
        });
        $("#description").on("blur", function(){
            getSaldoInicialFromUser();
        });
        
    }

    // ----------------------- get cuenta relacionada in movimiento ----------------------- //

    $("#parent_id").keyup(function() {
        if ($(this).val() != '') {
            getMovimientoByID($(this).val());
        }else{
            $('#parent_label').html('Ninguna');
        }
    });

    if ($('#showCuentaRelacionada').size()) { //movimiento edit form
        getMovimientoByID($("#parent_id").val());
    }


    // GET importe from importe base
    if($('#amount_base').length){

        $('#amount_base').on("blur", function(){
            if ($(this).val() != '' && $('#price').val() != '') {
                
                var importe = parseFloat($(this).val()) / parseFloat($('#price').val());
                    importe = $.number(importe, 2, '.', ',');

                $('#amount').val(importe);

            }else{
                
                $('#amount').val('');                
            }
        })

    }


    // GET importe from importe base
    if($('.factura_input_edit').length){

        $('.factura_input_edit').on("keypress", function(e){
            
            if ($(this).val() != '' && e.which == 13) {
            
                updateFactura($(this));

            }
        })

    }

 
    // Cuotas adeudadas input
    $('#pp_cuotas_adeudadas').blur(function(){
        getTotalDeudaPP();
        getImporteSistemaFrances();
    });    

    // plan de pago cliente select
    $("select[name='deuda_instalacion']").change( function(){
        getTotalDeudaPP();
        getImporteSistemaFrances();
    });

        // plan de pago cliente select
    $("select[name='pp_plan_pago']").change( function(){
        getImporteSistemaFrances();
    });


    function updateFactura(element){
        
        // disable element 
        element.prop( "disabled", true );

        // calculo el subtotal
        var subtotal = 0;
        $('.factura_fila_importe').each(function(){
            subtotal = parseFloat(subtotal) + parseFloat($(this).val());
        });

        // calculo el total
        total = parseFloat(subtotal) - parseFloat($('#importe_bonificacion').val());

        subtotal = $.number(subtotal, 2, '.', ',');
        total    = $.number(total, 2, '.', ',');

        // console.log(element);
        var data  = new FormData();
        data.append('_token', $("input[name='_token']").val()); // append token
        data.append('id', element.attr('id'));         // append val
        data.append('value', element.val());         // append val
        data.append('field_type', element.attr('field_type'));         // append val
        data.append('proporcional', element.attr('proporcional'));         // append val
        

        data.append('importe_bonificacion', $('#importe_bonificacion').val());         // append val
        data.append('importe_subtotal', subtotal);         // append val
        data.append('importe_total', total);         // append val

        // ajax callback
        $.ajax({                                                // send ajax
            url: "/admin/period/bill-edit/" + $('#factura_id').val(),
            type: "POST",
            data: data,
            // enctype: 'multipart/form-data',
            processData: false,                             // tell jQuery not to process the data
            contentType: false                              // tell jQuery not to set contentType
        }).done(function(data) {

            // console.log(data);

            // actualizo los totales
            $('.factura_importe_subtotal').html(subtotal);
            $('.factura_importe_total').html('$'+total);

            // formateo el campo
            element.val($.number(element.val(), 2, '.', ','));

            // enable element
            element.prop( "disabled", false );

        });
    }


    // ----------------------- tables ----------------------- //

    // users table
    if($('#dataTableUsers').length){
        
        var obj = $('#dataTableUsers');

        var action = '/admin/users/list';
        
        var columns =  [ { data: "id", name: "id" },
                        { data: "firstname", name: "firstname" },
                        { data: "lastname", name: "lastname" },
                        { data: "email", name: "email" },
                        { data: "tel1", name: "tel1" },
                        { data: function (obj) {

                            disabled = $('#current-user').val() == obj.id ? 'disabled="disabled"' : ''
                            status = obj.status == 1 ? 'checked' : '';
                            return '<input type="checkbox" class="js-switch status_switcher" name="status" '+status+' '+disabled+' data-id="' + obj.id + '" />';

                            }
                        },
                        { data: function (obj) {
                            return '<a href="/admin/users/view/' + obj.id + '" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> Ver</a>' +
                            '<a href="/admin/users/edit/' + obj.id + '" class="btn btn-success btn-xs"><i class="fa fa-edit"></i> Modificar</a>';
                            }
                        }];

        var columnDefs = [ { orderable: false, "targets": -1 },          // last column
                           {className: "dt-center", "targets": [-1, -2]}
                         ];

        var initSwitchery = true;
        var urlSwitchery = '/admin/users';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'asc'
        var pageLength = 10;
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense, pageLength);

    }

    // staff table
    if($('#dataTableStaff').length){
        
        var obj = $('#dataTableStaff');

        var action = '/admin/staff/list';
        
        var columns =  [ { data: "id", name: "id" },
                        { data: "firstname", name: "firstname" },
                        { data: "lastname", name: "lastname" },
                        { data: "email", name: "email" },
                        { data: "tel1", name: "tel1" },
                        { data: function (obj) {

                            disabled = $('#current-user').val() == obj.id ? 'disabled="disabled"' : ''
                            status = obj.status == 1 ? 'checked' : '';
                            return '<input type="checkbox" class="js-switch status_switcher" name="status" '+status+' '+disabled+' data-id="' + obj.id + '" />';

                            }
                        },
                        { data: function (obj) {
                            return '<a href="/admin/staff/view/' + obj.id + '" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> Ver</a>' +
                            '<a href="/admin/staff/edit/' + obj.id + '" class="btn btn-success btn-xs"><i class="fa fa-edit"></i> Modificar</a>';
                            }
                        }];

        var columnDefs = [ { orderable: false, "targets": -1 },          // last column
                           {className: "dt-center", "targets": [-1, -2]}
                         ];

        var initSwitchery = true;
        var urlSwitchery = '/admin/staff';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'asc'
        var pageLength = 10;
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense, pageLength);

    }

    // clients table
    if($('#dataTableClients').length){
        
        var obj = $('#dataTableClients');

        var action = '/admin/clients/list';
        
        var columns =  [ { data: "nro_cliente", name: "nro_cliente" },
                        { data: function (obj) {

                            disabled = $('#current-user').val() == obj.id ? 'disabled="disabled"' : '';
                            status = obj.status == 1 ? 'checked' : '';
                            return '<input type="checkbox" class="js-switch status_switcher" name="status" '+status+' '+disabled+' data-id="' + obj.id + '" />';

                            }
                        },
                        { data: "dni", name: "dni" },
                        { data: "firstname", name: "firstname" },
                        { data: "lastname", name: "lastname" },
                        { data: "email", name: "email" },
                        { data: "tel1", name: "tel1" },
                        { data: "ont_serie1", name: "ont_serie1" },
                        { data: "ont_funcionando", name: "ont_funcionando" },
                        { data: "drop", name: "drop" },
                        { data: function (obj) {

                            dis_contrato     = (obj.firma_contrato == null && obj.ont_instalado == null) ? 'disabled="disabled" title="Para asignar servicios es necesario completar los Datos Técnicos" ' : 'title="Listado de los servicios asignados al Cliente"';
                            url_contrato = (obj.firma_contrato == null && obj.ont_instalado == null) ? '#' : '/admin/clients/services/' + obj.id;
                            
                            dis_factura  = obj.total_facturas == 0 ? 'disabled="disabled" title="No se encontraron Facturas para este cliente" ' : '';
                            url_factura  = obj.total_facturas == 0 ? '#' : '/admin/clients/bills/' + obj.id;
                            url_payment  = '/admin/clients/payment_plan/' + obj.id;

                            return '<a href="/admin/clients/view/' + obj.id + '" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> Ver</a>' +
                                   '<a href="/admin/clients/edit/' + obj.id + '" class="btn btn-success btn-xs"><i class="fa fa-edit"></i> Modificar</a>' +
                                   '<a href="'+ url_contrato + '" class="btn btn-warning btn-xs" '+dis_contrato+'><i class="fa fa-cubes"></i> Servicios</a>' +
                                   '<a href="'+ url_factura + '" class="btn btn-primary btn-xs" '+dis_factura+'><i class="fa fa-file-text"></i> Facturas</a>' +
                                   '<a href="'+ url_payment + '" class="btn btn-danger btn-xs"><i class="fa fa-file-powerpoint-o"></i> Plan de Pagos</a>';
                            }
                        }];

        var columnDefs = [ { orderable: false, "targets": -1 },          // last column
                           {className: "dt-center", "targets": [1, -1]}
                         ];

        var initSwitchery = true;
        var urlSwitchery = '/admin/clients';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'asc'
        var pageLength = 100;
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense, pageLength);

    }

    // roles table
    if($('#dataTableRoles').length){
        
        var obj = $('#dataTableRoles');

        var action = '/roles/list';
        
        var columns =  [ { data: "id", name: "id" },
                        { data: "display_name", name: "display_name" },
                        { data: "description", name: "description" },
                        { data: function (obj) {

                                var permissions = '';

                                if(obj.perms != null && obj.perms != '' &&  obj.perms.length !== 0){

                                    $.each( obj.perms, function( key, value) {
                                    
                                        permissions = permissions + '<span class="tag">'+value.display_name+'</span>';
                                    
                                    });

                                }

                                return permissions;
                            }

                        },
                        { data: function (obj) {
                            return '<a href="/roles/edit/' + obj.id + '" class="btn btn-success btn-xs"><i class="fa fa-edit"></i> Modificar</a>';
                            }
                        }];

        var columnDefs = [ { orderable: false, "targets": [-1] },          // last column
                           {className: "dt-center", "targets": [-1]}
                         ];

        var initSwitchery = false;
        var urlSwitchery = '';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'asc'
        var pageLength = 10;
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense, pageLength);

    }

    // services table
    if($('#dataTableServices').length){
        
        var obj = $('#dataTableServices');

        var action = '/admin/services/list';
        
        var columns =  [ { data: "id", name: "id" },
                        { data: "nombre", name: "nombre" },
                        { data: "tipo", name: "tipo" },
                        { data: "abono_mensual", name: "abono_mensual" },
                        { data: "abono_proporcional", name: "abono_proporcional" },
                        { data: "costo_instalacion", name: "costo_instalacion" },
                        { data: function (obj) {

                            status = obj.status == 1 ? 'checked' : '';
                            return '<input type="checkbox" class="js-switch status_switcher" name="status" '+status+' disabled="disabled" data-id="' + obj.id + '" />';

                            }
                        },
                        { data: function (obj) {
                            return '<a href="/admin/services/edit/' + obj.id + '" class="btn btn-success btn-xs"><i class="fa fa-edit"></i> Modificar</a>';
                            }
                        }];

        var columnDefs = [ { orderable: false, "targets": -1 },          // last column
                           {className: "dt-center", "targets": [-1, -2, 3, 7]},
                           { className: "dt-right", "targets": [4, 5, 6] }
                         ];

        var initSwitchery = true;
        var urlSwitchery = '/admin/services';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'asc'
        var pageLength = 10;
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense, pageLength);

    }
    
    // client services table
    if($('#dataTableClientServices').length){
        
        var obj = $('#dataTableClientServices');

        var action = '/admin/clients/services/list/' + $('#user_id').val();
        
        var columns =  [{ data: "id", name: "id" }, 
                        { data: "contrato_nro", name: "contrato_nro" },
                        { data: "nombre", name: "nombre" },
                        { data: "tipo", name: "tipo" },
                        { data: "abono_mensual", name: "abono_mensual" },
                        { data: "abono_proporcional", name: "abono_proporcional" },
                        { data: "costo_instalacion", name: "costo_instalacion" },
                        { data: "plan_pago", name: "plan_pago" },
                        { data: function (obj) {
                            
                            disabled = obj.servicio.status == 0 ? 'disabled="disabled"' : ''
                            
                            status = obj.status == 1 ? 'checked' : '';
                            return '<input type="checkbox" class="js-switch status_switcher" name="status" '+status+' '+disabled+' data-id="' + obj.id + '" />';

                            }
                        },
                        { data: function (obj) {
                            
                            href = obj.servicio.status == 1 ? '/admin/clients/services/edit/' + obj.id : '#';
                            disabled = obj.servicio.status == 0 ? 'disabled="disabled"' : ''

                            return '<a href="'+href+'" class="btn btn-success btn-xs" '+disabled+'><i class="fa fa-edit"></i> Modificar</a>';
                            }
                        }];

        var columnDefs = [ { orderable: false, "targets": -1 },          // last column
                           {className: "dt-center", "targets": [-1, -2, 3, 7]},
                           { className: "dt-right", "targets": [4, 5, 6] }
                         ];

        var initSwitchery = true;
        var urlSwitchery = '/admin/clients/services';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'asc'
        var pageLength = 10;
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense, pageLength);

    }

    // talonarios table
    if($('#dataTableTalonarios').length){
        
        var obj = $('#dataTableTalonarios');

        var action = '/admin/config/invoice/list';
        
        var columns =  [ { data: "letra", name: "letra" },
                        { data: "nombre", name: "nombre" },
                        { data: "nro_punto_vta", name: "nro_punto_vta" },
                        { data: "nro_inicial", name: "nro_inicial" },
                        { data: function (obj) {
                            return '<a href="/admin/config/invoice/edit/' + obj.id + '" class="btn btn-success btn-xs"><i class="fa fa-edit"></i> Modificar</a>';
                            }
                        }];

        var columnDefs = [ { orderable: false, "targets": -1 },          // last column
                           {className: "dt-center", "targets": [0, 2, 3, 4]},
                           { className: "dt-right", "targets": [] }
                         ];

        var initSwitchery = false;
        // var urlSwitchery = '/admin/services';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'asc'
        var pageLength = 10;
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense, pageLength);

    }

    // cuotas table
    if($('#dataTableCuotas').length){

        var obj = $('#dataTableCuotas');

        var action = '/admin/config/dues/list';
        
        var columns =  [{ data: "numero", name: "numero" },
                        { data: function (obj) {
                                    return obj.interes + '%';
                                }
                        },

                        { data: function (obj) {
                            return '<a href="/admin/config/dues/edit/' + obj.id + '" class="btn btn-success btn-xs"><i class="fa fa-edit"></i> Modificar</a>';
                            }
                        }];

        var columnDefs = [ { orderable: false, "targets": -1 },          // last column
                           {className: "dt-center", "targets": []},
                           { className: "dt-right", "targets": [] }
                         ];

        var initSwitchery = false;
        // var urlSwitchery = '/admin/services';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'asc'
        var pageLength = 10;
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense, pageLength);

    }

    // period table
    if($('#dataTablePeriodo').length){
        
        var obj = $('#dataTablePeriodo');

        var action = '/admin/period/list';
        
        var columns =  [ 
                        { data: "id", name: "id" },
                        { data: "periodo", name: "periodo" },
                        { data: "fecha_emision", name: "fecha_emision" },
                        { data: function (obj) {

                            return obj.pagas + ' de ' + obj.total;

                            }
                        },
                        { data: function (obj) {

                            return obj.mails + ' de ' + obj.total;

                            }
                        },
                        { data: function (obj) {

                            return '<a href="' + obj.pdf + '" target="_blank" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> Ver PDF</a>' +
                                   '<a href="/admin/period/view/' + obj.periodo + '" class="btn btn-success btn-xs"><i class="fa fa-file-text-o"></i> Ver Facturas</a>' +
                                   //'<a href="/admin/period/download/pmc/' + obj.periodo + '" class="btn btn-primary btn-xs" title="Descargar Archivo de Pago Mis Cuentas"><i class="fa fa-file-powerpoint-o"></i> Archivo PMC</a>' +
                                   '<a href="/admin/period/send/' + obj.periodo + '" class="btn btn-warning btn-xs" title="Enviar las facturas a los Clientes"><i class="fa fa-send-o"></i> Enviar Correos</a>';
                            }
                        }];

        var columnDefs = [ { orderable: false, "targets": -1 },          // last column
                           { className: "dt-center", "targets": [-1, 1, 2]},
                           { visible: false, 'targets': [0] }

                         ];

        var initSwitchery = false;
        var urlSwitchery = '/admin/period';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'desc'
        var pageLength = 10;
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense, pageLength);

    }

    // facturas table
    if($('#dataTableClientFacturas').length){
        
        var obj = $('#dataTableClientFacturas');

        var action = '/admin/clients/bills/list/' + $('#user_id').val();
        
        var columns =  [{ data: "id", name: "id" },
                        { data: "periodo", name: "periodo" },
                        { data: function (obj) {

                            return obj.talonario.letra + ' ' + obj.talonario.nro_punto_vta + ' - '+ obj.nro_factura;

                            },
                            name: "nro_factura"
                        },
                        // { data: "nro_cliente", name: "nro_cliente" },
                        // { data: function (obj) {

                        //     return obj.cliente.nombre_apellido;

                        //     },
                        //     name: "cliente.nombre_apellido"
                        // },
                        { data: "fecha_emision", name: "fecha_emision" },
                        { data: "primer_vto_fecha", name: "primer_vto_fecha" },
                        { data: "importe_total", name: "importe_total" },
                        { data: "importe_pago", name: "importe_pago" },
                        // { data: function (obj) {

                        //     return obj.mail_to != null && obj.mail_to != '' ? obj.mail_to : 'NO';

                        //     },
                        //     name: "mail_to"
                        // },                        
                        { data: function (obj) {

                            return obj.fecha_pago == null  ? '<a href="/admin/period/bill-pay/' + obj.id + '" class="btn btn-danger btn-xs" title="Imputar Pago">Pendiente</a>' : '<strong>Pagada</strong><br>' + obj.fecha_pago + '<br>' + obj.forma_pago;

                            }
                        },                        
                        { data: function (obj) {

                            var btnSend = '';
                            if (obj.cliente.email != null && obj.cliente.email != '') {
                                btnSend = '<a href="/admin/period/bill-send/' + obj.id + '" class="btn btn-warning btn-xs" title="Enviar la factura por Correo Electrónico"><i class="fa fa-send-o"></i> Enviar</a>';
                            }

                            var btnBonificacion = '';
                            if (obj.btn_bonificacion == true ) {
                                btnBonificacion = '<a href="/admin/period/bill-improve/' + obj.id + '" class="btn btn-primary btn-xs"><i class="fa fa-dollar"></i> Bonificar</a>';
                            }

                            var btnUpdate = '';
                            if (obj.fecha_pago == null && obj.btn_actualizar == true ) {
                                btnUpdate = '<a href="/admin/period/bill-update/' + obj.id + '" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i> Actualizar</a>';
                            }

                            var btnCancelarPago = '';
                            if (obj.fecha_pago != null) {
                                btnCancelarPago = '<a href="/admin/period/bill-pay-cancel/' + obj.id + '" class="btn btn-danger btn-xs"><i class="fa fa-remove"></i> Cancelar Pago</a>';

                            }
                            
                            return '<a href="' + obj.pdf + '" target="_blank" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> Ver PDF</a>' +
                                   '<a href="/admin/period/bill/' + obj.id + '" class="btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Detalle</a>' +
                                   btnSend +
                                   btnBonificacion +
                                   btnUpdate +
                                   btnCancelarPago;
                            }
                        }
                        ];

        var columnDefs = [ { orderable: false, "targets": -1 },          // last column
                           { className: "dt-center", "targets": [1, 2, 3, 4, 6]},
                           { className: "dt-right", "targets": [5] },
                           { visible: false, 'targets': [0] }

                         ];

        var initSwitchery = false;
        var urlSwitchery = '/admin/period';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'desc'
        var pageLength = 10;
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense, pageLength);

    }

    // facturas periodo table
    if($('#dataTableFacturasPeriodo').length){
        
        var obj = $('#dataTableFacturasPeriodo');

        var action = '/admin/period/view/list/' + $('#periodo').val();
        
        var columns =  [{ data: "id", name: "id" },
                        { data: function (obj) {

                            return obj.talonario.letra + ' ' + obj.talonario.nro_punto_vta + ' - '+ obj.nro_factura;

                            },
                            name: "nro_factura"
                        },
                        { data: "nro_cliente", name: "nro_cliente" },
                        { data: function (obj) {

                            return obj.cliente.dni;

                            },
                            name: "cliente.dni"
                        },
                        { data: function (obj) {

                            return obj.cliente.nombre_apellido;

                            },
                            name: "cliente.nombre_apellido"
                        },
                        { data: "fecha_emision", name: "fecha_emision" },
                        { data: "primer_vto_fecha", name: "primer_vto_fecha" },
                        { data: "importe_total", name: "importe_total" },
                        { data: function (obj) {

                            return obj.mail_to != null && obj.mail_to != '' ? obj.mail_to : 'NO';

                            },
                            name: "mail_to"
                        },
                        { data: function (obj) {

                            return obj.fecha_pago == null  ? '<a href="/admin/period/bill-pay/' + obj.id + '" class="btn btn-danger btn-xs" title="Imputar Pago">Pendiente</a>' : 'Pagada: ' + obj.fecha_pago;

                            }
                        },                        
                        { data: function (obj) {

                            var btnSend = '';
                            if (obj.cliente.email != null && obj.cliente.email != '') {
                                btnSend = '<a href="/admin/period/bill-send/' + obj.id + '" class="btn btn-warning btn-xs" title="Enviar la factura por Correo Electrónico"><i class="fa fa-send-o"></i> Enviar</a>';
                            }

                            var btnBonificacion = '';
                            if (obj.btn_bonificacion == true ) {
                                btnBonificacion = '<a href="/admin/period/bill-improve/' + obj.id + '" class="btn btn-primary btn-xs"><i class="fa fa-dollar"></i> Bonificar</a>';
                            }

                            var btnUpdate = '';
                            if (obj.fecha_pago == null && obj.btn_actualizar == true ) {
                                btnUpdate = '<a href="/admin/period/bill-update/' + obj.id + '" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i> Actualizar</a>';
                            }

                            var btnCancelarPago = '';
                            if (obj.fecha_pago != null) {
                                btnCancelarPago = '<a href="/admin/period/bill-pay-cancel/' + obj.id + '" class="btn btn-danger btn-xs"><i class="fa fa-remove"></i> Cancelar Pago</a>';

                            }

                            return '<a href="' + obj.pdf + '" target="_blank" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> Ver PDF</a>' +
                                   '<a href="/admin/period/bill/' + obj.id + '" class="btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Detalle</a>' +
                                   btnSend +
                                   btnBonificacion +
                                   btnUpdate +
                                   btnCancelarPago;
                            }
                        }
                        ];

        var columnDefs = [ { orderable: false, "targets": -1 },          // last column
                           { className: "dt-center", "targets": [1, 2, 5, 6, 8, 9]},
                           { className: "dt-right", "targets": [7] },
                           { visible: false, 'targets': [0] }

                         ];

        var initSwitchery = false;
        var urlSwitchery = '/admin/period';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'desc'
        var pageLength = 10;
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense, pageLength);

    }

    // facturas periodo table
    if($('#dataTableFacturasBuscar').length){
        
        var obj = $('#dataTableFacturasBuscar');

        var action = '/admin/bills/list';
        
        var columns =  [{ data: "id", name: "id" },
                        { data: function (obj) {

                            return obj.talonario.letra + ' ' + obj.talonario.nro_punto_vta + ' - '+ obj.nro_factura;

                            },
                            name: "nro_factura"
                        },
                        { data: "nro_cliente", name: "nro_cliente" },
                        { data: function (obj) {

                            return obj.cliente.dni;

                            },
                            name: "cliente.dni"
                        },
                        { data: function (obj) {

                            return obj.cliente.nombre_apellido;

                            },
                            name: "cliente.nombre_apellido"
                        },
                        { data: "fecha_emision", name: "fecha_emision" },
                        { data: "primer_vto_fecha", name: "primer_vto_fecha" },
                        { data: "importe_total", name: "importe_total" },
                        { data: function (obj) {

                            return obj.mail_to != null && obj.mail_to != '' ? obj.mail_to : 'NO';

                            },
                            name: "mail_to"
                        },
                        { data: function (obj) {

                            return obj.fecha_pago == null  ? '<a href="/admin/period/bill-pay/' + obj.id + '" class="btn btn-danger btn-xs" title="Imputar Pago">Pendiente</a>' : 'Pagada: ' + obj.fecha_pago;

                            }
                        },                        
                        { data: function (obj) {

                            var btnSend = '';
                            if (obj.cliente.email != null && obj.cliente.email != '') {
                                btnSend = '<a href="/admin/period/bill-send/' + obj.id + '" class="btn btn-warning btn-xs" title="Enviar la factura por Correo Electrónico"><i class="fa fa-send-o"></i> Enviar</a>';
                            }

                            var btnBonificacion = '';
                            if (obj.btn_bonificacion == true ) {
                                btnBonificacion = '<a href="/admin/period/bill-improve/' + obj.id + '" class="btn btn-primary btn-xs"><i class="fa fa-dollar"></i> Bonificar</a>';
                            }

                            var btnUpdate = '';
                            if (obj.fecha_pago == null && obj.btn_actualizar == true ) {
                                btnUpdate = '<a href="/admin/period/bill-update/' + obj.id + '" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i> Actualizar</a>';
                            }

                            var btnCancelarPago = '';
                            if (obj.fecha_pago != null) {
                                btnCancelarPago = '<a href="/admin/period/bill-pay-cancel/' + obj.id + '" class="btn btn-danger btn-xs"><i class="fa fa-remove"></i> Cancelar Pago</a>';

                            }
                            
                            return '<a href="' + obj.pdf + '" target="_blank" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> Ver PDF</a>' +
                                   '<a href="/admin/period/bill/' + obj.id + '" class="btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Detalle</a>' +
                                   btnSend +
                                   btnBonificacion +
                                   btnUpdate +
                                   btnCancelarPago;
                            }
                        }
                        ];

        var columnDefs = [ { orderable: false, "targets": -1 },          // last column
                           { className: "dt-center", "targets": [1, 2, 5, 6, 8, 9]},
                           { className: "dt-right", "targets": [7] },
                           { visible: false, 'targets': [0] }

                         ];

        var initSwitchery = false;
        var urlSwitchery = '/admin/period';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'desc'
        var pageLength = 10;
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense, pageLength);

    }

    // reclamos table
    if($('#dataTableReclamos').length){
        
        var obj = $('#dataTableReclamos');

        var action = '/admin/claims/list';
        
        var columns =  [{ data: function (obj) {

                            data  = obj.id
                            style = obj.leido_admin == 0 ? 'highlight' : '';
                            return '<span class="'+style+'">'+data+'</span>';
                            },
                            name: "id"
                        },
                        { data: function (obj) {

                            data  = obj.titulo
                            style = obj.leido_admin == 0 ? 'highlight' : '';
                            return '<span class="'+style+'">'+data+'</span>';
                            },
                            name: "titulo"
                        },
                        { data: function (obj) {

                            data  = obj.usuario.firstname + ' ' + obj.usuario.lastname;
                            style = obj.leido_admin == 0 ? 'highlight' : '';
                            return '<span class="'+style+'">'+data+'</span>';
                            },
                            name: "usuario.firstname"
                        },
                        { data: function (obj) {

                            data  = obj.servicio == null ? '-' : obj.servicio.nombre;
                            style = obj.leido_admin == 0 ? 'highlight' : '';
                            return '<span class="'+style+'">'+data+'</span>';
                            },
                            name: "servicio.nombre"
                        },
                        { data: function (obj) {

                            data  = obj.fecha;
                            style = obj.leido_admin == 0 ? 'highlight' : '';
                            return '<span class="'+style+'">'+data+'</span>';
                            },
                            name: "created_at"
                        },
                        { data: function (obj) {

                            data        = obj.status == 0 ? 'Abierto' : 'Cerrado';
                            styleStatus = obj.status == 0 ? 'label-success ' : 'label-default ';

                            // return obj.leido_admin == 0 ? '<span class="highlight">No Leido</span>' : '<span>Leido</span>';
                            style = obj.leido_admin == 0 ? 'highlight' : '';
                            // <span class="label label-success pull-right">Coming Soon</span>
                            return '<span class="label '+styleStatus + style+'">'+data+'</span>';
                            },
                            name: "status"
                        },
                        { data: function (obj) {
                            return '<a href="/admin/claims/reply/' + obj.id + '" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> Ver</a';
                            }
                        }];

        var columnDefs = [ { orderable: false, "targets": -1 },          // last column
                           {className: "dt-center", "targets": [0, 4, 5, 6, -1]},
                           { className: "dt-right", "targets": [] }
                         ];

        var initSwitchery = false;
        // var urlSwitchery = '/admin/services';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'desc'
        var pageLength = 10;
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense, pageLength);

    }

    // auditoria table
    if($('#dataTableAuditoria').length){
        
        var obj = $('#dataTableAuditoria');

        var action = '/admin/audit/list';
        
        var columns =  [ { data: "id", name: "id" },
                        { data: function (obj) {

                            return 'ID: ' + obj.user.id +' - ' + obj.user.firstname + ' ' + obj.user.lastname;
                            
                            },
                          name: "user.firstname"
                        },
                        { data: "event", name: "event" },
                        { data: "auditable_type", name: "auditable_type" },
                        { data: "old_values", name: "old_values" },
                        { data: "new_values", name: "new_values" },
                        { data: function (obj) {

                            return '<a href="'+obj.url+'">'+obj.url+'</>';
                            
                            },
                          name: "url"
                        },
                        { data: "date", name: "date" },
                        // { data: "ip_address", name: "ip_address" },
                       ];

        var columnDefs = [ {className: "dt-center", "targets": [-1]}
                         ];

        var initSwitchery = false;
        var urlSwitchery = '/audit';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'desc'
        var pageLength = 100;
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense, pageLength);

    }


    // client payment plan table
    if($('#dataTableClientPaymentPlan').length){
        
        var obj = $('#dataTableClientPaymentPlan');

        var action = '/admin/clients/payment_plan/list/' + $('#user_id').val();
        
        var columns =  [/*{ data: "id", name: "id" },*/ 
                        { data: "nombre", name: "nombre" },
                        { data: "abono_mensual", name: "abono_mensual" },
                        { data: "pp_cuotas_adeudadas", name: "pp_cuotas_adeudadas" },
                        { data: "pp_importe_total_adeudado", name: "pp_importe_total_adeudado" },
                        { data: "plan_pago", name: "plan_pago" },
                        { data: "abono_mensual_pagar", name: "abono_mensual_pagar" },
                        { data: function (obj) {
                            
                            disabled = obj.servicio.status == 0 ? 'disabled="disabled"' : ''
                            
                            status = obj.status == 1 ? 'checked' : '';
                            return '<input type="checkbox" class="js-switch status_switcher" name="status" '+status+' '+disabled+' data-id="' + obj.id + '" />';

                            }
                        },
                        { data: function (obj) {
                            
                            href = obj.servicio.status == 1 ? '/admin/clients/payment_plan/edit/' + obj.id : '#';
                            disabled = obj.servicio.status == 0 ? 'disabled="disabled"' : ''

                            return '<a href="'+href+'" class="btn btn-success btn-xs" '+disabled+'><i class="fa fa-edit"></i> Modificar</a>';
                            }
                        }];

        var columnDefs = [ { orderable: false, "targets": -1 },          // last column
                           {className: "dt-center", "targets": [-1, -2, 2, 4]},
                           { className: "dt-right", "targets": [1,3,4,5] }
                         ];

        var initSwitchery = true;
        var urlSwitchery = '/admin/clients/payment_plan';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'asc'
        var pageLength = 10;
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense, pageLength);

    }
});

// function to render table
function fillTable(obj, action,columns, columnDefs, initSwitchery = false, urlSwitchery, disableOthers, drawMap = false, orderColumn, orderSense, pageLength = 10) {

    // $.fn.dataTable.moment( 'DD/MM/YYYY' );

    obj.DataTable({
            pageLength: pageLength,
            language: {
                url: "/vendors/datatables.net/js/i18n/es_ar.lang",
            },
            dom: "lfrtip", // l -> paginator | B -> buttons
            buttons: [
                {
                  extend: "copy",
                  className: "btn-sm"
                },
                {
                  extend: "csv",
                  className: "btn-sm"
                },
                {
                  extend: "excel",
                  className: "btn-sm"
                },
                {
                  extend: "pdfHtml5", //pdf
                  className: "btn-sm"
                },
                {
                  extend: "print",
                  className: "btn-sm"
                },
            ],
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: action,
            columnDefs: columnDefs,
            columns: columns,
            order: [ [orderColumn, orderSense] ],
            drawCallback: function(response) {

                // console.log(response.json.data);
                
                // get data from Ajax response
                var data = response.json.data;

                // initialize switchery elements
                if (initSwitchery) {
                    var elems = document.querySelectorAll('.status_switcher');

                    for (var i = 0; i < elems.length; i++) {
                      var switchery = new Switchery(elems[i], {color: '#26b99a'});
                      // var switchery = new Switchery(elems[i]);
                    }
                }

                // switch change event
                $('.status_switcher').on('change', function (e) {

                    updateStatus($(this), urlSwitchery, disableOthers);

                    if (drawMap) {
                        updateMapIcon(data, $('.status_switcher').index($(this)));
                    }

                });

                if (drawMap) {
                    drawGoogleMaps(data);
                }
            }
    });
}


// set actiontive/inactive
function updateStatus(element, urlSwitchery, disableOthers = false) {
    
    // disable others elements
    if (disableOthers == true) {
    
        // $('.js-switch').show();
        $('.switchery').remove();

        $('.js-switch').each(function () {
            
            if ($(this).attr('data-id') != element.attr('data-id')) {

                $(this).attr('checked', false);
            }   

        })

        var elems = document.querySelectorAll('.js-switch');

        for (var i = 0; i < elems.length; i++) {
          var switchery = new Switchery(elems[i], {color: '#26b99a'});
          // var switchery = new Switchery(elems[i]);
        }

    }


    // console.log(element);
    var data  = new FormData();
    data.append('_token', $("input[name='_token']").val()); // append token
    data.append('status', element.prop('checked'));         // append val

    // ajax callback
    $.ajax({                                                // send ajax
        url: urlSwitchery + "/updatestatus/" + element.attr('data-id'),
        type: "POST",
        data: data,
        // enctype: 'multipart/form-data',
        processData: false,                             // tell jQuery not to process the data
        contentType: false                              // tell jQuery not to set contentType
    }).done(function(data) {

        // console.log(data);

        // if (data.status == 'success') {
            
        // }


    });

}

// jQuery autocomplete
function getAutocompleteData(ajaxURL, elementList, elementID ) {

    // console.log(elementList);
    var data  = new FormData();
    data.append('_token', $("input[name='_token']").val()); // append token

    // ajax callback
    $.ajax({                                                // send ajax
        url: ajaxURL,
        type: "POST",
        data: data,
        processData: false,                             // tell jQuery not to process the data
        contentType: false                              // tell jQuery not to set contentType
    }).done(function(data) {

        // console.log(data);

        if (data != 'null') {

            elementList.prop( "disabled", false );

            // initialize autocomplete with custom appendTo
            elementList.autocomplete({
                lookup: data,
                onSelect: function (suggestion) {
                    elementID.val(suggestion.data);
                    // alert('You selected: ' + suggestion.value + ', ' + suggestion.data);
                }
            });

        }else{
            elementList.prop( "disabled", true );
            elementList.attr('placeholder', 'No existen Datos');
        
        }
     });

 }

function getClientServices(){

    if ( $('#dataTableClientServicesAdd').size() && $("#autocomplete-client-name").size() && $('#user_id').val() != '') {

        // redraw table
        $('#dataTableClientServicesAdd').DataTable().clear().destroy();

        var obj = $('#dataTableClientServicesAdd');

        var action = '/admin/clients/services/list/' + $('#user_id').val();
        
        var columns =  [{ data: "id", name: "id" },
                        { data: "contrato_nro", name: "contrato_nro" },
                        { data: "nombre", name: "nombre" },
                        { data: "tipo", name: "tipo" },
                        { data: "abono_mensual", name: "abono_mensual" },
                        { data: "abono_proporcional", name: "abono_proporcional" },
                        { data: "costo_instalacion", name: "costo_instalacion" },
                        { data: "plan_pago", name: "plan_pago" },
                        { data: function (obj) {
                            
                            disabled = obj.servicio.status == 0 ? 'disabled="disabled"' : ''
                            
                            status = obj.status == 1 ? 'checked' : '';
                            return '<input type="checkbox" class="js-switch status_switcher" name="status" '+status+' '+disabled+' data-id="' + obj.id + '" />';

                            }
                        },
                        { data: function (obj) {
                            
                            href = obj.servicio.status == 1 ? '/admin/clients/services/edit/' + obj.id : '#';
                            disabled = obj.servicio.status == 0 ? 'disabled="disabled"' : ''

                            return '<a href="'+href+'" class="btn btn-success btn-xs" '+disabled+'><i class="fa fa-edit"></i> Modificar</a>';
                            }
                        }];

        var columnDefs = [ { orderable: false, "targets": -1 },          // last column
                           {className: "dt-center", "targets": [-1, -2]},
                           { className: "dt-right", "targets": [4,5] }
                         ];

        var initSwitchery = true;
        var urlSwitchery = '/admin/clients/services';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'asc'
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense);
    
    }else{
        
    }
}

function getPrecioFromServicio() {

    if($('#current_prices').length == 0 || $('#current_prices').val() != $('#servicio_id').val()){

        // console.log(elementList);
        var data  = new FormData();
        data.append('_token', $("input[name='_token']").val()); // append token
        data.append('servicio_id', $('#servicio_id').val());            // append user_id

        // ajax callback
        $.ajax({                                                // send ajax
            url: '/admin/clients/services/detail',
            type: "POST",
            data: data,
            processData: false,                             // tell jQuery not to process the data
            contentType: false                              // tell jQuery not to set contentType
        }).done(function(data) {

            // console.log(data);

            if (data != 'null' && data != '') {

                var abono_mensual = data.abono_mensual;
                data.abono_mensual = abono_mensual.replace(',', '');

                $('#abono_mensual').val(data.abono_mensual);
                $('#abono_proporcional').val(data.abono_proporcional);
                $('#costo_instalacion').val(data.costo_instalacion);
                $('#costo_instalacion').attr('costo_instalacion_base', data.costo_instalacion);

                // importe sugerido
                getAbonoProporcional(data.abono_mensual);

                // calculo del plan de pago
                setInteresCostoInstalacion();
            
            }

         });

    }

 }


function getAbonoProporcional(value){

    if (value != '') {

        importe = $.number(parseFloat(value / 30), 2);

        $('#abono_proporcional_sugerido').html('<a href="#" class="abono_proporcional_sugerido_importe">'+importe+'</a>');
    }
    
}

function getReclamosNoLeidos() {

        // ajax callback
        var response;
        $.ajax({
            url: "/admin/claims/listUnread",
            type: "GET",
            processData: false,                             // tell jQuery not to process the data
            contentType: false                              // tell jQuery not to set contentType
        }).done(function(data) {

            var content = '';
            
            if (data.length > 0) {
                
                $('.msg_number').html(data.length);
                
                $.each( data, function( key, value) {

                    content +='<li>'+
                                '<a href="/admin/claims/reply/'+value.id+'">' +
                                    '<span>' +
                                        '<span>' +value.usuario.firstname+' '+value.usuario.lastname+'</span>' +
                                        '<span class="time">' +value.fecha+'</span>' +
                                    '</span>' +
                                    '<span class="message">' +
                                        value.titulo
                                    '</span>' +
                                '</a>' +
                            '</li>';
                });
                
            }else{

                content +='<li>'+
                              '<span class="message">No hay reclamos sin leer</span>' +
                          '</li>';
            
            }

            // ultimo link con el acceso a la lista completa de reclamos
            content += '<li>'+
                            '<div class="text-center">'+
                                '<a href="/admin/claims">'+
                                    '<strong>Ver todos los reclamos </strong>'+
                                    '<i class="fa fa-angle-right"></i>'+
                                '</a>'+
                            '</div>'+
                        '</li>';

            $('.msg_list').html(content);

            // console.log(data);

        });    

}
 
// ajax get Cobro Express Screen
function getCobroexpressScreen() {
    
    // clear error messages
    $('.container-name ul').remove();
    $('.autocomplete-client-name').removeClass('parsley-error');

    // $('.container-date-from ul').remove();
    // $('#date_from').removeClass('parsley-error');

    // $('.container-date-to ul').remove();
    // $('#date_to').removeClass('parsley-error');


    // user name validation
    if ($('#autocomplete-client-name').val() != '' && $("input[name='user_id']").val() == '' ) {

        $('#autocomplete-client-name').addClass('parsley-error');
        $('.container-name').append('<ul class="parsley-errors-list filled"><li class="parsley">Ingrese el nombre de un Cliente válido</li></ul>');
        return;
    }


    // if ($("input[name='user_id']").val() != '') {

        // console.log(elementList);
        var data  = new FormData();
        data.append('_token', $("input[name='_token']").val()); // append token
        // data.append('periodo', $('#periodo_all').is(':checked') ? '' : $("select[name='periodo']").val());
        data.append('user_id', $("input[name='user_id']").val());

        $('.filterResult').removeClass('hidden');
        $('.cobroexpressContainerGeneral').html('<div class="col-md-12 loader"><img src="/_admin/images/loader.gif" /></div>');

        // ajax callback
        $.ajax({                                                // send ajax
            url: '/admin/cobroexpress/search',
            type: "POST",
            data: data,
            processData: false,                             // tell jQuery not to process the data
            contentType: false                              // tell jQuery not to set contentType
        }).done(function(data) {

            console.log(data);

            if (data != 'null') {

                if (data.success == false) {

                    $('.cobroexpressContainerGeneral').html('<div class="alert alert-danger"><i class="fa fa-frown-o"></i> No se encontraron registros.</div>');

                }else{

                    var downloadResultButton = '<div class="row no-print">'+
                                               ' <a href="/admin/cobroexpress/comprobante-xls" target="_blank" class="btn btn-success pull-right" title="Descargar XLS"><i class="fa fa-file-excel-o"></i> Descargar XLS</a>'+
                                               ' <a href="/admin/cobroexpress/comprobante-pdf" target="_blank" class="btn btn-danger pull-right" title="Descargar PDF"><i class="fa fa-file-pdf-o"></i> Descargar PDF</a>'+
                                               '</div>';

                    // add download pdf button
                    $('.cobroexpressContainerGeneral').html(downloadResultButton);
                    
                    var result = '';
                    var totalFooter = '';
                    var total = 0;
                    
                    $.each( data, function( key, registro) {

                        // console.log(registro);
                    
                        result += '<div class="reportContainer resumenContainer">';
                            
                            result += '<h2>Cliente: <small><a href="/admin/clients/view/' + registro.detalle_cliente.id + '" target="_blank">' +  registro.cod_cliente + ' - ' + registro.detalle_cliente.firstname + ' ' + registro.detalle_cliente.lastname +'</a></small></h2>';
                            

                            result += '<table class="table table-striped resumenTable">';
                                
                                result += '<thead>';
                                    result += '<tr>';
                                        result += '<th scope="col" class="col-md-2">Fecha de pago</th>';
                                        result += '<th scope="col" class="col-md-8">Factura</th>';
                                        result += '<th scope="col" class="col-md-2" class="right">Importe</th>';
                                    result += '</tr>';
                                result += '</thead>';
                              
                                result += '<tbody class="resumenTableBody">';

                                        $.each( registro.detalle_pagos, function( key, fecha) {
                                            
                                            $.each( fecha, function( key, pago) {
                                                
                                                result += '<tr>';
                                                    result += '<td>'+pago.fecha+'</td>';
                                                    
                                                    if (pago.nro_factura > 0 && pago.nro_sucursal > 0) {
                                                        result += '<td><a href="/admin/period/bill/' + pago.nro_factura + '" target="_blank">'+pago.nro_sucursal+' - '+pago.nro_factura+'</a></td>';
                                                    }else{
                                                        result += '<td>(SIN ESPECIFICAR)</td>';
                                                    }
                                                    

                                                    result += '<td class="right">'+$.number(pago.importe, 2 )+'</td>';
                                                result += '</tr>';

                                            });

                                        });

                                result += '</tbody>';

                                result +='<tfoot class="resumenTableFoot">';
                                    result +='<tr>';
                                      result +='<td colspan="2"><b>Total</b></td>';
                                      result +='<td class="right"><b>' + $.number(registro.total_pagos, 2 ) + '</b></td>';
                                    result +='</tr>';
                                result +='</tfoot>'; 

                            result += '</table>';

                        result += '</div>';

                        total += registro.total_pagos;
                    
                    });
                    
                    // add table
                    $('.cobroexpressContainerGeneral').append(result);

                    // add total general
                    totalFooter += '<div class="reportContainer resumenContainer">';
                            
                            

                            totalFooter += '<table class="table table-striped resumenTable">';
                                totalFooter +='<tfoot class="resumenTableFoot">';
                                    totalFooter +='<tr>';
                                      totalFooter +='<td><b>Total General</b></td>';
                                      totalFooter +='<td class="right"><b>' + $.number(total, 2 ) + '</b></td>';
                                    totalFooter +='</tr>';
                                totalFooter +='</tfoot>'; 

                            totalFooter += '</table>';

                        totalFooter += '</div>';

                    $('.cobroexpressContainerGeneral').append(totalFooter);

                    // add download pdf button
                    $('.cobroexpressContainerGeneral').append(downloadResultButton);     

                }                  

            }else{

                $('.cobroexpressContainerGeneral').html('<div class="alert alert-danger"><i class="fa fa-frown-o"></i> No se encontraron registros.</div>');
                
            }
         });

    // }else{

        // $('#autocomplete-user-name').addClass('parsley-error');
        // $('.container-name').append('<ul class="parsley-errors-list filled"><li class="parsley">Ingrese un nombre válido</li></ul>');
    
    // }    




 }

// init Google Maps
function initMap() {
    
    if($('#map').length){
        
        // console.log('Google maps loaded');

        // Create map
        GMap = new google.maps.Map($('#map').get(0), {
          zoom: 18
        });
        
        markersArray = [];

        // Create InfoWindow
        infowindow = new google.maps.InfoWindow();
    
    }

}

// update icon for render to map
function updateMapIcon(data, index) {
    
        data[index].status = data[index].status == 0 ? 1 : 0;

        drawGoogleMaps(data);
}

// draw Google Maps
function drawGoogleMaps(data) {

    if(google && google.maps){

        // console.log('Google maps rendered');
        // console.log(data);

        // clear all Markers
        while(markersArray.length) { markersArray.pop().setMap(null); }


        // Center map with first data
        GMap.setCenter({lat: parseFloat(data[0].lat), lng: parseFloat(data[0].long)});


        // Create Markers
        $.each( data, function( key, device ) {

            // console.log(device);

            // set content to infowindow
            var contentString = '<p><b>ID:</b> ' + device.id + '</p>' + 
                                '<p><b>Nombre:</b> ' + device.name + '</p>' +
                                '<p><b>Tipo:</b> ' + device.type.name + '</p>';

            // add marker
            var marker = new google.maps.Marker({
                position: {lat: parseFloat(device.lat), lng: parseFloat(device.long)},
                map: GMap,
                title: device.name,
                icon: '/images/icon-'+device.type.name.toLowerCase()+'-'+device.status+'.png'
            });
            markersArray.push(marker);

            // add event to marker
            marker.addListener('click', function() {
                infowindow.setContent(contentString);

                // infowindow.close();
                infowindow.open(GMap, marker);
            });

        });

    }

}

function setInteresCostoInstalacion(){

    if ($('#costo_instalacion').attr('costo_instalacion_base') != '' && $('select[name="plan_pago"]').val() != '') {

        var costo = 0;
        costo = parseFloat($('#costo_instalacion').attr('costo_instalacion_base')) + parseFloat($('#costo_instalacion').attr('costo_instalacion_base') * $('select[name="plan_pago"] option:selected').attr('tasa') / 100);
        costo = $.number(costo, 2, '.', ',');

        $('#costo_instalacion').val(costo); 

    }

}


// ajax get Balance Screen
function getBalanceScreen() {
    
    // clear error messages
    $('.container-name ul').remove();
    $('.autocomplete-client-name').removeClass('parsley-error');

    $('.container-date-from ul').remove();
    $('#date_from').removeClass('parsley-error');

    $('.container-date-to ul').remove();
    $('#date_to').removeClass('parsley-error');


    // user name validation
    if ($('#autocomplete-client-name').val() != '' && $("input[name='user_id']").val() == '' ) {

        $('#autocomplete-client-name').addClass('parsley-error');
        $('.container-name').append('<ul class="parsley-errors-list filled"><li class="parsley">Ingrese el nombre de un Cliente válido</li></ul>');
        return;
    }


    // if ($("input[name='user_id']").val() != '') {

        // console.log(elementList);
        var data  = new FormData();
        data.append('_token', $("input[name='_token']").val()); // append token
        data.append('periodo', $('#periodo_all').is(':checked') ? '' : $("select[name='periodo']").val());
        data.append('user_id', $("input[name='user_id']").val());

        $('.filterResult').removeClass('hidden');
        $('.balanceContainerGeneral').html('<div class="col-md-12 loader"><img src="/_admin/images/loader.gif" /></div>');

        // ajax callback
        $.ajax({                                                // send ajax
            url: '/admin/balance/general/search',
            type: "POST",
            data: data,
            processData: false,                             // tell jQuery not to process the data
            contentType: false                              // tell jQuery not to set contentType
        }).done(function(data) {

            // console.log(data);

            if (data != 'null') {

                if (data.success == false) {

                    $('.balanceContainerGeneral').html('<div class="alert alert-danger"><i class="fa fa-frown-o"></i> No se encontraron facturas.</div>');

                }else{

                    var downloadResultButton = '<div class="row no-print">'+
                                               ' <a href="/admin/balance/general/comprobante" target="_blank" class="btn btn-primary pull-right" title="Descargar PDF"><i class="fa fa-download"></i> Descargar</a>'+
                                               '</div>';

                    var downloadResultButton = '<div class="row no-print">'+
                                               ' <a href="/admin/balance/general/comprobante-xls" target="_blank" class="btn btn-success pull-right" title="Descargar XLS"><i class="fa fa-file-excel-o"></i> Descargar XLS</a>'+
                                               ' <a href="/admin/balance/general/comprobante-pdf" target="_blank" class="btn btn-danger pull-right" title="Descargar PDF"><i class="fa fa-file-pdf-o"></i> Descargar PDF</a>'+
                                               '</div>';

                    // add download pdf button
                    $('.balanceContainerGeneral').html(downloadResultButton);
                    
                    var cliente = $('#client_all').is(':checked') ? 'Todos' : $("#autocomplete-client-name").val();
                    var result = '';
                    var footer_facturas_total = 0;
                    var footer_facturas_pagadas = 0;
                    var footer_facturas_adeudadas = 0;
                    var footer_importe_facturado = 0;
                    var footer_importe_pagado = 0;
                    
                    result += '<div class="reportContainer resumenContainer">';
                        
                        
                        result += '<h2>Cliente: <small>'+ cliente +'</small></h2>';
                        
                        // result += '<h4>'+infoCuenta.name+' <small>'+infoDivisa.name+' ('+infoDivisa.code+') | '+data.desde+' - '+data.hasta+'</small></h4>';

                        result += '<table class="table table-striped resumenTable">';
                            
                            result += '<thead>';
                                result += '<tr>';
                                    result += '<th>Período</th>';
                                    result += '<th class="center">Total Facturas</th>';
                                    result += '<th class="center">Facturas Pagadas</th>';
                                    result += '<th class="center">Facturas Adeudadas</th>';
                                    result += '<th class="right">Importe Facturado</th>';
                                    result += '<th class="right">Importe Pagado</th>';
                                result += '</tr>';
                            result += '</thead>';
                          
                            result += '<tbody class="resumenTableBody">';

                                $.each( data, function( key, periodo) {
                                    
                                    footer_facturas_total     = footer_facturas_total + periodo.facturas_total;
                                    footer_facturas_pagadas   = footer_facturas_pagadas + periodo.facturas_pagadas;
                                    footer_facturas_adeudadas = footer_facturas_adeudadas + periodo.facturas_adeudadas;
                                    footer_importe_facturado  = parseFloat(footer_importe_facturado) + parseFloat(periodo.importe_facturado);
                                    footer_importe_pagado     = parseFloat(footer_importe_pagado) + parseFloat(periodo.importe_pagado);

                                    result += '<tr>';
                                        result += '<td>'+periodo.periodo+'</td>';
                                        result += '<td class="center">'+periodo.facturas_total+'</td>';
                                        result += '<td class="center">'+periodo.facturas_pagadas+'</td>';
                                        result += '<td class="center">'+periodo.facturas_adeudadas+'</td>';
                                        result += '<td class="right">'+$.number(periodo.importe_facturado, 2 )+'</td>';
                                        result += '<td class="right">'+$.number(periodo.importe_pagado, 2 )+'</td>';
                                    result += '</tr>';
                                });

                            result += '</tbody>';

                            result +='<tfoot class="resumenTableFoot">';
                                result +='<tr>';
                                  result +='<td><b>Totales</b></td>';
                                  result +='<td class="center"><b>' + footer_facturas_total + '</b></td>';
                                  result +='<td class="center"><b>' + footer_facturas_pagadas + '</b></td>';
                                  result +='<td class="center"><b>' + footer_facturas_adeudadas + '</b></td>';
                                  result +='<td class="right"><b>' + $.number(footer_importe_facturado, 2 ) + '</b></td>';
                                  result +='<td class="right"><b>' + $.number(footer_importe_pagado, 2 ) + '</b></td>';
                                result +='</tr>';
                            result +='</tfoot>'; 

                        result += '</table>';

                    result += '</div>';
                    
                    // add table
                    $('.balanceContainerGeneral').append(result);

                    // add download pdf button
                    $('.balanceContainerGeneral').append(downloadResultButton);     

                }                  

            }else{

                $('.balanceContainerGeneral').html('<div class="alert alert-danger"><i class="fa fa-frown-o"></i> No se encontraron movimientos.</div>');
                
            }
         });

    // }else{

        // $('#autocomplete-user-name').addClass('parsley-error');
        // $('.container-name').append('<ul class="parsley-errors-list filled"><li class="parsley">Ingrese un nombre válido</li></ul>');
    
    // }    




 }


// ajax get Balance Detalle Screen
function getBalanceDetalleScreen() {

    $('.container-name ul').remove();
    $('#autocomplete-client-name').removeClass('parsley-error');

    $('.container-date-from ul').remove();
    $('#date_from').removeClass('parsley-error');

    $('.container-date-to ul').remove();
    $('#date_to').removeClass('parsley-error');

    // user name validation
    if ($('#autocomplete-client-name').val() != '' && $("input[name='user_id']").val() == '' ) {

        $('#autocomplete-client-name').addClass('parsley-error');
        $('.container-name').append('<ul class="parsley-errors-list filled"><li class="parsley">Ingrese un nombre válido</li></ul>');
        return;
    }

    // console.log(elementList);
    var data  = new FormData();
    data.append('_token', $("input[name='_token']").val()); // append token
    data.append('user_id', $("input[name='user_id']").val());
    // data.append('user_all', $('#user_all').prop("checked"));
    data.append('date_from', $("input[name='date_from']").val());
    data.append('date_to', $("input[name='date_to']").val());
    // data.append('type', type);  // screen / pdf

    $('.filterResult').removeClass('hidden');
    $('.balanceDetalleContainerGeneral').html('<div class="col-md-12 loader"><img src="/_admin/images/loader.gif" /></div>');

    // ajax callback
    $.ajax({                                                // send ajax
        url: '/admin/balance/detail/search',
        type: "POST",
        data: data,
        processData: false,                             // tell jQuery not to process the data
        contentType: false                              // tell jQuery not to set contentType
    }).done(function(data) {

        // console.log(data);

        if (data != 'null') {

                var downloadResultButton = '<div class="row no-print">'+
                                           ' <a href="/admin/balance/detail/comprobante-xls" target="_blank" class="btn btn-success pull-right" title="Descargar XLS"><i class="fa fa-file-excel-o"></i> Descargar XLS</a>'+
                                           ' <a href="/admin/balance/detail/comprobante-pdf" target="_blank" class="btn btn-danger pull-right" title="Descargar PDF"><i class="fa fa-file-pdf-o"></i> Descargar PDF</a>'+
                                           '</div>';


                // add download pdf button
                $('.balanceDetalleContainerGeneral').html(downloadResultButton);
                
                $.each( data, function( key, users) {
                    
                    // console.log(key);
                    // console.log(factura);

                    var result = '';
                    var total_importe_facturado = 0;
                    var total_importe_pagado = 0;
                    var total_importe_adeudado = 0;

                    var user = users[0];

                    result += '<div class="reportContainer resumenContainer">';
                        
                        result += '<h2><a href="/admin/clients/bills/' + user.cliente.id + '" target="_blank">' + user.cliente.nombre_apellido +'</a> <small>(Cliente Nro. '+user.nro_cliente+')</small></h2>';
                        
                        result += '<table class="table table-striped resumenTable">';
                            
                            result += '<thead>';
                                result += '<tr>';
                                    result += '<th>Período</th>';
                                    result += '<th>Factura</th>';
                                    result += '<th class="center">Fecha de Emisión</th>';
                                    result += '<th class="right">Importe Facturado</th>';
                                    result += '<th class="center">Fecha de Pago</th>';
                                    result += '<th class="right">Importe Pagado</th>';
                                    result += '<th>Medio de Pago</th>';
                                    result += '<th>Importe Adeudado</th>';
                                result += '</tr>';
                            result += '</thead>';
                          
                            result += '<tbody class="resumenTableBody">';
                                
                                $.each( users, function( key, factura) {

                                    if (factura.importe_pago != '') {
                                    
                                        class_tr =  '';
                                        importe_pago = factura.importe_pago;
                                        importe_adeudado = '';
                                    
                                    }else{
                                        
                                        class_tr =  'debe';
                                        importe_pago = 0;
                                        total_importe_adeudado = parseFloat(total_importe_adeudado) + parseFloat(factura.importe_total);
                                        importe_adeudado = factura.importe_total;

                                    }

                                    // totalizo las facturas y los pagos
                                    total_importe_facturado = parseFloat(total_importe_facturado) + parseFloat(factura.importe_total);
                                    total_importe_pagado = parseFloat(total_importe_pagado) + parseFloat(importe_pago);

                                    // compongo el cuerpo de la tabla
                                    result += '<tr class="'+class_tr+'"">';
                                        result += '<td class="center">'+factura.periodo+'</td>';
                                        result += '<td><a href="/admin/period/bill/' + factura.id + '" target="_blank">'+factura.talonario.letra + ' ' + factura.talonario.nro_punto_vta + ' - '+ factura.nro_factura+'</a></td>';
                                        result += '<td class="center">'+factura.fecha_emision+'</td>';
                                        result += '<td class="right">'+factura.importe_total+'</td>';
                                        result += '<td class="center">'+factura.fecha_pago+'</td>';
                                        result += '<td class="right">'+factura.importe_pago+'</td>';
                                        result += '<td>'+factura.forma_pago+'</td>';
                                        result += '<td class="right">'+importe_adeudado+'</td>';
                                    result += '</tr>';

                                });

                            result += '</tbody>';

                            result +='<tfoot class="resumenTableFoot">';
                                result +='<tr>';
                                  result +='<td colspan="2"><b>Total</b></td>';
                                  result +='<td colspan="2" class="right"><b>' + $.number(total_importe_facturado, 2) + '</b></td>';
                                  result +='<td colspan="2" class="right"><b>' + $.number(total_importe_pagado, 2) + '</b></td>';
                                  result +='<td colspan="2" class="right"><b>' + $.number(total_importe_adeudado, 2) + '</b></td>';
                                result +='</tr>';
                            result +='</tfoot>'; 

                        result += '</table>';

                    result += '</div>';
                        
                    $('.balanceDetalleContainerGeneral').append(result);

            });

            // add download pdf button
            $('.balanceDetalleContainerGeneral').append(downloadResultButton);   

                               

        }else{

            $('.balanceDetalleContainerGeneral').html('<div class="alert alert-danger"><i class="fa fa-frown-o"></i> No se encontraron movimientos.</div>');
            
        }

     });

    // }else{

    //     $('#autocomplete-client-name').addClass('parsley-error');
    //     $('.container-name').append('<ul class="parsley-errors-list filled"><li class="parsley">Ingrese un nombre válido</li></ul>');
    
    // }

 }

function getTotalDeudaPP(){

    var importe = $('#abono_mensual').val();
    var cuotas_deuda = $('#pp_cuotas_adeudadas').val();
    var deuda_instalacion = $('#deuda_instalacion').val();
    
    if (importe != '' && cuotas_deuda) {

        subtotal = parseFloat(importe) * parseFloat(cuotas_deuda); 
        total_deuda = parseFloat(subtotal) + parseFloat(importe * deuda_instalacion / 100);

        $('#total_deuda').val($.number(total_deuda, 2, '.', ','));
        $('#hidden_total_deuda').val(total_deuda);
        
    }   
}

function getImporteSistemaFrances(){

    var importe = $('#abono_mensual').val();
    var cuotas_deuda = $('#pp_cuotas_adeudadas').val();
    var tasa = $('#pp_tasa').val() / 100;
    var cuotas_pago = $('#pp_plan_pago').val();
    var deuda_instalacion = $('#deuda_instalacion').val();
    var total_deuda = $('#hidden_total_deuda').val();

    if (importe != '' && cuotas_deuda != '' && cuotas_pago != '' && total_deuda != '') {

        if (cuotas_pago == 1) {

            cuota_mensual = $.number(parseFloat(total_deuda), 2, '.', ',');

        }else{

            // calculo subtotal 
            subtotal = parseFloat(importe) * parseFloat(cuotas_deuda); 
            
            // calculo numnerador 
            base = tasa == 0 ? 1 : tasa; 
            numerador   = ( Math.pow(1 + parseFloat(tasa), cuotas_pago) ) * base;
            
            // calculo denominador
            denominador = ( Math.pow(1 + parseFloat(tasa), cuotas_pago) ) - 1;
            denominador = denominador == 0 ? 1 : denominador;

            // calculo cuota mensual
            cuota_mensual = $.number(parseFloat(total_deuda) * (numerador / denominador), 2, '.', ',');
            
        }

        $('#abono_mensual_pagar').val(cuota_mensual);

    }


}



// ----------------------------------------------------------------------------------------------
// ----------------------------------- UNUSED FUNCTIONS -----------------------------------------
// ----------------------------------------------------------------------------------------------



/*
function getCuentaFromUser() {

    // console.log(elementList);
    var data  = new FormData();
    data.append('_token', $("input[name='_token']").val()); // append token
    data.append('user_id', $('#user_id').val());            // append user_id

    // ajax callback
    $.ajax({                                                // send ajax
        url: '/users/get/cuenta',
        type: "POST",
        data: data,
        processData: false,                             // tell jQuery not to process the data
        contentType: false                              // tell jQuery not to set contentType
    }).done(function(data) {

        // console.log(data);

        if (data != 'null' && data != '') {

            $('select[name="cuenta"]').val(data);
        
            // element defined in movimientos edit
            // if ($('#preventDefaultBehabior').size() == 0){

                selectDivisaDefault($('select[name="divisa"]'), $('select[name="cuenta"]').find(':selected').attr('divisa'));
                
                if ($('select[name="divisa"]').size() && $('#price').size()) {
                
                    selectPriceDefault($('#price'), $('select[name="divisa"]').find(':selected').attr('price'), true);
                
                }
            // }

        }

     });

 }

// get cotizacion from Google
function getCotizacionFromGoogle(elementID, divisaFrom, divisaTo, amount) {

    if(divisaFrom != '' && divisaFrom.length == 3){
    
        var data  = new FormData();
        data.append('_token', $("input[name='_token']").val()); // append token
        data.append('code_from', divisaFrom);
        data.append('code_to', divisaTo);
        data.append('amount', amount);

        elementID.val('');
        elementID.attr('placeholder', 'Buscando Cotización...');
        elementID.prop( "disabled", true );
        
        // ajax callback
        $.ajax({                                                // send ajax
            url: '/divisas/convertfromgoogle',
            type: "POST",
            data: data,
            processData: false,                             // tell jQuery not to process the data
            contentType: false                              // tell jQuery not to set contentType
        }).done(function(response) {

            // console.log(response);
            if (response == 'error') {
            
                elementID.attr('placeholder', 'Error al recuperar la cotización, ingréselo manualmente');
            
            }else{

                elementID.val(response);
                
            }

         });
        
        elementID.prop( "disabled", false );
    
    }
}

// get cotizacion from Google
function getCotizacionFromGoogleLabel(elementID, divisaFrom, divisaTo, amount) {

    if(divisaFrom != ''){
    
        var data  = new FormData();
        data.append('_token', $("input[name='_token']").val()); // append token
        data.append('code_from', divisaFrom);
        data.append('code_to', divisaTo);
        data.append('amount', amount);

        elementID.html('Buscando Cotización...');
        
        // ajax callback
        $.ajax({                                                // send ajax
            url: '/divisas/convertfromgoogle',
            type: "POST",
            data: data,
            processData: false,                             // tell jQuery not to process the data
            contentType: false                              // tell jQuery not to set contentType
        }).done(function(response) {

            // console.log(response);
            if (response == 'error') {
            
                elementID.html('Error al recuperar la cotización');
            
            }else{

                elementID.html('<a href="#" class="current-price-google">'+response+'</a>');
                
            }

         });
        
    
    }
}

function selectDivisaDefault(element, index) {
    
    element.val(index);
}

function selectPriceDefault(element, value, getRemoteCotizacion) {
    element.val(value);
    if (getRemoteCotizacion) {
        getCotizacionFromGoogleLabel($('#current-price'), $('select[name="divisa"]').find(':selected').attr('code'), '', 1);
    }
}


// ajax get Balance Screen
/*
function getComisionesScreen() {

    $('.container-date-from ul').remove();
    $('#date_from').removeClass('parsley-error');

    $('.container-date-to ul').remove();
    $('#date_to').removeClass('parsley-error');

    // console.log(elementList);
    var data  = new FormData();
    data.append('_token', $("input[name='_token']").val()); // append token
    data.append('cuenta_id', $('#periodo_all').is(':checked') ? '' : $("select[name='cuenta']").val());
    
    data.append('date_from', $("input[name='date_from']").val());
    data.append('date_to', $("input[name='date_to']").val());

    $('.filterResult').removeClass('hidden');
    $('.comisionesContainerGeneral').html('<div class="col-md-12 loader"><img src="/images/loader.gif" /></div>');

    // ajax callback
    $.ajax({                                                // send ajax
        url: '/comisiones/search',
        type: "POST",
        data: data,
        processData: false,                             // tell jQuery not to process the data
        contentType: false                              // tell jQuery not to set contentType
    }).done(function(data) {

        // console.log(data);

        if (data != 'null') {

            if (data.success == false) {

                $('.comisionesContainerGeneral').html('<div class="alert alert-danger"><i class="fa fa-frown-o"></i> No se encontraron movimientos.</div>');

            }else{

                var subtotal = 0;
                var downloadResultButton = '<div class="row no-print">'+
                                           ' <a href="/comisiones/comprobante" class="btn btn-primary pull-right" title="Descargar PDF"><i class="fa fa-download"></i> Descargar</a>'+
                                           // '  <button class="btn btn-primary pull-right downloadResultButton" style="margin-right: 5px;"><i class="fa fa-download"></i> Descargar PDF</button>'
                                           '</div>';

                // add download pdf button
                $('.comisionesContainerGeneral').html(downloadResultButton);
                
                var result = '';
                var resultFooter = '';
                var debeTotal = 0;
                var haberTotal = 0;
                var saldoInicialFooter = 0;
                var haberTotalFooter = 0;
                
                $.each( data.movimientos, function( key, cuenta) {
                    
                    // var infoUser = data.users[key];
                    var infoCuenta = data.cuentas[key];
                    
                    $.each( cuenta, function( key, divisa) {
                    // $.each( users, function( key, cuenta) {
                    
                        var infoDivisa = data.divisas[key];
                        // var infoCuenta = data.cuentas[key];

                        result += '<div class="reportContainer resumenContainer">';
                            
                            result += '<h4>'+infoCuenta.name+' <small>'+infoDivisa.name+' ('+infoDivisa.code+') | '+data.desde+' - '+data.hasta+'</small></h4>';

                            result += '<table class="table table-striped resumenTable">';
                                
                                result += '<thead>';
                                    result += '<tr>';
                                        result += '<th>ID</th>';
                                        result += '<th style="width: 40%">Nombre y Apellido</th>';
                                        result += '<th class="right">Saldo Inicial</th>';
                                        result += '<th class="right">Haber</th>';
                                        result += '<th class="right">Subtotal</th>';
                                    result += '</tr>';
                                result += '</thead>';
                              
                                result += '<tbody class="resumenTableBody">';

                                    $.each( divisa, function( key, users) {
                                    
                                        // var infoDivisa = data.divisas[key];
                                        var infoUser = data.users[key];

                                        // console.log(key);
                                        // console.log(divisa);
                                        var haberTotal = 0;
                                        var subtotal = users.saldo_inicial;
                                        
                                        $.each( users.movimientos, function( key, movimiento) {

                                            var haber = movimiento.amount;
                                            subtotal = parseFloat(subtotal) + parseFloat(movimiento.amount); 
                                            haberTotal = parseFloat(haberTotal) + parseFloat(haber);
                                            haber = haber == 0 ? '' : $.number(parseFloat(haber), 2); 

                                        });

                                        // totales resumen final
                                        saldoInicialFooter  = parseFloat(saldoInicialFooter) + parseFloat(users.saldo_inicial);
                                        haberTotalFooter  = parseFloat(haberTotalFooter) + parseFloat(haberTotal);

                                        haberTotal = haberTotal > 0 ? $.number(haberTotal, 2 ) : '';

                                        result += '<tr>';
                                            result += '<td>'+infoUser.id+'</td>';
                                            result += '<td>'+infoUser.firstname+' '+infoUser.lastname+'</td>';
                                            result += '<td class="right">'+$.number(users.saldo_inicial, 2 )+'</td>';
                                            // result += '<td class="right">'+debeTotal+'</td>';
                                            result += '<td class="right">'+haberTotal+'</td>';
                                            result += '<td class="right">'+$.number(subtotal, 2 )+'</td>';
                                        result += '</tr>';

                                    });

                                result += '</tbody>';

                            result += '</table>';

                        result += '</div>';

                        resultFooter +='<tr>';
                          resultFooter += '<td><b>'+infoCuenta.name+' <small>'+infoDivisa.name+' ('+infoDivisa.code+')'+'</small></b></td>';
                          resultFooter +='<td class="right"><b>' + $.number(saldoInicialFooter, 2 ) + '</b></td>';
                          resultFooter +='<td class="right"><b>' + $.number(haberTotalFooter, 2 ) + '</b></td>';
                          resultFooter +='<td class="right"><b>'+$.number(saldoInicialFooter + haberTotalFooter, 2 )+'</b></td>';
                        resultFooter +='</tr>';

                        saldoInicialFooter = 0;
                        haberTotalFooter = 0;

                    });
                    
                });


                result += '<hr><br>';                        
                result += '<h2>Totales por cuenta <small>'+data.desde+' - '+data.hasta+'</small></h2>';


                // Resumen table
                result += '<table class="table table-striped ResumenTable_">';
                    result += '<thead>';
                        result += '<tr>';
                            result += '<th style="width: 50%">Cuenta</th>';
                            result += '<th class="right">Saldo Inicial</th>';
                            result += '<th class="right">Haber</th>';
                            result += '<th class="right">Saldo Actual</th>';
                        result += '</tr>';
                    result += '</thead>';
                    result += '<tbody class="balanceTableBody_">';
                    result += resultFooter;
                    result += '</tbody>';
                result += '</table>';

                
                // add table
                $('.comisionesContainerGeneral').append(result);


                // add download pdf button
                $('.comisionesContainerGeneral').append(downloadResultButton);     

            }                  

        }else{

            $('.comisionesContainerGeneral').html('<div class="alert alert-danger"><i class="fa fa-frown-o"></i> No se encontraron movimientos.</div>');
            
        }
     });

 }

// get saldo inicial
function getSaldoInicialFromUser() {

    if ($("#user_id").val() != '' && $("select[name='cuenta']").val() != '' && $("select[name='divisa']").val() && $("input[name='date']").val() != '' && $("input[name='amount']").val() != '') {

        var data  = new FormData();
        data.append('_token', $("input[name='_token']").val()); // append token
        data.append('user_id', $("#user_id").val());
        data.append('cuenta_id', $("select[name='cuenta']").val());
        data.append('divisa_id', $("select[name='divisa']").val());
        data.append('date', $("input[name='date']").val());

        // ajax callback
        $.ajax({                                                // send ajax
            url: '/movimientos/get_saldo_inicial',
            type: "POST",
            data: data,
            processData: false,                             // tell jQuery not to process the data
            contentType: false                              // tell jQuery not to set contentType
        }).done(function(data) {

            // console.log(data);

            if (data != 'null') {

                // set saldo actual
                if($('input[name="type"]:checked').val() == 'd'){
                    saldo_actual = $.number(parseFloat(data) - parseFloat($('#amount').val()), 2);
                }else{
                    saldo_actual = $.number(parseFloat(data) + parseFloat($('#amount').val()), 2);
                }

                $('#saldo_inicial').html($.number(parseFloat(data), 2));
                $('#saldo_actual').html(saldo_actual);

            }else{
                $('#saldo_inicial').html('');
                $('#saldo_actual').html('');
            
            }
         });
    
    }
 
 }

 // get saldo inicial

function getMovimientoByID(id) {

    // $('#parent_label').html('');

    if (id != '' ) {

        var data  = new FormData();
        data.append('_token', $("input[name='_token']").val()); // append token
        data.append('id', id);

        // ajax callback
        $.ajax({                                                // send ajax
            url: '/movimientos/get_by_id',
            type: "POST",
            data: data,
            processData: false,                             // tell jQuery not to process the data
            contentType: false                              // tell jQuery not to set contentType
        }).done(function(data) {

            console.log(data);

            if (data != 'null') {

                label = data.user.firstname + ' ' + data.user.lastname + ' | ' +data.amount + ' | ' + data.date;
                
                $('#parent_label').html(label);

            }else{

                $('#parent_label').html('Ninguno');
            
            }
         });
    
    }
 
}
*/

/*
$("#user_all").change( function(){
   $("#autocomplete-user-name").attr("disabled", $(this).is(':checked')); 


});
*/