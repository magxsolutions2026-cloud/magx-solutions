<?php
    // Use Philippine time for all PHP date/time functions
    date_default_timezone_set('Asia/Manila');

    $conn = mysqli_connect('localhost','root','root','magx_db');

    if (isset($_POST["action"])) {
        if ($_POST["action"] == "LOAD") {
            $sql = "SELECT * FROM tbl_logss ORDER BY record_no DESC LIMIT 5";
            $result = mysqli_query($conn, $sql);
            
            $totalQuery = "SELECT COUNT(*) AS total FROM tbl_logss";
            $totalResult = mysqli_query($conn, $totalQuery);
            $totalRow = mysqli_fetch_assoc($totalResult);
            $totalRecords = $totalRow['total'];

            $output = "";

            $output .= '
            <div class="logs-container">
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th colspan="4" class="logs-title">LOGS</th>
                        </tr>
                        <tr>
                            <th>TIME</th>
                            <th>DATE</th>
                            <th>name</th>
                            <th>TYPE OF USER</th>
                        </tr>
                    </thead>
                    <tbody>';

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                    $output .= '
                        <tr>
                            <td>' . strtoupper($row["oras"]) . '</td>
                            <td>' . strtoupper($row["petsa"]) . '</td>
                            <td>' . strtoupper($row["nameofuser"]) . '</td>
                            <td>' . strtoupper($row["unit"]) . '</td>
                        </tr>';
                }
            } else {
                $output .= '<tr><td colspan="4" align="center">No records found</td></tr>';
            }

            $output .= '
                    </tbody>
                </table>
                <div class="logs-footer">Total Number of Logs: ' . $totalRecords . '</div>
            </div>';

            echo $output;
        }
    }




    // ADD STOCK
    if (isset($_POST['action']) && $_POST['action'] == "ADD_STOCK") {
        $branch    = mysqli_real_escape_string($conn, $_POST['branch']);
        $fuel_type = mysqli_real_escape_string($conn, $_POST['fuel_type']);
        $quantity  = (float) $_POST['quantity'];

        // Get current stock before addition (this is the last_stock)
        $lastStockQuery = "SELECT current_stock FROM fuel_inventory WHERE branch = '$branch' AND fuel_type = '$fuel_type'";
        $lastStockResult = mysqli_query($conn, $lastStockQuery);
        $lastStockRow = mysqli_fetch_assoc($lastStockResult);
        $lastStock = $lastStockRow['current_stock'];

        // Update inventory
        $sql = "UPDATE fuel_inventory 
                SET current_stock = current_stock + $quantity 
                WHERE branch='$branch' AND fuel_type='$fuel_type'";
        if (mysqli_query($conn, $sql)) {
            // Get current stock after addition (this is the available_stock = current_stock)
            $availableStockQuery = "SELECT current_stock FROM fuel_inventory WHERE branch = '$branch' AND fuel_type = '$fuel_type'";
            $availableStockResult = mysqli_query($conn, $availableStockQuery);
            $availableStockRow = mysqli_fetch_assoc($availableStockResult);
            $availableStock = $availableStockRow['current_stock'];

            // Log transaction with last_stock and available_stock
            mysqli_query($conn, "INSERT INTO fuel_transactions (branch, fuel_type, quantity, action, last_stock, available_stock, created_at) 
                                VALUES ('$branch', '$fuel_type', $quantity, 'ADD', $lastStock, $availableStock, NOW())");
            echo "Stock added successfully!";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
        exit;
    }

    // LESS STOCK
    if (isset($_POST['action']) && $_POST['action'] == "LESS_STOCK") {
        $branch    = mysqli_real_escape_string($conn, $_POST['branch']);
        $fuel_type = mysqli_real_escape_string($conn, $_POST['fuel_type']);
        $quantity  = (float) $_POST['quantity'];

        // Get current stock before deduction (this is the last_stock)
        $lastStockQuery = "SELECT current_stock FROM fuel_inventory WHERE branch = '$branch' AND fuel_type = '$fuel_type'";
        $lastStockResult = mysqli_query($conn, $lastStockQuery);
        $lastStockRow = mysqli_fetch_assoc($lastStockResult);
        $lastStock = $lastStockRow['current_stock'];

        // Update inventory
        $sql = "UPDATE fuel_inventory 
                SET current_stock = GREATEST(current_stock - $quantity, 0) 
                WHERE branch='$branch' AND fuel_type='$fuel_type'";
        if (mysqli_query($conn, $sql)) {
            // Get current stock after deduction (this is the available_stock = current_stock)
            $availableStockQuery = "SELECT current_stock FROM fuel_inventory WHERE branch = '$branch' AND fuel_type = '$fuel_type'";
            $availableStockResult = mysqli_query($conn, $availableStockQuery);
            $availableStockRow = mysqli_fetch_assoc($availableStockResult);
            $availableStock = $availableStockRow['current_stock'];

            // Log transaction with last_stock and available_stock
            mysqli_query($conn, "INSERT INTO fuel_transactions (branch, fuel_type, quantity, action, last_stock, available_stock, created_at) 
                                VALUES ('$branch', '$fuel_type', $quantity, 'LESS', $lastStock, $availableStock, NOW())");
            echo "Stock lessened successfully!";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
        exit;
    }

    // GET TRANSACTIONS
    if (isset($_POST['action']) && $_POST['action'] == "GET_TRANSACTIONS") {
        $branch = mysqli_real_escape_string($conn, $_POST['branch']);
        
        // First, update any existing "SALE" records to "SOLD" for consistency
        mysqli_query($conn, "UPDATE fuel_transactions SET action = 'SOLD' WHERE action = 'SALE' AND branch = '$branch'");
        
        $result = mysqli_query($conn, "
            SELECT fuel_type, quantity, action, created_at, last_stock, available_stock 
            FROM fuel_transactions 
            WHERE branch='$branch' 
            ORDER BY created_at DESC 
            LIMIT 6
        ");
        
        $transactions = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $row['created_at'] = date('M. d, Y', strtotime($row['created_at']));

            $transactions[] = $row;
        }
        echo json_encode($transactions);
        exit;
    }

   // DAILY SALE
   // NOTE: This action only touches inventory/transactions and does NOT insert into gas_sales_tbl.
   if (isset($_POST['action']) && $_POST['action'] == "DAILY_SALE") {
        $branch    = mysqli_real_escape_string($conn, $_POST["branch"]);
        $fuel_type = mysqli_real_escape_string($conn, $_POST["fuel_type"]);
        $quantity  = (float) $_POST["quantity"];

        // Get current stock before deduction (this is the last_stock)
        $lastStockQuery  = "SELECT current_stock FROM fuel_inventory WHERE branch = '$branch' AND fuel_type = '$fuel_type'";
        $lastStockResult = mysqli_query($conn, $lastStockQuery);
        $lastStockRow    = mysqli_fetch_assoc($lastStockResult);
        $lastStock       = $lastStockRow ? $lastStockRow['current_stock'] : 0;

        // Deduct from inventory (use GREATEST to prevent negative stock)
        $sql = "UPDATE fuel_inventory 
                SET current_stock = GREATEST(current_stock - $quantity, 0)
                WHERE branch = '$branch' AND fuel_type = '$fuel_type'";
        if (!mysqli_query($conn, $sql)) {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
            exit;
        }

        // Get current stock after deduction (this is the available_stock = current_stock)
        $availableStockQuery  = "SELECT current_stock FROM fuel_inventory WHERE branch = '$branch' AND fuel_type = '$fuel_type'";
        $availableStockResult = mysqli_query($conn, $availableStockQuery);
        $availableStockRow    = mysqli_fetch_assoc($availableStockResult);
        $availableStock       = $availableStockRow ? $availableStockRow['current_stock'] : 0;

        // Insert into transactions log with last_stock and available_stock
        $sql2 = "INSERT INTO fuel_transactions (branch, fuel_type, action, quantity, last_stock, available_stock, created_at) 
                 VALUES ('$branch', '$fuel_type', 'SOLD', $quantity, $lastStock, $availableStock, NOW())";
        if (!mysqli_query($conn, $sql2)) {
            echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
            exit;
        }

        echo json_encode(["status" => "success"]);
        exit;
    }




    




    if (isset($_POST['action']) && $_POST['action'] == 'GET_INVENTORY') {
        // Get branch from AJAX safely
        $branch = mysqli_real_escape_string($conn, $_POST['branch']); 

        $query = mysqli_query($conn, "
            SELECT fuel_type, current_stock 
            FROM fuel_inventory 
            WHERE branch='$branch' 
            ORDER BY FIELD(fuel_type,'Diesel','Premium','Unleaded')
        ");

        $stock = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $stock[] = (int)$row['current_stock'];
        }

        echo json_encode($stock); // returns array like [1200, 900, 1500]
        exit; // important! stops further output
    }


    

    // List products for select options
    if (isset($_POST['action']) && $_POST['action'] == 'BI_GET_PRODUCTS') {
        $res = mysqli_query($conn, "SELECT id, name FROM bi_products ORDER BY name ASC");
        $products = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $products[] = $row;
        }
        echo json_encode($products);
        exit;
    }

    // Get inventory for a branch (labels + values + max)
    if (isset($_POST['action']) && $_POST['action'] == 'BI_GET_INVENTORY') {
        $branch = mysqli_real_escape_string($conn, $_POST['branch']);
        $sql = "SELECT p.name, p.max_stock, IFNULL(i.current_stock,0) AS qty
                FROM bi_products p
                LEFT JOIN bi_inventory i ON i.product_id=p.id AND i.branch='$branch'
                ORDER BY p.name";
        $res = mysqli_query($conn, $sql);
        $labels = []; $values = []; $max = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $labels[] = $row['name'];
            $values[] = (int)$row['qty'];
            $max[]    = (int)$row['max_stock'];
        }
        echo json_encode(["labels"=>$labels, "values"=>$values, "max"=>$max]);
        exit;
    }

    // Add stock
    if (isset($_POST['action']) && $_POST['action'] == 'BI_ADD_STOCK') {
        $branch     = mysqli_real_escape_string($conn, $_POST['branch']);
        $product_id = (int) $_POST['product_id'];
        $quantity   = (int) $_POST['quantity'];

        // Ensure inventory row exists
        mysqli_query($conn, "INSERT INTO bi_inventory(branch, product_id, current_stock)
                              SELECT '$branch', $product_id, 0
                              WHERE NOT EXISTS(
                                SELECT 1 FROM bi_inventory WHERE branch='$branch' AND product_id=$product_id
                              )");

        $ok = mysqli_query($conn, "UPDATE bi_inventory SET current_stock = current_stock + $quantity
                                   WHERE branch='$branch' AND product_id=$product_id");
        if ($ok) {
            mysqli_query($conn, "INSERT INTO bi_transactions(branch, product_id, action, quantity, created_at)
                                 VALUES('$branch',$product_id,'ADD',$quantity,NOW())");
            echo json_encode(["status"=>"success"]);
        } else {
            echo json_encode(["status"=>"error","message"=>mysqli_error($conn)]);
        }
        exit;
    }

    // Lessen stock
    if (isset($_POST['action']) && $_POST['action'] == 'BI_LESS_STOCK') {
        $branch     = mysqli_real_escape_string($conn, $_POST['branch']);
        $product_id = (int) $_POST['product_id'];
        $quantity   = (int) $_POST['quantity'];

        $ok = mysqli_query($conn, "UPDATE bi_inventory SET current_stock = GREATEST(current_stock - $quantity,0)
                                   WHERE branch='$branch' AND product_id=$product_id");
        if ($ok) {
            mysqli_query($conn, "INSERT INTO bi_transactions(branch, product_id, action, quantity, created_at)
                                 VALUES('$branch',$product_id,'LESS',$quantity,NOW())");
            echo json_encode(["status"=>"success"]);
        } else {
            echo json_encode(["status"=>"error","message"=>mysqli_error($conn)]);
        }
        exit;
    }

    // Transactions for BI
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
        echo json_encode($rows);
        exit;
    }


        if (isset($_POST["gastblsales"])) {
            if ($_POST["gastblsales"] == "LOADGASSALES") {

            // ---- Inputs ----
            $report_type = isset($_POST['report_type']) ? $_POST['report_type'] : 'perbranch'; // default perbranch
            $from_date   = !empty($_POST['from_date']) ? $_POST['from_date'] : null;
            $to_date     = !empty($_POST['to_date']) ? $_POST['to_date'] : null;

            // ---- Determine aggregation ----
            switch ($report_type) {
                
                
                case 'monthly':
                    $group_by = "YEAR(log_date), MONTH(log_date)";
                    $display_label = "CONCAT(MONTHNAME(log_date), ' ', YEAR(log_date))";
                    $sales_header = "MONTHLY SALES";
                    break;
                case 'yearly':
                    $group_by = "YEAR(log_date)";
                    $display_label = "YEAR(log_date)";
                    $sales_header = "YEARLY SALES";
                    break;
                case 'perbranch':
                default:
                    $group_by = "";
                    $display_label = "";
                    $sales_header = "DAILY SALES PER BRANCH";
                    $report_type = 'perbranch';
                    break;
            }

            // ---- Date filter ----
            $date_condition = "";
            if ($from_date && $to_date) {
                $date_condition = "WHERE DATE(log_date) BETWEEN '$from_date' AND '$to_date'";
            }

            // ---- SQL ----
             if ($report_type == 'perbranch') {
                // Include Lubricants + Expenses for per-branch daily (aggregated per branch & date)
                // New schema: gas_sales_tbl is per-fuel row; aggregate total_amount per branch/log_date
                $sql = "SELECT agg.id, agg.branch, agg.log_date,
                            agg.fuel_total,
                            IFNULL(l.lube_total,0) AS lube_total,
                            IFNULL(e.expense_total,0) AS expense_total,
                            (agg.fuel_total + IFNULL(l.lube_total,0) - IFNULL(e.expense_total,0)) AS net_sales
                        FROM (
                            SELECT MIN(id) AS id,
                                   branch,
                                   log_date,
                                   SUM(total_amount) AS fuel_total
                            FROM gas_sales_tbl
                            " . ($date_condition ? $date_condition : "") . "
                            GROUP BY branch, log_date
                        ) agg
                        LEFT JOIN (
                            SELECT branch, log_date, SUM(quantity * pprice) AS lube_total
                            FROM lub_sales_tbl
                            GROUP BY branch, log_date
                        ) l ON agg.branch = l.branch AND agg.log_date = l.log_date
                        LEFT JOIN (
                            SELECT branch, log_date, SUM(amount) AS expense_total
                            FROM gas_expenses_tbl
                            GROUP BY branch, log_date
                        ) e ON agg.branch = e.branch AND agg.log_date = e.log_date
                        ORDER BY agg.log_date DESC, agg.branch
                        LIMIT 6";

                $totalQuery = "SELECT COUNT(*) AS total FROM (
                                    SELECT branch, log_date
                                    FROM gas_sales_tbl
                                    " . ($date_condition ? $date_condition : "") . "
                                    GROUP BY branch, log_date
                               ) sub";
            } else {
                // Aggregated (monthly/yearly) using new per-fuel schema
                // First aggregate fuel per branch/log_date from gas_sales_tbl, then join lubes/expenses
                $sql = "SELECT $display_label AS period,
                            SUM(fuel_total) AS fuel_total,
                            SUM(lube_total) AS lube_total,
                            SUM(expense_total) AS expense_total,
                            SUM(fuel_total + lube_total - expense_total) AS total_sales
                        FROM (
                            SELECT g.branch, g.log_date,
                                   g.fuel_total,
                                   IFNULL(l.lube_total,0) AS lube_total,
                                   IFNULL(e.expense_total,0) AS expense_total
                            FROM (
                                SELECT branch,
                                       log_date,
                                       SUM(total_amount) AS fuel_total
                                FROM gas_sales_tbl
                                " . ($date_condition ? "WHERE DATE(log_date) BETWEEN '$from_date' AND '$to_date'" : "") . "
                                GROUP BY branch, log_date
                            ) g
                            LEFT JOIN (
                                SELECT branch, log_date, SUM(quantity * pprice) AS lube_total
                                FROM lub_sales_tbl
                                GROUP BY branch, log_date
                            ) l ON g.branch = l.branch AND g.log_date = l.log_date
                            LEFT JOIN (
                                SELECT branch, log_date, SUM(amount) AS expense_total
                                FROM gas_expenses_tbl
                                GROUP BY branch, log_date
                            ) e ON g.branch = e.branch AND g.log_date = e.log_date
                        ) t
                        " . ($group_by ? "GROUP BY $group_by" : "") . "
                        ORDER BY MIN(log_date) DESC";

                $totalQuery = "SELECT COUNT(*) AS total FROM (
                                    SELECT 1
                                    FROM (
                                        SELECT g.branch, g.log_date
                                        FROM (
                                            SELECT branch,
                                                   log_date,
                                                   SUM(total_amount) AS fuel_total
                                            FROM gas_sales_tbl
                                            " . ($date_condition ? "WHERE DATE(log_date) BETWEEN '$from_date' AND '$to_date'" : "") . "
                                            GROUP BY branch, log_date
                                        ) g
                                        LEFT JOIN (
                                            SELECT branch, log_date, SUM(quantity * pprice) AS lube_total
                                            FROM lub_sales_tbl
                                            GROUP BY branch, log_date
                                        ) l ON g.branch = l.branch AND g.log_date = l.log_date
                                        LEFT JOIN (
                                            SELECT branch, log_date, SUM(amount) AS expense_total
                                            FROM gas_expenses_tbl
                                            GROUP BY branch, log_date
                                        ) e ON g.branch = e.branch AND g.log_date = e.log_date
                                    ) t
                                    " . ($group_by ? "GROUP BY $group_by" : "") . "
                                ) sub";
            }


            $result = mysqli_query($conn, $sql);
            $totalResult = mysqli_query($conn, $totalQuery);
            $totalRow = mysqli_fetch_assoc($totalResult);
            $totalRecords = $totalRow['total'];

            // ---- Output table ----
            $output = '
            <style>
                .report-table tr:hover:not(.no-hover) td {
                background: #814c4c81 !important;
                cursor: pointer;
                transition: all 0.3s ease;
                
            }
            </style>

            <table style="width: 100%; border-radius: 20px;">
                <tr><td style="padding: 10px;">
                <table style="width: 100%; border-collapse: collapse;" class="report-table" id="border">

                <tr class="no-hover">
                    <td colspan="4"  class="report-header">
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

            

            // ---- Table headers ----
            if ($report_type == 'perbranch') {
                $output .= '
                <tr class="no-hover">
                    <td class="data" style="width:8%; background:#CCCCCC; padding:8px; text-align:center;"><strong></strong></td>
                    <td class="data" style="width:45%; background:#CCCCCC; padding:8px; text-align:center;"><strong>BRANCH</strong></td>
                    <td class="data" style="width:35%; background:#CCCCCC; padding:8px; text-align:center;"><strong>DAILY SALES</strong></td>
                </tr>';
            } else {
                $output .= '
                <tr>
                    <td class="data" style="width:50%; background:#CCCCCC; padding:8px; text-align:center;"><strong>PERIOD</strong></td>
                    <td class="data" style="width:40%; background:#CCCCCC; padding:8px; text-align:center;"><strong>'.$sales_header.'</strong></td>
                    <td class="data" style="width:10%; background:#CCCCCC; padding:8px; text-align:center;"></td>
                </tr>';
            }

            // ---- Table rows ----
            if (mysqli_num_rows($result) > 0) {
                if ($report_type == 'perbranch') {
                    // Fetch all rows grouped by date
                    $all_rows = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        $all_rows[$row['log_date']][] = $row;
                    }

                    foreach ($all_rows as $date => $rows_for_date) {
                        // Calculate total sales for this date
                        $total_sales = 0;
                        foreach ($rows_for_date as $row) {
                            $total_sales += $row['net_sales'];
                        }

                        // Output date row with total and PDF buttons
                        $output .= '
                        <tr class="no-hover">
                            <td colspan="3" style="background:#ddd; padding:10px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">

                                    <!-- Date on left -->
                                    <div style="flex: 1; font-weight: bold;">
                                        ' . date("M. d, Y", strtotime($date)) . '
                                        -(
                                        Total Sales: ₱ ' . number_format($total_sales, 2) . ')
                                    </div>

                                    <!-- PDF buttons on right -->
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <form method="POST" action="export_pdf.php" target="_blank" style="display:inline;">
                                            <input type="hidden" name="period" value="' . $date . '">
                                            <input type="hidden" name="report_type" value="' . $report_type . '">
                                            <button title="View" type="submit" name="action" value="view" style="padding:2px; background:none; border:none;">
                                                <img src="view.png" alt="view" style="width:25px; height:25px;">
                                            </button>
                                        </form>

                                    <form method="POST" action="export_pdf.php" style="display:inline; margin-left:5px;">
                                        <input type="hidden" name="period" value="' . $date . '">
                                        <input type="hidden" name="report_type" value="' . $report_type . '">
                                        <button title="Download" type="submit" name="action" value="download" style="padding:2px; background:none; border:none;">
                                            <img src="down.png" alt="download" style="width:25px; height:25px;">
                                        </button>
                                    </form>
                                    </div>

                                </div>
                            </td>
                        </tr>';

                        // Output each branch row
                        foreach ($rows_for_date as $row) {
                            $output .= '
                            <tr>
                                <td class="data" style="background:#EEEEEE; padding:10px; text-align:center;">
                                    <input type="checkbox" class="check_box" name="selected_ids[]" value="'.$row["id"].'" style="accent-color:#672222; ">
                                </td>
                                <td class="data" style="background:#EEEEEE; padding:10px; text-align:center;">' . strtoupper($row["branch"]) . '</td>
                                <td class="data" style="background:#EEEEEE; padding:10px;">
                                    <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                                        <span>₱' . number_format($row["net_sales"],2) . '</span>
                                        <button type="button" class="btn btn-sm btn-outline-dark date-details-btn" data-date="' . $date . '" data-branch="' . htmlspecialchars($row["branch"]) . '">View</button>
                                    </div>
                                </td>
                            </tr>';
                        }
                    }
                } else {
                    // Other report types
                    while ($row = mysqli_fetch_assoc($result)) {
                        $output .= '
                        <tr>
                            <td class="data" style="background:#EEEEEE; padding:10px; text-align:center;">' . strtoupper($row['period']) . '</td>
                            <td class="data" style="background:#EEEEEE; padding:10px; text-align:center;">₱' . number_format($row['total_sales'],2) . '</td>
                            <td class="data" style="background:#EEEEEE; padding:10px; text-align:center;">
                                <form method="POST" action="export_pdf.php" target="_blank" style="display:inline;">
                                    <input type="hidden" name="period" value="'.$row['period'].'">
                                    <input type="hidden" name="report_type" value="'.$report_type.'">
                                    <button type="submit" name="action" value="view" class="btn btn-primary" style="padding:2px; background:none; border:none;">
                                        <img src="view.png" alt="icon" style="width:25px; height:25px;">
                                    </button>
                                </form>
                                <form method="POST" action="export_pdf.php" style="display:inline;">
                                    <input type="hidden" name="period" value="'.$row['period'].'">
                                    <input type="hidden" name="report_type" value="'.$report_type.'">
                                    <button type="submit" name="action" value="download" class="btn btn-danger" style="padding:2px; background:none; border:none;">
                                        <img src="down.png" alt="icon" style="width:25px; height:25px;">
                                    </button>
                                </form>
                            </td>
                        </tr>';
                    }
                }
            } else {
                $colspan = ($report_type == 'perbranch') ? 3 : 2;
                $output .= '<tr><td colspan="'.$colspan.'" align="center">No records found</td></tr>';
            }

            // Total records
            $output .= '
            <tr>
                <td colspan="3" style="padding:2px; text-align:right;">
                    <font size="1">Total Number of Records: '.$totalRecords.'</font>
                </td>
            </tr>

            </table>
            </td></tr>
            </table>';

            echo $output;
            }





                if ($_POST["gastblsales"] == "DATE_DETAILS") {
                    $date = mysqli_real_escape_string($conn, $_POST["date"]);
                    $branchFilter = isset($_POST["branch"]) ? mysqli_real_escape_string($conn, $_POST["branch"]) : '';
                    $detailScope = isset($_POST["detail_scope"]) ? $_POST["detail_scope"] : 'branch';
                    $branchCondition = ($detailScope === 'branch' && $branchFilter !== '') ? " AND branch = '$branchFilter'" : '';

                    // New schema: show one row per fuel type (exactly like the DB table format)
                    $salesResult = mysqli_query($conn, "
                        SELECT id, branch, fuel_type, volume_sold, fuel_price, total_amount, netsales, oras, preparedby
                        FROM gas_sales_tbl
                        WHERE log_date = '$date' $branchCondition
                        ORDER BY branch ASC, oras DESC, FIELD(fuel_type,'Diesel','Premium','Unleaded')
                    ");

                    // Build sales details table with modern cards
                    $output = '
                    <style>
                        .ag-details-card { border: none; border-radius: 14px; box-shadow: 0 6px 16px rgba(0,0,0,0.12); overflow: hidden; margin-bottom: 14px; }
                        .ag-details-card .card-header { background: linear-gradient(90deg, #672222, #8c2f2f); color: #fff; font-weight: 700; padding: 10px 14px; }
                        .ag-details-card .card-body { padding: 12px 14px; background:#fff; }
                        .ag-details-table th, .ag-details-table td { vertical-align: middle; }
                        .ag-details-table thead { background: #f4f4f4; }
                        .ag-num { text-align: right; white-space: nowrap; }
                    </style>

                    <div class="ag-details-card">
                        <div class="card-header">Fuel Sales</div>
                        <div class="card-body">
                        <div class="table-responsive">
                        <table class="table table-sm table-striped table-bordered align-middle ag-details-table mb-0">
                            <thead>
                                <tr>
                                    <th style="text-align:center; white-space:nowrap;">Time</th>
                                    <th style="text-align:center; white-space:nowrap;">Fuel Type</th>
                                    <th style="text-align:center; white-space:nowrap;">Volume Sold (L)</th>
                                    <th style="text-align:center; white-space:nowrap;">Price (₱/L)</th>
                                    <th style="text-align:center; white-space:nowrap;">Total Amount</th>
                                    <th style="text-align:center; white-space:nowrap;">Action</th>
                                </tr>
                            </thead>
                            <tbody>';

                    $totalNet = 0;
                    if (mysqli_num_rows($salesResult) > 0) {
                        while ($row = mysqli_fetch_assoc($salesResult)) {
                            // Each DB row is already a single fuel type with its own totals
                            $totalNet += floatval($row['total_amount']);
                            $output .= '<tr>
                                <td style="text-align:center;">'.htmlspecialchars($row['oras']).'</td>
                                <td style="text-align:center;">'.htmlspecialchars($row['fuel_type']).'</td>
                                <td class="ag-num">'.number_format($row['volume_sold'], 2).'</td>
                                <td class="ag-num">₱'.number_format($row['fuel_price'], 2).'</td>
                                <td class="ag-num" style="font-weight:bold;">₱'.number_format($row['total_amount'], 2).'</td>
                                <td style="text-align:center;">
                                    <button type="button"
                                            class="btn btn-sm  sale-edit-btn"
                                            data-id="'.$row['id'].'"
                                            data-branch="'.htmlspecialchars($row['branch']).'"
                                            title="Edit sale">
                                        <img src="iconedit.png" alt="Edit" style="border:none; width:20px; height:20px; vertical-align:middle;">
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm  sale-delete-btn"
                                            data-id="'.$row['id'].'"
                                            data-branch="'.htmlspecialchars($row['branch']).'"
                                            title="Delete sale"
                                            style="margin-left:4px;">
                                        <img src="icondelete.png" alt="Delete" style="border:none; width:20px; height:20px; vertical-align:middle;">
                                    </button>
                                </td>
                            </tr>';
                        }
                    } else {
                        $output .= '<tr><td colspan="6" class="text-center">No sales recorded for this date.</td></tr>';
                    }

                    $output .= '</tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">Total Net Sales:</th>
                                <th class="ag-num">₱'.number_format($totalNet, 2).'</th>
                            </tr>
                        </tfoot>
                    </table>
                    </div>
                    </div>
                    </div>';

                    // Lubricant summary
                    $lubTotal = 0;
                    $lubResult = mysqli_query($conn, "
                        SELECT pname, SUM(quantity) AS total_qty, SUM(quantity * pprice) AS total_amount
                        FROM lub_sales_tbl
                        WHERE log_date = '$date' " . (($detailScope === 'branch' && $branchFilter !== '') ? "AND branch = '$branchFilter'" : "") . "
                        GROUP BY pname
                        ORDER BY pname ASC
                    ");

                    if (mysqli_num_rows($lubResult) > 0) {
                        $output .= '<div class="ag-details-card">
                        <div class="card-header">Lubricant Sales</div>
                        <div class="card-body">
                        <div class="table-responsive">
                        <table class="table table-sm table-striped table-bordered ag-details-table mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>';

                        while ($lub = mysqli_fetch_assoc($lubResult)) {
                            $lubTotal += floatval($lub['total_amount']);
                            $output .= '<tr>
                                <td>'.htmlspecialchars($lub['pname']).'</td>
                                <td class="ag-num">'.number_format($lub['total_qty'], 2).'</td>
                                <td class="ag-num">₱'.number_format($lub['total_amount'], 2).'</td>
                            </tr>';
                        }

                        $output .= '</tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-end">Lubricant Total</th>
                                    <th class="ag-num">₱'.number_format($lubTotal, 2).'</th>
                                </tr>
                            </tfoot>
                        </table>
                        </div></div></div>';
                    }

                    // Expenses summary
                    $expenseTotal = 0;
                    $expResult = mysqli_query($conn, "
                        SELECT expense_type, other_description, amount
                        FROM gas_expenses_tbl
                        WHERE log_date = '$date' " . (($detailScope === 'branch' && $branchFilter !== '') ? "AND branch = '$branchFilter'" : "") . "
                        ORDER BY expense_type ASC
                    ");

                    if (mysqli_num_rows($expResult) > 0) {
                        $output .= '<div class="ag-details-card">
                        <div class="card-header">Expenses</div>
                        <div class="card-body">
                        <div class="table-responsive">
                        <table class="table table-sm table-striped table-bordered ag-details-table mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>';

                        while ($exp = mysqli_fetch_assoc($expResult)) {
                            $expenseTotal += floatval($exp['amount']);
                            $output .= '<tr>
                                <td>'.htmlspecialchars($exp['expense_type']).'</td>
                                <td class="ag-num">₱'.number_format($exp['amount'], 2).'</td>
                                <td>'.htmlspecialchars($exp['other_description']).'</td>
                            </tr>';
                        }

                        $output .= '<tfoot>
                            <tr>
                                <th class="text-end">Total</th>
                                <th colspan="2" class="ag-num">₱'.number_format($expenseTotal, 2).'</th>
                            </tr>
                        </tfoot>';

                        $output .= '</tbody></table></div></div></div>';
                    }

                    // Totals card
                    $output .= '<div class="ag-details-card mb-0">
                        <div class="card-header">Totals</div>
                        <div class="card-body">
                            <table class="table table-sm table-striped table-bordered ag-details-table mb-0">
                                <tbody>
                                    <tr><th style="width:50%;">Fuel Total</th><td class="ag-num">₱'.number_format($totalNet, 2).'</td></tr>
                                    <tr><th>Lubricant Total</th><td class="ag-num">₱'.number_format($lubTotal, 2).'</td></tr>
                                    <tr><th>Expenses</th><td class="ag-num">₱'.number_format($expenseTotal, 2).'</td></tr>
                                    <tr><th>Net Sales</th><td class="ag-num fw-bold">₱'.number_format(($totalNet + $lubTotal) - $expenseTotal, 2).'</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>';

                    echo $output;
                    exit;
                }

                // --- Per-row Sales Details operations (independent edit/delete) ---
                if ($_POST["gastblsales"] == "DATE_DETAILS_SELECT_ROW") {
                    $id = intval($_POST["id"]);
                    $sql = "SELECT id, branch, log_date, oras, fuel_type, volume_sold, fuel_price, total_amount 
                            FROM gas_sales_tbl 
                            WHERE id='$id' LIMIT 1";
                    $res = mysqli_query($conn, $sql);
                    $row = $res ? mysqli_fetch_assoc($res) : null;
                    echo json_encode($row ?: []);
                    exit;
                }

                if ($_POST["gastblsales"] == "DATE_DETAILS_UPDATE_ROW") {
                    $id          = intval($_POST["id"]);
                    $newVolume   = isset($_POST["volume_sold"]) ? floatval($_POST["volume_sold"]) : 0;
                    $newPrice    = isset($_POST["fuel_price"]) ? floatval($_POST["fuel_price"]) : 0;

                    if ($id <= 0 || $newVolume <= 0 || $newPrice <= 0) {
                        echo "Invalid data.";
                        exit;
                    }

                    // Get existing row with date/time info
                    $oldRes = mysqli_query($conn, "SELECT branch, fuel_type, volume_sold, log_date, oras FROM gas_sales_tbl WHERE id='$id' LIMIT 1");
                    if (!$oldRes || !($old = mysqli_fetch_assoc($oldRes))) {
                        echo "Record not found.";
                        exit;
                    }

                    $branch    = $old["branch"];
                    $fuel_type = $old["fuel_type"];
                    $oldVolume = floatval($old["volume_sold"]);
                    $log_date  = $old["log_date"];
                    $oras      = $old["oras"];

                    // Get current stock before adjustment (this is the last_stock for transaction)
                    $lastStockQuery = "SELECT current_stock FROM fuel_inventory WHERE branch = '$branch' AND fuel_type = '$fuel_type'";
                    $lastStockResult = mysqli_query($conn, $lastStockQuery);
                    $lastStockRow = mysqli_fetch_assoc($lastStockResult);
                    $lastStock = $lastStockRow ? $lastStockRow['current_stock'] : 0;

                    // Adjust inventory based on change in volume (simple delta adjustment)
                    $delta = $newVolume - $oldVolume; // positive -> more sold (deduct more), negative -> less sold (add back)
                    if ($delta != 0) {
                        $invSql = "UPDATE fuel_inventory 
                                   SET current_stock = GREATEST(current_stock - $delta, 0)
                                   WHERE branch='$branch' AND fuel_type='$fuel_type'";
                        mysqli_query($conn, $invSql);
                    }

                    // Get current stock after adjustment (this is the available_stock for transaction)
                    $availableStockQuery = "SELECT current_stock FROM fuel_inventory WHERE branch = '$branch' AND fuel_type = '$fuel_type'";
                    $availableStockResult = mysqli_query($conn, $availableStockQuery);
                    $availableStockRow = mysqli_fetch_assoc($availableStockResult);
                    $availableStock = $availableStockRow ? $availableStockRow['current_stock'] : 0;

                    $newTotal = $newVolume * $newPrice;

                    $upd = mysqli_query($conn, "
                        UPDATE gas_sales_tbl 
                        SET volume_sold = $newVolume,
                            fuel_price  = $newPrice,
                            total_amount = $newTotal,
                            netsales    = $newTotal
                        WHERE id = '$id'
                    ");

                    if ($upd) {
                        // Update the corresponding transaction
                        // Match transaction by branch, fuel_type, action='SOLD', and approximate time/quantity
                        // Convert oras to time format for matching (handle both formats)
                        $timeMatch = date('H:i:s', strtotime($oras));
                        $dateTimeMatch = $log_date . ' ' . $timeMatch;
                        
                        // Find transaction created around the same time (within 5 minutes) with matching branch, fuel_type, and action
                        $transactionQuery = "
                            SELECT id FROM fuel_transactions 
                            WHERE branch = '$branch' 
                            AND fuel_type = '$fuel_type' 
                            AND action = 'SOLD' 
                            AND quantity = $oldVolume
                            AND DATE(created_at) = '$log_date'
                            ORDER BY ABS(TIMESTAMPDIFF(SECOND, created_at, '$dateTimeMatch')) ASC
                            LIMIT 1
                        ";
                        $transactionResult = mysqli_query($conn, $transactionQuery);
                        
                        if ($transactionResult && $transactionRow = mysqli_fetch_assoc($transactionResult)) {
                            $transactionId = $transactionRow['id'];
                            // Update transaction with new quantity and stock levels
                            mysqli_query($conn, "
                                UPDATE fuel_transactions 
                                SET quantity = $newVolume,
                                    last_stock = $lastStock,
                                    available_stock = $availableStock,
                                    created_at = created_at
                                WHERE id = $transactionId
                            ");
                        } else {
                            // If no matching transaction found, create a new one
                            mysqli_query($conn, "
                                INSERT INTO fuel_transactions(branch, fuel_type, action, quantity, last_stock, available_stock, created_at)
                                VALUES ('$branch','$fuel_type','SOLD',$newVolume, $lastStock, $availableStock, NOW())
                            ");
                        }
                        
                        echo "Sale row updated successfully.";
                    } else {
                        echo "Error updating sale row: " . mysqli_error($conn);
                    }
                    exit;
                }

                if ($_POST["gastblsales"] == "DATE_DETAILS_DELETE_ROW") {
                    $id = intval($_POST["id"]);
                    if ($id <= 0) {
                        echo "Invalid record.";
                        exit;
                    }

                    // Get sale record details before deleting
                    $oldRes = mysqli_query($conn, "SELECT branch, fuel_type, volume_sold, log_date, oras FROM gas_sales_tbl WHERE id='$id' LIMIT 1");
                    if (!$oldRes || !($old = mysqli_fetch_assoc($oldRes))) {
                        echo "Record not found.";
                        exit;
                    }

                    $branch    = $old["branch"];
                    $fuel_type = $old["fuel_type"];
                    $qty       = floatval($old["volume_sold"]);
                    $log_date  = $old["log_date"];
                    $oras      = $old["oras"];

                    // Get current stock before restoration (this is the last_stock for transaction)
                    $lastStockQuery = "SELECT current_stock FROM fuel_inventory WHERE branch = '$branch' AND fuel_type = '$fuel_type'";
                    $lastStockResult = mysqli_query($conn, $lastStockQuery);
                    $lastStockRow = mysqli_fetch_assoc($lastStockResult);
                    $lastStock = $lastStockRow ? $lastStockRow['current_stock'] : 0;

                    // Restore inventory from this row before deleting
                    if ($qty > 0) {
                        mysqli_query($conn, "
                            UPDATE fuel_inventory 
                            SET current_stock = current_stock + $qty
                            WHERE branch='$branch' AND fuel_type='$fuel_type'
                        ");
                    }

                    // Get current stock after restoration (this is the available_stock for transaction)
                    $availableStockQuery = "SELECT current_stock FROM fuel_inventory WHERE branch = '$branch' AND fuel_type = '$fuel_type'";
                    $availableStockResult = mysqli_query($conn, $availableStockQuery);
                    $availableStockRow = mysqli_fetch_assoc($availableStockResult);
                    $availableStock = $availableStockRow ? $availableStockRow['current_stock'] : 0;

                    // Find and delete the corresponding transaction
                    // Match transaction by branch, fuel_type, action='SOLD', and approximate time/quantity
                    $timeMatch = date('H:i:s', strtotime($oras));
                    $dateTimeMatch = $log_date . ' ' . $timeMatch;
                    
                    // Find transaction created around the same time with matching branch, fuel_type, action, and quantity
                    $transactionQuery = "
                        SELECT id FROM fuel_transactions 
                        WHERE branch = '$branch' 
                        AND fuel_type = '$fuel_type' 
                        AND action = 'SOLD' 
                        AND quantity = $qty
                        AND DATE(created_at) = '$log_date'
                        ORDER BY ABS(TIMESTAMPDIFF(SECOND, created_at, '$dateTimeMatch')) ASC
                        LIMIT 1
                    ";
                    $transactionResult = mysqli_query($conn, $transactionQuery);
                    
                    if ($transactionResult && $transactionRow = mysqli_fetch_assoc($transactionResult)) {
                        $transactionId = $transactionRow['id'];
                        // Delete the transaction
                        mysqli_query($conn, "DELETE FROM fuel_transactions WHERE id = $transactionId");
                    }

                    // Delete the sale record
                    $del = mysqli_query($conn, "DELETE FROM gas_sales_tbl WHERE id='$id'");
                    if ($del) {
                        echo "Sale row deleted successfully.";
                    } else {
                        echo "Error deleting sale row: " . mysqli_error($conn);
                    }
                    exit;
                }



                if ($_POST["gastblsales"] == "LOADLUBINV") {
                    $branch = isset($_POST['branch']) ? mysqli_real_escape_string($conn, $_POST['branch']) : '';
                    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
                    $searchEsc = mysqli_real_escape_string($conn, $search);

                    // Build WHERE conditions
                    $where = [];
                    if ($branch !== '') { $where[] = "branch='$branch'"; }
                    if ($searchEsc !== '') {
                        $where[] = "(pname LIKE '%$searchEsc%' OR UPPER(pname) LIKE UPPER('%$searchEsc%'))";
                    }
                    $whereSql = count($where) ? (' WHERE ' . implode(' AND ', $where)) : '';

                    $sql = "SELECT * FROM lub_inventory_tbl$whereSql ORDER BY inv_id DESC LIMIT 6";
                    $totalQuery = "SELECT COUNT(*) AS total FROM lub_inventory_tbl$whereSql";

                    $result = mysqli_query($conn, $sql);
                    $totalResult = mysqli_query($conn, $totalQuery);
                    $totalRow = mysqli_fetch_assoc($totalResult);
                    $totalRecords = $totalRow['total'];

                    $output = '
                    <table style="width: 100%; border-radius: 20px;">
                        <tr><td style="padding: 10px;">
                        <table style="width: 100%; border-collapse: collapse;" id="border">
                            <tr>
                                <td colspan="5" id="border" class="report-header">
                                    <div class="report-header-container">
                                        <span class="report-title">LUBRICANT INVENTORY ' . ($branch != '' ? strtoupper($branch) : '') . '</span>

                                        <div class="header-search">
                                            <input type="text" style="margin-right: 10px;" class="form-control" placeholder="Search here..." value="'.htmlspecialchars($search, ENT_QUOTES).'">
                                            <button class="btn-search">Search</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td id="border" style="width: 3%; background: #CCCCCC; padding: 8px;"><center></center></td>
                                <td id="border" style="width: 32%; background: #CCCCCC; padding: 8px;"><center><strong>PRODUCT NAME</strong></center></td>
                                <td id="border" style="width: 32%; background: #CCCCCC; padding: 8px;"><center><strong>AVAILABLE STOCK</strong></center></td>
                                <td id="border" style="width: 32%; background: #CCCCCC; padding: 8px;"><center><strong>LAST UPDATED</strong></center></td>
                            </tr>';

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_array($result)) {
                            $output .= '
                            <tr>
                                <td id="border" style="background: #EEEEEE; text-align:center; padding:10px;">
                                    <input type="checkbox" style="accent-color: maroon;" class="check_box" value="' . $row["inv_id"] . '">
                                </td>
                                <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . strtoupper($row["pname"]) . '</center></td>
                                <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . $row["available_stock"] . '</center></td>
                                <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . $row["last_updated"] . '</center></td>
                            </tr>';
                        }
                    } else {
                        $output .= '<tr><td colspan="3" align="center">No records found</td></tr>';
                    }

                    $output .= '
                            <tr>
                                <td colspan="3" style="padding: 2px;">
                                    <font size="1">Total Number of Records: ' . $totalRecords . '</font>
                                </td>
                            </tr>
                        </table>
                        </td></tr>
                    </table>';

                    echo $output;
                }




                
                if (isset($_POST["gastblsales"]) && $_POST["gastblsales"] == "LOADDAILYTRANS") {

                    $branch = isset($_POST['branch']) ? mysqli_real_escape_string($conn, $_POST['branch']) : '';
                    $priceExpression = "CAST(pprice AS DECIMAL(10,2))"; // safe arithmetic

                    if ($branch != '') {
                        // Filtered by branch
                        $sql = "
                            SELECT 
                                s.log_date,
                                s.pname,
                                SUM(IFNULL(s.qty_in, 0)) AS total_qty_in,
                                SUM(s.quantity) AS total_qty_out,
                                SUM(s.quantity * $priceExpression) AS total_amount,
                                IFNULL(i.available_stock,0) AS available_stock,
                                (IFNULL(i.available_stock,0) - SUM(IFNULL(s.qty_in,0)) + SUM(s.quantity)) AS last_stock
                            FROM lub_sales_tbl s
                            LEFT JOIN lub_inventory_tbl i 
                                ON s.pname = i.pname AND s.branch = i.branch
                            WHERE s.branch = '$branch'
                            GROUP BY s.log_date, s.pname
                            ORDER BY s.log_date DESC
                            LIMIT 10
                        ";

                        $totalQuery = "SELECT COUNT(DISTINCT log_date) AS total 
                                    FROM lub_sales_tbl 
                                    WHERE branch = '$branch'";

                    } else {
                        // All branches
                        $sql = "
                            SELECT 
                                s.log_date,
                                s.branch,
                                s.pname,
                                SUM(IFNULL(s.qty_in, 0)) AS total_qty_in,
                                SUM(s.quantity) AS total_qty_out,
                                SUM(s.quantity * $priceExpression) AS total_amount,
                                IFNULL(i.available_stock,0) AS available_stock,
                                (IFNULL(i.available_stock,0) - SUM(IFNULL(s.qty_in,0)) + SUM(s.quantity)) AS last_stock
                            FROM lub_sales_tbl s
                            LEFT JOIN lub_inventory_tbl i 
                                ON s.pname = i.pname AND s.branch = i.branch
                            GROUP BY s.log_date, s.branch, s.pname
                            ORDER BY s.log_date DESC
                            LIMIT 6
                        ";

                        $totalQuery = "SELECT COUNT(DISTINCT log_date) AS total 
                                    FROM lub_sales_tbl";
                    }

                    // Execute queries
                    $result = mysqli_query($conn, $sql) or die("Query failed: " . mysqli_error($conn));
                    $totalResult = mysqli_query($conn, $totalQuery) or die("Count query failed: " . mysqli_error($conn));
                    $totalRow = mysqli_fetch_assoc($totalResult);
                    $totalRecords = $totalRow['total'];

                    // Table header
                    $branchLabel = $branch != '' ? strtoupper($branch) : 'ALL BRANCHES';
                    $output = '
                    <table style="width: 100%; border-radius: 20px;">
                        <tr><td style="padding: 10px;">
                        <table style="width: 100%; border-collapse: collapse;" id="border">
                            <tr>
                                <td colspan="7" id="border" class="report-header">
                                    <div class="report-header-container">
                                        <span class="report-title">DAILY TRANSACTIONS ' . $branchLabel . '</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td id="border" style="width: 15%; background: #CCCCCC; padding: 8px;"><center><strong>DATE</strong></center></td>
                                <td id="border" style="width: 20%; background: #CCCCCC; padding: 8px;"><center><strong>PRODUCT</strong></center></td>
                                <td id="border" style="width: 15%; background: #CCCCCC; padding: 8px;"><center><strong>LAST STOCK</strong></center></td>
                                <td id="border" style="width: 12%; background: #CCCCCC; padding: 8px;"><center><strong>QTY IN</strong></center></td>
                                <td id="border" style="width: 12%; background: #CCCCCC; padding: 8px;"><center><strong>QTY OUT</strong></center></td>
                                <td id="border" style="width: 15%; background: #CCCCCC; padding: 8px;"><center><strong>AVAILABLE STOCK</strong></center></td>
                                <td id="border" style="width: 20%; background: #CCCCCC; padding: 8px;"><center><strong>TOTAL AMOUNT</strong></center></td>
                            </tr>';

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Apply formulas across all branches
                            $last_stock = (int)$row["last_stock"]; // already computed via SQL
                            $qty_in = (int)$row["total_qty_in"];
                            $qty_out = (int)$row["total_qty_out"];
                            $available_stock = $last_stock + $qty_in - $qty_out; // AVAILABLE STOCK

                            $output .= '
                            <tr>
                                <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . $row["log_date"] . '</center></td>
                                <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . strtoupper($row["pname"]) . '</center></td>
                                <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . $last_stock . '</center></td>
                                <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . $qty_in . '</center></td>
                                <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . $qty_out . '</center></td>
                                <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . $available_stock . '</center></td>
                                <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . number_format($row["total_amount"], 2) . '</center></td>
                            </tr>';
                        }
                    } else {
                        $output .= '<tr><td colspan="7" align="center">No records found</td></tr>';
                    }

                    $output .= '
                            <tr>
                                <td colspan="7" style="padding: 2px;">
                                    <font size="1">Total Number of Days: ' . $totalRecords . '</font>
                                </td>
                            </tr>
                        </table>
                        </td></tr>
                    </table>';

                    echo $output;
                    exit;
                }






                // SAVE: insert or update lubricant stock
                if ($_POST["gastblsales"] == "SAVELUB") {
                    $branch = mysqli_real_escape_string($conn, $_POST["branch"]);
                    $pname = mysqli_real_escape_string($conn, $_POST["pname"]);
                    $qty_in = isset($_POST["quantity"]) ? (int)$_POST["quantity"] : 0;
                    $price = isset($_POST["price"]) ? floatval($_POST["price"]) : 0;
                    $date = isset($_POST["date"]) ? $_POST["date"] : date('Y-m-d');

                    if ($qty_in <= 0) {
                        echo "Invalid quantity";
                        exit;
                    }

                    if ($price <= 0) {
                        echo "Invalid price";
                        exit;
                    }

                    // Check if product exists in this branch
                    $checkSql = "SELECT * FROM lub_inventory_tbl WHERE pname='$pname' AND branch='$branch'";
                    $checkResult = mysqli_query($conn, $checkSql);

                    if (mysqli_num_rows($checkResult) > 0) {
                        $row = mysqli_fetch_assoc($checkResult);
                        $new_qty_in = $row['qty_in'] + $qty_in;
                        $new_available_stock = $row['available_stock'] + $qty_in;

                        $updateSql = "UPDATE lub_inventory_tbl 
                                    SET qty_in='$new_qty_in', available_stock='$new_available_stock', last_updated=NOW() 
                                    WHERE inv_id=" . $row['inv_id'];
                        mysqli_query($conn, $updateSql);

                        echo "Stock has been updated for $pname in $branch.";
                    } else {
                        $insertSql = "INSERT INTO lub_inventory_tbl (pname, branch, qty_in, qty_out, available_stock) 
                                    VALUES ('$pname', '$branch', '$qty_in', 0, '$qty_in')";
                        mysqli_query($conn, $insertSql);

                        echo "New product $pname has been added to $branch stock.";
                    }

                    // Insert into gas_expenses_tbl as "Other" expense
                    $expenseSql = "INSERT INTO gas_expenses_tbl (log_date, branch, expense_type, other_description, amount) 
                                   VALUES ('$date', '$branch', 'Other', 'Lubricant Purchase: $pname', '$price')";
                    mysqli_query($conn, $expenseSql);

                    // Insert into lub_sales_tbl with qty_in for tracking
                    $lubSalesSql = "INSERT INTO lub_sales_tbl (log_date, oras, branch, pname, pprice, quantity, qty_in) 
                                    VALUES ('$date', NOW(), '$branch', '$pname', '$price', 0, '$qty_in')";
                    mysqli_query($conn, $lubSalesSql);

                    exit;
                }

                // SELECT: get a single lubricant record
                if ($_POST["gastblsales"] == "SELECTLUB") {
                    $id = mysqli_real_escape_string($conn, $_POST["id"]);
                    $sql = "SELECT * FROM lub_inventory_tbl WHERE inv_id='$id'";
                    $result = mysqli_query($conn, $sql);
                    $output = [];

                    if ($row = mysqli_fetch_assoc($result)) {
                        $output["inv_id"] = $row["inv_id"];
                        $output["pname"] = $row["pname"];
                        $output["branch"] = $row["branch"];
                        $output["qty_in"] = $row["qty_in"];
                        $output["qty_out"] = $row["qty_out"];
                        $output["available_stock"] = $row["available_stock"];
                        $output["last_updated"] = $row["last_updated"];
                    }

                    echo json_encode($output);
                    exit;
                }

                // UPDATE: overwrite a lubricant record
                if ($_POST["gastblsales"] == "UPDATELUB") {
                    $id = mysqli_real_escape_string($conn, $_POST["id"]);
                    $pname = mysqli_real_escape_string($conn, $_POST["pname"]);
                    $branch = mysqli_real_escape_string($conn, $_POST["branch"]);
                    $qty_in = isset($_POST["qty_in"]) ? (int)$_POST["qty_in"] : 0;
                    $qty_out = isset($_POST["qty_out"]) ? (int)$_POST["qty_out"] : 0;
                    $available_stock = $qty_in - $qty_out;

                    $sql = "UPDATE lub_inventory_tbl 
                            SET pname='$pname', branch='$branch', qty_in='$qty_in', qty_out='$qty_out', available_stock='$available_stock', last_updated=NOW() 
                            WHERE inv_id='$id'";
                    $execute = mysqli_query($conn, $sql);

                    if ($execute) {
                        echo "The lubricant record has been updated";
                    }
                    exit;
                }

                // DELETE: remove a lubricant record
                if ($_POST["gastblsales"] == "DELETELUB") {
                    $id = mysqli_real_escape_string($conn, $_POST["id"]);
                    $sql = "DELETE FROM lub_inventory_tbl WHERE inv_id='$id'";
                    $execute = mysqli_query($conn, $sql);

                    if ($execute) {
                        echo "The lubricant record has been deleted";
                    }
                    exit;
                }




                if ($_POST["gastblsales"] == "GET_LUB_PRODUCTS") {
                    $branch = isset($_POST['branch']) ? mysqli_real_escape_string($conn, $_POST['branch']) : '';

                    $sql = ($branch != '') 
                        ? "SELECT pname AS product_name, available_stock FROM lub_inventory_tbl WHERE branch='$branch'"
                        : "SELECT pname AS product_name, available_stock FROM lub_inventory_tbl";

                    $result = mysqli_query($conn, $sql);

                    $products = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        $products[] = [
                            'product_name'    => $row['product_name'],
                            'available_stock' => (int) $row['available_stock']
                        ];
                    }

                    echo json_encode($products);
                    exit;
                }




                 // ---- SAVE ----
                if ($_POST["gastblsales"] == "SAVE") {
                    $log_date  = $_POST["date"];
                    $log_time  = date("h:i:s A");
                    $branch    = $_POST["branch"];
                    $preparedby = isset($_POST["preparedby"]) ? mysqli_real_escape_string($conn, $_POST["preparedby"]) : '';

                    // Fuel volumes and prices from form
                    $dvs  = floatval($_POST["dvs"]);
                    $dvsp = floatval($_POST["dvsp"]);
                    $pvs  = floatval($_POST["pvs"]);
                    $pvsp = floatval($_POST["pvsp"]);
                    $uvs  = floatval($_POST["uvs"]);
                    $uvsp = floatval($_POST["uvsp"]);

                    // Per‑fuel totals
                    $dtotal = $dvs * $dvsp;
                    $ptotal = $pvs * $pvsp;
                    $utotal = $uvs * $uvsp;

                    // Overall fuel netsales for the day (fuel only; lubes/expenses handled in reporting)
                    $netsales_total = $dtotal + $ptotal + $utotal;

                    // Insert one row per fuel type into new gas_sales_tbl schema
                    $insertOk = true;

                    if ($dvs > 0) {
                        $sql = "INSERT INTO gas_sales_tbl
                                    (log_date, oras, branch, fuel_type, volume_sold, fuel_price, total_amount, netsales, preparedby)
                                VALUES
                                    ('$log_date', '$log_time', '$branch', 'Diesel', $dvs, $dvsp, $dtotal, $dtotal, '$preparedby')";
                        if (!mysqli_query($conn, $sql)) {
                            $insertOk = false;
                        }
                    }

                    if ($pvs > 0) {
                        $sql = "INSERT INTO gas_sales_tbl
                                    (log_date, oras, branch, fuel_type, volume_sold, fuel_price, total_amount, netsales, preparedby)
                                VALUES
                                    ('$log_date', '$log_time', '$branch', 'Premium', $pvs, $pvsp, $ptotal, $ptotal, '$preparedby')";
                        if (!mysqli_query($conn, $sql)) {
                            $insertOk = false;
                        }
                    }

                    if ($uvs > 0) {
                        $sql = "INSERT INTO gas_sales_tbl
                                    (log_date, oras, branch, fuel_type, volume_sold, fuel_price, total_amount, netsales, preparedby)
                                VALUES
                                    ('$log_date', '$log_time', '$branch', 'Unleaded', $uvs, $uvsp, $utotal, $utotal, '$preparedby')";
                        if (!mysqli_query($conn, $sql)) {
                            $insertOk = false;
                        }
                    }

                    if($insertOk){
                        // --- Lubricants ---
                        if(isset($_POST['pname']) && isset($_POST['pprice']) && isset($_POST['pqty'])){
                            $pname  = $_POST['pname'];
                            $pprice = $_POST['pprice'];
                            $pqty   = $_POST['pqty'];

                            for($i = 0; $i < count($pname); $i++){
                                $name  = mysqli_real_escape_string($conn, $pname[$i]);
                                $price = mysqli_real_escape_string($conn, $pprice[$i]);
                                $qty   = (int)$pqty[$i];

                                if(!empty($name) && !empty($price) && $qty > 0){
                                    mysqli_query($conn, "
                                        INSERT INTO lub_sales_tbl(log_date, oras, branch, pname, pprice, quantity)
                                        VALUES('$log_date','$log_time','$branch','$name','$price','$qty')
                                    ");

                                    mysqli_query($conn, "
                                        UPDATE lub_inventory_tbl
                                        SET qty_out = qty_out + $qty,
                                            available_stock = available_stock - $qty
                                        WHERE pname = '$name' AND branch = '$branch'
                                    ");
                                }
                            }
                        }

                        // --- Expenses ---
                        if(isset($_POST['expense_type']) && isset($_POST['expense_amount'])){
                            $expense_types   = $_POST['expense_type'];
                            $expense_amounts = $_POST['expense_amount'];
                            $other_expense   = $_POST['other_expense'];

                            for($i = 0; $i < count($expense_types); $i++){
                                $type   = mysqli_real_escape_string($conn, $expense_types[$i]);
                                $amount = floatval($expense_amounts[$i]);
                                $other  = !empty($other_expense[$i]) ? mysqli_real_escape_string($conn, $other_expense[$i]) : null;

                                if(!empty($type) && $amount > 0){
                                    mysqli_query($conn, "
                                        INSERT INTO gas_expenses_tbl(log_date, branch, expense_type, other_description, amount)
                                        VALUES('$log_date', '$branch', '$type', ".($other ? "'$other'" : "NULL").", $amount)
                                    ");
                                }
                            }
                        }

                        // --- Fuel inventory ---
                        $fuels = [
                            ["type" => "Diesel",   "qty" => $dvs],
                            ["type" => "Premium",  "qty" => $pvs],
                            ["type" => "Unleaded", "qty" => $uvs]
                        ];

                        foreach($fuels as $fuel){
                            if($fuel["qty"] > 0){
                                // Get current stock before deduction (this is the last_stock)
                                $lastStockQuery  = "SELECT current_stock FROM fuel_inventory WHERE branch = '$branch' AND fuel_type = '{$fuel['type']}'";
                                $lastStockResult = mysqli_query($conn, $lastStockQuery);
                                $lastStockRow    = mysqli_fetch_assoc($lastStockResult);
                                $lastStock       = $lastStockRow ? $lastStockRow['current_stock'] : 0;

                                mysqli_query($conn, "
                                    UPDATE fuel_inventory 
                                    SET current_stock = GREATEST(current_stock - {$fuel['qty']}, 0) 
                                    WHERE branch='$branch' AND fuel_type='{$fuel['type']}'
                                ");

                                // Get current stock after deduction (this is the available_stock = current_stock)
                                $availableStockQuery  = "SELECT current_stock FROM fuel_inventory WHERE branch = '$branch' AND fuel_type = '{$fuel['type']}'";
                                $availableStockResult = mysqli_query($conn, $availableStockQuery);
                                $availableStockRow    = mysqli_fetch_assoc($availableStockResult);
                                $availableStock       = $availableStockRow ? $availableStockRow['current_stock'] : 0;

                                mysqli_query($conn, "
                                    INSERT INTO fuel_transactions(branch, fuel_type, action, quantity, last_stock, available_stock, created_at)
                                    VALUES ('$branch','{$fuel['type']}','SOLD',{$fuel['qty']}, $lastStock, $availableStock, NOW())
                                ");
                            }
                        }

                        echo 'The record has been saved';
                    } else {
                        echo 'Error: ' . mysqli_error($conn);
                    }
                }

                // ---- SELECT ----
                if ($_POST["gastblsales"] == "SELECT") {
                    $id = intval($_POST["id"]);

                    // First, get the group key (branch + log_date) for this sale id
                    $baseRes = mysqli_query($conn, "SELECT branch, log_date FROM gas_sales_tbl WHERE id='$id' LIMIT 1");
                    $output = [];

                    if ($baseRes && $baseRow = mysqli_fetch_assoc($baseRes)) {
                        $branch   = $baseRow["branch"];
                        $log_date = $baseRow["log_date"];

                        // Aggregate per‑fuel volumes and totals for this branch/date
                        $sql = "
                            SELECT 
                                '$log_date' AS log_date,
                                '$branch'   AS branch,
                                SUM(CASE WHEN fuel_type='Diesel'   THEN volume_sold   ELSE 0 END) AS dvs,
                                SUM(CASE WHEN fuel_type='Diesel'   THEN fuel_price    ELSE 0 END) AS dvsp_dummy,
                                SUM(CASE WHEN fuel_type='Premium'  THEN volume_sold   ELSE 0 END) AS pvs,
                                SUM(CASE WHEN fuel_type='Premium'  THEN fuel_price    ELSE 0 END) AS pvsp_dummy,
                                SUM(CASE WHEN fuel_type='Unleaded' THEN volume_sold   ELSE 0 END) AS uvs,
                                SUM(CASE WHEN fuel_type='Unleaded' THEN fuel_price    ELSE 0 END) AS uvsp_dummy,
                                SUM(CASE WHEN fuel_type='Diesel'   THEN total_amount  ELSE 0 END) AS dtotal,
                                SUM(CASE WHEN fuel_type='Premium'  THEN total_amount  ELSE 0 END) AS ptotal,
                                SUM(CASE WHEN fuel_type='Unleaded' THEN total_amount  ELSE 0 END) AS utotal,
                                SUM(total_amount) AS netsales
                            FROM gas_sales_tbl
                            WHERE branch='$branch' AND log_date='$log_date'
                        ";
                        $result = mysqli_query($conn, $sql);

                        if ($result && $row = mysqli_fetch_assoc($result)) {
                            $output["id"]       = $id; // use clicked row id as reference
                            $output["log_date"] = $row["log_date"];
                            $output["branch"]   = $row["branch"];
                            $output["dvs"]      = $row["dvs"];
                            $output["pvs"]      = $row["pvs"];
                            $output["uvs"]      = $row["uvs"];
                            // Note: prices for editing will be taken from current fuel_prices via JS
                            $output["dtotal"]   = $row["dtotal"];
                            $output["ptotal"]   = $row["ptotal"];
                            $output["utotal"]   = $row["utotal"];
                            $output["netsales"] = $row["netsales"];

                            // --- Lubricants ---
                            $lub_sql    = "SELECT * FROM lub_sales_tbl WHERE branch='$branch' AND log_date='$log_date'";
                            $lub_result = mysqli_query($conn, $lub_sql);
                            $lub_array  = [];
                            while ($lub_row = mysqli_fetch_assoc($lub_result)) {
                                $lub_array[] = [
                                    "pname"  => $lub_row["pname"],
                                    "pprice" => $lub_row["pprice"],
                                    "qty_in" => $lub_row["quantity"]  
                                ];
                            }
                            $output["lubricants"] = $lub_array;

                            // --- Expenses ---
                            $exp_sql    = "SELECT * FROM gas_expenses_tbl WHERE branch='$branch' AND log_date='$log_date'";
                            $exp_result = mysqli_query($conn, $exp_sql);
                            $exp_array  = [];
                            while($exp_row = mysqli_fetch_assoc($exp_result)){
                                $exp_array[] = [
                                    "type"  => $exp_row["expense_type"],
                                    "desc"  => $exp_row["other_description"],
                                    "amount"=> $exp_row["amount"]
                                ];
                            }
                            $output["expenses"] = $exp_array;
                        }
                    }

                    echo json_encode($output);
                }

                // ---- UPDATE ----
                if ($_POST["gastblsales"] == "UPDATE") {
                    $id       = intval($_POST["id"]);
                    $log_date = $_POST["date"];
                    $log_time = date("h:i:s A");
                    $branch   = $_POST["branch"];

                    $dvs  = floatval($_POST["dvs"]);
                    $dvsp = floatval($_POST["dvsp"]);
                    $pvs  = floatval($_POST["pvs"]);
                    $pvsp = floatval($_POST["pvsp"]);
                    $uvs  = floatval($_POST["uvs"]);
                    $uvsp = floatval($_POST["uvsp"]);

                    $dtotal   = $dvs * $dvsp;
                    $ptotal   = $pvs * $pvsp;
                    $utotal   = $uvs * $uvsp;
                    $netsales = $dtotal + $ptotal + $utotal;

                    // Determine original branch/log_date for this logical sale group
                    $baseRes = mysqli_query($conn, "SELECT branch, log_date FROM gas_sales_tbl WHERE id='$id' LIMIT 1");
                    $origBranch = $branch;
                    $origDate   = $log_date;
                    if ($baseRes && $baseRow = mysqli_fetch_assoc($baseRes)) {
                        $origBranch = $baseRow['branch'];
                        $origDate   = $baseRow['log_date'];
                    }

                    // Get old per‑fuel quantities BEFORE updating (aggregate by fuel_type for that branch/date)
                    $old_dvs = 0;
                    $old_pvs = 0;
                    $old_uvs = 0;
                    $oldFuelRes = mysqli_query($conn, "
                        SELECT fuel_type, SUM(volume_sold) AS qty
                        FROM gas_sales_tbl
                        WHERE branch='$origBranch' AND log_date='$origDate'
                        GROUP BY fuel_type
                    ");
                    while ($rf = mysqli_fetch_assoc($oldFuelRes)) {
                        if ($rf['fuel_type'] === 'Diesel')   $old_dvs = floatval($rf['qty']);
                        if ($rf['fuel_type'] === 'Premium')  $old_pvs = floatval($rf['qty']);
                        if ($rf['fuel_type'] === 'Unleaded') $old_uvs = floatval($rf['qty']);
                    }

                    // Delete old per‑fuel rows for this logical sale
                    mysqli_query($conn, "DELETE FROM gas_sales_tbl WHERE branch='$origBranch' AND log_date='$origDate'");

                    // Insert new set of rows for the updated sale (per‑fuel)
                    $updateOk = true;

                    if ($dvs > 0) {
                        $sql = "INSERT INTO gas_sales_tbl
                                    (log_date, oras, branch, fuel_type, volume_sold, fuel_price, total_amount, netsales, preparedby)
                                VALUES
                                    ('$log_date', '$log_time', '$branch', 'Diesel', $dvs, $dvsp, $dtotal, $dtotal, '')";
                        if (!mysqli_query($conn, $sql)) $updateOk = false;
                    }
                    if ($pvs > 0) {
                        $sql = "INSERT INTO gas_sales_tbl
                                    (log_date, oras, branch, fuel_type, volume_sold, fuel_price, total_amount, netsales, preparedby)
                                VALUES
                                    ('$log_date', '$log_time', '$branch', 'Premium', $pvs, $pvsp, $ptotal, $ptotal, '')";
                        if (!mysqli_query($conn, $sql)) $updateOk = false;
                    }
                    if ($uvs > 0) {
                        $sql = "INSERT INTO gas_sales_tbl
                                    (log_date, oras, branch, fuel_type, volume_sold, fuel_price, total_amount, netsales, preparedby)
                                VALUES
                                    ('$log_date', '$log_time', '$branch', 'Unleaded', $uvs, $uvsp, $utotal, $utotal, '')";
                        if (!mysqli_query($conn, $sql)) $updateOk = false;
                    }

                    if ($updateOk) {
                        // --- Fuel: revert old inventory ---
                        // Restore old fuel quantities to inventory
                        if($old_dvs > 0){
                            $result = mysqli_query($conn, "
                                UPDATE fuel_inventory 
                                SET current_stock = current_stock + $old_dvs
                                WHERE branch='$origBranch' AND fuel_type='Diesel'
                            ");
                            if(!$result) {
                                error_log("Failed to restore Diesel inventory: " . mysqli_error($conn));
                            }
                        }
                        if($old_pvs > 0){
                            $result = mysqli_query($conn, "
                                UPDATE fuel_inventory 
                                SET current_stock = current_stock + $old_pvs
                                WHERE branch='$origBranch' AND fuel_type='Premium'
                            ");
                            if(!$result) {
                                error_log("Failed to restore Premium inventory: " . mysqli_error($conn));
                            }
                        }
                        if($old_uvs > 0){
                            $result = mysqli_query($conn, "
                                UPDATE fuel_inventory 
                                SET current_stock = current_stock + $old_uvs
                                WHERE branch='$origBranch' AND fuel_type='Unleaded'
                            ");
                            if(!$result) {
                                error_log("Failed to restore Unleaded inventory: " . mysqli_error($conn));
                            }
                        }

                        // --- Lubricants: revert old inventory ---
        // Restore old lubricant inventory before deleting old sales records
        $old_lub_sales_result = mysqli_query($conn, "SELECT pname, quantity FROM lub_sales_tbl WHERE branch='$branch' AND log_date='$log_date'");
        while($old_lub_sale = mysqli_fetch_assoc($old_lub_sales_result)) {
            $old_pname = mysqli_real_escape_string($conn, $old_lub_sale['pname']);
            $old_quantity = intval($old_lub_sale['quantity']);
            
            // Restore the old quantity back to available stock
            mysqli_query($conn, "
                UPDATE lub_inventory_tbl 
                SET available_stock = available_stock + $old_quantity,
                    qty_out = qty_out - $old_quantity
                WHERE pname='$old_pname' AND branch='$branch'
            ");
        }
        
        // Delete old lubs
        mysqli_query($conn, "DELETE FROM lub_sales_tbl WHERE branch='$branch' AND log_date='$log_date'");

        // Insert new lubricants
        if(isset($_POST['pname'])){
            $pname  = $_POST['pname'];
            $pprice = $_POST['pprice'];
            $pqty   = $_POST['pqty'];

            for($i=0;$i<count($pname);$i++){
                $name  = mysqli_real_escape_string($conn, $pname[$i]);
                $price = floatval($pprice[$i]);
                $qty   = floatval($pqty[$i]);

                if(!empty($name) && $qty>0){
                    mysqli_query($conn, "
                        INSERT INTO lub_sales_tbl(log_date, oras, branch, pname, pprice, quantity)
                        VALUES('$log_date','$log_time','$branch','$name','$price','$qty')
                    ");
                    mysqli_query($conn, "
                        UPDATE lub_inventory_tbl
                        SET qty_out = qty_out + $qty,
                            available_stock = available_stock - $qty
                        WHERE pname='$name' AND branch='$branch'
                    ");
                }
            }
        }

                        // --- Fuel: deduct new quantities from inventory ---
                        // First, clean up old fuel transaction logs for this sale
                        mysqli_query($conn, "DELETE FROM fuel_transactions WHERE branch='$branch' AND action='SOLD' AND DATE(created_at)='$log_date'");
                        
                        $fuels = [
                            ['type' => 'Diesel', 'qty' => $dvs],
                            ['type' => 'Premium', 'qty' => $pvs],
                            ['type' => 'Unleaded', 'qty' => $uvs]
                        ];

                        foreach($fuels as $fuel){
                            if($fuel["qty"] > 0){
                                // Get current stock before deduction (this is the last_stock)
                                $lastStockQuery = "SELECT current_stock FROM fuel_inventory WHERE branch = '$branch' AND fuel_type = '{$fuel['type']}'";
                                $lastStockResult = mysqli_query($conn, $lastStockQuery);
                                $lastStockRow = mysqli_fetch_assoc($lastStockResult);
                                $lastStock = $lastStockRow['current_stock'];

                                $result = mysqli_query($conn, "
                                    UPDATE fuel_inventory 
                                    SET current_stock = GREATEST(current_stock - {$fuel['qty']}, 0) 
                                    WHERE branch='$branch' AND fuel_type='{$fuel['type']}'
                                ");
                                if(!$result) {
                                    error_log("Failed to deduct {$fuel['type']} inventory: " . mysqli_error($conn));
                                }

                                // Get current stock after deduction (this is the available_stock = current_stock)
                                $availableStockQuery = "SELECT current_stock FROM fuel_inventory WHERE branch = '$branch' AND fuel_type = '{$fuel['type']}'";
                                $availableStockResult = mysqli_query($conn, $availableStockQuery);
                                $availableStockRow = mysqli_fetch_assoc($availableStockResult);
                                $availableStock = $availableStockRow['current_stock'];

                                $result2 = mysqli_query($conn, "
                                    INSERT INTO fuel_transactions(branch, fuel_type, action, quantity, last_stock, available_stock, created_at)
                                    VALUES ('$branch','{$fuel['type']}','SOLD',{$fuel['qty']}, $lastStock, $availableStock, NOW())
                                ");
                                if(!$result2) {
                                    error_log("Failed to insert {$fuel['type']} transaction: " . mysqli_error($conn));
                                }
                            }
                        }

                        // --- Expenses ---
                        mysqli_query($conn, "DELETE FROM gas_expenses_tbl WHERE branch='$branch' AND log_date='$log_date'");
                        if(isset($_POST['expense_type']) && isset($_POST['expense_amount'])){
                            $expense_types   = $_POST['expense_type'];
                            $expense_amounts = $_POST['expense_amount'];
                            $other_expense   = $_POST['other_expense'];

                            for($i = 0; $i < count($expense_types); $i++){
                                $type   = mysqli_real_escape_string($conn, $expense_types[$i]);
                                $amount = floatval($expense_amounts[$i]);
                                $other  = !empty($other_expense[$i]) ? mysqli_real_escape_string($conn, $other_expense[$i]) : null;

                                if(!empty($type) && $amount > 0){
                                    mysqli_query($conn, "
                                        INSERT INTO gas_expenses_tbl(log_date, branch, expense_type, other_description, amount)
                                        VALUES('$log_date', '$branch', '$type', ".($other ? "'$other'" : "NULL").", $amount)
                                    ");
                                }
                            }
                        }

                        echo 'The record has been updated. Old fuel restored and new fuel deducted from inventory.';
                    } else {
                        echo 'Error updating gas sale: ' . mysqli_error($conn);
                    }
                }

                // ---- DELETE ----
                if ($_POST["gastblsales"] == "DELETE") {
                    $id = intval($_POST["id"]);

                    // Determine logical sale group (branch + log_date) from clicked row
                    $baseRes = mysqli_query($conn,"SELECT branch, log_date FROM gas_sales_tbl WHERE id='$id' LIMIT 1");
                    if($baseRes && $baseRow = mysqli_fetch_assoc($baseRes)){
                        $branch   = $baseRow['branch'];
                        $log_date = $baseRow['log_date'];

                        // Aggregate per‑fuel quantities for this branch/date
                        $dvs = 0; $pvs = 0; $uvs = 0;
                        $fuelRes = mysqli_query($conn, "
                            SELECT fuel_type, SUM(volume_sold) AS qty
                            FROM gas_sales_tbl
                            WHERE branch='$branch' AND log_date='$log_date'
                            GROUP BY fuel_type
                        ");
                        while ($fr = mysqli_fetch_assoc($fuelRes)) {
                            if ($fr['fuel_type'] === 'Diesel')   $dvs = floatval($fr['qty']);
                            if ($fr['fuel_type'] === 'Premium')  $pvs = floatval($fr['qty']);
                            if ($fr['fuel_type'] === 'Unleaded') $uvs = floatval($fr['qty']);
                        }

                        // Restore fuel inventory
                        if($dvs > 0){
                            mysqli_query($conn, "
                                UPDATE fuel_inventory 
                                SET current_stock = current_stock + $dvs
                                WHERE branch='$branch' AND fuel_type='Diesel'
                            ");
                        }
                        if($pvs > 0){
                            mysqli_query($conn, "
                                UPDATE fuel_inventory 
                                SET current_stock = current_stock + $pvs
                                WHERE branch='$branch' AND fuel_type='Premium'
                            ");
                        }
                        if($uvs > 0){
                            mysqli_query($conn, "
                                UPDATE fuel_inventory 
                                SET current_stock = current_stock + $uvs
                                WHERE branch='$branch' AND fuel_type='Unleaded'
                            ");
                        }

                        // Delete fuel transaction logs
                        mysqli_query($conn,"DELETE FROM fuel_transactions WHERE branch='$branch' AND action='SOLD' AND DATE(created_at)='$log_date'");

                        // Restore lubricant inventory before deleting sales records
                        $lub_sales_result = mysqli_query($conn, "SELECT pname, quantity FROM lub_sales_tbl WHERE branch='$branch' AND log_date='$log_date'");
                        while($lub_sale = mysqli_fetch_assoc($lub_sales_result)) {
                            $pname = mysqli_real_escape_string($conn, $lub_sale['pname']);
                            $quantity = intval($lub_sale['quantity']);
                            
                            // Restore the quantity back to available stock
                            mysqli_query($conn, "
                                UPDATE lub_inventory_tbl 
                                SET available_stock = available_stock + $quantity,
                                    qty_out = qty_out - $quantity
                                WHERE pname='$pname' AND branch='$branch'
                            ");
                        }
                        
                        // Delete lubricants
                        mysqli_query($conn,"DELETE FROM lub_sales_tbl WHERE branch='$branch' AND log_date='$log_date'");

                        // Delete expenses
                        mysqli_query($conn,"DELETE FROM gas_expenses_tbl WHERE branch='$branch' AND log_date='$log_date'");

                        // Delete all gas sale rows for this logical sale (branch + date)
                        $str = "DELETE FROM gas_sales_tbl WHERE branch='$branch' AND log_date='$log_date'";
                        $execute = mysqli_query($conn, $str);

                        if ($execute) {
                            echo 'The record and corresponding fuel inventory, lubricant sales, and expenses have been restored/deleted';
                        } else {
                            echo 'Error deleting gas sales: ' . mysqli_error($conn);
                        }
                    } else {
                        echo 'Record not found';
                    }
                }

                  
    }











    if (isset($_POST["leasetbl"])) {
        if ($_POST["leasetbl"] == "LOADLEASEREC") {
             // Get current month/year
          $current_month = date("n");
            $current_year  = date("Y");

         $sql = "
            SELECT l.tenant_id, l.tname, l.ddate,
                CASE 
                    WHEN EXISTS (
                    SELECT 1 
                    FROM lease_pay_tbl p 
                    WHERE p.tenant_id = l.tenant_id
                        AND YEAR(p.pdate) = YEAR(CURDATE())
                        AND MONTH(p.pdate) = MONTH(CURDATE())
                    )
                    THEN 1 ELSE 0
                END AS is_paid,
                DATE_ADD(l.ddate, INTERVAL (
                    SELECT COUNT(*) FROM lease_pay_tbl p2 WHERE p2.tenant_id = l.tenant_id
                ) MONTH) AS next_due
            FROM lease_tbl l
        ";

               



            
            $result = mysqli_query($conn, $sql);
            $ctr = 0;
            $output = "";

            $output .= '
            <style>
                .report-table tr:hover:not(.no-hover) td {
                    background: #814c4c81 !important;
                    cursor: pointer;
                    transition: all 0.3s ease;
                }
                .lease-pdf-btn img { 
                    transition: transform 0.2s ease, filter 0.2s ease; 
                    filter: invert(1) brightness(2) contrast(200%);
                }
                .lease-pdf-btn img:hover { 
                    transform: scale(1.15) translateY(-2px); 
                    filter: invert(1) brightness(2.5) contrast(220%);
                }
            </style>
            <table id="tb"  style="width: 100%;  border-radius: 20px;">
                <tr><td style="padding: 10px;">
            <table style="width: 100%; border-collapse: collapse;" class="report-table" id="border">

            <tr class="no-hover">


            <td colspan="5" id="border" class="report-header">
                <div class="report-header-container">
                    <!-- Left side -->
                    <span class="report-title">
                        COMMERCIAL LEASE MONITOR
                    </span>

                    
                    <div style="margin-left:auto;">
                        <form method="POST" action="lease_export_pdf.php" target="_blank" style="display:inline;">
                            <button title="View" type="submit" name="action" value="view" class="lease-pdf-btn" style="padding:2px; background:none; border:none;">
                                <img src="view.png" alt="view" style="width:25px; height:25px;">
                            </button>
                        </form>
                        <form method="POST" action="lease_export_pdf.php" style="display:inline; margin-left:5px;">
                            <button title="Download" type="submit" name="action" value="download" class="lease-pdf-btn" style="padding:2px; background:none; border:none;">
                                <img src="down.png" alt="download" style="width:25px; height:25px;">
                            </button>
                        </form>
                    </div>
                </div>
                
                    
                
                
            </td>

            <tr class="no-hover">
            <td id="border" style="width: 4%; background: #CCCCCC; padding: 5px; vertical-align: top;"></td>
            <td id="border" style="width: 30%; background: #CCCCCC; padding: 5px; vertical-align: middle;"><center><strong>TENANT NAME</strong></center></td>
            <td id="border" style="width: 30%; background: #CCCCCC; padding: 5px; vertical-align: middle;"><center><strong>DUE DATE</strong></center></td>
            <td id="border" style="width: 30%; background: #CCCCCC; padding: 5px; vertical-align: middle;"><center><strong>STATUS</strong></center></td>
            <td id="border" style="width: 6%; background: #CCCCCC; padding: 5px; vertical-align: top;"></td>
            ';

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            

                            if ($row["is_paid"] == 1) {
                                $status = '<span style="color: green;">PAID</span>';
                            } else {
                                $status = '<span style="color: red;">Not PAID</span>';
                            }

                            // Calculate auto-updating due date
                            $due_date = date("M d, Y", strtotime($row["next_due"]));

                            $output .= '
            <tr>
                <td id="border" style="background: #EEEEEE;"><center><input type="checkbox" style="accent-color: maroon;" class="check_box" value="' . $row["tenant_id"] . '"></center></td>
                <td id="border" style="background: #EEEEEE;"><center>' . strtoupper($row["tname"]) . '</center></td>
                <td id="border" style="background: #EEEEEE;"><center>' . strtoupper($due_date) . '</center></td>
                <td id="border" style="background: #EEEEEE;"><center>' . $status . '</center></td>
                <td id="border" style="background: #EEEEEE;"><center>
                    
                    <button style="padding:2px; background:none; border:none;" class="btn btn-sm btn-primary view-history" data-id="' . $row["tenant_id"] . '"> 
                        <img src="eye.png" alt="icon" id="eye" style="width:25px; height:25px; vertical-align:middle;">
                    </button>
                </center></td>
            </tr>';
                            $ctr++;
                        }
                    } else {
                        $output .= '<tr><td colspan="5" align="center">No records found</td></tr>';
                    }

                    $output .= '<tr><td colspan="5" style="padding: 2px;"><font size="1">Total Number of Records: ' . $ctr . '</font></td></tr>
            </table>
            </td></tr>
            </table>';

            echo $output;
        }   
        
                // ------------------- SAVE TENANT -------------------
    if ($_POST["leasetbl"] == "SAVE") {
        date_default_timezone_set("Asia/Manila");

        $tname      = trim($_POST["tname"]);
        $rental_fee = floatval($_POST["rental_fee"]);
        $contact    = trim($_POST["contact"]);
        $ddate      = $_POST["date"];
        $status     = $_POST["status"];

        // Validation
        if (!$tname || $rental_fee <= 0 || !$contact || !$ddate || $status === "") {
            echo "Please fill all required fields correctly.";
            exit;
        }

        // Check for duplicates
        $check = mysqli_query($conn, "SELECT * FROM lease_tbl WHERE tname='$tname' AND ddate='$ddate'");
        if (mysqli_num_rows($check) > 0) {
            echo "Tenant already added for this date.";
            exit;
        }

        // Insert tenant
        $insertTenant = mysqli_query($conn, "
            INSERT INTO lease_tbl(tname, rent_fee, contact, tstatus, ddate)
            VALUES ('$tname','$rental_fee','$contact','$status','$ddate')
        ");

        echo $insertTenant ? "Tenant successfully added." : "Database insert failed: " . mysqli_error($conn);
        exit;
    }

    // ------------------- SELECT TENANT -------------------
    if ($_POST["leasetbl"] == "SELECT") {
        $tenant_id = intval($_POST["id"]);

        $sql = "SELECT * FROM lease_tbl WHERE tenant_id='$tenant_id'";
        $res = mysqli_query($conn, $sql);
        $output = [];

        if ($row = mysqli_fetch_assoc($res)) {
            $output = [
                "status"     => "success",
                "id"         => $row["tenant_id"],
                "tname"      => $row["tname"],
                "rent_fee"   => $row["rent_fee"],
                "contact"    => $row["contact"],
                "tstatus"    => $row["tstatus"],
                "ddate"      => $row["ddate"]
            ];
        } else {
            $output = ["status" => "error", "message" => "Tenant record not found."];
        }

        echo json_encode($output);
        exit;
    }

    // ------------------- UPDATE TENANT -------------------
    if ($_POST["leasetbl"] == "UPDATE") {
        $tenant_id  = intval($_POST["id"]);
        $tname      = trim($_POST["tname"]);
        $rental_fee = floatval($_POST["rental_fee"]);
        $contact    = trim($_POST["contact"]);
        $status     = $_POST["status"];
        $ddate      = $_POST["date"];

        // Validation
        if (!$tname || $rental_fee <= 0 || !$contact || !$ddate || $status === "") {
            echo "Please fill all required fields correctly.";
            exit;
        }

        // Check duplicates excluding current record
        $check = mysqli_query($conn, "SELECT * FROM lease_tbl WHERE tname='$tname' AND ddate='$ddate' AND tenant_id != '$tenant_id'");
        if (mysqli_num_rows($check) > 0) {
            echo "Tenant already added for this date.";
            exit;
        }

        // Update query
        $str = "UPDATE lease_tbl SET
                    tname='$tname',
                    rent_fee='$rental_fee',
                    contact='$contact',
                    tstatus='$status',
                    ddate='$ddate'
                WHERE tenant_id='$tenant_id'";
        $execute = mysqli_query($conn, $str);

        echo $execute ? "The tenant record has been updated." : "Update failed: " . mysqli_error($conn);
        exit;
    }



                if ($_POST["leasetbl"] == "DELETE") {
                    if (!empty($_POST["ids"]) && is_array($_POST["ids"])) {
                        $tenant_ids = array_map('intval', $_POST["ids"]); // sanitize
                        $id_list = implode(",", $tenant_ids);

                        $str = "DELETE FROM lease_tbl WHERE tenant_id IN ($id_list)";
                        $execute = mysqli_query($conn, $str);

                        if ($execute) {
                            echo "Selected tenant(s) deleted successfully.";
                        } else {
                            echo "Error deleting tenants: " . mysqli_error($conn);
                        }
                    } else {
                        echo "No tenants selected for deletion.";
                    }
                }




                if ($_POST["leasetbl"] == "HISTORY") {
                    $tenant_id = $_POST["tenant_id"];

                    // Detect if compensation column exists
                    $hasComp = false;
                    $colRes = mysqli_query($conn, "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lease_pay_tbl' AND COLUMN_NAME = 'compensation'");
                    if ($colRes && mysqli_num_rows($colRes) > 0) { $hasComp = true; }

                    $sql = "SELECT pdate, pay_month, pay_year, payment" . ($hasComp ? ", compensation" : "") . "
                            FROM lease_pay_tbl 
                            WHERE tenant_id = '$tenant_id'
                            ORDER BY pdate DESC";
                    $result = mysqli_query($conn, $sql);

                    $output = '<table class="table table-bordered">
                        <tr>
                            <th>Date Paid</th>
                            <th>Month</th>
                            <th>Year</th>
                            <th>Amount</th>
                        </tr>';
                    if (!$result) {
                        die("SQL Error in HISTORY action: " . mysqli_error($conn) . " | Query: " . $sql);
                    }
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $comp = ($hasComp && isset($row['compensation'])) ? floatval($row['compensation']) : 0;
                            $total = floatval($row['payment']) + $comp;
                            $output .= "<tr>
                                <td>" . date("M d, Y", strtotime($row['pdate'])) . "</td>
                                <td>" . $row['pay_month'] . "</td>
                                <td>" . $row['pay_year'] . "</td>
                                <td>" . number_format($total, 2) . "</td>
                            </tr>";
                        }
                    } else {
                        $output .= "<tr><td colspan='4'>No payment history found.</td></tr>";
                    }

                    $output .= "</table>";

                    echo $output;
                }

                // ------------------- ADD PAYMENT (Admin) -------------------
                if ($_POST["leasetbl"] == "ADD_PAYMENT") {
                    $tenant_id   = intval($_POST["tenant_id"]);
                    $payment     = floatval($_POST["payment"]);
                    $compensation= isset($_POST["compensation"]) ? floatval($_POST["compensation"]) : 0;
                    $pdate       = $_POST["pdate"];
                    $logtime     = date("H:i:s");
                    $month       = date("n");
                    $year        = date("Y");

                    if ($tenant_id <= 0 || $payment <= 0 || empty($pdate)) {
                        echo "Please fill all required fields correctly.";
                        exit;
                    }

                    // Ensure compensation column exists (optional auto-add)
                    $colRes = mysqli_query($conn, "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lease_pay_tbl' AND COLUMN_NAME = 'compensation'");
                    if ($colRes && mysqli_num_rows($colRes) == 0) {
                        @mysqli_query($conn, "ALTER TABLE lease_pay_tbl ADD COLUMN compensation DECIMAL(12,2) NOT NULL DEFAULT 0.00");
                    }

                    $dup = mysqli_query($conn, "SELECT 1 FROM lease_pay_tbl WHERE tenant_id='$tenant_id' AND pay_month='$month' AND pay_year='$year'");
                    if ($dup && mysqli_num_rows($dup) > 0) {
                        echo "Payment for this month already exists for this tenant.";
                        exit;
                    }

                    $sql = "INSERT INTO lease_pay_tbl (tenant_id, payment, compensation, pdate, logtime, pay_month, pay_year)
                            VALUES ('$tenant_id', '$payment', '$compensation', '$pdate', '$logtime', '$month', '$year')";
                    if (mysqli_query($conn, $sql)) {
                        echo "Payment successfully added for this month.";
                    } else {
                        echo "Error: " . mysqli_error($conn);
                    }
                    exit;
                }

                // ------------------- TENANT LIST (for Add Payment select) -------------------
                if ($_POST["leasetbl"] == "TENANT_LIST") {
                    $tenants = [];
                    $res = mysqli_query($conn, "SELECT tenant_id, tname FROM lease_tbl ORDER BY tname ASC");
                    if ($res) {
                        while ($row = mysqli_fetch_assoc($res)) {
                            $tenants[] = $row;
                        }
                    }
                    echo json_encode($tenants);
                    exit;
                }
        
    }
    


    









    if (isset($_POST["brotbl"])) {

        // ---------- LOAD INVENTORY ----------
        if ($_POST["brotbl"] == "LOADBROINV") {

            // Get the selected label from AJAX POST
            $selectedLabel = isset($_POST['label_filter']) ? $_POST['label_filter'] : '';

            // Get distinct labels for the dropdown
            $labelQuery = "SELECT DISTINCT label FROM bros_inventory ORDER BY label ASC";
            $labelResult = mysqli_query($conn, $labelQuery);
            if (!$labelResult) { die("Label Query failed: " . mysqli_error($conn)); }

            // Fetch inventory filtered by label if selected
            $sql = "SELECT id, product_name, classification, unit_price, available_stock, label
                    FROM bros_inventory";

            if (!empty($selectedLabel)) {
                $sql .= " WHERE label = '" . mysqli_real_escape_string($conn, $selectedLabel) . "'";
            }
            $sql .= " ORDER BY product_name ASC";
            $result = mysqli_query($conn, $sql);
            if (!$result) { die("Inventory Query failed: " . mysqli_error($conn)); }

            // Total records (optional)
            $totalQuery = "SELECT COUNT(*) AS total FROM bros_inventory";
            $totalResult = mysqli_query($conn, $totalQuery);
            $totalRow = mysqli_fetch_assoc($totalResult);
            $totalRecords = $totalRow['total'];

            $output = '';
            $output .= '
            <table style="width: 100%; border-radius: 20px;">
                <tr><td style="padding: 10px;">
                <table class="border-table" style="width: 100%; border-collapse: collapse;" id="border">

                <tr>
                    <td colspan="9" id="border" class="report-header">
                        <div class="report-header-container">
                            <span class="report-title">BROS INASAL INVENTORY</span>
                            <div class="header-search">';

        
            $output .= '<select id="label_filter" class="form-control">
                            <option style="background-color:#672222;" value="">-- Select Label --</option>';
            while ($labelRow = mysqli_fetch_assoc($labelResult)) {
                $selected = ($labelRow['label'] == $selectedLabel) ? "selected" : "";
                $output .= '<option style="background-color:#672222;" value="' . $labelRow['label'] . '" ' . $selected . '>' . strtoupper($labelRow['label']) . '</option>';
            }
            $output .= '</select>';

            $output .= '</div></div></td></tr>

                <tr>
                    <td id="border" style="width: 2%; background: #CCCCCC; padding: 8px;"></td>
                    <td id="border" style="width: 20%; background: #CCCCCC; padding: 8px;"><center><strong>PRODUCT</strong></center></td>
                    <td id="border" style="width: 5%; background: #CCCCCC; padding: 8px;"><center><strong>CLASSIFICATION</strong></center></td>
                    <td id="border" style="width: 10%; background: #CCCCCC; padding: 8px;"><center><strong>AVAILABLE STOCK</strong></center></td>
                    <td id="border" style="width: 15%; background: #CCCCCC; padding: 8px;"><center><strong>STATUS</strong></center></td>
                    <td id="border" style="width: 6%; background: #CCCCCC; padding: 8px;"></td>
                </tr>';

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                    $status = ($row["available_stock"] <= 10) ? "<span style='color:red;font-weight:bold;'>NEED TO RESTOCK</span>" : "<span style='color:green;'>OK</span>";
                    $rowClass = ($row["available_stock"] <= 10) ? "low-stock" : "";
                    $output .= '
                    <tr class="'.$rowClass.'">
                        <td id="border" style="background: #EEEEEE; text-align:center; padding:10px;">
                            <input type="checkbox" style="accent-color: maroon;" class="check_box" value="' . $row["id"] . '">
                        </td>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . strtoupper($row["product_name"]) . '</center></td>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . strtoupper($row["classification"]) . '</center></td>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . intval($row["available_stock"]) . '</center></td>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . $status . '</center></td>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center><button id="stockbtn" class="btn-reset" style="color:white; background: linear-gradient(90deg, #672222, #8c2f2f);">ADD STOCK</button></center></td>
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
        }

   
       // ---------- LOAD DAILY REPORT ----------
if ($_POST["brotbl"] == "LOADBRODAILY") {

    // Check for date filters
    $from_date = isset($_POST['from_date']) && $_POST['from_date'] != '' ? $_POST['from_date'] : null;
    $to_date   = isset($_POST['to_date']) && $_POST['to_date'] != '' ? $_POST['to_date'] : null;

    if ($from_date && $to_date) {
        $sql = "SELECT * 
                FROM bros_dailyreport_tbl 
                WHERE report_date BETWEEN '$from_date' AND '$to_date'
                ORDER BY report_date DESC";

        $totalQuery = "SELECT COUNT(*) AS total 
                       FROM bros_dailyreport_tbl 
                       WHERE report_date BETWEEN '$from_date' AND '$to_date'";
    } else {
        $sql = "SELECT * 
                FROM bros_dailyreport_tbl 
                ORDER BY report_date DESC
                LIMIT 7";

        $totalQuery = "SELECT COUNT(*) AS total FROM bros_dailyreport_tbl";
    }

    $result = mysqli_query($conn, $sql);
    $totalResult = mysqli_query($conn, $totalQuery);
    $totalRow = mysqli_fetch_assoc($totalResult);
    $totalRecords = $totalRow['total'];

    $output = "";

    $output .= '
    <table style="width: 100%; border-radius: 20px;">
        <tr><td style="padding: 10px;">
        <table style="width: 100%; border-collapse: collapse;" id="border">

        <tr>
            <td colspan="6" id="border" class="report-header">
                <div class="report-header-container">
                    <!-- Left side -->
                    <span class="report-title">BROS DAILY REPORTS</span>

                    <!-- Right side: date filter -->
                    <div class="date-filter">
                        <label for="from_date">From:</label>
                        <div class="header-search-filter" style="height:35px;">
                            <input type="date" id="from_date" class="date-input">
                        </div>
                        <label for="to_date">To:</label>
                        <div class="header-search-filter" style="height:35px;">
                            <input type="date" id="to_date" class="date-input">
                        </div>
                        
                        <button id="filterBtn" class="btn-filter" 
                            style="color:white; background: linear-gradient(90deg, #672222, #8c2f2f);">Filter</button>
                        <button id="resetBtn" class="btn-reset" 
                            style="color:white; background: linear-gradient(90deg, #672222, #8c2f2f);">Reset</button>
                    </div>
                </div>
            </td>
        </tr>

        <tr>
            <td id="border" style="width: 5%; background: #CCCCCC; padding: 8px;"></td>
            <td id="border" style="width: 15%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>DATE</strong></td>
            <td id="border" style="width: 20%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>GROSS SALES</strong></td>
            <td id="border" style="width: 20%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>EXPENSES</strong></td>
            <td id="border" style="width: 25%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>NET SALES</strong></td>
            <td id="border" style="width: 15%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>ACTION</strong></td>
        </tr>';

    if (mysqli_num_rows($result) > 0) {
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
                        <form method="POST" action="bropdf.php" target="_blank" style="display:inline;">
                            <input type="hidden" name="row_id" value="'.$row['id'].'">
                            <button type="submit" name="action" value="view" class="btn btn-primary" style="padding:2px; background:none; border:none;">
                                <img src="view.png" alt="icon" style="width:25px; height:25px;">
                            </button>
                        </form>

                        <form method="POST" action="bropdf.php" style="display:inline;">
                            <button type="submit" name="action" value="download" class="btn btn-danger" style="padding:2px; background:none; border:none;">
                                <img src="down.png" alt="icon" style="width:25px; height:25px;">
                            </button>
                        </form>
                    </div>
                </td>
            </tr>';
        }
    } else {
        $output .= '<tr><td colspan="5" align="center">No daily reports found</td></tr>';
    }

    $output .= '
        <tr><td colspan="5" style="padding: 2px;">
            <font size="1">Total Records: ' . $totalRecords . '</font>
        </td></tr>
        </table>
        </td></tr>
    </table>';

    echo $output;
}


       if ($_POST["brotbl"] == "SAVE_DAILY") {

    // Collect Daily Report Fields
    $report_date     = $_POST["report_date"];
    $cash_on_counter = $_POST["cash_on_counter"] ?: 0;
    $cash_in         = $_POST["cash_in"] ?: 0;
    $gcash_sales     = $_POST["gcash_sales"] ?: 0;
    $credit_sales    = $_POST["credit_sales"] ?: 0;
    $expenses_arr    = json_decode($_POST["expenses"], true);
    $cash_arr        = json_decode($_POST["cash_breakdown"], true);

    // ---------- Compute Sales ----------
    $total_cash_sales    = $cash_on_counter + $cash_in;
    $gross_sales         = $total_cash_sales + $gcash_sales + $credit_sales;
    $total_expense       = 0;

    foreach ($expenses_arr as $exp) {
        $total_expense += floatval($exp['amount']);
    }

    $net_sales           = $gross_sales - $total_expense;
    $total_sales_counter = $total_cash_sales + $gcash_sales;
    $over_short          = $cash_on_counter - $total_sales_counter;

    // ---------- Insert into bros_dailyreport_tbl ----------
    $insertDaily = mysqli_query($conn, "
        INSERT INTO bros_dailyreport_tbl 
        (report_date, cash_on_counter, cash_in, total_cash_sales, gcash_sales, credit_sales, gross_sales, expenses, net_sales, total_sales_counter, over_short)
        VALUES 
        ('$report_date','$cash_on_counter','$cash_in','$total_cash_sales','$gcash_sales','$credit_sales','$gross_sales','$total_expense','$net_sales','$total_sales_counter','$over_short')
    ");

    if ($insertDaily) {
        $daily_id = mysqli_insert_id($conn); // get inserted daily report ID

        // ---------- Insert Expenses ----------
        foreach ($expenses_arr as $exp) {
            $type  = $exp['type'];
            $other = $exp['other'];
            $amt   = floatval($exp['amount']);
            $final_type = ($type == "Others" && !empty($other)) ? $other : $type;

            if (!empty($final_type) && $amt > 0) {
                mysqli_query($conn, "
                    INSERT INTO bros_expense (report_id, expense_date, expense_type, amount)
                    VALUES ('$daily_id', '$report_date', '$final_type', '$amt')
                ");
            }
        }

        // ---------- Insert Cash Breakdown ----------
        $denoms = [1000, 500, 200, 100, 50, 20, 10, 5, 1];
        $cash_values = [];
        $total_amount = 0;

        foreach ($denoms as $d) {
            $qty = isset($cash_arr[$d]) ? intval($cash_arr[$d]) : 0; // ✅ FIXED
            $cash_values[$d] = $qty;
            $total_amount += $d * $qty;
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

        echo "Daily Report Saved Successfully";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    exit;
}

if ($_POST["brotbl"] == "UPDATE_DAILY") {
    $id              = $_POST["id"];
    $report_date     = $_POST["report_date"];
    $cash_on_counter = $_POST["cash_on_counter"] ?: 0;
    $cash_in         = $_POST["cash_in"] ?: 0;
    $gcash_sales     = $_POST["gcash_sales"] ?: 0;
    $credit_sales    = $_POST["credit_sales"] ?: 0;
    $expenses_arr    = json_decode($_POST["expenses"], true);
    $cash_arr        = json_decode($_POST["cash_breakdown"], true);

    // ---------- Recompute Sales ----------
    $total_cash_sales    = $cash_on_counter + $cash_in;
    $gross_sales         = $total_cash_sales + $gcash_sales + $credit_sales;
    $total_expense       = 0;

    foreach ($expenses_arr as $exp) {
        $total_expense += floatval($exp['amount']);
    }

    $net_sales           = $gross_sales - $total_expense;
    $total_sales_counter = $total_cash_sales + $gcash_sales;
    $over_short          = $cash_on_counter - $total_sales_counter;

    // ---------- Update Main Daily Report ----------
    $updateDaily = mysqli_query($conn, "
        UPDATE bros_dailyreport_tbl 
        SET 
            report_date       = '$report_date',
            cash_on_counter   = '$cash_on_counter',
            cash_in           = '$cash_in',
            total_cash_sales  = '$total_cash_sales',
            gcash_sales       = '$gcash_sales',
            credit_sales      = '$credit_sales',
            gross_sales       = '$gross_sales',
            expenses          = '$total_expense',
            net_sales         = '$net_sales',
            total_sales_counter = '$total_sales_counter',
            over_short        = '$over_short'
        WHERE id='$id'
    ");

    if ($updateDaily) {
        // ---------- Refresh Expenses ----------
        mysqli_query($conn, "DELETE FROM bros_expense WHERE report_id='$id'");
        foreach ($expenses_arr as $exp) {
            $type  = $exp['type'];
            $other = $exp['other'];
            $amt   = floatval($exp['amount']);
            $final_type = ($type == "Others" && !empty($other)) ? $other : $type;

            if (!empty($final_type) && $amt > 0) {
                mysqli_query($conn, "
                    INSERT INTO bros_expense (report_id, expense_date, expense_type, amount)
                    VALUES ('$id', '$report_date', '$final_type', '$amt')
                ");
            }
        }

        // ---------- Refresh Cash Breakdown ----------
        mysqli_query($conn, "DELETE FROM bros_cash_breakdown WHERE report_id='$id'");
        $denoms = [1000, 500, 200, 100, 50, 20, 10, 5, 1];
        $cash_values = [];
        $total_amount = 0;

        foreach ($denoms as $d) {
            $qty = isset($cash_arr[$d]) ? intval($cash_arr[$d]) : 0;
            $cash_values[$d] = $qty;
            $total_amount += $d * $qty;
        }

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

        echo "Daily Report Updated Successfully";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    exit;
}

if ($_POST["brotbl"] == "SELECT_DAILY") {
    $id = $_POST["id"];
    $sql = "SELECT * FROM bros_dailyreport_tbl WHERE id='$id'";
    $result = mysqli_query($conn, $sql);
    $output = [];

    if ($row = mysqli_fetch_assoc($result)) {
        $output["id"]                 = $row["id"];
        $output["report_date"]        = $row["report_date"];
        $output["cash_on_counter"]    = $row["cash_on_counter"];
        $output["cash_in"]            = $row["cash_in"];
        $output["total_cash_sales"]   = $row["total_cash_sales"];
        $output["gcash_sales"]        = $row["gcash_sales"];
        $output["credit_sales"]       = $row["credit_sales"];
        $output["gross_sales"]        = $row["gross_sales"];
        $output["expenses"]           = $row["expenses"];
        $output["net_sales"]          = $row["net_sales"];
        $output["total_sales_counter"]= $row["total_sales_counter"];
        $output["over_short"]         = $row["over_short"];

        // ---------- Expenses ----------
        $expRes = mysqli_query($conn, "SELECT expense_type, amount FROM bros_expense WHERE report_id='$id'");
        $expenses = [];
        while ($exp = mysqli_fetch_assoc($expRes)) {
            $type = in_array($exp['expense_type'], ["Electricity Expense","Water Expense","Salary Expense"]) 
                        ? $exp['expense_type']
                        : "Others";
            $other = ($type=="Others") ? $exp['expense_type'] : "";
            $expenses[] = [
                "expense_type" => $type,
                "amount"       => $exp['amount'],
                "other"        => $other
            ];
        }
        $output["expense_list"] = $expenses;

        // ---------- Cash Breakdown ----------
        $cashRes = mysqli_query($conn, "SELECT * FROM bros_cash_breakdown WHERE report_id='$id' LIMIT 1");
        $output["cash_breakdown"] = mysqli_fetch_assoc($cashRes);
    }

    echo json_encode($output);
    exit;
}

if ($_POST["brotbl"] == "DELETE_DAILY") {
    $id = $_POST["id"];

    // Delete children first
    mysqli_query($conn, "DELETE FROM bros_expense WHERE report_id='$id'");
    mysqli_query($conn, "DELETE FROM bros_cash_breakdown WHERE report_id='$id'");

    // Delete parent
    $execute = mysqli_query($conn, "DELETE FROM bros_dailyreport_tbl WHERE id='$id'");

    if ($execute) {
        echo "Daily Report has been deleted";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    exit;
}


















        // ---------- LOAD STOCK MOVEMENT ----------
        if ($_POST["brotbl"] == "LOADBROSTCK") {

            // Removed sm.status since it's no longer in bro_stock_movements
            $sql = "SELECT sm.id, i.product_name, sm.qty_in, sm.qty_out, sm.unit_cost, sm.movement_date
                    FROM bro_stock_movements sm
                    LEFT JOIN bros_inventory i ON sm.product_id = i.id
                    ORDER BY sm.movement_date DESC";

            $result = mysqli_query($conn, $sql);
            if (!$result) { die("Stock Movement Query failed: " . mysqli_error($conn)); }

            $totalQuery = "SELECT COUNT(*) AS total FROM bro_stock_movements";
            $totalResult = mysqli_query($conn, $totalQuery);
            $totalRow = mysqli_fetch_assoc($totalResult);
            $totalRecords = $totalRow['total'];

            $output = '';
            $output .= '
            <table style="width: 100%; border-radius: 20px;">
                <tr><td style="padding: 10px;">
                <table class="border-table" style="width: 100%; border-collapse: collapse;" id="border">

                <tr>
                    <td colspan="9" id="border" class="report-header">
                        <div class="report-header-container">
                            <!-- Left side -->
                            <span class="report-title">
                                BROS STOCK REPORT
                            </span>

                            <button id="stockbtn" class="btn-reset" style="color:white; background: linear-gradient(90deg, #672222, #8c2f2f);">ADD STOCK</button>
                        </div>
                    
                        
                    
                    
                    </td>
                </tr>

                <tr>
                    <td id="border" style="width: 30%; background: #CCCCCC; padding: 8px;"><center><strong>PRODUCT</strong></center></td>
                    <td id="border" style="width: 10%; background: #CCCCCC; padding: 8px;"><center><strong>QTY IN</strong></center></td>
                    <td id="border" style="width: 10%; background: #CCCCCC; padding: 8px;"><center><strong>QTY OUT</strong></center></td>
                    <td id="border" style="width: 15%; background: #CCCCCC; padding: 8px;"><center><strong>UNIT COST</strong></center></td>
                    <td id="border" style="width: 15%; background: #CCCCCC; padding: 8px;"><center><strong>DATE</strong></center></td>
                </tr>';

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                    $output .= '
                    <tr>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . strtoupper($row["product_name"]) . '</center></td>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . intval($row["qty_in"]) . '</center></td>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . intval($row["qty_out"]) . '</center></td>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . number_format($row["unit_cost"],2) . '</center></td>
                        <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . $row["movement_date"] . '</center></td>
                    </tr>';
                }
            } else {
                $output .= '<tr><td colspan="5" align="center">No records found</td></tr>';
            }

            $output .= '<tr><td colspan="5" style="padding: 2px;"><font size="1">Total Number of Records: ' . $totalRecords . '</font></td></tr>
                </table>
                </td></tr>
            </table>';
            echo $output;
        }





        if ($_POST["brotbl"] == "SAVE") {
            $label = $_POST["label"];
            $product_name = $_POST["product_name"];
            $classification = $_POST["classification"];
            $unit_price = $_POST["unit_price"];
            $quantity = $_POST["quantity"];

            // Check if product exists
            $check = mysqli_query($conn, "SELECT * FROM bros_inventory WHERE product_name='$product_name' AND label='$label'");
            if (mysqli_num_rows($check) > 0) {
                echo "Product already exists";
            } else {
                $insert = mysqli_query($conn, "INSERT INTO bros_inventory (label, product_name, classification, unit_price, available_stock, status)
                    VALUES ('$label', '$product_name', '$classification', '$unit_price', '$quantity', 'OK')");
                echo $insert ? "Product added successfully" : "Error: " . mysqli_error($conn);
            }
            exit; // <- STOP execution so no HTML follows
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
            $delete = mysqli_query($conn, "DELETE FROM bros_inventory WHERE id='$id'");
            
        
            echo $delete ? "Product deleted successfully" : "Error: " . mysqli_error($conn);
            exit; 
        }










        // ---------- SAVE STOCK MOVEMENT ----------
        if ($_POST["brotbl"] == "SAVE2") {
            $productName   = $_POST["product"];
            $movementType  = $_POST["movement_type"];
            $quantity      = intval($_POST["quantity"]);
            $unitCost      = floatval($_POST["unit_cost"]);
            $movementDate  = $_POST["movement_date"];

            // 1. Check if product exists
            $sql = "SELECT id FROM bros_inventory WHERE product_name = '$productName' LIMIT 1";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            $productId = $row ? $row["id"] : null;

            // 2. If not exists, insert new product row in inventory
            if (!$productId) {
                $insertProduct = "INSERT INTO bros_inventory (product_name, classification, unit_price, available_stock, status) 
                                VALUES ('$productName', 'Unclassified', '$unitCost', 0, 'OK')";
                $execProd = mysqli_query($conn, $insertProduct);

                if ($execProd) {
                    $productId = mysqli_insert_id($conn); // get new product id
                } else {
                    echo "Error: Failed to insert new product.";
                    exit;
                }
            }

            // 3. Prepare qty_in / qty_out
            $qty_in  = ($movementType == "IN") ? $quantity : 0;
            $qty_out = ($movementType == "OUT") ? $quantity : 0;

            // 4. Insert into stock movements
            $insert = "INSERT INTO bro_stock_movements (product_id, movement_date, qty_in, qty_out, unit_cost) 
                    VALUES ('$productId', '$movementDate', '$qty_in', '$qty_out', '$unitCost')";
            $execute = mysqli_query($conn, $insert);

            if ($execute) {
                // 5. Update available stock
                if ($movementType == "IN") {
                    $update = "UPDATE bros_inventory 
                            SET available_stock = available_stock + $quantity 
                            WHERE id = '$productId'";
                } else {
                    $update = "UPDATE bros_inventory 
                            SET available_stock = available_stock - $quantity 
                            WHERE id = '$productId'";
                }
                mysqli_query($conn, $update);

                // 6. Update status
                $updateStatus = "UPDATE bros_inventory 
                                SET status = CASE 
                                    WHEN available_stock <= 0 THEN 'NEED TO RESTOCK' 
                                    ELSE 'OK' 
                                END 
                                WHERE id = '$productId'";
                mysqli_query($conn, $updateStatus);

                echo "Stock has been saved successfully.";
            } else {
                echo "Error saving stock.";
            }
        }





















        



}



 // Handle Change Password & Username
    if (isset($_POST['action']) && $_POST['action'] == "CHANGE_PASS_USER") {
        $current_username = mysqli_real_escape_string($conn, $_POST['current_username']);
        $current_password = mysqli_real_escape_string($conn, $_POST['current_password']);
        $new_username = mysqli_real_escape_string($conn, $_POST['new_username']);
        $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
        
        // Verify current credentials
        $verify_sql = "SELECT * FROM tbl_acc WHERE adminuser = '$current_username' AND adminpass = '$current_password'";
        $verify_result = mysqli_query($conn, $verify_sql);
        
        if (mysqli_num_rows($verify_result) > 0) {
            // Update credentials
            $update_sql = "UPDATE tbl_acc SET adminuser = '$new_username', adminpass = '$new_password' WHERE adminuser = '$current_username'";
            if (mysqli_query($conn, $update_sql)) {
                echo "Password and username updated successfully!";
            } else {
                echo "Error updating credentials.";
            }
        } else {
            echo "Current username or password is incorrect.";
        }
    }

    // Handle Add Admin Account
    if (isset($_POST['action']) && $_POST['action'] == "ADD_ADMIN_ACCOUNT") {
        $admin_username = mysqli_real_escape_string($conn, $_POST['admin_username']);
        $admin_password = mysqli_real_escape_string($conn, $_POST['admin_password']);
        
        // Check if username already exists
        $check_sql = "SELECT * FROM tbl_acc WHERE adminuser = '$admin_username'";
        $check_result = mysqli_query($conn, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            echo "Username already exists. Please choose a different username.";
        } else {
            // Insert new admin account
            $insert_sql = "INSERT INTO tbl_acc (adminuser, adminpass, created_at) VALUES ('$admin_username', '$admin_password', NOW())";
            if (mysqli_query($conn, $insert_sql)) {
                echo "Backup admin account created successfully!";
            } else {
                echo "Error creating admin account.";
            }
        }
    }

    // ========== FUEL PRICE MANAGEMENT ==========
    
    // Get Current Prices (latest price for each branch)
    if (isset($_POST['action']) && $_POST['action'] == "GET_CURRENT_PRICES") {
        $sql = "SELECT p1.* 
                FROM fuel_prices p1
                INNER JOIN (
                    SELECT branch, MAX(CONCAT(effective_date, ' ', effective_time)) AS max_datetime
                    FROM fuel_prices
                    GROUP BY branch
                ) p2 ON p1.branch = p2.branch 
                    AND CONCAT(p1.effective_date, ' ', p1.effective_time) = p2.max_datetime
                ORDER BY p1.branch";
        
        $result = mysqli_query($conn, $sql);
        $prices = [];
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $prices[] = [
                    'branch' => $row['branch'],
                    'diesel_price' => $row['diesel_price'],
                    'premium_price' => $row['premium_price'],
                    'unleaded_price' => $row['unleaded_price'],
                    'effective_date' => $row['effective_date'],
                    'effective_time' => $row['effective_time']
                ];
            }
        }
        
        echo json_encode($prices);
        exit;
    }

    // Get Fuel Price for a Specific Branch (latest price)
    if (isset($_POST['action']) && $_POST['action'] == "GET_BRANCH_PRICE") {
        $branch = isset($_POST['branch']) ? mysqli_real_escape_string($conn, $_POST['branch']) : '';
        
        if (empty($branch)) {
            echo json_encode(['error' => 'Branch is required']);
            exit;
        }
        
        $sql = "SELECT p1.* 
                FROM fuel_prices p1
                INNER JOIN (
                    SELECT branch, MAX(CONCAT(effective_date, ' ', effective_time)) AS max_datetime
                    FROM fuel_prices
                    WHERE branch = '$branch'
                    GROUP BY branch
                ) p2 ON p1.branch = p2.branch 
                    AND CONCAT(p1.effective_date, ' ', p1.effective_time) = p2.max_datetime
                WHERE p1.branch = '$branch'
                LIMIT 1";
        
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            echo json_encode([
                'branch' => $row['branch'],
                'diesel_price' => $row['diesel_price'],
                'premium_price' => $row['premium_price'],
                'unleaded_price' => $row['unleaded_price'],
                'effective_date' => $row['effective_date'],
                'effective_time' => $row['effective_time']
            ]);
        } else {
            echo json_encode(['error' => 'No prices found for this branch']);
        }
        exit;
    }

    // Save Fuel Price
    if (isset($_POST['action']) && $_POST['action'] == "SAVE_FUEL_PRICE") {
        $branch = mysqli_real_escape_string($conn, $_POST['branch']);
        $effective_date = mysqli_real_escape_string($conn, $_POST['effective_date']);
        $effective_time = mysqli_real_escape_string($conn, $_POST['effective_time']);
        $diesel_price = floatval($_POST['diesel_price']);
        $premium_price = floatval($_POST['premium_price']);
        $unleaded_price = floatval($_POST['unleaded_price']);
        
        // Get current admin user (accept from POST or use default)
        $changed_by = isset($_POST['changed_by']) && !empty($_POST['changed_by']) 
            ? mysqli_real_escape_string($conn, $_POST['changed_by']) 
            : 'Admin';
        
        // Validate inputs
        if (empty($branch) || empty($effective_date) || empty($effective_time) || 
            $diesel_price <= 0 || $premium_price <= 0 || $unleaded_price <= 0) {
            echo "Please fill all fields with valid values.";
            exit;
        }
        
        // Insert new price record
        $sql = "INSERT INTO fuel_prices (branch, effective_date, effective_time, diesel_price, premium_price, unleaded_price, changed_by, created_at) 
                VALUES ('$branch', '$effective_date', '$effective_time', '$diesel_price', '$premium_price', '$unleaded_price', '$changed_by', NOW())";
        
        if (mysqli_query($conn, $sql)) {
            echo "Fuel prices saved successfully for $branch!";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
        exit;
    }

    // Get Price History
    if (isset($_POST['action']) && $_POST['action'] == "GET_PRICE_HISTORY") {
        $branch_filter = isset($_POST['branch']) && !empty($_POST['branch']) 
            ? "WHERE branch = '" . mysqli_real_escape_string($conn, $_POST['branch']) . "'" 
            : "";
        
        $sql = "SELECT * FROM fuel_prices $branch_filter 
                ORDER BY effective_date DESC, effective_time DESC, branch ASC";
        
        $result = mysqli_query($conn, $sql);
        $history = [];
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $history[] = [
                    'branch' => $row['branch'],
                    'effective_date' => $row['effective_date'],
                    'effective_time' => $row['effective_time'],
                    'diesel_price' => $row['diesel_price'],
                    'premium_price' => $row['premium_price'],
                    'unleaded_price' => $row['unleaded_price'],
                    'changed_by' => $row['changed_by'],
                    'created_at' => $row['created_at']
                ];
            }
        }
        
        echo json_encode($history);
        exit;
    }









?>