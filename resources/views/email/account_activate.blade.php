<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
		<title>{{ config('constants.title') }} | Activa tu cuenta</title>
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
								            
								            <p style="font-family:Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:15px;padding:0">
								              ¡Gracias por registrarte! Para completar la activación de tu cuenta, seguí el enlace de abajo:
								            </p>
								            
								            <p style="font-family:Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:15px;padding:0;width:auto" width="auto">
								            <a href="{{$remember_token_url}}" style="background-color:#348eda;border:solid 1px #348eda;border-color:#348eda;border-radius:5px;box-sizing:border-box;color:#fff;display:inline-block;font-size:14px;font-weight:bold;margin:0;padding:12px 25px;text-decoration:none;text-transform:capitalize" bgcolor="#348eda" target="_blank">Activar mi cuenta</a></p>
								            <p style="font-family:Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:15px;padding:0">Saludos, Equipo de {{ config('constants.title') }}</p>
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