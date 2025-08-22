<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>{{ config('constants.title') }} | Formulario de Contacto</title>
	</head>
	<body>
		<div style="background-color:#f6f6f6;font-family:Helvetica Neue,Helvetica,Arial,sans-serif;font-size:14px;height:100%;line-height:1.6;margin:0;padding:0;width:100%" bgcolor="#f6f6f6" height="100%" width="100%">
			<table style="background-color:#f6f6f6;border-collapse:separate;border-spacing:0;box-sizing:border-box;width:100%" width="100%" bgcolor="#f6f6f6">
				<tbody>
				  <tr>
				    <td style="box-sizing:border-box;font-family:Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-size:14px;font-weight:normal;margin:0;vertical-align:top" valign="top"></td>
				    <td style="box-sizing:border-box;display:block;font-family:Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-size:14px;font-weight:normal;margin:0 auto;max-width:580px;padding:10px;text-align:center;vertical-align:top;width:auto" valign="top" align="center" width="auto">
					    <div style="box-sizing:border-box;display:block;margin:0 auto;max-width:580px;padding:10px;text-align:left" align="left">

							<table style="background:#fff;border:1px solid #e9e9e9;border-collapse:separate;border-radius:3px;border-spacing:0;box-sizing:border-box;width:100%" width="100%">
							  <tbody><tr>
								    <td style="box-sizing:border-box;font-family:Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-size:14px;font-weight:normal;margin:0;padding:30px;vertical-align:top" valign="top">
								      <table style="border-collapse:separate;border-spacing:0;box-sizing:border-box;width:100%" width="100%">
								        <tbody><tr>
								          <td style="box-sizing:border-box;font-family:Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-size:14px;font-weight:normal;margin:0;vertical-align:top" valign="top">

											<div style="font-family:Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-size:20px;font-weight:normal;margin:0;padding:10px;">
							                    <strong>Nuevo Contacto</strong>
						                  	</div>	

								            <p style="font-family:Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:15px;padding:0;width:auto" width="auto">
								            <p><strong>Nombre:</strong> {{$request->name}}</p>
								            <p><strong>Teléfono:</strong> {{$request->phone}}</p>
								            <p><strong>Correo electr&oacute;nico:</strong> {{$request->email}}</p>
								            <p><strong>Mensaje:</strong> {{$request->message}}</p>

								            <hr>
							            
								            <p style="text-align: center; font-family:Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-size:14px;font-weight:normal;margin:20px 0 0 0;margin-bottom:15px;padding:0">Equipo de {{ config('constants.title') }} <br> <a href="{{$url}}" style="color:#73879C;font-size:14px;" target="_blank">{{$url}}</a></p>
								          </td>
								        </tr>
								      </tbody></table>
								    </td>
								  </tr>
							  </tbody>
							</table>
						</div>
				    </td>
				    <td style="box-sizing:border-box;font-family:Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-size:14px;font-weight:normal;margin:0;vertical-align:top" valign="top"></td>
				</tr>
				</tbody>
			</table>
		</div>
	</body>
</html>