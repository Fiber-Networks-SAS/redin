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
      <!-- <a class="hiddenanchor" id="signup"></a> -->
      <!-- <a class="hiddenanchor" id="signin"></a> -->

      <div class="login_wrapper">

        <span class="top-logo">
          <a href="/"><img src="{{ config('constants.logo') }}" class="logo-home" alt="{{ config('constants.title') }}">{{ config('constants.title') }}</a>
        </span>

        <div class="animate form login_form">
          <section class="login_content">

            <form action="/reset/password" method="POST">
              
              {{ csrf_field() }}

              <input type="hidden" id="etoken" name="etoken" value="{{$rToken}}">

              <h1> Reestablecer mi contraseña</h1>

              <!-- register success -->
              @if (session('status'))
                    <div class="panel panel-{{session('status')}}">
                        <div class="panel-heading">
                            {{session('message')}}
                        </div>     
                    </div>     
              @endif

              <!-- login error -->
              @if ($errors->has('email'))
                  <div class="panel panel-danger">
                      <div class="panel-heading">
                          Ha ocurrido un error, intente mas tarde o pongase en contacto telefónicamente.
                      </div>     
                  </div>     
              @endif

              <div>
                <input type="password" class="form-control @if ($errors->has('password')) parsley-error @endif" placeholder="Contraseña" name="password" required="" />
                @if ($errors->has('password')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('password') }}</li></ul> @endif
              </div>

              <div>
                <input type="password" class="form-control @if ($errors->has('password_confirm')) parsley-error @endif" placeholder="Confirmación de Contraseña" name="password_confirm" required="" />
                @if ($errors->has('password_confirm')) <ul class="parsley-errors-list filled"><li class="parsley">{{ $errors->first('password_confirm') }}</li></ul> @endif
              </div>
              <div>

              <div>
                <input type="submit" class="btn btn-default submit" value="Reestablecer">
              </div>

              <div class="clearfix"></div>
              
              <br /><br />
              
              <!-- <div class="separator"> -->
                <p class="change_link">
                  <a href="/login" class="to_register"> Iniciar Sesion </a>
                </p>

                <div class="clearfix"></div>
                <br />


              <!-- </div> -->
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
