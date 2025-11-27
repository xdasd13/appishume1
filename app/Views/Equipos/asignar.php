<?= $header ?>
<link rel="stylesheet" href="<?= base_url('assets/css/Equipos-asignar.css') ?>">

<!-- Incluir SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <h2><?= $titulo ?></h2>
    <!-- Mostrar mensajes con SweetAlert -->
    <?php if (session()->getFlashdata('error')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?= session()->getFlashdata('error') ?>',
                    confirmButtonColor: '#FF8C00'
                });
            });
        </script>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: '<?= session()->getFlashdata('success') ?>',
                    confirmButtonColor: '#FF8C00'
                });
            });
        </script>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">Información del Servicio</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Servicio:</strong> <?= $servicio['servicio'] ?></p>
                    <p><strong>Cliente:</strong>
                        <?= !empty($servicio['cliente_nombre']) ? $servicio['cliente_nombre'] : 'Cliente no especificado' ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Fecha del Evento:</strong>
                        <?= date('d/m/Y H:i', strtotime($servicio['fechahoraservicio'])) ?></p>
                    <p><strong>Dirección:</strong> <?= $servicio['direccion'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <form method="post" action="<?= base_url('equipos/saveEquipo') ?>" class="mt-4" id="form-asignacion">
        <?= csrf_field() ?>
        <input type="hidden" name="idserviciocontratado" value="<?= $servicio['idserviciocontratado'] ?>">

        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Asignación de Personal</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="idusuario" class="form-label">
                        Usuario/Técnico
                        <small class="text-muted">(Verde: Disponible, Amarillo: Ya asignado, Rojo: Conflicto de
                            horario)</small>
                    </label>
                    <select class="form-select" id="idusuario" name="idusuario" required>
                        <option value="">Seleccionar usuario</option>
                        <?php foreach ($tecnicos as $usuario): ?>
                            <option value="<?= $usuario['idusuario'] ?>" class="<?php
                              if ($usuario['disponible']) {
                                  echo 'usuario-disponible';
                              } elseif ($usuario['yaAsignado']) {
                                  echo 'usuario-ya-asignado';
                              } else {
                                  echo 'usuario-no-disponible';
                              }
                              ?>" data-disponible="<?= $usuario['disponible'] ? '1' : '0' ?>"
                                data-ya-asignado="<?= $usuario['yaAsignado'] ? '1' : '0' ?>"
                                data-conflictos="<?= htmlspecialchars(json_encode($usuario['conflictos'])) ?>"
                                data-nombre="<?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?>"
                                <?= !$usuario['disponible'] ? 'disabled' : '' ?>>
                                <?= $usuario['nombres'] . ' ' . $usuario['apellidos'] . ' (' . $usuario['cargo'] . ')' ?>
                                <?php if ($usuario['disponible']): ?>
                                    <span class="badge badge-disponible">Disponible</span>
                                <?php elseif ($usuario['yaAsignado']): ?>
                                    <span class="badge badge-asignado">Ya asignado</span>
                                <?php else: ?>
                                    <span class="badge badge-ocupado">Ocupado</span>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Área para mostrar información de disponibilidad -->
                    <div id="disponibilidad-info" class="disponibilidad-alert"></div>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción del Equipo</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required
                        placeholder="Describe el equipo a asignar, herramientas necesarias, etc."></textarea>
                </div>

                <div class="mb-3">
                    <label for="estadoservicio" class="form-label">Fase del Servicio</label>
                    <select class="form-select" id="estadoservicio" name="estadoservicio" required>
                        <option value="">Seleccionar fase</option>
                        <option value="Planificación" selected>Planificación</option>
                        <option value="Producción">Producción</option>
                        <option value="Postproducción">Postproducción</option>
                        <option value="Finalizado">Finalizado</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Resumen de disponibilidad -->
        <div class="card mb-4 summary-card">
            <div class="summary-header">
                <h6 class="mb-0">Resumen de Disponibilidad del Personal</h6>
            </div>
            <div class="summary-body">
                <div class="row">
                    <?php
                    $disponibles = array_filter($tecnicos, fn($u) => $u['disponible']);
                    $yaAsignados = array_filter($tecnicos, fn($u) => $u['yaAsignado']);
                    $ocupados = array_filter($tecnicos, fn($u) => !$u['disponible'] && !$u['yaAsignado']);
                    ?>

                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-2">
                            <span class="status-indicator indicator-success"></span>
                            <span class="badge badge-disponible ms-2"><?= count($disponibles) ?> Disponibles</span>
                        </div>
                        <ul class="user-list">
                            <?php foreach ($disponibles as $usuario): ?>
                                <li><?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-2">
                            <span class="status-indicator indicator-warning"></span>
                            <span class="badge badge-asignado ms-2"><?= count($yaAsignados) ?> Ya Asignados</span>
                        </div>
                        <ul class="user-list">
                            <?php foreach ($yaAsignados as $usuario): ?>
                                <li><?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-2">
                            <span class="status-indicator indicator-danger"></span>
                            <span class="badge badge-ocupado ms-2"><?= count($ocupados) ?> Con Conflictos</span>
                        </div>
                        <ul class="user-list">
                            <?php foreach ($ocupados as $usuario): ?>
                                <li><?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="<?= base_url('equipos') ?>"
                class="btn btn-secondary me-md-2">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary" id="btn-asignar" disabled>
                <i class="fas fa-save"></i> Asignar Equipo
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectUsuario = document.getElementById('idusuario');
        const infoDisponibilidad = document.getElementById('disponibilidad-info');
        const btnAsignar = document.getElementById('btn-asignar');
        const formAsignacion = document.getElementById('form-asignacion');

        selectUsuario.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            infoDisponibilidad.style.display = 'none';

            if (!selectedOption.value) {
                btnAsignar.disabled = true;
                return;
            }

            const disponible = selectedOption.getAttribute('data-disponible') === '1';
            const yaAsignado = selectedOption.getAttribute('data-ya-asignado') === '1';
            const conflictos = JSON.parse(selectedOption.getAttribute('data-conflictos') || '[]');

            infoDisponibilidad.style.display = 'block';

            if (disponible) {
                infoDisponibilidad.className = 'disponibilidad-alert alert-success';
                infoDisponibilidad.innerHTML = '<strong>✅ Usuario Disponible</strong><br>Este usuario puede ser asignado al servicio sin conflictos.';
                btnAsignar.disabled = false;
            } else if (yaAsignado) {
                infoDisponibilidad.className = 'disponibilidad-alert alert-warning';
                infoDisponibilidad.innerHTML = '<strong>⚠️ Usuario Ya Asignado</strong><br>Este usuario ya está asignado a este servicio específico.';
                btnAsignar.disabled = true;
            } else if (conflictos.length > 0) {
                let conflictosHtml = '<strong>❌ Conflictos de Horario</strong><br>';
                conflictosHtml += 'Este usuario tiene los siguientes conflictos:<ul>';

                conflictos.forEach(function (conflicto) {
                    conflictosHtml += `<li><strong>${conflicto.servicio}</strong> - ${conflicto.fecha}`;
                    if (conflicto.descripcion) {
                        conflictosHtml += `<br><small>${conflicto.descripcion}</small>`;
                    }
                    conflictosHtml += '</li>';
                });

                conflictosHtml += '</ul>';
                infoDisponibilidad.className = 'disponibilidad-alert alert-danger';
                infoDisponibilidad.innerHTML = conflictosHtml;
                btnAsignar.disabled = true;
            }
        });

        // Validación del formulario con SweetAlert
        formAsignacion.addEventListener('submit', function (e) {
            e.preventDefault();

            const selectedOption = selectUsuario.options[selectUsuario.selectedIndex];

            if (!selectedOption.value) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor seleccione un usuario.',
                    confirmButtonColor: '#FF8C00'
                });
                return;
            }

            const disponible = selectedOption.getAttribute('data-disponible') === '1';
            const nombreUsuario = selectedOption.getAttribute('data-nombre');

            if (!disponible) {
                Swal.fire({
                    icon: 'error',
                    title: 'Usuario no disponible',
                    text: 'El usuario seleccionado no está disponible. Por favor seleccione otro usuario.',
                    confirmButtonColor: '#FF8C00'
                });
                return;
            }

            // Confirmación con SweetAlert
            Swal.fire({
                title: 'Confirmar asignación',
                html: `¿Está seguro de asignar a <strong>${nombreUsuario}</strong> a este servicio?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#FF8C00',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, asignar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Si confirma, enviar el formulario
                    formAsignacion.submit();
                }
            });
        });
    });
</script>
<?= $footer ?>