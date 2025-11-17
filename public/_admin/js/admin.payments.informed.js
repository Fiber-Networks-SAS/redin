(function () {
  function ensureJQuery(cb) {
    if (window.jQuery) return cb();
    var s = document.createElement('script');
    s.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
    s.integrity = 'sha256-K+ctZQ+YdQV0Q0r6j6RlmK8tVD1Q6Xo8X9p+Y4E6kLw=';
    s.crossOrigin = 'anonymous';
    s.onload = cb;
    document.head.appendChild(s);
  }

  ensureJQuery(function () {
    var $ = window.jQuery;
    console.log('admin.payments.informed external script loaded');
    $(function () {
      var pagoIdActual = null;

      window.aprobarPago = function(pagoId) {
        console.log('window.aprobarPago called with id=', pagoId);
        pagoIdActual = pagoId;
        $('#observaciones_aprobar').val('');
        $('#modalAprobar').modal('show');
      }

      window.rechazarPago = function(pagoId) {
        console.log('window.rechazarPago called with id=', pagoId);
        pagoIdActual = pagoId;
        $('#observaciones_rechazar').val('');
        $('#modalRechazar').modal('show');
      }

      $('#btnConfirmarAprobar').off('click.confirmAprobar').on('click.confirmAprobar', function() {
        if (pagoIdActual) {
          procesarPago(pagoIdActual, 'approve', $('#observaciones_aprobar').val());
        }
      });

      $('#btnConfirmarRechazar').off('click.confirmRechazar').on('click.confirmRechazar', function() {
        var observaciones = $('#observaciones_rechazar').val().trim();
        if (!observaciones) {
          alert('Debe especificar el motivo del rechazo');
          return;
        }
        if (pagoIdActual) {
          procesarPago(pagoIdActual, 'reject', observaciones);
        }
      });

      // Direct click as fallback
      $('.btn-aprobar-pago').off('click.aprobar').on('click.aprobar', function(e) {
        var id = $(this).attr('data-pago-id');
        console.log('direct aprobrar click, id=', id);
        if (id !== undefined) {
          window.aprobarPago(id);
        }
      });

      // Delegated handler
      $(document).on('click', '.btn-aprobar-pago', function(e) {
        var id = $(this).attr('data-pago-id');
        console.log('delegated aprobrar click, id=', id);
        if (id !== undefined) {
          window.aprobarPago(id);
        }
      });

      // Direct click as fallback
      $('.btn-rechazar-pago').off('click.rechazar').on('click.rechazar', function(e) {
        var id = $(this).attr('data-pago-id');
        console.log('direct rechazar click, id=', id);
        if (id !== undefined) {
          window.rechazarPago(id);
        }
      });

      // Delegated handler
      $(document).on('click', '.btn-rechazar-pago', function(e) {
        var id = $(this).attr('data-pago-id');
        console.log('delegated rechazar click, id=', id);
        if (id !== undefined) {
          window.rechazarPago(id);
        }
      });

      window.procesarPago = function(pagoId, action, observaciones) {
        console.log('procesarPago called', pagoId, action, observaciones);
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        $.post('/admin/payments/informed/' + pagoId + '/' + action, {
          observaciones: observaciones
        })
        .done(function(response) {
          if (response.success) {
            alert(response.success);
            location.reload();
          } else {
            alert('Error: ' + response.error);
          }
        })
        .fail(function(xhr) {
          var error = 'Error desconocido';
          if (xhr.responseJSON && xhr.responseJSON.error) {
            error = xhr.responseJSON.error;
          }
          alert('Error: ' + error);
        })
        .always(function() {
          $('#modalAprobar').modal('hide');
          $('#modalRechazar').modal('hide');
          pagoIdActual = null;
        });
      }

    });
  });
})();
