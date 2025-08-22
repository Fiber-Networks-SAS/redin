<!DOCTYPE html>
<html lang="es">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="shortcut icon" href="/favicon.png">

    <title>{{ config('constants.title') }} | Administración </title>

    <!-- Bootstrap -->
    <link href="/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="/vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- iCheck -->
    <link href="/vendors/iCheck/skins/flat/green.css" rel="stylesheet">    
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
    <link href="/_admin/build/css/custom.min.css" rel="stylesheet">

    <link href="/_admin/css/custom.css" rel="stylesheet">
  </head>

    <body class="nav-md">
        <div class="container body">
          <div class="main_container">
            
            <div class="col-md-3 left_col">
                    <div class="left_col scroll-view">
                        <div class="navbar nav_title" style="border: 0;">
                            <a href="/admin/dashboard" class="site_title">{{ config('constants.title') }}</span></a>
                        </div>

                        <div class="clearfix"></div>

                        <!-- menu profile quick info -->
                        <div class="profile">
                            <div class="profile_pic">
                                <img src="{{ Auth::user()->picture ? '/pictures/' . Auth::user()->picture : '/_admin/images/user_default.png' }}" alt="Picture" class="img-circle profile_img">
                            </div>
                            <div class="profile_info">
                                <span>Bienvenido,</span>
                                <h2>{{ Auth::user()->firstname }}</h2>
                                <input type="hidden" id="current-user" value="{{ Auth::user()->id }}">
                            </div>
                        </div>
                        <!-- /menu profile quick info -->

                        <br />

                        <!-- sidebar menu -->
                        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                        <div class="menu_section">
                            <h3>{{ Auth::user()->roles[0]->display_name }}</h3>
                            <ul class="nav side-menu">
                              
                                <li><a href="/admin/dashboard"><i class="fa fa-home"></i> Escritorio</a></li>

                                <li><a href="/admin/claims"><i class="fa fa-comments"></i>Reclamos</a></li>
                                
                                <li><a href="/admin/services"><i class="fa fa-cubes"></i>Servicios</a></li>

                                <li><a><i class="fa fa-users"></i> Personas <span class="fa fa-chevron-down"></span></a>
                                    <ul class="nav child_menu">
                                      <li><a href="/admin/users">Administradores</a></li>
                                      <li><a href="/admin/staff">Personal</a></li>
                                      <li><a href="/admin/clients">Clientes</a></li>
                                    </ul>
                                </li>

                                <li><a><i class="fa fa-file-text"></i> Facturas <span class="fa fa-chevron-down"></span></a>
                                    <ul class="nav child_menu">
                                      <li><a href="/admin/period"></i>Períodos</a></li>
                                      <li><a href="/admin/bills"></i>Buscar</a></li>
                                      <li><a href="/admin/bills/single"></i>Factura Simple</a></li>
                                    </ul>
                                </li>
                                
                                <li><a href="/admin/cobroexpress"><i class="fa fa-copyright"></i>Cobro Express</a></li>
                                
                                <li><a><i class="fa fa-line-chart"></i> Balance <span class="fa fa-chevron-down"></span></a>
                                  <ul class="nav child_menu">
                                    <li><a href="/admin/balance/general">General</a></li>
                                    <li><a href="/admin/balance/detail">Detalle</a></li>
                                  </ul>
                                </li>

                                <li><a><i class="fa fa-cog"></i> Configuraciones <span class="fa fa-chevron-down"></span></a>
                                  <ul class="nav child_menu">
                                    <li><a href="/admin/config/invoice">Talonarios</a></li>
                                    <li><a href="/admin/config/interests">Intereses</a></li>
                                    <li><a href="/admin/config/dues">Cuotas</a></li>
                                    <li><a href="/admin/config/payments">Plan de Pagos</a></li>
                                  </ul>
                                </li>
                                
                                <li><a><i class="fa fa-wrench"></i> Utilidades <span class="fa fa-chevron-down"></span></a>
                                  <ul class="nav child_menu">
                                    <li><a href="/admin/audit">Auditor&iacute;a</a></li>
                                    <li><a href="/admin/backup">Backup</a></li>
                                  </ul>
                                </li>

                               
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
                        <img src="{{ Auth::user()->picture ? '/pictures/' . Auth::user()->picture : '/_admin/images/user_default.png' }}" alt="">{{ Auth::user()->firstname }}
                        <span class=" fa fa-angle-down"></span>
                      </a>
                      <ul class="dropdown-menu dropdown-usermenu pull-right">
                        <li><a href="/admin/profile"> Perfil</a></li>
                        <!-- <li>
                          <a href="javascript:;">
                            <span class="badge bg-red pull-right">50%</span>
                            <span>Settings</span>
                          </a>
                        </li> -->
                        <!-- <li><a href="javascript:;">Help</a></li> -->
                        <li><a href="/admin/logout"><i class="fa fa-sign-out pull-right"></i> Cerrar Sesion</a></li>
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