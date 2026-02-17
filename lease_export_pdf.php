<?php
// ---------- LEASE SUMMARY PDF ----------
date_default_timezone_set('Asia/Manila');

// ---------- DB CONNECT ----------
$con = mysqli_connect('localhost','root','root','tmc_admin_db');
$logfile = __DIR__ . '/lease_export_pdf_error.log';

// ---------- DETERMINE ACTION (INLINE OR DOWNLOAD) ----------
$action = isset($_GET['action']) ? $_GET['action'] : 'inline';

// ---------- PREVENT STRAY OUTPUT BEFORE PDF ----------
ob_start();

// ---------- MAIN TENANTS QUERY (NO SPACE_UNIT) ----------
        $sql = "
            SELECT l.tenant_id, l.tname, l.ddate,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM lease_pay_tbl p
                        WHERE p.tenant_id = l.tenant_id
                        AND YEAR(p.pdate) = YEAR(CURDATE())
                        AND MONTH(p.pdate) = MONTH(CURDATE())
                    ) THEN 1 ELSE 0
                END AS is_paid,
                DATE_ADD(l.ddate, INTERVAL (
                    SELECT COUNT(*) FROM lease_pay_tbl p2 WHERE p2.tenant_id = l.tenant_id
                ) MONTH) AS next_due
            FROM lease_tbl l
            ORDER BY l.tname ASC
        ";

        $res = mysqli_query($con, $sql);
        $rows = [];
        if ($res === false) {
            $err = 'Main tenants query failed: ' . mysqli_error($con) . " | SQL: " . $sql;
            @file_put_contents($logfile, date('[Y-m-d H:i:s] ') . $err . "\n", FILE_APPEND);
        } else {
            $num = mysqli_num_rows($res);
            @file_put_contents($logfile, date('[Y-m-d H:i:s] ') . "Main tenants query returned rows: $num\n", FILE_APPEND);
            if ($num > 0) {
                while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; }
            }
        }

// ---------- PAYMENTS AGGREGATION PER TENANT ----------
        $payMap = [];
        $resPaySql = "SELECT tenant_id, COUNT(*) AS months_paid, MAX(pdate) AS last_paid, SUM(payment + IFNULL(compensation,0)) AS total_paid FROM lease_pay_tbl GROUP BY tenant_id";
        $resPay = mysqli_query($con, $resPaySql);
        if ($resPay === false) {
            $err = 'Payments aggregation query failed: ' . mysqli_error($con) . " | SQL: " . $resPaySql;
            @file_put_contents($logfile, date('[Y-m-d H:i:s] ') . $err . "\n", FILE_APPEND);
        } else {
            while ($p = mysqli_fetch_assoc($resPay)) {
                $payMap[$p['tenant_id']] = [
                    'months_paid' => (int)$p['months_paid'],
                    'last_paid'   => $p['last_paid'],
                    'total_paid'  => (float)$p['total_paid']
                ];
            }
        }

// ---------- PREPARE METRICS ----------
        $totalTenants = count($rows);
$paidThisMonth = 0; $grandTotalPaid = 0.0;
        foreach ($rows as $r) {
            $tid = (int)$r['tenant_id'];
    if ((int)$r['is_paid'] === 1) { $paidThisMonth++; }
            $grandTotalPaid += isset($payMap[$tid]) ? $payMap[$tid]['total_paid'] : 0.0;
}


// ---------- UI FROM EXPORT_PDF.PHP (ADAPTED) ----------
        $logoPath = realpath(__DIR__ . '/tmclogo.png');
$reportTitle = 'TMC COMMERCIAL LEASING SUMMARY';
$reportSubtitle = 'Lease status and upcoming due dates';
$generatedAt = date('F d, Y h:i A');
$headerDate = date('F j, Y');

// ---------- BUILD HTML WITH MODERN STYLING ----------
$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; font-size: 12px; }
        .header { text-align: center; margin-bottom: 15px; position: relative; }
        .logo { position: absolute; float: left; width: 60px; height: auto; margin-right: 10px; }
        .title { font-size: 18px; font-weight: bold; color: #672222; margin-left: -80px; padding-top: 10px; }
        .subtitle { margin-top: -30px; font-size: 12px; }
        .divider { border-top: 1px solid #c8c8c8; margin: 10px 0; }
        .header-date { position: absolute; right: 0; top: 8px; font-size: 12px; color: #333; }

        .kpi-table { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 15px; background: transparent; }
        .kpi-table th { background: #f8f8f8; border: 1px solid #e6e6e6; border-right: none; border-bottom:none; padding: 8px 6px; font-size: 9px; font-weight: normal; color: #333; text-align: center; }
        .kpi-table th:last-child { border-right: 1px solid #e6e6e6; }
        .kpi-table td { background: #f8f8f8; border: 1px solid #e6e6e6; border-top: none; border-right: none; padding: 6px; font-size: 13px; font-weight: bold; text-align: center; }
        .kpi-table td:last-child { border-right: 1px solid #e6e6e6; }
        .kpi-green { color: #006400; }
        .kpi-blue { color: #000080; }
        .kpi-black { color: #000; }

        .section-title { font-size: 13px; font-weight: 700; margin: 20px 0 12px 0; text-transform: uppercase; color: #2c3e50; letter-spacing: 1px; border-bottom: 3px solid #672222; padding-bottom: 5px; position: relative; }
        .section-title::after { content: ""; position: absolute; bottom: -3px; left: 0; width: 30px; height: 3px; background: linear-gradient(90deg, #672222, #8b3a3a); }

        .table { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 20px; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .table th, .table td { border: none; border-bottom: 1px solid #e8e8e8; padding: 8px; font-size: 11px; vertical-align: middle; text-align: center; }
        .table th { background: #672222; color: #ffffff; font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; padding: 10px 8px; }
        .table tbody tr:nth-child(even) { background-color: #fafafa; }
        .table tbody tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .no-records { text-align: center; font-style: italic; font-size: 12px; padding: 20px; color: #6c757d; }

        .footer { position: fixed; bottom: 0; left: 10px; right: 10px; font-size: 9px; color: #787878; border-top: 1px solid #e6e6e6; padding-top: 2px; }
        .page-number { float: right; }
    </style>
</head>
<body>';

// ---------- HEADER ----------
$html .= '
<div class="header">
    <img src="tmclogo.png" class="logo" style="width: 18mm; height: 18mm;">
    <div style="display: inline-block;">
        <div class="title">' . $reportTitle . '</div>
        <div class="subtitle">' . $reportSubtitle . '</div>
         <div style="font-size: 10px; color: #666; margin-top: 5px;">Marana 1st, City of Ilagan, Isabela</div>
    </div>
    <div class="header-date">' . $headerDate . '</div>
    <div class="divider"></div>
</div>';

// ---------- EXECUTIVE SUMMARY (KPI TABLE) ----------
$html .= '<div class="section-title">Executive Summary</div>
<table class="kpi-table">
    <thead>
        <tr>
            <th style="width: 20%;">Total Tenants</th>
            <th style="width: 20%;">Paid This Month</th>
            <th style="width: 60%;">Total Collections (₱)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="kpi-black">' . number_format($totalTenants) . '</td>
            <td class="kpi-blue">' . number_format($paidThisMonth) . '</td>
            <td class="kpi-green">' . number_format($grandTotalPaid, 2) . '</td>
        </tr>
    </tbody>
</table>';

// ---------- TENANT DETAILS TABLE ----------
$html .= '<div class="section-title">Tenant Details</div>';
        if ($totalTenants === 0) {
            $html .= '<div class="no-records">No tenant records found.</div>';
        } else {
    $html .= '<table class="table">
        <thead>
            <tr>
                <th class="text-left" style="width: 20%;">Tenant</th>
                <th style="width: 12%;">Start Date</th>
                <th style="width: 12%;">Months Paid</th>
                <th style="width: 14%;">Total Paid (₱)</th>
                <th style="width: 14%;">Last Paid</th>
                <th style="width: 13%;">Status</th>
                <th style="width: 15%;">Next Due</th>
            </tr>
        </thead>
        <tbody>';
            foreach ($rows as $r) {
                $tid = (int)$r['tenant_id'];
                $monthsPaid = isset($payMap[$tid]) ? $payMap[$tid]['months_paid'] : 0;
                $totalPaid = isset($payMap[$tid]) ? $payMap[$tid]['total_paid'] : 0.0;
                $lastPaid = isset($payMap[$tid]) && $payMap[$tid]['last_paid'] ? date('M d, Y', strtotime($payMap[$tid]['last_paid'])) : '&mdash;';
                $start = new DateTime($r['ddate']);
                $status = ((int)$r['is_paid'] === 1) ? 'PAID (this month)' : 'NOT PAID';
                $nextDue = $r['next_due'] ? date('M d, Y', strtotime($r['next_due'])) : '&mdash;';

        $html .= '<tr>
            <td class="text-left">'.htmlspecialchars(strtoupper($r['tname'])).'</td>
            <td>' . $start->format('M d, Y') . '</td>
            <td>' . $monthsPaid . '</td>
            <td class="text-right">' . number_format($totalPaid, 2) . '</td>
            <td>' . $lastPaid . '</td>
            <td>' . $status . '</td>
            <td>' . $nextDue . '</td>
        </tr>';
    }
    $html .= '</tbody></table>';
        }

        $html .= '</body></html>';

// ---------- GENERATE PDF USING MPDF ----------
require_once __DIR__ . '/vendor/autoload.php';
try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'Legal',
        'orientation' => 'P',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 20,
        'margin_bottom' => 20,
        'margin_header' => 0,
        'margin_footer' => 18
    ]);

    $pdfTitle = 'TMC Lease Summary - ' . date('F j, Y');
    $mpdf->SetTitle($pdfTitle);
            $mpdf->SetAuthor('TMC');
    $mpdf->SetCreator('TMC Reporting');
    $mpdf->SetSubject('Lease summary overview');
    $mpdf->SetKeywords('TMC, lease, tenants, report, PDF');

    $mpdf->SetHTMLFooter('
        <div class="footer">
            <span>' . $generatedAt . '</span>
            <span class="page-number">Page {PAGENO} of {nbpg}</span>
        </div>
    ');

            $mpdf->WriteHTML($html);

    // ---------- CLEAR OUTPUT BUFFERS BEFORE OUTPUTTING PDF ----------
    ob_clean();

    $filename = 'Lease_Summary_' . date('Ymd') . '.pdf';
            if ($action === 'download') {
                $mpdf->Output($filename, 'D');
            } else {
                $mpdf->Output($filename, 'I');
            }
        } catch (\Exception $e) {
            $err = 'mPDF generation error: ' . $e->getMessage();
            @file_put_contents($logfile, date('[Y-m-d H:i:s] ') . $err . "\n", FILE_APPEND);
            header('Content-Type: text/plain; charset=utf-8');
            echo $err;
        }

        ?>
