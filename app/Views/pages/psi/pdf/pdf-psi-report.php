<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P2H Report</title>

    <style>
        @page {
            margin: 140px 20px 20px 20px;
        }

        body {
            font-family: 'Helvetica', sans-serif;
        }

        .pageHeader {
            position: fixed;
            top: -120px;
            left: 0;
            right: 0;
            height: 100px;
            z-index: -1;
            padding-bottom: 10px;
        }

        .header-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 90%;
            height: 100px;
        }

        .bago {
            position: absolute;
            top: 0;
            height: 100px;
            border-top-right-radius: 25%;
            border-bottom-right-radius: 25%;
        }

        .s-1 {
            height: 100%;
            width: 85%;
            background-color: #2d3628;
            left: 0;
            z-index: 1;
        }

        .s-2 {
            height: 100%;
            width: 65%;
            background-color: #627254;
            left: 0;
            z-index: 2;
        }

        .s-3 {
            height: 100%;
            width: 45%;
            background-color: #cce0c2;
            left: 0;
            z-index: 2;
            font-size: 3mm;
        }

        .logo-container {
            position: absolute;
            top: 0;
            right: 0;
            width: 10%;
            height: 100px;
            text-align: right;
        }

        #hte_logo {
            height: 80px;
            width: auto;
            margin-top: 10px;
        }

        .mainContent {
            padding: 0;
            color: #333;
        }

        .equipment-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .equipment-meta {
            background-color: #f9f9f9;
            padding: 0;
            border-radius: 5px;
            margin-bottom: 0;
            border-left: 4px solid #2d3628;
        }

        .details {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .details th:nth-child(1) {
            width: 35%;
        }

        .details th:nth-child(2) {
            width: 15%;
        }

        .details th:nth-child(3) {
            width: 15%;
        }

        .details th:nth-child(4) {
            width: 35%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #2d3628;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 12px;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }

        tr:nth-child(even) {
            background-color: #fcfcfc;
        }

        .status-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
            background: #ff8a8a;
            color: #440000;
        }

        /* Specific styles for the signature table */
        .signature-table {
            border: 1px solid #ddd;
        }

        .signature-table th {
            background-color: #627254;
            /* Lighter green to distinguish from main table */
            padding: 6px 10px;
        }

        .signature-table td {
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <div class="pageHeader">
        <div class="header-bg">
            <div class="s-1 bago"></div>
            <div class="s-2 bago"></div>
            <div class="s-3 bago">
                <h1><small>Mine Operation - P2H Report</small></h1>
                <p><small>This page displays only active defects identified during pre-start inspections. It does not reflect fully operational equipment; please use this list to prioritize urgent maintenance and safety repairs.</small></p>
            </div>
        </div>

        <div class="logo-container">
            <img src="<?= FCPATH . 'asset/page/template/logo_1-1.png'; ?>" alt="hte_logo" id="hte_logo">
        </div>
    </div>

    <div class="mainContent">
        <?php if (!empty($combined_data)): ?>
            <?php foreach ($combined_data as $typeName => $sessions): ?>
                <div class="equipmentSegment">
                    <h2 style="color: #2d3628; border-bottom: 2px solid #627254; padding-bottom: 5px;">
                        <?= esc(ucwords(str_replace('_', ' ', $typeName))) ?>
                    </h2>

                    <?php foreach ($sessions as $item): ?>
                        <div class="equipment-card">
                            <div class="equipment-meta">
                                <table style="width: 100%; margin-top: 0;">
                                    <tr>
                                        <td style="font-size: large; border: none;"><strong>ID:</strong> <?= esc($item['equipment_id']) ?></td>
                                        <td style="border: none;"><strong>Shift:</strong> <?= esc($item['shift']) ?></td>
                                        <td style="border: none;"><strong>Date:</strong> <?= esc($item['date']) ?></td>
                                        <td style="border: none;"><strong>HM:</strong> <?= esc($item['hm_start']) ?> - <?= esc($item['hm_end']) ?></td>
                                    </tr>
                                </table>
                            </div>

                            <table class="details">
                                <thead>
                                    <tr>
                                        <th>Check Item</th>
                                        <th>Danger Tag</th>
                                        <th>Status</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $rows = explode(';;', $item['psi_details']);
                                    foreach ($rows as $rowData):
                                        $parts = explode('|', $rowData);
                                        $checkName = $parts[0] ?? '-';
                                        $tagName   = $parts[1] ?? '-';
                                        $note      = $parts[2] ?? '-';
                                    ?>
                                        <tr>
                                            <td style="font-weight: bold;"><?= esc($checkName) ?></td>
                                            <td><?= esc($tagName) ?></td>
                                            <td><span class="status-badge">Open</span></td>
                                            <td><?= esc($note) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <?php if (!empty($item['validation'])): ?>
                                <table class="signature-table" style="margin-top: 15px;">
                                    <thead>
                                        <tr>
                                            <th style="width: 35%;">Name</th>
                                            <th style="width: 40%;">Note</th>
                                            <th style="width: 25%; text-align: center;">Signature</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>FM:</strong> <?= esc($item['validation']['fm_name'] ?? '-') ?></td>
                                            <td><?= esc($item['validation']['fm_note'] ?? '-') ?></td>
                                            <td style="text-align: center; padding: 2px;">
                                                <?php if (!empty($item['validation']['fm_sign'])): ?>
                                                    <img src="<?= $item['validation']['fm_sign'] ?>" style="max-height: 40px; width: auto;" alt="FM Sign">
                                                <?php else: ?>
                                                    <span style="color: #999; font-size: 10px;">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>SPV:</strong> <?= esc($item['validation']['spv_name'] ?? '-') ?></td>
                                            <td><?= esc($item['validation']['spv_note'] ?? '-') ?></td>
                                            <td style="text-align: center; padding: 2px;">
                                                <?php if (!empty($item['validation']['spv_sign'])): ?>
                                                    <img src="<?= $item['validation']['spv_sign'] ?>" style="max-height: 40px; width: auto;" alt="SPV Sign">
                                                <?php else: ?>
                                                    <span style="color: #999; font-size: 10px;">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>

</html>