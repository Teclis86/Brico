<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Stampa DDT <?php echo $ddt['number']; ?></title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.4; padding: 20px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .company-info { width: 45%; }
        .customer-info { width: 45%; border: 1px solid #000; padding: 10px; }
        .doc-info { margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .footer { margin-top: 50px; font-size: 12px; border-top: 1px solid #000; padding-top: 10px; }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 150px;
            color: rgba(255, 0, 0, 0.2);
            font-weight: bold;
            z-index: -1;
            pointer-events: none;
            border: 10px solid rgba(255, 0, 0, 0.2);
            padding: 20px;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <?php if(($ddt['status'] ?? 'confirmed') === 'cancelled'): ?>
        <div class="watermark">ANNULLATO</div>
    <?php endif; ?>

    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()">Stampa</button>
        <button onclick="window.close()">Chiudi</button>
    </div>

    <div class="header">
        <div class="company-info">
            <h3>BRICO STORE SRL</h3>
            <p>Via Roma, 123 - 00100 Roma (RM)<br>
            P.IVA: 12345678901<br>
            Tel: 06.12345678</p>
        </div>
        <div style="text-align: center; display: flex; align-items: center; justify-content: center;">
            <svg id="barcode"></svg>
        </div>
        <div class="customer-info">
            <strong>Destinatario:</strong><br>
            <?php echo htmlspecialchars($ddt['customer_name']); ?><br>
            <br>
            <strong>Luogo di Destinazione:</strong><br>
            <?php echo htmlspecialchars($ddt['destination']); ?>
        </div>
    </div>

    <div class="doc-info">
        <h2>DOCUMENTO DI TRASPORTO (D.D.T.)</h2>
        <p>
            <strong>Numero:</strong> <?php echo htmlspecialchars($ddt['number']); ?> &nbsp;&nbsp;&nbsp;
            <strong>Data:</strong> <?php echo date('d/m/Y', strtotime($ddt['date'])); ?>
        </p>
        <p><strong>Causale:</strong> Vendita / Trasferimento</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Codice Articolo</th>
                <th>Descrizione</th>
                <th style="text-align: right;">Q.tà</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['barcode']); ?></td>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td style="text-align: right;"><?php echo $item['quantity']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <strong>Note:</strong> <?php echo nl2br(htmlspecialchars($ddt['notes'])); ?>
    </div>

    <div class="footer">
        <div style="display: flex; justify-content: space-between;">
            <div style="width: 30%; border-top: 1px solid #000; padding-top: 5px;">Firma Conducente</div>
            <div style="width: 30%; border-top: 1px solid #000; padding-top: 5px;">Firma Destinatario</div>
            <div style="width: 30%; border-top: 1px solid #000; padding-top: 5px;">Firma Mittente</div>
        </div>
        <p style="margin-top: 20px; text-align: center;">Merce viaggiante a rischio e pericolo del committente.</p>
    </div>

    <script>
        JsBarcode("#barcode", "<?php echo $ddt['number']; ?>", {
            format: "CODE128",
            width: 2,
            height: 50,
            displayValue: true
        });
    </script>

</body>
</html>
