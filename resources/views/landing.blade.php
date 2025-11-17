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
        <div class="item active" style="background-image: url({{ isset($homeSettings['slider_bg']) ? $homeSettings['slider_bg']->value : '/_landing/images/slider/1.jpg' }})">
          <div class="caption">
            <h1 class="">{{ isset($homeSettings['slider_title']) ? $homeSettings['slider_title']->value : (isset($contentSections['slider']) ? $contentSections['slider']->title : 'ReDin') }}</h1>
            <p class="animated fadeInRightBig">{{ isset($homeSettings['slider_subtitle']) ? $homeSettings['slider_subtitle']->value : (isset($contentSections['slider']) ? $contentSections['slider']->subtitle : 'Lider en Telecomunicaciones') }}</p>
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
            <img class="img-responsive" src="{{ config('constants.logo') }}" alt="logo">
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
            <h2>{{ isset($homeSettings['services_title']) ? $homeSettings['services_title']->value : (isset($contentSections['services']) ? $contentSections['services']->title : 'Nuestros Servicios') }}</h2>
            <p>{{ isset($homeSettings['services_subtitle']) ? $homeSettings['services_subtitle']->value : (isset($contentSections['services']) ? $contentSections['services']->subtitle : 'Ofrecemos soluciones integrales en telecomunicaciones para satisfacer todas tus necesidades de conectividad.') }}</p>
            <p>{{ isset($homeSettings['services_text']) ? $homeSettings['services_text']->value : (isset($contentSections['services']) ? $contentSections['services']->content : 'Descubre nuestra amplia gama de servicios diseñados para brindarte la mejor experiencia en comunicaciones.') }}</p>
          </div>
        </div> 
      </div>
      <div class="text-center our-services">
        <div class="row">
          @if(isset($servicios) && $servicios->count() > 0)
            @foreach($servicios as $index => $servicio)
              @php
                // Configurar animaciones escalonadas
                $animationClass = ($index < 3) ? 'wow fadeInDown' : 'wow fadeInUp';
                $animationDelay = 300 + ($index % 3) * 150;
              @endphp
              <div class="col-sm-4 {{ $animationClass }}" data-wow-duration="1000ms" data-wow-delay="{{ $animationDelay }}ms">
                <div class="service-icon">
                  <i class="fa {{ $servicio->icono }}"></i>
                </div>
                <div class="service-info">
                  <h3>{{ $servicio->nombre }}</h3>
                  <p>
                    <strong>{{ $servicio->tipo_nombre }}</strong><br>
                    @if($servicio->detalle)
                      {{ $servicio->detalle }}
                    @else
                      Abono mensual: ${{ number_format($servicio->abono_mensual, 2) }}
                      @if($servicio->costo_instalacion)
                        <br>Instalación: ${{ number_format($servicio->costo_instalacion, 2) }}
                      @endif
                    @endif
                  </p>
                </div>
              </div>
              @if(($index + 1) % 3 == 0 && ($index + 1) < $servicios->count())
                </div><div class="row">
              @endif
            @endforeach
          @else
            <!-- Servicios por defecto si no hay en la base de datos -->
            <div class="col-sm-4 wow fadeInDown" data-wow-duration="1000ms" data-wow-delay="300ms">
              <div class="service-icon">
                <i class="fa fa-wifi"></i>
              </div>
              <div class="service-info">
                <h3>Internet</h3>
                <p>Conexión de alta velocidad para tu hogar o empresa</p>
              </div>
            </div>
            <div class="col-sm-4 wow fadeInDown" data-wow-duration="1000ms" data-wow-delay="450ms">
              <div class="service-icon">
                <i class="fa fa-phone"></i>
              </div>
              <div class="service-info">
                <h3>Telefonía</h3>
                <p>Servicio telefónico con cobertura nacional e internacional</p>
              </div>
            </div>
            <div class="col-sm-4 wow fadeInDown" data-wow-duration="1000ms" data-wow-delay="600ms">
              <div class="service-icon">
                <i class="fa fa-desktop"></i>
              </div>
              <div class="service-info">
                <h3>Television Full HD</h3>
                <p>Entretenimiento digital con la mejor calidad</p>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </section><!--/#services-->

  <section id="features" class="parallax">
    <div class="container">
      <div class="row company">
        <div class="col-sm-12">
          <div class="about-info wow fadeInUp" data-wow-duration="1000ms" data-wow-delay="300ms">
            <h2>{{ isset($homeSettings['company_title']) ? $homeSettings['company_title']->value : (isset($contentSections['company']) ? $contentSections['company']->title : 'La Empresa') }}</h2>
            <p class="title">{{ isset($homeSettings['company_subtitle']) ? $homeSettings['company_subtitle']->value : (isset($contentSections['company']) ? $contentSections['company']->subtitle : 'Conectamos tu mundo con la mejor experiencia de Internet') }}</p>
            {!! isset($homeSettings['company_text']) ? nl2br(e($homeSettings['company_text']->value)) : (isset($contentSections['company']) ? nl2br(e($contentSections['company']->content)) : '<p>En nuestra empresa, transformamos la manera en que las personas se conectan ofreciendo Internet simétrico por fibra óptica, la tecnología más avanzada en conectividad. Gracias a ello, disfrutás de velocidades iguales de subida y bajada, lo que te permite navegar, trabajar, estudiar, jugar en línea o transmitir contenido sin límites ni interrupciones.</p><p>La fibra óptica garantiza estabilidad, rapidez y confiabilidad en todo momento, incluso en los momentos de mayor demanda. Además, entendemos que cada cliente es único, por eso ofrecemos soluciones personalizadas, adaptándonos a tus necesidades específicas sin importar el plan que elijas.</p><p>Nuestro compromiso es brindarte un servicio de alta calidad, con un equipo siempre disponible para acompañarte y una atención cercana que te hace sentir respaldado en cada conexión. Apostamos a la innovación tecnológica para estar siempre un paso adelante, asegurando que tu experiencia digital sea la mejor.</p>') !!}
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
          <h2>{{ isset($homeSettings['internet_title']) ? $homeSettings['internet_title']->value : (isset($contentSections['internet']) ? $contentSections['internet']->title : 'Internet') }}</h2>
          <p style="text-align: center; font-size: large; font-style: italic;"><strong>{{ isset($homeSettings['internet_subtitle']) ? $homeSettings['internet_subtitle']->value : (isset($contentSections['internet']) ? $contentSections['internet']->subtitle : 'Internet simétrico por fibra óptica, pensado para vos') }}</strong></p>
          {!! isset($homeSettings['internet_text']) ? nl2br(e($homeSettings['internet_text']->value)) : (isset($contentSections['internet']) ? nl2br(e($contentSections['internet']->content)) : '<p>Disfrutá de una conexión rápida, estable y confiable, con la misma velocidad de subida y bajada para que trabajes, estudies o juegues sin límites.</p><p>Nos adaptamos a tus necesidades con soluciones personalizadas, sin importar el plan contratado.</p><p>Con compromiso, innovación y atención cercana, te ofrecemos una experiencia de Internet superior.</p>') !!}
        </div>
      </div>
      <div class="pricing-table">
        <div class="row">
          @if(isset($serviciosInternet) && $serviciosInternet->count() > 0)
            @foreach($serviciosInternet as $index => $servicioInternet)
              @php
                // Determinar si es el plan destacado (el del medio si hay 3, o el segundo si hay más)
                $totalServicios = $serviciosInternet->count();
                $esPlanDestacado = false;
                if ($totalServicios == 3 && $index == 1) {
                  $esPlanDestacado = true;
                } elseif ($totalServicios > 3 && $index == 1) {
                  $esPlanDestacado = true;
                }
                
                // Configurar animaciones escalonadas
                $animationDelay = 300 + ($index * 200);
                $colClass = $totalServicios <= 3 ? 'col-sm-4' : 'col-md-3 col-sm-6';
              @endphp
              
              <div class="{{ $colClass }}">
                <div class="single-table {{ $esPlanDestacado ? 'featured' : '' }} wow flipInY" data-wow-duration="1000ms" data-wow-delay="{{ $animationDelay }}ms">
                  <h3>{{ $servicioInternet->nombre }}</h3>
                  <div class="price">
                    ${{ number_format($servicioInternet->abono_mensual, 0) }}<span>/Mes</span>
                  </div>
                  <ul>
                    @if($servicioInternet->detalle)
                      @foreach(explode("\n", $servicioInternet->detalle) as $linea)
                        @if(trim($linea))
                          <li>{{ trim($linea) }}</li>
                        @endif
                      @endforeach
                    @else
                      <li>Servicio de Internet</li>
                      @if($servicioInternet->costo_instalacion)
                        <li>Instalación: ${{ number_format($servicioInternet->costo_instalacion, 0) }}</li>
                      @endif
                    @endif
                  </ul>
                  <a href="#contact" class="btn btn-lg btn-primary">Consultar</a>
                </div>
              </div>
              
              @if($totalServicios > 3 && ($index + 1) % 4 == 0 && ($index + 1) < $totalServicios)
                </div><div class="row">
              @endif
            @endforeach
          @else
            <!-- Planes por defecto si no hay servicios de Internet en la base de datos -->
            <div class="col-sm-4">
              <div class="single-table wow flipInY" data-wow-duration="1000ms" data-wow-delay="300ms">
                <h3>Básico</h3>
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
          @endif
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
            <h2>{{ isset($homeSettings['contact_title']) ? $homeSettings['contact_title']->value : (isset($contentSections['contact']) ? $contentSections['contact']->title : 'Contacto') }}</h2>
            <p>{{ isset($homeSettings['contact_text']) ? $homeSettings['contact_text']->value : (isset($contentSections['contact']) ? $contentSections['contact']->content : '¿Tienes alguna pregunta o necesitas más información sobre nuestros servicios? Completa el formulario a continuación y nos pondremos en contacto contigo lo antes posible.') }}</p>
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
                <p>Contáctanos</p>
                <br>
                <ul class="address">
                  <li><i class="fa fa-whatsapp"></i> +54 9 376 5525956 </li>
                  <!-- <li><i class="fa fa-whatsapp"></i> +54 3765 125439 </li> -->
                  <li><i class="fa fa-envelope"></i> <a href="mailto:administracion@redin.com.ar">administracion@redin.com.ar</a></li>
                  <li><i class="fa fa-globe"></i> <a href="http://www.redin.com.ar">www.redin.com.ar</a></li>
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
            <li><a class="envelope" href="mailto:administracion@redin.com.ar"><i class="fa fa-envelope"></i></a></li>
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