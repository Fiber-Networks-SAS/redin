<!DOCTYPE html>
<html lang="es">
<head>
  
  <title>{{ config('constants.title') }}</title>
  
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  
  <meta name="description" content="">
  <meta name="author" content="{{ config('constants.author') }}">
  
      <!-- Bootstrap -->
    <link href="/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- web-fonts -->
  <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
  <!-- font-awesome -->
  <link href="/_construction/fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet">
  <!-- Style CSS -->
  <link href="/_construction/css/style.css" rel="stylesheet">

  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
      <![endif]-->

  <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
  
  <link rel="shortcut icon" href="favicon.png">

</head><!--/head-->

  <body>
    <section class="wraper">
      <header class="header">
        <h1>QUALITY TEL NETWORKS</h1>
        <h2>Estamos renovando el sitio, muy pronto podrás acceder.</h2>
        <h3><i class="fa fa-check"></i> Si ya sos cliente podrás acceder al sistema de autogestión.</h3>
        <h3><i class="fa fa-check"></i> Si aún no sos cliente podrás conocer mejor nuestros servicios.</h3>
      </header>
      <!-- .header -->


      <!-- <section class="countdown-wrapper">
        <ul id="back-countdiown">
          <li>                    
            <span class="hours">00</span>
            <p>Falta muy poco...</p>
          </li>
          <li>
            <span class="hours">00</span>
            <p>horas</p>
          </li>
          <li>
            <span class="minutes">00</span>
            <p>minutes</p>
          </li>
          <li>
            <span class="seconds">00</span>
            <p>seconds</p>
          </li>               
        </ul>
      </section> --><!-- .countdown-wrapper -->

       <section class="subscribe">
        <!-- <form class="subscribe-form" role="form" method="">
          <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Correo Electrónico">
          <input type="submit" value="Avísenme" class="form-control">
        </form> -->

        <h3>Envianos tu consulta</h3>
        <form id="main-contact-form" name="contact-form" method="post" action="/contact">
          
          {{ csrf_field() }}
          
          <div class="row  wow fadeInUp" data-wow-duration="1000ms" data-wow-delay="300ms">
            <div class="col-sm-6">
              <div class="form-group">
                <input type="text" name="name" class="form-control" placeholder="Nombre *" required="required">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
            <input type="text" name="phone" class="form-control" placeholder="Teléfono *" required="required">
              </div>
            </div>
          </div>
          <div class="form-group">
                <input type="email" name="email" class="form-control" placeholder="Correo electrónico">
          </div>
          <div class="form-group">
            <textarea name="message" id="message" class="form-control" rows="4" placeholder="Mensaje *" required="required"></textarea>
          </div>                        
          <div class="form-group">
            <button type="submit" class="btn-submit">Enviar</button>
          </div>
        </form> 

      </section>

      <footer class="footer">

        <section class="social-links">

          <ul class="">
            <li><i class="fa fa-map-marker"></i> San Martin 439 Piso 12 C - C.A.B.A. <i class="fa fa-phone"></i> +54 011 60913728 </li>
            <li><i class="fa fa-globe"></i> <a href="http://www.qtnmisiones.com">www.qtnmisiones.com</a>&nbsp<i class="fa fa-envelope"></i> <a href="mailto:adm.qtn@gmail.com">adm.qtn@gmail.com</a></li>
          </ul>

        </section><!-- /.social-links -->

        <ul class="copyright">
          <li>Quality Tel Networks - 2018</li>
        </ul>

      </footer>
    </section>


    <div class="fullscreen-bg">
      <video loop muted autoplay poster="/_construction/img/videoframe.jpg" class="fullscreen-bg__video">
        <source src="/_construction/img/video-bg.mp4" type="video/mp4">
      </video>
    </div> <!-- .fullscreen-bg -->

    <!-- Script -->
    <script src="/_construction/js/jquery-2.1.4.min.js"></script>
    <!-- <script src="/_construction/js/coundown-timer.js"></script> -->
    <script src="/_construction/js/scripts.js"></script>

    </body>
</html>