<?= $header ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i><?= $titulo ?></h4>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= base_url('equipos/saveEquipo') ?>" id="equipoForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="idequipo" value="<?= $equipo['idequipo'] ?>">
                        <input type="hidden" name="idserviciocontratado" value="<?= $equipo['idserviciocontratado'] ?>">

                        <div class="mb-3">
                            <label for="idusuario" class="form-label">
                                <i class="fas fa-user me-2 text-primary"></i>Usuario/Técnico
                            </label>
                            <select class="form-select" id="idusuario" name="idusuario" required>
                                <option value="">Seleccionar usuario</option>
                                <?php foreach ($tecnicos as $tecnico): ?>
                                    <option value="<?= $tecnico['idusuario'] ?>"
                                        <?= $tecnico['idusuario'] == $equipo['idusuario'] ? 'selected' : '' ?>>
                                        <?= $tecnico['nombreCompleto'] . ' (' . $tecnico['cargo'] . ')' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">
                                <i class="fas fa-clipboard-list me-2 text-primary"></i>Descripción del Equipo
                            </label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                                required><?= $equipo['descripcion'] ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="estadoservicio" class="form-label">
                                <i class="fas fa-tasks me-2 text-primary"></i>Fase del Servicio
                            </label>
                            <select class="form-select" id="estadoservicio" name="estadoservicio" required>
                                <option value="Planificación" <?= $equipo['estadoservicio'] == 'Planificación' ? 'selected' : '' ?>>
                                    <i class="fas fa-calendar-alt"></i> Planificación
                                </option>
                                <option value="Producción" <?= $equipo['estadoservicio'] == 'Producción' ? 'selected' : '' ?>>
                                    <i class="fas fa-video"></i> Producción
                                </option>
                                <option value="Postproducción" <?= $equipo['estadoservicio'] == 'Postproducción' ? 'selected' : '' ?>>
                                    <i class="fas fa-magic"></i> Postproducción
                                </option>
                                <option value="Finalizado" <?= $equipo['estadoservicio'] == 'Finalizado' ? 'selected' : '' ?>>
                                    <i class="fas fa-check-circle"></i> Finalizado
                                </option>
                            </select>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="<?= base_url('equipos') ?>" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="btnActualizar">
                                <i class="fas fa-save me-2"></i>Actualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('equipoForm');
        const btnActualizar = document.getElementById('btnActualizar');
        const estadoOriginal = document.getElementById('estadoservicio').value;

        // Mostrar notificaciones de flash messages
        <?php if (session()->getFlashdata('success')): ?>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '<?= addslashes(session()->getFlashdata('success')) ?>',
                confirmButtonColor: '#28a745'
            });
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?= addslashes(session()->getFlashdata('error')) ?>',
                confirmButtonColor: '#dc3545'
            });
        <?php endif; ?>

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const estadoNuevo = document.getElementById('estadoservicio').value;
            const usuario = document.getElementById('idusuario');
            const usuarioTexto = usuario.options[usuario.selectedIndex].text;

            // Verificar si hay cambios significativos
            let mensaje = '¿Deseas actualizar la información del equipo?';
            let icono = 'question';

            if (estadoOriginal !== estadoNuevo) {
                mensaje = `¿Deseas cambiar el estado del equipo de "${estadoOriginal}" a "${estadoNuevo}"?`;
                icono = estadoNuevo === 'Completado' ? 'success' : estadoNuevo === 'En Proceso' ? 'info' : 'warning';
            }

            const result = await Swal.fire({
                title: 'Confirmar Actualización',
                text: mensaje,
                icon: icono,
                showCancelButton: true,
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d'
            });

            if (result.isConfirmed) {
                // Mostrar loading
                btnActualizar.disabled = true;
                btnActualizar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Actualizando...';

                // Enviar formulario
                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    // Parsear respuesta JSON
                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Guardar mensaje en sessionStorage para mostrarlo después del redirect
                        sessionStorage.setItem('toast_message', data.message || 'Cambio realizado correctamente');
                        sessionStorage.setItem('toast_icon', 'success');

                        // Redirigir inmediatamente
                        window.location.href = data.redirect || '<?= base_url('equipos') ?>';
                    } else {
                        throw new Error(data.message || 'Error en la respuesta del servidor');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Ocurrió un error al actualizar el equipo',
                        confirmButtonColor: '#dc3545'
                    });

                    // Restaurar botón
                    btnActualizar.disabled = false;
                    btnActualizar.innerHTML = '<i class="fas fa-save me-2"></i>Actualizar';
                }
            }
        });
    });
</script>

<?= $footer ?>