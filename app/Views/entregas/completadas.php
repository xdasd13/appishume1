<?= $header; ?>
<div class="container">
  <div class="page-inner">

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
              <h4 class="card-title">Todas las Entregas Registradas</h4>
              <div>
                <a href="<?= base_url('entregas') ?>" class="btn btn-primary btn-round">
                  <i class="fas fa-arrow-left mr-2"></i>Volver a Contratos
                </a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="fas fa-exclamation-circle mr-2"></i>
              <?= nl2br(htmlspecialchars(session()->getFlashdata('error'))) ?>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="fas fa-check-circle mr-2"></i>
              <?= htmlspecialchars(session()->getFlashdata('success')) ?>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('info')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
              <i class="fas fa-info-circle mr-2"></i>
              <?= htmlspecialchars(session()->getFlashdata('info')) ?>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <?php endif; ?>

            <div class="row mb-4">
              <div class="col-md-12">
                <div class="alert alert-info">
                  <i class="fas fa-info-circle mr-2"></i>
                  <strong>Información completa:</strong> Aquí encontrará todas las entregas registradas con sus
                  fechas y responsables para responder rápidamente a reclamos.
                </div>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-striped" id="entregas-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Servicio</th>
                    <th>Fecha Entrega</th>
                    <th>Responsable</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($entregas as $e): ?>
                  <tr>
                    <td><?= $e['identregable'] ?></td>
                    <td>
                      <div class="d-flex flex-column">
                        <span class="font-weight-bold"><?= $e['nombre_cliente'] ?> <?= $e['apellido_cliente'] ?></span>
                        <span class="text-muted small">Contrato #<?= $e['idcontrato'] ?></span>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex flex-column">
                        <span class="font-weight-bold"><?= $e['servicio'] ?? 'No disponible' ?></span>
                        <span class="text-muted small">
                          <?= isset($e['fechahoraservicio']) ? date('d/m/Y', strtotime($e['fechahoraservicio'])) : 'No disponible' ?>
                        </span>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex flex-column">
                        <span class="font-weight-bold"><?= date('d/m/Y', strtotime($e['fechahoraentrega'])) ?></span>
                        <span class="text-muted small"><?= date('h:i A', strtotime($e['fechahoraentrega'])) ?></span>
                      </div>
                    </td>
                    <td>
                      <div>
                        <?= $e['nombre_entrega'] ?> <?= $e['apellido_entrega'] ?>
                      </div>
                    </td>
                    <td>
                      <?php if($e['estado'] == 'completada'): ?>
                      <span class="badge badge-success"><?= isset($e['estado_visual']) ? $e['estado_visual'] : '✅ Entregada' ?></span>
                      <?php else: ?>
                      <span class="badge badge-warning"><?= isset($e['estado_visual']) ? $e['estado_visual'] : '⏳ Pendiente' ?></span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="btn-group">
                        <a href="<?= base_url('entregas/ver/' . $e['identregable']) ?>" class="btn btn-sm btn-primary" title="Ver detalle completo">
                          <i class="fas fa-eye"></i> Ver
                        </a>
                        <a href="<?= base_url('entregas/editar/' . $e['identregable']) ?>" class="btn btn-sm btn-warning" title="Editar formato y comprobante">
                          <i class="fas fa-edit"></i> Editar
                        </a>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>




<?= $footer; ?>

<script>
$(document).ready(function() {
  // Configuración de idioma DataTable (reutilizable)
  var dtSpanishConfig = {
    "sProcessing": "Procesando...",
    "sLengthMenu": "Mostrar _MENU_ registros",
    "sZeroRecords": "No se encontraron resultados",
    "sEmptyTable": "Ningún dato disponible en esta tabla",
    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
    "sInfoPostFix": "",
    "sSearch": "Buscar:",
    "sUrl": "",
    "sInfoThousands": ",",
    "sLoadingRecords": "Cargando...",
    "oPaginate": {
      "sFirst": "Primero",
      "sLast": "Último",
      "sNext": "Siguiente",
      "sPrevious": "Anterior"
    },
    "oAria": {
      "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
      "sSortDescending": ": Activar para ordenar la columna de manera descendente"
    }
  };
  
  // Inicializar DataTable
  $('#entregas-table').DataTable({
    "language": dtSpanishConfig,
    "order": [[0, "desc"]], // Ordenar por ID de entregable (descendente)
    "responsive": true
  });
});
</script>