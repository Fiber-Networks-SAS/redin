@include('layout.header')

<!-- page content -->
<div class="right_col" role="main">
  
	<div class="">

		<div class="page-title">
		  <div class="title_left">
		    <h3>Escritorio</h3>
		  </div>
		</div>

		<div class="clearfix"></div>

		<div class="row">


	    	<div class="row top_tiles">

				<a href="/my-invoice">
			      <div class="animated flipInY col-lg-3 col-md-3 col-sm-3 col-xs-12">
			        <div class="tile-stats">
			          <div class="icon"><i class="fa fa-file-text"></i></div>
			          <div class="count">Mis Facturas</div>
			        </div>
			      </div>
				</a>
			    
				<a href="my-claims">
			      <div class="animated flipInY col-lg-3 col-md-3 col-sm-3 col-xs-12">
			        <div class="tile-stats">
			          <div class="icon"><i class="fa fa-comments"></i></div>
			          <div class="count">Mis Reclamos</div>
			        </div>
			      </div>
			    </a>

			</div>




		</div>

	</div>

</div>
<!-- /page content -->

@include('layout.footer')