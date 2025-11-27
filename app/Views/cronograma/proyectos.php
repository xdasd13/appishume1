<?= $header ?>
<link rel="stylesheet" href="<?= base_url('assets/css/cronograma-proyectos.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/maplibre-routes.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
<!-- MapLibre GL -->
<link rel="stylesheet" href="https://unpkg.com/maplibre-gl@3.6.1/dist/maplibre-gl.css"
    crossorigin="anonymous" referrerpolicy="no-referrer">
<script src="https://unpkg.com/maplibre-gl@3.6.1/dist/maplibre-gl.js" crossorigin="anonymous" defer></script>

<script>
    window.ORS_API_KEY = '<?= esc(getenv('ORS_API_KEY') ?? '') ?>';
    window.DEFAULT_PROJECT_LOCATION = {
        name: 'Av. Luis Massaro 791, Chincha Alta 11702',
        lat: -13.4192,
        lng: -76.1325
    };
</script>

<div class="container">
    <div class="dashboard-header">
        <div class="dashboard-title">
            <i class="fa-solid fa-clipboard-list dashboard-icon"></i>
            <h1>Proyectos Activos</h1>
        </div>
        <p class="dashboard-subtitle">Gestiona y supervisa todos tus proyectos audiovisuales en tiempo real</p>
    </div>

    <div class="projects-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-tasks"></i>Proyectos en Curso
                <span class="projects-count"><?= count($proyectos) ?></span>
            </h2>
            <a href="<?= base_url('proyectos') ?>" class="view-all">
                Ver todos <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="projects-grid">
            <?php if (!empty($proyectos)): ?>
                <?php foreach ($proyectos as $proyecto): ?>
                    <div class="project-card">
                        <div class="project-header">
                            <span class="project-status status-activo">
                                <?= $proyecto['total_servicios'] ?> Servicio<?= $proyecto['total_servicios'] > 1 ? 's' : '' ?>
                            </span>
                            <h3 class="project-title"><?= esc($proyecto['cliente']) ?></h3>
                            <p class="project-client">
                                <i class="fas fa-phone me-1"></i><?= esc($proyecto['telefono_cliente'] ?? 'Sin teléfono') ?>
                            </p>
                        </div>

                        <div class="project-body">
                            <!-- Lista de servicios contratados -->
                            <div class="project-detail">
                                <div class="detail-icon">
                                    <i class="fas fa-list"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Servicios Contratados</div>
                                    <div class="detail-value">
                                        <?php foreach ($proyecto['servicios'] as $index => $servicio): ?>
                                            <?php
                                            $mapColors = [
                                                'Finalizado' => '#10b981',
                                                'Postproducción' => '#f59e0b',
                                                'Producción' => '#3b82f6',
                                                'Planificación' => '#7c3aed'
                                            ];
                                            $colorEstado = $mapColors[$servicio['estado']] ?? '#7c3aed';
                                            ?>
                                            <div class="servicio-item" style="margin-bottom: 8px; padding: 8px; background: #f8f9fa; border-radius: 6px; border-left: 3px solid <?= $colorEstado ?>;">

                                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px;">
                                                    <div>
                                                        <strong style="color: #2c3e50; font-size: 0.95rem;">
                                                            <i class="fas fa-check-circle me-1" style="font-size: 0.8rem;"></i>
                                                            <?= esc($servicio['servicio']) ?>
                                                        </strong>
                                                        <small style="display:block;color:#7f8c8d;margin-top:4px;">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            <?= date('d/m/Y H:i', strtotime($servicio['fechahoraservicio'])) ?>
                                                        </small>
                                                    </div>
                                                    <div style="display:flex; align-items:center; gap:8px;">
                                                        <span class="badge" style="font-size: 0.7rem; padding: 3px 8px; background: <?= $colorEstado ?>; color: white;">
                                                            <?= esc($servicio['estado']) ?>
                                                        </span>

                                                        <a href="<?= base_url('equipos/asignar/' . $servicio['idserviciocontratado']) ?>" class="btn btn-sm btn-outline-primary"
                                                            title="Asignar técnico" style="white-space: nowrap;">
                                                            <i class="fas fa-user-plus"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="project-detail">
                                <div class="detail-icon">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Fecha Más Próxima</div>
                                    <div class="detail-value">
                                        <?= date('d/m/Y H:i', strtotime($proyecto['fecha_mas_proxima'])) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="project-detail">
                                <div class="detail-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Ubicación Principal</div>
                                    <div class="detail-value"
                                        style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                                        <a href="#" class="location-link"
                                            onclick="MapLibreRoutes.openRoute('<?= addslashes(esc($proyecto['direccion_principal'])) ?>'); return false;"
                                            title="Ver ruta hacia esta ubicación">
                                            <i class="fas fa-directions"></i><?= esc($proyecto['direccion_principal']) ?>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="progress-container">
                                <div class="progress-info">
                                    <span class="progress-label">Progreso Promedio</span>
                                    <span class="progress-percentage"><?= $proyecto['progreso_promedio'] ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $proyecto['progreso_promedio'] ?>%"></div>
                                </div>
                            </div>

                            <div class="project-actions">
                                <a href="<?= base_url('clientes/ver/' . $proyecto['idcliente']) ?>"
                                    class="project-btn btn-blue">
                                    <i class="fas fa-user"></i> Ver Cliente
                                </a>
                                <a href="<?= base_url('cronograma') ?>" class="project-btn btn-orange">
                                    <i class="fas fa-calendar"></i> Cronograma
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3 class="empty-text">No hay proyectos activos en este momento</h3>
                    <a href="<?= base_url('servicios/crear') ?>" class="project-btn btn-primary"
                        style="display: inline-flex; width: auto;">
                        <i class="fas fa-plus"></i> Crear Primer Proyecto
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/js/maplibre-routes.js') ?>"></script>
<?= $footer ?>