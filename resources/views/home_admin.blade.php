<!DOCTYPE html>
<html lang="es">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="UTF-8">
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
    <!-- Animate.css -->
    <link href="/vendors/animate.css/animate.min.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="/_admin/build/css/custom.min.css" rel="stylesheet">
  </head>

  <body class="login">
      <div class="login_wrapper">
        <div class="animate form login_form">
          <section class="login_content">
            
            @if (Session::has('login_errors'))
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <i class="icon-frown"></i> {{ trans('auth.failed') }}
                    </div>     
                </div>     
            @endif

            <form action="/admin/login" method="POST">
              {{ csrf_field() }}

              <h1>Administración</h1>
              <div>
                <input type="text" class="form-control" placeholder="{{ trans('words.email') }}" name="email" value="{{ old('email') }}" required="" />
                @if ($errors->has('user')) <p class="help-block">{{ $errors->first('user') }}</p> @endif
              </div>
              <div>
                <input type="password" class="form-control" placeholder="{{ trans('words.password') }}" name="password" value="{{ old('password') }}" required="" />
                @if ($errors->has('password')) <p class="help-block">{{ $errors->first('password') }}</p> @endif
              </div>
              <div>
                <input type="submit" class="btn btn-default submit" value="Ingresar">
                <!-- <a class="reset_pass" href="#">Lost your password?</a> -->
              </div>

              <div class="clearfix"></div>

              <div class="separator">
                <!-- <p class="change_link">New to site?
                  <a href="#signup" class="to_register"> Create Account </a>
                </p> -->

                <div class="clearfix"></div>
                <br />

                <div>
                  <h1><i class="{{ config('constants.icon') }} "></i> {{ config('constants.title') }}</h1>
                  <p>{{ config('constants.copy') }}</p>
                </div>
              </div>
            </form>
          </section>
        </div>
      </div>

  </body>
</html>
