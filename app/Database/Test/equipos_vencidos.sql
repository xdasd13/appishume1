USE ishumeProyectos;

ALTER TABLE equipos
    MODIFY estadoservicio ENUM('Planificación','Producción','Postproducción','Finalizado','Vencido') DEFAULT 'Planificación';

UPDATE equipos e
INNER JOIN servicioscontratados sc ON sc.idserviciocontratado = e.idserviciocontratado
SET e.estadoservicio = 'Vencido'
WHERE COALESCE(e.estadoservicio, 'Planificación') NOT IN ('Finalizado', 'Vencido')
  AND sc.fechahoraservicio < NOW();
