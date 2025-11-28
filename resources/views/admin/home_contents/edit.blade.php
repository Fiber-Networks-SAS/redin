@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Editar Contenido de Home</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Editar Contenido: {{ ucfirst($content->section) }}</h2>
            <span class="nav navbar-right">
              <a href="{{ url('/admin/home-contents') }}" class="btn btn-warning btn-xs">
                <i class="fa fa-arrow-left"></i> Volver
              </a>
            </span>
            <div class="clearfix"></div>
          </div>
          <div class="x_content">

            <form method="POST" action="{{ url('/admin/home-contents/edit/' . $content->id) }}" enctype="multipart/form-data" class="form-horizontal form-label-left">
              {{ csrf_field() }}

              <div class="form-group {{ $errors->has('section') ? 'has-error' : '' }}">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="section">Sección <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <select class="form-control" name="section" id="section" required>
                    <option value="">Seleccione una sección</option>
                    <option value="slider" {{ $content->section == 'slider' ? 'selected' : '' }}>Slider Principal</option>
                    <option value="services" {{ $content->section == 'services' ? 'selected' : '' }}>Servicios</option>
                    <option value="company" {{ $content->section == 'company' ? 'selected' : '' }}>La Empresa</option>
                    <option value="internet" {{ $content->section == 'internet' ? 'selected' : '' }}>Internet</option>
                    <option value="contact" {{ $content->section == 'contact' ? 'selected' : '' }}>Contacto</option>
                  </select>
                  @if ($errors->has('section')) <p class="help-block">{{ $errors->first('section') }}</p> @endif
                  <p class="help-block">Identificador único de la sección</p>
                </div>
              </div>

              <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="title">Título</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" class="form-control" name="title" id="title"
                         value="{{ old('title', $content->title) }}" placeholder="Título de la sección">
                  @if ($errors->has('title')) <p class="help-block">{{ $errors->first('title') }}</p> @endif
                </div>
              </div>

              <div class="form-group {{ $errors->has('subtitle') ? 'has-error' : '' }}">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="subtitle">Subtítulo</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" class="form-control" name="subtitle" id="subtitle"
                         value="{{ old('subtitle', $content->subtitle) }}" placeholder="Subtítulo de la sección">
                  @if ($errors->has('subtitle')) <p class="help-block">{{ $errors->first('subtitle') }}</p> @endif
                </div>
              </div>

              <div class="form-group {{ $errors->has('content') ? 'has-error' : '' }}">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="content">Contenido</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <textarea class="form-control" name="content" id="content" rows="6"
                            placeholder="Contenido de la sección (puede incluir HTML)">{{ old('content', $content->content) }}</textarea>
                  @if ($errors->has('content')) <p class="help-block">{{ $errors->first('content') }}</p> @endif
                </div>
              </div>

              <div class="form-group {{ $errors->has('image') ? 'has-error' : '' }}">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="image">Imagen</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  @if($content->image_path)
                    <div style="margin-bottom:10px;"><img src="{{ asset('storage/' . $content->image_path) }}" alt="Imagen actual" style="max-width:300px;"></div>
                  @endif
                  <input type="file" class="form-control" name="image" id="image" accept="image/*">
                  @if ($errors->has('image')) <p class="help-block">{{ $errors->first('image') }}</p> @endif
                  <p class="help-block">Seleccione una nueva imagen para reemplazar la actual (opcional)</p>
                </div>
              </div>

              <div class="form-group {{ $errors->has('link_text') ? 'has-error' : '' }}">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="link_text">Texto del Enlace</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" class="form-control" name="link_text" id="link_text"
                         value="{{ old('link_text', $content->link_text) }}" placeholder="Ejemplo: Ver más, Contactanos">
                  @if ($errors->has('link_text')) <p class="help-block">{{ $errors->first('link_text') }}</p> @endif
                </div>
              </div>

              <div class="form-group {{ $errors->has('link_url') ? 'has-error' : '' }}">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="link_url">URL del Enlace</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" class="form-control" name="link_url" id="link_url"
                         value="{{ old('link_url', $content->link_url) }}" placeholder="Ejemplo: #contact, /login">
                  @if ($errors->has('link_url')) <p class="help-block">{{ $errors->first('link_url') }}</p> @endif
                </div>
              </div>

              <div class="form-group {{ $errors->has('sort_order') ? 'has-error' : '' }}">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="sort_order">Orden</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="number" class="form-control" name="sort_order" id="sort_order"
                         value="{{ old('sort_order', $content->sort_order) }}" min="0">
                  @if ($errors->has('sort_order')) <p class="help-block">{{ $errors->first('sort_order') }}</p> @endif
                  <p class="help-block">Orden de aparición (0 = primero)</p>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Estado</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="is_active" value="1" {{ old('is_active', $content->is_active) ? 'checked' : '' }}>
                      Contenido activo (visible en la página)
                    </label>
                  </div>
                </div>
              </div>
                <!-- Campo Estado eliminado -->

              <div class="ln_solid"></div>
              <div class="form-group">
                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                  <button type="submit" class="btn btn-success">
                    <i class="fa fa-save"></i> Actualizar Contenido
                  </button>
                  <a href="{{ url('/admin/home-contents') }}" class="btn btn-warning">
                    <i class="fa fa-times"></i> Cancelar
                  </a>
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@include('layout_admin.footer')