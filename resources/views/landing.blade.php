<!DOCTYPE html>
<html lang="es">
<head>
  
  <title>{{ config('constants.title') }}</title>
  
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="{{ config('constants.author') }}">
  
  
  <link href="/_landing/css/bootstrap.min.css" rel="stylesheet">
  <link href="/_landing/css/animate.min.css" rel="stylesheet"> 
  <link href="/_landing/css/font-awesome.min.css" rel="stylesheet">
  <link href="/_landing/css/lightbox.css" rel="stylesheet">
  <link href="/_landing/css/main.css" rel="stylesheet">
  <link id="css-preset" href="/_landing/css/presets/preset1.css" rel="stylesheet">
  <link href="/_landing/css/responsive.css" rel="stylesheet">

  <!--[if lt IE 9]>
    <script src="/_landing/js/html5shiv.js"></script>
    <script src="/_landing/js/respond.min.js"></script>
  <![endif]-->
  
  <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
  
  <link rel="shortcut icon" href="favicon.png">

</head><!--/head-->

<body>

  <!--.preloader-->
  <div class="preloader"> <i class="fa fa-circle-o-notch fa-spin"></i></div>
  <!--/.preloader-->

  <header id="home">
    <div id="home-slider" class="carousel slide carousel-fade" data-ride="carousel">
      <div class="carousel-inner">
        <div class="item active" style="background-image: url(/_landing/images/slider/1.jpg)">
          <div class="caption">
            <!-- <h1 class="animated fadeInLeftBig"><img src="images/logo2.png" alt=""> <span>Quality Tel Networks</span></h1> -->
            <h1 class="">Quality Tel Networks</h1>
            <!-- <h1 class="animated fadeInLeftBig"> Quality Tel Networks</h1> -->
            <!-- <p class="animated fadeInRightBig">Quality Tel Networks</p> -->
            <p class="animated fadeInRightBig">Lider en Telecomunicaciones</p>
            <a data-scroll class="btn btn-start animated fadeInUpBig" href="#services">Nuestros servicios</a>
          </div>
        </div>
<!--         <div class="item" style="background-image: url(images/slider/2.jpg)">
          <div class="caption">
            <h1 class="animated fadeInLeftBig">Say Hello to <span>QTN</span></h1>
            <p class="animated fadeInRightBig">Bootstrap - Responsive Design - Retina Ready - Parallax</p>
            <a data-scroll class="btn btn-start animated fadeInUpBig" href="#services">Start now</a>
          </div>
        </div>
        <div class="item" style="background-image: url(images/slider/3.jpg)">
          <div class="caption">
            <h1 class="animated fadeInLeftBig">We are <span>Creative</span></h1>
            <p class="animated fadeInRightBig">Bootstrap - Responsive Design - Retina Ready - Parallax</p>
            <a data-scroll class="btn btn-start animated fadeInUpBig" href="#services">Start now</a>
          </div>
        </div> -->
      </div>
      <!-- <a class="left-control" href="#home-slider" data-slide="prev"><i class="fa fa-angle-left"></i></a> -->
      <!-- <a class="right-control" href="#home-slider" data-slide="next"><i class="fa fa-angle-right"></i></a> -->

      <a id="tohash" href="#services"><i class="fa fa-angle-down"></i></a>

    </div><!--/#home-slider-->
    <div class="main-nav">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#home">
            <h1><img class="img-responsive" src="{{ config('constants.logo') }}" alt="logo">QTN</h1>
          </a>                    
        </div>
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav navbar-right">                 
            <li class="scroll active"><a href="#home">Inicio</a></li>
            <li class="scroll"><a href="#services">Servicios</a></li> 
            <li class="scroll"><a href="#features">La Empresa</a></li>                     
            <li class="scroll"><a href="#pricing">Internet</a></li>
            <li class="scroll"><a href="#contact">Contacto</a></li>       
            <li class="nav-clients"><a href="/login">Clientes</a></li>       
          </ul>
        </div>
      </div>
    </div><!--/#main-nav-->
  </header><!--/#home-->
  <section id="services">
    <div class="container">
      <div class="heading wow fadeInUp" data-wow-duration="1000ms" data-wow-delay="300ms">
        <div class="row">
          <div class="text-center col-sm-8 col-sm-offset-2">
            <h2>Servicios</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore </p>
            <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. </p>
          </div>
        </div> 
      </div>
      <div class="text-center our-services">
        <div class="row">
          <div class="col-sm-4 wow fadeInDown" data-wow-duration="1000ms" data-wow-delay="300ms">
            <div class="service-icon">
              <i class="fa fa-cloud"></i>
            </div>
            <div class="service-info">
              <h3>Servicio 1</h3>
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore</p>
            </div>
          </div>
          <div class="col-sm-4 wow fadeInDown" data-wow-duration="1000ms" data-wow-delay="450ms">
            <div class="service-icon">
              <i class="fa fa-wifi"></i>
            </div>
            <div class="service-info">
              <h3>Servicio 2</h3>
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore</p>
            </div>
          </div>
          <div class="col-sm-4 wow fadeInDown" data-wow-duration="1000ms" data-wow-delay="550ms">
            <div class="service-icon">
              <i class="fa fa-phone"></i>
            </div>
            <div class="service-info">
              <h3>Servicio 3</h3>
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore</p>
            </div>
          </div>
          <div class="col-sm-4 wow fadeInUp" data-wow-duration="1000ms" data-wow-delay="650ms">
            <div class="service-icon">
              <i class="fa fa-send-o"></i>
            </div>
            <div class="service-info">
              <h3>Servicio 4</h3>
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore</p>
            </div>
          </div>
          <div class="col-sm-4 wow fadeInUp" data-wow-duration="1000ms" data-wow-delay="750ms">
            <div class="service-icon">
              <i class="fa fa-tachometer"></i>
            </div>
            <div class="service-info">
              <h3>Servicio 5</h3>
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore</p>
            </div>
          </div>
          <div class="col-sm-4 wow fadeInUp" data-wow-duration="1000ms" data-wow-delay="850ms">
            <div class="service-icon">
              <i class="fa fa-cloud-upload"></i>
            </div>
            <div class="service-info">
              <h3>Servicio 6</h3>
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section><!--/#services-->

  <section id="features" class="parallax">
    <div class="container">
      <div class="row company">
        <div class="col-sm-12">
          <div class="about-info wow fadeInUp" data-wow-duration="1000ms" data-wow-delay="300ms">
            <h2>La Empresa</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.Ullamco laboris nisi ut aliquip ex ea commodo consequat. </p>
            <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Sed ut perspiciatis unde omnis iste. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
          </div>
        </div>
      </div>

      <div class="row count">
        <div class="col-sm-3 col-xs-6 wow fadeInLeft" data-wow-duration="1000ms" data-wow-delay="300ms">
          <i class="fa fa-users"></i>
          <h3 class="timer">4000</h3>
          <p>Clientes</p>
        </div>
        <div class="col-sm-3 col-xs-6 wow fadeInLeft" data-wow-duration="1000ms" data-wow-delay="500ms">
          <i class="fa fa-desktop"></i>
          <h3 class="timer">200</h3>                    
          <p>Instalaciones mensuales</p>
        </div> 
        <div class="col-sm-3 col-xs-6 wow fadeInLeft" data-wow-duration="1000ms" data-wow-delay="700ms">
          <i class="fa fa-trophy"></i>
          <h3 class="timer">10</h3>                    
          <p>Certificaciones</p>
        </div> 
        <div class="col-sm-3 col-xs-6 wow fadeInLeft" data-wow-duration="1000ms" data-wow-delay="900ms">
          <i class="fa fa-comment-o"></i>                    
          <h3>24/7</h3>
          <p>Soporte</p>
        </div>                 
      </div>
    </div>
  </section><!--/#features-->

  <section id="pricing">
    <div class="container">
      <div class="row">
        <div class="heading text-center col-sm-8 col-sm-offset-2 wow fadeInUp" data-wow-duration="1200ms" data-wow-delay="300ms">
          <h2>Internet</h2>
          <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua ut enim ad minim veniam</p>
        </div>
      </div>
      <div class="pricing-table">
        <div class="row">
          <div class="col-sm-4">
            <div class="single-table wow flipInY" data-wow-duration="1000ms" data-wow-delay="300ms">
              <h3>Live</h3>
              <div class="price">
                $500<span>/Mes</span>                          
              </div>
              <ul>
                <li>6 Megas</li>
                <li>1 Casilla de Correo</li>
                <li>Contratación mínima 6 meses</li>
              </ul>
              <a href="#contact" class="btn btn-lg btn-primary">Consultar</a>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="single-table featured wow flipInY" data-wow-duration="1000ms" data-wow-delay="500ms">
              <h3>Premium</h3>
              <div class="price">
                $700<span>/Mes</span>                                
              </div>
              <ul>
                <li>10 Megas</li>
                <li>5 Casillas de Correo</li>
                <li>Contratación mínima 6 meses</li>
              </ul>
              <a href="#contact" class="btn btn-lg btn-primary">Consultar</a>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="single-table wow flipInY" data-wow-duration="1000ms" data-wow-delay="800ms">
              <h3>Ultra</h3>
              <div class="price">
                $900<span>/Mes</span>                                
              </div>
              <ul>
                <li>20 Megas</li>
                <li>10 Casillas de Correo</li>
                <li>Contratación mínima 6 meses</li>
              </ul>
              <a href="#contact" class="btn btn-lg btn-primary">Consultar</a>
            </div>
          </div>
            <!-- <div class="col-sm-3">
            <div class="single-table wow flipInY" data-wow-duration="1000ms" data-wow-delay="1100ms">
              <h3>Professional</h3>
              <div class="price">
                $49<span>/Month</span>                    
              </div>
              <ul>
                <li>Free Setup</li>
                <li>10GB Storage</li>
                <li>100GB Bandwith</li>
                <li>5 Products</li>
              </ul>
              <a href="#" class="btn btn-lg btn-primary">Sign up</a>
            </div>
          </div> -->
        </div>
      </div>
    </div>
  </section><!--/#pricing-->


  <section>
    <div id="google-map" class="wow fadeIn" data-latitude="-27.410174" data-longitude="-55.979776" data-wow-duration="1000ms" data-wow-delay="400ms"></div>
    <div id="contact-us" class="parallax">
      <div class="container" id="contact">
        <div class="row">
          <div class="heading text-center col-sm-8 col-sm-offset-2 wow fadeInUp" data-wow-duration="1000ms" data-wow-delay="300ms">
            <h2>Contacto</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua ut enim ad minim veniam</p>
          </div>
        </div>
        <div class="contact-form wow fadeIn" data-wow-duration="1000ms" data-wow-delay="600ms">
          <div class="row">
            <div class="col-sm-6">
              
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

            </div>
            <div class="col-sm-6">
              <div class="contact-info wow fadeInUp" data-wow-duration="1000ms" data-wow-delay="300ms">
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.</p>
                <br>
                <ul class="address">
                  <li><i class="fa fa-map-marker"></i> San Martin 439 Piso 12 C - CABA  </li>
                  <li><i class="fa fa-phone"></i> +54 011 60913728 </li>
                  <!-- <li><i class="fa fa-whatsapp"></i> +54 3765 125439 </li> -->
                  <li><i class="fa fa-envelope"></i> <a href="mailto:adm.qtn@gmail.com">adm.qtn@gmail.com</a></li>
                  <li><i class="fa fa-globe"></i> <a href="http://www.qtnmisiones.com">www.qtnmisiones.com</a></li>
                </ul>
              </div>                            
            </div>
          </div>
        </div>
      </div>
    </div>        
  </section><!--/#contact-->

  <footer id="footer">
    <div class="footer-top wow fadeInUp" data-wow-duration="1000ms" data-wow-delay="300ms">
      <div class="container text-center">
        <div class="footer-logo">
          <a href="#home"><img class="img-responsive" src="{{ config('constants.logo') }}" alt=""></a> {{ config('constants.copy') }}
        </div>
        <div class="social-icons">
          <ul>
            <li><a class="envelope" href="mailto:adm.qtn@gmail.com"><i class="fa fa-envelope"></i></a></li>
            <!-- <li><a class="twitter" href="#"><i class="fa fa-twitter"></i></a></li>  -->
            <li><a class="dribbble" href="#home"><i class="fa fa-dribbble"></i></a></li>
            <!-- <li><a class="facebook" href="#"><i class="fa fa-facebook"></i></a></li> -->
            <!-- <li><a class="linkedin" href="#"><i class="fa fa-linkedin"></i></a></li> -->
            <!-- <li><a class="tumblr" href="#"><i class="fa fa-tumblr-square"></i></a></li> -->
          </ul>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="container">
        <div class="row">
          <div class="col-sm-6">
            <!-- <p>QTN - Quality Tel Networks | 2017</p> -->
          </div>
          <!-- <div class="col-sm-6">
            <p class="pull-right">Crafted by <a href="http://designscrazed.org/">Allie</a></p>
          </div> -->
        </div>
      </div>
    </div>
  </footer>

  <script type="text/javascript" src="/_landing/js/jquery.js"></script>
  <script type="text/javascript" src="/_landing/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true&key=AIzaSyAjP03tsjpyaAKKpu2vy6Ka_xAlqJngSH0"></script>
  <script type="text/javascript" src="/_landing/js/jquery.inview.min.js"></script>
  <script type="text/javascript" src="/_landing/js/wow.min.js"></script>
  <script type="text/javascript" src="/_landing/js/mousescroll.js"></script>
  <script type="text/javascript" src="/_landing/js/smoothscroll.js"></script>
  <script type="text/javascript" src="/_landing/js/jquery.countTo.js"></script>
  <script type="text/javascript" src="/_landing/js/lightbox.min.js"></script>
  <script type="text/javascript" src="/_landing/js/main.js"></script>

</body>
</html>