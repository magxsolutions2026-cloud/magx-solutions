<?php
    $conn = mysqli_connect('localhost','root','root','tmc_admin_db');

    if (isset($_POST["leasetbl"])) {
        // ------------------- STATUS OPTIONS (for dropdown) -------------------
        if ($_POST["leasetbl"] == "STATUS_OPTIONS") {
            echo json_encode([
                ["value" => "1", "label" => "PAID"],
                ["value" => "0", "label" => "NOT PAID"]
            ]);
            exit;
        }

        if ($_POST["leasetbl"] == "LOADLEASEREC") {
             // Get current month/year
          $current_month = date("n");
            $current_year  = date("Y");

        $sql = "
            SELECT l.tenant_id, l.tname, l.ddate,
                -- Latest monthly rate (price_of_unit if present, else 0)
                (
                    SELECT COALESCE(NULLIF(pp.price_of_unit,0), 0)
                    FROM lease_pay_tbl pp
                    WHERE pp.tenant_id = l.tenant_id
                    ORDER BY pp.pay_year DESC, pp.pay_month DESC, pp.pdate DESC
                    LIMIT 1
                ) AS monthly_rate,
                0 AS monthly_due,
                -- Total paid across all time (payment + compensation)
                (
                    SELECT COALESCE(SUM(p.payment + COALESCE(p.compensation,0)),0)
                    FROM lease_pay_tbl p 
                    WHERE p.tenant_id = l.tenant_id
                ) AS total_paid_all,
                (SELECT MIN(period_from) FROM lease_pay_tbl p3 WHERE p3.tenant_id = l.tenant_id) AS first_period_from,
                (SELECT MAX(period_to) FROM lease_pay_tbl p4 WHERE p4.tenant_id = l.tenant_id) AS last_period_to,
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


            <td colspan="8" id="border" class="report-header">
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
            <td id="border" style="width: 19%; background: #CCCCCC; padding: 5px; vertical-align: middle;"><center><strong>TENANT NAME</strong></center></td>
            <td id="border" style="width: 15%; background: #CCCCCC; padding: 5px; vertical-align: middle;"><center><strong>PERIOD FROM</strong></center></td>
            <td id="border" style="width: 15%; background: #CCCCCC; padding: 5px; vertical-align: middle;"><center><strong>PERIOD TO</strong></center></td>
            <td id="border" style="width: 12%; background: #CCCCCC; padding: 5px; vertical-align: middle;"><center><strong>STATUS</strong></center></td>
            <td id="border" style="width: 12%; background: #CCCCCC; padding: 5px; vertical-align: middle;"><center><strong>BALANCE</strong></center></td>
            <td id="border" style="width: 12%; background: #CCCCCC; padding: 5px; vertical-align: middle;"><center><strong>ADVANCE</strong><br/><small>(applied to next)</small></center></td>
            <td id="border" style="width: 6%; background: #CCCCCC; padding: 5px; vertical-align: top;"></td>
            ';

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $tenantNameEsc = htmlspecialchars($row["tname"], ENT_QUOTES, 'UTF-8');

                            // --- Real-life advance/balance computation ---
                            $monthlyRate = floatval($row["monthly_rate"]);
                            $monthlyRate = floatval($row["monthly_rate"]);
                            $totalPaidAll   = floatval($row["total_paid_all"]);

                            if ($monthlyRate <= 0) {
                                // No rate available: show unpaid with zero balance/advance
                                $status = '<span style="color: red;">Not PAID</span>';
                                $balance_display = number_format(0, 2);
                                $advance_display = number_format(0, 2);
                            } else {
                                $startDate = !empty($row["first_period_from"]) ? $row["first_period_from"] : $row["ddate"];
                                $elapsedMonths = 1;
                                if (!empty($startDate)) {
                                    $startMonth = new DateTime(date('Y-m-01', strtotime($startDate)));
                                    $currentMonth = new DateTime(date('Y-m-01'));
                                    $elapsedMonths = (($currentMonth->format('Y') - $startMonth->format('Y')) * 12) +
                                                     ($currentMonth->format('n') - $startMonth->format('n')) + 1;
                                    if ($elapsedMonths < 1) { $elapsedMonths = 1; }
                                }

                                $monthsCovered  = floor($totalPaidAll / $monthlyRate);
                                $remainder      = $totalPaidAll - ($monthsCovered * $monthlyRate);

                                $balanceAmount  = 0;
                                $advanceAmount  = 0;

                                if ($monthsCovered >= $elapsedMonths) {
                                    $advanceMonths = $monthsCovered - $elapsedMonths;
                                    $advanceAmount = ($advanceMonths * $monthlyRate) + $remainder;
                                } else {
                                    $balanceMonths = $elapsedMonths - $monthsCovered;
                                    $balanceAmount = ($balanceMonths * $monthlyRate) - $remainder;
                                    if ($balanceAmount < 0) {
                                        // Extra remainder actually becomes advance
                                        $advanceAmount = abs($balanceAmount);
                                        $balanceAmount = 0;
                                    }
                                }

                                $status = ($totalPaidAll + 0.009) >= ($elapsedMonths * $monthlyRate)
                                    ? '<span style="color: green;">PAID</span>'
                                    : '<span style="color: red;">Not PAID</span>';

                                $balance_display = number_format(max(0, $balanceAmount), 2);
                                $advance_display = number_format(max(0, $advanceAmount), 2);
                            }
                            
                            // Format period FROM information
                            $period_from_display = "—";
                            if (!empty($row["first_period_from"])) {
                                $period_from_display = date("M d, Y", strtotime($row["first_period_from"]));
                            } else if (!empty($row["ddate"])) {
                                // Fallback to ddate if no period info
                                $period_from_display = date("M d, Y", strtotime($row["ddate"]));
                            }
                            
                            // Format period TO information
                            $period_to_display = "—";
                            if (!empty($row["last_period_to"])) {
                                $period_to_display = date("M d, Y", strtotime($row["last_period_to"]));
                            }

                            $output .= '
            <tr>
                <td id="border" style="background: #EEEEEE;"><center><input type="checkbox" style="accent-color: maroon;" class="check_box" value="' . $row["tenant_id"] . '"></center></td>
                <td id="border" style="background: #EEEEEE;"><center>' . strtoupper($row["tname"]) . '</center></td>
                <td id="border" style="background: #EEEEEE;"><center>' . $period_from_display . '</center></td>
                <td id="border" style="background: #EEEEEE;"><center>' . $period_to_display . '</center></td>
                <td id="border" style="background: #EEEEEE;"><center>' . $status . '</center></td>
                <td id="border" style="background: #EEEEEE;"><center>' . $balance_display . '</center></td>
                <td id="border" style="background: #EEEEEE;"><center>' . $advance_display . '</center></td>
                <td id="border" style="background: #EEEEEE;"><center>
                    
                    <button style="padding:2px; background:none; border:none;" class="btn btn-sm btn-primary view-history" data-id="' . $row["tenant_id"] . '" data-name="' . $tenantNameEsc . '"> 
                        <img src="eye.png" alt="icon" id="eye" style="width:25px; height:25px; vertical-align:middle;">
                    </button>
                </center></td>
            </tr>';
                            $ctr++;
                        }
                    } else {
                        $output .= '<tr><td colspan="8" align="center">No records found</td></tr>';
                    }

                    $output .= '<tr><td colspan="8" style="padding: 2px;"><font size="1">Total Number of Records: ' . $ctr . '</font></td></tr>
            </table>
            </td></tr>
            </table>';

            echo $output;
        }   
        
        // ------------------- SAVE TENANT -------------------
        if ($_POST["leasetbl"] == "SAVE") {
            date_default_timezone_set("Asia/Manila");

            $tname      = trim($_POST["tname"]);
            $contact    = trim($_POST["contact"]);
            $ddate      = $_POST["date"];
            $status     = "0"; // default NOT PAID

            // Validation
            if (!$tname || !$contact || !$ddate) {
                echo "Please fill all required fields correctly.";
                exit;
            }

            if (!preg_match('/^09\d{9}$/', $contact)) {
                echo "Contact number must be 11 digits and start with 09.";
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
                INSERT INTO lease_tbl(tname, contact, tstatus, ddate)
                VALUES ('$tname','$contact','$status','$ddate')
            ");

            if ($insertTenant) {
                echo "Tenant successfully added.";
            } else {
                echo "Database insert failed: " . mysqli_error($conn);
            }
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
            $contact    = trim($_POST["contact"]);
            $ddate      = $_POST["date"];

            // Validation
            if (!$tname || !$contact || !$ddate) {
                echo "Please fill all required fields correctly.";
                exit;
            }

            if (!preg_match('/^09\d{9}$/', $contact)) {
                echo "Contact number must be 11 digits and start with 09.";
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
                        contact='$contact',
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
            $tenant_id = mysqli_real_escape_string($conn, $_POST["tenant_id"]);
            $from = isset($_POST["from"]) ? trim($_POST["from"]) : "";
            $to   = isset($_POST["to"]) ? trim($_POST["to"]) : "";

            $dateFilter = "";
            $params = [];
            if ($from !== "" && $to !== "") {
                $dateFilter = " AND DATE(pdate) BETWEEN ? AND ? ";
                $params[] = $from;
                $params[] = $to;
            } else if ($from !== "") {
                $dateFilter = " AND DATE(pdate) >= ? ";
                $params[] = $from;
            } else if ($to !== "") {
                $dateFilter = " AND DATE(pdate) <= ? ";
                $params[] = $to;
            }

            // Detect if compensation column exists
            $hasComp = false;
            $colRes = mysqli_query($conn, "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lease_pay_tbl' AND COLUMN_NAME = 'compensation'");
            if ($colRes && mysqli_num_rows($colRes) > 0) { $hasComp = true; }

            // Detect if period columns exist
            $hasPeriod = false;
            $colRes = mysqli_query($conn, "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lease_pay_tbl' AND COLUMN_NAME = 'period_from'");
            if ($colRes && mysqli_num_rows($colRes) > 0) { $hasPeriod = true; }

            // Build SQL with optional date filters
            $sql = "SELECT pay_id, pdate, pay_month, pay_year, payment" .
                   ($hasComp ? ", compensation" : "") .
                   ($hasPeriod ? ", period_from, period_to" : "") . "
                    FROM lease_pay_tbl
                    WHERE tenant_id = ? " . $dateFilter . "
                    ORDER BY pay_year DESC, pay_month DESC, pdate DESC";

            // Use prepared statement for safety when date filters are present
            if (!empty($params)) {
                $stmt = mysqli_prepare($conn, $sql);
                // Build types: tenant_id int, dates strings
                $types = "i" . str_repeat("s", count($params));
                mysqli_stmt_bind_param($stmt, $types, $tenant_id, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
            } else {
                // No date filters: simple query
                $result = mysqli_query($conn, str_replace(" WHERE tenant_id = ? ", " WHERE tenant_id = '$tenant_id' ", $sql));
            }

            if (!$result) {
                die("SQL Error in HISTORY action: " . mysqli_error($conn) . " | Query: " . $sql);
            }

            // Group rows by full paid date (month/day/year)
            $groups = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $dateKey = date("Y-m-d", strtotime($row['pdate']));
                if (!isset($groups[$dateKey])) {
                    $groups[$dateKey] = [
                        "display" => date("M d, Y", strtotime($row['pdate'])),
                        "rows"    => [],
                        "total"   => 0
                    ];
                }

                $comp  = ($hasComp && isset($row['compensation'])) ? floatval($row['compensation']) : 0;
                $total = floatval($row['payment']) + $comp;

                $period_info = "—";
                if ($hasPeriod && !empty($row['period_from']) && !empty($row['period_to'])) {
                    $period_from  = date("M d, Y", strtotime($row['period_from']));
                    $period_to    = date("M d, Y", strtotime($row['period_to']));
                    $period_info  = $period_from . "<br/>to " . $period_to;
                }

                $groups[$dateKey]["rows"][] = [
                    "pay_id"   => $row['pay_id'],
                    "period"   => $period_info,
                    "amount"   => number_format($total, 2),
                    "payment"  => $row['payment'],
                    "comp"     => $hasComp ? $comp : 0,
                    "pay_month"=> $row['pay_month'],
                    "pay_year" => $row['pay_year'],
                    "pfrom"    => $hasPeriod ? $row['period_from'] : null,
                    "pto"      => $hasPeriod ? $row['period_to'] : null
                ];
                $groups[$dateKey]["total"] += $total;
            }

            $output = '<table class="table table-bordered">
                <tr class="no-hover">
                   
                </tr>';

            if (empty($groups)) {
                $output .= "<tr><td colspan='3'>No payment history found.</td></tr>";
            } else {
                $groupIndex = 0;
                foreach ($groups as $dateKey => $group) {
                    $groupIndex++;
                    $groupId = "hist_" . preg_replace('/[^a-zA-Z0-9]/', '_', $dateKey) . "_" . $groupIndex;
                    $entryCount = count($group["rows"]);
                    $output .= "
                    <tr class='no-hover'>
                        <td colspan='3'>
                            <button style='color:#672222;' class='btn btn-link p-0 history-toggle' data-bs-toggle='collapse' data-bs-target='#{$groupId}' aria-expanded='false' aria-controls='{$groupId}'>
                                <strong>{$group["display"]}</strong> ({$entryCount} entr" . ($entryCount > 1 ? "ies" : "y") . ") - Total: " . number_format($group["total"], 2) . "
                            </button>
                        </td>
                    </tr>
                    <tr id='{$groupId}' class='collapse'>
                        <td colspan='3' class='p-0'>
                            <table class='table table-sm mb-0'>
                                <tr>
                                    <th style='width:35%;'>Date Paid</th>
                                    <th style='width:30%;'>Period</th>
                                    <th style='width:20%;'>Amount</th>
                                    <th style='width:15%;'>Actions</th>
                                </tr>";
                    foreach ($group["rows"] as $row) {
                        $payId = htmlspecialchars($row["pay_id"]);
                        $periodSafe = $row["period"];
                        $paymentRaw = htmlspecialchars($row["payment"]);
                        $compRaw = htmlspecialchars($row["comp"]);
                        $payMonth = htmlspecialchars($row["pay_month"]);
                        $payYear  = htmlspecialchars($row["pay_year"]);
                        $pFrom    = $row["pfrom"] ? htmlspecialchars($row["pfrom"]) : '';
                        $pTo      = $row["pto"] ? htmlspecialchars($row["pto"]) : '';

                        $output .= "
                                <tr>
                                    <td>{$group["display"]}</td>
                                    <td><small>{$periodSafe}</small></td>
                                    <td>{$row["amount"]}</td>
                                    <td>
                                        <button class='btn btn-link p-0 history-edit' 
                                            data-tenant-id='".intval($tenant_id)."'
                                            data-pay-id='{$payId}'
                                            data-payment='{$paymentRaw}'
                                            data-compensation='{$compRaw}'
                                            data-pay-month='{$payMonth}'
                                            data-pay-year='{$payYear}'
                                            data-period-from='{$pFrom}'
                                            data-period-to='{$pTo}'
                                            title='Edit payment'>
                                            <img src=\"iconedit.png\" alt=\"edit\" style=\"width:20px; height:20px;\">
                                        </button>
                                        <button class='btn btn-link p-0 history-delete ms-1' 
                                            data-tenant-id='".intval($tenant_id)."'
                                            data-pay-id='{$payId}'
                                            title='Delete payment'>
                                            <img src=\"icondelete.png\" alt=\"delete\" style=\"width:20px; height:20px;\">
                                        </button>
                                    </td>
                                </tr>";
                    }
                    $output .= "
                            </table>
                        </td>
                    </tr>";
                }
            }

            $output .= "</table>";

            echo $output;
        }

        // ------------------- GET PAYMENT (single) -------------------
        if ($_POST["leasetbl"] == "GET_PAYMENT") {
            $pay_id = intval($_POST["pay_id"]);
            if ($pay_id <= 0) {
                echo json_encode(["status" => "error", "message" => "Invalid payment id."]);
                exit;
            }

            $stmt = mysqli_prepare($conn, "
                SELECT pay_id,
                       tenant_id,
                       payment,
                       COALESCE(compensation,0) AS compensation,
                       COALESCE(price_of_unit,0) AS price_of_unit,
                       pdate,
                       pay_month,
                       pay_year,
                       period_from,
                       period_to
                FROM lease_pay_tbl
                WHERE pay_id = ?
                LIMIT 1
            ");
            mysqli_stmt_bind_param($stmt, "i", $pay_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($res && mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                echo json_encode(["status" => "success", "data" => $row]);
            } else {
                echo json_encode(["status" => "error", "message" => "Payment not found."]);
            }
            exit;
        }

        // ------------------- ADD PAYMENT (Admin) -------------------
        if ($_POST["leasetbl"] == "ADD_PAYMENT") {
            $tenant_id    = intval($_POST["tenant_id"]);
            $price_of_unit = floatval($_POST["price_of_unit"]);
            $payment      = floatval($_POST["payment"]);
            $compensation = isset($_POST["compensation"]) ? floatval($_POST["compensation"]) : 0;
            $pdate        = $_POST["pdate"];
            $months       = isset($_POST["months"]) ? intval($_POST["months"]) : 1;
            $logtime      = date("H:i:s");

            if ($tenant_id <= 0 || $price_of_unit <= 0 || $payment <= 0 || empty($pdate)) {
                echo "Please fill all required fields correctly.";
                exit;
            }
            
            // Note: months can be 0 for partial payments (paunang bayad)

            // Allow partial payments (paunang bayad) - payment can be less than price_of_unit
            // Recalculate months based on actual payment
            $months_paid = floor($payment / $price_of_unit);
            $isPartialPayment = ($payment < $price_of_unit);
            $months = max(0, $months_paid); // Allow 0 months for partial payments
            $remainder = $payment - ($months_paid * $price_of_unit);

            // Ensure required columns exist
            $colRes = mysqli_query($conn, "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lease_pay_tbl' AND COLUMN_NAME = 'compensation'");
            if ($colRes && mysqli_num_rows($colRes) == 0) {
                @mysqli_query($conn, "ALTER TABLE lease_pay_tbl ADD COLUMN compensation DECIMAL(12,2) NOT NULL DEFAULT 0.00");
            }
            
            $colRes = mysqli_query($conn, "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lease_pay_tbl' AND COLUMN_NAME = 'price_of_unit'");
            if ($colRes && mysqli_num_rows($colRes) == 0) {
                @mysqli_query($conn, "ALTER TABLE lease_pay_tbl ADD COLUMN price_of_unit DECIMAL(12,2) DEFAULT NULL");
            }
            
            $colRes = mysqli_query($conn, "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lease_pay_tbl' AND COLUMN_NAME = 'period_from'");
            if ($colRes && mysqli_num_rows($colRes) == 0) {
                @mysqli_query($conn, "ALTER TABLE lease_pay_tbl ADD COLUMN period_from DATE DEFAULT NULL");
            }
            
            $colRes = mysqli_query($conn, "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lease_pay_tbl' AND COLUMN_NAME = 'period_to'");
            if ($colRes && mysqli_num_rows($colRes) == 0) {
                @mysqli_query($conn, "ALTER TABLE lease_pay_tbl ADD COLUMN period_to DATE DEFAULT NULL");
            }

            // Get tenant's starting date (ddate from lease_tbl)
            $tenantQuery = mysqli_query($conn, "SELECT ddate FROM lease_tbl WHERE tenant_id='$tenant_id'");
            $tenantRow = mysqli_fetch_assoc($tenantQuery);
            $tenantStartDate = $tenantRow ? $tenantRow['ddate'] : $pdate;

            $success_count = 0;
            $error_count = 0;

            // Get the last payment aggregate to detect partial months
            $lastAggQuery = mysqli_query($conn, "
                SELECT pay_month, pay_year,
                       SUM(payment + COALESCE(compensation,0)) AS total_paid,
                       MAX(price_of_unit) AS rate,
                       MAX(period_from) AS period_from,
                       MAX(period_to) AS period_to
                FROM lease_pay_tbl
                WHERE tenant_id='$tenant_id'
                GROUP BY pay_year, pay_month
                ORDER BY pay_year DESC, pay_month DESC
                LIMIT 1
            ");
            $lastPayQuery = mysqli_query($conn, "SELECT pay_month, pay_year, period_to FROM lease_pay_tbl WHERE tenant_id='$tenant_id' ORDER BY pay_year DESC, pay_month DESC LIMIT 1");
            $firstMonthStart = null;
            $partialOutstanding = 0;
            $lastMonth = null; $lastYear = null;
            if ($lastAggQuery && mysqli_num_rows($lastAggQuery) > 0) {
                $agg = mysqli_fetch_assoc($lastAggQuery);
                $lastMonth = intval($agg['pay_month']);
                $lastYear  = intval($agg['pay_year']);
                $lastTotal = floatval($agg['total_paid']);
                $lastRate  = floatval($agg['rate']) > 0 ? floatval($agg['rate']) : $price_of_unit;
                if ($lastRate > 0 && ($lastTotal + 0.009) < $lastRate) {
                    $partialOutstanding = $lastRate - $lastTotal;
                    // Apply to the same month first
                    $apply = min($payment, $partialOutstanding);
                    $updPartial = mysqli_query($conn, "UPDATE lease_pay_tbl 
                        SET payment = payment + '$apply', compensation = compensation + '$compensation', price_of_unit = IFNULL(NULLIF(price_of_unit,0), '$price_of_unit')
                        WHERE tenant_id='$tenant_id' AND pay_month='$lastMonth' AND pay_year='$lastYear'");
                    if ($updPartial) {
                        $success_count++;
                        $payment -= $apply;
                        $partialOutstanding -= $apply;
                    } else {
                        $error_count++;
                    }
                }
            }

            // If payment was fully consumed by partial catch-up, stop here
            if ($payment <= 0.009) {
                echo "Payment applied to outstanding balance.";
                exit;
            }

            // Recompute months/remainder after partial catch-up
            $months_paid = floor($payment / $price_of_unit);
            $months = max(0, $months_paid); // Allow 0 for partial payments
            $remainder = $payment - ($months_paid * $price_of_unit);
            $isPartialPayment = ($payment < $price_of_unit);
            
            // Handle partial payment (paunang bayad) - less than one month
            if ($isPartialPayment && $months == 0) {
                // Determine which month to apply this partial payment to
                $targetMonth = null;
                $targetYear = null;
                $targetMonthStart = null;
                $targetMonthEnd = null;
                
                // Re-fetch last payment for partial payment logic
                $lastPayQueryPartial = mysqli_query($conn, "SELECT pay_month, pay_year, period_to FROM lease_pay_tbl WHERE tenant_id='$tenant_id' ORDER BY pay_year DESC, pay_month DESC LIMIT 1");
                if ($lastPayQueryPartial && mysqli_num_rows($lastPayQueryPartial) > 0) {
                    $lastPayRowPartial = mysqli_fetch_assoc($lastPayQueryPartial);
                    // Check if last month is still incomplete
                    $lastMonth = intval($lastPayRowPartial['pay_month']);
                    $lastYear = intval($lastPayRowPartial['pay_year']);
                    
                    // Get total paid for last month
                    $lastMonthTotal = mysqli_query($conn, "
                        SELECT SUM(payment + COALESCE(compensation,0)) AS total 
                        FROM lease_pay_tbl 
                        WHERE tenant_id='$tenant_id' AND pay_month='$lastMonth' AND pay_year='$lastYear'
                    ");
                    $lastTotalRow = mysqli_fetch_assoc($lastMonthTotal);
                    $lastTotal = floatval($lastTotalRow['total']);
                    $lastRate = floatval($price_of_unit);
                    
                    // If last month is incomplete, apply to it
                    if ($lastTotal < $lastRate) {
                        $targetMonth = $lastMonth;
                        $targetYear = $lastYear;
                        $targetMonthStart = date('Y-m-01', strtotime("$lastYear-$lastMonth-01"));
                        $targetMonthEnd = date('Y-m-t', strtotime("$lastYear-$lastMonth-01"));
                    } else {
                        // Last month is complete, apply to next month
                        $nextMonthDate = date('Y-m-01', strtotime("$lastYear-$lastMonth-01 +1 month"));
                        $targetMonth = date('n', strtotime($nextMonthDate));
                        $targetYear = date('Y', strtotime($nextMonthDate));
                        $targetMonthStart = date('Y-m-01', strtotime($nextMonthDate));
                        $targetMonthEnd = date('Y-m-t', strtotime($nextMonthDate));
                    }
                } else {
                    // No previous payments - need to reset query result
                    mysqli_data_seek($lastPayQueryPartial, 0);
                    // No previous payments - apply to tenant's start month
                    if (!empty($tenantStartDate)) {
                        $targetMonthStart = date('Y-m-01', strtotime($tenantStartDate));
                        $targetMonth = date('n', strtotime($tenantStartDate));
                        $targetYear = date('Y', strtotime($tenantStartDate));
                        $targetMonthEnd = date('Y-m-t', strtotime($tenantStartDate));
                    } else {
                        // Fallback to current month
                        $targetMonthStart = date('Y-m-01');
                        $targetMonth = date('n');
                        $targetYear = date('Y');
                        $targetMonthEnd = date('Y-m-t');
                    }
                }
                
                // Insert or update partial payment
                $dupPartial = mysqli_query($conn, "SELECT 1 FROM lease_pay_tbl WHERE tenant_id='$tenant_id' AND pay_month='$targetMonth' AND pay_year='$targetYear'");
                if (mysqli_num_rows($dupPartial) == 0) {
                    $sqlPartial = "INSERT INTO lease_pay_tbl (tenant_id, payment, compensation, pdate, logtime, pay_month, pay_year, price_of_unit, period_from, period_to)
                            VALUES ('$tenant_id', '$payment', '$compensation', '$pdate', '$logtime', '$targetMonth', '$targetYear', '$price_of_unit', '$targetMonthStart', '$targetMonthEnd')";
                    if (mysqli_query($conn, $sqlPartial)) {
                        echo "Partial payment (paunang bayad) of " . number_format($payment, 2) . " successfully added for " . date("M Y", strtotime($targetMonthStart)) . ".";
                    } else {
                        echo "Error adding partial payment: " . mysqli_error($conn);
                    }
                } else {
                    // Update existing record
                    $updPartial = mysqli_query($conn, "UPDATE lease_pay_tbl 
                        SET payment = payment + '$payment', compensation = compensation + '$compensation', price_of_unit = IFNULL(NULLIF(price_of_unit,0), '$price_of_unit')
                        WHERE tenant_id='$tenant_id' AND pay_month='$targetMonth' AND pay_year='$targetYear'");
                    if ($updPartial) {
                        echo "Partial payment (paunang bayad) of " . number_format($payment, 2) . " successfully added to " . date("M Y", strtotime($targetMonthStart)) . ".";
                    } else {
                        echo "Error updating partial payment: " . mysqli_error($conn);
                    }
                }
                exit;
            }
            
            // For full payments (>= 1 month), continue with normal processing
            $lastPayRow = null;
            if ($lastPayQuery) {
                mysqli_data_seek($lastPayQuery, 0); // Reset pointer
                $lastPayRow = mysqli_fetch_assoc($lastPayQuery);
            }
            
            if ($lastPayRow) {
                // Start from the month after the last payment's period_to
                if (!empty($lastPayRow['period_to'])) {
                    $firstMonthStart = date('Y-m-01', strtotime($lastPayRow['period_to'] . ' +1 day'));
                } else {
                    // Fallback to last payment month + 1
                    $lastMonthRow = $lastPayRow['pay_month'];
                    $lastYearRow = $lastPayRow['pay_year'];
                    if ($lastMonthRow && $lastYearRow) {
                        $firstMonthStart = date('Y-m-01', strtotime("$lastYearRow-$lastMonthRow-01 +1 month"));
                    }
                }
            }
            
            // If still null or invalid, start from tenant's ddate month
            if (empty($firstMonthStart) || strtotime($firstMonthStart) === false) {
                if (!empty($tenantStartDate)) {
                    $firstMonthStart = date('Y-m-01', strtotime($tenantStartDate));
                } else {
                    // Final fallback to current month
                    $firstMonthStart = date('Y-m-01');
                }
            }

            $paymentPerMonth = $price_of_unit;

            // Calculate overall period start and end for the success message
            $periodStart = $firstMonthStart; // First month start date
            $periodEndDate = date('Y-m-t', strtotime($firstMonthStart . " +" . ($months - 1 + ($remainder > 0 ? 1 : 0)) . " months")); // Last month end date

            // Create payment records for each month
            for ($i = 0; $i < $months; $i++) {
                // Calculate the month for this iteration
                $targetDateStr = date('Y-m-01', strtotime($firstMonthStart . " +" . $i . " months"));
                $targetMonth = date('n', strtotime($targetDateStr));
                $targetYear = date('Y', strtotime($targetDateStr));
                
                $monthStart = date('Y-m-01', strtotime($targetDateStr));
                $monthEnd = date('Y-m-t', strtotime($targetDateStr));

                // Check if payment already exists for this month/year
                $dup = mysqli_query($conn, "SELECT 1 FROM lease_pay_tbl WHERE tenant_id='$tenant_id' AND pay_month='$targetMonth' AND pay_year='$targetYear'");
                
                if (mysqli_num_rows($dup) == 0) {
                    $sql = "INSERT INTO lease_pay_tbl (tenant_id, payment, compensation, pdate, logtime, pay_month, pay_year, price_of_unit, period_from, period_to)
                            VALUES ('$tenant_id', '$paymentPerMonth', '$compensation', '$pdate', '$logtime', '$targetMonth', '$targetYear', '$price_of_unit', '$monthStart', '$monthEnd')";
                    
                    if (mysqli_query($conn, $sql)) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                } else {
                    // If a record exists, add to it (treat as additional payment)
                    $upd = mysqli_query($conn, "UPDATE lease_pay_tbl 
                        SET payment = payment + '$paymentPerMonth', compensation = compensation + '$compensation', price_of_unit = IFNULL(NULLIF(price_of_unit,0), '$price_of_unit')
                        WHERE tenant_id='$tenant_id' AND pay_month='$targetMonth' AND pay_year='$targetYear'");
                    if ($upd) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                }
            }

            // Apply any remainder to the next month as advance/partial payment
            if ($remainder > 0.009) { // small threshold to avoid float noise
                $nextMonthDateStr = date('Y-m-01', strtotime($firstMonthStart . " +" . $months . " months"));
                $nextMonth = date('n', strtotime($nextMonthDateStr));
                $nextYear = date('Y', strtotime($nextMonthDateStr));
                $nextStart = date('Y-m-01', strtotime($nextMonthDateStr));
                $nextEnd   = date('Y-m-t', strtotime($nextMonthDateStr));

                $dupRem = mysqli_query($conn, "SELECT 1 FROM lease_pay_tbl WHERE tenant_id='$tenant_id' AND pay_month='$nextMonth' AND pay_year='$nextYear'");
                if (mysqli_num_rows($dupRem) == 0) {
                    $sqlRem = "INSERT INTO lease_pay_tbl (tenant_id, payment, compensation, pdate, logtime, pay_month, pay_year, price_of_unit, period_from, period_to)
                            VALUES ('$tenant_id', '$remainder', '$compensation', '$pdate', '$logtime', '$nextMonth', '$nextYear', '$price_of_unit', '$nextStart', '$nextEnd')";
                    if (mysqli_query($conn, $sqlRem)) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                } else {
                    $updRem = mysqli_query($conn, "UPDATE lease_pay_tbl 
                        SET payment = payment + '$remainder', compensation = compensation + '$compensation', price_of_unit = IFNULL(NULLIF(price_of_unit,0), '$price_of_unit')
                        WHERE tenant_id='$tenant_id' AND pay_month='$nextMonth' AND pay_year='$nextYear'");
                    if ($updRem) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                }
            }

            if ($success_count > 0) {
                echo "Successfully added $success_count payment record(s) for " . date("M d, Y", strtotime($periodStart)) . " to " . date("M d, Y", strtotime($periodEndDate)) . ".";
                if ($error_count > 0) {
                    echo " $error_count payment(s) were skipped (already exist).";
                }
            } else {
                echo "No payments were added. All months may already have payments or an error occurred.";
            }
            exit;
        }

        // ------------------- ADD ADVANCE PAYMENT (Multiple Months) -------------------
        if ($_POST["leasetbl"] == "ADD_ADVANCE_PAYMENT") {
            $tenant_id   = intval($_POST["tenant_id"]);
            $payment     = floatval($_POST["payment"]);
            $compensation= isset($_POST["compensation"]) ? floatval($_POST["compensation"]) : 0;
            $pdate       = $_POST["pdate"];
            $months_to_pay = intval($_POST["months_to_pay"]);
            $logtime     = date("H:i:s");
            $current_month = date("n");
            $current_year = date("Y");

            if ($tenant_id <= 0 || $payment <= 0 || empty($pdate) || $months_to_pay <= 0) {
                echo "Please fill all required fields correctly.";
                exit;
            }

            // Ensure compensation column exists
            $colRes = mysqli_query($conn, "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lease_pay_tbl' AND COLUMN_NAME = 'compensation'");
            if ($colRes && mysqli_num_rows($colRes) == 0) {
                @mysqli_query($conn, "ALTER TABLE lease_pay_tbl ADD COLUMN compensation DECIMAL(12,2) NOT NULL DEFAULT 0.00");
            }

            $success_count = 0;
            $error_count = 0;

            // Create payments for multiple months
            for ($i = 0; $i < $months_to_pay; $i++) {
                $target_month = $current_month + $i;
                $target_year = $current_year;
                
                // Handle year overflow
                if ($target_month > 12) {
                    $target_month = $target_month - 12;
                    $target_year = $target_year + 1;
                }

                // Check if payment already exists for this month/year
                $dup = mysqli_query($conn, "SELECT 1 FROM lease_pay_tbl WHERE tenant_id='$tenant_id' AND pay_month='$target_month' AND pay_year='$target_year'");
                
                if (mysqli_num_rows($dup) == 0) {
                    $sql = "INSERT INTO lease_pay_tbl (tenant_id, payment, compensation, pdate, logtime, pay_month, pay_year)
                            VALUES ('$tenant_id', '$payment', '$compensation', '$pdate', '$logtime', '$target_month', '$target_year')";
                    
                    if (mysqli_query($conn, $sql)) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                } else {
                    $error_count++;
                }
            }

            if ($success_count > 0) {
                echo "Successfully added $success_count advance payment(s).";
                if ($error_count > 0) {
                    echo " $error_count payment(s) were skipped (already exist or error occurred).";
                }
            } else {
                echo "No payments were added. All months may already have payments or an error occurred.";
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

        // ------------------- GET PAYMENT (for editing) -------------------
        if ($_POST["leasetbl"] == "GET_PAYMENT") {
            $pay_id = intval($_POST["pay_id"]);
            if ($pay_id <= 0) {
                echo json_encode(["status" => "error", "message" => "Invalid payment ID."]);
                exit;
            }

            // Ensure columns exist that we want to return
            @mysqli_query($conn, "ALTER TABLE lease_pay_tbl ADD COLUMN price_of_unit DECIMAL(12,2) DEFAULT NULL");
            @mysqli_query($conn, "ALTER TABLE lease_pay_tbl ADD COLUMN compensation DECIMAL(12,2) NOT NULL DEFAULT 0.00");
            @mysqli_query($conn, "ALTER TABLE lease_pay_tbl ADD COLUMN period_from DATE DEFAULT NULL");
            @mysqli_query($conn, "ALTER TABLE lease_pay_tbl ADD COLUMN period_to DATE DEFAULT NULL");

            $sql = "SELECT pay_id, tenant_id, payment, compensation, price_of_unit, pdate, pay_month, pay_year, period_from, period_to
                    FROM lease_pay_tbl
                    WHERE pay_id = '$pay_id'
                    LIMIT 1";
            $res = mysqli_query($conn, $sql);
            if ($row = mysqli_fetch_assoc($res)) {
                echo json_encode([
                    "status" => "success",
                    "data" => [
                        "pay_id"       => intval($row["pay_id"]),
                        "tenant_id"    => intval($row["tenant_id"]),
                        "payment"      => floatval($row["payment"]),
                        "compensation" => floatval($row["compensation"]),
                        "price_of_unit"=> isset($row["price_of_unit"]) ? floatval($row["price_of_unit"]) : 0,
                        "pdate"        => $row["pdate"],
                        "pay_month"    => intval($row["pay_month"]),
                        "pay_year"     => intval($row["pay_year"]),
                        "period_from"  => $row["period_from"],
                        "period_to"    => $row["period_to"]
                    ]
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Payment not found."]);
            }
            exit;
        }

        // ------------------- UPDATE PAYMENT -------------------
        if ($_POST["leasetbl"] == "UPDATE_PAYMENT") {
            $pay_id = intval($_POST["pay_id"]);
            $tenant_id = intval($_POST["tenant_id"]);
            $price_of_unit = floatval($_POST["price_of_unit"]);
            $payment = floatval($_POST["payment"]);
            $compensation = isset($_POST["compensation"]) ? floatval($_POST["compensation"]) : 0;
            $pdate = $_POST["pdate"];
            $logtime = date("H:i:s");

            if ($pay_id <= 0 || $tenant_id <= 0 || $price_of_unit <= 0 || $payment <= 0 || empty($pdate)) {
                echo "Please fill all required fields correctly.";
                exit;
            }

            // Get the original payment record to know which month it belongs to
            $origQuery = mysqli_query($conn, "SELECT pay_month, pay_year, period_to, payment AS orig_payment FROM lease_pay_tbl WHERE pay_id='$pay_id'");
            if (!$origQuery || mysqli_num_rows($origQuery) == 0) {
                echo "Payment record not found.";
                exit;
            }
            $origRow = mysqli_fetch_assoc($origQuery);
            $origMonth = intval($origRow['pay_month']);
            $origYear = intval($origRow['pay_year']);
            $origPeriodTo = $origRow['period_to'];
            $origPayment = floatval($origRow['orig_payment']);

            $success_count = 0;
            $error_count = 0;

            // If payment is less than or equal to monthly rate, just update the record
            if ($payment <= $price_of_unit) {
                $update = mysqli_query($conn, "
                    UPDATE lease_pay_tbl 
                    SET payment = '$payment',
                        compensation = '$compensation',
                        price_of_unit = '$price_of_unit',
                        pdate = '$pdate'
                    WHERE pay_id = '$pay_id'
                ");

                if ($update) {
                    echo "Payment record updated successfully.";
                } else {
                    echo "Failed to update payment: " . mysqli_error($conn);
                }
                exit;
            }

            // Payment exceeds monthly rate - update current month and apply excess to future months
            // Step 1: Update current month's payment to the monthly rate
            $update = mysqli_query($conn, "
                UPDATE lease_pay_tbl 
                SET payment = '$price_of_unit',
                    compensation = '$compensation',
                    price_of_unit = '$price_of_unit',
                    pdate = '$pdate'
                WHERE pay_id = '$pay_id'
            ");

            if ($update) {
                $success_count++;
            } else {
                $error_count++;
                echo "Failed to update payment: " . mysqli_error($conn);
                exit;
            }

            // Step 2: Calculate excess payment
            $excess = $payment - $price_of_unit;
            
            // Step 3: Calculate how many full months the excess covers
            $months_paid = floor($excess / $price_of_unit);
            $remainder = $excess - ($months_paid * $price_of_unit);

            // Step 4: Determine starting month for excess payments
            $firstMonthStart = null;
            if (!empty($origPeriodTo)) {
                // Start from the month after the current payment's period_to
                $firstMonthStart = date('Y-m-01', strtotime($origPeriodTo . ' +1 day'));
            } else {
                // Fallback to current month + 1
                $firstMonthStart = date('Y-m-01', strtotime("$origYear-$origMonth-01 +1 month"));
            }

            // Step 5: Create payment records for each month covered by excess
            if ($months_paid > 0) {
                for ($i = 0; $i < $months_paid; $i++) {
                    $targetDateStr = date('Y-m-01', strtotime($firstMonthStart . " +" . $i . " months"));
                    $targetMonth = date('n', strtotime($targetDateStr));
                    $targetYear = date('Y', strtotime($targetDateStr));
                    
                    $monthStart = date('Y-m-01', strtotime($targetDateStr));
                    $monthEnd = date('Y-m-t', strtotime($targetDateStr));

                    // Check if payment already exists for this month/year
                    $dup = mysqli_query($conn, "SELECT 1 FROM lease_pay_tbl WHERE tenant_id='$tenant_id' AND pay_month='$targetMonth' AND pay_year='$targetYear'");
                    
                    if (mysqli_num_rows($dup) == 0) {
                        $sql = "INSERT INTO lease_pay_tbl (tenant_id, payment, compensation, pdate, logtime, pay_month, pay_year, price_of_unit, period_from, period_to)
                                VALUES ('$tenant_id', '$price_of_unit', '$compensation', '$pdate', '$logtime', '$targetMonth', '$targetYear', '$price_of_unit', '$monthStart', '$monthEnd')";
                        
                        if (mysqli_query($conn, $sql)) {
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                    } else {
                        // If a record exists, add to it (treat as additional payment)
                        $upd = mysqli_query($conn, "UPDATE lease_pay_tbl 
                            SET payment = payment + '$price_of_unit', compensation = compensation + '$compensation', price_of_unit = IFNULL(NULLIF(price_of_unit,0), '$price_of_unit')
                            WHERE tenant_id='$tenant_id' AND pay_month='$targetMonth' AND pay_year='$targetYear'");
                        if ($upd) {
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                    }
                }
            }

            // Step 6: Apply any remainder to the next month as advance/partial payment
            if ($remainder > 0.009) { // small threshold to avoid float noise
                $nextMonthDateStr = date('Y-m-01', strtotime($firstMonthStart . " +" . $months_paid . " months"));
                $nextMonth = date('n', strtotime($nextMonthDateStr));
                $nextYear = date('Y', strtotime($nextMonthDateStr));
                $nextStart = date('Y-m-01', strtotime($nextMonthDateStr));
                $nextEnd   = date('Y-m-t', strtotime($nextMonthDateStr));

                $dupRem = mysqli_query($conn, "SELECT 1 FROM lease_pay_tbl WHERE tenant_id='$tenant_id' AND pay_month='$nextMonth' AND pay_year='$nextYear'");
                if (mysqli_num_rows($dupRem) == 0) {
                    $sqlRem = "INSERT INTO lease_pay_tbl (tenant_id, payment, compensation, pdate, logtime, pay_month, pay_year, price_of_unit, period_from, period_to)
                            VALUES ('$tenant_id', '$remainder', '$compensation', '$pdate', '$logtime', '$nextMonth', '$nextYear', '$price_of_unit', '$nextStart', '$nextEnd')";
                    if (mysqli_query($conn, $sqlRem)) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                } else {
                    $updRem = mysqli_query($conn, "UPDATE lease_pay_tbl 
                        SET payment = payment + '$remainder', compensation = compensation + '$compensation', price_of_unit = IFNULL(NULLIF(price_of_unit,0), '$price_of_unit')
                        WHERE tenant_id='$tenant_id' AND pay_month='$nextMonth' AND pay_year='$nextYear'");
                    if ($updRem) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                }
            }

            // Step 7: Return success message
            if ($success_count > 0) {
                $msg = "Payment record updated successfully.";
                if ($months_paid > 0 || $remainder > 0.009) {
                    $msg .= " Excess payment of " . number_format($excess, 2) . " applied to " . ($months_paid + ($remainder > 0.009 ? 1 : 0)) . " future month(s).";
                }
                if ($error_count > 0) {
                    $msg .= " $error_count payment(s) had errors.";
                }
                echo $msg;
            } else {
                echo "Payment record updated, but failed to apply excess to future months.";
            }
            exit;
        }

        // ------------------- DELETE PAYMENT -------------------
        if ($_POST["leasetbl"] == "DELETE_PAYMENT") {
            $pay_id = intval($_POST["pay_id"]);
            if ($pay_id <= 0) {
                echo "Invalid payment ID.";
                exit;
            }

            $del = mysqli_query($conn, "DELETE FROM lease_pay_tbl WHERE pay_id='$pay_id'");
            if ($del) {
                echo "Payment record deleted.";
            } else {
                echo "Failed to delete payment: " . mysqli_error($conn);
            }
            exit;
        }
    }
?>
