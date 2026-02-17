<?php
// dailygaspdf.php — Modern Daily Report with mPDF
require_once __DIR__ . '/vendor/autoload.php';

// Start output buffering to prevent any output before PDF generation
ob_start();

date_default_timezone_set('Asia/Manila');

// Helper function to format currency
function formatCurrency($amount) {
    return number_format(floatval($amount ?: 0), 2);
}

// Helper function to safely get array value
function safeGet($array, $key, $default = 0) {
    return isset($array[$key]) ? $array[$key] : $default;
}

// ---- Start session to get user info ----
session_start();

// ---- DB connect ----
$con = mysqli_connect('localhost','root','root','tmc_admin_db');
if (!$con) {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Database connection failed: ' . mysqli_connect_error();
    exit;
}
mysqli_set_charset($con, 'utf8');

// ---- Inputs ----
$action = isset($_POST['action']) ? $_POST['action'] : 'view';
$row_id = isset($_POST['row_id']) ? intval($_POST['row_id']) : 0;
$period = isset($_POST['period']) ? $_POST['period'] : '';

// ---- Get current user information (full name) ----
$preparedBy = 'System Administrator'; // Default fallback
if (!empty($_SESSION['fullname'])) {
    $preparedBy = $_SESSION['fullname'];
} else {
    $first = isset($_SESSION['fname']) ? trim($_SESSION['fname']) : '';
    $last  = isset($_SESSION['lname']) ? trim($_SESSION['lname']) : '';
    $user  = isset($_SESSION['username']) ? trim($_SESSION['username']) : '';
    if ($first !== '' || $last !== '') {
        $preparedBy = trim($first . ' ' . $last);
    } elseif ($user !== '') {
        $preparedBy = $user;
    }
}

// If we have a username, try to pull full name from tbl_pending_users (in main user DB)
if (!empty($_SESSION['username'])) {
    $pendingUser = mysqli_real_escape_string($con, $_SESSION['username']);
    // tbl_pending_users lives in tmc_db (registration DB), so reference it explicitly
    $pendingSql = "SELECT fname, mname, lname FROM tmc_db.tbl_pending_users WHERE username='$pendingUser' LIMIT 1";
    $pendingRes = mysqli_query($con, $pendingSql);
    if ($pendingRes && mysqli_num_rows($pendingRes) > 0) {
        $pendingRow = mysqli_fetch_assoc($pendingRes);
        $fn = isset($pendingRow['fname']) ? trim($pendingRow['fname']) : '';
        $mn = isset($pendingRow['mname']) ? trim($pendingRow['mname']) : '';
        $ln = isset($pendingRow['lname']) ? trim($pendingRow['lname']) : '';
        $fullPendingName = trim(preg_replace('/\s+/', ' ', $fn . ' ' . $mn . ' ' . $ln));
        if ($fullPendingName !== '') {
            $preparedBy = $fullPendingName;
        }
    }
}

// Resolve row/date/branch
$branch = '';
$dateForReport = '';
if ($row_id > 0) {
    $metaRes = mysqli_query($con, "SELECT branch, log_date FROM gas_sales_tbl WHERE id=" . intval($row_id) . " LIMIT 1");
    if ($metaRes && mysqli_num_rows($metaRes) > 0) {
        $metaRow = mysqli_fetch_assoc($metaRes);
        $branch = $metaRow['branch'];
        $dateForReport = $metaRow['log_date'];
    }
}
if ($dateForReport == '' && $period != '') {
    $dateForReport = $period; // fallback if provided
}

if ($dateForReport == '') {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No date provided for report.';
    exit;
}

// Fetch aggregated fuel rows for this date and branch
$fuelData = [
    'Diesel' => ['volume' => 0, 'price' => 0, 'total' => 0],
    'Premium' => ['volume' => 0, 'price' => 0, 'total' => 0],
    'Unleaded' => ['volume' => 0, 'price' => 0, 'total' => 0],
];
$fuelQuery = "SELECT fuel_type, SUM(volume_sold) AS volume, MAX(fuel_price) AS price, SUM(total_amount) AS total
              FROM gas_sales_tbl
              WHERE DATE(log_date)='" . mysqli_real_escape_string($con, $dateForReport) . "'";
if ($branch != '') {
    $fuelQuery .= " AND branch='" . mysqli_real_escape_string($con, $branch) . "'";
}
$fuelQuery .= " GROUP BY fuel_type";
$resGas = mysqli_query($con, $fuelQuery);
if ($resGas && mysqli_num_rows($resGas) > 0) {
    while ($row = mysqli_fetch_assoc($resGas)) {
        $type = $row['fuel_type'];
        if (isset($fuelData[$type])) {
            $fuelData[$type]['volume'] = floatval($row['volume']);
            $fuelData[$type]['price'] = floatval($row['price']);
            $fuelData[$type]['total'] = floatval($row['total']);
        }
    }
}

$lubeRows = [];
$sqlLube = "SELECT branch, pname, SUM(quantity) AS quantity, MAX(pprice) AS pprice, SUM(quantity * pprice) AS line_total
            FROM lub_sales_tbl WHERE DATE(log_date)='" . mysqli_real_escape_string($con, $dateForReport) . "'";
if ($branch != '') { $sqlLube .= " AND branch='" . mysqli_real_escape_string($con, $branch) . "'"; }
$sqlLube .= " GROUP BY branch, pname";
$resLube = mysqli_query($con, $sqlLube);
if ($resLube && mysqli_num_rows($resLube) > 0) {
    while ($r = mysqli_fetch_assoc($resLube)) { $lubeRows[] = $r; }
}

$expRows = [];
$sqlExp = "SELECT branch, expense_type, other_description, amount FROM gas_expenses_tbl WHERE DATE(log_date)='" . mysqli_real_escape_string($con, $dateForReport) . "'";
if ($branch != '') { $sqlExp .= " AND branch='" . mysqli_real_escape_string($con, $branch) . "'"; }
$resExp = mysqli_query($con, $sqlExp);
if ($resExp && mysqli_num_rows($resExp) > 0) {
    while ($r = mysqli_fetch_assoc($resExp)) { $expRows[] = $r; }
}

// ---- Get Prepared By Information (similar to export_pdf.php) ----
$allPreparedBy = [];
$prepSql = "SELECT DISTINCT preparedby FROM gas_sales_tbl WHERE DATE(log_date)='" . mysqli_real_escape_string($con, $dateForReport) . "' AND preparedby IS NOT NULL AND preparedby <> ''";
if ($branch != '') { $prepSql .= " AND branch='" . mysqli_real_escape_string($con, $branch) . "'"; }
$prepRes = mysqli_query($con, $prepSql);
if ($prepRes && mysqli_num_rows($prepRes) > 0) {
    while ($p = mysqli_fetch_assoc($prepRes)) {
        $prepName = trim($p['preparedby']);
        if ($prepName !== '' && !in_array($prepName, $allPreparedBy)) {
            $allPreparedBy[] = $prepName;
        }
    }
}
$preparedByText = !empty($allPreparedBy) ? implode(', ', $allPreparedBy) : $preparedBy;

// ---- mPDF Configuration ----
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

// ---- Set PDF Properties ----
$reportTitle = 'TMC GASOLINE STATION SALES REPORT';
$dateForReport = date('F j, Y', strtotime($dateForReport));
$reportSubtitle = 'For the day of ' . $dateForReport;
$pdfTitle = 'Daily Sales Report - ' . $dateForReport;
$generatedAt = date('F d, Y h:i A');

$mpdf->SetTitle($pdfTitle);
$mpdf->SetAuthor('TMC');
$mpdf->SetCreator('TMC Reporting');
$mpdf->SetSubject('Daily sales for selected branch/date');
$mpdf->SetKeywords('TMC, sales, daily, fuel, lubricant, report, PDF');

// ---- Start HTML Content ----
$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        /* ===== BASE ===== */
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 0;
            font-size: 12px;
        }

        /* ===== HEADER ===== */
        .header { 
            text-align: center; 
            margin-bottom: 15px; 
            position: relative;
        }

        .logo { 
            position: absolute;
            float: left; 
            width: 60px;
            height: auto;
            margin-right: 10px;
        }

        .title { 
            font-size: 18px; 
            font-weight: bold; 
            color: #672222; 
            margin-left: -80px;
            padding-top: 10px;
        }

        .subtitle { 
            margin-top: -30px;
            font-size: 12px; 
        }

        .divider { 
            border-top: 1px solid #c8c8c8; 
            margin: 10px 0; 
        }

        /* ===== KPI TABLE STYLING ===== */
        .kpi-green { color: #006400; }
        .kpi-red { color: #990000; }
        .kpi-black { color: #000000; }
        .kpi-blue { color: #000080; }
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 15px;
            background: transparent;
            border-radius: 0;
            box-shadow: none;
        }

        .kpi-table th {
            background: #f8f8f8;
            border: 1px solid #e6e6e6;
            border-right: none;
            border-bottom:none;
            padding: 8px 6px;
            font-size: 9px;
            font-weight: normal;
            text-transform: none;
            letter-spacing: normal;
            color: #333;
            text-align: center;
            vertical-align: middle;
        }

        .kpi-table th:last-child {
            border-right: 1px solid #e6e6e6;
        }

        .kpi-table td {
            background: #f8f8f8;
            border: 1px solid #e6e6e6;
            border-top: none;
            border-right: none;
            padding: 6px;
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }

        .kpi-table td:last-child {
            border-right: 1px solid #e6e6e6;
        }

        .kpi-table tbody tr:hover {
            background-color: #f0f8ff;
        }

        /* ===== SECTIONS ===== */
        .section-title { 
            font-size: 13px; 
            font-weight: 700; 
            margin: 20px 0 12px 0; 
            text-transform: uppercase;
            color: #2c3e50;
            letter-spacing: 1px;
            border-bottom: 3px solid #672222;
            padding-bottom: 5px;
            position: relative;
        }

        .section-title::after {
            content: \'\';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 30px;
            height: 3px;
            background: linear-gradient(90deg, #672222, #8b3a3a);
        }

        /* ===== MODERN TABLES ===== */
        .table { 
            width: 100%; 
            border-collapse: separate; 
            border-spacing: 0;
            margin-bottom: 20px; 
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .table th, 
        .table td { 
            border: none;
            border-bottom: 1px solid #e8e8e8; 
            padding: 8px; 
            font-size: 11px; 
            vertical-align: middle;
            text-align: center;
        }

        .table th { 
            background: #672222; 
            color: #ffffff;
            font-weight: 600; 
            text-align: center; 
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 8px;
        }

        .table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .table tbody tr:hover {
            background-color: #f0f8ff;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }

        .total-row { 
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); 
            font-weight: 600; 
            border-top: 2px solid #672222;
        }

        .total-row td {
            border-top: 2px solid #672222;
            border-bottom: none;
            font-size: 11px;
        }

        /* ===== FOOTER ===== */
        .footer { 
            position: fixed; 
            bottom: 0; 
            left: 10px; 
            right: 10px; 
            font-size: 9px; 
            color: #787878; 
            border-top: 1px solid #e6e6e6; 
            padding-top: 2px; 
        }

        .page-number { float: right; }

        .branch-header {
            font-size: 14px;
            font-weight: bold;
            color: #672222;
            margin-bottom: 10px;
            border-bottom: 1px solid #c8c8c8;
            padding-bottom: 5px;
        }

        .financial-summary {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 11px;
        }

        .summary-total {
            border-top: 1px solid #006400;
            font-weight: bold;
            color: #006400;
            font-size: 12px;
            margin-top: 8px;
            padding-top: 5px;
        }

        /* ===== SUMMARY TABLE STYLING ===== */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            background: transparent;
            border: none;
            box-shadow: none;
        }

        .summary-table td {
            border: none;
            padding: 5px 0;
            font-size: 11px;
            vertical-align: middle;
            background: transparent;
        }

        .summary-table td:first-child {
            text-align: left;
        }

        .summary-table td:last-child {
            text-align: right;
        }

        .summary-table .summary-total-row td {
            border-top: 1px solid #006400;
            font-weight: bold;
            color: #006400;
            font-size: 12px;
            margin-top: 8px;
            padding-top: 5px;
        }

        /* ===== PREPARED BY STYLING ===== */
        .prepared-by {
            margin-top: 30px;
            text-align: right;
            font-size: 11px;
        }

        .prepared-by-label {
            margin-bottom: 5px;
            color: #333;
        }

        .prepared-by-signature {
            font-weight: bold;
            font-size: 12px;
            border-bottom: 1px solid #000;
            width: 200px;
            margin-left: auto;
            padding-bottom: 2px;
            color: #2c3e50;
        }
    </style>
</head>
<body>';

// Header
$html .= '
<div class="header">
    <img src="tmclogo.png" class="logo" style="width: 18mm; height: 18mm;">
    <div style="display: inline-block;">
        <div class="title">' . $reportTitle . '</div>
        <div class="subtitle">' . $reportSubtitle . '</div>
        <div style="font-size: 9px; color: #888; margin-top: 3px;">Marana 1st, City of Ilagan, Isabela</div>
    </div>
    <div class="divider"></div>
</div>';

// Determine if we have any fuel data
$hasFuelData = array_sum(array_column($fuelData, 'total')) > 0;

if (!$hasFuelData) {
    $html .= '<div style="text-align: center; font-style: italic; font-size: 12px; padding: 20px; color: #6c757d;">No data found for the selected record.</div>';
} else {
    $branchName = $branch !== '' ? $branch : 'All Branches';
    
    // Branch title
    $html .= '<div class="branch-header">Branch: ' . $branchName . '</div>';

    // Calculate totals
    $fuelTotal = ($fuelData['Diesel']['total'] ?: 0) + ($fuelData['Premium']['total'] ?: 0) + ($fuelData['Unleaded']['total'] ?: 0);
    $lubeSum = 0.0;
    foreach ($lubeRows as $l) {
        $lineTotal = isset($l['line_total']) ? $l['line_total'] : (($l['quantity'] ?: 0) * ($l['pprice'] ?: 0));
        if ($branch === '' || $l['branch'] === $branchName) {
            $lubeSum += $lineTotal;
        }
    }
    $expSum = 0.0;
    foreach ($expRows as $er) { 
        $expSum += ($er['amount'] ?: 0); 
    }
    $netSum = ($fuelTotal + $lubeSum) - $expSum;

    // Branch KPIs (Fuel, Lubes, Expenses, Net)
    $html .= '<table class="kpi-table">
        <thead>
            <tr>
                <th style="width: 25%;">Fuel Sales (₱)</th>
                <th style="width: 25%;">Lubricant Sales (₱)</th>
                <th style="width: 25%;">Expenses (₱)</th>
                <th style="width: 25%;">Net Sales (₱)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="kpi-black">' . formatCurrency($fuelTotal) . '</td>
                <td class="kpi-black">' . formatCurrency($lubeSum) . '</td>
                <td class="kpi-red">' . formatCurrency($expSum) . '</td>
                <td class="kpi-green">' . formatCurrency($netSum) . '</td>
            </tr>
        </tbody>
    </table>';

    // Fuel Sales table
    $html .= '<div class="section-title">Fuel Sales</div>
    <table class="table">
        <thead>
            <tr>
                <th class="text-center" style="width: 26%;">Fuel Type</th>
                <th class="text-center" style="width: 21%;">Volume Sold(L)</th>
                <th class="text-center" style="width: 26%;">Price per L (₱)</th>
                <th class="text-center" style="width: 27%;">Total (₱)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">Diesel</td>
                <td class="text-center">' . formatCurrency($fuelData['Diesel']['volume']) . '</td>
                <td class="text-center">' . formatCurrency($fuelData['Diesel']['price']) . '</td>
                <td class="text-center">' . formatCurrency($fuelData['Diesel']['total']) . '</td>
            </tr>
            <tr>
                <td class="text-center">Premium</td>
                <td class="text-center">' . formatCurrency($fuelData['Premium']['volume']) . '</td>
                <td class="text-center">' . formatCurrency($fuelData['Premium']['price']) . '</td>
                <td class="text-center">' . formatCurrency($fuelData['Premium']['total']) . '</td>
            </tr>
            <tr>
                <td class="text-center">Unleaded</td>
                <td class="text-center">' . formatCurrency($fuelData['Unleaded']['volume']) . '</td>
                <td class="text-center">' . formatCurrency($fuelData['Unleaded']['price']) . '</td>
                <td class="text-center">' . formatCurrency($fuelData['Unleaded']['total']) . '</td>
            </tr>
            <tr class="total-row">
                <td colspan="3" class="text-right">Fuel Total</td>
                <td class="text-center">₱ ' . formatCurrency($fuelTotal) . '</td>
            </tr>
        </tbody>
    </table>';

    // Lubricant Sales table
    $html .= '<div class="section-title">Lubricant Sales</div>
    <table class="table">
        <thead>
            <tr>
                <th class="text-center" style="width: 42%;">Product Name</th>
                <th class="text-center" style="width: 16%;">Quantity</th>
                <th class="text-center" style="width: 21%;">Price (₱)</th>
                <th class="text-center" style="width: 21%;">Total (₱)</th>
            </tr>
        </thead>
        <tbody>';

    $hasLube = false;
    $lubeTotal = 0;
    foreach ($lubeRows as $l) {
        if ($l['branch'] === $branchName) {
            $lineTotal = ($l['quantity'] ?: 0) * ($l['pprice'] ?: 0);
            $html .= '<tr>
                <td class="text-left">' . $l['pname'] . '</td>
                <td class="text-center">' . $l['quantity'] . '</td>
                <td class="text-center">' . formatCurrency($l['pprice']) . '</td>
                <td class="text-center">' . formatCurrency($lineTotal) . '</td>
            </tr>';
            $lubeTotal += $lineTotal;
            $hasLube = true;
        }
    }
    
    if (!$hasLube) {
        $html .= '<tr>
            <td colspan="4" class="text-center" style="padding: 20px; color: #6c757d; font-style: italic;">(No lubricant sales for this branch)</td>
        </tr>';
    } else {
        $html .= '<tr class="total-row">
            <td colspan="3" class="text-right">Lubricant Total</td>
            <td class="text-center">₱ ' . formatCurrency($lubeTotal) . '</td>
        </tr>';
    }
    
    $html .= '</tbody>
    </table>';

    // Expenses table
    $html .= '<div class="section-title">Expenses</div>
    <table class="table">
        <thead>
            <tr>
                <th class="text-center" style="width: 50%;">Type / Description</th>
                <th class="text-center" style="width: 50%;">Amount (₱)</th>
            </tr>
        </thead>
        <tbody>';

    $hasExp = false;
    $expTotal = 0;
    foreach ($expRows as $er) {
        $label = $er['expense_type'];
        if (!empty($er['other_description'])) { 
            $label .= ' - '.$er['other_description']; 
        }
        $html .= '<tr>
            <td class="text-left">' . $label . '</td>
            <td class="text-right">' . formatCurrency($er['amount']) . '</td>
        </tr>';
        $hasExp = true; 
        $expTotal += ($er['amount'] ?: 0);
    }
    
    if (!$hasExp) {
        $html .= '<tr>
            <td colspan="2" class="text-center" style="padding: 20px; color: #6c757d; font-style: italic;">(No expenses recorded)</td>
        </tr>';
    } else {
        $html .= '<tr class="total-row">
            <td class="text-right">Total Expenses</td>
            <td class="text-right">₱ ' . formatCurrency($expTotal) . '</td>
        </tr>';
    }
    
    $html .= '</tbody>
    </table>';

    // Branch Financial Summary - Professional Format
    $grandTotal = $fuelTotal + $lubeTotal;
    $net = $grandTotal - $expTotal;
    
    $html .= '<div class="section-title">Branch Financial Summary</div>
    <div class="financial-summary">
        <table class="summary-table">
            <tr>
                <td>Total Sales (Fuel + Lubricants):</td>
                <td style="text-align:right;">₱ ' . formatCurrency($grandTotal) . '</td>
            </tr>
            <tr>
                <td>Less: Operating Expenses:</td>
                <td style="color: #b40000; text-align:right;">₱ ' . formatCurrency($expTotal) . '</td>
            </tr>
            <tr class="summary-total-row">
                <td>Net Profit:</td>
                <td style="text-align:right;">₱ ' . formatCurrency($net) . '</td>
            </tr>
        </table>
    </div>';
}

// Add Prepared By section
$html .= '


<div style="margin-top: 40px; page-break-inside: avoid;">
    <div style="padding-top: 20px; margin-top: 30px;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="text-align: left;">
                

                <div style="font-size: 11px; color: #666; margin-bottom: 5px;">Prepared by:</div>
                <div style="text-align:center; font-size: 12px; font-weight: bold; color: #2c3e50; border-bottom: 1px solid #000; width: 250px; padding-bottom: 2px;">
                    ' . htmlspecialchars($preparedByText) . '
                </div>
            </div>
            
        </div>
        
    </div>
</div>';

// Close HTML and add footer
$html .= '</body>
</html>';

// Set footer
$mpdf->SetHTMLFooter('
    <div class="footer">
        <span>' . $generatedAt . '</span>
        <span class="page-number">Page {PAGENO} of {nbpg}</span>
    </div>
');

// Write HTML to PDF
$mpdf->WriteHTML($html);

// ---- Output ----
// Clear any output buffer before generating PDF
ob_clean();

$filename = 'Daily_Report_' . ($branch ? str_replace(' ','_',$branch).'_' : '') . $dateForReport . '.pdf';
if ($action === 'download') {
    $mpdf->Output($filename, 'D');
} else {
    $mpdf->Output($filename, 'I');
}