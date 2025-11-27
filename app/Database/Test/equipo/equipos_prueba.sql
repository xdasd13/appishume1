USE ishumeProyectos;
START TRANSACTION;

-- Reseteo controlado del dataset QA
DELETE FROM equipos WHERE idserviciocontratado IN (9101, 9102, 9103, 9104);

-- Asignaciones de equipos usando las nuevas fases de servicio
INSERT INTO equipos (idserviciocontratado, idusuario, descripcion, estadoservicio, fecha_asignacion) VALUES
    (9101, 9001, 'Equipo audiovisual principal: Blackmagic 6K + estabilizador Ronin', 'Planificación', '2025-11-15 09:30:00'),
    (9102, 9002, 'Unidad de streaming: Atem Mini Extreme + enlace 4G', 'Producción', '2025-12-01 14:45:00'),
    (9103, 9001, 'Postproducción fotográfica: catálogo Lightroom y álbum premium', 'Postproducción', '2026-01-05 10:10:00'),
    (9104, 9002, 'Entrega final de grabaciones editadas y highlights', 'Finalizado', '2026-01-12 18:20:00');

COMMIT;

SELECT 'Equipos QA cargados' AS metrica, COUNT(*) AS equipos_activos
FROM equipos WHERE idserviciocontratado BETWEEN 9101 AND 9104;
