<!DOCTYPE html>
<html lang="es">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('constants.title') }} | Clientes</title>
    
    <link rel="shortcut icon" href="/favicon.png">

    <!-- Bootstrap -->
    <link href="/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="/vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- jQuery custom content scroller -->
    <link href="/vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet"/>

    <!-- Datatables -->
    <link href="/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <!-- <link href="/vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css" rel="stylesheet"> -->
    <link href="/vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
    <!-- <link href="/vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css" rel="stylesheet"> -->

    <!-- Switchery -->
    <link href="/vendors/switchery/dist/switchery.min.css" rel="stylesheet">    

    <!-- Custom Theme Style -->
    <link href="/_clients/build/css/custom.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:700i" rel="stylesheet">

    <link href="/_clients/css/custom.css" rel="stylesheet">
  </head>

    <body class="nav-md">
        <div class="container body">
          <div class="main_container">
            
            <div class="col-md-3 left_col">
                    <div class="left_col scroll-view">
                        <div class="navbar nav_title">
                            <a href="/dashboard" class="site_title"><img src="{{ config('constants.logo') }}" class="logo-dashboard" alt="{{ config('constants.title') }}">{{ config('constants.title') }}</a>
                        </div>

                        <div class="clearfix"></div>

                        <!-- menu profile quick info -->
                        <div class="profile">
                            <div class="profile_pic">
                                <img src="{{ Auth::user()->picture ? '/pictures/' . Auth::user()->picture : '/_clients/images/user_default.png' }}" alt="Picture" class="img-circle profile_img">
                            </div>
                            <div class="profile_info">
                                <!-- <span>Bienvenido,</span> -->
                                <!-- <h2>{{ Auth::user()->firstname }}</h2> -->
                                <input type="hidden" id="current-user" value="{{ Auth::user()->id }}">
                            </div>
                        </div>
                        <!-- /menu profile quick info -->

                        <br />

                        <!-- sidebar menu -->
                        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                        <div class="menu_section">
                        <h3>{{ Auth::user()->firstname }}</h3>
                            <ul class="nav side-menu">
                              
                                <li><a href="/dashboard">Escritorio <i class="fa fa-home pull-right"></i></a></li>
                                
                                <li><a href="/profile">Perfil <i class="fa fa-user pull-right"></i></a></li>
                                
                                
                                  <li><a href="/my-invoice">Mis Facturas <i class="fa fa-file-text pull-right"></i></a></li>
                                  
                                  <li><a href="/my-claims">Mis Reclamos <i class="fa fa-comments pull-right"></i></a></li>
                                
                                <!-- <li><a href="/logout">Cerrar Sesion <i class="fa fa-sign-out pull-right"></i></a></li> -->



                            </ul>
                        </div>

                    </div>
                    <!-- /sidebar menu -->

                    <!-- /menu footer buttons -->
                    <!-- <div class="sidebar-footer hidden-small">
                      <a data-toggle="tooltip" data-placement="top" title="Settings">
                        <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
                      </a>
                      <a data-toggle="tooltip" data-placement="top" title="FullScreen">
                        <span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
                      </a>
                      <a data-toggle="tooltip" data-placement="top" title="Lock">
                        <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
                      </a>
                      <a data-toggle="tooltip" data-placement="top" title="Salir">
                        <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
                      </a>
                    </div> -->
                    <!-- /menu footer buttons -->
              </div>
            </div>

            <!-- top navigation -->
            <div class="top_nav">
              <div class="nav_menu">
                <nav>
                  <div class="nav toggle">
                    <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                  </div>

                  <ul class="nav navbar-nav navbar-right">
                    <li class="">
                      <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <img src="{{ Auth::user()->picture ? '/pictures/' . Auth::user()->picture : '/_clients//images/user_default.png' }}" alt="">{{ Auth::user()->firstname }}
                        <span class=" fa fa-angle-down"></span>
                      </a>
                      <ul class="dropdown-menu dropdown-usermenu pull-right">
                        <li><a href="/profile"> Perfil</a></li>
                        <!-- <li>
                          <a href="javascript:;">
                            <span class="badge bg-red pull-right">50%</span>
                            <span>Settings</span>
                          </a>
                        </li> -->
                        <!-- <li><a href="javascript:;">Help</a></li> -->
                        <li><a href="/logout"><i class="fa fa-sign-out pull-right"></i> Cerrar Sesion</a></li>
                      </ul>
                    </li>

                    <li role="presentation" class="dropdown msg_conainer">
                      <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-envelope-o"></i>
                        <span class="badge bg-green msg_number"></span>
                      </a>
                      <ul id="menu1" class="dropdown-menu list-unstyled msg_list" role="menu"></ul>
                    </li>

                  </ul>
                </nav>
              </div>
            </div>
            <!-- /top navigation -->  