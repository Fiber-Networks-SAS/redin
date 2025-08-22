@include('layout_admin.header')

<!-- 
vw: 1/100th viewport width
vh: 1/100th viewport height
vmin: 1/100th of the smallest side
vmax: 1/100th of the largest side
 -->

<!-- page content -->
<div class="right_col" role="main" style="height: 105vh;">
  
  	<div class="">
	    <div class="page-title">
	      <div class="title_left">
	        <h3><i class="fa fa-home"></i> Escritorio</h3>
	      </div>
	    </div>

		<div class="clearfix"></div>

		<div class="row">

	      <div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
	        <div class="tile-stats">
	          <!-- <div class="icon"><i class="fa fa-group"></i></div> -->
	          <div class="count"><a href="/admin/services">Servicios</a></div>
	          <h3>Servicios de la empresa</h3>
	          <p>Alta de servicios que se ofrecen</p>
	        </div>
	      </div>

	      <div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
	        <div class="tile-stats">
	          <!-- <div class="icon"><i class="fa fa-group"></i></div> -->
	          <div class="count"><a href="/admin/balance">Balance</a></div>
	          <h3>Balance de la empresa</h3>
	          <p>Gestión de Balance</p>
	        </div>
	      </div>

	      <div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
	        <div class="tile-stats">
	          <!-- <div class="icon"><i class="fa fa-group"></i></div> -->
	          <div class="count"><a href="/admin/claims">Reclamos</a></div>
	          <h3>Reclamos de clientes</h3>
	          <p>Gestión de Reclamos</p>
	        </div>
	      </div>

		</div>

  	</div>

  	<div class="">
	    <div class="page-title">
	      <div class="title_left">
	        <h3><i class="fa fa-file-text"></i> Facturas</h3>
	      </div>
	    </div>

		<div class="clearfix"></div>

		<div class="row">

	      <div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
	        <div class="tile-stats">
	          <!-- <div class="icon"><i class="fa fa-group"></i></div> -->
	          <div class="count"><a href="/admin/period">Períodos</a></div>
	          <h3>Facturar período</h3>
	          <p>Gestión de Períodos Facturados</p>
	        </div>
	      </div>

	      <div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
	        <div class="tile-stats">
	          <!-- <div class="icon"><i class="fa fa-group"></i></div> -->
	          <div class="count"><a href="/admin/bills">Buscar</a></div>
	          <h3>Buscar Facturas</h3>
	          <p>Gestión de Facturación</p>
	        </div>
	      </div>

		</div>

  	</div>

  	<div class="">
	    <div class="page-title">
	      <div class="title_left">
	        <h3><i class="fa fa-users"></i> Personas</h3>
	      </div>
	    </div>

		<div class="clearfix"></div>

	    <div class="row">
			<div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
				<div class="tile-stats">
				  <!-- <div class="icon"><i class="fa fa-user"></i></div> -->
				  <div class="count"><a href="/admin/users">Administradores</a></div>
				  <h3>Administradores del sistema</h3>
				  <p>Gestión de los Usuarios Administradores</p>
				</div>
			</div>

			<div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
				<div class="tile-stats">
				  <!-- <div class="icon"><i class="fa fa-user"></i></div> -->
				  <div class="count"><a href="/admin/staff">Personal</a></div>
				  <h3>Personal de la empresa</h3>
				  <p>Gestión del Personal (sin acceso al sistema)</p>
				</div>
			</div>

			<div class="animated flipInY col-lg-4 col-md-6 col-sm-6 col-xs-12">
				<div class="tile-stats">
				  <!-- <div class="icon"><i class="fa fa-group"></i></div> -->
				  <div class="count"><a href="/admin/clients">Clientes</a></div>
				  <h3>Clientes de la empresa</h3>
				  <p>Gestión de clientes de la empresa</p>
				</div>
			</div>



	    </div>
  	</div>

  	<div class="">
	    <div class="page-title">
	      <div class="title_left">
	        <h3><i class="fa fa-cog"></i> Configuraciones</h3>
	      </div>
	    </div>

		<div class="clearfix"></div>

	    <div class="row">
			<div class="animated flipInY col-lg-6 col-md-6 col-sm-6 col-xs-12">
				<div class="tile-stats">
				  <!-- <div class="icon"><i class="fa fa-user"></i></div> -->
				  <div class="count"><a href="/admin/config/invoice">Talonarios</a></div>
				  <h3>Configuración de Talonarios</h3>
				  <p>Talonarios que son utlizados para facturar</p>
				</div>
			</div>

			<div class="animated flipInY col-lg-6 col-md-6 col-sm-6 col-xs-12">
				<div class="tile-stats">
				  <!-- <div class="icon"><i class="fa fa-user"></i></div> -->
				  <div class="count"><a href="/admin/config/interests">Intereses</a></div>
				  <h3>Configuración de los intereses</h3>
				  <p>Intereses calculables en las facturas</p>
				</div>
			</div>




	    </div>
  	</div>
  
</div>
<!-- /page content -->

@include('layout_admin.footer')