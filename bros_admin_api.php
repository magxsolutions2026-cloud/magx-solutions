<?php
    // Dedicated API for Bro's Inasal features only
    $conn = mysqli_connect('localhost','root','root','tmc_admin_db');
    if (!$conn) { die('DB connection failed: ' . mysqli_connect_error()); }

    // Helper function to compute inventory values
    function computeInventoryValues($beginning_inv, $ending_inv, $unit_cost) {
        $beg_inv_value = floatval($beginning_inv) * floatval($unit_cost);
        $end_inv_value = floatval($ending_inv) * floatval($unit_cost);
        return [
            'beg_inv_value' => $beg_inv_value,
            'end_inv_value' => $end_inv_value
        ];
    }

    // ---------- BRO_DATE_DETAILS (daily report view) ----------
    if ($_POST["brotbl"] == "BRO_DATE_DETAILS") {
        $id = intval($_POST["id"]);
        $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM bros_dailyreport_tbl WHERE id='$id' LIMIT 1"));
        if (!$row) {
            echo '<p class="text-danger mb-0">Report not found.</p>';
            exit;
        }

        $report_date = $row["report_date"];

        // Expenses
        $expRes = mysqli_query($conn, "SELECT expense_type, specification, amount FROM bros_expense WHERE report_id='$id'");
        $expenses = [];
        $expense_total = 0;
        while ($e = mysqli_fetch_assoc($expRes)) {
            $expenses[] = $e;
            $expense_total += floatval($e['amount']);
        }

        // Cash breakdown
        $cashRes = mysqli_query($conn, "SELECT * FROM bros_cash_breakdown WHERE report_id='$id' LIMIT 1");
        $cashRow = $cashRes ? mysqli_fetch_assoc($cashRes) : null;
        $cash_total = $cashRow ? floatval($cashRow['total_amount']) : 0;

        // Other sales (by date)
        $osRes = mysqli_query($conn, "SELECT pname, pprice, quantity FROM bros_other_sale WHERE log_date='".mysqli_real_escape_string($conn,$report_date)."'");
        $otherSales = [];
        $other_total = 0;
        while ($os = mysqli_fetch_assoc($osRes)) {
            $otherSales[] = $os;
            $other_total += floatval($os['pprice']) * intval($os['quantity']);
        }

        ob_start();
        ?>
        <style>
            .bro-details-card { border: none; border-radius: 14px; box-shadow: 0 6px 16px rgba(0,0,0,0.12); overflow: hidden; margin-bottom: 14px; }
            .bro-details-card .card-header { background: linear-gradient(90deg, #672222, #8c2f2f); color: #fff; font-weight: 700; padding: 10px 14px; }
            .bro-details-card .card-body { padding: 12px 14px; background:#fff; }
            .bro-details-table th, .bro-details-table td { vertical-align: middle; }
            .bro-details-table thead { background: #f4f4f4; }
            .bro-num { text-align: right; white-space: nowrap; }
        </style>
        

        <div class="bro-details-card">
            <div class="card-header">Sales Summary</div>
            <div class="card-body">
                <table class="table table-sm table-striped table-bordered bro-details-table mb-2">
                    <tbody>
                        <tr><th style="width:50%;">POS Sales</th><td class="bro-num">₱<?php echo number_format($row["total_sales_pos"],2); ?></td></tr>
                        <tr><th>Other Sales</th><td class="bro-num">₱<?php echo number_format($other_total,2); ?></td></tr>
                        <tr><th>Gross Sales</th><td class="bro-num">₱<?php echo number_format($row["gross_sales"],2); ?></td></tr>
                        <tr><th>Expenses</th><td class="bro-num">₱<?php echo number_format($expense_total,2); ?></td></tr>
                        <tr><th>Net Sales</th><td class="bro-num">₱<?php echo number_format($row["net_sales"],2); ?></td></tr>
                    </tbody>
                </table>
                <table class="table table-sm table-striped table-bordered bro-details-table mb-0">
                    <tbody>
                        <tr><th style="width:50%;">Cash on Counter</th><td class="bro-num">₱<?php echo number_format($row["cash_on_counter"],2); ?></td></tr>
                        <tr><th>Cash In (Remittance)</th><td class="bro-num">₱<?php echo number_format($row["cash_in"],2); ?></td></tr>
                        <tr><th>GCash Sales</th><td class="bro-num">₱<?php echo number_format($row["gcash_sales"],2); ?></td></tr>
                        <tr><th>Credit Sales</th><td class="bro-num">₱<?php echo number_format($row["credit_sales"],2); ?></td></tr>
                        <tr><th>Total Cash Sales</th><td class="bro-num">₱<?php echo number_format($row["total_cash_sales"],2); ?></td></tr>
                        <tr><th>Total Sales (Counter)</th><td class="bro-num">₱<?php echo number_format($row["total_sales_counter"],2); ?></td></tr>
                        <tr><th>Over / Short</th><td class="bro-num">₱<?php echo number_format($row["over_short"],2); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bro-details-card">
            <div class="card-header">Expenses</div>
            <div class="card-body">
            <?php if (count($expenses) > 0): ?>
                <table class="table table-sm table-striped table-bordered bro-details-table">
                    <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenses as $e): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($e['expense_type']); ?></td>
                                <td class="bro-num">₱<?php echo number_format($e['amount'],2); ?></td>
                                <td><?php echo htmlspecialchars($e['specification']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-end">Total</th>
                            <th colspan="2" class="bro-num">₱<?php echo number_format($expense_total,2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <p class="text-muted mb-1">No expenses recorded.</p>
            <?php endif; ?>
            </div>
        </div>

        <div class="bro-details-card">
            <div class="card-header">Other Sales</div>
            <div class="card-body">
            <?php if (count($otherSales) > 0): ?>
                <table class="table table-sm table-striped table-bordered bro-details-table">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($otherSales as $os): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($os['pname']); ?></td>
                                <td><?php echo intval($os['quantity']); ?></td>
                                <td class="bro-num">₱<?php echo number_format($os['pprice'],2); ?></td>
                                <td class="bro-num">₱<?php echo number_format(floatval($os['pprice'])*intval($os['quantity']),2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th class="bro-num">₱<?php echo number_format($other_total,2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <p class="text-muted mb-1">No other sales recorded.</p>
            <?php endif; ?>
            </div>
        </div>

        <div class="bro-details-card mb-0">
            <div class="card-header">Cash Breakdown</div>
            <div class="card-body">
            <?php if ($cashRow): ?>
                <table class="table table-sm table-striped table-bordered bro-details-table">
                    <tbody>
                        <tr><th style="width:50%;">Total Cash Counted</th><td class="bro-num">₱<?php echo number_format($cash_total,2); ?></td></tr>
                        <tr><th>1000</th><td class="bro-num"><?php echo intval($cashRow['cash_1000']); ?></td></tr>
                        <tr><th>500</th><td class="bro-num"><?php echo intval($cashRow['cash_500']); ?></td></tr>
                        <tr><th>200</th><td class="bro-num"><?php echo intval($cashRow['cash_200']); ?></td></tr>
                        <tr><th>100</th><td class="bro-num"><?php echo intval($cashRow['cash_100']); ?></td></tr>
                        <tr><th>50</th><td class="bro-num"><?php echo intval($cashRow['cash_50']); ?></td></tr>
                        <tr><th>20</th><td class="bro-num"><?php echo intval($cashRow['cash_20']); ?></td></tr>
                        <tr><th>10</th><td class="bro-num"><?php echo intval($cashRow['cash_10']); ?></td></tr>
                        <tr><th>5</th><td class="bro-num"><?php echo intval($cashRow['cash_5']); ?></td></tr>
                        <tr><th>1</th><td class="bro-num"><?php echo intval($cashRow['cash_1']); ?></td></tr>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted mb-1">No cash breakdown recorded.</p>
            <?php endif; ?>
            </div>
        </div>
        <?php
        $html = ob_get_clean();
        echo $html;
        exit;
    }

    if (!isset($_POST["brotbl"])) {
        http_response_code(400);
        echo 'Invalid request';
        exit;
    }

    // ---------- LOAD INVENTORY ----------
    if ($_POST["brotbl"] == "LOADBROINV") {
        $selectedLabel = isset($_POST['label_filter']) ? $_POST['label_filter'] : '';
        $searchTerm = isset($_POST['search_term']) ? $_POST['search_term'] : '';

        $labelQuery = "SELECT DISTINCT label FROM bros_inventory ORDER BY label ASC";
        $labelResult = mysqli_query($conn, $labelQuery);
        if (!$labelResult) { die("Label Query failed: " . mysqli_error($conn)); }

        $sql = "SELECT id, product_name, classification, unit_price, available_stock, label FROM bros_inventory";
        $whereConditions = [];
        
        if (!empty($selectedLabel)) {
            $whereConditions[] = "label = '" . mysqli_real_escape_string($conn, $selectedLabel) . "'";
        }
        
        if (!empty($searchTerm)) {
            $whereConditions[] = "product_name LIKE '%" . mysqli_real_escape_string($conn, $searchTerm) . "%'";
        }
        
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        $sql .= " ORDER BY product_name ASC";
        $result = mysqli_query($conn, $sql);
        if (!$result) { die("Inventory Query failed: " . mysqli_error($conn)); }

        $totalQuery = "SELECT COUNT(*) AS total FROM bros_inventory";
        $totalResult = mysqli_query($conn, $totalQuery);
        $totalRow = mysqli_fetch_assoc($totalResult);
        $totalRecords = $totalRow['total'];

        $output = '';
        $output .= '

            <style>
                .report-table tr:hover:not(.no-hover) td {
                    background: #814c4c81 !important;
                    cursor: pointer;
                    transition: all 0.3s ease;
                }
            </style>

           <table style="width: 100%; border-radius: 20px;">
                <tr class="no-hover"><td style="padding: 10px;">
                <table style="width: 100%; border-collapse: collapse;" class="report-table" id="border">

                <tr class="no-hover">
                    <td colspan="9" class="data" class="report-header" style="background: linear-gradient(90deg, #672222, #8c2f2f); border-radius: 10px 10px 0 0; padding:10px;">
                        <div class="report-header-container" >
                            <span class="report-title" style="color:white;">BROS INASAL INVENTORY</span>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                
                            <div class="header-search">';

        $output .= '<select id="label_filter" class="form-control labels">
                        <option style="background-color:#672222;" value="">-- Select Label/Category --</option>';
        while ($labelRow = mysqli_fetch_assoc($labelResult)) {
            $selected = ($labelRow['label'] == $selectedLabel) ? "selected" : "";
            $output .= '<option style="background-color:#672222;" value="' . $labelRow['label'] . '" ' . $selected . '>' . strtoupper($labelRow['label']) . '</option>';
        }
        $output .= '</select>';

        $output .= '</div></div></div></td></tr>
                <tr class="no-hover">
                    <td colspan="100%" style="width: 100%; background: linear-gradient(90deg, #672222, #8c2f2f);">
                        <div class="header-search2" style="display: flex; align-items: center; width: 100%;">
                            <div class="product-search-container" style="flex: 1; margin-right: 10px; position: relative;">
                                <input type="text" id="search_product" style="background: rgba(255, 255, 255, 0.15); border:none; color:white;" autocomplete="off" class="form-control product-search-input" placeholder="Type to search products..." value="' . htmlspecialchars($searchTerm, ENT_QUOTES) . '">
                                <div class="product-dropdown"></div>
                            </div>
                            <button type="button" id="search_btn" class="btn-search">Search</button>
                        </div>
                    </td>
                </tr>
                <tr class="no-hover">
                    <td class="data" style="width: 2%; background: #CCCCCC; padding: 8px;"></td>
                    <td class="data" style="width: 20%; background: #CCCCCC; padding: 8px;"><center><strong>PRODUCT</strong></center></td>
                    <td class="data" style="width: 5%; background: #CCCCCC; padding: 8px;"><center><strong>CLASSIFICATION</strong></center></td>
                    <td class="data" style="width: 10%; background: #CCCCCC; padding: 8px;"><center><strong>AVAILABLE STOCK</strong></center></td>
                    <td class="data" style="width: 15%; background: #CCCCCC; padding: 8px;"><center><strong>STATUS</strong></center></td>
                   
                </tr>';

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_array($result)) {
                $status = ($row["available_stock"] <= 10) ? "<span style='color:red;font-weight:bold;'>NEED TO RESTOCK</span>" : "<span style='color:green;'>OK</span>";
                $rowClass = ($row["available_stock"] <= 10) ? "low-stock" : "";
                $output .= '
                    <tr class="'.$rowClass.'">
                        <td class="data" style="background: #EEEEEE; text-align:center; padding:10px;">
                            <input type="checkbox" style="accent-color: maroon;" class="check_box" value="' . $row["id"] . '">
                        </td>
                        <td class="data" style="background: #EEEEEE; padding:10px;"><center>' . strtoupper($row["product_name"]) . '</center></td>
                        <td class="data" style="background: #EEEEEE; padding:10px;"><center>' . strtoupper($row["classification"]) . '</center></td>
                        <td class="data" style="background: #EEEEEE; padding:10px;"><center>' . intval($row["available_stock"]) . '</center></td>
                        <td class="data" style="background: #EEEEEE; padding:10px;"><center>' . $status . '</center></td>
                        

                        
                    </tr>';
            }
        } else {
            $output .= '<tr><td colspan="6" align="center">No records found</td></tr>';
        }

        $output .= '<tr><td colspan="6" style="padding: 2px;"><font size="1">Total Number of Records: ' . $totalRecords . '</font></td></tr>
                </table>
                </td></tr>
            </table>';

        echo $output;
        exit;
    }

    // ---------- LOAD DAILY REPORT ----------
    if ($_POST["brotbl"] == "LOADBRODAILY") {
        $from_date   = isset($_POST['from_date']) && $_POST['from_date'] != '' ? $_POST['from_date'] : null;
        $to_date     = isset($_POST['to_date']) && $_POST['to_date'] != '' ? $_POST['to_date'] : null;
        $report_type = isset($_POST['report_type']) ? $_POST['report_type'] : '';

        // Date filter SQL
        $date_condition = '';
        if ($from_date && $to_date) {
            $date_condition = "WHERE DATE(report_date) BETWEEN '$from_date' AND '$to_date'";
        }

        // Determine SQL based on report_type
        $is_aggregated = in_array($report_type, ['monthly','yearly']);
        if ($is_aggregated) {
            if ($report_type === 'monthly') {
                $group_by = "YEAR(report_date), MONTH(report_date)";
                $period   = "CONCAT(MONTHNAME(report_date), ' ', YEAR(report_date))";
            } else { // yearly
                $group_by = "YEAR(report_date)";
                $period   = "YEAR(report_date)";
            }

            $sql = "SELECT $period AS period,
                           SUM(gross_sales)   AS gross_total,
                           SUM(expenses)      AS expense_total,
                           SUM(net_sales)     AS net_total,
                           MIN(report_date)   AS min_date
                    FROM bros_dailyreport_tbl
                    " . ($date_condition ? "$date_condition" : "") . "
                    GROUP BY $group_by
                    ORDER BY min_date DESC";

            $totalQuery = "SELECT COUNT(*) AS total FROM (
                                SELECT 1 FROM bros_dailyreport_tbl ".($date_condition?"$date_condition":"")." GROUP BY $group_by
                            ) t";
        } else {
            // Daily (default). If no dates provided, last 7 days
            if ($from_date && $to_date) {
                $sql = "SELECT * FROM bros_dailyreport_tbl $date_condition ORDER BY report_date DESC";
                $totalQuery = "SELECT COUNT(*) AS total FROM bros_dailyreport_tbl $date_condition";
            } else {
                $sql = "SELECT * FROM bros_dailyreport_tbl ORDER BY report_date DESC LIMIT 7";
                $totalQuery = "SELECT COUNT(*) AS total FROM bros_dailyreport_tbl";
            }
        }

        $result = mysqli_query($conn, $sql);
        $totalResult = mysqli_query($conn, $totalQuery);
        $totalRow = mysqli_fetch_assoc($totalResult);
        $totalRecords = $totalRow['total'];

        $output = "";
        $output .= '
    <style>
        /* Row hover for Daily Report */
        #border tr:hover:not(.no-hover) td {
            background: #814c4c81 !important;
            cursor: pointer;
            transition: all 0.3s ease;
        }
    </style>
    <table style="width: 100%; border-radius: 20px;">
        <tr class="no-hover"><td style="padding: 10px;">
        <table style="width: 100%; border-collapse: collapse;" id="border">

        <tr class="no-hover">
            <td colspan="6" id="border" class="report-header">
                <div class="report-header-container">
                    <span class="report-title">
                        <div class="header-search" style="margin-right: 0px;">
                            <select id="label_filter" name="label_filter" class="form-control">
                                <option style="background-color:#672222;" value="perbranch" '.($report_type=="perbranch"?"selected":"").'>Daily Sales Report</option>
                                <option style="background-color:#672222;" value="monthly" '.($report_type=="monthly"?"selected":"").'>Monthly Sales Report</option>
                                <option style="background-color:#672222;" value="yearly" '.($report_type=="yearly"?"selected":"").'>Yearly Sales Report</option>
                            </select>
                        </div>
                    </span>

                    <div class="date-filter">
                        <label for="from_date">From:</label>
                        <div class="header-search-filter" style="height:35px;">
                            <input type="date" id="from_date" class="date-input" value="' . (!empty($from_date) ? $from_date : '') . '">
                        </div>
                        <label for="to_date">To:</label>
                        <div class="header-search-filter" style="height:35px;">
                            <input type="date" id="to_date" class="date-input" value="' . (!empty($to_date) ? $to_date : '') . '">
                        </div>
                        <button id="filterBtn" class="btn-filter" style="color:white; background: linear-gradient(90deg, #672222, #8c2f2f);">Filter</button>
                        <button id="resetBtn" class="btn-reset" style="color:white; background: linear-gradient(90deg, #672222, #8c2f2f);">Reset</button>
                    </div>
                </div>
            </td>
        </tr>';

        // Headers
        if ($is_aggregated) {
            $output .= '
        <tr class="no-hover">
            <td id="border" style="width: 40%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>PERIOD</strong></td>
            <td id="border" style="width: 15%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>GROSS SALES</strong></td>
            <td id="border" style="width: 15%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>EXPENSES</strong></td>
            <td id="border" style="width: 15%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>NET SALES</strong></td>
            <td style="width: 15%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>ACTION</strong></td>
        </tr>';
        } else {
            $output .= '
        <tr class="no-hover">
            <td id="border" style="width: 5%; background: #CCCCCC; padding: 8px;"></td>
            <td id="border" style="width: 15%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>DATE</strong></td>
            <td id="border" style="width: 20%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>GROSS SALES</strong></td>
            <td id="border" style="width: 20%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>EXPENSES</strong></td>
            <td id="border" style="width: 25%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>NET SALES</strong></td>
            <td style="width: 15%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>ACTION</strong></td>
        </tr>';
        }

        if (mysqli_num_rows($result) > 0) {
            if ($is_aggregated) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $periodVal = $row['period'];
                    $output .= '
                <tr>
                    <td id="border" style="background:#EEEEEE; padding:10px; text-align:center;">' . strtoupper($periodVal) . '</td>
                    <td id="border" style="background:#EEEEEE; padding:10px; text-align:center;">₱' . number_format($row['gross_total'], 2) . '</td>
                    <td id="border" style="background:#EEEEEE; padding:10px; text-align:center;">₱' . number_format($row['expense_total'], 2) . '</td>
                    <td id="border" style="background:#EEEEEE; padding:10px; text-align:center;">₱' . number_format($row['net_total'], 2) . '</td>
                    <td id="border" style="background:#EEEEEE; padding:10px; text-align:center;">
                        <form method="POST" action="bropdf.php" target="_blank" style="display:inline;">
                            <input type="hidden" name="period" value="' . htmlspecialchars($periodVal, ENT_QUOTES) . '">
                            <input type="hidden" name="report_type" value="' . htmlspecialchars($report_type, ENT_QUOTES) . '">
                            <button type="submit" name="action" value="view" class="btn btn-primary" style="padding:2px; background:none; border:none;">
                                <img src="view.png" alt="icon" style="width:25px; height:25px;">
                            </button>
                        </form>
                        <form method="POST" action="bropdf.php" style="display:inline;">
                            <input type="hidden" name="period" value="' . htmlspecialchars($periodVal, ENT_QUOTES) . '">
                            <input type="hidden" name="report_type" value="' . htmlspecialchars($report_type, ENT_QUOTES) . '">
                            <button type="submit" name="action" value="download" class="btn btn-danger" style="padding:2px; background:none; border:none;">
                                <img src="down.png" alt="icon" style="width:25px; height:25px;">
                            </button>
                        </form>
                    </td>
                </tr>';
                }
            } else {
                while ($row = mysqli_fetch_array($result)) {
                    $output .= '
                <tr>
                    <td id="border" style="background: #EEEEEE; text-align:center; padding-left:10px;">
                        <input type="checkbox" style="accent-color: maroon;" class="check_box" value=' . $row["id"] . '>
                    </td>
                    <td id="border" style="background:#EEEEEE; padding:10px; text-align:center;">' 
                    . date("M. j, Y", strtotime($row["report_date"])) . 
                    '</td>
                    <td id="border" style="background:#EEEEEE; padding:10px; text-align:center;">₱' . number_format($row["gross_sales"], 2) . '</td>
                    <td id="border" style="background:#EEEEEE; padding:10px; text-align:center;">₱' . number_format($row["expenses"], 2) . '</td>
                    <td id="border" style="background:#EEEEEE; padding:10px; text-align:center;">₱' . number_format($row["net_sales"], 2) . '</td>
                    <td id="border" style="background:#EEEEEE; text-align:center; padding:10px;">
                         <div>
                            <button type="button"
                                    class="btn btn-sm btn-outline-dark date-details-btn"
                                    data-id="'.intval($row["id"]).'"
                                    data-date="'.htmlspecialchars($row["report_date"]).'"
                                    data-branch="">
                                View
                            </button>
                            <form method="POST" action="bropdf.php" target="_blank" style="display:inline;">
                                <input type="hidden" name="row_id" value="'.$row['id'].'">
                                <button type="submit" name="action" value="view" class="btn btn-primary" style="padding:2px; background:none; border:none;">
                                    <img src="view.png" alt="icon" style="width:25px; height:25px;">
                                </button>
                            </form>

                            <form method="POST" action="bropdf.php" style="display:inline;">
                                <input type="hidden" name="row_id" value="'.$row['id'].'">
                                <button type="submit" name="action" value="download" class="btn btn-danger" style="padding:2px; background:none; border:none;">
                                    <img src="down.png" alt="icon" style="width:25px; height:25px;">
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>';
                }
            }
        } else {
            $output .= $is_aggregated
                ? '<tr><td colspan="4" align="center">No records found</td></tr>'
                : '<tr><td colspan="5" align="center">No daily reports found</td></tr>';
        }

        $output .= '
        <tr><td colspan="6" style="padding: 2px;">
            <font size="1">Total Records: ' . $totalRecords . '</font>
        </td></tr>
        </table>
        </td></tr>
    </table>';

        echo $output;
        exit;
    }

    // ---------- SAVE_DAILY ----------
    if ($_POST["brotbl"] == "SAVE_DAILY") {
        $report_date     = $_POST["report_date"];
         $preparedby = isset($_POST["preparedby"]) ? mysqli_real_escape_string($conn, $_POST["preparedby"]) : '';
        $cash_on_counter = $_POST["cash_on_counter"] ?: 0;
        $cash_in         = $_POST["cash_in"] ?: 0;
        $gcash_sales     = $_POST["gcash_sales"] ?: 0;
        $credit_sales    = $_POST["credit_sales"] ?: 0;
        $expenses_arr    = json_decode($_POST["expenses"], true);
        $cash_arr        = json_decode($_POST["cash_breakdown"], true);
        $total_sales_pos = isset($_POST["Totalsales"]) ? floatval($_POST["Totalsales"]) : 0;

        // Check if report already exists for this date
        $check = mysqli_query($conn, "SELECT * FROM bros_dailyreport_tbl WHERE report_date='$report_date'");
        if(mysqli_num_rows($check) > 0){
            echo 'You already submitted a report for this date...';
            exit;
        }
        // POS total (from frontend field "Totalsales")
        $total_sales_pos = isset($_POST["Totalsales"]) ? floatval($_POST["Totalsales"]) : 0;
        // Compute other sales from dynamic rows (pname, pprice, quantity)
        $other_sales_total = 0;
        if (isset($_POST["other_sales_rows"])) {
            $os_rows = json_decode($_POST["other_sales_rows"], true);
            if (is_array($os_rows)) {
                foreach ($os_rows as $os_item) {
                    $price = isset($os_item['pprice']) ? floatval($os_item['pprice']) : 0;
                    $qty   = isset($os_item['quantity']) ? intval($os_item['quantity']) : 0;
                    $other_sales_total += ($price * $qty);
                }
            }
        }

        // Compute cash breakdown totals first
        $denoms = [1000, 500, 200, 100, 50, 20, 10, 5, 1];
        $cash_values = [];
        $total_amount = 0;
        foreach ($denoms as $d) {
            $qty = isset($cash_arr[$d]) ? intval($cash_arr[$d]) : 0;
            $cash_values[$d] = $qty;
            $total_amount += $d * $qty;
        }


         // Treat Cash In as staff remittance (can be less). It reduces expected cash on hand
        $total_cash_sales    = max(0, ($cash_on_counter - $cash_in));
        // Align with PDF logic: Gross = POS (Totalsales) + Other Sales
        $total_expense       = 0;
        foreach ($expenses_arr as $exp) { $total_expense += floatval($exp['amount']); }
        $gross_sales         = floatval($total_sales_pos) + floatval($other_sales_total);
        $net_sales           = $gross_sales - $total_expense;
        // Total sales counter includes cash, gcash and credit per PDF computation
        $total_sales_counter = $total_cash_sales + $gcash_sales + $credit_sales;
        // Over/Short per PDF: Total Sales (counter) - Net Sales (Should be)
        $over_short          = $total_sales_counter - $net_sales;

        $insertDaily = mysqli_query($conn, "
            INSERT INTO bros_dailyreport_tbl 
            (report_date, preparedby, cash_on_counter, cash_in, total_cash_sales, gcash_sales, credit_sales, gross_sales, total_sales_pos, expenses, net_sales, total_sales_counter, over_short)
            VALUES 
            ('$report_date','$preparedby','$cash_on_counter','$cash_in','$total_cash_sales','$gcash_sales','$credit_sales','$gross_sales','$total_sales_pos','$total_expense','$net_sales','$total_sales_counter','$over_short')
        ");

        if ($insertDaily) {
            $daily_id = mysqli_insert_id($conn);

            foreach ($expenses_arr as $exp) {
                $type = isset($exp['type']) ? mysqli_real_escape_string($conn, $exp['type']) : '';
                // Support old 'other' key as fallback to populate specification
                $spec = '';
                if (isset($exp['specification'])) {
                    $spec = mysqli_real_escape_string($conn, $exp['specification']);
                } elseif (isset($exp['other'])) {
                    $spec = mysqli_real_escape_string($conn, $exp['other']);
                }
                $amt  = isset($exp['amount']) ? floatval($exp['amount']) : 0;
                if ((!empty($type) || !empty($spec)) && $amt > 0) {
                    mysqli_query($conn, "
                        INSERT INTO bros_expense (report_id, expense_date, expense_type, specification, amount)
                        VALUES ('$daily_id', '$report_date', '$type', '$spec', '$amt')
                    ");
                }
            }

            mysqli_query($conn, "
                INSERT INTO bros_cash_breakdown 
                (report_id, cash_1000, cash_500, cash_200, cash_100, cash_50, cash_20, cash_10, cash_5, cash_1, total_amount)
                VALUES (
                    '$daily_id',
                    '{$cash_values[1000]}',
                    '{$cash_values[500]}',
                    '{$cash_values[200]}',
                    '{$cash_values[100]}',
                    '{$cash_values[50]}',
                    '{$cash_values[20]}',
                    '{$cash_values[10]}',
                    '{$cash_values[5]}',
                    '{$cash_values[1]}',
                    '$total_amount'
                )
            ");

            // Save Other Sales rows into bros_other_sale table (by log_date)
            if (isset($_POST["other_sales_rows"])) {
                $other_sales_rows = json_decode($_POST["other_sales_rows"], true);
                if (is_array($other_sales_rows)) {
                    foreach ($other_sales_rows as $item) {
                        $pname    = mysqli_real_escape_string($conn, $item['pname']);
                        $pprice   = mysqli_real_escape_string($conn, strval($item['pprice']));
                        $quantity = intval($item['quantity']);
                        if (!empty($pname) && $quantity > 0) {
                            mysqli_query($conn, "INSERT INTO bros_other_sale (log_date, pname, pprice, quantity) VALUES ('$report_date', '$pname', '$pprice', '$quantity')");
                        }
                    }
                }
            }

            echo "Daily Report Saved Successfully";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
        exit;
    }

    // ---------- UPDATE_DAILY ----------
    if ($_POST["brotbl"] == "UPDATE_DAILY") {
        $id              = $_POST["id"];
        $report_date     = $_POST["report_date"];
        $preparedby = isset($_POST["preparedby"]) ? mysqli_real_escape_string($conn, $_POST["preparedby"]) : '';
        $cash_on_counter = $_POST["cash_on_counter"] ?: 0;
        $cash_in         = $_POST["cash_in"] ?: 0;
        $gcash_sales     = $_POST["gcash_sales"] ?: 0;
        $credit_sales    = $_POST["credit_sales"] ?: 0;
        $total_sales_pos = isset($_POST["Totalsales"]) ? floatval($_POST["Totalsales"]) : 0;
        $expenses_arr    = json_decode($_POST["expenses"], true);
        $cash_arr        = json_decode($_POST["cash_breakdown"], true);
        // Compute other sales from dynamic rows
        $other_sales_total = 0;
        if (isset($_POST["other_sales_rows"])) {
            $os_rows = json_decode($_POST["other_sales_rows"], true);
            if (is_array($os_rows)) {
                foreach ($os_rows as $os_item) {
                    $price = isset($os_item['pprice']) ? floatval($os_item['pprice']) : 0;
                    $qty   = isset($os_item['quantity']) ? intval($os_item['quantity']) : 0;
                    $other_sales_total += ($price * $qty);
                }
            }
        }

        // Compute cash breakdown totals first
        $denoms = [1000, 500, 200, 100, 50, 20, 10, 5, 1];
        $cash_values = [];
        $total_amount = 0;
        foreach ($denoms as $d) {
            $qty = isset($cash_arr[$d]) ? intval($cash_arr[$d]) : 0;
            $cash_values[$d] = $qty;
            $total_amount += $d * $qty;
        }

         // Treat Cash In as staff remittance (can be less). It reduces expected cash on hand
        $total_cash_sales    = max(0, ($cash_on_counter - $cash_in));
        // Align with PDF logic: Gross = POS (Totalsales) + Other Sales
        $total_expense       = 0;
        foreach ($expenses_arr as $exp) { $total_expense += floatval($exp['amount']); }
        $gross_sales         = floatval($total_sales_pos) + floatval($other_sales_total);
        $net_sales           = $gross_sales - $total_expense;
        // Total sales counter includes cash, gcash and credit per PDF computation
        $total_sales_counter = $total_cash_sales + $gcash_sales + $credit_sales;
        // Over/Short per PDF: Total Sales (counter) - Net Sales (Should be)
        $over_short          = $total_sales_counter - $net_sales;

        $updateDaily = mysqli_query($conn, "
            UPDATE bros_dailyreport_tbl 
            SET 
                report_date       = '$report_date',
                preparedby        = '$preparedby',
                cash_on_counter   = '$cash_on_counter',
                cash_in           = '$cash_in',
                total_cash_sales  = '$total_cash_sales',
                gcash_sales       = '$gcash_sales',
                credit_sales      = '$credit_sales',
                gross_sales       = '$gross_sales',
                total_sales_pos   = '$total_sales_pos',
                expenses          = '$total_expense',
                net_sales         = '$net_sales',
                total_sales_counter = '$total_sales_counter',
                over_short        = '$over_short'
            WHERE id='$id'
        ");

        if ($updateDaily) {
            mysqli_query($conn, "DELETE FROM bros_expense WHERE report_id='$id'");
            foreach ($expenses_arr as $exp) {
                $type = isset($exp['type']) ? mysqli_real_escape_string($conn, $exp['type']) : '';
                $spec = '';
                if (isset($exp['specification'])) {
                    $spec = mysqli_real_escape_string($conn, $exp['specification']);
                } elseif (isset($exp['other'])) {
                    $spec = mysqli_real_escape_string($conn, $exp['other']);
                }
                $amt  = isset($exp['amount']) ? floatval($exp['amount']) : 0;
                if ((!empty($type) || !empty($spec)) && $amt > 0) {
                    mysqli_query($conn, "
                        INSERT INTO bros_expense (report_id, expense_date, expense_type, specification, amount)
                        VALUES ('$id', '$report_date', '$type', '$spec', '$amt')
                    ");
                }
            }

            mysqli_query($conn, "DELETE FROM bros_cash_breakdown WHERE report_id='$id'");
            mysqli_query($conn, "
                INSERT INTO bros_cash_breakdown 
                (report_id, cash_1000, cash_500, cash_200, cash_100, cash_50, cash_20, cash_10, cash_5, cash_1, total_amount)
                VALUES (
                    '$id',
                    '{$cash_values[1000]}',
                    '{$cash_values[500]}',
                    '{$cash_values[200]}',
                    '{$cash_values[100]}',
                    '{$cash_values[50]}',
                    '{$cash_values[20]}',
                    '{$cash_values[10]}',
                    '{$cash_values[5]}',
                    '{$cash_values[1]}',
                    '$total_amount'
                )
            ");

            // Refresh Other Sales rows for this report date
            mysqli_query($conn, "DELETE FROM bros_other_sale WHERE log_date='$report_date'");
            if (isset($_POST["other_sales_rows"])) {
                $other_sales_rows = json_decode($_POST["other_sales_rows"], true);
                if (is_array($other_sales_rows)) {
                    foreach ($other_sales_rows as $item) {
                        $pname    = mysqli_real_escape_string($conn, $item['pname']);
                        $pprice   = mysqli_real_escape_string($conn, strval($item['pprice']));
                        $quantity = intval($item['quantity']);
                        if (!empty($pname) && $quantity > 0) {
                            mysqli_query($conn, "INSERT INTO bros_other_sale (log_date, pname, pprice, quantity) VALUES ('$report_date', '$pname', '$pprice', '$quantity')");
                        }
                    }
                }
            }

            echo "Daily Report Updated Successfully";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
        exit;
    }

    // ---------- SELECT_DAILY ----------
    if ($_POST["brotbl"] == "SELECT_DAILY") {
        $id = $_POST["id"];
        $sql = "SELECT * FROM bros_dailyreport_tbl WHERE id='$id'";
        $result = mysqli_query($conn, $sql);
        $output = [];

        if ($row = mysqli_fetch_assoc($result)) {
            $output["id"]                 = $row["id"];
            $output["report_date"]        = $row["report_date"];
            $output["preparedby"]         = $row["preparedby"];
            $output["cash_on_counter"]    = $row["cash_on_counter"];
            $output["cash_in"]            = $row["cash_in"];
            $output["total_cash_sales"]   = $row["total_cash_sales"];
            $output["gcash_sales"]        = $row["gcash_sales"];
            $output["credit_sales"]       = $row["credit_sales"];
            $output["total_sales_pos"]    = $row["total_sales_pos"];
            $output["gross_sales"]        = $row["gross_sales"];
            $output["expenses"]           = $row["expenses"];
            $output["net_sales"]          = $row["net_sales"];
            $output["total_sales_counter"]= $row["total_sales_counter"];
            $output["over_short"]         = $row["over_short"];

            $expRes = mysqli_query($conn, "SELECT expense_type, specification, amount FROM bros_expense WHERE report_id='$id'");
            $expenses = [];
            while ($exp = mysqli_fetch_assoc($expRes)) {
                $type = in_array($exp['expense_type'], ["Electricity Expense","Water Expense","Salary Expense","Supplies Expense","Royalty Fee","Internet Expense","Direct Operating Expense","Miscellaneous Expense"]) 
                            ? $exp['expense_type']
                            : (empty($exp['expense_type']) ? "" : $exp['expense_type']);
                // For backward compatibility: if type is not in preset list, keep it in type and leave specification as DB value
                $other = isset($exp['specification']) ? $exp['specification'] : "";
                $expenses[] = [
                    "expense_type" => $type,
                    "amount"       => $exp['amount'],
                    "other"        => $other,
                    "specification" => $other
                ];
            }
            $output["expense_list"] = $expenses;

            $cashRes = mysqli_query($conn, "SELECT * FROM bros_cash_breakdown WHERE report_id='$id' LIMIT 1");
            $output["cash_breakdown"] = mysqli_fetch_assoc($cashRes);

            // Other sales list (by report date)
            $osRes = mysqli_query($conn, "SELECT record_no, pname, pprice, quantity FROM bros_other_sale WHERE log_date='".$row["report_date"]."' ORDER BY record_no ASC");
            $otherSales = [];
            $otherSalesTotal = 0;
            while ($os = mysqli_fetch_assoc($osRes)) { $otherSales[] = $os; }
            $output["other_sales_list"] = $otherSales;
            // Compute total other sales
            foreach ($otherSales as $os) {
                $price = isset($os['pprice']) ? floatval($os['pprice']) : 0;
                $qty   = isset($os['quantity']) ? intval($os['quantity']) : 0;
                $otherSalesTotal += ($price * $qty);
            }
            $output["other_sales"] = $otherSalesTotal;
        }

        echo json_encode($output);
        exit;
    }

    // ---------- DELETE_DAILY ----------
    if ($_POST["brotbl"] == "DELETE_DAILY") {
        $id = $_POST["id"];
        // Get date to clean related other sales
        $dateRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT report_date FROM bros_dailyreport_tbl WHERE id='$id'"));
        $repDate = $dateRow ? $dateRow['report_date'] : null;
        mysqli_query($conn, "DELETE FROM bros_expense WHERE report_id='$id'");
        mysqli_query($conn, "DELETE FROM bros_cash_breakdown WHERE report_id='$id'");
        if ($repDate) { mysqli_query($conn, "DELETE FROM bros_other_sale WHERE log_date='$repDate'"); }
        $execute = mysqli_query($conn, "DELETE FROM bros_dailyreport_tbl WHERE id='$id'");
        echo $execute ? "Daily Report has been deleted" : ("Error: " . mysqli_error($conn));
        exit;
    }

    // ---------- LOAD STOCK MOVEMENT ----------
    if ($_POST["brotbl"] == "LOADBROSTCK") {
        $from_date   = isset($_POST['from_date']) && $_POST['from_date'] != '' ? $_POST['from_date'] : null;
        $to_date     = isset($_POST['to_date']) && $_POST['to_date'] != '' ? $_POST['to_date'] : null;

        // Date filter SQL
        $date_condition = '';
        if ($from_date && $to_date) {
            $date_condition = "WHERE DATE(sm.movement_date) BETWEEN '$from_date' AND '$to_date'";
        }

        // Daily stock movement query - If no dates provided, last 30 days
        if ($from_date && $to_date) {
            $sql = "SELECT sm.id, sm.product_id, i.product_name, sm.qty_in, sm.qty_out,
                    COALESCE(i.unit_price, sm.unit_cost) AS unit_cost,
                    sm.movement_date, sm.beginning_inv, sm.ending_inv
                    FROM bro_stock_movements sm
                    LEFT JOIN bros_inventory i ON sm.product_id = i.id
                    $date_condition
                    ORDER BY sm.movement_date DESC, sm.id DESC";
            $totalQuery = "SELECT COUNT(*) AS total FROM bro_stock_movements sm $date_condition";
        } else {
            $sql = "SELECT sm.id, sm.product_id, i.product_name, sm.qty_in, sm.qty_out,
                    sm.unit_cost,sm.movement_date, sm.beginning_inv, sm.ending_inv
                    FROM bro_stock_movements sm
                    LEFT JOIN bros_inventory i ON sm.product_id = i.id
                    ORDER BY sm.movement_date DESC, sm.id DESC LIMIT 30";
            $totalQuery = "SELECT COUNT(*) AS total FROM bro_stock_movements";
        }

        $result = mysqli_query($conn, $sql);
        if (!$result) { die("Stock Movement Query failed: " . mysqli_error($conn)); }

        $totalResult = mysqli_query($conn, $totalQuery);
        $totalRow = mysqli_fetch_assoc($totalResult);
        $totalRecords = $totalRow['total'];

        $output = '';
        $output .= '
            <style>
                /* Row hover for Stock Movement */
                .border-table tr:hover:not(.no-hover) td {
                    background: #814c4c81 !important;
                    cursor: pointer;
                    transition: all 0.3s ease;
                }
            </style>
            <table style="width: 100%; border-radius: 20px;">
                <tr class="no-hover"><td style="padding: 10px;">
                <table class="border-table" style="width: 100%; border-collapse: collapse;" id="border">

                <tr class="no-hover">
                    <td colspan="9" id="border" class="report-header">
                        <div class="report-header-container">
                            <span class="report-title">STOCK MOVEMENT REPORT</span>

                            <div class="date-filter">
                                <label for="from_date">From:</label>
                                <div class="header-search-filter" style="height:35px;">
                                    <input type="date" id="from_date" class="date-input" value="' . (!empty($from_date) ? $from_date : '') . '">
                                </div>
                                <label for="to_date">To:</label>
                                <div class="header-search-filter" style="height:35px;">
                                    <input type="date" id="to_date" class="date-input" value="' . (!empty($to_date) ? $to_date : '') . '">
                                </div>
                                <button id="filterBtn" class="btn-filter" style="color:white; background: linear-gradient(90deg, #672222, #8c2f2f);">Filter</button>
                                <button id="resetBtn" class="btn-reset" style="color:white; background: linear-gradient(90deg, #672222, #8c2f2f);">Reset</button>
                                 
                            </div>
                             
                             <img src="add.png" id="stockbtn" alt="add" style="position: fixed; right: 28px; bottom: 28px; width: 60px; height: 60px; border-radius: 50%;  cursor: pointer; z-index: 1100; filter: drop-shadow(5px 5px 10px rgba(0,0,0,0.3)); transition: transform 0.2s ease;">
                           
                        </div>
                    </td>
                </tr>';

        // Headers - Only daily movement columns
        $output .= '
            <tr class="no-hover">
                <td id="border" style="width: 12%; background: #CCCCCC; padding: 8px;"><center><strong>DATE</strong></center></td>
                <td id="border" style="width: 24%; background: #CCCCCC; padding: 8px;"><center><strong>PRODUCT</strong></center></td>
                <td id="border" style="width: 12%; background: #CCCCCC; padding: 8px;"><center><strong>BEGINNING INV</strong></center></td>
                <td id="border" style="width: 9%; background: #CCCCCC; padding: 8px;"><center><strong>QTY IN</strong></center></td>
                <td id="border" style="width: 9%; background: #CCCCCC; padding: 8px;"><center><strong>QTY OUT</strong></center></td>
                <td id="border" style="width: 12%; background: #CCCCCC; padding: 8px;"><center><strong>ENDING INV</strong></center></td>
                <td id="border" style="width: 12%; background: #CCCCCC; padding: 8px;"><center><strong>UNIT COST</strong></center></td>
                <td class="delhide" style="width: 10%; background: #CCCCCC; padding: 8px;"><center><strong>ACTION</strong></center></td>
            </tr>';

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $pid  = $row['product_id'];
                $date = date('M. j, Y', strtotime($row['movement_date']));

                // Prefer stored values; fallback compute for legacy rows
                $begin = isset($row['beginning_inv']) ? intval($row['beginning_inv']) : null;
                $end   = isset($row['ending_inv']) ? intval($row['ending_inv']) : null;
                if ($begin === null || $end === null) {
                    // Running balance up to the previous row (including earlier same-day rows)
                    $prevSql = "SELECT COALESCE(SUM(qty_in - qty_out),0) AS bal
                                FROM bro_stock_movements
                                WHERE product_id='$pid' AND (movement_date < '$date' OR (movement_date = '$date' AND id < {$row['id']}))";
                    $prevRes = mysqli_query($conn, $prevSql);
                    $prevRow = mysqli_fetch_assoc($prevRes);
                    $begin = intval($prevRow['bal']);
                    $end   = $begin + intval($row['qty_in']) - intval($row['qty_out']);
                }

                $output .= '
                    <tr>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . $date . '</center></td>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . strtoupper($row["product_name"]) . '</center></td>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . $begin . '</center></td>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . intval($row['qty_in']) . '</center></td>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . intval($row['qty_out']) . '</center></td>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . $end . '</center></td>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . number_format(floatval($row['unit_cost']),2) . '</center></td>
                        
                        <td class="delhide" style="background: #EEEEEE; padding:10px;">
                            <center>
                                <button type="button" class="btn btn-danger btn-sm delete-stock" data-id="' . intval($row['id']) . '" title="Delete">
                                    <img src="deleteicon.png" alt="delete" style="width:20px; height:20px; vertical-align:middle;">
                                </button>
                            </center>
                        </td>
                    </tr>';
            }
        } else {
            $output .= '<tr><td colspan="8" align="center">No stock movements found</td></tr>';
        }

        $output .= '<tr><td colspan="8" style="padding: 2px;"><font size="1">Total Number of Records: ' . $totalRecords . '</font></td></tr>
                </table>
                </td></tr>
            </table>';
        echo $output;
        exit;
    }

    // ---------- INVENTORY CRUD ----------
    if ($_POST["brotbl"] == "SAVE") {
        $label = $_POST["label"];
        $product_name = $_POST["product_name"];
        $classification = $_POST["classification"];
        $unit_price = $_POST["unit_price"];
        $quantity = $_POST["quantity"];

        $check = mysqli_query($conn, "SELECT * FROM bros_inventory WHERE product_name='$product_name' AND label='$label'");
        if (mysqli_num_rows($check) > 0) {
            echo "Product already exists";
        } else {
            // Insert new product into inventory
            $insert = mysqli_query($conn, "INSERT INTO bros_inventory (label, product_name, classification, unit_price, available_stock, status)
                VALUES ('$label', '$product_name', '$classification', '$unit_price', '$quantity', 'OK')");
            
            if ($insert) {
                echo "Product added successfully";
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        }
        exit;
    }

    if ($_POST["brotbl"] == "SELECT") {
        $id = $_POST["id"];
        $res = mysqli_query($conn, "SELECT * FROM bros_inventory WHERE id='$id'");
        $data = mysqli_fetch_assoc($res);
        echo json_encode($data);
        exit;
    }

    if ($_POST["brotbl"] == "UPDATE") {
        $id = $_POST["id"];
        $label = $_POST["label"];
        $product_name = $_POST["product_name"];
        $classification = $_POST["classification"];
        $unit_price = $_POST["unit_price"];
        $quantity = $_POST["quantity"];

        $update = mysqli_query($conn, "UPDATE bros_inventory SET 
            label='$label', product_name='$product_name', classification='$classification', 
            unit_price='$unit_price', available_stock='$quantity', status='OK' 
            WHERE id='$id'");
        echo $update ? "Product updated successfully" : "Error: " . mysqli_error($conn);
        exit;
    }

    if ($_POST["brotbl"] == "DELETE") {
        $id = $_POST["id"];
        
        // First delete related stock movements
        $deleteMovements = mysqli_query($conn, "DELETE FROM bro_stock_movements WHERE product_id='$id'");
        
        // Then delete the product from inventory
        $delete = mysqli_query($conn, "DELETE FROM bros_inventory WHERE id='$id'");
        
        if ($delete) {
            echo "Product and related stock movements deleted successfully";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
        exit;
    }

    // ---------- GET PRODUCTS (for stock modal) ----------
    if ($_POST["brotbl"] == "GET_PRODUCTS") {
        $res = mysqli_query($conn, "SELECT id, product_name FROM bros_inventory ORDER BY product_name ASC");
        $items = [];
        while ($r = mysqli_fetch_assoc($res)) { $items[] = $r; }
        header('Content-Type: application/json');
        echo json_encode($items);
        exit;
    }

    // ---------- BI_GET_TRANSACTIONS (moved from adminfunction.php) ----------
    if (isset($_POST['action']) && $_POST['action'] == 'BI_GET_TRANSACTIONS') {
        $branch = mysqli_real_escape_string($conn, $_POST['branch']);
        $sql = "SELECT t.created_at, p.name AS product, t.action, t.quantity
                FROM bi_transactions t
                JOIN bi_products p ON p.id=t.product_id
                WHERE t.branch='$branch'
                ORDER BY t.created_at DESC
                LIMIT 20";
        $res = mysqli_query($conn, $sql);
        $rows = [];
        while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; }
        header('Content-Type: application/json');
        echo json_encode($rows);
        exit;
    }

    // ---------- GET BEGINNING INV (for product/date) ----------
    if ($_POST["brotbl"] == "GET_BEGIN") {
        $productName  = $_POST["product"];
        $movementDate = $_POST["movement_date"];
        $sql = "SELECT id FROM bros_inventory WHERE product_name = '".mysqli_real_escape_string($conn,$productName)."' LIMIT 1";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($res);
        $pid = $row ? intval($row['id']) : 0;
        $begin = 0;
        if ($pid > 0) {
            $q = "SELECT COALESCE(SUM(qty_in - qty_out),0) AS bal
                  FROM bro_stock_movements
                  WHERE product_id='$pid' AND movement_date < '".mysqli_real_escape_string($conn,$movementDate)."'";
            $r2 = mysqli_query($conn, $q);
            $a = mysqli_fetch_assoc($r2);
            $begin = intval($a['bal']);
        }
        header('Content-Type: application/json');
        echo json_encode([ 'begin' => $begin ]);
        exit;
    }

    // ---------- SAVE STOCK MOVEMENT ----------
    if ($_POST["brotbl"] == "SAVE2") {
		$productName   = $_POST["product"];
		$movementType  = isset($_POST["movement_type"]) ? $_POST["movement_type"] : null;
		$quantity      = isset($_POST["quantity"]) ? intval($_POST["quantity"]) : 0;
		$unitCost      = floatval($_POST["unit_cost"]);
		$shippingCost  = isset($_POST["shipping_cost"]) ? floatval($_POST["shipping_cost"]) : 0.00;
		$movementDate  = $_POST["movement_date"];

		// Support submitting both qty_in and qty_out simultaneously; fallback to movement_type + quantity
		$qtyInExplicit  = isset($_POST["qty_in"]) ? max(0, intval($_POST["qty_in"])) : null;
		$qtyOutExplicit = isset($_POST["qty_out"]) ? max(0, intval($_POST["qty_out"])) : null;

        $sql = "SELECT id FROM bros_inventory WHERE product_name = '$productName' LIMIT 1";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $productId = $row ? $row["id"] : null;

        if (!$productId) {
            $insertProduct = "INSERT INTO bros_inventory (product_name, classification, unit_price, available_stock, status) 
                            VALUES ('$productName', 'Unclassified', '$unitCost', 0, 'OK')";
            $execProd = mysqli_query($conn, $insertProduct);
            if ($execProd) {
                $productId = mysqli_insert_id($conn);
            } else {
                echo "Error: Failed to insert new product.";
                exit;
            }
        }

		if (($qtyInExplicit !== null && $qtyInExplicit > 0) || ($qtyOutExplicit !== null && $qtyOutExplicit > 0)) {
			$qty_in  = $qtyInExplicit ?: 0;
			$qty_out = $qtyOutExplicit ?: 0;
		} else {
			$qty_in  = ($movementType == "IN") ? $quantity : 0;
			$qty_out = ($movementType == "OUT") ? $quantity : 0;
		}

        // Disallow backdating: incoming date cannot be earlier than latest existing movement date
        $latestSql = "SELECT MAX(DATE(movement_date)) AS max_date FROM bro_stock_movements WHERE product_id='$productId'";
        $latestRes = mysqli_query($conn, $latestSql);
        if ($latestRes) {
            $latest = mysqli_fetch_assoc($latestRes);
            if (!empty($latest['max_date']) && strtotime($movementDate) < strtotime($latest['max_date'])) {
                echo "Error: Cannot record a movement dated earlier than the latest existing date (".$latest['max_date'].") for this product.";
                exit;
            }
        }

        // Check if there is already a movement for this product on this date (deduplicate per day)
        $existSql = "SELECT id, qty_in, qty_out, beginning_inv FROM bro_stock_movements WHERE product_id='$productId' AND DATE(movement_date)='$movementDate' LIMIT 1";
        $existRes = mysqli_query($conn, $existSql);
        if ($existRes && mysqli_num_rows($existRes) > 0) {
            $exist = mysqli_fetch_assoc($existRes);
            $old_qty_in  = intval($exist['qty_in']);
            $old_qty_out = intval($exist['qty_out']);
            $beginning_inv = intval($exist['beginning_inv']);

            $new_qty_in  = $old_qty_in + $qty_in;
            $new_qty_out = $old_qty_out + $qty_out;
            $ending_inv  = $beginning_inv + $new_qty_in - $new_qty_out;

            // Adjust inventory by delta between new and old diffs
            $old_diff = $old_qty_in - $old_qty_out;
            $new_diff = $new_qty_in - $new_qty_out;
            $delta    = $new_diff - $old_diff;

            // Compute inventory values for update
            $inv_values = computeInventoryValues($beginning_inv, $ending_inv, $unitCost);
            
            mysqli_query($conn, "UPDATE bro_stock_movements SET qty_in='$new_qty_in', qty_out='$new_qty_out', unit_cost='$unitCost', shipping_cost='$shippingCost', ending_inv='$ending_inv', beg_inv_value='{$inv_values['beg_inv_value']}', end_inv_value='{$inv_values['end_inv_value']}' WHERE id='{$exist['id']}'");
            mysqli_query($conn, "UPDATE bros_inventory SET available_stock = available_stock + $delta WHERE id = '$productId'");
            mysqli_query($conn, "UPDATE bros_inventory SET status = CASE WHEN available_stock <= 0 THEN 'NEED TO RESTOCK' ELSE 'OK' END WHERE id = '$productId'");
            echo "Stock has been updated for the day.";
        } else {
            // Compute beginning inventory as all prior movements up to this date (excluding this new row)
            $begSql = "SELECT COALESCE(SUM(qty_in - qty_out),0) AS bal
                       FROM bro_stock_movements
                       WHERE product_id='$productId' AND DATE(movement_date) <= '$movementDate'";
            $begRes = mysqli_query($conn, $begSql);
            $begRow = $begRes ? mysqli_fetch_assoc($begRes) : [ 'bal' => 0 ];
            $beginning_inv = intval($begRow['bal']);

            // Ending is this row's resulting balance
            $ending_inv = $beginning_inv + $qty_in - $qty_out;

            // Compute inventory values
            $inv_values = computeInventoryValues($beginning_inv, $ending_inv, $unitCost);
            
            // Insert including beginning and ending inventory with computed values
            $insert = "INSERT INTO bro_stock_movements (product_id, movement_date, qty_in, qty_out, unit_cost, shipping_cost, beginning_inv, ending_inv, beg_inv_value, end_inv_value) 
                    VALUES ('$productId', '$movementDate', '$qty_in', '$qty_out', '$unitCost', '$shippingCost', '$beginning_inv', '$ending_inv', '{$inv_values['beg_inv_value']}', '{$inv_values['end_inv_value']}')";
            $execute = mysqli_query($conn, $insert);

            if ($execute) {
                $diff = $qty_in - $qty_out;
                mysqli_query($conn, "UPDATE bros_inventory SET available_stock = available_stock + $diff WHERE id = '$productId'");
                mysqli_query($conn, "UPDATE bros_inventory SET status = CASE WHEN available_stock <= 0 THEN 'NEED TO RESTOCK' ELSE 'OK' END WHERE id = '$productId'");
                echo "Stock has been saved successfully.";
            } else {
                echo "Error saving stock.";
            }
        }
        exit;
    }

    // ---------- DELETE STOCK MOVEMENT ----------
    if ($_POST["brotbl"] == "DELETE_STOCK") {
        $rowId = intval($_POST["id"]);
        $res = mysqli_query($conn, "SELECT product_id, qty_in, qty_out FROM bro_stock_movements WHERE id='$rowId' LIMIT 1");
        if ($res && ($r = mysqli_fetch_assoc($res))) {
            $pid = intval($r['product_id']);
            $diff = intval($r['qty_in']) - intval($r['qty_out']);
            mysqli_query($conn, "DELETE FROM bro_stock_movements WHERE id='$rowId'");
            mysqli_query($conn, "UPDATE bros_inventory SET available_stock = available_stock - $diff WHERE id='$pid'");
            mysqli_query($conn, "UPDATE bros_inventory SET status = CASE WHEN available_stock <= 0 THEN 'NEED TO RESTOCK' ELSE 'OK' END WHERE id='$pid'");
            echo "Stock movement deleted";
        } else {
            echo "Error: Record not found";
        }
        exit;
    }

    // ---------- CHANGE_PASS_USER ----------
    if (isset($_POST['action']) && $_POST['action'] == 'CHANGE_PASS_USER') {
        $con = mysqli_connect('sql305.infinityfree.com','if0_40064351','US6waMf6Ka','if0_40064351_tmcuser');
        $current_username = $_POST["current_username"];
        $current_password = $_POST["current_password"];
        $new_username = $_POST["new_username"];
        $new_password = $_POST["new_password"];

        // Verify current credentials
        $verify_sql = "SELECT * FROM tbl_user WHERE username = '$current_username' AND password = '$current_password'";
        $verify_result = mysqli_query($con, $verify_sql);

        if (mysqli_num_rows($verify_result) > 0) {
            // Update username and password
            $update_sql = "UPDATE tbl_user SET username = '$new_username', password = '$new_password' WHERE username = '$current_username'";
            $update_result = mysqli_query($con, $update_sql);

            if ($update_result) {
                echo "Username and password updated successfully!";
            } else {
                echo "Error updating username and password!";
            }
        } else {
            echo "Current username or password is incorrect!";
        }
        exit;
    }

    // If no route matched
    echo 'Unknown brotbl action';
    exit;
?>


