<?php

namespace App\Models;

use CodeIgniter\Model;

class ProyectoModel extends Model
{
    protected $table = 'servicioscontratados';
    protected $primaryKey = 'idserviciocontratado';
    protected $allowedFields = [];

    /**
     * Obtiene todos los proyectos activos de la empresa
     * Un proyecto se considera activo si tiene servicios contratados con estados diferentes a 'Completado'
     */
    public function getProyectosActivos()
    {
        // Consulta simplificada para obtener todos los servicios contratados
        $query = "
            SELECT 
                sc.idserviciocontratado,
                s.servicio,
                CASE 
                    WHEN c.idempresa IS NOT NULL THEN e.razonsocial
                    ELSE CONCAT(p.nombres, ' ', p.apellidos)
                END as cliente,
                sc.fechahoraservicio,
                sc.direccion,
                COALESCE(eq.estadoservicio, 'Planificación') as estado,
                CASE 
                    WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Finalizado' THEN 100
                    WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Postproducción' THEN 80
                    WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Producción' THEN 55
                    WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Vencido' THEN 0
                    ELSE 20
                END as progreso
            FROM servicioscontratados sc
            INNER JOIN cotizaciones cot ON sc.idcotizacion = cot.idcotizacion
            INNER JOIN contratos con ON cot.idcotizacion = con.idcotizacion
            INNER JOIN clientes c ON cot.idcliente = c.idcliente
            LEFT JOIN personas p ON c.idpersona = p.idpersona
            LEFT JOIN empresas e ON c.idempresa = e.idempresa
            INNER JOIN servicios s ON sc.idservicio = s.idservicio
            LEFT JOIN equipos eq ON sc.idserviciocontratado = eq.idserviciocontratado
            ORDER BY sc.fechahoraservicio ASC
        ";
        
        try {
            $result = $this->db->query($query)->getResult();
            log_message('info', 'Proyectos encontrados: ' . count($result));
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error en consulta de proyectos: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene proyectos agrupados por cliente con todos sus servicios
     * Retorna un array donde cada elemento es un cliente con sus servicios
     */
    public function getProyectosAgrupadosPorCliente()
    {
        $query = "
            SELECT 
                c.idcliente,
                CASE 
                    WHEN c.idempresa IS NOT NULL THEN e.razonsocial
                    ELSE CONCAT(p.nombres, ' ', p.apellidos)
                END as cliente,
                CASE 
                    WHEN c.idempresa IS NOT NULL THEN e.telefono
                    ELSE p.telprincipal
                END as telefono_cliente,
                sc.idserviciocontratado,
                s.servicio,
                sc.fechahoraservicio,
                sc.direccion,
                COALESCE(eq.estadoservicio, 'Planificación') as estado,
                CASE 
                    WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Finalizado' THEN 100
                    WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Postproducción' THEN 80
                    WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Producción' THEN 55
                    WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Vencido' THEN 0
                    ELSE 20
                END as progreso,
                cot.fechaevento,
                te.evento as tipoevento
            FROM servicioscontratados sc
            INNER JOIN cotizaciones cot ON sc.idcotizacion = cot.idcotizacion
            INNER JOIN clientes c ON cot.idcliente = c.idcliente
            LEFT JOIN personas p ON c.idpersona = p.idpersona
            LEFT JOIN empresas e ON c.idempresa = e.idempresa
            INNER JOIN servicios s ON sc.idservicio = s.idservicio
            LEFT JOIN equipos eq ON sc.idserviciocontratado = eq.idserviciocontratado
            LEFT JOIN tipoeventos te ON cot.idtipoevento = te.idtipoevento
            ORDER BY c.idcliente, sc.fechahoraservicio ASC
        ";
        
        try {
            $result = $this->db->query($query)->getResult();
            
            // Debug: Verificar resultados de la consulta
            log_message('info', 'getProyectosAgrupadosPorCliente - Total registros: ' . count($result));
            
            // Agrupar servicios por cliente
            $proyectosAgrupados = [];
            foreach ($result as $row) {
                $idCliente = $row->idcliente;
                log_message('info', 'Procesando cliente: ' . $idCliente . ' - ' . $row->cliente);
                
                // Si el cliente no existe en el array, crearlo
                if (!isset($proyectosAgrupados[$idCliente])) {
                    $proyectosAgrupados[$idCliente] = [
                        'idcliente' => $idCliente,
                        'cliente' => $row->cliente,
                        'telefono_cliente' => $row->telefono_cliente,
                        'servicios' => [],
                        'total_servicios' => 0,
                        'progreso_promedio' => 0,
                        'fecha_mas_proxima' => $row->fechahoraservicio,
                        'direccion_principal' => $row->direccion
                    ];
                }
                
                // Agregar servicio al cliente
                $proyectosAgrupados[$idCliente]['servicios'][] = [
                    'idserviciocontratado' => $row->idserviciocontratado,
                    'servicio' => $row->servicio,
                    'fechahoraservicio' => $row->fechahoraservicio,
                    'direccion' => $row->direccion,
                    'estado' => $row->estado,
                    'progreso' => $row->progreso,
                    'fechaevento' => $row->fechaevento,
                    'tipoevento' => $row->tipoevento
                ];
                
                $proyectosAgrupados[$idCliente]['total_servicios']++;
            }
            
            // Calcular progreso promedio para cada cliente
            foreach ($proyectosAgrupados as &$proyecto) {
                $sumaProgreso = 0;
                foreach ($proyecto['servicios'] as $servicio) {
                    $sumaProgreso += $servicio['progreso'];
                }
                $proyecto['progreso_promedio'] = round($sumaProgreso / $proyecto['total_servicios']);
            }
            
            // Convertir a array indexado
            $proyectosAgrupados = array_values($proyectosAgrupados);
            
            log_message('info', 'Proyectos agrupados por cliente: ' . count($proyectosAgrupados));
            return $proyectosAgrupados;
            
        } catch (\Exception $e) {
            log_message('error', 'Error en getProyectosAgrupadosPorCliente: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Función de prueba para verificar datos básicos
     */
    public function testConexion()
    {
        try {
            // Contar servicios contratados
            $servicios = $this->db->query("SELECT COUNT(*) as total FROM servicioscontratados")->getRow();
            
            // Contar contratos
            $contratos = $this->db->query("SELECT COUNT(*) as total FROM contratos")->getRow();
            
            // Contar equipos
            $equipos = $this->db->query("SELECT COUNT(*) as total FROM equipos")->getRow();
            
            return [
                'servicios' => $servicios->total,
                'contratos' => $contratos->total,
                'equipos' => $equipos->total
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error en testConexion: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene todos los proyectos (activos e inactivos)
     */
    public function getTodosLosProyectos()
    {
        return $this->db->query("
            SELECT DISTINCT
                sc.idserviciocontratado,
                s.servicio,
                CASE 
                    WHEN c.idempresa IS NOT NULL THEN e.razonsocial
                    ELSE CONCAT(p.nombres, ' ', p.apellidos)
                END as cliente,
                sc.fechahoraservicio,
                sc.direccion,
                COALESCE(eq.estadoservicio, 'Pendiente') as estado,
                CASE 
                    WHEN COALESCE(eq.estadoservicio, 'Pendiente') = 'Completado' THEN 100
                    WHEN COALESCE(eq.estadoservicio, 'Pendiente') = 'En Proceso' THEN 65
                    WHEN COALESCE(eq.estadoservicio, 'Pendiente') = 'Programado' THEN 35
                    ELSE 10
                END as progreso,
                cot.fechaevento,
                te.evento as tipoevento,
                CASE 
                    WHEN COALESCE(eq.estadoservicio, 'Planificación') NOT IN ('Finalizado', 'Vencido') 
                         AND sc.fechahoraservicio >= CURDATE() THEN 'Activo'
                    ELSE 'Inactivo'
                END as estadoproyecto
            FROM servicioscontratados sc
            INNER JOIN cotizaciones cot ON sc.idcotizacion = cot.idcotizacion
            INNER JOIN contratos con ON cot.idcotizacion = con.idcotizacion
            INNER JOIN clientes c ON cot.idcliente = c.idcliente
            LEFT JOIN personas p ON c.idpersona = p.idpersona
            LEFT JOIN empresas e ON c.idempresa = e.idempresa
            INNER JOIN servicios s ON sc.idservicio = s.idservicio
            LEFT JOIN equipos eq ON sc.idserviciocontratado = eq.idserviciocontratado
            LEFT JOIN tipoeventos te ON cot.idtipoevento = te.idtipoevento
            ORDER BY sc.fechahoraservicio DESC
        ")->getResult();
    }

    /**
     * Obtiene un proyecto específico por ID
     */
    public function getProyectoPorId($idserviciocontratado)
    {
        return $this->db->query("
            SELECT 
                sc.idserviciocontratado,
                s.servicio,
                s.descripcion as descripcion_servicio,
                CASE 
                    WHEN c.idempresa IS NOT NULL THEN e.razonsocial
                    ELSE CONCAT(p.nombres, ' ', p.apellidos)
                END as cliente,
                sc.fechahoraservicio,
                sc.direccion,
                sc.cantidad,
                sc.precio,
                COALESCE(eq.estadoservicio, 'Pendiente') as estado,
                eq.descripcion as descripcion_equipo,
                CASE 
                    WHEN COALESCE(eq.estadoservicio, 'Pendiente') = 'Completado' THEN 100
                    WHEN COALESCE(eq.estadoservicio, 'Pendiente') = 'En Proceso' THEN 65
                    WHEN COALESCE(eq.estadoservicio, 'Pendiente') = 'Programado' THEN 35
                    ELSE 10
                END as progreso,
                cot.fechaevento,
                cot.fechacotizacion,
                te.evento as tipoevento,
                CONCAT(u.nombres, ' ', u.apellidos) as responsable,
                ca.cargo as cargo_responsable
            FROM servicioscontratados sc
            INNER JOIN cotizaciones cot ON sc.idcotizacion = cot.idcotizacion
            INNER JOIN contratos con ON cot.idcotizacion = con.idcotizacion
            INNER JOIN clientes c ON cot.idcliente = c.idcliente
            LEFT JOIN personas p ON c.idpersona = p.idpersona
            LEFT JOIN empresas e ON c.idempresa = e.idempresa
            INNER JOIN servicios s ON sc.idservicio = s.idservicio
            LEFT JOIN equipos eq ON sc.idserviciocontratado = eq.idserviciocontratado
            LEFT JOIN usuarios u ON eq.idusuario = u.idusuario
            LEFT JOIN personas pu ON u.idpersona = pu.idpersona
            LEFT JOIN cargos ca ON u.idcargo = ca.idcargo
            LEFT JOIN tipoeventos te ON cot.idtipoevento = te.idtipoevento
            WHERE sc.idserviciocontratado = ?
        ", [$idserviciocontratado])->getRow();
    }

    /**
     * Obtiene estadísticas de proyectos
     */
    public function getEstadisticasProyectos()
    {
        return $this->db->query("
            SELECT 
                COUNT(*) as total_proyectos,
                SUM(CASE WHEN COALESCE(eq.estadoservicio, 'Planificación') NOT IN ('Finalizado', 'Vencido') 
                         AND sc.fechahoraservicio >= CURDATE() THEN 1 ELSE 0 END) as proyectos_activos,
                SUM(CASE WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Finalizado' THEN 1 ELSE 0 END) as proyectos_finalizados,
                SUM(CASE WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Postproducción' THEN 1 ELSE 0 END) as proyectos_postproduccion,
                SUM(CASE WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Producción' THEN 1 ELSE 0 END) as proyectos_produccion,
                SUM(CASE WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Planificación' THEN 1 ELSE 0 END) as proyectos_planificacion,
                SUM(CASE WHEN COALESCE(eq.estadoservicio, 'Planificación') = 'Vencido' THEN 1 ELSE 0 END) as proyectos_vencidos
            FROM servicioscontratados sc
            INNER JOIN cotizaciones cot ON sc.idcotizacion = cot.idcotizacion
            INNER JOIN contratos con ON cot.idcotizacion = con.idcotizacion
            LEFT JOIN equipos eq ON sc.idserviciocontratado = eq.idserviciocontratado
        ")->getRow();
    }

    /**
     * Obtiene proyectos por estado específico
     */
    public function getProyectosPorEstado($estado)
    {
        return $this->db->query("
            SELECT DISTINCT
                sc.idserviciocontratado,
                s.servicio,
                CASE 
                    WHEN c.idempresa IS NOT NULL THEN e.razonsocial
                    ELSE CONCAT(p.nombres, ' ', p.apellidos)
                END as cliente,
                sc.fechahoraservicio,
                sc.direccion,
                COALESCE(eq.estadoservicio, 'Pendiente') as estado,
                CASE 
                    WHEN COALESCE(eq.estadoservicio, 'Pendiente') = 'Completado' THEN 100
                    WHEN COALESCE(eq.estadoservicio, 'Pendiente') = 'En Proceso' THEN 65
                    WHEN COALESCE(eq.estadoservicio, 'Pendiente') = 'Programado' THEN 35
                    ELSE 10
                END as progreso,
                cot.fechaevento,
                te.evento as tipoevento
            FROM servicioscontratados sc
            INNER JOIN cotizaciones cot ON sc.idcotizacion = cot.idcotizacion
            INNER JOIN contratos con ON cot.idcotizacion = con.idcotizacion
            INNER JOIN clientes c ON cot.idcliente = c.idcliente
            LEFT JOIN personas p ON c.idpersona = p.idpersona
            LEFT JOIN empresas e ON c.idempresa = e.idempresa
            INNER JOIN servicios s ON sc.idservicio = s.idservicio
            LEFT JOIN equipos eq ON sc.idserviciocontratado = eq.idserviciocontratado
            LEFT JOIN tipoeventos te ON cot.idtipoevento = te.idtipoevento
            WHERE COALESCE(eq.estadoservicio, 'Planificación') = ?
            ORDER BY sc.fechahoraservicio ASC
        ", [$estado])->getResult();
    }
}
