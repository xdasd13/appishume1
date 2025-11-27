USE ishumeProyectos;
START TRANSACTION;

-- Limpiar pagos QA previos
DELETE FROM controlpagos WHERE idcontrato IN (9001, 9002);

-- Pagos realistas (montos moderados, métodos variados)
INSERT INTO controlpagos (idpagos, idcontrato, saldo, amortizacion, deuda, idtipopago, numtransaccion, fechahora, idusuario, comprobante, dni_pagador, nombre_pagador) VALUES
    (9501, 9001, 2550.00, 1050.00, 1500.00, 2, 'TRX-20241115-001', '2024-11-15 11:20:00', 9001, NULL, '46801235', 'Lucía Salazar'),
    (9502, 9001, 1500.00, 900.00, 600.00, 5, 'YAPE-20241205-014', '2024-12-05 16:45:00', 9001, NULL, '46801235', 'Lucía Salazar'),
    (9503, 9001, 600.00, 600.00, 0.00, 1, 'EFE-20241218-003', '2024-12-18 12:10:00', 9002, NULL, '76342109', 'Sofía Rimachi'),

    (9504, 9002, 2070.00, 870.00, 1200.00, 3, 'VISA-20241201-221', '2024-12-01 09:55:00', 9002, NULL, '51478963', 'Pedro Quispe'),
    (9505, 9002, 1200.00, 600.00, 600.00, 2, 'TRX-20241222-110', '2024-12-22 18:40:00', 9001, NULL, '51478963', 'Pedro Quispe'),
    (9506, 9002, 600.00, 300.00, 300.00, 5, 'YAPE-20250105-032', '2025-01-05 13:05:00', 9002, NULL, '76342109', 'Sofía Rimachi');

COMMIT;

SELECT 'Pagos QA cargados' AS metrica, COUNT(*) AS registros FROM controlpagos WHERE idpagos BETWEEN 9501 AND 9506;
