<?php
    $conn = mysqli_connect('localhost', 'root', 'root', 'tmc_admin_db');

    if (isset($_POST["action"])) {
        if ($_POST["action"] == "LOAD") {
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

            <table style="width: 100%;  border-radius: 20px;">
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
                        <button title="Refresh" id="refreshBtn" style="padding:2px; background:none; border:none; margin-right:5px;">
                            <img src="reficon.png" alt="refresh" style="width:25px; height:25px;">
                        </button>
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
    <td id="border" style="width: 40%; background: #CCCCCC; padding: 5px; vertical-align: middle;"><center><strong>TENANT NAME</strong></center></td>
    <td id="border" style="width: 30%; background: #CCCCCC; padding: 5px; vertical-align: middle;"><center><strong>DUE DATE</strong></center></td>
    <td id="border" style="width: 20%; background: #CCCCCC; padding: 5px; vertical-align: middle;"><center><strong>STATUS</strong></center></td>
    <td id="border" style="width: 10%; background: #CCCCCC; padding: 5px; vertical-align: top;"></td>
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
                $output .= '<tr><td colspan="4" align="center">No records found</td></tr>';
            }

            $output .= '<tr><td colspan="4" style="padding: 2px;"><font size="1">Total Number of Records: ' . $ctr . '</font></td></tr>
    </table>
    </td></tr>
    </table>';

            echo $output;
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

    }



    if ($_POST["action"] == "HISTORY") {
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
                    
                    <td>" . number_format($total, 2) . "</td>
                </tr>";
            }
        } else {
            $output .= "<tr><td colspan='4'>No payment history found.</td></tr>";
        }

        $output .= "</table>";

        echo $output;
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
?>
