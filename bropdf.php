<?php
// Pro PDF layout for Bro's Inasal Daily Report + Stock Movement
require_once __DIR__ . '/vendor/autoload.php';

// Start output buffering to prevent any output before PDF generation
ob_start();

// Helper function to format currency
function formatCurrency($amount) {
    return number_format(floatval($amount ?: 0), 2);
}

// Helper function to safely get array value
function safeGet($array, $key, $default = 0) {
    return isset($array[$key]) ? $array[$key] : $default;
}

// ---- DB connect ----
$conn = mysqli_connect('localhost','root','root','tmc_admin_db');
if (!$conn) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Database Connection Failed: " . mysqli_connect_error();
    exit;
}
mysqli_set_charset($conn, 'utf8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $row_id = isset($_POST['row_id']) ? $_POST['row_id'] : null; // daily mode
    $period = isset($_POST['period']) ? $_POST['period'] : null; // aggregated mode
    $report_type = isset($_POST['report_type']) ? $_POST['report_type'] : '';
    $action = isset($_POST['action']) ? $_POST['action'] : 'view';

    // Mode detection: daily by row_id, aggregated by period+report_type
    $isAggregated = in_array($report_type, ['monthly','yearly']);
    
    if (!$row_id && !$isAggregated) {
        echo 'Invalid request';
        exit;
    }

    if ($isAggregated) {
        // Build date predicates from period
        $where = '';
        if ($report_type === 'monthly') {
            $dt = DateTime::createFromFormat('F Y', $period);
            if ($dt) {
                $year = intval($dt->format('Y'));
                $month = intval($dt->format('m'));
                $where = "YEAR(report_date) = $year AND MONTH(report_date) = $month";
            } else {
                echo 'Invalid monthly period';
                exit;
            }
        } elseif ($report_type === 'yearly') {
            $year = intval($period);
            $where = "YEAR(report_date) = $year";
        }

        // Fetch all daily rows in that aggregated period
        $aggSql = "SELECT * FROM bros_dailyreport_tbl WHERE $where ORDER BY report_date ASC";
        $aggRes = mysqli_query($conn, $aggSql);
        $days = [];
        if ($aggRes) {
            while ($d = mysqli_fetch_assoc($aggRes)) { $days[] = $d; }
        }
        if (count($days) === 0) {
            echo 'No daily records found for the selected period';
            exit;
        }
        // Prepare summary totals and analytics
        $sum_gross = 0.0; $sum_exp = 0.0; $sum_net = 0.0;
        $min_day = null; $max_day = null; $min_net = null; $max_net = null;
        $min_gross = null; $max_gross = null; $min_gross_day = null; $max_gross_day = null;
        $min_date = null; $max_date = null;
        foreach ($days as $d) {
            $g = floatval($d['gross_sales'] ?: 0);
            $e = floatval($d['expenses'] ?: 0);
            $n = floatval($d['net_sales'] ?: 0);
            $sum_gross += $g; $sum_exp += $e; $sum_net += $n;
            $curDate = $d['report_date'];
            if ($min_date === null || $curDate < $min_date) $min_date = $curDate;
            if ($max_date === null || $curDate > $max_date) $max_date = $curDate;
            if ($min_net === null || $n < $min_net) { $min_net = $n; $min_day = $d; }
            if ($max_net === null || $n > $max_net) { $max_net = $n; $max_day = $d; }
            if ($min_gross === null || $g < $min_gross) { $min_gross = $g; $min_gross_day = $d; }
            if ($max_gross === null || $g > $max_gross) { $max_gross = $g; $max_gross_day = $d; }
        }
        $num_days = count($days);
        // Build Other Sales by date to align Gross with "Gross Sales (Should be)" (POS + Other)
        $otherByDate = [];
        if ($min_date && $max_date) {
            $osMapSql = "SELECT log_date, SUM(pprice * quantity) AS total FROM bros_other_sale " .
                        "WHERE log_date BETWEEN '" . mysqli_real_escape_string($conn, $min_date) . "' AND '" . mysqli_real_escape_string($conn, $max_date) . "' " .
                        "GROUP BY log_date";
            $osMapRes = mysqli_query($conn, $osMapSql);
            if ($osMapRes) {
                while ($r = mysqli_fetch_assoc($osMapRes)) {
                    $otherByDate[$r['log_date']] = floatval($r['total'] ?: 0);
                }
            }
        }
        // Recompute gross totals and groupings based on POS + Other Sales
        $sum_gross = 0.0;
        $avg_exp   = $num_days ? ($sum_exp   / $num_days) : 0;
        $avg_net   = $num_days ? ($sum_net   / $num_days) : 0;
        $by_month = [];
        foreach ($days as $d) {
            $dt = $d['report_date'];
            $m = (int)date('n', strtotime($dt));
            if (!isset($by_month[$m])) { $by_month[$m] = ['gross'=>0.0,'exp'=>0.0,'net'=>0.0,'days'=>0]; }
            $grossShouldDay = floatval($d['total_sales_pos'] ?: 0) + (isset($otherByDate[$dt]) ? $otherByDate[$dt] : 0.0);
            $sum_gross += $grossShouldDay;
            $by_month[$m]['gross'] += $grossShouldDay;
            $by_month[$m]['exp']   += ($d['expenses'] ?: 0);
            $by_month[$m]['net']   += ($d['net_sales'] ?: 0);
            $by_month[$m]['days']  += 1;
        }
        $avg_gross = $num_days ? ($sum_gross / $num_days) : 0;
        $net_margin_pct = ($sum_gross > 0) ? ($sum_net / $sum_gross * 100.0) : 0.0;
        // PDF header text
        $title = "BRO'S INASAL SALES REPORT";
        if ($report_type === 'monthly') $subtitle = 'For The Month Of - '.$period;
        else $subtitle = 'Yearly Summary - '.$period;

        // ==== Prepare Income Statement data for monthly/yearly ====
        $includeIncomeStatement = in_array($report_type, ['monthly','yearly']);
        // For Income Statement, Total Gross Sales should be based on "Gross Sales (Should be)"
        // which equals POS sales + Other Sales across the period
        $sum_pos_sales = 0.0;
        foreach ($days as $d) { $sum_pos_sales += floatval($d['total_sales_pos'] ?: 0); }
        $sum_other_sales = 0.0;
        if ($min_date && $max_date) {
            $osAggSql = "SELECT SUM(pprice * quantity) AS total FROM bros_other_sale WHERE log_date BETWEEN '".
                        mysqli_real_escape_string($conn, $min_date)."' AND '".mysqli_real_escape_string($conn, $max_date)."'";
            $osAggRes = mysqli_query($conn, $osAggSql);
            if ($osAggRes) {
                $row = mysqli_fetch_assoc($osAggRes);
                $sum_other_sales = floatval($row['total'] ?: 0);
            }
        }
        $isSalesSum = $sum_pos_sales + $sum_other_sales;
        $cogs_begin_val = 0.0; $cogs_purchases = 0.0; $cogs_freight = 0.0; $cogs_ending_val = 0.0;
        // Use EXACT categories as per UI select
        $expenseBuckets = [
            'Utility Expense' => 0.0,
            'Salary Expense' => 0.0,
            'Supplies Expense' => 0.0,
            'Royalty Fee' => 0.0,
            'Internet Expense' => 0.0,
            'Direct Operating Expense' => 0.0,
            'Miscellaneous Expense' => 0.0,
        ];
        $sum_over_short = 0.0;

        if ($includeIncomeStatement) {
            // Pull stock movements for the period to compute COGS
            // Use movement_date between the aggregated period min/max dates
            $fromDate = $min_date; $toDate = $max_date;
            $smWhere = '';
            if ($fromDate && $toDate) {
                $smWhere = "sm.movement_date BETWEEN '" . mysqli_real_escape_string($conn, $fromDate) . "' AND '" . mysqli_real_escape_string($conn, $toDate) . "'";
            } else {
                // Fallback (should not happen): prevent returning everything
                $smWhere = "1=0";
            }
            $smSql = "SELECT sm.product_id, sm.movement_date, sm.qty_in, sm.unit_cost, sm.shipping_cost, sm.beginning_inv, sm.ending_inv, sm.beg_inv_value, sm.end_inv_value
                      FROM bro_stock_movements sm
                      WHERE $smWhere
                      ORDER BY sm.product_id ASC, sm.movement_date ASC, sm.id ASC";
            $smRes = mysqli_query($conn, $smSql);
            $firstRowByProduct = [];
            $lastRowByProduct = [];
            if ($smRes) {
                while ($r = mysqli_fetch_assoc($smRes)) {
                    $pid = $r['product_id'];
                    if (!isset($firstRowByProduct[$pid])) { $firstRowByProduct[$pid] = $r; }
                    $lastRowByProduct[$pid] = $r; // keep overwriting to end up with last
                    $cogs_purchases += (floatval($r['qty_in'] ?: 0) * floatval($r['unit_cost'] ?: 0));
                    // Count freight-in only when there are purchases on the row
                    if (intval($r['qty_in'] ?: 0) > 0) {
                        $cogs_freight += floatval($r['shipping_cost'] ?: 0);
                    }
                }
            }
            foreach ($firstRowByProduct as $pid => $r) {
                $cogs_begin_val += floatval($r['beg_inv_value'] ?: 0);
            }
            foreach ($lastRowByProduct as $pid => $r) {
                $cogs_ending_val += floatval($r['end_inv_value'] ?: 0);
            }

            // Expenses within the date range
            // Determine date range from collected days
            $fromDate = $min_date; $toDate = $max_date;
            if ($fromDate && $toDate) {
                $expSql = "SELECT expense_type, amount FROM bros_expense WHERE expense_date BETWEEN '".mysqli_real_escape_string($conn,$fromDate)."' AND '".mysqli_real_escape_string($conn,$toDate)."'";
                $expRes = mysqli_query($conn, $expSql);
                if ($expRes) {
                    while ($e = mysqli_fetch_assoc($expRes)) {
                        $type = trim($e['expense_type']);
                        $amt  = floatval($e['amount'] ?: 0);
                        if (array_key_exists($type, $expenseBuckets)) {
                            $expenseBuckets[$type] += $amt;
                        } else {
                            $expenseBuckets['Miscellaneous Expense'] += $amt;
                        }
                    }
                }
                // Cash short/over (use daily table field over_short)
                $osSql = "SELECT SUM(over_short) AS s FROM bros_dailyreport_tbl WHERE report_date BETWEEN '".mysqli_real_escape_string($conn,$fromDate)."' AND '".mysqli_real_escape_string($conn,$toDate)."'";
                $osRes = mysqli_query($conn, $osSql);
                if ($osRes) { $row = mysqli_fetch_assoc($osRes); $sum_over_short = floatval($row['s'] ?: 0); }
            }
        }
    } else {
        // Daily mode
        $dailyRes = mysqli_query($conn, "SELECT * FROM bros_dailyreport_tbl WHERE id='".mysqli_real_escape_string($conn,$row_id)."'");
        $daily = $dailyRes ? mysqli_fetch_assoc($dailyRes) : null;
        if (!$daily) {
            echo 'No record found.';
            exit;
        }
        $report_date = $daily['report_date'];
    }

    // Daily-only ancillary data
    $expenses = [];
    $cash = null;
    $stocks = [];
    $otherSalesRows = [];
    $other_sales_val = 0.0;
    $recalc_expenses = 0.0;
    
    if (!$isAggregated) {
        // ---- Fetch Expenses ----
        $expRes = mysqli_query($conn, "SELECT expense_type, amount FROM bros_expense WHERE report_id='".mysqli_real_escape_string($conn,$row_id)."'");
        if ($expRes) {
            while ($r = mysqli_fetch_assoc($expRes)) { 
                $expenses[] = $r; 
                $recalc_expenses += floatval($r['amount']); 
            }
        }

        // ---- Fetch Other Sales (by report date) ----
        if (!empty($report_date)) {
            $osSql = "SELECT pname, pprice, quantity FROM bros_other_sale WHERE log_date='".mysqli_real_escape_string($conn,$report_date)."' ORDER BY pname";
            $osRes = mysqli_query($conn, $osSql);
            if ($osRes) {
                while ($os = mysqli_fetch_assoc($osRes)) { 
                    $otherSalesRows[] = $os; 
                    $other_sales_val += floatval($os['pprice'] ?: 0) * intval($os['quantity'] ?: 0);
                }
            }
        }

        // ---- Fetch Cash Breakdown ----
        $cashRes = mysqli_query($conn, "SELECT * FROM bros_cash_breakdown WHERE report_id='".mysqli_real_escape_string($conn,$row_id)."' LIMIT 1");
        if ($cashRes) { $cash = mysqli_fetch_assoc($cashRes); }

        // ---- Fetch Stock Movements for the report date ----
        $stockSql = "SELECT i.product_name, i.available_stock, sm.product_id, sm.qty_in, sm.qty_out, sm.unit_cost, sm.shipping_cost, sm.beginning_inv, sm.ending_inv, sm.movement_date
                     FROM bro_stock_movements sm
                     LEFT JOIN bros_inventory i ON sm.product_id = i.id
                     WHERE sm.movement_date = '".mysqli_real_escape_string($conn,$report_date)."'
                     ORDER BY i.product_name";
        $stockRes = mysqli_query($conn, $stockSql);
        if ($stockRes) {
            while ($s = mysqli_fetch_assoc($stockRes)) { $stocks[] = $s; }
        }
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

    // ---- Set PDF Properties ----
    if ($isAggregated) {
        $reportTitle = $title;
        $reportSubtitle = $subtitle;
        $pdfTitle = "Bro's Inasal Aggregated Report - " . $period;
    } else {
        $reportTitle = "BRO'S INASAL DAILY REPORT";
        $reportSubtitle = 'For '.date('F d, Y', strtotime($report_date));
        $pdfTitle = "Bro's Inasal Daily Report - " . $report_date;
    }
    $generatedAt = date('F d, Y h:i A');
    
    $mpdf->SetTitle($pdfTitle);
    $mpdf->SetAuthor('BRO\'S INASAL');
    $mpdf->SetCreator('Admin Dashboard');
    $mpdf->SetSubject('Daily sales summary');
    $mpdf->SetKeywords("bros inasal, daily report, sales");

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
                font-size: 15px;
                
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
                font-size: 20px; 
                font-weight: bold; 
                color: #672222; 
                margin-left: -80;
                padding-top: 10px;
            }

            .subtitle { 
                margin-top: -30;
                font-size: 15px; 
                
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
                font-size: 11px;
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
                font-size: 12px; 
                font-weight: bold; 
                margin: 15px 0 8px 0; 
                text-transform: uppercase;
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
                padding: 12px 8px; 
                font-size: 13px; 
                vertical-align: middle;
                text-align:center;
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

            .table tbody tr {
                transition: background-color 0.2s ease;
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

            /* Enhanced section titles */
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

            /* ===== HIGHLIGHT BOX ===== */
            .highlight-box { 
                
                color: #012f10ff; background: #e6ffe6; border-radius: 4px;
                border-bottom: 1px solid #e8e8e8;
                padding: 2px 4px; 
                display: inline-block;
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

            #totalsales td {
                padding: 5px;
                font-size: 13px;
                
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

    // Executive Summary Cards
    $renderKpi = false; $kpiValues = [];
    if ($isAggregated) {
        $kpiValues = [
            ['label' => 'Total Gross (₱)', 'value' => formatCurrency($sum_gross), 'class' => 'kpi-green'],
            ['label' => 'Total Expenses (₱)', 'value' => formatCurrency($sum_exp), 'class' => 'kpi-red'],
            ['label' => 'Total Net (₱)', 'value' => formatCurrency($sum_net), 'class' => 'kpi-green'],
            ['label' => 'Days Included', 'value' => $num_days, 'class' => 'kpi-black'],
        ];
        $renderKpi = true;
    }
    
    if ($renderKpi) {
        $html .= '<table class="kpi-table">
            <thead>
                <tr>';
        foreach ($kpiValues as $kpi) {
            $html .= '<th style="width: 25%;">' . $kpi['label'] . '</th>';
        }
        $html .= '</tr>
            </thead>
            <tbody>
                <tr>';
        foreach ($kpiValues as $kpi) {
            $html .= '<td class="' . $kpi['class'] . '">' . $kpi['value'] . '</td>';
        }
        $html .= '</tr>
            </tbody>
        </table>';
    }

    // Daily mode TOTAL SALES section
    if (!$isAggregated) {
        // Compute values for this section
        $cashOnCounter = floatval($daily['cash_on_counter'] ?: 0);
        $cashIn        = floatval($daily['cash_in'] ?: 0);
        $gcashVal      = floatval($daily['gcash_sales'] ?: 0);
        $creditVal     = floatval($daily['credit_sales'] ?: 0);
        $otherSales    = $other_sales_val;
        $totalCashSales = max(0.0, $cashOnCounter - $cashIn);
        // Display Gross (POS + Other Sales) per new rule
        $grossShould    = floatval($daily['total_sales_pos'] ?: 0) + $otherSales;
        $expenseSum     = ($recalc_expenses > 0) ? $recalc_expenses : floatval($daily['expenses'] ?: 0);
        $netShould      = $grossShould - $expenseSum;
        $totalSalesCounter = $totalCashSales + $gcashVal + $creditVal;
        $overShortDisplay  = $totalSalesCounter - $netShould;

        $html .= '
            
            <div class="section-title">TOTAL SALES</div>
                <div style="border: 3px solid #672222; padding: 8px; margin-bottom: 15px;">

                    

                    <table id="totalsales" width="100%" style="border-collapse: collapse; border: none; font-size: 11px;">
                        <tr>
                            <!-- LEFT COLUMN -->
                            <td style="width: 50%; vertical-align: top; border: none; padding-right: 15px;">
                                <table width="100%" style="border: none; border-collapse: collapse;">
                                   

                                    <tr>
                                        <td style="font-weight:bold;" >Date: ' . date('F d, Y', strtotime($report_date)) . '</td>
                                        
                                    </tr>
                                
                                    <tr>
                                        <td>Cash on Counter</td>
                                        <td style="text-align: right;">' . formatCurrency($cashOnCounter) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Less: Cash In</td>
                                        <td style="text-align: right;">' . formatCurrency($cashIn) . '</td>
                                    </tr>
                                    <tr style="font-weight: bold;"><td>Total Cash Sales</td><td style="text-align: right;">' . formatCurrency($totalCashSales) . '</td></tr>
                                    <tr><td>Add: Gcash Sales</td><td style="text-align: right;">' . formatCurrency($gcashVal) . '</td></tr>
                                    <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;Credit Sales</td><td style="text-align: right;">' . formatCurrency($creditVal) . '</td></tr>
                                    <tr style="font-weight: bold;"><td>TOTAL SALES (counter)</td><td style="text-align: right;">' . formatCurrency($totalSalesCounter) . '</td></tr>
                                    <tr><td>Less: Net Sales (Should be)</td><td style="text-align: right;">' . formatCurrency($netShould) . '</td></tr>
                                    <tr style="font-weight: bold;">
                                        <td>Over (Short)</td>
                                        <td class="highlight-box" style="text-align: right;">
                                            <span >' . formatCurrency($overShortDisplay) . '</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>

                            <!-- RIGHT COLUMN -->
                            <td style="width: 50%; vertical-align: top; border: none; padding-top:30px; padding-left: 15px;">
                                <table  width="100%" style="border: none; border-collapse: collapse;">
                                    
                                    <tr><td>Total Sales (POS)</td><td style="text-align: right;">' . formatCurrency($daily['total_sales_pos']) . '</td></tr>
                                    <tr><td>Other Sales</td><td style="text-align: right;">' . formatCurrency($otherSales) . '</td></tr>
                                    <tr><td style="font-weight:bold;">Gross Sales (Should be) </td><td style="font-weight:bold; text-align: right;">' . formatCurrency($grossShould) . '</td></tr>
                                    <tr><td>Less: Expense</td><td style="text-align: right;">' . formatCurrency($expenseSum) . '</td></tr>
                                    <tr style="font-weight: bold;"><td>NET SALES</td><td style="text-align: right;">' . formatCurrency($netShould) . '</td></tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                </div>';

    }

    if ($isAggregated) {
        // Professional Aggregated Summary
        if ($report_type === 'monthly') {
            $html .= '<div class="section-title">Monthly Overview</div>';
        } else {
            $html .= '<div class="section-title">Yearly Overview</div>';
        }
        
        $html .= '<div style="margin-bottom: 15px;">Best Net Day: ' . ($max_day ? date('M d, Y', strtotime($max_day['report_date'])) . ' (₱' . number_format($max_net,2) . ')' : '-') . '</div>';

        // Daily Summary table (Gross reflects POS + Other Sales)
        $html .= '<div class="section-title">Daily Summary</div>
        <table class="table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 21%;">Date</th>
                    <th class="text-center" style="width: 26%;">Gross (Should be) (₱)</th>
                    <th class="text-center" style="width: 26%;">Expenses (₱)</th>
                    <th class="text-center" style="width: 27%;">Net (₱)</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($days as $d) {
            $grossShouldDay = floatval($d['total_sales_pos'] ?: 0) + (isset($otherByDate[$d['report_date']]) ? $otherByDate[$d['report_date']] : 0.0);
            $html .= '<tr>
                <td class="text-center">' . date('M d, Y', strtotime($d['report_date'])) . '</td>
                <td class="text-right">' . number_format($grossShouldDay,2) . '</td>
                <td class="text-right">' . number_format(($d['expenses'] ?: 0),2) . '</td>
                <td class="text-right">' . number_format(($d['net_sales'] ?: 0),2) . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
        </table>';

        // Additional breakdowns
        if ($report_type === 'yearly') {
            $html .= '<div class="section-title">By Month</div>
            <table class="table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 21%;">Month</th>
                        <th class="text-center" style="width: 26%;">Gross (Should be) (₱)</th>
                        <th class="text-center" style="width: 26%;">Expenses (₱)</th>
                        <th class="text-center" style="width: 27%;">Net (₱)</th>
                    </tr>
                </thead>
                <tbody>';
            
            ksort($by_month);
            foreach ($by_month as $m => $tot) {
                $monthName = date('M', mktime(0,0,0,$m,1));
                $html .= '<tr>
                    <td class="text-center">' . $monthName . '</td>
                    <td class="text-right">' . number_format($tot['gross'],2) . '</td>
                    <td class="text-right">' . number_format($tot['exp'],2) . '</td>
                    <td class="text-right">' . number_format($tot['net'],2) . '</td>
                </tr>';
            }
            
            $html .= '</tbody>
            </table>';
        }

        // ===== Income Statement (Monthly/Yearly) =====
        if ($includeIncomeStatement) {
            $total_cogs = ($cogs_begin_val + $cogs_purchases + $cogs_freight) - $cogs_ending_val;
            $gross_profit = $isSalesSum - $total_cogs;
            $total_expenses = 0.0; foreach ($expenseBuckets as $v) { $total_expenses += $v; }
            $operating_income = $gross_profit - $total_expenses;
            $net_income = $operating_income + $sum_over_short; // other income: cash short/over

            // Styled box and centered headers like the screenshot
            $periodText = ($report_type==='monthly') ? ('For the Month of  ' . date('F d', strtotime($max_date))) : ('For the Year of ' . $period);
            $html .= '<div style="margin-top:10px; padding:12px; border:2px solid #672222; border-radius:4px; width:100%; margin-left:auto; margin-right:auto;">
                <header style="border-radius:5px; color:#ffffff; padding:10px; background: linear-gradient(90deg, #672222, #8c2f2f);">
                    <div style="text-align:center; font-weight:bold;">INCOME STATEMENT</div>
                    <div style="text-align:center; font-weight:bold;">BRO\'S INASAL</div>
                    <div style="text-align:center; margin-bottom:6px;">' . htmlspecialchars($periodText) . '</div>
                </header>
                <table id="IS_table" style="margin-top:40px; width:100%; border-collapse:collapse; font-size:13px;">
                    <tbody>
                        <tr>
                            <td style="width:60%; font-weight:bold;">Total Gross Sales</td>
                            <td style="width:20%; text-align:right; font-weight:bold;"></td>
                            <td style="width:20%; text-align:right; font-weight:bold;">₱ ' . number_format($isSalesSum,2) . '</td>
                            
                        </tr>
                        <tr>
                            <td style="padding-top:8px; font-weight:bold;">Less: Cost of Good Sold (COGS)</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="padding-left:24px;">Beginning Inventory</td>
                            <td style="text-align:right;">' . number_format($cogs_begin_val,2) . '</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="padding-left:24px;">Add Purchases</td><td style="text-align:right;">' . number_format($cogs_purchases,2) . '</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="padding-left:48px;">Freight-in</td><td style="text-align:right;">' . number_format($cogs_freight,2) . '</td>
                        </tr>
                        <tr>
                            <td style="padding-left:24px;">Less: Ending Inventory</td>
                            <td style="text-align:right;">' . number_format($cogs_ending_val,2) . '</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style=" font-weight:bold;">Total COGS</td>
                            
                            <td style="border-top:1px solid #000; text-align:right; font-weight:bold;"></td>
                            <td style="border-top:1px solid #000; text-align:right; font-weight:bold;">' . number_format($total_cogs,2) . '</td>
                        </tr>
                        <tr>
                            <td style=" font-weight:bold;">Gross Profit</td>
                            <td style="border-top:1px solid #000; text-align:right; font-weight:bold;"></td>
                            <td style="border-top:1px solid #000; text-align:right; font-weight:bold;">' . number_format($gross_profit,2) . '</td>
                        </tr>
                        <tr><td style="padding-top:8px; font-weight:bold;">Less: Expenses</td><td></td></tr>';

            // Render each expense bucket if non-zero, using exact order from your UI
            $order = ['Salary Expense','Utility Expense','Supplies Expense','Royalty Fee','Internet Expense','Direct Operating Expense','Miscellaneous Expense'];
            foreach ($order as $k) {
                $val = isset($expenseBuckets[$k]) ? $expenseBuckets[$k] : 0.0;
                if ($val > 0) {
                    $html .= '<tr><td class="text-left" style="padding-left:25px;">' . $k . '</td><td class="text-right">' . number_format($val,2) . '</td></tr>';
                }
            }
            $html .= '<tr>
                            <td style=" font-weight:bold;">Total Expenses</td>
                            <td style="border-top:1px solid #000; text-align:right; font-weight:bold;"></td>
                            <td style="border-top:1px solid #000; text-align:right; font-weight:bold;">' . number_format($total_expenses,2) . '</td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold;">Operating Income</td>
                            <td style="border-top:1px solid #000; text-align:right; font-weight:bold;"></td>
                            <td style="border-top:1px solid #000; text-align:right; font-weight:bold;">₱ ' . number_format($operating_income,2) . '</td>
                        </tr>
                        <tr>
                            <td style="padding-left:24px;">Other Income - Cash Short/Over</td>
                            <td></td>
                            <td style="text-align:right;">₱ ' . number_format($sum_over_short,2) . '</td>
                        </tr>
                        <tr>
                            <td style=" font-weight:bold;">Net Income</td>
                            <td style="border-top:1px solid #000; border-bottom: 1px solid #000; text-align:right; font-weight:bold;"></td>
                            <td style="border-top:1px solid #000; border-bottom: 1px solid #000;  text-align:right; font-weight:bold;">₱ ' . number_format($net_income,2) . '</td>
                        </tr>
                        <tr >
                            <td style="margin-top:5px;"></td>
                        </tr>
                        <tr style="padding-top:5px;">
                            <td style=" font-weight:bold;"></td>
                            <td style="border-top:1px solid #000; text-align:right; font-weight:bold;"></td>
                            <td style="border-top:1px solid #000; text-align:right; font-weight:bold;"></td>
                        </tr>
                    </tbody>
                </table>
            </div>';
        }
    } else {
         // ---- Cash Breakdown ----
        $html .= '<div class="section-title">Cash Breakdown</div>';
        if ($cash) {
            $html .= '<table class="table modern-table">
                <thead>
                    <tr>
                        <th style="width: 32%;">Denomination (₱)</th>
                        <th style="width: 32%;">Quantity</th>
                        <th style="width: 36%;">Total (₱)</th>
                    </tr>
                </thead>
                <tbody>';
            
            $denoms = [1000,500,200,100,50,20,10,5,1];
            foreach ($denoms as $d) {
                $qty = isset($cash['cash_'.$d]) ? (int)$cash['cash_'.$d] : 0;
                if ($qty > 0) {
                    $html .= '<tr>
                        <td class="text-right">' . number_format($d,2) . '</td>
                        <td class="text-center">' . $qty . '</td>
                        <td class="text-right">' . number_format($d*$qty,2) . '</td>
                    </tr>';
                }
            }
            
            $html .= '<tr class="total-row">
                <td colspan="2" class="text-right">Total Cash Counted</td>
                <td class="text-right">₱' . number_format(($cash['total_amount'] ?: 0),2) . '</td>
            </tr>
            </tbody>
            </table>';
        } else {
            $html .= '<table class="table modern-table">
                <tr><td class="text-center" style="padding: 20px; color: #6c757d; font-style: italic;">(No cash breakdown recorded)</td></tr>
            </table>';
        }


        // ---- Other Sales (Left) and Expenses (Right) side-by-side ----
        $html .= '
        <table id="othersale" style="width:100%; border-collapse: collapse;">
            <tr>
                <!-- LEFT COLUMN: Other Sales -->
                <td style="width:50%; vertical-align: top; padding-right:5px;">
                    <div class="section-title">Other Sales</div>
                   
                    <table class="table modern-table">
                        <thead>
                            <tr>
                                <th style="width:40%;">Product</th>
                                <th style="width:20%;">Price</th>
                                <th style="width:15%;">Qty</th>
                                <th style="width:25%;">Total</th>
                            </tr>
                        </thead>
                        <tbody>';

        $totalOtherSales = 0.0; 
        $hasOther = false;
        foreach ($otherSalesRows as $os) {
            $pname = $os['pname'];
            $price = floatval($os['pprice'] ?: 0);
            $qty   = intval($os['quantity'] ?: 0);
            $total = $price * $qty;
            $html .= '<tr>
                <td class="text-left">' . strtoupper($pname) . '</td>
                <td class="text-right">' . formatCurrency($price) . '</td>
                <td class="text-center">' . $qty . '</td>
                <td class="text-right">' . formatCurrency($total) . '</td>
            </tr>';
            $hasOther = true; 
            $totalOtherSales += $total;
        }

        if (!$hasOther) {
            $html .= '<tr><td colspan="4" class="text-center" style="padding: 20px; color: #6c757d; font-style: italic;">(No other sales recorded)</td></tr>';
        } else {
            $html .= '<tr class="total-row">
                <td colspan="3" class="text-right"><b>Total Other Sales (₱)</b></td>
                <td class="text-right"><b>' . formatCurrency($totalOtherSales) . '</b></td>
            </tr>';
        }

        $html .= '</tbody></table>
            </td>

            <!-- RIGHT COLUMN: Expenses -->
            <td style="width:50%; vertical-align: top; padding-left:5px;">
                <div class="section-title">Expenses</div>
               
                <table class="table modern-table">
                    <thead>
                        <tr>
                            <th style="width:60%;">Type / Description</th>
                            <th style="width:40%;">Amount (₱)</th>
                        </tr>
                    </thead>
                    <tbody>';

        $total_exp = 0.0; 
        $hasExp = false;
        foreach ($expenses as $exp) {
            $html .= '<tr>
                <td class="text-left">' . htmlspecialchars($exp['expense_type']) . '</td>
                <td class="text-right">' . formatCurrency($exp['amount']) . '</td>
            </tr>';
            $total_exp += ($exp['amount'] ?: 0);
            $hasExp = true;
        }

        if (!$hasExp) {
            $html .= '<tr><td colspan="2" class="text-center" style="padding: 20px; color: #6c757d; font-style: italic;">(No expenses recorded)</td></tr>';
        } else {
            $html .= '<tr class="total-row">
                <td class="text-right"><b>Total Expenses (₱)</b></td>
                <td class="text-right"><b>' . formatCurrency($total_exp) . '</b></td>
            </tr>';
        }
        
        $html .= '</tbody></table>
                </td>
            </tr>
        </table>';

       

        // ---- Stock Movements (for report date) ----
        $html .= '<div class="section-title">Stock Movements (' . date('M d, Y', strtotime($report_date)) . ')</div>
        <table class="table modern-table">
            <thead>
                <tr>
                
                    <th style="width: 20%;">Product</th>
                    <th style="width: 10%;">Beginning</th>
                    <th style="width: 10%;">Qty In</th>
                    <th style="width: 10%;">Qty Out</th>
                    <th style="width: 10%;">Ending</th>
                    <th style="width: 14%;">Unit Cost (₱)</th>
                    <th style="width: 14%;">Shipping (₱)</th>
                    <th style="width: 14%;">Inventory Value (₱)</th>
                    
                    
                </tr>
            </thead>
            <tbody>';
        
        $hasSt = false; 
        $sumIn = 0; 
        $sumOut = 0;
        $sumShip = 0.0;
        foreach ($stocks as $st) {
            $qtyIn = intval($st['qty_in'] ?: 0);
            $qtyOut = intval($st['qty_out'] ?: 0);
            $unitCost = floatval($st['unit_cost'] ?: 0);
            $shipCost = floatval($st['shipping_cost'] ?: 0);
            $beginInv = isset($st['beginning_inv']) ? intval($st['beginning_inv']) : null;
            $endInv = isset($st['ending_inv']) ? intval($st['ending_inv']) : null;
            $html .= '<tr>
                
                <td class="text-left">' . strtoupper($st['product_name']) . '</td>
                <td class="text-center">' . ($beginInv !== null ? $beginInv : '-') . '</td>
                <td class="text-center">' . $qtyIn . '</td>
                <td class="text-center">' . $qtyOut . '</td>
                <td class="text-center">' . ($endInv !== null ? $endInv : '-') . '</td>
                <td class="text-right">' . number_format($unitCost,2) . '</td>
                <td class="text-right">' . number_format($shipCost,2) . '</td>
                <td class="text-right">' . number_format(($endInv * $unitCost),2) . '</td>
                
            </tr>';
            
            $sumIn += $qtyIn;
            $sumOut += $qtyOut;
            $sumShip += $shipCost;
            $sumendInv += $endInv;
            $sumbeginInv += $beginInv;
            $sumunitCost += $unitCost;
            $sumtotalinv = ($sumendInv * $sumunitCost);
            
            $hasSt = true;
        }
        
        if (!$hasSt) {
            $html .= '<tr><td colspan="8" class="text-center" style="padding: 20px; color: #6c757d; font-style: italic;">(No stock movements recorded for this date)</td></tr>';
        } else {
            $html .= '<tr class="total-row" >
                <td style="font-size:14px;" class="text-right">Totals</td>
                
                <td style="font-size:14px;" class="text-center">'. $sumbeginInv .'</td>
                <td style="font-size:14px;" class="text-center">' . $sumIn . '</td>
                <td style="font-size:14px;" class="text-center">' . $sumOut . '</td>
                <td style="font-size:14px;" class="text-center">'. $sumendInv .'</td>
                <td style="font-size:14px;" class="text-center">₱ ' . number_format($sumunitCost,2) . '</td>
                <td style="font-size:14px;" class="text-right">₱ ' . number_format($sumShip,2) . '</td>
                <td style="font-size:14px;" class="text-center">₱ ' . number_format($sumtotalinv,2) . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
        </table>';
    }

    // Add Prepared By section at the bottom
    $html .= '
    <div style="margin-top: 40px; page-break-inside: avoid;">
        <div style="padding-top: 20px; margin-top: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div style="text-align: left;">
                    <div style="font-size: 11px; color: #666; margin-bottom: 5px;">Prepared by:</div>
                    <div style="text-align:center; font-size: 12px; font-weight: bold; color: #2c3e50; border-bottom: 1px solid #000; width: 250px; padding-bottom: 2px;">
                        ' . htmlspecialchars($daily['preparedby'] ?: 'System Administrator') . '
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
    
    if ($action === 'download') {
        $mpdf->Output($isAggregated ? ('Bros_Aggregated_Report_'.$period.'.pdf') : ('Bros_Daily_Report_'.$report_date.'.pdf'), 'D');
    } else {
        $mpdf->Output($isAggregated ? ('Bros_Aggregated_Report_'.$period.'.pdf') : ('Bros_Daily_Report_'.$report_date.'.pdf'), 'I');
    }
}
?>
