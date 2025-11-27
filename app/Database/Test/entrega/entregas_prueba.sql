USE ishumeProyectos;
START TRANSACTION;

-- Limpiar entregas QA previas
DELETE FROM entregables WHERE identregable IN (9701, 9702, 9703, 9704);

-- Entregas enlazadas a los servicios QA 9101-9104
INSERT INTO entregables (identregable, idserviciocontratado, idpersona, fechahoraentrega, fecha_real_entrega, observaciones, estado, comprobante_entrega) VALUES
    (9701, 9101, 9005, '2025-12-27 19:30:00', '2025-12-27 19:30:00', 'Cloud con fotos RAW entregadas a cliente', 'completada', NULL),
    (9702, 9102, 9005, '2025-12-28 10:00:00', '2025-12-28 10:00:00', 'Link de streaming y respaldo publicado', 'completada', NULL),
    (9703, 9103, 9005, '2026-01-17 21:10:00', NULL, 'Actualizando color grading inicial', 'pendiente', NULL),
    (9704, 9104, 9005, '2026-01-22 11:45:00', NULL, 'Revisión con el cliente programada', 'pendiente', NULL);

COMMIT;

SELECT 'Entregas QA cargadas' AS metrica, COUNT(*) AS registros FROM entregables WHERE identregable BETWEEN 9701 AND 9704;
