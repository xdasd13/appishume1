<?= $header; ?>

<div class="container">
    <div class="page-inner">
        

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <?= htmlspecialchars(session()->getFlashdata('success')) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?= nl2br(htmlspecialchars(session()->getFlashdata('error'))) ?>
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

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title">Información de Entrega #<?= $entrega['identregable'] ?></h4>
                            <span class="ml-3 badge <?= $entrega['estado'] == 'completada' ? 'badge-success' : 'badge-warning' ?>">
                                <?= isset($entrega['estado_visual']) ? $entrega['estado_visual'] : ($entrega['estado'] == 'completada' ? '✅ ENTREGADO' : '⏳ EN POSTPRODUCCIÓN') ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5 class="text-primary">
                                    <i class="fas fa-user-circle mr-2"></i>Cliente
                                </h5>
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <th class="bg-light">Nombre</th>
                                        <td><?= isset($entrega['nombre_cliente']) ? $entrega['nombre_cliente'] : 'No disponible' ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Documento</th>
                                        <td>
                                            <?php if(isset($entrega['tipodoc']) && !empty($entrega['tipodoc']) && isset($entrega['numerodoc']) && !empty($entrega['numerodoc'])): ?>
                                                <?= $entrega['tipodoc'] ?>: <?= $entrega['numerodoc'] ?>
                                            <?php else: ?>
                                                No disponible
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Teléfono</th>
                                        <td><?= isset($entrega['telprincipal']) && !empty($entrega['telprincipal']) ? $entrega['telprincipal'] : 'No disponible' ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Dirección</th>
                                        <td><?= isset($entrega['direccion']) && !empty($entrega['direccion']) ? $entrega['direccion'] : 'No disponible' ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5 class="text-info">
                                    <i class="fas fa-briefcase mr-2"></i>Servicio
                                </h5>
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <th class="bg-light">Servicio</th>
                                        <td><?= isset($entrega['servicio']) && !empty($entrega['servicio']) ? $entrega['servicio'] : 'No disponible' ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Descripción</th>
                                        <td><?= isset($entrega['descripcion_servicio']) && !empty($entrega['descripcion_servicio']) ? $entrega['descripcion_servicio'] : 'No disponible' ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Fecha Servicio</th>
                                        <td><?= isset($entrega['fechahoraservicio']) && !empty($entrega['fechahoraservicio']) ? date('d/m/Y H:i', strtotime($entrega['fechahoraservicio'])) : 'No disponible' ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Cantidad</th>
                                        <td><?= isset($entrega['cantidad']) ? $entrega['cantidad'] : 'No disponible' ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Precio</th>
                                        <td><?= isset($entrega['precio']) ? 'S/ ' . number_format($entrega['precio'], 2) : 'No disponible' ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="text-success">
                                    <i class="fas fa-truck mr-2"></i>Información de Entrega
                                </h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th class="bg-light" width="25%">Responsable</th>
                                        <td>
                                            <div>
                                                <?= isset($entrega['nombre_entrega']) && !empty($entrega['nombre_entrega']) && $entrega['nombre_entrega'] !== 'Sin nombre' ? 
                                                    $entrega['nombre_entrega'] . ' ' . $entrega['apellido_entrega'] : 
                                                    session()->get('usuario_nombre') ?? 'No disponible' ?>
                                                <div class="text-muted small">
                                                    <?= isset($entrega['numerodoc_entrega']) && !empty($entrega['numerodoc_entrega']) && $entrega['numerodoc_entrega'] !== 'No disponible' ? 
                                                        'DNI: ' . $entrega['numerodoc_entrega'] : '' ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Fecha/Hora Entrega</th>
                                        <td>
                                            <?php if(isset($entrega['estado']) && $entrega['estado'] == 'completada' && isset($entrega['fecha_real_entrega']) && !empty($entrega['fecha_real_entrega'])): ?>
                                                <?= date('d/m/Y H:i', strtotime($entrega['fecha_real_entrega'])) ?>
                                            <?php else: ?>
                                                <?= isset($entrega['fechahoraentrega']) && !empty($entrega['fechahoraentrega']) ? date('d/m/Y H:i', strtotime($entrega['fechahoraentrega'])) : 'No disponible' ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Formato Entrega</th>
                                        <td><?= isset($entrega['observaciones']) && !empty($entrega['observaciones']) ? $entrega['observaciones'] : 'No disponible' ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Estado</th>
                                        <td>
                                            <span class="badge <?= (isset($entrega['estado']) && $entrega['estado'] == 'completada') ? 'badge-success' : 'badge-warning' ?> p-2">
                                                <?= isset($entrega['estado_visual']) ? $entrega['estado_visual'] : (isset($entrega['estado']) && $entrega['estado'] == 'completada' ? '✅ ENTREGADO' : '⏳ EN POSTPRODUCCIÓN') ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Controles -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= base_url('entregas/historial') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>Volver
                            </a>
                            <div>
                                <a href="<?= base_url('entregas/editar/' . $entrega['identregable']) ?>" class="btn btn-warning mr-2">
                                    <i class="fas fa-edit mr-2"></i>Editar
                                </a>
                                <a href="<?= base_url('entregas/imprimir/' . $entrega['identregable']) ?>" target="_blank" class="btn btn-info">
                                    <i class="fas fa-print mr-2"></i>Imprimir
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-file-pdf mr-2"></i>Comprobante de Entrega
                        </h4>
                    </div>
                    <div class="card-body p-0">
                        <?php if(isset($entrega['comprobante_entrega']) && !empty($entrega['comprobante_entrega'])): ?>
                            <div style="height: 500px;">
                                <iframe src="<?= base_url('uploads/comprobantes_entrega/' . $entrega['comprobante_entrega']) ?>" 
                                        style="width: 100%; height: 100%; border: none;"></iframe>
                            </div>
                            <div class="p-3">
                                <a href="<?= base_url('uploads/comprobantes_entrega/' . $entrega['comprobante_entrega']) ?>" 
                                   class="btn btn-block btn-success" download>
                                    <i class="fas fa-download mr-2"></i>Descargar comprobante
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="p-4 text-center">
                                <i class="fas fa-file-pdf text-danger fa-4x mb-3"></i>
                                <p>No hay comprobante de entrega disponible.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-primary text-white">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-info-circle mr-2"></i>Información Adicional
                        </h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Contrato
                                <span class="badge badge-primary">#<?= isset($entrega['idcontrato']) && !empty($entrega['idcontrato']) ? $entrega['idcontrato'] : 'N/A' ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Cotización
                                <span class="badge badge-info">#<?= isset($entrega['idcotizacion']) && !empty($entrega['idcotizacion']) ? $entrega['idcotizacion'] : 'N/A' ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Días de Postproducción
                                <span class="badge badge-info"><?= isset($entrega['dias_postproduccion']) ? abs($entrega['dias_postproduccion']) : '0' ?> días</span>
                            </li>
                        </ul>

                        <?php if(isset($entrega['estado']) && $entrega['estado'] == 'completada'): ?>
                            <div class="alert alert-success mt-3">
                                <i class="fas fa-check-circle mr-2"></i>
                                <strong>Entrega Completada</strong><br>
                                Esta entrega fue completada satisfactoriamente.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Entrega Pendiente</strong><br>
                                Esta entrega aún está pendiente de completar.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?= $footer; ?>