<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EquipoModel;
use App\Services\EquipoService;
use App\Services\NotificationService;
use App\Services\DueReminderService;

use App\Libraries\HistorialHelper;
use App\Models\HistorialActividadesModel;

/**
 * Controlador de Equipos refactorizado aplicando KISS
 * Responsabilidades claras: solo manejo de flujo HTTP
 */
class Equipos extends BaseController
{
    protected EquipoModel $equipoModel;
    protected EquipoService $equipoService;
    protected NotificationService $notificationService;
    protected DueReminderService $dueReminderService;

    public function __construct()
    {
        $this->equipoModel = new EquipoModel();
        $this->equipoService = new EquipoService();
        $this->notificationService = new NotificationService();
        $this->dueReminderService = new DueReminderService();
        helper(['estado', 'url']); // Cargar helpers necesarios
    }

    /**
     * Método privado para renderizar vistas con header/footer
     * KISS: elimina duplicación de código
     */
    private function render(string $view, array $data = []): string
    {
        $data['header'] = view('Layouts/header');
        $data['footer'] = view('Layouts/footer');
        return view($view, $data);
    }

    /**
     * Vista principal del tablero Kanban
     * KISS: método simple y claro
     * Filtra automáticamente por usuario si es trabajador
     */
    public function index(): string
    {
        // Obtener información del usuario logueado
        $tipoUsuario = session()->get('tipo_usuario') ?? session()->get('usuario_tipo');
        $usuarioId = session()->get('usuario_id') ?? session()->get('idusuario');

        $this->equipoService->sincronizarVencidos();

        // Determinar si se debe filtrar por usuario
        $filtrarPorUsuario = null;
        
        // Si es trabajador, solo mostrar sus propios equipos
        if ($tipoUsuario === 'trabajador' && $usuarioId) {
            $filtrarPorUsuario = $usuarioId;
        }
        
        $data = [
            'titulo' => $tipoUsuario === 'trabajador' ? 'Mis Asignaciones' : 'Gestión de Equipos',
            'equiposKanban' => $this->equipoModel->getEquiposParaKanban(null, $filtrarPorUsuario),
            'estadisticas' => $this->equipoService->obtenerEstadisticas($filtrarPorUsuario),
            'tecnicos' => $this->equipoService->obtenerTodosTecnicos(),
            'es_trabajador' => $tipoUsuario === 'trabajador',
            'usuario_actual_id' => $usuarioId
        ];
        
        return $this->render('equipos/listar', $data);
    }

    public function vencidos(): string
    {
        $this->equipoService->sincronizarVencidos();

        $data = [
            'titulo' => 'Proyectos Vencidos',
            'equipos' => $this->equipoModel->getEquiposVencidos(),
            'header' => view('Layouts/header', ['titulo' => 'Proyectos Vencidos']),
            'footer' => view('Layouts/footer')
        ];

        return view('equipos/vencidos', $data);
    }

    /**
     * Vista para asignar técnico a un servicio
     * KISS: delegación clara al servicio
     */
    public function asignar(int $idserviciocontratado)
    {
        $servicio = $this->equipoModel->getServicioInfo($idserviciocontratado);
        
        if (!$servicio) {
            session()->setFlashdata('error', 'Servicio no encontrado');
            return redirect()->to('equipos');
        }

        $data = [
            'titulo' => 'Asignar Técnico al Servicio',
            'servicio' => $servicio,
            'tecnicos' => $this->equipoService->obtenerTecnicosDisponibles(
                $idserviciocontratado, 
                $servicio['fechahoraservicio']
            )
        ];
        
        return $this->render('equipos/asignar', $data);
    }

    /**
     * Método unificado para guardar/actualizar equipos
     * KISS: una sola función para ambas operaciones
     */
    public function guardar()
    {
        return $this->saveEquipo();
    }

    /**
     * Método unificado para guardar/actualizar equipos
     * KISS: una sola función para ambas operaciones
     */
    public function actualizar()
    {
        return $this->saveEquipo();
    }

    /**
     * Método unificado para guardar/actualizar equipos
     * KISS: una sola función para ambas operaciones
     * Soporta tanto peticiones normales como AJAX
     */
    public function saveEquipo()
    {
        // Validaciones básicas
        $rules = [
            'idusuario' => [
                'rules' => 'required|integer',
                'errors' => [
                    'required' => 'Debe seleccionar un usuario/técnico.',
                    'integer' => 'El identificador del usuario es inválido.'
                ]
            ],
            'descripcion' => [
                'rules' => 'required|min_length[5]',
                'errors' => [
                    'required' => 'La descripción del equipo es obligatoria.',
                    'min_length' => 'La descripción debe tener al menos 5 caracteres.'
                ]
            ],
            'estadoservicio' => [
                'rules' => 'required|in_list[Planificación,Producción,Postproducción,Finalizado]',
                'errors' => [
                    'required' => 'Debe seleccionar la fase del servicio.',
                    'in_list' => 'La fase seleccionada no es válida.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            $validationErrors = $this->validator ? $this->validator->getErrors() : [];
            $error = !empty($validationErrors)
                ? implode(' ', $validationErrors)
                : 'Por favor complete correctamente todos los campos.';
            
            // Si es AJAX, devolver JSON
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $error
                ]);
            }
            
            session()->setFlashdata('error', $error);
            return redirect()->back()->withInput();
        }

        $equipoId = $this->request->getPost('idequipo'); // null si es nuevo
        $usuarioId = (int)$this->request->getPost('idusuario');
        $servicioId = (int)$this->request->getPost('idserviciocontratado');
        $descripcion = $this->request->getPost('descripcion');
        $estado = $this->request->getPost('estadoservicio');

        $equipoExistente = null;
        $previousUsuarioId = null;

        if ($equipoId) {
            $equipoExistente = $this->equipoModel->asArray()->find($equipoId);
            if ($equipoExistente) {
                if (!$servicioId) {
                    $servicioId = $equipoExistente['idserviciocontratado'];
                }
                $previousUsuarioId = $equipoExistente['idusuario'] ?? null;
            }
        }

        // Obtener información del servicio
        $servicio = $this->equipoModel->getServicioInfo($servicioId);
        if (!$servicio) {
            $error = 'Servicio no encontrado.';
            
            // Si es AJAX, devolver JSON
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $error
                ]);
            }
            
            session()->setFlashdata('error', $error);
            return redirect()->to('equipos');
        }

        // Validar asignación usando el servicio
        $validacion = $this->equipoService->validarAsignacion(
            $usuarioId, 
            $servicioId, 
            $servicio['fechahoraservicio'], 
            $equipoId
        );

        if (!$validacion['valido']) {
            $error = implode(', ', $validacion['errores']);
            
            // Si es AJAX, devolver JSON
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $error
                ]);
            }
            
            foreach ($validacion['errores'] as $err) {
                session()->setFlashdata('error', $err);
            }
            return redirect()->back()->withInput();
        }

        // Preparar datos
        $data = [
            'idusuario' => $usuarioId,
            'descripcion' => $descripcion,
            'estadoservicio' => $estado
        ];

        // Guardar o actualizar
        try {
            if ($equipoId) {
                // Actualizar
                $success = $this->equipoModel->update($equipoId, $data);
                $mensaje = $success ? 'Asignación actualizada correctamente' : 'Error al actualizar';
            } else {
                // Crear nuevo
                $data['idserviciocontratado'] = $servicioId;
                $success = $this->equipoModel->insert($data);
                $mensaje = $success ? 'Técnico asignado correctamente' : 'Error al asignar técnico';
            }

            if ($success && $servicio) {
                $clienteNombre = $servicio['cliente_nombre'] ?? 'Cliente';
                $servicioNombre = $servicio['servicio'] ?? 'Servicio';
                $shouldNotify = false;

                if (!$equipoId) {
                    $shouldNotify = true;
                } elseif ($previousUsuarioId && (int)$previousUsuarioId !== $usuarioId) {
                    $shouldNotify = true;
                }

                if ($shouldNotify) {
                    $this->notificationService->createAssignmentNotification(
                        $usuarioId,
                        $clienteNombre,
                        $servicioNombre,
                        $servicioId
                    );
                }
            }

            // Si es AJAX, devolver JSON
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => $success,
                    'message' => $mensaje,
                    'redirect' => base_url('equipos')  // Siempre regresar al tablero general
                ]);
            }

            session()->setFlashdata($success ? 'success' : 'error', $mensaje);
            
        } catch (\Exception $e) {
            log_message('error', 'Error en saveEquipo: ' . $e->getMessage());
            $error = 'Error interno del sistema';
            
            // Si es AJAX, devolver JSON
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $error
                ]);
            }
            
            session()->setFlashdata('error', $error);
        }

        // Siempre regresar al tablero general para mantener consistencia
        return redirect()->to('equipos');
    }

    /**
     * Vista para editar asignación de equipo
     * KISS: método simple y claro
     */
    public function editar(int $idequipo)
    {
        $equipo = $this->equipoModel->getEquipoConDetalles($idequipo);
        
        if (!$equipo) {
            session()->setFlashdata('error', 'Equipo no encontrado');
            return redirect()->to('equipos');
        }

        $data = [
            'titulo' => 'Editar Asignación de Equipo',
            'equipo' => $equipo,
            'tecnicos' => $this->equipoService->obtenerTecnicosDisponibles(
                $equipo['idserviciocontratado'], 
                $equipo['fechahoraservicio']
            )
        ];
        
        return $this->render('equipos/editar', $data);
    }

    /**
     * Vistas filtradas por servicio o usuario
     * KISS: métodos simples para vistas específicas
     */
    public function porServicio(int $servicioId)
    {
        $servicio = $this->equipoModel->getServicioInfo($servicioId);
        
        if (!$servicio) {
            session()->setFlashdata('error', 'Servicio no encontrado');
            return redirect()->to('equipos');
        }

        $data = [
            'titulo' => 'Equipos del Servicio: ' . $servicio['servicio'],
            'equiposKanban' => $this->equipoModel->getEquiposParaKanban($servicioId),
            'servicio' => $servicio
        ];
        
        return $this->render('equipos/listar', $data);
    }

    public function porUsuario(int $usuarioId): string
    {
        $equipos = $this->equipoModel->getEquiposPorUsuario($usuarioId);
        
        $data = [
            'titulo' => 'Mis Asignaciones',
            'equipos' => $equipos
        ];
        
        return $this->render('equipos/listar', $data);
    }

    /**
     * Endpoint AJAX para actualizar estado de equipos
     * KISS: delegación completa al servicio
     */
    public function actualizarEstado(): \CodeIgniter\HTTP\ResponseInterface
    {
        // Log para debugging
        log_message('info', 'Método actualizarEstado llamado');
        
        if (!$this->request->isAJAX()) {
            log_message('error', 'Petición no es AJAX');
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'Petición no válida']);
        }

        $input = json_decode($this->request->getBody(), true);
        log_message('info', 'Input recibido: ' . json_encode($input));
        
        $equipoId = (int)($input['id'] ?? 0);
        $nuevoEstado = $input['estado'] ?? '';

        if (!$equipoId || !$nuevoEstado) {
            log_message('error', 'Parámetros faltantes - ID: ' . $equipoId . ', Estado: ' . $nuevoEstado);
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Parámetros faltantes'
            ]);
        }

        try {
            // Obtener usuario actual para la auditoría - buscar en todas las variantes
            $usuarioId = session()->get('idusuario') 
                      ?? session()->get('usuario_id') 
                      ?? session()->get('user_id');
            
            $tipoUsuario = session()->get('tipo_usuario') ?? session()->get('usuario_tipo');
            
            log_message('info', 'Datos de sesión completos: ' . json_encode([
                'usuario_logueado' => session()->get('usuario_logueado'),
                'usuario_id' => session()->get('usuario_id'),
                'idusuario' => session()->get('idusuario'),
                'tipo_usuario' => session()->get('tipo_usuario'),
                'usuario_tipo' => session()->get('usuario_tipo')
            ]));
            
            // VALIDACIÓN DE SEGURIDAD: Si es trabajador, verificar que el equipo le pertenece
            if ($tipoUsuario === 'trabajador' && $usuarioId) {
                $equipo = $this->equipoModel->find($equipoId);
                if (!$equipo) {
                    log_message('warning', "Trabajador {$usuarioId} intentó actualizar equipo inexistente {$equipoId}");
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Equipo no encontrado'
                    ]);
                }
                
                // Verificar que el equipo pertenece al trabajador
                if ($equipo['idusuario'] != $usuarioId) {
                    log_message('warning', "Trabajador {$usuarioId} intentó actualizar equipo {$equipoId} que pertenece a usuario {$equipo['idusuario']}");
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'No tienes permiso para modificar esta asignación'
                    ]);
                }
            }
            
            if (!$usuarioId) {
                log_message('warning', 'Usuario no encontrado en sesión, buscando usuario por defecto');
                
                // Buscar el primer usuario administrador como fallback
                $db = \Config\Database::connect();
                // Intentar diferentes campos para encontrar un admin
                $usuarioDefault = $db->query("SELECT idusuario FROM usuarios WHERE tipo_usuario = 'admin' OR tipousuario = 'admin' OR rol = 'admin' LIMIT 1")->getRowArray();
                
                // Si no encuentra admin, usar cualquier usuario activo
                if (!$usuarioDefault) {
                    $usuarioDefault = $db->query("SELECT idusuario FROM usuarios WHERE estado = 1 LIMIT 1")->getRowArray();
                }
                
                if ($usuarioDefault) {
                    $usuarioId = $usuarioDefault['idusuario'];
                    log_message('info', "Usando usuario por defecto para auditoría: {$usuarioId}");
                } else {
                    log_message('warning', 'No se encontró usuario por defecto, usando método tradicional');
                    // Fallback al método tradicional si no hay usuario disponible
                    $resultado = $this->equipoService->actualizarEstado($equipoId, $nuevoEstado);
                    
                    return $this->response->setJSON([
                        'success' => $resultado['success'],
                        'message' => $resultado['message'] . ' (sin auditoría)'
                    ]);
                }
            }
            
            // Verificar si existe la tabla de auditoría
            $db = \Config\Database::connect();
            
            try {
                $tableExists = $db->query("SHOW TABLES LIKE 'auditoria_kanban'")->getNumRows() > 0;
                log_message('info', 'Verificación tabla auditoria_kanban: ' . ($tableExists ? 'existe' : 'no existe'));
            } catch (\Exception $e) {
                log_message('error', 'Error verificando tabla: ' . $e->getMessage());
                $tableExists = false;
            }
            
            if ($tableExists) {
                // Usar el método con auditoría del EquipoModel
                log_message('info', "Intentando actualizar con auditoría - Equipo: {$equipoId}, Estado: {$nuevoEstado}, Usuario: {$usuarioId}");
                
                $actualizado = $this->equipoModel->cambiarEstadoConAuditoria($equipoId, $nuevoEstado, $usuarioId);
                
                if ($actualizado) {
                    log_message('info', 'Estado actualizado con auditoría exitosamente');
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Estado actualizado correctamente'
                    ]);
                } else {
                    log_message('error', 'Error al actualizar estado con auditoría - Fallback a método tradicional');
                    
                    // Fallback al método tradicional si falla la auditoría
                    $resultado = $this->equipoService->actualizarEstado($equipoId, $nuevoEstado);
                    
                    return $this->response->setJSON([
                        'success' => $resultado['success'],
                        'message' => $resultado['message'] . ' (fallback sin auditoría)'
                    ]);
                }
            } else {
                // Fallback al método tradicional si no existe la tabla
                log_message('warning', 'Tabla auditoria_kanban no existe, usando método tradicional');
                $resultado = $this->equipoService->actualizarEstado($equipoId, $nuevoEstado);
                
                return $this->response->setJSON([
                    'success' => $resultado['success'],
                    'message' => $resultado['message'] . ' (sin auditoría)'
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error en actualizarEstado: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error interno del servidor'
            ]);
        }
    }

    // ==================== MÉTODOS DEL SISTEMA DE HISTORIAL ====================

    /**
     * Registrar cambio de estado en el historial
     */
    private function registrarCambioEstadoEquipo(int $equipoId, string $estadoAnterior, string $estadoNuevo)
    {
        try {
            log_message('info', "Iniciando registro historial - Equipo: $equipoId, De: $estadoAnterior, A: $estadoNuevo");
            
            $historialHelper = new HistorialHelper();
            
            // Obtener información del equipo y servicio
            $equipo = $this->equipoModel->getEquipoConDetalles($equipoId);
            log_message('info', 'Equipo con detalles: ' . json_encode($equipo));
            
            if (!$equipo) {
                log_message('error', 'No se pudo obtener detalles del equipo: ' . $equipoId);
                return false;
            }
            
            $contexto = $equipo['servicio'] ?? 'Servicio';
            log_message('info', 'Contexto del servicio: ' . $contexto);
            
            $resultado = $historialHelper->registrarCambioEstado(
                'equipos',
                $equipoId,
                $estadoAnterior,
                $estadoNuevo,
                $contexto
            );
            
            log_message('info', 'Resultado del registro en historial: ' . ($resultado ? 'éxito' : 'falló'));
            return $resultado;
            
        } catch (\Exception $e) {
            log_message('error', 'Error registrando cambio de estado: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Obtener historial de actividades de un equipo específico
     */
    public function historial(int $equipoId)
    {
        try {
            $historialHelper = new HistorialHelper();
            $historial = $historialHelper->getHistorialFormateado('equipos', $equipoId, 50);
            
            return $this->response->setJSON([
                'success' => true,
                'historial' => $historial
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo historial: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener historial'
            ]);
        }
    }

    /**
     * Obtener historial de actividades de un servicio específico
     */
    public function historialServicio(int $servicioId)
    {
        try {
            $historialHelper = new HistorialHelper();
            $historial = $historialHelper->getHistorialFormateado('servicioscontratados', $servicioId, 50);
            
            return $this->response->setJSON([
                'success' => true,
                'historial' => $historial
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo historial del servicio: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener historial del servicio'
            ]);
        }
    }

    /**
     * Generar reporte de productividad del equipo
     */
    public function reporteProductividad()
    {
        try {
            $fechaInicio = $this->request->getGet('fecha_inicio') ?? date('Y-m-01');
            $fechaFin = $this->request->getGet('fecha_fin') ?? date('Y-m-d');
            
            $historialModel = new HistorialActividadesModel();
            $reporte = $historialModel->getReporteProductividadEquipos($fechaInicio, $fechaFin);
            
            return $this->response->setJSON([
                'success' => true,
                'reporte' => $reporte,
                'periodo' => [
                    'inicio' => $fechaInicio,
                    'fin' => $fechaFin
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error generando reporte de productividad: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar reporte de productividad'
            ]);
        }
    }

    /**
     * Obtener estadísticas de actividades por período
     */
    public function estadisticasPeriodo()
    {
        try {
            $fechaInicio = $this->request->getGet('fecha_inicio') ?? date('Y-m-01');
            $fechaFin = $this->request->getGet('fecha_fin') ?? date('Y-m-d');
            
            $historialModel = new HistorialActividadesModel();
            $estadisticas = $historialModel->getEstadisticasPeriodo($fechaInicio, $fechaFin);
            
            return $this->response->setJSON([
                'success' => true,
                'estadisticas' => $estadisticas,
                'periodo' => [
                    'inicio' => $fechaInicio,
                    'fin' => $fechaFin
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo estadísticas: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ]);
        }
    }
}