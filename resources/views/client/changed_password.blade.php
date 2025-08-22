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
        <div class="animate form login_form">
          <section class="login_content reset_password">
            <form action="#" method="POST">
                <h1>Contraseña reestablecida</h1>
                
                <p class="text-white">Perfecto! Tu contraseña ha sido reestablecida! <a href="/login">Iniciar Sesion</a></p>

                <div class="clearfix"></div>

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
