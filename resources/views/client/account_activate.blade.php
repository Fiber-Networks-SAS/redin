<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('constants.title') }}</title>

    <!-- Bootstrap -->
    <link href="/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <!-- <link href="/vendors/nprogress/nprogress.css" rel="stylesheet"> -->
    <!-- Animate.css -->
    <!-- <link href="/vendors/animate.css/animate.min.css" rel="stylesheet"> -->

    <!-- Custom Theme Style -->
    <link href="/_clients/build/css/custom.css" rel="stylesheet">
  </head>

  <body class="activate_account">

    <div class="login_wrapper">

        <section class="login_content">

            <h2>¡Has activado tu cuenta correctamente!</h2>
            <p>Ahora pod&eacute;s entrar y disfrutar del servicio.</p>

            <div class="clearfix"></div>


            <div class="separator"></div>

            <p>Tu contraseña es <span class="password">{{$password}}</span></p>
            
            <br>
            
            <a href="/login" class="btn btn-default submit">Iniciar Sesion</a>

            <!-- <div class="separator"></div> -->
            
            
            
        </section>

    </div>

  </body>
</html>
