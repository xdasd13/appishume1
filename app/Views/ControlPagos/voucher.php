<?php
$numeroPago = str_pad($pago['idpagos'], 6, '0', STR_PAD_LEFT);
$fechaPago = date('d/m/Y H:i:s', strtotime($pago['fechahora']));
$tipoPago = $tipo_pago['tipopago'] ?? 'N/A';
$referencia = !empty($pago['numtransaccion']) ? $pago['numtransaccion'] : 'No registrado';
$pagadorDocumento = !empty($pago['dni_pagador']) ? esc($pago['dni_pagador']) : 'No registrado';
$pagadorNombre = !empty($pago['nombre_pagador']) ? esc($pago['nombre_pagador']) : 'No registrado';
$clienteNombre = !empty($info_contrato['nombres'])
    ? trim($info_contrato['nombres'] . ' ' . ($info_contrato['apellidos'] ?? ''))
    : ($info_contrato['razonsocial'] ?? '');
$clienteDocumento = $info_contrato['nrodocumento'] ?? ($info_contrato['ruc'] ?? 'N/A');
$clienteCorreo = $info_contrato['email'] ?? 'N/A';
$clienteTelefono = $info_contrato['telefono'] ?? 'N/A';
$totalContrato = number_format($info_contrato['monto_total'] ?? 0, 2);
$saldoAnterior = number_format($pago['saldo'] ?? 0, 2);
$montoPagado = number_format($pago['amortizacion'] ?? 0, 2);
$nuevoSaldo = number_format($pago['deuda'] ?? 0, 2);
$contratoPagado = ($pago['deuda'] ?? 0) <= 0.01;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Pago - <?= $numeroPago ?></title>
    <style>
        body {
            font-family: "Helvetica", Arial, sans-serif;
            font-size: 13px;
            margin: 40px;
            color: #222;
            background: #f7f7f7;
        }

        body.pdf-exporting {
            margin: 0 !important;
            background: #fff;
            display: flex;
            justify-content: center;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .empresa {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .ruc { font-size: 14px; margin-top: 3px; }

        .documento-box {
            border: 2px solid #000;
            padding: 12px;
            width: 260px;
            margin: 12px auto;
            text-align: center;
        }

        .documento-titulo { font-size: 16px; font-weight: bold; }
        .numero { font-size: 18px; margin-top: 5px; }

        #voucher-document {
            width: 100%;
            max-width: 780px;
            margin: 0 auto;
            background: #fff;
            padding: 10px 30px 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.05);
        }

        #voucher-document.pdf-exporting {
            max-width: 794px; /* ancho aproximado A4 en px */
            width: 794px;
            margin: 0 auto;
            padding: 25px 35px;
            box-shadow: none;
        }

        .section {
            margin-top: 22px;
            border: 1px solid #ccc;
            padding: 12px 15px;
            border-radius: 4px;
        }

        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
            text-transform: uppercase;
        }

        table { width: 100%; border-collapse: collapse; }
        td { padding: 4px 0; vertical-align: top; }
        .label { width: 38%; font-weight: bold; }

        .pagado {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            margin-top: 8px;
            color: #0a7d00;
        }

        .firma { margin-top: 60px; text-align: center; }
        .firma-line {
            width: 55%;
            border-top: 1px solid #000;
            margin: 0 auto;
            padding-top: 5px;
            font-size: 13px;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            font-size: 11px;
            color: #555;
            text-align: center;
        }

        .actions {
            margin-top: 25px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .actions button {
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            background: #111;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }

        .actions button.secondary {
            background: #444;
        }

        @media print {
            body { margin: 20px; }
            .actions { display: none; }
        }
    </style>
</head>
<body>

<div id="voucher-document">
    <div class="header">
        <div class="empresa">ISHUME Productora Audiovisual</div>
        <div class="ruc">RUC: 10727174040</div>
        <div>Av. Luis Massaro 791 – Tel: 991157028</div>

        <div class="documento-box">
            <div class="documento-titulo">COMPROBANTE DE PAGO</div>
            <div class="numero">N° <?= $numeroPago ?></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Información del Pago</div>
        <table>
            <tr><td class="label">Número de Pago:</td><td>#<?= $pago['idpagos'] ?></td></tr>
            <tr><td class="label">Fecha y Hora:</td><td><?= $fechaPago ?></td></tr>
            <tr><td class="label">Contrato Asociado:</td><td>#<?= $pago['idcontrato'] ?></td></tr>
            <tr><td class="label">Tipo de Pago:</td><td><?= esc($tipoPago) ?></td></tr>
            <tr><td class="label">Referencia / N° Operación:</td><td><?= esc($referencia) ?></td></tr>
            <tr><td class="label">Registrado por:</td><td><?= esc($pago['nombreusuario'] ?? 'N/A') ?></td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Datos del Pagador</div>
        <table>
            <tr><td class="label">Documento de Identidad:</td><td><?= $pagadorDocumento ?></td></tr>
            <tr><td class="label">Nombre Completo:</td><td><?= $pagadorNombre ?></td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Datos del Cliente</div>
        <table>
            <tr><td class="label">Cliente:</td><td><?= esc($clienteNombre) ?></td></tr>
            <tr><td class="label">Documento:</td><td><?= esc($clienteDocumento) ?></td></tr>
            <tr><td class="label">Correo:</td><td><?= esc($clienteCorreo) ?></td></tr>
            <tr><td class="label">Teléfono:</td><td><?= esc($clienteTelefono) ?></td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Detalles del Pago</div>
        <table>
            <tr><td class="label">Total del Contrato:</td><td>S/ <?= $totalContrato ?></td></tr>
            <tr><td class="label">Saldo Anterior:</td><td>S/ <?= $saldoAnterior ?></td></tr>
            <tr><td class="label">Monto Pagado:</td><td>S/ <?= $montoPagado ?></td></tr>
            <tr><td class="label">Nuevo Saldo:</td><td>S/ <?= $nuevoSaldo ?></td></tr>
        </table>

        <?php if ($contratoPagado): ?>
            <div class="pagado">✔ CONTRATO CANCELADO EN SU TOTALIDAD</div>
        <?php endif; ?>
    </div>

    <div class="firma">
        <div class="firma-line">Firma y Sello Autorizado</div>
        <div>ISHUME Productora Audiovisual</div>
    </div>

    <div class="footer">
        Documento generado automáticamente el <?= date('d/m/Y H:i:s') ?>.<br>
        Este comprobante es válido como constancia de pago según normativa civil peruana.
    </div>
</div> <!-- voucher-document -->

<div class="actions">
    <button class="secondary" onclick="window.print()">Imprimir Comprobante</button>
    <button id="btnDescargarPdf">Descargar PDF</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    document.getElementById('btnDescargarPdf').addEventListener('click', function () {
        const elemento = document.getElementById('voucher-document');
        document.body.classList.add('pdf-exporting');
        elemento.classList.add('pdf-exporting');
        const opciones = {
            margin: 0,
            filename: `comprobante-<?= $numeroPago ?>.pdf`,
            image: { type: 'jpeg', quality: 0.95 },
            html2canvas: {
                scale: 2,
                useCORS: true,
                scrollY: 0,
                scrollX: 0
            },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().set(opciones).from(elemento).save().finally(() => {
            document.body.classList.remove('pdf-exporting');
            elemento.classList.remove('pdf-exporting');
        });
    });
</script>

</body>
</html>