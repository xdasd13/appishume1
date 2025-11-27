USE ishumeProyectos;

START TRANSACTION;

-- Dataset sencillo: solo dos proyectos basados en los catálogos existentes.
-- Eliminamos únicamente los registros que vamos a recrear para evitar duplicados.
DELETE FROM servicioscontratados WHERE idserviciocontratado IN (201, 202, 203, 204);
DELETE FROM contratos WHERE idcontrato IN (101, 102);
DELETE FROM cotizaciones WHERE idcotizacion IN (101, 102);

-- Proyecto 1: Boda íntima (cliente 1, servicios 1 y 2)
INSERT INTO cotizaciones (idcotizacion, idcliente, idtipocontrato, idusuariocrea, fechacotizacion, fechaevento, idtipoevento)
VALUES (101, 1, 1, 1, '2025-11-01', '2025-11-20', 1);

-- Proyecto 2: Quinceañero familiar (cliente 2, servicios 3 y 5)
INSERT INTO cotizaciones (idcotizacion, idcliente, idtipocontrato, idusuariocrea, fechacotizacion, fechaevento, idtipoevento)
VALUES (102, 2, 1, 2, '2025-11-08', '2025-12-05', 2);

-- Contratos asociados
INSERT INTO contratos (idcontrato, idcotizacion, idcliente, autorizapublicacion) VALUES
    (101, 101, 1, 1),
    (102, 102, 2, 0);

-- Servicios contratados para cada proyecto
INSERT INTO servicioscontratados (idserviciocontratado, idcotizacion, idservicio, cantidad, precio, fechahoraservicio, direccion) VALUES
    (201, 101, 1, 1, 780.00, '2025-11-20 17:00:00', 'Casa Moreyra, San Isidro'),
    (202, 101, 2, 1, 960.00, '2025-11-20 16:00:00', 'Casa Moreyra, San Isidro'),
    (203, 102, 3, 1, 520.00, '2025-12-05 19:30:00', 'Club Social Moquegua, Miraflores'),
    (204, 102, 5, 1, 380.00, '2025-12-05 21:00:00', 'Club Social Moquegua, Miraflores');

COMMIT;

-- Comprobación rápida para saber cuántos servicios demo hay
SELECT 'Proyectos demo cargados' AS metrica, COUNT(*) AS valor
FROM servicioscontratados
WHERE idserviciocontratado IN (201, 202, 203, 204);
