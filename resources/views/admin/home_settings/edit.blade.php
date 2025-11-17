@extends('admin.layouts.app')

@section('content')
<div class="right_col" role="main">
    <div class="page-title">
        <div class="title_left">
            <h3>Editar contenido de la Home</h3>
        </div>
    </div>
    <div class="clearfix"></div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="row">
        <div class="col-md-10 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Contenido editable</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <form action="{{ url('admin/home-settings') }}" method="POST" enctype="multipart/form-data" class="form-horizontal form-label-left">
                        {{ csrf_field() }}
                        <ul class="nav nav-tabs" role="tablist" style="margin-bottom:20px;">
                            <li class="active"><a href="#slider" role="tab" data-toggle="tab">Slider</a></li>
                            <li><a href="#services" role="tab" data-toggle="tab">Servicios</a></li>
                            <li><a href="#company" role="tab" data-toggle="tab">La Empresa</a></li>
                            <li><a href="#internet" role="tab" data-toggle="tab">Internet</a></li>
                            <li><a href="#contact" role="tab" data-toggle="tab">Contacto</a></li>
                        </ul>
                        <div class="tab-content" style="padding:20px 0;">
                            <div class="tab-pane fade in active" id="slider">
                                <div class="form-group">
                                    <label for="slider_title" class="control-label col-md-3 col-sm-3 col-xs-12">Título principal del slider</label>
                                    <div class="col-md-7 col-sm-7 col-xs-12">
                                        <input type="text" class="form-control" id="slider_title" name="slider_title" value="{{ isset($settings['slider_title']) ? $settings['slider_title']->value : '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="slider_subtitle" class="control-label col-md-3 col-sm-3 col-xs-12">Subtítulo del slider</label>
                                    <div class="col-md-7 col-sm-7 col-xs-12">
                                        <input type="text" class="form-control" id="slider_subtitle" name="slider_subtitle" value="{{ isset($settings['slider_subtitle']) ? $settings['slider_subtitle']->value : '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="slider_bg" class="control-label col-md-3 col-sm-3 col-xs-12">Imagen de fondo del slider</label>
                                    <div class="col-md-7 col-sm-7 col-xs-12">
                                        @if(isset($settings['slider_bg']) && !empty($settings['slider_bg']->value))
                                            <div style="margin-bottom:10px;"><img src="{{ asset($settings['slider_bg']->value) }}" alt="Fondo actual" style="max-width:300px;"></div>
                                        @endif
                                        <input type="text" class="form-control" id="slider_bg" name="slider_bg" value="{{ isset($settings['slider_bg']) ? $settings['slider_bg']->value : '' }}">
                                        <small>Coloca la ruta o URL de la imagen. (Ej: /_landing/images/slider/1.jpg)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="services">
                                <div class="form-group">
                                    <label for="services_title" class="control-label col-md-3 col-sm-3 col-xs-12">Título sección Servicios</label>
                                    <div class="col-md-7 col-sm-7 col-xs-12">
                                        <input type="text" class="form-control" id="services_title" name="services_title" value="{{ isset($settings['services_title']) ? $settings['services_title']->value : '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="services_subtitle" class="control-label col-md-3 col-sm-3 col-xs-12">Subtítulo sección Servicios</label>
                                    <div class="col-md-7 col-sm-7 col-xs-12">
                                        <input type="text" class="form-control" id="services_subtitle" name="services_subtitle" value="{{ isset($settings['services_subtitle']) ? $settings['services_subtitle']->value : '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="services_text" class="control-label col-md-3 col-sm-3 col-xs-12">Texto sección Servicios</label>
                                    <div class="col-md-7 col-sm-7 col-xs-12">
                                        <textarea class="form-control" id="services_text" name="services_text" rows="3">{{ isset($settings['services_text']) ? $settings['services_text']->value : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="company">
                                <div class="form-group">
                                    <label for="company_title" class="control-label col-md-3 col-sm-3 col-xs-12">Título sección La Empresa</label>
                                    <div class="col-md-7 col-sm-7 col-xs-12">
                                        <input type="text" class="form-control" id="company_title" name="company_title" value="{{ isset($settings['company_title']) ? $settings['company_title']->value : '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="company_subtitle" class="control-label col-md-3 col-sm-3 col-xs-12">Subtítulo sección La Empresa</label>
                                    <div class="col-md-7 col-sm-7 col-xs-12">
                                        <input type="text" class="form-control" id="company_subtitle" name="company_subtitle" value="{{ isset($settings['company_subtitle']) ? $settings['company_subtitle']->value : '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="company_text" class="control-label col-md-3 col-sm-3 col-xs-12">Texto sección La Empresa</label>
                                    <div class="col-md-7 col-sm-7 col-xs-12">
                                        <textarea class="form-control" id="company_text" name="company_text" rows="4">{{ isset($settings['company_text']) ? $settings['company_text']->value : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="internet">
                                <div class="form-group">
                                    <label for="internet_title" class="control-label col-md-3 col-sm-3 col-xs-12">Título sección Internet</label>
                                    <div class="col-md-7 col-sm-7 col-xs-12">
                                        <input type="text" class="form-control" id="internet_title" name="internet_title" value="{{ isset($settings['internet_title']) ? $settings['internet_title']->value : '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="internet_subtitle" class="control-label col-md-3 col-sm-3 col-xs-12">Subtítulo sección Internet</label>
                                    <div class="col-md-7 col-sm-7 col-xs-12">
                                        <input type="text" class="form-control" id="internet_subtitle" name="internet_subtitle" value="{{ isset($settings['internet_subtitle']) ? $settings['internet_subtitle']->value : '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="internet_text" class="control-label col-md-3 col-sm-3 col-xs-12">Texto sección Internet</label>
                                    <div class="col-md-7 col-sm-7 col-xs-12">
                                        <textarea class="form-control" id="internet_text" name="internet_text" rows="4">{{ isset($settings['internet_text']) ? $settings['internet_text']->value : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="contact">
                                <div class="form-group">
                                    <label for="contact_title" class="control-label col-md-3 col-sm-3 col-xs-12">Título sección Contacto</label>
                                    <div class="col-md-7 col-sm-7 col-xs-12">
                                        <input type="text" class="form-control" id="contact_title" name="contact_title" value="{{ isset($settings['contact_title']) ? $settings['contact_title']->value : '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="contact_text" class="control-label col-md-3 col-sm-3 col-xs-12">Texto sección Contacto</label>
                                    <div class="col-md-7 col-sm-7 col-xs-12">
                                        <textarea class="form-control" id="contact_text" name="contact_text" rows="3">{{ isset($settings['contact_text']) ? $settings['contact_text']->value : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3">
                                <button type="submit" class="btn btn-success btn-lg">Guardar cambios</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $('.nav-tabs a').click(function(e){
            e.preventDefault();
            $(this).tab('show');
        });
    });
</script>
@endsection
