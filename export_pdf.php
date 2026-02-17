<?php
// export_pdf.php — Modern Table Layout with mPDF
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
$period = isset($_POST['period']) ? $_POST['period'] : date('Y-m-d');
$report_type = isset($_POST['report_type']) ? $_POST['report_type'] : 'daily';

$gasRows = [];
$lubeRows = [];
// expenses
$expRows = [];

// ---- Build WHERE clauses based on report_type (aligned to per-fuel rows) ----
$gasWhere = '';
$lubeWhere = '';
$expWhere = '';

switch($report_type) {
    case 'daily':
        $periodEsc = mysqli_real_escape_string($con, $period);
        $gasWhere  = "DATE(log_date) = '$periodEsc'";
        $lubeWhere = "DATE(log_date) = '$periodEsc'";
        $expWhere  = "DATE(log_date) = '$periodEsc'";
        $report_label = date('F j, Y', strtotime($period));
        break;

    case 'monthly':
        $dateObj = DateTime::createFromFormat('F Y', $period);
        if (!$dateObj) {
            $dateObj = new DateTime();
        }
        $month = $dateObj->format('m');
        $year  = $dateObj->format('Y');
        $gasWhere  = "YEAR(log_date) = $year AND MONTH(log_date) = $month";
        $lubeWhere = "YEAR(log_date) = $year AND MONTH(log_date) = $month";
        $expWhere  = "YEAR(log_date) = $year AND MONTH(log_date) = $month";
        $report_label = $period;
        break;

    case 'yearly':
        $year = intval($period);
        $gasWhere  = "YEAR(log_date) = $year";
        $lubeWhere = "YEAR(log_date) = $year";
        $expWhere  = "YEAR(log_date) = $year";
        $report_label = $period;
        break;

    default:
        $periodEsc = mysqli_real_escape_string($con, $period);
        $gasWhere  = "DATE(log_date) = '$periodEsc'";
        $lubeWhere = "DATE(log_date) = '$periodEsc'";
        $expWhere  = "DATE(log_date) = '$periodEsc'";
        $report_label = date('F j, Y', strtotime($period));
        break;
}

// ---- Queries (per-fuel rows aggregated) ----
$sqlGas = "SELECT branch,
                  fuel_type,
                  SUM(volume_sold) AS volume,
                  MAX(fuel_price) AS price,
                  SUM(total_amount) AS total,
                  GROUP_CONCAT(DISTINCT preparedby SEPARATOR ', ') AS preparedby
           FROM gas_sales_tbl
           WHERE $gasWhere
           GROUP BY branch, fuel_type
           ORDER BY branch";

$sqlLube = "SELECT branch, pname, SUM(quantity) AS quantity, MAX(pprice) AS pprice, SUM(quantity * pprice) AS line_total
            FROM lub_sales_tbl
            WHERE $lubeWhere
            GROUP BY branch, pname";

$sqlExp = "SELECT branch, expense_type, other_description, amount 
           FROM gas_expenses_tbl 
           WHERE $expWhere";

// ---- Fetch Gas Sales (per-fuel aggregated) ----
$gasByBranch = [];
$allPreparedBy = [];
$resGas = mysqli_query($con, $sqlGas);
if ($resGas && mysqli_num_rows($resGas) > 0) {
    while ($r = mysqli_fetch_assoc($resGas)) {
        $branch = $r['branch'];
        $type   = $r['fuel_type'];
        if (!isset($gasByBranch[$branch])) {
            $gasByBranch[$branch] = [
                'branch' => $branch,
                'dvs' => 0, 'dvsp' => 0, 'dtotal' => 0,
                'pvs' => 0, 'pvsp' => 0, 'ptotal' => 0,
                'uvs' => 0, 'uvsp' => 0, 'utotal' => 0,
                'preparedby_list' => []
            ];
        }

        $volume = floatval($r['volume']);
        $price  = floatval($r['price']);
        $total  = floatval($r['total']);

        if ($type === 'Diesel') {
            $gasByBranch[$branch]['dvs']    = $volume;
            $gasByBranch[$branch]['dvsp']   = $price;
            $gasByBranch[$branch]['dtotal'] = $total;
        } elseif ($type === 'Premium') {
            $gasByBranch[$branch]['pvs']    = $volume;
            $gasByBranch[$branch]['pvsp']   = $price;
            $gasByBranch[$branch]['ptotal'] = $total;
        } elseif ($type === 'Unleaded') {
            $gasByBranch[$branch]['uvs']    = $volume;
            $gasByBranch[$branch]['uvsp']   = $price;
            $gasByBranch[$branch]['utotal'] = $total;
        }

        if (!empty($r['preparedby'])) {
            $prepList = explode(', ', $r['preparedby']);
            foreach ($prepList as $prep) {
                $prep = trim($prep);
                if ($prep !== '' && !in_array($prep, $gasByBranch[$branch]['preparedby_list'])) {
                    $gasByBranch[$branch]['preparedby_list'][] = $prep;
                }
                if ($prep !== '' && !in_array($prep, $allPreparedBy)) {
                    $allPreparedBy[] = $prep;
                }
            }
        }
    }
}

// Finalize preparedby strings and convert to list for legacy compatibility
foreach ($gasByBranch as $b => $data) {
    $gasByBranch[$b]['preparedby'] = !empty($data['preparedby_list']) ? implode(', ', $data['preparedby_list']) : '';
}
$gasRows = array_values($gasByBranch);

// ---- Fetch Lubricant Sales ----
$resLube = mysqli_query($con, $sqlLube);
if ($resLube && mysqli_num_rows($resLube) > 0) {
    while ($r = mysqli_fetch_assoc($resLube)) {
        $lubeRows[] = $r;
    }
}

// ---- Fetch Expenses ----
$resExp = mysqli_query($con, $sqlExp);
if ($resExp && mysqli_num_rows($resExp) > 0) {
    while ($r = mysqli_fetch_assoc($resExp)) {
        $expRows[] = $r;
    }
}



switch($report_type) {
    case 'daily':
        $report_period_text = "For the day of {$report_label}";
        break;
    case 'monthly':
        $report_period_text = "For the month of {$report_label}";
        break;
    case 'yearly':
        $report_period_text = "For the year of {$report_label}";
        break;
    default:
        $report_period_text = "For the day of {$report_label}";
        break;
}


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

// ---- Get Prepared By Information ----
$preparedByText = !empty($allPreparedBy) ? implode(', ', $allPreparedBy) : 'System Administrator';

// ---- Set PDF Properties ----
$reportTitle = 'TMC GASOLINE STATION SALES REPORT';
$reportSubtitle = $report_period_text;
$pdfTitle = 'TMC Gasoline Station Sales Report - ' . $report_label;
$generatedAt = date('F d, Y h:i A');

$mpdf->SetTitle($pdfTitle);
$mpdf->SetAuthor('TMC');
$mpdf->SetCreator('TMC Reporting');
$mpdf->SetSubject('Sales summary for selected period');
$mpdf->SetKeywords('TMC, sales, fuel, lubricant, report, PDF');

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

        /* Modern table styling for specific sections */
        .modern-table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .modern-table th {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 9px;
            padding: 12px 8px;
        }

        .modern-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .modern-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .modern-table tbody tr:hover {
            background-color: #e3f2fd;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

        /* Branch page styling */
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
        <div style="font-size: 10px; color: #666; margin-top: 5px;">Marana 1st, City of Ilagan, Isabela</div>
       
    </div>
    <div class="divider"></div>
</div>';

// ---- Executive Summary (computed totals) ----
$overall = [
	'fuelVolume' => 0.0,
	'fuelRevenue' => 0.0,
	'lubeRevenue' => 0.0,
	'expenses' => 0.0,
	'netRevenue' => 0.0,
	'branches' => 0,
];

$branchSummaries = [];
foreach ($gasRows as $gr) {
    $branch = $gr['branch'];
    $fuelTotal = ($gr['dtotal'] ?: 0) + ($gr['ptotal'] ?: 0) + ($gr['utotal'] ?: 0);
    $fuelVolume = ($gr['dvs'] ?: 0) + ($gr['pvs'] ?: 0) + ($gr['uvs'] ?: 0);
    $branchSummaries[$branch] = [
        'fuelTotal' => $fuelTotal,
        'fuelVolume' => $fuelVolume,
        'lubeTotal' => 0.0,
        'grandTotal' => 0.0,
        'expenses' => 0.0,
        'netTotal' => 0.0,
    ];
    $overall['fuelVolume'] += $fuelVolume;
    $overall['fuelRevenue'] += $fuelTotal;
}

foreach ($lubeRows as $lr) {
    $branch = $lr['branch'];
    $lineTotal = isset($lr['line_total']) ? $lr['line_total'] : (($lr['quantity'] ?: 0) * ($lr['pprice'] ?: 0));
    if (!isset($branchSummaries[$branch])) {
        $branchSummaries[$branch] = [
            'fuelTotal' => 0.0,
            'fuelVolume' => 0.0,
            'lubeTotal' => 0.0,
            'grandTotal' => 0.0,
            'expenses' => 0.0,
            'netTotal' => 0.0,
        ];
    }
    $branchSummaries[$branch]['lubeTotal'] += $lineTotal;
    $overall['lubeRevenue'] += $lineTotal;
}

// Merge expenses into summaries (outside of lube loop to avoid duplication)
foreach ($expRows as $er) {
    $branch = $er['branch'];
    $amount = ($er['amount'] ?: 0);
    if (!isset($branchSummaries[$branch])) {
        $branchSummaries[$branch] = [
            'fuelTotal' => 0.0,
            'fuelVolume' => 0.0,
            'lubeTotal' => 0.0,
            'grandTotal' => 0.0,
            'expenses' => 0.0,
            'netTotal' => 0.0,
        ];
    }
    $branchSummaries[$branch]['expenses'] += $amount;
    $overall['expenses'] += $amount;
}

// Compute grand totals per branch and count branches
foreach ($branchSummaries as $b => $sum) {
    $branchSummaries[$b]['grandTotal'] = $sum['fuelTotal'] + $sum['lubeTotal'];
    $branchSummaries[$b]['netTotal'] = $branchSummaries[$b]['grandTotal'] - $sum['expenses'];
}
$overall['branches'] = count($branchSummaries);
$overall['netRevenue'] = ($overall['fuelRevenue'] + $overall['lubeRevenue']) - $overall['expenses'];

// Identify top branch by grandTotal
$topBranch = null;
$topBranchTotal = 0.0;
foreach ($branchSummaries as $b => $sum) {
    if ($sum['grandTotal'] > $topBranchTotal) {
        $topBranch = $b;
        $topBranchTotal = $sum['grandTotal'];
    }
}

// Sort branches by net total desc for overview ranking
uasort($branchSummaries, function($a, $b) {
    if ($a['netTotal'] == $b['netTotal']) return 0;
    return ($a['netTotal'] < $b['netTotal']) ? 1 : -1;
});

// Executive Summary Table
$html .= '<div class="section-title">Executive Summary</div>
<table class="kpi-table">
    <thead>
        <tr>
            <th style="width: 25%;">Net Revenue (₱)</th>
            <th style="width: 25%;">Total Expenses (₱)</th>
            <th style="width: 25%;">Total Fuel Sales (₱)</th>
            <th style="width: 25%;">Total Lub Sales (₱)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="kpi-green"> ' . formatCurrency($overall['netRevenue']) . '</td>
            <td class="kpi-red">' . formatCurrency($overall['expenses']) . '</td>
            <td class="kpi-black">' . formatCurrency($overall['fuelRevenue']) . '</td>
            <td class="kpi-blue">' . formatCurrency($overall['lubeRevenue']) . '</td>
        </tr>
    </tbody>
</table>';

if ($topBranch !== null) {
    $formattedTotal = formatCurrency($topBranchTotal);
    $html .= '<div style="margin-bottom: 15px;">
        <div style="font-size: 11px;">Top Performing Branch:</div>
        <div style="font-size: 11px; font-weight: bold;">' . $topBranch . ' (₱ ' . $formattedTotal . ')</div>
    </div>';
}

// Branch Overview table
$html .= '<div class="section-title">Branch Overview</div>
<table class="table">
    <thead>
        <tr>
            <th class="text-center" style="width: 32%;">Branch</th>
            <th class="text-center" style="width: 17%;">Fuel Sales</th>
            <th class="text-center" style="width: 17%;">Lubricant Sales</th>
            <th class="text-center" style="width: 17%;">Expenses</th>
            <th class="text-center" style="width: 17%;">Net Sales</th>
        </tr>
    </thead>
    <tbody>';

foreach ($branchSummaries as $b => $sum) {
    $html .= '<tr>
        <td class="text-left">' . $b . '</td>
        <td class="text-right">' . formatCurrency($sum['fuelTotal']) . '</td>
        <td class="text-right">' . formatCurrency($sum['lubeTotal']) . '</td>
        <td class="text-right">' . formatCurrency($sum['expenses']) . '</td>
        <td class="text-right">' . formatCurrency($sum['netTotal']) . '</td>
    </tr>';
}

// Totals row
$totFuel = 0.0; $totLube = 0.0; $totExp = 0.0; $totNet = 0.0;
foreach ($branchSummaries as $sum) {
    $totFuel += $sum['fuelTotal'];
    $totLube += $sum['lubeTotal'];
    $totExp  += $sum['expenses'];
    $totNet  += $sum['netTotal'];
}

$html .= '<tr class="total-row">
    <td class="text-right">TOTAL (₱)</td>
    <td class="text-right">' . formatCurrency($totFuel) . '</td>
    <td class="text-right">' . formatCurrency($totLube) . '</td>
    <td class="text-right">' . formatCurrency($totExp) . '</td>
    <td class="text-right">' . formatCurrency($totNet) . '</td>
</tr>
</tbody>
</table>';

// ---- Report Guide ----
$html .= '<div class="section-title">How to Read this Report</div>
<div style="font-size: 11px; margin-bottom: 20px;">
    - Amounts are in Philippine Peso (₱).<br>
    - Fuel volumes are reported in liters (L).<br>
    - Overview shows branches ranked by total revenue.<br>
    - Detailed sections include per-branch fuel and lubricant breakdowns.
</div>';

// ---- Branch-wise Sales (one page per branch) ----
if (!empty($branchSummaries)) {
    // Index gas rows by branch for consistent order with overview ranking
    $gasByBranch = [];
    foreach ($gasRows as $r) {
        $gasByBranch[$r['branch']] = $r;
    }

    // Print branch details sorted by ranking table order
    $branchCount = 0;
    foreach ($branchSummaries as $branchName => $_sum) {
        // Add page break before each branch
        $html .= '<pagebreak>';
        
        // Add header to each branch page
        $branchPreparedBy = $preparedByText; // Will be updated below when we get the actual branch data
        $html .= '
        <div class="header">
            <img src="tmclogo.png" class="logo" style="width: 18mm; height: 18mm;">
            <div style="display: inline-block;">
                <div class="title">' . $reportTitle . '</div>
                <div class="subtitle">' . $reportSubtitle . '</div>
                <div style="font-size: 10px; color: #666; margin-top: 5px;">Marana 1st, City of Ilagan, Isabela</div>
                <div style="font-size: 9px; color: #888; margin-top: 3px;">Prepared by: ' . htmlspecialchars($branchPreparedBy) . '</div>
            </div>
            <div class="divider"></div>
        </div>';
        
        if (!isset($gasByBranch[$branchName])) {
            // If branch has only lube sales but no gas row, create empty gas row structure
            $gasByBranch[$branchName] = [
                'branch' => $branchName,
                'dvs' => 0, 'dvsp' => 0, 'dtotal' => 0,
                'pvs' => 0, 'pvsp' => 0, 'ptotal' => 0,
                'uvs' => 0, 'uvsp' => 0, 'utotal' => 0,
            ];
        }
        $r = $gasByBranch[$branchName];
        $branch = $r['branch'];

        // Update branch prepared by information
        if (!empty($r['preparedby'])) {
            $branchPreparedBy = $r['preparedby'];
        }

        // Branch title
        $html .= '<div class="branch-header">Branch: ' . $branch . '</div>';
        
        // Prepared by information for this specific branch
        $html .= '<div style="font-size: 10px; color: #666; margin-bottom: 10px; text-align: right;">Prepared by: ' . htmlspecialchars($branchPreparedBy) . '</div>';

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
                    <td class="kpi-black">' . formatCurrency($_sum['fuelTotal']) . '</td>
                    <td class="kpi-black">' . formatCurrency($_sum['lubeTotal']) . '</td>
                    <td class="kpi-red">' . formatCurrency($_sum['expenses']) . '</td>
                    <td class="kpi-green">' . formatCurrency($_sum['netTotal']) . '</td>
                </tr>
            </tbody>
        </table>';

        // ====================
        // Fuel Sales (Table)
        // ====================
        $html .= '<div class="section-title">Fuel Sales</div>';
        
        if ($report_type === 'perbranch') {
            $html .= '<table class="table">
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
                        <td class="text-center">' . formatCurrency($r['dvs']) . '</td>
                        <td class="text-center">' . formatCurrency($r['dvsp']) . '</td>
                        <td class="text-center">' . formatCurrency($r['dtotal']) . '</td>
                    </tr>
                    <tr>
                        <td class="text-center">Premium</td>
                        <td class="text-center">' . formatCurrency($r['pvs']) . '</td>
                        <td class="text-center">' . formatCurrency($r['pvsp']) . '</td>
                        <td class="text-center">' . formatCurrency($r['ptotal']) . '</td>
                    </tr>
                    <tr>
                        <td class="text-center">Unleaded</td>
                        <td class="text-center">' . formatCurrency($r['uvs']) . '</td>
                        <td class="text-center">' . formatCurrency($r['uvsp']) . '</td>
                        <td class="text-center">' . formatCurrency($r['utotal']) . '</td>
                    </tr>';
        } else {
            $html .= '<table class="table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 37%;">Fuel Type</th>
                        <th class="text-center" style="width: 31%;">Volume Sold(L)</th>
                        <th class="text-center" style="width: 32%;">Total (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">Diesel</td>
                        <td class="text-center">' . formatCurrency($r['dvs']) . '</td>
                        <td class="text-center">' . formatCurrency($r['dtotal']) . '</td>
                    </tr>
                    <tr>
                        <td class="text-center">Premium</td>
                        <td class="text-center">' . formatCurrency($r['pvs']) . '</td>
                        <td class="text-center">' . formatCurrency($r['ptotal']) . '</td>
                    </tr>
                    <tr>
                        <td class="text-center">Unleaded</td>
                        <td class="text-center">' . formatCurrency($r['uvs']) . '</td>
                        <td class="text-center">' . formatCurrency($r['utotal']) . '</td>
                    </tr>';
        }

        // Fuel total row
        $fuelTotal = ($r['dtotal'] ?: 0) + ($r['ptotal'] ?: 0) + ($r['utotal'] ?: 0);
        if ($report_type === 'perbranch') {
            $html .= '<tr class="total-row">
                <td colspan="3" class="text-right">Fuel Total</td>
                <td class="text-center">₱ ' . formatCurrency($fuelTotal) . '</td>
            </tr>';
        } else {
            $html .= '<tr class="total-row">
                <td colspan="2" class="text-right">Fuel Total</td>
                <td class="text-center">₱ ' . formatCurrency($fuelTotal) . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
        </table>';

        // ====================
        // Lubricant Sales (Table)
        // ====================
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
            if ($l['branch'] === $branch) {
                $lineTotal = isset($l['line_total']) ? $l['line_total'] : (($l['quantity'] ?: 0) * ($l['pprice'] ?: 0));
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

        // ====================
        // Branch Expenses and Net
        // ====================
        $branchExpense = 0.0;
        foreach ($expRows as $er) {
            if ($er['branch'] === $branch) {
                $branchExpense += ($er['amount'] ?: 0);
            }
        }
        $branchTotal = $fuelTotal + $lubeTotal;
        $branchNet = $branchTotal - $branchExpense;

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
        foreach ($expRows as $er) {
            if ($er['branch'] === $branch) {
                $label = $er['expense_type'];
                if (!empty($er['other_description'])) {
                    $label .= ' - '.$er['other_description'];
                }
                $html .= '<tr>
                    <td class="text-left">' . $label . '</td>
                    <td class="text-right">' . formatCurrency($er['amount']) . '</td>
                </tr>';
                $hasExp = true;
            }
        }
        
        if (!$hasExp) {
            $html .= '<tr>
                <td colspan="2" class="text-center" style="padding: 20px; color: #6c757d; font-style: italic;">(No expenses recorded for this branch)</td>
            </tr>';
        } else {
            $html .= '<tr class="total-row">
                <td class="text-right">Total Expenses</td>
                <td class="text-right">₱ ' . formatCurrency($branchExpense) . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
        </table>';

        // Branch Financial Summary - Professional Format
        $html .= '<div class="section-title">Branch Financial Summary</div>
        <div class="financial-summary">
            <table class="summary-table">
                <tr>
                    <td>Total Sales (Fuel + Lubricants):</td>
                    <td style="text-align:right;">₱ ' . formatCurrency($branchTotal) . '</td>
                </tr>
                <tr>
                    <td>Less: Operating Expenses:</td>
                    <td style="color: #b40000; text-align:right;">₱ ' . formatCurrency($branchExpense) . '</td>
                </tr>
                <tr class="summary-total-row">
                    <td>Net Profit:</td>
                    <td style="text-align:right;">₱ ' . formatCurrency($branchNet) . '</td>
                </tr>
            </table>
        </div>';
        
        $branchCount++;
    }
} else {
    $html .= '<div style="text-align: center; font-style: italic; font-size: 12px; padding: 20px; color: #6c757d;">No branch data found for this period.</div>';
}

// Add Prepared By section at the end
$html .= '<div style="margin-top: 40px; page-break-inside: avoid;">
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

$filename = 'Report_' . $report_label . '.pdf';
if ($action === 'download') {
    $mpdf->Output($filename, 'D');
} else {
    $mpdf->Output($filename, 'I');
}
