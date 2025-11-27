<?php

namespace App\Models;

use CodeIgniter\Model;

class CronogramaModel extends Model
{
    protected $table = 'servicioscontratados';
    protected $primaryKey = 'idserviciocontratado';
    protected $allowedFields = ['idcotizacion', 'idservicio', 'fechahoraservicio', 'direccion'];

    /**
     * Obtiene estadísticas del cronograma
     */
    public function getEstadisticas()
    {
        try {
            // Servicios activos - consulta más simple
            $serviciosActivos = $this->db->query("
                SELECT COUNT(*) as total 
                FROM servicioscontratados
            ")->getRow()->total;

            // Equipos asignados - consulta más simple
            $equiposAsignados = $this->db->query("
                SELECT COUNT(*) as total 
                FROM equipos
            ")->getRow()->total;

            // Técnicos disponibles (usuarios activos con cargos técnicos)
            $tecnicosDisponibles = $this->db->query("
                SELECT COUNT(*) as total 
                FROM usuarios u
                INNER JOIN cargos c ON u.idcargo = c.idcargo
                WHERE (c.cargo LIKE '%Técnico%' 
                   OR c.cargo LIKE '%Coordinador%' 
                   OR c.cargo LIKE '%Fotógrafo%' 
                   OR c.cargo LIKE '%Operador%'
                   OR c.cargo LIKE '%Gerente%')
                AND u.estado = 1
            ")->getRow()->total;

            // Fallback: si no encuentra técnicos específicos, contar todos los usuarios activos
            if ($tecnicosDisponibles == 0) {
                $tecnicosDisponibles = $this->db->query("
                    SELECT COUNT(*) as total 
                    FROM usuarios
                    WHERE estado = 1
                ")->getRow()->total;
            }

            return [
                'servicios_count' => $serviciosActivos,
                'equipos' => $equiposAsignados,
                'tecnicos' => $tecnicosDisponibles
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error en getEstadisticas: ' . $e->getMessage());
            return [
                'servicios_count' => 0,
                'equipos' => 0,
                'tecnicos' => 0
            ];
        }
    }

    /**
     * Obtiene eventos para el calendario en formato FullCalendar
     */
    public function getEventosCalendario($start = null, $end = null)
    {
        try {
            $whereClause = "";
            if ($start && $end) {
                $whereClause = "AND sc.fechahoraservicio BETWEEN '$start' AND '$end'";
            }

            // Primero intentar consulta completa
            $queryCompleta = "
                SELECT 
                    sc.idserviciocontratado as id,
                    CONCAT(COALESCE(s.servicio, 'Servicio'), ' - ', 
                        CASE 
                            WHEN c.idempresa IS NOT NULL THEN COALESCE(e.razonsocial, 'Empresa')
                            ELSE CONCAT(COALESCE(p.nombres, 'Cliente'), ' ', COALESCE(p.apellidos, ''))
                        END
                    ) as title,
                    sc.fechahoraservicio as start,
                    COALESCE(sc.direccion, 'Sin dirección') as direccion,
                    CASE 
                        WHEN c.idempresa IS NOT NULL THEN COALESCE(e.telefono, 'Sin teléfono')
                        ELSE COALESCE(p.telefono, 'Sin teléfono')
                    END as telefono,
                    COALESCE(eq.estadoservicio, 'Planificación') as estado,
                    CASE 
                        WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Finalizado' THEN '#4caf50'
                        WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Postproducción' THEN '#ff9800'
                        WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Producción' THEN '#2196f3'
                        WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Vencido' THEN '#f44336'
                        ELSE '#757575'
                    END as color
                FROM servicioscontratados sc
                LEFT JOIN cotizaciones cot ON sc.idcotizacion = cot.idcotizacion
                LEFT JOIN contratos con ON cot.idcotizacion = con.idcotizacion
                LEFT JOIN clientes c ON cot.idcliente = c.idcliente
                LEFT JOIN personas p ON c.idpersona = p.idpersona
                LEFT JOIN empresas e ON c.idempresa = e.idempresa
                LEFT JOIN servicios s ON sc.idservicio = s.idservicio
                LEFT JOIN equipos eq ON sc.idserviciocontratado = eq.idserviciocontratado
                WHERE 1=1 $whereClause
                ORDER BY sc.fechahoraservicio ASC
            ";

            log_message('info', 'CronogramaModel - Intentando consulta completa');
            $eventos = $this->db->query($queryCompleta)->getResult();
            
            // Si no hay resultados, intentar consulta simplificada
            if (empty($eventos)) {
                log_message('info', 'CronogramaModel - Consulta completa sin resultados, intentando simplificada');
                
                $querySimple = "
                    SELECT 
                        sc.idserviciocontratado as id,
                        CONCAT('Servicio ID: ', sc.idservicio, ' - ', DATE_FORMAT(sc.fechahoraservicio, '%d/%m/%Y %H:%i')) as title,
                        sc.fechahoraservicio as start,
                        COALESCE(sc.direccion, 'Sin dirección') as direccion,
                        'Sin teléfono' as telefono,
                        'Planificación' as estado,
                        '#2196f3' as color
                    FROM servicioscontratados sc
                    WHERE 1=1 $whereClause
                    ORDER BY sc.fechahoraservicio ASC
                ";
                
                $eventos = $this->db->query($querySimple)->getResult();
                log_message('info', 'CronogramaModel - Consulta simple encontró: ' . count($eventos) . ' eventos');
            } else {
                log_message('info', 'CronogramaModel - Consulta completa encontró: ' . count($eventos) . ' eventos');
            }
            
            // Formatear eventos para FullCalendar
            $eventosFormateados = [];
            foreach ($eventos as $evento) {
                $eventosFormateados[] = [
                    'id' => $evento->id,
                    'title' => $evento->title,
                    'start' => $evento->start,
                    'color' => $evento->color,
                    'extendedProps' => [
                        'direccion' => $evento->direccion,
                        'telefono' => $evento->telefono,
                        'estado' => $evento->estado
                    ]
                ];
            }

            log_message('info', 'CronogramaModel - Eventos formateados: ' . count($eventosFormateados));
            return $eventosFormateados;

        } catch (\Exception $e) {
            log_message('error', 'Error en getEventosCalendario: ' . $e->getMessage());
            log_message('error', 'Error trace: ' . $e->getTraceAsString());
            
            // En caso de error, intentar consulta mínima
            try {
                log_message('info', 'CronogramaModel - Intentando consulta de emergencia');
                $queryEmergencia = "SELECT idserviciocontratado, fechahoraservicio, direccion FROM servicioscontratados ORDER BY fechahoraservicio";
                $eventosEmergencia = $this->db->query($queryEmergencia)->getResult();
                
                $eventosFormateados = [];
                foreach ($eventosEmergencia as $evento) {
                    $eventosFormateados[] = [
                        'id' => $evento->idserviciocontratado,
                        'title' => 'Servicio - ' . date('d/m/Y H:i', strtotime($evento->fechahoraservicio)),
                        'start' => $evento->fechahoraservicio,
                        'color' => '#ff9800',
                        'extendedProps' => [
                            'direccion' => $evento->direccion ?? 'Sin dirección',
                            'telefono' => 'Sin teléfono',
                            'estado' => 'Planificación'
                        ]
                    ];
                }
                
                log_message('info', 'CronogramaModel - Consulta de emergencia encontró: ' . count($eventosFormateados) . ' eventos');
                return $eventosFormateados;
                
            } catch (\Exception $e2) {
                log_message('error', 'Error en consulta de emergencia: ' . $e2->getMessage());
                return [];
            }
        }
    }

    /**
     * Obtiene los próximos servicios (siguiente semana)
     */
    public function getProximosServicios($limite = 10)
    {
        try {
            $query = "
                SELECT 
                    sc.idserviciocontratado,
                    sc.fechahoraservicio,
                    sc.direccion,
                    s.servicio,
                    CASE 
                        WHEN c.idempresa IS NOT NULL THEN e.razonsocial
                        ELSE CONCAT(p.nombres, ' ', p.apellidos)
                    END as cliente,
                    COALESCE(eq.estadoservicio, 'Planificación') as estado
                FROM servicioscontratados sc
                INNER JOIN cotizaciones cot ON sc.idcotizacion = cot.idcotizacion
                INNER JOIN contratos con ON cot.idcotizacion = con.idcotizacion
                INNER JOIN clientes c ON cot.idcliente = c.idcliente
                LEFT JOIN personas p ON c.idpersona = p.idpersona
                LEFT JOIN empresas e ON c.idempresa = e.idempresa
                INNER JOIN servicios s ON sc.idservicio = s.idservicio
                LEFT JOIN equipos eq ON sc.idserviciocontratado = eq.idserviciocontratado
                WHERE sc.fechahoraservicio >= NOW()
                AND sc.fechahoraservicio <= DATE_ADD(NOW(), INTERVAL 14 DAY)
                AND COALESCE(eq.estadoservicio, 'Planificación') NOT IN ('Finalizado', 'Vencido')
                ORDER BY sc.fechahoraservicio ASC
                LIMIT $limite
            ";

            return $this->db->query($query)->getResult();

        } catch (\Exception $e) {
            log_message('error', 'Error en getProximosServicios: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene servicios por fecha específica
     */
    public function getServiciosPorFecha($fecha)
    {
        try {
            $query = "
                SELECT 
                    sc.idserviciocontratado,
                    sc.fechahoraservicio,
                    sc.direccion,
                    s.servicio,
                    CASE 
                        WHEN c.idempresa IS NOT NULL THEN e.razonsocial
                        ELSE CONCAT(p.nombres, ' ', p.apellidos)
                    END as cliente,
                    COALESCE(eq.estadoservicio, 'Planificación') as estado,
                    CASE 
                        WHEN c.idempresa IS NOT NULL THEN e.telefono
                        ELSE p.telefono
                    END as telefono
                FROM servicioscontratados sc
                INNER JOIN cotizaciones cot ON sc.idcotizacion = cot.idcotizacion
                INNER JOIN contratos con ON cot.idcotizacion = con.idcotizacion
                INNER JOIN clientes c ON cot.idcliente = c.idcliente
                LEFT JOIN personas p ON c.idpersona = p.idpersona
                LEFT JOIN empresas e ON c.idempresa = e.idempresa
                INNER JOIN servicios s ON sc.idservicio = s.idservicio
                LEFT JOIN equipos eq ON sc.idserviciocontratado = eq.idserviciocontratado
                WHERE DATE(sc.fechahoraservicio) = ?
                ORDER BY sc.fechahoraservicio ASC
            ";

            return $this->db->query($query, [$fecha])->getResult();

        } catch (\Exception $e) {
            log_message('error', 'Error en getServiciosPorFecha: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene resumen semanal de servicios
     */
    public function getResumenSemanal()
    {
        try {
            $query = "
                SELECT 
                    DATE(sc.fechahoraservicio) as fecha,
                    COUNT(*) as total_servicios,
                    SUM(CASE WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Finalizado' THEN 1 ELSE 0 END) as finalizados,
                    SUM(CASE WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Postproducción' THEN 1 ELSE 0 END) as postproduccion,
                    SUM(CASE WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Producción' THEN 1 ELSE 0 END) as produccion,
                    SUM(CASE WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Planificación' THEN 1 ELSE 0 END) as planificacion,
                    SUM(CASE WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Vencido' THEN 1 ELSE 0 END) as vencidos
                FROM servicioscontratados sc
                LEFT JOIN equipos eq ON sc.idserviciocontratado = eq.idserviciocontratado
                WHERE sc.fechahoraservicio >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                AND sc.fechahoraservicio <= DATE_ADD(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(sc.fechahoraservicio)
                ORDER BY fecha ASC
            ";

            return $this->db->query($query)->getResult();

        } catch (\Exception $e) {
            log_message('error', 'Error en getResumenSemanal: ' . $e->getMessage());
            return [];
        }
    }
}
