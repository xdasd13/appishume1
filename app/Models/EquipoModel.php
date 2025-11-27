<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Equipos refactorizado aplicando KISS
 * Consultas limpias y sin duplicación
 */
class EquipoModel extends Model
{
    protected $table = 'equipos';
    protected $primaryKey = 'idequipo';
    protected $allowedFields = ['idserviciocontratado', 'idusuario', 'descripcion', 'estadoservicio'];
    protected $useTimestamps = false;
    protected $returnType = 'array';

    /**
     * Consulta base con todos los joins necesarios
     * Aplicando DRY: una sola consulta reutilizable
     */
    private function getBaseQuery(): \CodeIgniter\Database\BaseBuilder
    {
        return $this->db->table('equipos e')
            ->select('e.idequipo, e.idserviciocontratado, e.idusuario, e.descripcion, e.estadoservicio, e.fecha_asignacion')
            ->select('u.nombreusuario, p.nombres, p.apellidos, c.cargo')
            ->select('s.servicio, sc.direccion, sc.fechahoraservicio, co.fechaevento')
            ->select('te.evento as tipoevento')
            ->select('CONCAT(p.nombres, " ", p.apellidos) as nombre_completo')
            ->select('IF(cl.idpersona IS NOT NULL, CONCAT(pc.nombres, " ", pc.apellidos), IF(cl.idempresa IS NOT NULL, emp.razonsocial, "Cliente no especificado")) as cliente_nombre', false)
            ->select('IF(cl.idpersona IS NOT NULL, pc.telprincipal, IF(cl.idempresa IS NOT NULL, emp.telefono, NULL)) as cliente_telefono', false)
            ->select('cl.idcliente')
            ->join('usuarios u', 'e.idusuario = u.idusuario')
            ->join('personas p', 'u.idpersona = p.idpersona')
            ->join('cargos c', 'u.idcargo = c.idcargo')
            ->join('servicioscontratados sc', 'e.idserviciocontratado = sc.idserviciocontratado')
            ->join('servicios s', 'sc.idservicio = s.idservicio')
            ->join('cotizaciones co', 'sc.idcotizacion = co.idcotizacion')
            ->join('clientes cl', 'co.idcliente = cl.idcliente')
            ->join('personas pc', 'cl.idpersona = pc.idpersona', 'left')
            ->join('empresas emp', 'cl.idempresa = emp.idempresa', 'left')
            ->join('tipoeventos te', 'co.idtipoevento = te.idtipoevento', 'left');
    }

    /**
     * Obtiene todos los equipos con detalles completos
     * KISS: método simple y claro
     */
    public function getEquiposConDetalles(): array
    {
        return $this->getBaseQuery()
            ->orderBy('sc.fechahoraservicio', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtiene equipos por servicio específico
     * KISS: reutiliza consulta base
     */
    public function getEquiposPorServicio(int $idserviciocontratado): array
    {
        return $this->getBaseQuery()
            ->where('e.idserviciocontratado', $idserviciocontratado)
            ->orderBy('e.estadoservicio')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtiene equipos por usuario específico
     * KISS: reutiliza consulta base
     */
    public function getEquiposPorUsuario(int $idusuario): array
    {
        return $this->getBaseQuery()
            ->where('e.idusuario', $idusuario)
            ->orderBy('sc.fechahoraservicio', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtiene un equipo específico con todos los detalles
     * KISS: reutiliza consulta base
     */
    public function getEquipoConDetalles(int $idequipo): ?array
    {
        $resultado = $this->getBaseQuery()
            ->where('e.idequipo', $idequipo)
            ->get()
            ->getRowArray();
            
        return $resultado ?: null;
    }

    /**
     * Obtiene equipos agrupados por estado para el Kanban
     * KISS: método específico para la vista
     * Ordenamiento: Más recientes primero (por fecha de asignación DESC)
     * 
     * Si no se filtra por usuario (vista general), incluye también servicios sin asignación
     * que aparecerán como "Pendiente"
     * 
     * @param int|null $servicioId Filtrar por servicio específico
     * @param int|null $usuarioId Filtrar por usuario específico (para trabajadores)
     */
    public function getEquiposParaKanban(?int $servicioId = null, ?int $usuarioId = null): array
    {
        // Si se filtra por usuario específico (trabajador), usar consulta tradicional
        // Solo mostrar equipos asignados a ese usuario
        if ($usuarioId) {
            $query = $this->getBaseQuery();
            
            if ($servicioId) {
                $query->where('e.idserviciocontratado', $servicioId);
            }
            
            $query->where('e.idusuario', $usuarioId);
            $query->where('e.estadoservicio !=', 'Vencido');
            
            $equipos = $query->orderBy('e.fecha_asignacion', 'DESC')
                ->orderBy('e.idequipo', 'DESC')
                ->get()
                ->getResultArray();
        } else {
            // Vista general: incluir TODOS los servicios contratados (con y sin asignación)
            // Similar a como lo hace ProyectoModel
            // Si un servicio tiene múltiples asignaciones, solo mostrar la más reciente
            $sql = "
                SELECT 
                    e.idequipo, 
                    sc.idserviciocontratado, 
                    e.idusuario, 
                    e.descripcion, 
                    COALESCE(e.estadoservicio, 'Pendiente') as estadoservicio, 
                    e.fecha_asignacion,
                    u.nombreusuario, 
                    p.nombres, 
                    p.apellidos, 
                    c.cargo,
                    s.servicio, 
                    sc.direccion, 
                    sc.fechahoraservicio, 
                    co.fechaevento,
                    te.evento as tipoevento,
                    CONCAT(p.nombres, ' ', p.apellidos) as nombre_completo,
                    IF(cl.idpersona IS NOT NULL, CONCAT(pc.nombres, ' ', pc.apellidos), 
                       IF(cl.idempresa IS NOT NULL, emp.razonsocial, 'Cliente no especificado')) as cliente_nombre,
                    IF(cl.idpersona IS NOT NULL, pc.telprincipal, 
                       IF(cl.idempresa IS NOT NULL, emp.telefono, NULL)) as cliente_telefono,
                    cl.idcliente
                FROM servicioscontratados sc
                INNER JOIN servicios s ON sc.idservicio = s.idservicio
                INNER JOIN cotizaciones co ON sc.idcotizacion = co.idcotizacion
                INNER JOIN clientes cl ON co.idcliente = cl.idcliente
                LEFT JOIN personas pc ON cl.idpersona = pc.idpersona
                LEFT JOIN empresas emp ON cl.idempresa = emp.idempresa
                LEFT JOIN tipoeventos te ON co.idtipoevento = te.idtipoevento
                LEFT JOIN (
                    SELECT e1.*
                    FROM equipos e1
                    INNER JOIN (
                        SELECT idserviciocontratado, MAX(idequipo) as max_idequipo
                        FROM equipos
                        WHERE estadoservicio != 'Vencido'
                        GROUP BY idserviciocontratado
                    ) e2 ON e1.idserviciocontratado = e2.idserviciocontratado 
                         AND e1.idequipo = e2.max_idequipo
                ) e ON sc.idserviciocontratado = e.idserviciocontratado
                LEFT JOIN usuarios u ON e.idusuario = u.idusuario
                LEFT JOIN personas p ON u.idpersona = p.idpersona
                LEFT JOIN cargos c ON u.idcargo = c.idcargo
                WHERE COALESCE(e.estadoservicio, 'Pendiente') != 'Vencido'
                  AND YEAR(sc.fechahoraservicio) = YEAR(CURDATE())
                  AND sc.fechahoraservicio >= CURDATE()
            ";
            
            $params = [];
            if ($servicioId) {
                $sql .= " AND sc.idserviciocontratado = ?";
                $params[] = $servicioId;
            }
            
            $sql .= " ORDER BY sc.fechahoraservicio ASC, e.fecha_asignacion DESC, e.idequipo DESC";
            
            if (!empty($params)) {
                $equipos = $this->db->query($sql, $params)->getResultArray();
            } else {
                $equipos = $this->db->query($sql)->getResultArray();
            }
        }

        // Agrupar por estado para facilitar el renderizado
        // Flujo: Planificación → Producción → Postproducción → Finalizado
        $agrupados = [
            'Planificación' => [],
            'Producción' => [],
            'Postproducción' => [],
            'Finalizado' => []
        ];

        foreach ($equipos as $equipo) {
            $estado = $equipo['estadoservicio'] ?? 'Planificación';
            
            if (isset($agrupados[$estado])) {
                $agrupados[$estado][] = $equipo;
            } elseif ($estado !== 'Vencido') {
                // En caso de tener estados no contemplados (ej. datos antiguos), mapearlos
                $agrupados['Planificación'][] = $equipo;
            }
        }

        return $agrupados;
    }

    public function marcarServiciosVencidos(?string $fechaReferencia = null): int
    {
        $fecha = $fechaReferencia ?? date('Y-m-d H:i:s');

        $sql = "
            UPDATE equipos e
            INNER JOIN servicioscontratados sc ON sc.idserviciocontratado = e.idserviciocontratado
            SET e.estadoservicio = 'Vencido'
            WHERE COALESCE(e.estadoservicio, 'Pendiente') NOT IN ('Completado', 'Vencido')
              AND sc.fechahoraservicio < ?
        ";

        $this->db->query($sql, [$fecha]);

        return $this->db->affectedRows();
    }

    public function getEquiposVencidos(): array
    {
        return $this->getBaseQuery()
            ->where('e.estadoservicio', 'Vencido')
            ->orderBy('sc.fechahoraservicio', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtiene información básica de un servicio contratado
     * KISS: método simple para obtener datos del servicio
     */
    public function getServicioInfo(int $servicioId): ?array
    {
        return $this->db->table('servicioscontratados sc')
            ->select('sc.*, s.servicio, co.fechaevento, te.evento as tipoevento,
                     CASE 
                        WHEN cl.idpersona IS NOT NULL THEN CONCAT(p.nombres, " ", p.apellidos)
                        WHEN cl.idempresa IS NOT NULL THEN e.razonsocial
                        ELSE "Cliente no especificado"
                     END as cliente_nombre')
            ->join('servicios s', 'sc.idservicio = s.idservicio')
            ->join('cotizaciones co', 'sc.idcotizacion = co.idcotizacion')
            ->join('clientes cl', 'co.idcliente = cl.idcliente')
            ->join('tipoeventos te', 'co.idtipoevento = te.idtipoevento', 'left')
            ->join('personas p', 'cl.idpersona = p.idpersona', 'left')
            ->join('empresas e', 'cl.idempresa = e.idempresa', 'left')
            ->where('sc.idserviciocontratado', $servicioId)
            ->get()
            ->getRowArray();
    }

    /**
     * Cambiar estado de equipo con auditoría
     */
    public function cambiarEstadoConAuditoria(int $idequipo, string $nuevoEstado, int $idusuario): bool
    {
        try {
            // Obtener estado actual
            $equipoActual = $this->asArray()->find($idequipo);
            if (!$equipoActual) {
                log_message('error', "Equipo no encontrado: {$idequipo}");
                return false;
            }

            $estadoAnterior = $equipoActual['estadoservicio'];
            log_message('info', "Cambiando estado equipo {$idequipo}: {$estadoAnterior} -> {$nuevoEstado}");

            // Verificar si existen los campos de auditoría en la tabla equipos
            $db = \Config\Database::connect();
            $fields = $db->getFieldNames('equipos');
            $tieneAuditoria = in_array('idusuario_ultima_modificacion', $fields);

            // Preparar datos para actualizar
            $updateData = ['estadoservicio' => $nuevoEstado];
            
            if ($tieneAuditoria) {
                $updateData['idusuario_ultima_modificacion'] = $idusuario;
                log_message('info', 'Actualizando con campos de auditoría');
            } else {
                log_message('warning', 'Campos de auditoría no existen, actualizando solo estado');
            }

            // Actualizar equipo
            $actualizado = $this->update($idequipo, $updateData);

            if ($actualizado && $estadoAnterior !== $nuevoEstado) {
                // Verificar si existe la tabla de auditoría
                $tablaExists = $db->query("SHOW TABLES LIKE 'auditoria_kanban'")->getNumRows() > 0;
                
                if ($tablaExists) {
                    // Registrar en auditoría
                    $auditoriaModel = new AuditoriaKanbanModel();
                    $registrado = $auditoriaModel->registrarCambio(
                        $idequipo,
                        $idusuario,
                        'cambiar_estado',
                        $estadoAnterior,
                        $nuevoEstado
                    );
                    log_message('info', 'Auditoría registrada: ' . ($registrado ? 'sí' : 'no'));
                } else {
                    log_message('warning', 'Tabla auditoria_kanban no existe');
                }
            }

            return $actualizado;
            
        } catch (\Exception $e) {
            log_message('error', 'Error en cambiarEstadoConAuditoria: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reasignar equipo con auditoría
     */
    public function reasignarConAuditoria(int $idequipo, int $nuevoUsuario, int $usuarioQueReasigna): bool
    {
        try {
            // Obtener datos actuales
            $equipoActual = $this->asArray()->find($idequipo);
            if (!$equipoActual) {
                return false;
            }

            $usuarioAnterior = $equipoActual['idusuario'];

            // Verificar si existen los campos de auditoría
            $db = \Config\Database::connect();
            $fields = $db->getFieldNames('equipos');
            $tieneAuditoria = in_array('idusuario_ultima_modificacion', $fields);

            // Preparar datos para actualizar
            $updateData = ['idusuario' => $nuevoUsuario];
            
            if ($tieneAuditoria) {
                $updateData['idusuario_ultima_modificacion'] = $usuarioQueReasigna;
            }

            // Actualizar equipo
            $actualizado = $this->update($idequipo, $updateData);

            if ($actualizado && $usuarioAnterior !== $nuevoUsuario) {
                // Verificar si existe la tabla de auditoría
                $tablaExists = $db->query("SHOW TABLES LIKE 'auditoria_kanban'")->getNumRows() > 0;
                
                if ($tablaExists) {
                    // Registrar en auditoría
                    $auditoriaModel = new AuditoriaKanbanModel();
                    $auditoriaModel->registrarCambio(
                        $idequipo,
                        $usuarioQueReasigna,
                        'reasignar',
                        (string)$usuarioAnterior,
                        (string)$nuevoUsuario
                    );
                }
            }

            return $actualizado;
            
        } catch (\Exception $e) {
            log_message('error', 'Error en reasignarConAuditoria: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear equipo con auditoría
     */
    public function crearConAuditoria(array $datos, int $usuarioCreador): int|false
    {
        $datos['idusuario_ultima_modificacion'] = $usuarioCreador;
        
        $idequipo = $this->insert($datos);
        
        if ($idequipo) {
            // Registrar en auditoría
            $auditoriaModel = new AuditoriaKanbanModel();
            $auditoriaModel->registrarCambio(
                $idequipo,
                $usuarioCreador,
                'crear',
                null,
                $datos['estadoservicio'] ?? 'Pendiente'
            );
        }

        return $idequipo;
    }
}