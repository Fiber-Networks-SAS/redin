    
        <!-- Modal -->
        <div class="modal fade" id="modalDivisasISO" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content"><!-- modal content --></div>
            </div>
        </div>
        <!-- /.modal -->
    
        <!-- footer content -->
        <footer>
          <div class="pull-right">
            <!-- Gentelella - Bootstrap Admin Template by <a href="https://colorlib.com">Colorlib</a> -->

            <i class="{{ config('constants.icon') }} "></i> {{ config('constants.copy') }}

          </div>
          <div class="clearfix"></div>
        </footer>
        <!-- /footer content -->
      </div>
    </div>


    <!-- jQuery -->
    <script src="/vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- FastClick -->
    <script src="/vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="/vendors/nprogress/nprogress.js"></script>
    <!-- iCheck -->
    <script src="/vendors/iCheck/icheck.min.js"></script>    
    <!-- jQuery custom content scroller -->
    <script src="/vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>

    <!-- Datatables -->
    <script src="/vendors/datatables.net/js/jquery.dataTables.min.js"></script>
    <!-- <script src="/vendors/datatables.net/js/moment.min.js"></script> -->
    <!-- <script src="/vendors/datatables.net/js/datetime-moment.js"></script> -->
    <script src="/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="/vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="/vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
    <script src="/vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
    <script src="/vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="/vendors/datatables.net-buttons/js/buttons.print.min.js"></script>
    <!-- <script src="/vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script> -->
    <!-- <script src="/vendors/datatables.net-keytable/js/dataTables.keyTable.min.js"></script> -->
    <script src="/vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    
    <!-- <script src="/vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script> -->
    <!-- <script src="/vendors/datatables.net-scroller/js/datatables.scroller.min.js"></script> -->
    <!-- <script src="/vendors/jszip/dist/jszip.min.js"></script> -->
    <!-- <script src="/vendors/pdfmake/build/pdfmake.min.js"></script> -->
    <!-- <script src="/vendors/pdfmake/build/vfs_fonts.js"></script>     -->
    
    <!-- Switchery -->
    <script src="/vendors/switchery/dist/switchery.min.js"></script>    

    <!-- jquery.inputmask -->
    <script src="/vendors/jquery.inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>

    <!-- jQuery autocomplete -->
    <script src="/vendors/devbridge-autocomplete/dist/jquery.autocomplete.min.js"></script>

    <!-- jQuery number -->
    <script src="/vendors/jquery-number-master/jquery.number.min.js"></script>

    <!-- Custom Theme Scripts -->
    <script src="/_admin/build/js/custom.min.js"></script>

    <!-- Google Maps -->
    <!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAtqWsq5Ai3GYv6dSa6311tZiYKlbYT4mw&callback=initMap" defer></script> -->
    
    <!-- Main Script -->
    <script src="/_admin/js/script.js"></script>

    <script>console.log('layout_admin footer loaded');</script>
    @stack('scripts')
    <script>
      try {
        if (!window.jQuery) {
          console.warn('jQuery not found at footer fallback');
        } else {
          if (typeof window.aprobarPago === 'undefined' || typeof window.rechazarPago === 'undefined') {
            console.warn('admin.payments.informed functions not present; adding fallback handlers');
            (function($){
              $(document).on('click', '.btn-aprobar-pago', function(e){
                var id = $(this).data('pago-id') || $(this).attr('data-pago-id');
                console.warn('fallback aprobrar click id=', id);
                if (window.aprobarPago) window.aprobarPago(id);
              });
              $(document).on('click', '.btn-rechazar-pago', function(e){
                var id = $(this).data('pago-id') || $(this).attr('data-pago-id');
                console.warn('fallback rechazar click id=', id);
                if (window.rechazarPago) window.rechazarPago(id);
              });
            })(window.jQuery);
            // Also try to dynamically load the external script if it wasn't loaded
            try {
              var dynamicScriptUrl = "<?php echo e(asset('_admin/js/admin.payments.informed.js')); ?>" + '?_=' + (new Date()).getTime();
              console.warn('attempting to dynamically load: ', dynamicScriptUrl);
              if (typeof $.getScript === 'function') {
                $.getScript(dynamicScriptUrl)
                  .done(function() {
                    console.log('getScript: loaded admin.payments.informed.js dynamically');
                  })
                  .fail(function() {
                    console.error('getScript: failed to load admin.payments.informed.js dynamically');
                    // Try direct append of script tag
                    var s = document.createElement('script');
                    s.src = dynamicScriptUrl;
                    s.async = true;
                    s.onload = function() { console.log('dynamic appended script loaded'); };
                    s.onerror = function() { console.error('dynamic appended script failed'); };
                    document.head.appendChild(s);
                  });
              } else {
                console.warn('$.getScript is not available, appending script tag directly');
                var s = document.createElement('script');
                s.src = dynamicScriptUrl;
                s.async = true;
                s.onload = function() { console.log('appended script loaded'); };
                s.onerror = function() { console.error('appended script failed'); };
                document.head.appendChild(s);
              }
            } catch(err) {
              console.error('getScript threw', err);
            }
          }
        }
      } catch(err) {
        console.error('Error in footer fallback script', err);
      }
    </script>

  </body>
</html>