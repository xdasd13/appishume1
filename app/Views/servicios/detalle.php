<?= $header ?>
<link rel="stylesheet" href="<?= base_url('assets/css/servicios-detalle.css') ?>">
<div class="container mt-4">
    <!-- Encabezado Principal -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="text-primary mb-0">
                    <i class="fas fa-clipboard-list me-2"></i><?= $titulo ?>
                </h2>
                <a href="<?= base_url('equipos') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver a Equipos
                </a>
            </div>
            <hr class="mt-3">
        </div>
    </div>

    <!-- Tarjeta de Información del Servicio -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Información del Servicio
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Columna Izquierda -->
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-concierge-bell"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">SERVICIO</small>
                                        <strong class="text-primary"><?= $servicio->servicio ?></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="info-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">CLIENTE</small>
                                        <strong><?= !empty($servicio->razonsocial) ? $servicio->razonsocial : $servicio->nombres . ' ' . $servicio->apellidos ?></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="info-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">TIPO DE EVENTO</small>
                                        <strong><?= $servicio->tipo_evento ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha -->
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">FECHA DEL EVENTO</small>
                                        <strong><?= date('d/m/Y', strtotime($servicio->fechaevento)) ?></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="info-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">FECHA Y HORA DEL SERVICIO</small>
                                        <strong><?= date('d/m/Y H:i', strtotime($servicio->fechahoraservicio)) ?></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="info-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">DIRECCIÓN</small>
                                        <strong><?= $servicio->direccion ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información Adicional -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted">CANTIDAD</h6>
                                    <h3 class="text-primary mb-0"><?= $servicio->cantidad ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted">PRECIO</h6>
                                    <h3 class="text-success mb-0">S/ <?= number_format($servicio->precio, 2) ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Equipos Asignados -->
    <div class="row">
        <div class="col-12">
            <div class="card border-info shadow-sm">
                <div class="card-header bg-info text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-users-cog me-2"></i>Equipos Asignados
                            <span class="badge bg-white text-info ms-2"><?= count($equipos) ?></span>
                        </h4>
                        <div>
                            <a href="<?= base_url('equipos/asignar/' . $servicio->idserviciocontratado) ?>"
                                class="btn btn-light btn-sm me-2">
                                <i class="fas fa-plus-circle me-1"></i>Asignar Nuevo Equipo
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($equipos)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 25%">
                                            <i class="fas fa-user me-1"></i>Usuario Asignado
                                        </th>
                                        <th style="width: 40%">
                                            <i class="fas fa-file-alt me-1"></i>Descripción
                                        </th>
                                        <th style="width: 20%">
                                            <i class="fas fa-tasks me-1"></i>Estado
                                        </th>
                                        <th style="width: 15%">
                                            <i class="fas fa-cogs me-1"></i>Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($equipos as $equipo): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                                        style="width: 35px; height: 35px;">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <strong><?= $equipo['nombres'] . ' ' . $equipo['apellidos'] ?></strong>
                                                        <br>
                                                        <small class="text-muted">@<?= $equipo['nombreusuario'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="d-inline-block text-truncate" style="max-width: 300px;"
                                                    title="<?= $equipo['descripcion'] ?>">
                                                    <?= $equipo['descripcion'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $estado = $equipo['estadoservicio'] ?? 'Planificación';
                                                $estadoConfigs = [
                                                    'Planificación' => ['color' => '#7c3aed', 'icon' => 'fa-route'],
                                                    'Producción' => ['color' => '#3b82f6', 'icon' => 'fa-video'],
                                                    'Postproducción' => ['color' => '#f59e0b', 'icon' => 'fa-pen-to-square'],
                                                    'Finalizado' => ['color' => '#10b981', 'icon' => 'fa-check-circle'],
                                                    'Vencido' => ['color' => '#ef4444', 'icon' => 'fa-triangle-exclamation']
                                                ];
                                                $config = $estadoConfigs[$estado] ?? ['color' => '#6b7280', 'icon' => 'fa-circle-info'];
                                                ?>
                                                <span class="badge text-uppercase" style="background: <?= $config['color'] ?>; color: #fff;">
                                                    <i class="fas <?= $config['icon'] ?> me-1"></i>
                                                    <?= $estado ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= base_url('equipos/editar/' . $equipo['idequipo']) ?>"
                                                        class="btn btn-warning btn-sm" title="Editar Asignación">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay equipos asignados</h5>
                            <p class="text-muted mb-4">Este servicio no tiene personal asignado todavía.</p>
                            <a href="<?= base_url('equipos/asignar/' . $servicio->idserviciocontratado) ?>"
                                class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>Asignar Primer Equipo
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estilos adicionales -->


<?= $footer ?>