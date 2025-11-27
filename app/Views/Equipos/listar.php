<?= $header ?>
<link rel="stylesheet" href="<?= base_url('assets/css/Equipos-listar.css') ?>">

<!-- Cargar helper de estados -->
<?php helper('estado'); ?>

<div class="container-fluid mt-4">
    <!-- Encabezado con estadísticas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="text-primary mb-0">
                    <i class="fas fa-users-cog me-2"></i><?= $titulo ?>
                </h2>

                <!-- Estadísticas rápidas -->
                <?php
                $fasesTablero = ['Planificación', 'Producción', 'Postproducción', 'Finalizado'];
                $estadisticas = $estadisticas ?? [];
                ?>
                <div class="d-flex gap-2">
                    <?php foreach ($fasesTablero as $fase): ?>
                        <?php
                        $configFase = [
                            'Planificación' => ['color' => '#7c3aed', 'icono' => 'fas fa-calendar-alt'],
                            'Producción' => ['color' => '#3b82f6', 'icono' => 'fas fa-video'],
                            'Postproducción' => ['color' => '#f59e0b', 'icono' => 'fas fa-magic'],
                            'Finalizado' => ['color' => '#10b981', 'icono' => 'fas fa-check-circle']
                        ];
                        $config = $configFase[$fase];
                        ?>
                        <div class="badge fs-6 px-3 py-2" style="background-color: <?= $config['color'] ?>; color: white; font-family: 'Poppins', sans-serif; font-weight: 600;">
                            <i class="<?= $config['icono'] ?> me-1"></i>
                            <?= $fase ?>: <?= $estadisticas[$fase] ?? 0 ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Tarjeta de Información del Servicio -->
    <?php if (isset($servicio)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Servicio</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong class="text-primary"><i
                                            class="fas fa-concierge-bell me-2"></i>Servicio:</strong>
                                    <p class="mb-0"><?= is_array($servicio) ? $servicio['servicio'] : $servicio->servicio ?>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <strong class="text-primary"><i class="fas fa-user me-2"></i>Cliente:</strong>
                                    <p class="mb-0"><?php
                                    if (is_array($servicio)) {
                                        if (!empty($servicio['razonsocial'])) {
                                            echo $servicio['razonsocial'];
                                        } elseif (isset($servicio['nombres']) && isset($servicio['apellidos'])) {
                                            echo $servicio['nombres'] . ' ' . $servicio['apellidos'];
                                        } elseif (isset($servicio['cliente_nombre'])) {
                                            echo $servicio['cliente_nombre'];
                                        } else {
                                            echo 'Cliente no especificado';
                                        }
                                    } else {
                                        if (!empty($servicio->razonsocial)) {
                                            echo $servicio->razonsocial;
                                        } elseif (isset($servicio->nombres) && isset($servicio->apellidos)) {
                                            echo $servicio->nombres . ' ' . $servicio->apellidos;
                                        } elseif (isset($servicio->cliente_nombre)) {
                                            echo $servicio->cliente_nombre;
                                        } else {
                                            echo 'Cliente no especificado';
                                        }
                                    }
                                    ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong class="text-primary"><i class="fas fa-calendar-day me-2"></i>Fecha del
                                        Evento:</strong>
                                    <p class="mb-0"><?php
                                    if (is_array($servicio)) {
                                        $fecha = isset($servicio['fechaevento']) ? $servicio['fechaevento'] : (isset($servicio['fechahoraservicio']) ? $servicio['fechahoraservicio'] : null);
                                    } else {
                                        $fecha = isset($servicio->fechaevento) ? $servicio->fechaevento : (isset($servicio->fechahoraservicio) ? $servicio->fechahoraservicio : null);
                                    }
                                    echo $fecha ? date('d/m/Y', strtotime($fecha)) : 'Fecha no especificada';
                                    ?></p>
                                </div>
                                <div class="mb-3">
                                    <strong class="text-primary"><i class="fas fa-star me-2"></i>Tipo de Evento:</strong>
                                    <p class="mb-0"><?php
                                    if (is_array($servicio)) {
                                        echo isset($servicio['tipo_evento']) ? $servicio['tipo_evento'] : 'Tipo no especificado';
                                    } else {
                                        echo isset($servicio->tipo_evento) ? $servicio->tipo_evento : 'Tipo no especificado';
                                    }
                                    ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <a href="<?= base_url('equipos/asignar/' . (is_array($servicio) ? $servicio['idserviciocontratado'] : $servicio->idserviciocontratado)) ?>"
                                class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>Asignar Nuevo Equipo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tarjeta de Información del Usuario -->
    <?php if (isset($usuario)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-user-tie me-2"></i>Información del Usuario</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <strong class="text-info"><i class="fas fa-id-card me-2"></i>Nombre:</strong>
                                    <p class="mb-0"><?= $usuario->nombres . ' ' . $usuario->apellidos ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <strong class="text-info"><i class="fas fa-user me-2"></i>Usuario:</strong>
                                    <p class="mb-0"><?= $usuario->nombreusuario ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <strong class="text-info"><i class="fas fa-briefcase me-2"></i>Cargo:</strong>
                                    <p class="mb-0"><?= $usuario->cargo ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- Barra de Búsqueda y Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" id="searchInput" class="form-control"
                                    placeholder="Buscar por cliente, servicio o técnico...">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <select id="filterTecnico" class="form-select">
                                <option value="">Todos los trabajadores</option>
                                <?php if (isset($tecnicos)): ?>
                                    <?php foreach ($tecnicos as $tecnico): ?>
                                        <option value="<?= $tecnico['idusuario'] ?>">
                                            <?= esc($tecnico['nombres'] . ' ' . $tecnico['apellidos']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select id="filterVencimiento" class="form-select">
                                <option value="">Todas las fechas</option>
                                <option value="hoy">Hoy</option>
                                <option value="semana">Esta semana</option>
                                <option value="mes">Este mes</option>
                                <option value="vencidos">Vencidos</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button id="btnResetFilters" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo me-1"></i>Limpiar
                            </button>
                        </div>
                    </div>

                    <div id="searchResults" class="mt-3 d-none">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <span id="resultsCount">0</span> resultados encontrados
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensaje informativo para trabajadores -->
    <?php if (isset($es_trabajador) && $es_trabajador): ?>
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="fas fa-info-circle me-3 fs-4"></i>
                    <div>
                        <strong>Vista de Trabajador:</strong> Este tablero muestra únicamente los servicios asignados a ti. 
                        Solo puedes gestionar tus propias asignaciones.
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tablero Kanban-->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 text-dark">
                    <i class="fas fa-columns me-2 text-primary"></i>
                    <?= isset($es_trabajador) && $es_trabajador ? 'Mi Tablero de Trabajo' : 'Tablero Kanban' ?>
                </h4>
                <?php if (isset($servicio) && (!isset($es_trabajador) || !$es_trabajador)): ?>
                    <a href="<?= base_url('equipos/asignar/' . $servicio['idserviciocontratado']) ?>"
                        class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Asignar Técnico
                    </a>
                <?php endif; ?>
            </div>

            <?php
            // Usar datos agrupados del modelo o crear estructura por defecto
            $fasesTablero = ['Planificación', 'Producción', 'Postproducción', 'Finalizado'];
            $equiposKanban = $equiposKanban ?? [];
            foreach ($fasesTablero as $fase) {
                if (!array_key_exists($fase, $equiposKanban)) {
                    $equiposKanban[$fase] = [];
                }
            }
            ?>

            <?php if (!empty(array_filter($equiposKanban))): ?>
                <!-- Tablero Kanban Simplificado -->
                <div class="kanban-board">
                    <div class="row g-4">
                        <?php
                        // Configuración de columnas KISS - 4 fases
                        $columnas = [
                            'Planificación' => ['color' => '#7c3aed', 'icono' => 'fas fa-calendar-alt'],
                            'Producción' => ['color' => '#3b82f6', 'icono' => 'fas fa-video'],
                            'Postproducción' => ['color' => '#f59e0b', 'icono' => 'fas fa-magic'],
                            'Finalizado' => ['color' => '#10b981', 'icono' => 'fas fa-check-circle']
                        ];
                        ?>

                        <?php foreach ($columnas as $estado => $config): ?>
                            <div class="col-lg-3 col-md-6">
                                <div class="kanban-column">
                                    <!-- Header de columna -->
                                    <div class="kanban-column-header" style="background-color: <?= $config['color'] ?>; color: white;">
                                        <h5 class="text-center text-white mb-0" style="font-family: 'Poppins', sans-serif; font-weight: 700; letter-spacing: 0.3px;">
                                            <i class="<?= $config['icono'] ?> me-2"></i><?= $estado ?>
                                            <span class="badge bg-white text-dark ms-2" style="font-family: 'Poppins', sans-serif; font-weight: 600;">
                                                <?= count($equiposKanban[$estado]) ?>
                                            </span>
                                        </h5>
                                    </div>

                                    <!-- Cuerpo de columna -->
                                    <div class="kanban-column-body" id="<?= strtolower(str_replace(' ', '-', $estado)) ?>"
                                        data-estado="<?= $estado ?>">

                                        <?php if (!empty($equiposKanban[$estado])): ?>
                                            <?php if (isset($agrupar_por_cliente) && $agrupar_por_cliente): ?>
                                                <!-- Modo agrupado por cliente -->
                                                <?php foreach ($equiposKanban[$estado] as $clienteData): ?>
                                                    <?= renderClienteCard($clienteData, $estado) ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <!-- Modo individual (sin agrupar) -->
                                                <?php foreach ($equiposKanban[$estado] as $equipo): ?>
                                                    <?= renderEquipoCard($equipo, $estado) ?>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="kanban-empty-state">
                                                <i class="fas fa-inbox fa-2x mb-2 text-muted"></i>
                                                <p class="text-muted mb-0">No hay equipos en <?= strtolower($estado) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Estado vacío -->
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay equipos asignados</h5>
                    <p class="text-muted">Comienza asignando técnicos a los servicios contratados.</p>
                    <?php if (isset($servicio)): ?>
                        <a href="<?= base_url('equipos/asignar/' . $servicio['idserviciocontratado']) ?>"
                            class="btn btn-primary mt-3">
                            <i class="fas fa-plus me-2"></i>Asignar Primer Técnico
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Función PHP para renderizar tarjetas -->
<?php
function renderEquipoCard(array $equipo, string $estado): string
{
    // Verificar si el servicio tiene asignación (tiene idequipo)
    $tieneAsignacion = !empty($equipo['idequipo']);
    
    // Mapeo local de colores e iconos
    $colores = [
        'Programado' => 'secondary',
        'Pendiente' => 'warning',
        'En Proceso' => 'info',
        'Completado' => 'success'
    ];

    $iconos = [
        'Programado' => 'fas fa-calendar-alt',
        'Pendiente' => 'fas fa-clock',
        'En Proceso' => 'fas fa-spinner',
        'Completado' => 'fas fa-check-circle'
    ];

    $colorEstado = $colores[$estado] ?? 'secondary';
    $iconoEstado = $iconos[$estado] ?? 'fas fa-question-circle';

    // Truncar descripción a 2 líneas (aproximadamente 80 caracteres)
    $descripcion = $equipo['descripcion'] ?? 'Sin descripción asignada';
    $descripcionCorta = strlen($descripcion) > 80
        ? substr($descripcion, 0, 80) . '...'
        : $descripcion;

    // Nombre corto del técnico (solo si hay asignación)
    $nombreCorto = 'Sin asignar';
    if ($tieneAsignacion && !empty($equipo['nombre_completo'])) {
        $nombreCorto = explode(' ', $equipo['nombre_completo'])[0] . ' ' . substr(explode(' ', $equipo['nombre_completo'])[1] ?? '', 0, 1) . '.';
    } elseif ($tieneAsignacion && !empty($equipo['nombreusuario'])) {
        $nombreCorto = $equipo['nombreusuario'];
    }

    ob_start();
    ?>
    <div class="kanban-card" data-id="<?= $equipo['idequipo'] ?? '' ?>" data-status="<?= $equipo['estadoservicio'] ?? $estado ?>"
        data-cliente="<?= esc(strtolower($equipo['cliente_nombre'] ?? '')) ?>"
        data-servicio="<?= esc(strtolower($equipo['servicio'] ?? '')) ?>"
        data-tecnico="<?= esc(strtolower($equipo['nombre_completo'] ?? 'Sin asignar')) ?>" 
        data-usuario-id="<?= $equipo['idusuario'] ?? '' ?>"
        data-fecha="<?= $equipo['fechahoraservicio'] ?? '' ?>" 
        <?php if ($tieneAsignacion): ?>draggable="true"<?php endif; ?> 
        style="font-family: 'Poppins', sans-serif;">
        <!-- Header de tarjeta -->
        <div class="card-header-kanban">
            <span class="badge text-white" style="background-color: <?= $colorEstado ?>; font-family: 'Poppins', sans-serif; font-weight: 600;">
                <i class="<?= $iconoEstado ?> me-1"></i><?= $estado ?>
            </span>
            <?php if ($tieneAsignacion): ?>
                <span class="kanban-card-id text-muted">#<?= $equipo['idequipo'] ?></span>
            <?php else: ?>
                <span class="kanban-card-id text-muted" style="font-style: italic;">Sin asignar</span>
            <?php endif; ?>
        </div>

        <!-- Cuerpo de tarjeta -->
        <div class="card-body-kanban">
            <div class="mb-2">
                <strong class="text-primary d-block" style="font-size: 1.1rem; font-family: 'Poppins', sans-serif; font-weight: 700;">
                    <i class="fas fa-user-circle me-1"></i><?= esc($equipo['cliente_nombre'] ?? 'Cliente no especificado') ?>
                </strong>
                <div class="text-success fw-bold mb-1" style="font-size: 0.95rem; font-family: 'Poppins', sans-serif; font-weight: 600;">
                    <i class="fas fa-concierge-bell me-1"></i><?= esc($equipo['servicio'] ?? 'Servicio') ?>
                </div>
                <small class="text-muted" style="font-family: 'Poppins', sans-serif;">
                    <i class="fas fa-user me-1"></i><?= esc($nombreCorto) ?>
                    <?php if ($tieneAsignacion && !empty($equipo['cargo'])): ?>
                        <span class="ms-2">
                            <i class="fas fa-briefcase me-1"></i><?= esc($equipo['cargo']) ?>
                        </span>
                    <?php endif; ?>
                </small>
            </div>

            <?php if ($tieneAsignacion && !empty($descripcion) && $descripcion !== 'Sin descripción asignada'): ?>
                <div class="description-container mb-3">
                    <p class="description-text mb-0" title="<?= esc($descripcion) ?>" style="font-family: 'Poppins', sans-serif;">
                        <?= esc($descripcionCorta) ?>
                    </p>
                    <?php if (strlen($descripcion) > 80): ?>
                        <button class="btn btn-link btn-sm p-0 text-decoration-none" onclick="toggleDescription(this)" style="font-family: 'Poppins', sans-serif;">
                            <small>Ver más</small>
                        </button>
                    <?php endif; ?>
                </div>
            <?php elseif (!$tieneAsignacion): ?>
    
            <?php endif; ?>

            <div class="mb-2">
                <?php if (!empty($equipo['fechahoraservicio'])): ?>
                    <div class="text-muted small mb-1" style="font-family: 'Poppins', sans-serif;">
                        <i class="fas fa-calendar me-1"></i>
                        <strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($equipo['fechahoraservicio'])) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($equipo['cliente_telefono'])): ?>
                    <div class="text-muted small mb-1" style="font-family: 'Poppins', sans-serif;">
                        <i class="fas fa-phone me-1"></i>
                        <strong>Tel:</strong> <?= esc($equipo['cliente_telefono']) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($equipo['tipoevento'])): ?>
                    <div class="text-muted small">
                        <i class="fas fa-star me-1"></i>
                        <strong>Evento:</strong> <?= esc($equipo['tipoevento']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Acciones de tarjeta -->
        <div class="card-actions">
            <?php if ($tieneAsignacion): ?>
                <a href="<?= base_url('equipos/editar/' . $equipo['idequipo']) ?>" class="btn btn-sm btn-outline-primary"
                    title="Editar asignación" data-bs-toggle="tooltip">
                    <i class="fas fa-edit"></i>
                </a>
            <?php else: ?>
                <a href="<?= base_url('equipos/asignar/' . $equipo['idserviciocontratado']) ?>" class="btn btn-sm btn-outline-success"
                    title="Asignar técnico" data-bs-toggle="tooltip">
                    <i class="fas fa-user-plus"></i>
                </a>
            <?php endif; ?>
            <a href="<?= base_url('servicios/' . $equipo['idserviciocontratado']) ?>" class="btn btn-sm btn-outline-info"
                title="Ver servicio completo" data-bs-toggle="tooltip">
                <i class="fas fa-eye"></i>
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Función para renderizar tarjeta de cliente con múltiples servicios
 */
function renderClienteCard(array $clienteData, string $estado): string
{
    // Mapeo local de colores e iconos - Paleta Vibrante
    $colores = [
        'Programado' => '#7c3aed',
        'Pendiente' => '#f59e0b',
        'En Proceso' => '#3b82f6',
        'Completado' => '#10b981'
    ];

    $iconos = [
        'Programado' => 'fas fa-calendar-alt',
        'Pendiente' => 'fas fa-clock',
        'En Proceso' => 'fas fa-spinner',
        'Completado' => 'fas fa-check-circle'
    ];

    $colorEstado = $colores[$estado] ?? '#6b7280';
    $iconoEstado = $iconos[$estado] ?? 'fas fa-question-circle';

    ob_start();
    ?>
    <div class="kanban-card kanban-card-cliente" data-cliente-id="<?= $clienteData['idcliente'] ?>"
        data-status="<?= $estado ?>" style="font-family: 'Poppins', sans-serif;">
        <!-- Header de tarjeta de cliente -->
        <div class="card-header-kanban"
            style="background: linear-gradient(135deg, #ff6b35, #ff4d00); color: white; padding: 12px; font-family: 'Poppins', sans-serif;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="font-size: 1.1rem; display: block; font-family: 'Poppins', sans-serif; font-weight: 700;">
                        <i class="fas fa-user-circle me-2"></i><?= esc($clienteData['cliente_nombre']) ?>
                    </strong>
                    <small style="opacity: 0.9; font-family: 'Poppins', sans-serif;">
                        <i class="fas fa-phone me-1"></i><?= esc($clienteData['cliente_telefono'] ?? 'Sin teléfono') ?>
                    </small>
                </div>
                <span class="badge bg-light text-dark" style="font-size: 0.85rem;">
                    <?= $clienteData['total_equipos'] ?> Servicio<?= $clienteData['total_equipos'] > 1 ? 's' : '' ?>
                </span>
            </div>
        </div>

        <!-- Cuerpo con lista de servicios -->
        <div class="card-body-kanban" style="padding: 10px;">
            <?php foreach ($clienteData['equipos'] as $index => $equipo): ?>
                <?php
                // Nombre corto del técnico
                $nombreCorto = !empty($equipo['nombre_completo'])
                    ? explode(' ', $equipo['nombre_completo'])[0] . ' ' . substr(explode(' ', $equipo['nombre_completo'])[1] ?? '', 0, 1) . '.'
                    : $equipo['nombreusuario'];
                ?>
                <div class="servicio-item-kanban" style="
                    margin-bottom: 8px; 
                    padding: 10px; 
                    background: #f8f9fa; 
                    border-radius: 8px; 
                    border-left: 4px solid 
                    <?php
                    if ($estado == 'Completado')
                        echo '#27AE60';
                    elseif ($estado == 'En Proceso')
                        echo '#E67E22';
                    else
                        echo '#FF9900';
                    ?>;
                    transition: all 0.2s ease;
                " onmouseover="this.style.background='#e9ecef'; this.style.transform='translateX(3px)';"
                    onmouseout="this.style.background='#f8f9fa'; this.style.transform='translateX(0)';">

                    <!-- Servicio y técnico -->
                    <div style="margin-bottom: 6px;">
                        <strong style="color: #2c3e50; font-size: 0.95rem; display: block;">
                            <i class="fas fa-briefcase me-1" style="color: #FF9900;"></i>
                            <?= esc($equipo['servicio']) ?>
                        </strong>
                        <small style="color: #7f8c8d; display: block; margin-top: 3px;">
                            <i class="fas fa-user me-1"></i><?= esc($nombreCorto) ?>
                            <span style="margin-left: 8px;">
                                <i class="fas fa-id-badge me-1"></i><?= esc($equipo['cargo']) ?>
                            </span>
                        </small>
                    </div>

                    <!-- Fecha y ubicación -->
                    <div style="font-size: 0.85rem; color: #6c757d;">
                        <div style="margin-bottom: 3px;">
                            <i class="fas fa-calendar me-1"></i>
                            <?= date('d/m/Y H:i', strtotime($equipo['fechahoraservicio'])) ?>
                        </div>
                        <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            <?= esc($equipo['direccion']) ?>
                        </div>
                    </div>

                    <!-- Acciones rápidas -->
                    <div style="margin-top: 8px; display: flex; gap: 6px;">
                        <a href="<?= base_url('equipos/editar/' . $equipo['idequipo']) ?>"
                            class="btn btn-sm btn-outline-primary" style="font-size: 0.75rem; padding: 3px 8px;"
                            title="Editar asignación">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?= base_url('servicios/' . $equipo['idserviciocontratado']) ?>"
                            class="btn btn-sm btn-outline-info" style="font-size: 0.75rem; padding: 3px 8px;"
                            title="Ver servicio">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer con acción para ver cliente -->
        <div style="padding: 10px; border-top: 1px solid #e9ecef; background: #f8f9fa;">
            <a href="<?= base_url('clientes/ver/' . $clienteData['idcliente']) ?>"
                class="btn btn-sm btn-outline-primary w-100" style="font-size: 0.85rem;">
                <i class="fas fa-user-circle me-1"></i>Ver Detalles del Cliente
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>
<script>
    const BASE_URL = '<?= base_url() ?>';
    const ES_TRABAJADOR = <?= isset($es_trabajador) && $es_trabajador ? 'true' : 'false' ?>;
    const USUARIO_ACTUAL_ID = <?= isset($usuario_actual_id) ? $usuario_actual_id : 'null' ?>;
    
    // Endpoint absoluto para actualizar estado (evita problemas de concatenación de URL)
    const ENDPOINT_ACTUALIZAR_ESTADO = '<?= base_url('equipos/actualizar-estado') ?>';
    
    <?php if (session()->getFlashdata('success')): ?>
        window.FLASH_SUCCESS = '<?= addslashes(session()->getFlashdata('success')) ?>';
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        window.FLASH_ERROR = '<?= addslashes(session()->getFlashdata('error')) ?>';
    <?php endif; ?>
</script>

<script src="<?= base_url('assets/js/modules/kanban/validation.js') ?>"></script>
<script src="<?= base_url('assets/js/modules/kanban/ui.js') ?>"></script>
<script src="<?= base_url('assets/js/modules/kanban/drag-drop.js') ?>"></script>
<script src="<?= base_url('assets/js/modules/kanban/search.js') ?>"></script>
<script src="<?= base_url('assets/js/modules/kanban/main.js') ?>"></script>

<?= $footer ?>