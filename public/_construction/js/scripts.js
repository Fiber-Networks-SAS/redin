jQuery(function ($) {

    'use strict';

    // -------------------------------------------------------------
    // Countdown
    // -------------------------------------------------------------
    (function () {
        /*
        $("#back-countdiown").countdown({
            date: "30 august 2020 12:00:00",
            format: "on"
        });
        */
    }()); 





    // Contact form
    var form = $('#main-contact-form');
    form.submit(function(event){
        event.preventDefault();
        var form_status = $('<div class="form_status"></div>');
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: form.serialize(),
            beforeSend: function(){
                form.prepend( form_status.html('<p><i class="fa fa-spinner fa-spin"></i> Enviando su consulta...</p>').fadeIn() );
            }
        }).done(function(data){
            
            // clear form
            form.get(0).reset();

            var msg = '<div class="alert alert-success" role="alert">' +
                        '<strong>Gracias por contactarse con nosotros!</strong> Nos pondremos en contacto a la brevedad.' +
                      '</div>';

            form_status.html(msg).delay(3000).fadeOut();

        });
    });


}); // JQuery end


$(document).on('click', '.m-menu .dropdown-menu', function(e) {
  e.stopPropagation()
})