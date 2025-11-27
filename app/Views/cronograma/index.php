<?= $header ?>
<link rel="stylesheet" href="<?= base_url('assets/css/cronograma-index.css') ?>">
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />


<div class="container">
    <div style="display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-clipboard-list" style="font-size: 24px;"></i>
        <h1 style="margin: 0;">Cronograma de Servicios</h1>
    </div>
    <br>

    <!-- ESTADÍSTICAS RÁPIDAS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $servicios_count ?? 0 ?></h3>
                    <p>Servicios activos</p>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $equipos ?? 0 ?></h3>
                    <p>Equipos asignados</p>
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-icon">
                    <i class="fas fa-user-cog"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $tecnicos ?? 0 ?></h3>
                    <p>Personal disponible</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendario -->
    <div class="card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Lista de próximos servicios -->
    <div class="card">
        <div class="card-header">
            <h2>Próximos Servicios</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-calendar-day"></i> Fecha</th>
                            <th><i class="fas fa-user"></i> Cliente</th>
                            <th><i class="fas fa-briefcase"></i> Servicio</th>
                            <th><i class="fas fa-map-marker-alt"></i> Lugar</th>
                            <th><i class="fas fa-info-circle"></i> Estado</th>
                            <th><i class="fas fa-cogs"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proximos as $servicio): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($servicio->fechahoraservicio)) ?></td>
                                <td><?= $servicio->cliente ?></td>
                                <td><?= $servicio->servicio ?></td>
                                <td><?= $servicio->direccion ?></td>
                                <td>
                                    <?php
                                    $badgeMap = [
                                        'planificación' => 'badge-planificacion',
                                        'producción' => 'badge-produccion',
                                        'postproducción' => 'badge-postproduccion',
                                        'finalizado' => 'badge-finalizado'
                                    ];
                                    $estado = strtolower($servicio->estado);
                                    $badgeClass = $badgeMap[$estado] ?? 'badge-planificacion';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= ucfirst($servicio->estado) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('equipos/por-servicio/' . $servicio->idserviciocontratado) ?>"
                                        class="btn btn-orange">
                                        <i class="fas fa-users"></i> Equipos
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'es',
            initialView: 'dayGridMonth',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: {
                url: '<?= base_url('cronograma/eventos') ?>',
                failure: function () {
                    console.error('Error al cargar eventos del calendario');
                    alert('Error al cargar los eventos del calendario. Verifique la conexión.');
                }
            },
            eventDidMount: function (info) {
                console.log('Evento cargado:', info.event.title, info.event.start);
            },
            eventsSet: function (events) {
                console.log('Total eventos cargados:', events.length);
                if (events.length === 0) {
                    console.warn('No se encontraron eventos para mostrar en el calendario');
                }
            },
            eventClick: function (info) {
                const evento = info.event.extendedProps;
                Swal.fire({
                    icon: 'info',
                    title: info.event.title,
                    html: `<b>Fecha:</b> ${info.event.start.toLocaleString()}<br>
                       <b>Lugar:</b> ${evento.direccion}<br>
                       <b>Teléfono:</b> ${evento.telefono}`,
                    confirmButtonColor: '#ff9800'
                });
            },
            dateClick: function (info) {
                Swal.fire({
                    title: 'Crear servicio',
                    text: `¿Deseas crear un servicio para el ${info.dateStr}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ff9800',
                    cancelButtonColor: '#222222ff',
                    confirmButtonText: 'Sí, crear',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '<?= base_url('servicios/crear') ?>?fecha=' + info.dateStr;
                    }
                });
            }
        });
        calendar.render();
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?= $footer ?>