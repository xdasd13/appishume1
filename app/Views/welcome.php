<?= $header ?>
<?php
use App\Models\ProyectoModel;
use App\Models\ControlPagoModel;
use App\Models\InventarioEquipoModel;
use App\Models\EntregasModel;
use App\Models\ContratoModel;

// Instanciar modelos
$proyectoModel = new ProyectoModel();
$controlPagoModel = new ControlPagoModel();
$inventarioModel = new InventarioEquipoModel();
$entregasModel = new EntregasModel();
$contratoModel = new ContratoModel();

// 1. Obtener Datos Crudos
$statsProyectos = $proyectoModel->getEstadisticasProyectos();
$statsPagos = $controlPagoModel->obtenerEstadisticasPagos();
$statsInventario = $inventarioModel->getEstadisticas();
$contratosDeuda = $contratoModel->obtenerContratosConDeuda();
$proyectosActivos = $proyectoModel->getProyectosActivos();
$todasEntregas = $entregasModel->obtenerTodasLasEntregas();

// 2. Calcular Métricas Principales
$deudaTotal = $statsPagos['deuda_total'] ?? 0;
$numProyectosActivos = $statsProyectos->proyectos_activos ?? 0;
$equiposDisponibles = $statsInventario['total_disponible'] ?? 0;
$totalEquipos = $statsInventario['total'] ?? 1; // Evitar div por cero

// 3. Calcular KPIs y Entregas
$entregasPendientes = 0;
$entregasATiempo = 0;
$totalEntregasCount = count($todasEntregas);

foreach ($todasEntregas as $entrega) {
    // Contar pendientes
    if ($entrega['estado'] == 'pendiente') {
        $entregasPendientes++;
    }

    // Calcular eficiencia (simplificada)
    $fechaLimite = strtotime($entrega['fechahoraentrega']);
    $fechaReal = !empty($entrega['fecha_real_entrega']) ? strtotime($entrega['fecha_real_entrega']) : time();

    if ($fechaReal <= $fechaLimite) {
        $entregasATiempo++;
    }
}

// Fórmulas de KPIs
$tasaCobro = ($statsPagos['total_pagado'] + $deudaTotal) > 0
    ? ($statsPagos['total_pagado'] / ($statsPagos['total_pagado'] + $deudaTotal)) * 100
    : 0;

$utilizacionInventario = (($totalEquipos - $equiposDisponibles) / $totalEquipos) * 100;

$eficienciaEntrega = ($totalEntregasCount > 0)
    ? ($entregasATiempo / $totalEntregasCount) * 100
    : 100;

// Datos para gráficos
$equiposEnUso = $totalEquipos - $equiposDisponibles;
?>

<!-- Estilos Específicos del Dashboard -->
<style>
    :root {
        --primary-color: #4e73df;
        --success-color: #1cc88a;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --dark-bg: #f8f9fc;
        --card-bg: #ffffff;
        --text-gray: #5a5c69;
    }

    body {
        background-color: var(--dark-bg);
        font-family: 'Nunito', sans-serif;
    }

    .dashboard-container {
        padding: 20px;
        max-width: 100%;
    }

    /* Grid Layout */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }

    /* Cards */
    .stat-card {
        background: var(--card-bg);
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border-left: 5px solid var(--primary-color);
        transition: transform 0.2s;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    .stat-card.border-success {
        border-left-color: var(--success-color);
    }

    .stat-card.border-warning {
        border-left-color: var(--warning-color);
    }

    .stat-card.border-danger {
        border-left-color: var(--danger-color);
    }

    .stat-text h6 {
        color: var(--text-gray);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        margin-bottom: 5px;
    }

    .stat-text .value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #333;
    }

    .stat-icon {
        font-size: 2rem;
        color: #dddfeb;
    }

    /* Main Content Sections */
    .main-section {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 25px;
    }

    .content-card {
        background: var(--card-bg);
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .card-header {
        background: #f8f9fc;
        padding: 15px 20px;
        border-bottom: 1px solid #e3e6f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header h5 {
        margin: 0;
        font-weight: 700;
        color: var(--primary-color);
    }

    .card-body {
        padding: 20px;
    }

    /* Tables */
    .table-modern {
        width: 100%;
        border-collapse: collapse;
    }

    .table-modern th {
        text-align: left;
        padding: 12px;
        background-color: #f8f9fc;
        color: var(--text-gray);
        font-size: 0.85rem;
        font-weight: 700;
    }

    .table-modern td {
        padding: 12px;
        border-bottom: 1px solid #e3e6f0;
        font-size: 0.9rem;
    }

    .status-badge {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .bg-primary-light {
        background-color: #e3e6f0;
        color: #4e73df;
    }

    .bg-success-light {
        background-color: #d4edda;
        color: #155724;
    }

    .bg-warning-light {
        background-color: #fff3cd;
        color: #856404;
    }

    .bg-danger-light {
        background-color: #f8d7da;
        color: #721c24;
    }

    /* Action Buttons */
    .action-bar {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
    }

    .btn-action {
        flex: 1;
        padding: 15px;
        border: none;
        border-radius: 8px;
        color: white;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: opacity 0.2s;
        text-decoration: none;
    }

    .btn-action:hover {
        opacity: 0.9;
        color: white;
    }

    .btn-primary {
        background: linear-gradient(45deg, #4e73df, #224abe);
    }

    .btn-success {
        background: linear-gradient(45deg, #1cc88a, #13855c);
    }

    .btn-info {
        background: linear-gradient(45deg, #36b9cc, #258391);
    }

    /* Charts Section */
    .charts-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .main-section {
            grid-template-columns: 1fr;
        }

        .charts-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .action-bar {
            flex-direction: column;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="dashboard-container">

    <!-- 1. Acciones Rápidas -->
    <div class="action-bar">
        <a href="<?= base_url('cronograma/proyectos') ?>" class="btn-action btn-primary">
            <i class="fas fa-plus-circle"></i> Nuevo Proyecto
        </a>
        <a href="<?= base_url('controlpagos/crear') ?>" class="btn-action btn-success">
            <i class="fas fa-money-bill-wave"></i> Registrar Pago
        </a>
        <a href="<?= base_url('entregas/crear') ?>" class="btn-action btn-info">
            <i class="fas fa-box-open"></i> Registrar Entrega
        </a>
    </div>

    <!-- 2. Métricas Estratégicas (KPI Cards) -->
    <div class="dashboard-grid">
        <!-- Deuda Total -->
        <div class="stat-card border-danger">
            <div class="stat-text">
                <h6>Deuda por Cobrar</h6>
                <div class="value text-danger">S/ <?= number_format($deudaTotal, 2) ?></div>
                <small class="text-muted">Tasa de Cobro: <?= number_format($tasaCobro, 1) ?>%</small>
            </div>
            <div class="stat-icon text-danger">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
        </div>

        <!-- Proyectos Activos -->
        <div class="stat-card border-primary">
            <div class="stat-text">
                <h6>Proyectos Activos</h6>
                <div class="value text-primary"><?= $numProyectosActivos ?></div>
                <small class="text-muted">En ejecución</small>
            </div>
            <div class="stat-icon text-primary">
                <i class="fas fa-project-diagram"></i>
            </div>
        </div>

        <!-- Equipos Disponibles -->
        <div class="stat-card border-success">
            <div class="stat-text">
                <h6>Equipos Disponibles</h6>
                <div class="value text-success"><?= $equiposDisponibles ?></div>
                <small class="text-muted">Utilización: <?= number_format($utilizacionInventario, 1) ?>%</small>
            </div>
            <div class="stat-icon text-success">
                <i class="fas fa-camera"></i>
            </div>
        </div>

        <!-- Entregas Pendientes -->
        <div class="stat-card border-warning">
            <div class="stat-text">
                <h6>Entregas Pendientes</h6>
                <div class="value text-warning"><?= $entregasPendientes ?></div>
                <small class="text-muted">Eficiencia: <?= number_format($eficienciaEntrega, 1) ?>%</small>
            </div>
            <div class="stat-icon text-warning">
                <i class="fas fa-shipping-fast"></i>
            </div>
        </div>
    </div>

    <!-- 3. Sección Central -->
    <div class="main-section">
        <!-- Tabla Izquierda: Próximos Eventos -->
        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-calendar-alt"></i> Próximos Eventos</h5>
                <a href="<?= base_url('cronograma') ?>" class="btn btn-sm btn-primary">Ver Todo</a>
            </div>
            <div class="card-body p-0">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($proyectosActivos)): ?>
                            <?php foreach (array_slice($proyectosActivos, 0, 5) as $proyecto): ?>
                                <tr>
                                    <td><?= esc($proyecto->cliente) ?></td>
                                    <td><?= esc($proyecto->servicio) ?></td>
                                    <td><?= date('d/m/Y', strtotime($proyecto->fechahoraservicio)) ?></td>
                                    <td>
                                        <span class="status-badge bg-primary-light text-primary">
                                            <?= esc($proyecto->estado) ?> (<?= $proyecto->progreso ?>%)
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center p-3">No hay proyectos activos</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabla Derecha: Top Deudores -->
        <div class="content-card">
            <div class="card-header">
                <h5 class="text-danger"><i class="fas fa-exclamation-circle"></i> Top Deudores</h5>
            </div>
            <div class="card-body p-0">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Deuda</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($contratosDeuda)): ?>
                            <?php foreach (array_slice($contratosDeuda, 0, 5) as $contrato): ?>
                                <tr>
                                    <td>
                                        <?= esc($contrato['nombres'] ?? $contrato['razonsocial']) ?>
                                        <br><small class="text-muted">Contrato #<?= $contrato['idcontrato'] ?></small>
                                    </td>
                                    <td class="text-danger font-weight-bold">
                                        S/ <?= number_format($contrato['saldo_actual'], 2) ?>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('controlpagos/ver/' . $contrato['idcontrato']) ?>"
                                            class="btn btn-sm btn-outline-danger" title="Cobrar">
                                            <i class="fas fa-dollar-sign"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center p-3">¡Todo al día! 🎉</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 4. Gráficos -->
    <div class="charts-grid">
        <div class="content-card">
            <div class="card-header">
                <h5>Resumen Financiero</h5>
            </div>
            <div class="card-body">
                <canvas id="financeChart"></canvas>
            </div>
        </div>
        <div class="content-card">
            <div class="card-header">
                <h5>Estado del Inventario</h5>
            </div>
            <div class="card-body">
                <canvas id="inventoryChart"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- Scripts para Gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de Inventario
    const ctxInv = document.getElementById('inventoryChart').getContext('2d');
    new Chart(ctxInv, {
        type: 'doughnut',
        data: {
            labels: ['Disponible', 'En Uso', 'Mantenimiento'],
            datasets: [{
                data: [
                    <?= $equiposDisponibles ?>,
                    <?= $equiposEnUso ?>,
                    0 // Ajustar si hay dato de mantenimiento
                ],
                backgroundColor: ['#1cc88a', '#4e73df', '#e74a3b'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Gráfico Financiero (Simulado con datos reales acumulados)
    const ctxFin = document.getElementById('financeChart').getContext('2d');
    new Chart(ctxFin, {
        type: 'bar',
        data: {
            labels: ['Total'],
            datasets: [
                {
                    label: 'Pagado',
                    data: [<?= $statsPagos['total_pagado'] ?>],
                    backgroundColor: '#1cc88a'
                },
                {
                    label: 'Por Cobrar',
                    data: [<?= $deudaTotal ?>],
                    backgroundColor: '#e74a3b'
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

<!-- SweetAlert Logic (Mantener lo existente) -->
<script>
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });

    <?php if (session()->getFlashdata('success')): ?>
        Toast.fire({
            icon: "success",
            title: "¡Bienvenido!",
            text: "<?= addslashes(session()->getFlashdata('success')) ?>",
            customClass: {
                popup: 'swal-success-popup',
                title: 'swal-success-title',
                content: 'swal-success-text'
            }
        });
    <?php endif; ?>

    // Detector de sesión expirada
    let sessionCheckInterval;

    function checkSessionStatus() {
        fetch('<?= base_url('auth/check-session') ?>', {
            method: 'GET',
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (!data.valid) {
                    clearInterval(sessionCheckInterval);
                    showSessionExpiredAlert();
                }
            })
            .catch(error => {
                console.log('Error verificando sesión:', error);
            });
    }

    function showSessionExpiredAlert() {
        Swal.fire({
            title: 'Sesión Expirada',
            text: 'Tu sesión ha expirado por inactividad. Serás redirigido al login.',
            icon: 'warning',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: true,
            confirmButtonText: 'Ir al Login',
            confirmButtonColor: '#FF6B00',
            background: '#ffffff',
            customClass: {
                popup: 'session-expired-popup',
                title: 'session-expired-title',
                content: 'session-expired-text'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= base_url('login') ?>';
            }
        });
    }

    // Iniciar verificación de sesión cada 5 minutos
    sessionCheckInterval = setInterval(checkSessionStatus, 300000);
</script>

<?= $footer ?>