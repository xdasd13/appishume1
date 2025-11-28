<?= $header; ?>

<div class="page-inner">

    <!-- Tarjetas de estadísticas rápidas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-5">
                            <div class="icon-big text-center icon-success">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Reportes Disponibles</p>
                                <h4 class="card-title"><?= count($reportes_disponibles) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-5">
                            <div class="icon-big text-center icon-primary">
                                <i class="fas fa-filter"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Filtros Activos</p>
                                <h4 class="card-title" id="filtros-activos">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-5">
                            <div class="icon-big text-center icon-info">
                                <i class="fas fa-download"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Exportaciones</p>
                                <h4 class="card-title">PDF</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-5">
                            <div class="icon-big text-center icon-warning">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Última Actualización</p>
                                <h4 class="card-title"><?= date('H:i') ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel principal de reportes -->
    <div class="row">
        <!-- Panel de selección de reportes -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-list mr-2"></i>
                        Seleccionar Reporte
                    </h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="tipo-reporte">Tipo de Reporte</label>
                        <select class="form-control" id="tipo-reporte" name="tipo_reporte">
                            <option value="">-- Seleccionar reporte --</option>
                            <?php foreach ($reportes_disponibles as $key => $reporte): ?>
                                <option value="<?= $key ?>" 
                                        data-categoria="<?= $reporte['categoria'] ?>"
                                        data-descripcion="<?= $reporte['descripcion'] ?>"
                                        data-filtros="<?= implode(',', $reporte['filtros']) ?>">
                                    <?= $reporte['nombre'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filtros dinámicos -->
                    <div id="panel-filtros">
                        <h5 class="mb-3">
                            <i class="fas fa-filter mr-2"></i>
                            Filtros Disponibles
                        </h5>
                        <div id="filtros-dinamicos">
                            <!-- Los filtros se generarán dinámicamente aquí -->
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary btn-block" id="generar-reporte">
                                <i class="fas fa-play mr-2"></i>
                                Generar Reporte
                            </button>
                            <button type="button" class="btn btn-secondary btn-block" id="limpiar-filtros">
                                <i class="fas fa-eraser mr-2"></i>
                                Limpiar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de exportación -->
            <div class="card mt-4" id="panel-exportacion">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-download mr-2"></i>
                        Exportar Reporte
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <button type="button" class="btn btn-danger btn-block" id="exportar-pdf">
                                <i class="fas fa-file-pdf mr-2"></i>
                                Exportar PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de resultados -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">
                            <i class="fas fa-chart-line mr-2"></i>
                            Resultados del Reporte
                        </h4>
                        <div class="ml-auto">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="actualizar-reporte">
                                <i class="fas fa-sync-alt mr-1"></i>
                                Actualizar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Estado inicial -->
                    <div id="estado-inicial" class="text-center py-5">
                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Selecciona un reporte para comenzar</h5>
                        <p class="text-muted">Elige un tipo de reporte del panel izquierdo y configura los filtros según tus necesidades.</p>
                    </div>

                    <!-- Loading -->
                    <div id="loading-reporte" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Generando reporte...</span>
                        </div>
                        <p class="mt-3 text-muted">Generando reporte...</p>
                    </div>

                    <!-- Área de errores -->
                    <div id="area-errores">
                        <!-- Aquí se mostrarán los errores como texto -->
                    </div>

                    <!-- Contenido del reporte -->
                    <div id="contenido-reporte">
                        <!-- Aquí se cargará el contenido del reporte -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para vista detallada -->
    <div class="modal fade" id="modal-detalle" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye mr-2"></i>
                        Vista Detallada
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="contenido-detalle">
                        <!-- Contenido detallado -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="exportar-detalle">
                        <i class="fas fa-download mr-2"></i>
                        Exportar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts específicos para reportes se cargan al final -->

<?= $footer; ?>

<script>
$(document).ready(function() {
    let reporteActual = null;
    let filtrosActuales = {};

    // Datos de filtros base
    const filtrosBase = <?= json_encode($filtros_base) ?>;
    const reportesDisponibles = <?= json_encode($reportes_disponibles) ?>;

    // Debug: mostrar los reportes disponibles en consola
    console.log('Reportes disponibles:', reportesDisponibles);

    // Manejar selección de reporte
    $('#tipo-reporte').on('change', function() {
        const tipoReporte = $(this).val();
        
        console.log('Tipo de reporte seleccionado:', tipoReporte);
        
        if (tipoReporte) {
            reporteActual = tipoReporte;
            const reporte = reportesDisponibles[tipoReporte];
            
            console.log('Reporte encontrado:', reporte);
            
            // Verificar que el reporte existe
            if (!reporte) {
                console.error('Reporte no encontrado:', tipoReporte);
            }
            
            // Generar filtros dinámicos
            if (reporte && reporte.filtros) {
                generarFiltrosDinamicos(reporte.filtros);
                $('#panel-filtros').show();
                $('#panel-exportacion').show();
                console.log('Filtros generados:', reporte.filtros);
            }
            
            // Limpiar resultados anteriores
            limpiarResultados();
        } else {
            limpiarTodo();
        }
    });

    // Función para generar filtros dinámicos
    function generarFiltrosDinamicos(filtrosRequeridos) {
        const container = $('#filtros-dinamicos');
        container.empty();
        filtrosActuales = {};

        filtrosRequeridos.forEach(function(filtroKey) {
            const filtro = filtrosBase[filtroKey];
            if (filtro) {
                const div = $('<div class="form-group"></div>');
                
                // Label
                const label = $('<label></label>').text(filtro.label);
                div.append(label);

                // Input según el tipo
                let input;
                if (filtro.tipo === 'select') {
                    input = $('<select class="form-control"></select>');
                    input.attr('name', filtroKey);
                    
                    // Opciones
                    Object.keys(filtro.opciones).forEach(function(key) {
                        const option = $('<option></option>')
                            .val(key)
                            .text(filtro.opciones[key]);
                        
                        // Preseleccionar "todos" si existe
                        if (key === 'todos') {
                            option.attr('selected', 'selected');
                        }
                        
                        input.append(option);
                    });
                } else if (filtro.tipo === 'date') {
                    input = $('<input type="date" class="form-control">');
                    input.attr('name', filtroKey);
                    input.attr('placeholder', filtro.placeholder);
                } else {
                    input = $('<input type="text" class="form-control">');
                    input.attr('name', filtroKey);
                    input.attr('placeholder', filtro.placeholder);
                }

                div.append(input);
                container.append(div);

                // Manejar cambios en filtros
                input.on('change', function() {
                    filtrosActuales[filtroKey] = $(this).val();
                    actualizarContadorFiltros();
                });
            }
        });

        actualizarContadorFiltros();
    }

    // Actualizar contador de filtros activos
    function actualizarContadorFiltros() {
        const filtrosActivos = Object.values(filtrosActuales).filter(val => val && val !== 'todos' && val !== '').length;
        $('#filtros-activos').text(filtrosActivos);
    }

    // Generar reporte
    $('#generar-reporte').on('click', function() {
        if (!reporteActual) {
            Swal.fire('Error', 'Por favor selecciona un tipo de reporte', 'error');
            return;
        }

        mostrarLoading();
        
        // Recopilar filtros
        const filtros = {};
        $('#filtros-dinamicos input, #filtros-dinamicos select').each(function() {
            const valor = $(this).val();
            if (valor && valor !== 'todos') {
                filtros[$(this).attr('name')] = valor;
            }
        });

        // Obtener token CSRF dinámico
        const csrfTokenGenerar = $('meta[name="csrf-token"]').attr('content');
        const csrfNameGenerar = '<?= csrf_token() ?>';
        
        // Enviar petición AJAX
        $.ajax({
            url: '<?= base_url("reportes/generar") ?>',
            method: 'POST',
            data: {
                tipo_reporte: reporteActual,
                filtros: filtros,
                formato: 'html',
                [csrfNameGenerar]: csrfTokenGenerar || ''
            },
            success: function(response) {
                $('#loading-reporte').hide();
                $('#area-errores').hide().html(''); // Limpiar errores anteriores
                
                // Verificar si la respuesta es HTML (vista) o JSON
                if (typeof response === 'string') {
                    // Es una vista HTML
                    $('#contenido-reporte').html(response);
                    $('#contenido-reporte').show();
                    $('#estado-inicial').hide();
                } else {
                    // Es una respuesta JSON
                    if (response.success) {
                        // Reporte exitoso con datos
                        $('#contenido-reporte').html(response.html || 'Reporte generado correctamente');
                        $('#contenido-reporte').show();
                        $('#estado-inicial').hide();
                    } else if (response.sin_datos) {
                        // No hay datos para el reporte - mostrar como texto
                        mostrarErrorComoTexto('Sin Resultados', response.mensaje, 'info');
                        $('#estado-inicial').show();
                    } else {
                        // Error en la respuesta - mostrar como texto
                        mostrarErrorComoTexto('Error', response.error || 'Error al generar el reporte', 'error');
                        $('#estado-inicial').show();
                    }
                }
            },
            error: function(xhr) {
                $('#loading-reporte').hide();
                
                let mensajeError = 'Error al generar el reporte';
                
                // Intentar obtener el mensaje de error del servidor
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    mensajeError = xhr.responseJSON.error;
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.error) {
                            mensajeError = response.error;
                        }
                    } catch(e) {
                        // No es JSON, usar mensaje por defecto
                    }
                }
                
                // Manejar diferentes tipos de errores solo si no hay mensaje del servidor
                if (mensajeError === 'Error al generar el reporte') {
                    if (xhr.status === 403) {
                        mensajeError = 'No tiene permisos para generar reportes. Contacte al administrador.';
                    } else if (xhr.status === 500) {
                        mensajeError = 'Error interno del servidor. Intente nuevamente.';
                    } else if (xhr.status === 404) {
                        mensajeError = 'El servicio de reportes no está disponible.';
                    } else if (xhr.status === 0) {
                        mensajeError = 'Error de conexión. Verifique su conexión a internet.';
                    }
                }
                
                // Mostrar error como texto
                mostrarErrorComoTexto('Error', mensajeError, 'error');
                
                console.error('Error en reporte:', xhr);
                $('#estado-inicial').show();
                $('#loading-reporte').hide();
            }
        });
    });

    // Limpiar filtros
    $('#limpiar-filtros').on('click', function() {
        console.log('Limpiando filtros...');
        
        // Limpiar inputs de texto y fecha
        $('#filtros-dinamicos input[type="text"], #filtros-dinamicos input[type="date"]').val('');
        
        // Restablecer selects a "todos"
        $('#filtros-dinamicos select').each(function() {
            $(this).val('todos').trigger('change');
        });
        
        // Limpiar objeto de filtros
        filtrosActuales = {};
        actualizarContadorFiltros();
        
        console.log('Filtros limpiados');
    });

    // Actualizar reporte
    $('#actualizar-reporte').on('click', function() {
        if (reporteActual) {
            $('#generar-reporte').click();
        }
    });

    // Exportar PDF
    $('#exportar-pdf').on('click', function() {
        if (!reporteActual) {
            mostrarErrorComoTexto('Error', 'Por favor genera un reporte primero', 'error');
            return;
        }

        const filtros = {};
        $('#filtros-dinamicos input, #filtros-dinamicos select').each(function() {
            const valor = $(this).val();
            if (valor && valor !== 'todos') {
                filtros[$(this).attr('name')] = valor;
            }
        });

        // Mostrar loading
        mostrarErrorComoTexto('Exportando PDF', 'Generando archivo PDF...', 'info');

        // Obtener token CSRF del meta tag o del input hidden si existe
        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        const csrfName = '<?= csrf_token() ?>';
        
        // Si no está en el meta tag, buscar en inputs hidden
        if (!csrfToken) {
            csrfToken = $('input[name="' + csrfName + '"]').val();
        }
        
        // Debug: verificar token
        if (!csrfToken) {
            console.error('CSRF Token no encontrado');
            mostrarErrorComoTexto('Error', 'No se pudo obtener el token de seguridad. Por favor, recargue la página.', 'error');
            return;
        }
        
        console.log('Token CSRF encontrado:', csrfToken.substring(0, 20) + '...');
        console.log('Nombre CSRF:', csrfName);
        
        // Preparar datos de la petición (sin CSRF ya que está excluido)
        const postData = {
            tipo_reporte: reporteActual,
            filtros: JSON.stringify(filtros)
        };
        
        // Hacer petición AJAX para exportar PDF
        $.ajax({
            url: '<?= base_url("reportes/exportarPDF") ?>',
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: postData,
            xhrFields: {
                responseType: 'blob' // Esperar respuesta binaria
            },
            success: function(data, status, xhr) {
                // Ocultar mensaje de carga
                $('#area-errores').hide();
                
                // Verificar si es un PDF o un error JSON
                const contentType = xhr.getResponseHeader('Content-Type');
                
                if (contentType && contentType.includes('application/pdf')) {
                    // Es un PDF, crear blob y descargar
                    const blob = new Blob([data], { type: 'application/pdf' });
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    
                    // Obtener nombre del archivo del header Content-Disposition
                    const contentDisposition = xhr.getResponseHeader('Content-Disposition');
                    let filename = 'reporte.pdf';
                    if (contentDisposition) {
                        const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                        if (filenameMatch) {
                            filename = filenameMatch[1];
                        }
                    }
                    
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                    
                    // Mostrar mensaje de éxito
                    mostrarErrorComoTexto('Éxito', 'PDF descargado correctamente', 'success');
                    setTimeout(() => {
                        $('#area-errores').hide();
                    }, 3000);
                } else if (contentType && contentType.includes('application/json')) {
                    // Es un error JSON
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        try {
                            const jsonResponse = JSON.parse(e.target.result);
                            if (jsonResponse.error) {
                                mostrarErrorComoTexto('Error', jsonResponse.error, 'error');
                            }
                        } catch (err) {
                            mostrarErrorComoTexto('Error', 'Error al procesar la respuesta del servidor', 'error');
                        }
                    };
                    reader.readAsText(data);
                } else {
                    // Respuesta inesperada
                    mostrarErrorComoTexto('Error', 'Respuesta del servidor no reconocida', 'error');
                    console.error('Respuesta inesperada:', contentType);
                }
            },
            error: function(xhr, status, error) {
                let mensajeError = 'Error al exportar PDF';
                let respuesta = null;
                
                try {
                    respuesta = JSON.parse(xhr.responseText);
                } catch (e) {
                    // Si no es JSON, podría ser HTML con un mensaje de error
                    const responseText = xhr.responseText || '';
                    if (responseText.includes('administrador') || responseText.includes('Debe iniciar')) {
                        mensajeError = 'Su sesión ha expirado o no tiene los permisos necesarios. Por favor, recargue la página.';
                    }
                }
                
                if (xhr.status === 403) {
                    if (respuesta && respuesta.message) {
                        mensajeError = respuesta.message;
                    } else {
                        // Intentar obtener mensaje del HTML si está disponible
                        const responseText = xhr.responseText || '';
                        if (responseText.includes('administrador') || responseText.includes('Debe iniciar')) {
                            mensajeError = 'Su sesión ha expirado. Por favor, recargue la página e inicie sesión nuevamente.';
                        } else {
                            mensajeError = 'Acceso denegado. Verifique que tenga permisos para exportar reportes.';
                        }
                    }
                } else if (xhr.status === 401) {
                    mensajeError = 'Su sesión ha expirado. Por favor, recargue la página e inicie sesión nuevamente.';
                } else if (xhr.status === 500) {
                    mensajeError = 'Error interno del servidor al exportar PDF. Por favor, intente nuevamente.';
                } else if (xhr.status === 0) {
                    mensajeError = 'Error de conexión. Verifique su conexión a internet.';
                }
                
                console.error('Error en exportación PDF:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    response: xhr.responseText,
                    error: error
                });
                
                mostrarErrorComoTexto('Error', mensajeError, 'error');
            }
        });
    });

    // Exportar Excel
    $('#exportar-excel').on('click', function() {
        if (!reporteActual) {
            mostrarErrorComoTexto('Error', 'Por favor genera un reporte primero', 'error');
            return;
        }

        const filtros = {};
        $('#filtros-dinamicos input, #filtros-dinamicos select').each(function() {
            const valor = $(this).val();
            if (valor && valor !== 'todos') {
                filtros[$(this).attr('name')] = valor;
            }
        });

        // Mostrar loading
        mostrarErrorComoTexto('Exportando Excel', 'Generando archivo Excel...', 'info');

        // Crear formulario temporal para descarga (para archivos binarios)
        const form = $('<form method="POST" action="<?= base_url("reportes/exportarExcel") ?>" target="_blank"></form>');
        form.append($('<input type="hidden" name="tipo_reporte">').val(reporteActual));
        form.append($('<input type="hidden" name="filtros">').val(JSON.stringify(filtros)));
        form.append($('<input type="hidden" name="<?= csrf_token() ?>">').val('<?= csrf_hash() ?>'));
        $('body').append(form);
        form.submit();
        form.remove();
        
        // Ocultar mensaje de loading después de un momento
        setTimeout(function() {
            $('#area-errores').hide();
        }, 2000);
    });

    // Funciones auxiliares
    function mostrarLoading() {
        $('#loading-reporte').show();
        $('#contenido-reporte').hide();
        $('#estado-inicial').hide();
    }

    function limpiarResultados() {
        $('#contenido-reporte').hide();
        $('#estado-inicial').show();
    }

    function limpiarTodo() {
        $('#panel-filtros').hide();
        $('#panel-exportacion').hide();
        $('#contenido-reporte').hide();
        $('#estado-inicial').show();
        $('#filtros-activos').text('0');
        reporteActual = null;
        filtrosActuales = {};
        console.log('Todo limpiado');
    }

    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    console.log('Sistema de reportes dinámicos inicializado correctamente');
});

// Función para mostrar errores como texto en lugar de modales
function mostrarErrorComoTexto(titulo, mensaje, tipo) {
    // Ocultar otros elementos
    $('#contenido-reporte').hide();
    $('#estado-inicial').hide();
    $('#loading-reporte').hide();
    
    // Determinar clase CSS según el tipo
    let claseAlerta = 'alert-danger';
    let tipoClase = 'alert-danger-custom';
    let icono = 'fas fa-exclamation-triangle';
    
    if (tipo === 'info') {
        claseAlerta = 'alert-info';
        tipoClase = 'alert-info-custom';
        icono = 'fas fa-info-circle';
    } else if (tipo === 'warning') {
        claseAlerta = 'alert-warning';
        tipoClase = 'alert-warning-custom';
        icono = 'fas fa-exclamation-triangle';
    } else if (tipo === 'success') {
        claseAlerta = 'alert-success';
        tipoClase = 'alert-success-custom';
        icono = 'fas fa-check-circle';
    }
    
    // Crear HTML del error usando clases CSS
    let htmlError = `
        <div class="alert ${claseAlerta} ${tipoClase} alert-dismissible fade show shadow-sm alert-custom" role="alert">
            <div class="d-flex align-items-start">
                <i class="${icono} fa-3x mr-3 alert-icon-custom"></i>
                <div class="flex-grow-1">
                    <h4 class="alert-heading mb-3 font-weight-bold alert-title-custom">
                        ${titulo}
                    </h4>
                    <div class="error-message alert-message-custom">
                        ${mensaje}
                    </div>
                </div>
                <button type="button" class="close ml-3 alert-close-custom" data-dismiss="alert" aria-label="Cerrar">
                    <span aria-hidden="true" class="alert-close-icon">&times;</span>
                </button>
            </div>
        </div>
    `;
    
    // Mostrar el error
    $('#area-errores').html(htmlError).show().removeClass('hidden');
    
    // Auto-ocultar después de 5 segundos solo si es info
    if (tipo === 'info') {
        setTimeout(function() {
            $('#area-errores .alert').fadeOut(500, function() {
                $(this).remove();
                if ($('#area-errores').html().trim() === '') {
                    $('#area-errores').hide();
                }
            });
        }, 5000);
    }
}
</script>
