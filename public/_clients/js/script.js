$(document).ready(function () {

    var interval = 10000; //10 segundos

    // muestro los reclamos no leidos al cargar la pagina
    getReclamosNoLeidos();

    // muestro los reclamos no leidos cada cierto intervalo
    setInterval(function(){
        getReclamosNoLeidos();
    }, interval);

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
    
    // click on cotizacion en linea CRUD movimientos 
    $(document).on('click', '.current-price-google' , function (){
        $('#price').val($('.current-price-google').html());
    });

    // GET choermanos for autocomplete list
    if($('#autocomplete-user-name').length){

        $('#autocomplete-user-name').keyup(function() {
            if ($(this).val() == '') {
                $('#user_id').val('');
            }
        })

        getAutocompleteData("/movimientos/cohermanolist", $('#autocomplete-user-name'), $('#user_id'));

    }

    // get cuenta from user 
    $('#autocomplete-user-name').change(function(){

        if ($(this).val() != '' && $('#user_id').val() != '') {

            getCuentaFromUser();

        }

    });

    // ----------------------- resumen form ----------------------- //

    // resumen de cuenta "Todos" cohermanos checkboxs
    $("#user_all").change( function(){
        $('.container-name ul').remove();
        $("#autocomplete-user-name").attr("disabled", $(this).is(':checked')); 
        $('#autocomplete-user-name').removeClass('parsley-error');
        $('#autocomplete-user-name').val('');
        $('#user_id').val('');

    });

    $("#resumenSearchForm").submit(function (e){

        e.preventDefault();

        getResumenScreen();

    });

    // ----------------------- balance form ----------------------- //

    $("#balanceSearchForm").submit(function (e){

        e.preventDefault();

        getBalanceScreen();

    });

    // ----------------------- comisiones form ----------------------- //

    $("#comisionesSearchForm").submit(function (e){

        e.preventDefault();

        getComisionesScreen();

    });

    // Balance "Todos" cuentas select
    $("#cuenta_all").change( function(){
        $("select[name='cuenta']").attr("disabled", $(this).is(':checked')); 
    });    

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

    // ----------------------- tables ----------------------- //

    // mis facturas table
    if($('#dataTableMisFacturas').length){
        var obj = $('#dataTableMisFacturas');

        var action = '/my-invoice/list/';
        
        var columns =  [{ data: "id", name: "id" },
                        { data: "periodo", name: "periodo" },
                        { data: function (obj) {
                            return obj.talonario.letra + ' ' + obj.talonario.nro_punto_vta + ' - '+ obj.nro_factura;
                            },
                            name: "nro_factura"
                        },
                        { data: "primer_vto_fecha", name: "primer_vto_fecha" },
                        { data: function (obj) {
                            return '$'+obj.importe_total;
                            }
                        },
                        { data: function (obj) {
                            return obj.fecha_pago == null  ? '<span class="label label-danger">Pendiente</span>' : 'Pagada: ' + obj.fecha_pago;
                            }
                        },                        
                        { data: function (obj) {
                            var detalle = '<a href="/my-invoice/detail/' + obj.id + '" class="btn btn-success btn-xs"><i class="fa fa-folder-open-o"></i> Detalle</a>';
                            var btn_accion = '';
                            if (obj.fecha_pago == null && obj.btn_actualizar == true ) {
                                btn_accion = '<a href="/my-invoice/update/' + obj.id + '" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i> Actualizar</a>';
                            }else{
                                btn_accion = '<a href="/my-invoice/download/' + obj.id + '" target="_blank" class="btn btn-info btn-xs"><i class="fa fa-download"></i> Descargar</a>';
                            }
                            var btn_pago = '';
                            var btn_informar = '';
                            if (obj.fecha_pago == null) {
                                btn_pago = '<a href="/my-invoice/pay/' + obj.id + '" class="btn btn-primary btn-xs"><i class="fa fa-credit-card"></i> Pagar con Mercado Pago</a>';
                                btn_informar = '<a href="/my-invoice/inform-payment/' + obj.id + '" class="btn btn-info btn-xs"><i class="fa fa-bank"></i> Informar Pago</a>';
                            }
                            return detalle + btn_accion + btn_pago + btn_informar;
                            }
                        }
                        ];

        var columnDefs = [ { orderable: false, "targets": -1 },          // last column
                           { className: "dt-center", "targets": [1, 2, 3, 5]},
                           { className: "dt-right", "targets": [4] },
                           { visible: false, 'targets': [0] }

                         ];

        var initSwitchery = false;
        var urlSwitchery = '/';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'desc'
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense);

    }

    // reclamos table
    if($('#dataTableMisReclamos').length){
        
        var obj = $('#dataTableMisReclamos');

        var action = '/my-claims/list';
        
        var columns =  [{ data: function (obj) {

                            data  = obj.id
                            style = obj.leido_client == 0 ? 'highlight' : '';

                            return '<span class="'+style+'">'+data+'</span>';
                            },
                            name: "id"
                        },
                        { data: function (obj) {

                            data  = obj.titulo
                            style = obj.leido_client == 0 ? 'highlight' : '';

                            return '<span class="'+style+'">'+data+'</span>';
                            },
                            name: "titulo"
                        },
                        { data: function (obj) {

                            data  = obj.fecha;
                            style = obj.leido_client == 0 ? 'highlight' : '';

                            return '<span class="'+style+'">'+data+'</span>';
                            },
                            name: "created_at"
                        },
                        { data: function (obj) {

                            data        = obj.status == 0 ? 'Abierto' : 'Cerrado';
                            styleStatus = obj.status == 0 ? 'label-success ' : 'label-default ';

                            style = obj.leido_client == 0 ? 'highlight' : '';
                            return '<span class="label '+styleStatus + style+'">'+data+'</span>';
                            },
                            name: "status"
                        },
                        { data: function (obj) {
                            return '<a href="/my-claims/reply/' + obj.id + '" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> Ver</a>';
                            }
                        }];

        var columnDefs = [ { orderable: false, "targets": -1 },          // last column
                           {className: "dt-center", "targets": [0, 3, -1]},
                           { className: "dt-right", "targets": [] }
                         ];

        var initSwitchery = false;
        // var urlSwitchery = '/admin/services';
        var disableOthers = false;
        var drawMap = false;
        var orderColumn = 0;
        var orderSense = 'desc'
        // configure table
        fillTable(obj, action, columns, columnDefs, initSwitchery, urlSwitchery, disableOthers, drawMap, orderColumn, orderSense);

    }

});

// function to render table
function fillTable(obj, action,columns, columnDefs, initSwitchery = false, urlSwitchery, disableOthers, drawMap = false, orderColumn, orderSense) {

    // $.fn.dataTable.moment( 'DD/MM/YYYY' );

    obj.DataTable({
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
                    var elems = document.querySelectorAll('.js-switch');

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
            elementList.attr('placeholder', 'No existen Cohermanos');
        
        }
     });

 }

function getReclamosNoLeidos() {

        // ajax callback
        var response;
        $.ajax({
            url: "/my-claims/listUnread",
            type: "GET",
            processData: false,                             // tell jQuery not to process the data
            contentType: false                              // tell jQuery not to set contentType
        }).done(function(data) {

            var content = '';
            
            if (data.length > 0) {
                
                $('.msg_number').html(data.length);
                
                $.each( data, function( key, value) {

                    content +='<li>'+
                                '<a href="/my-claims/reply/'+value.id+'">' +
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
                                '<a href="/my-claims">'+
                                    '<strong>Ver mis reclamos </strong>'+
                                    '<i class="fa fa-angle-right"></i>'+
                                '</a>'+
                            '</div>'+
                        '</li>';

            $('.msg_list').html(content);

            // console.log(data);

        });    

}