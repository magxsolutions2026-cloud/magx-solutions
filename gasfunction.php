<?php
session_start();
$conn = mysqli_connect('localhost', 'root', 'root', 'tmc_admin_db');


$branch = $_SESSION['branch'];

// Only process if action is set
if (isset($_POST["action"])) {

    if ($_POST["action"] == "LOAD") {
        // Date filters
        $from_date = !empty($_POST['from_date']) ? $_POST['from_date'] : null;
        $to_date   = !empty($_POST['to_date']) ? $_POST['to_date'] : null;

        // Build WHERE clauses
        $whereClauses = [];
        if ($branch != '') {
            $whereClauses[] = "branch = '$branch'";
        }
        if ($from_date && $to_date) {
            $whereClauses[] = "log_date BETWEEN '$from_date' AND '$to_date'";
        }

        $whereSQL = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

        // Queries
        $sql = "SELECT * FROM gas_sales_tbl $whereSQL ORDER BY id DESC LIMIT 6";
        $totalQuery = "SELECT COUNT(*) AS total FROM gas_sales_tbl $whereSQL";

        $result = mysqli_query($conn, $sql);
        $totalResult = mysqli_query($conn, $totalQuery);
        $totalRow = mysqli_fetch_assoc($totalResult);
        $totalRecords = $totalRow['total'];

        // Generate table
        $output = '';
        $output .= '
        <table style="width: 100%; border-radius: 20px;">
            <tr><td style="padding: 10px;">
            <table style="width: 100%; border-collapse: collapse;" id="border">

            <tr>
                <td colspan="9" id="border" class="report-header">
                    <div class="report-header-container">
                        <span class="report-title">GASOLINE SALES REPORT</span>
                        <div class="date-filter">
                            <label for="from_date">From:</label>
                            <div class="header-search" style="height:35px;">
                                <input type="date" id="from_date" class="date-input">
                            </div>
                            <label for="to_date">To:</label>
                            <div class="header-search" style="height:35px;">
                                <input type="date" id="to_date" class="date-input">
                            </div>
                            <button id="filterBtn" class="btn-filter" style="color:white; background: linear-gradient(90deg, #672222, #8c2f2f);">Filter</button>
                            <button id="resetBtn" class="btn-reset" style="color:white; background: linear-gradient(90deg, #672222, #8c2f2f);">Reset</button>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td id="border" style="width: 40%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>DATE</strong></td>
                <td id="border" style="width: 40%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>DAILY SALES</strong></td>
                <td id="border" style="width: 20%; background: #CCCCCC; padding: 8px; text-align:center;"><strong>ACTIONS</strong></td>
            </tr>';

        if (mysqli_num_rows($result) > 0) {
            $current_date = null;
            while ($row = mysqli_fetch_array($result)) {
                if ($current_date !== $row["log_date"]) {
                    $current_date = $row["log_date"];
                    $output .= '
                    ';
                }

                $output .= '
                <tr>
                    <td id="border" style="background: #EEEEEE; padding:10px; text-align:center;">' . date("F j, Y", strtotime($row["log_date"])) . '</td>
                    <td id="border" style="background: #EEEEEE; padding:10px; text-align:center;">₱' . number_format($row["netsales"], 2) . '</td>
                    <td id="border" style="background: #EEEEEE; padding:10px; text-align:center;">
                        <button type="button"
                                class="btn btn-sm btn-outline-dark date-details-btn"
                                data-date="'.$row['log_date'].'"
                                data-branch="'.htmlspecialchars($row['branch']).'">
                            View
                        </button>
                        <form method="POST" action="dailygaspdf.php" target="_blank" style="display:inline;">
                            <input type="hidden" name="row_id" value="'.$row['id'].'">
                            <input type="hidden" name="period" value="'.$row['log_date'].'">
                            <button type="submit" name="action" value="view" class="btn btn-primary" style="padding:2px; background:none; border:none;">
                                <img src="view.png" alt="icon" style="width:25px; height:25px;">
                            </button>
                        </form>
                        <form method="POST" action="dailygaspdf.php" style="display:inline;">
                            <input type="hidden" name="row_id" value="'.$row['id'].'">
                            <input type="hidden" name="period" value="'.$row['log_date'].'">
                            <button type="submit" name="action" value="download" class="btn btn-danger" style="padding:2px; background:none; border:none;">
                                <img src="down.png" alt="icon" style="width:25px; height:25px;">
                            </button>
                        </form>
                    </td>
                </tr>';
            }
        } else {
            $output .= '<tr><td colspan="9" align="center">No records found</td></tr>';
        }

        $output .= '
            <tr><td colspan="9" style="padding: 2px;">
                <font size="1">Total Number of Records: ' . $totalRecords . '</font>
            </td></tr>
            </table>
            </td></tr>
        </table>';

        echo $output;
    }

    if ($_POST["action"] == "DATE_DETAILS") {
        $date   = isset($_POST['date']) ? mysqli_real_escape_string($conn, $_POST['date']) : '';
        $branchReq = isset($_POST['branch']) ? mysqli_real_escape_string($conn, $_POST['branch']) : $branch;

        $fuelRows = [];
        $fuelSql = "SELECT fuel_type, volume_sold, fuel_price, total_amount, oras FROM gas_sales_tbl WHERE branch='$branchReq' AND log_date='$date' ORDER BY oras ASC";
        $fuelRes = mysqli_query($conn, $fuelSql);
        if ($fuelRes) {
            while ($fr = mysqli_fetch_assoc($fuelRes)) { $fuelRows[] = $fr; }
        }

        $lubeRows = [];
        $lubeSql = "SELECT pname, quantity, pprice, (quantity * pprice) AS line_total FROM lub_sales_tbl WHERE branch='$branchReq' AND log_date='$date'";
        $lubeRes = mysqli_query($conn, $lubeSql);
        if ($lubeRes) {
            while ($lr = mysqli_fetch_assoc($lubeRes)) { $lubeRows[] = $lr; }
        }

        $expRows = [];
        $expSql = "SELECT expense_type, amount, other_description FROM gas_expenses_tbl WHERE branch='$branchReq' AND log_date='$date'";
        $expRes = mysqli_query($conn, $expSql);
        if ($expRes) {
            while ($er = mysqli_fetch_assoc($expRes)) { $expRows[] = $er; }
        }

        $fuelTotal = 0; foreach ($fuelRows as $f) { $fuelTotal += floatval($f['total_amount']); }
        $lubeTotal = 0; foreach ($lubeRows as $l) { $lubeTotal += floatval($l['line_total']); }
        $expTotal  = 0; foreach ($expRows as $e) { $expTotal  += floatval($e['amount']); }
        $netTotal  = $fuelTotal + $lubeTotal - $expTotal;

        ob_start();
        ?>
        <style>
            .gm-details-card { border: none; border-radius: 14px; box-shadow: 0 6px 16px rgba(0,0,0,0.12); overflow: hidden; margin-bottom: 14px; }
            .gm-details-card .card-header { background: linear-gradient(90deg, #672222, #8c2f2f); color: #fff; font-weight: 700; padding: 10px 14px; }
            .gm-details-card .card-body { padding: 12px 14px; background:#fff; }
            .gm-details-table th, .gm-details-table td { vertical-align: middle; }
            .gm-details-table thead { background: #f4f4f4; }
            .gm-num { text-align: right; white-space: nowrap; }
        </style>
       

        <div class="gm-details-card">
            <div class="card-header">Fuel Sales</div>
            <div class="card-body">
            <?php if (count($fuelRows) > 0): ?>
                <table class="table table-sm table-striped table-bordered gm-details-table mb-0">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Fuel</th>
                            <th>Volume (L)</th>
                            <th>Price</th>
                            <th>Total</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fuelRows as $f): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($f['oras']); ?></td>
                                <td><?php echo htmlspecialchars($f['fuel_type']); ?></td>
                                <td class="gm-num"><?php echo number_format($f['volume_sold'], 2); ?></td>
                                <td class="gm-num">₱<?php echo number_format($f['fuel_price'], 2); ?></td>
                                <td class="gm-num">₱<?php echo number_format($f['total_amount'], 2); ?></td>
                                
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Fuel Total</th>
                            <th colspan="2" class="gm-num">₱<?php echo number_format($fuelTotal, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <p class="text-muted mb-1">No fuel sales.</p>
            <?php endif; ?>
            </div>
        </div>

        <div class="gm-details-card">
            <div class="card-header">Lubricant Sales</div>
            <div class="card-body">
            <?php if (count($lubeRows) > 0): ?>
                <table class="table table-sm table-striped table-bordered gm-details-table mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lubeRows as $l): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($l['pname']); ?></td>
                                <td class="gm-num"><?php echo intval($l['quantity']); ?></td>
                                <td class="gm-num">₱<?php echo number_format($l['pprice'], 2); ?></td>
                                <td class="gm-num">₱<?php echo number_format($l['line_total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Lubricant Total</th>
                            <th class="gm-num">₱<?php echo number_format($lubeTotal, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <p class="text-muted mb-1">No lubricant sales.</p>
            <?php endif; ?>
            </div>
        </div>

        <div class="gm-details-card">
            <div class="card-header">Expenses</div>
            <div class="card-body">
            <?php if (count($expRows) > 0): ?>
                <table class="table table-sm table-striped table-bordered gm-details-table mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expRows as $e): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($e['expense_type']); ?></td>
                                <td class="gm-num">₱<?php echo number_format($e['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($e['other_description']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-end">Total</th>
                            <th colspan="2" class="gm-num">₱<?php echo number_format($expTotal, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <p class="text-muted mb-1">No expenses.</p>
            <?php endif; ?>
            </div>
        </div>

        <div class="gm-details-card mb-0">
            <div class="card-header">Totals</div>
            <div class="card-body">
                <table class="table table-sm table-striped table-bordered gm-details-table mb-0">
                    <tbody>
                        <tr><th style="width:50%;">Fuel Total</th><td class="gm-num">₱<?php echo number_format($fuelTotal, 2); ?></td></tr>
                        <tr><th>Lubricant Total</th><td class="gm-num">₱<?php echo number_format($lubeTotal, 2); ?></td></tr>
                        <tr><th>Expenses</th><td class="gm-num">₱<?php echo number_format($expTotal, 2); ?></td></tr>
                        <tr><th>Net Sales</th><td class="gm-num fw-bold">₱<?php echo number_format($netTotal, 2); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        $output = ob_get_clean();
        echo $output;
        exit;
    }


                    if ($_POST["action"] == "SAVE") {
                        $licensed_id = $_POST["licensed_id"];
                        $firstName = $_POST["firstName"];
                        $middleName = $_POST["middleName"];
                        $lastName = $_POST["lastName"];
                        $age = $_POST["age"];
                        $gender = $_POST["gender"];
                        $specialized = $_POST["specialized"];
                        $hospital = $_POST["hospital"];
                        $dailyschedule = $_POST["dailyschedule"];
                        $timeschedule = $_POST["timeschedule"];

                        $str = "INSERT INTO tbl_doctors (licensedid, fname, mname, lname, age, gender, specialized, hospital, dailyschedule, timeschedule) VALUES (
                            '$licensed_id', '$firstName', '$middleName', '$lastName', '$age', '$gender', '$specialized', '$hospital', '$dailyschedule', '$timeschedule')";
                        $execute = mysqli_query($conn, $str);

                        if ($execute) {
                            echo 'The record has been saved';
                        }
                    }

                    if ($_POST["action"] == "SELECT") {
                        $sql = "SELECT * FROM tbl_doctors WHERE licensedid='" . $_POST["id"] . "'";
                        $result = mysqli_query($conn, $sql);
                        $output = [];

                        if ($row = mysqli_fetch_array($result)) {
                            $output["licensedid"] = strtoupper($row["licensedid"]);
                            $output["first_name"] = strtoupper($row["fname"]);
                            $output["middle_name"] = strtoupper($row["mname"]);
                            $output["last_name"] = strtoupper($row["lname"]);
                            $output["age"] = strtoupper($row["age"]);
                            $output["gender"] = strtoupper($row["gender"]);
                            $output["specialized"] = strtoupper($row["specialized"]);
                            $output["hospital"] = strtoupper($row["hospital"]);
                            $output["dailyschedule"] = strtoupper($row["dailyschedule"]);
                            $output["timeschedule"] = strtoupper($row["timeschedule"]);
                        }

                        echo json_encode($output);
                    }

                    if ($_POST["action"] == "UPDATE") {
                        $licensed_id = $_POST["licensed_id"];
                        $firstName = $_POST["firstName"];
                        $middleName = $_POST["middleName"];
                        $lastName = $_POST["lastName"];
                        $age = $_POST["age"];
                        $gender = $_POST["gender"];
                        $specialized = $_POST["specialized"];
                        $hospital = $_POST["hospital"];
                        $dailyschedule = $_POST["dailyschedule"];
                        $timeschedule = $_POST["timeschedule"];
                        $id = $_POST["id"];

                        $str = "UPDATE tbl_doctors SET licensedid='$licensed_id', fname='$firstName', mname='$middleName', lname='$lastName', age='$age', gender='$gender',
                        specialized='$specialized', hospital='$hospital', dailyschedule='$dailyschedule', timeschedule='$timeschedule' WHERE licensedid='$id'";
                        $execute = mysqli_query($conn, $str);

                        if ($execute) {
                            echo 'The record has been updated';
                        }
                    }

                    if ($_POST["action"] == "DELETE") {
                        $str = "DELETE FROM tbl_doctors WHERE licensedid='" . $_POST["id"] . "'";
                        $execute = mysqli_query($conn, $str);
                        if ($execute) {
                            echo 'The record has been deleted';
                        }
                    }

                    if ($_POST["action"] == "CHANGE_PASS_USER") {
                        $con = mysqli_connect('localhost', 'root', 'root', 'tmc_db');
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
                    }
        }



?>