<!DOCTYPE html>
<html lang="es">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="UTF-8">
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
    <!-- Animate.css -->
    <link href="/vendors/animate.css/animate.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:700i" rel="stylesheet">
    
    <!-- Custom Theme Style -->
    <link href="/_clients/build/css/custom.css" rel="stylesheet">

    <link rel="shortcut icon" href="favicon.png">

  </head>

  <body class="login">
    <div>
      <a class="hiddenanchor" id="signup"></a>
      <a class="hiddenanchor" id="signin"></a>

      <div class="login_wrapper">

        <span class="top-logo">
          <a href="/"><img src="{{ config('constants.logo') }}" class="logo-home" alt="{{ config('constants.title') }}"></a>
        </span>

        <div class="animate form login_form">
          <section class="login_content">

            <form action="login" method="POST">
              
              {{ csrf_field() }}

              <h1>Iniciar Sesion</h1>

              <!-- login error -->
              @if (Session::has('login_errors'))
                  <div class="panel panel-danger">
                      <div class="panel-heading">
                          {{ trans('auth.failed') }}
                      </div>     
                  </div>     
              @endif

              <div>
                <input type="email" class="form-control" placeholder="E-mail" name="email" value="{{ old('email') }}" required="" />
              </div>
              <div>
                <input type="password" class="form-control" placeholder="{{ trans('words.password') }}" name="password" value="{{ old('password') }}" required="" />
              </div>
              <div>
                <input type="submit" class="btn btn-default submit" value="Ingresar">
              </div>

              <div class="clearfix"></div>
              
              <br /><br />
              
              <!-- <div class="separator"> -->
                <p class="change_link">
                  <a href="#signup" class="to_register"> Crear Cuenta </a> |
                  <a class="reset_pass" href="/forgot_password">Olvide mi contraseña</a>
                </p>

                <div class="clearfix"></div>
                <br />


              <!-- </div> -->
            </form>

          </section>
        </div>

        <div id="register" class="animate form registration_form">
          <section class="login_content">
            
            <!-- <span class="top-logo"><img src="{{ config('constants.logo') }}" class="logo-home" alt="{{ config('constants.title') }}">{{ config('constants.title') }}</span> -->

            <form action="register" method="POST">
              
              {{ csrf_field() }}

              <h1>Crear Cuenta</h1>

              <!-- register success -->
              @if (session('status'))
                    <div class="panel panel-{{session('status')}}">
                        <div class="panel-heading">
                            {{session('message')}}
                        </div>     
                    </div>     
              @endif

              @if ($errors->has('dni') || $errors->has('email'))
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        @if ($errors->has('dni')) <p class="error-block">{{ $errors->first('dni') }}</p> @endif
                        @if ($errors->has('email')) <p class="error-block">{{ $errors->first('email') }}</p> @endif
                    </div>     
                </div>
              @endif

              <div>
                <input type="text" class="form-control" placeholder="Número de DNI/CUIT" name="dni" value="{{ old('dni') }}" required="" />
              </div>
              <div>
                <input type="email" class="form-control" placeholder="{{ trans('words.email') }}" name="email" value="{{ old('email') }}" required="" />
              </div>
              <div>
                <input type="submit" class="btn btn-default submit" value="Registrarme">
                <!-- <a class="reset_pass" href="#">Lost your password?</a> -->
              </div>


              <div class="clearfix"></div>
              
              <br /><br />
              
              <!-- <div class="separator"> -->
                <p class="change_link">
                  <a href="#signin" class="to_register"> Iniciar Sesion </a>
                </p>

                <div class="clearfix"></div>
                <br />


              </div>
            </form>
          </section>
        </div>
      </div>
    </div>


    <div class="copy">
      <p>{{ config('constants.copy') }}</p>
      <!-- <p>Todos los derechos reservados. {{ config('constants.developer') }}</p> -->
    </div>


  </body>
</html>
