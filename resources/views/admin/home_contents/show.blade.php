@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Detalle del Contenido</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Contenido: {{ ucfirst($content->section) }}</h2>
            <span class="nav navbar-right">
              <a href="{{ url('/admin/home-contents') }}" class="btn btn-warning btn-xs">
                <i class="fa fa-arrow-left"></i> Volver
              </a>
              <a href="{{ url('/admin/home-contents/edit/' . $content->id) }}" class="btn btn-primary btn-xs">
                <i class="fa fa-edit"></i> Editar
              </a>
            </span>
            <div class="clearfix"></div>
          </div>
          <div class="x_content">

            <div class="row">
              <div class="col-md-8">
                <table class="table table-striped">
                  <tr>
                    <th style="width: 150px;">Sección:</th>
                    <td>{{ ucfirst($content->section) }}</td>
                  </tr>
                  <tr>
                    <th>Título:</th>
                    <td>{{ $content->title ?: 'Sin título' }}</td>
                  </tr>
                  <tr>
                    <th>Subtítulo:</th>
                    <td>{{ $content->subtitle ?: 'Sin subtítulo' }}</td>
                  </tr>
                  <tr>
                    <th>Estado:</th>
                    <td>
                      @if($content->is_active)
                        <span class="label label-success">
                          <i class="fa fa-check"></i> Activo
                        </span>
                      @else
                        <span class="label label-danger">
                          <i class="fa fa-times"></i> Inactivo
                        </span>
                      @endif
                    </td>
                  </tr>
                    <!-- Estado eliminado -->
                  <tr>
                    <th>Orden:</th>
                    <td>{{ $content->sort_order }}</td>
                  </tr>
                  <tr>
                    <th>Texto del Enlace:</th>
                    <td>{{ $content->link_text ?: 'Sin enlace' }}</td>
                  </tr>
                  <tr>
                    <th>URL del Enlace:</th>
                    <td>{{ $content->link_url ?: 'Sin URL' }}</td>
                  </tr>
                  <tr>
                    <th>Creado:</th>
                    <td>{{ $content->created_at->format('d/m/Y H:i') }}</td>
                  </tr>
                  <tr>
                    <th>Última Modificación:</th>
                    <td>{{ $content->updated_at->format('d/m/Y H:i') }}</td>
                  </tr>
                </table>
              </div>

              <!-- Imagen eliminada -->
            </div>

            @if($content->content)
            <div class="row">
              <div class="col-md-12">
                <div class="well">
                  <h4>Contenido</h4>
                  <div style="border: 1px solid #ddd; padding: 15px; background-color: #f9f9f9;">
                    {!! $content->content !!}
                  </div>
                </div>
              </div>
            </div>
            @endif

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@include('layout_admin.footer')