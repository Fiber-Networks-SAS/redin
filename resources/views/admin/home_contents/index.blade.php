@include('layout_admin.header')

<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Contenido de la Home</h3>
      </div>
    </div>

    <div class="clearfix"></div>
                   <!-- Estado eliminado -->
                   <!-- Botón activar/desactivar eliminado -->
                   <!-- toggleActive eliminado -->

    @if (session('status'))
        <div class="panel panel-{{session('status')}}">
            <div class="panel-heading">
                <i class="fa {{session('icon')}}"></i> {{session('message')}}
            </div>
        </div>
    @endif

    <div class="row">
                    <td><!-- Estado eliminado --></td>
          <div class="x_content">

            <div class="table-responsive">
              <table class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Sección</th>
                    <th>Título</th>
                    <th>Orden</th>
                    <th>Última Modificación</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($contents as $content)
                  <tr>
                    <td><strong>{{ ucfirst($content->section) }}</strong></td>
                    <td>{{ $content->title ?: 'Sin título' }}</td>
                    <td>{{ $content->sort_order }}</td>
                    <td>{{ $content->updated_at->format('d/m/Y H:i') }}</td>
                    <td>
                      <a href="{{ url('/admin/home-contents/view/' . $content->id) }}" class="btn btn-info btn-xs" title="Ver">
                        <i class="fa fa-eye"></i>
                      </a>
                      <a href="{{ url('/admin/home-contents/edit/' . $content->id) }}" class="btn btn-warning btn-xs" title="Editar">
                        <i class="fa fa-edit"></i>
                      </a>
                      <button class="btn btn-danger btn-xs" onclick="deleteContent({{ $content->id }})" title="Eliminar">
                        <i class="fa fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="5" class="text-center">
                      <p>No hay contenido creado aún.</p>
                      <a href="{{ url('/admin/home-contents/create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Crear primer contenido
                      </a>
                    </td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">Confirmar Eliminación</h4>
      </div>
      <div class="modal-body">
        <p>¿Está seguro que desea eliminar este contenido? Esta acción no se puede deshacer.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">
          <i class="fa fa-trash"></i> Eliminar
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let contentIdToDelete = null;

function deleteContent(id) {
  contentIdToDelete = id;
  $('#deleteModal').modal('show');
}

$('#confirmDelete').click(function() {
  if (contentIdToDelete) {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    $.post('/admin/home-contents/delete/' + contentIdToDelete)
    .done(function(response) {
      if (response.success) {
        alert(response.success);
        location.reload();
      } else {
        alert('Error: ' + response.error);
      }
    })
    .fail(function(xhr) {
      let error = 'Error desconocido';
      if (xhr.responseJSON && xhr.responseJSON.error) {
        error = xhr.responseJSON.error;
      }
      alert('Error: ' + error);
    });
  }
});

function toggleActive(id) {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  $.post('/admin/home-contents/toggle/' + id)
  .done(function(response) {
    if (response.success) {
      alert(response.success);
      location.reload();
    } else {
      alert('Error: ' + response.error);
    }
  })
  .fail(function(xhr) {
    let error = 'Error desconocido';
    if (xhr.responseJSON && xhr.responseJSON.error) {
      error = xhr.responseJSON.error;
    }
    alert('Error: ' + error);
  });
}
</script>

@include('layout_admin.footer')