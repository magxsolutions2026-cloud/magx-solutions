<?php
    session_start();
    $conn = mysqli_connect('localhost', 'root', 'root', 'tmc_admin_db');

    $tenant_options = "";
    $sql = "SELECT tenant_id, tname FROM lease_tbl";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // 👇 store tenant_id as value
            $tenant_options .= "<option value=\"" . htmlspecialchars($row['tenant_id']) . "\">" 
                            . htmlspecialchars($row['tname']) . "</option>\n";
        }
    } else {
        $tenant_options = "<option disabled>No tenants found</option>";
    }






    if (isset($_POST["addtenant"])) {
        date_default_timezone_set("Asia/Manila");
       

        // Collect form data
        $tname       = $_POST["tname"];
        $rental_fee  = $_POST["rental_fee"];
        $contact     = $_POST["contact"];
        $ddate        = $_POST["date"];
        $status      = $_POST["status"]; // 1 = Payed, 0 = Not Payed

        // Basic validation
        if (
            $tname == "" || $rental_fee == "" ||
            $contact == "" || $ddate == "" || $status == ""
        ) {
            echo "<script>alert('Kindly fill all information');</script>";
        } else {
           
            $con = mysqli_connect('localhost', 'root', 'root', 'tmc_admin_db');

            // Ensure compensation column exists (optional auto-add)
            $colRes = mysqli_query($con, "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lease_pay_tbl' AND COLUMN_NAME = 'compensation'");
            if ($colRes && mysqli_num_rows($colRes) == 0) {
                @mysqli_query($con, "ALTER TABLE lease_pay_tbl ADD COLUMN compensation DECIMAL(12,2) NOT NULL DEFAULT 0.00");
            }

            // Optional: Check for duplicate entry for this tenant on this date
            $check = mysqli_query($con, "SELECT * FROM lease_tbl WHERE tname='$tname' AND ddate='$ddate'");
            $existing = mysqli_fetch_row($check);

            if ($existing) {
                echo "<script>alert('Tenant already added for this date.');</script>";
            } else {
                // Insert into tenants_tbl
                $sql = "INSERT INTO lease_tbl(tname, rent_fee, contact, tstatus, ddate)
                        VALUES ('$tname', '$rental_fee', '$contact', '$status' ,'$ddate')";
                mysqli_query($con, $sql);

                echo "<script>alert('Tenant successfully added.');</script>";
            }
        }
    }



    if (isset($_POST["addpay"])) {
        $tenant_id = $_POST["tenant_id"];  
        $payment   = $_POST["payment"];
        $compensation = isset($_POST["compensation"]) && $_POST["compensation"] !== "" ? $_POST["compensation"] : 0;
        $pdate     = $_POST["pdate"];
        $logtime   = date("H:i:s");
        $month     = date("n");
        $year      = date("Y");

        if ($tenant_id == "" || $payment == "" || $pdate == "") {
            echo "<script>alert('Kindly fill all information');</script>";
        } else {
            $con = mysqli_connect('localhost', 'root', 'root', 'tmc_admin_db');

            // ✅ Check if payment already exists for this tenant in the current month/year
            $check = mysqli_query($con, "SELECT * FROM lease_pay_tbl 
                                        WHERE tenant_id='$tenant_id' 
                                        AND pay_month='$month' 
                                        AND pay_year='$year'");
            if (mysqli_num_rows($check) > 0) {
                echo "<script>alert('Payment for this month already exists for this tenant.');</script>";
            } else {
                $sql = "INSERT INTO lease_pay_tbl (tenant_id, payment, compensation, pdate, logtime, pay_month, pay_year)
                        VALUES ('$tenant_id', '$payment', '$compensation', '$pdate', '$logtime', '$month', '$year')";
                if (mysqli_query($con, $sql)) {
                    echo "<script>alert('Payment successfully added for this month.');</script>";
                } else {
                    echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
                }
            }

            mysqli_close($con);
        }
    }


?>




<!DOCTYPE html>
<html>
    <head>
        
        <meta charset="utf-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Lease Main Page</title>
        <link rel="icon" type="png" href="tmclogo.png">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">



        <style>
            #eye:hover{
                transform: scale(1.2);
            }
            header {
                background: linear-gradient(90deg, #672222, #8c2f2f);
                color: white;
                font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
                text-shadow: 2px 2px 2px black;
                padding: 10px 20px;
                display: flex;
                align-items: center;
                gap: 18px;
            }
            /* Side Navigation Styles */
            .sidenav {
                background-color: rgba(63, 4, 4, 0.79);
                height: 100%;
                width: 0;
                position: fixed;
                z-index: 1050;
                top: 0;
                right: 0;
                overflow-x: hidden;
                padding-top: 60px;
                transition: 0.3s;
             }

            .sidenav a,#admininput {
                padding: 10px 30px;
                text-decoration: none;
                font-size: 18px;
                color: #fff;
                display: block;
                
            }

            .sidenav a:hover {
                background-color: white;
                border-radius: 10px;
                color: #672222;
            }

            .sidenav .closebtn {
                position: absolute;
                top: 10px;
                right: 25px;
                font-size: 36px;
                color: white;
            }

            #sidenav:hover{
                transform: scale(1.2);
            }
             #sidenav {
                cursor: pointer;
                transition: transform 0.3s ease;
            }
            /* End Side Navigation Styles */

            #addRecord {
                position: fixed;
                bottom: 20px;
                right: 20px;
                cursor: pointer;
                padding: 10px;
               
            }
            #addsales{
                display: none;
            }

            /* loader animation*/
            .loader-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.68); 
                justify-content: center;
                align-items: center;
                z-index: 9999;
                display: none;
            }

            
            .circle-loader {
                width: 50px;
                height: 50px;
                border: 6px solid rgba(0, 0, 0, 0.1);
                border-top: 6px solid #a50606ff;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                
            }


            
            
            
            



            
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
            /* end ng loader */


            .modal-header{
                background: linear-gradient(90deg, #672222, #8c2f2f);
                font-weight: bold;
                color:white;
                padding-left: 30px;
                padding-right: 30px;
            }
            .modal-content{
                box-shadow: 0 8px 24px rgba(0,0,0,0.2);
                border-radius:30px; border:none;
                overflow: hidden;
            }
            
            
            


            .report-header {
                background: linear-gradient(90deg, #672222, #8c2f2f);
                color: #FFF;
                border-radius: 8px 8px 0 0;
                padding: 12px 16px;
            }

            /* Flex layout */
            .report-header-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 12px;
            }

            /* Title */
            .report-title {
                font-size: 15px;
                font-weight: bold;
                letter-spacing: 1px;
                text-shadow: 1px 1px 3px rgba(0,0,0,0.6);
            }

            /* Date filter section */
            .date-filter {
                display: flex;
                gap: 8px;
                align-items: center;
                flex-wrap: wrap;
                font-size: 12px;
            }

            /* Date input fields */
            .date-input {
                padding: 6px 10px;
                font-size: 13px;
                border-radius: 6px;
                border: 1px solid #ccc;
                outline: none;
                transition: 0.3s;
            }
            .date-input:focus {
                border: 1px solid #8c2f2f;
                box-shadow: 0 0 6px rgba(140,47,47,0.6);
            }

            /* Payment history modal filters */
            .history-filters {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 5px 10px;
                
                transition: all 0.3s ease;
            }

            .history-filters label {
                color: #fff;
                font-size: 12px;
                margin-bottom: 0;
                opacity: 0.9;
            }

            .history-filters input {
                border: none;
                outline: none;
                background: rgba(255,255,255,0.9);
                border-radius: 6px;
                padding: 6px 10px;
                font-size: 13px;
                color: #672222;
                min-width: 140px;
            }

            .history-filters .btn-filter {
                background: #ffffff;
                border: none;
                padding: 8px 14px;
                border-radius: 10px;
                cursor: pointer;
                font-weight: bold;
                font-size: 13px;
                color: #672222;
                transition: all 0.3s ease;
                box-shadow: 0 3px 6px rgba(0,0,0,0.2);
                white-space: nowrap;
            }

            .history-filters .btn-filter:hover {
                background: #672222;
                color: white;
                transform: scale(1.03);
            }

            /* Responsive */
            @media (max-width: 768px) {
                .history-filters {
                    width: 100%;
                    flex-wrap: wrap;
                    margin-top: 10px;
                }
                .history-filters input {
                    width: 100%;
                }
                .history-filters .btn-filter {
                    width: 100%;
                    justify-content: center;
                    display: flex;
                }
                .history-header-flex {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 10px;
                }
            }

            /* Buttons */
            .btn-filter,
            .btn-reset {
                padding: 6px 14px;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-size: 13px;
                font-weight: bold;
                transition: 0.3s;
            }

            /* Filter button */
            .btn-filter {
                background: #ffd166;
                color: #333;
            }
            .btn-filter:hover {
                background: #ffbe0b;
                transform: translateY(-2px);
                box-shadow: 0 3px 6px rgba(0,0,0,0.3);
            }

            /* Reset button */
            .btn-reset {
                background: #ef233c;
                color: #fff;
            }
            .btn-reset:hover {
                background: #d90429;
                transform: translateY(-2px);
                box-shadow: 0 3px 6px rgba(0,0,0,0.3);
            }


             /* floating add button */
            #addpayment {
                background: radial-gradient(ellipse at center, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
                border: 2px solid rgba(255,255,255,0.7);
                box-shadow: 0 10px 24px rgba(0,0,0,0.35), inset 0 0 0 2px rgba(255,255,255,0.25);
                transition:
                    transform 0.2s ease,
                    box-shadow 0.2s ease,
                    filter 0.2s ease,
                    opacity 0.2s ease;
                opacity: 0.95;
            }
            #addpayment:hover {
                filter: drop-shadow(6px 10px 14px rgba(0,0,0,0.4));
                box-shadow: 0 14px 28px rgba(0,0,0,0.45), inset 0 0 0 2px rgba(255,255,255,0.35);
                transform: scale(1.08);
                opacity: 1;
            }
            #addpayment:active {
                transform: scale(0.98);
                box-shadow: 0 6px 14px rgba(0,0,0,0.35), inset 0 0 0 2px rgba(255,255,255,0.2);
            }
        </style>
        <link rel="stylesheet" href="assets/css/admin-unified-theme.css?v=20260217">
    </head>
    <body>
         <header>
            <img src="tmclogo.png" width="3%" height="auto" class="img-fluid" alt="logo" id="logo"/>
            <div class="header-title">
                <h1 class="h2 h3-md m-0" style="margin:0;">COMMERCIAL LEASING</h1>
                
            </div>
            <img src="ico.png" id="sidenav" height="auto" style="position: absolute; right: 2%;"  class="img-fluid" alt="logo"/>
        </header>
        
        <!-- Loader Overlay -->
        <div class="loader-overlay">
            <div class="circle-loader"></div>
        </div>
        
        <!-- Floating add button for Payment -->
        <img src="add.png" id="addpayment" alt="add" title="Add Payment" onclick="showAddPaymentModal(); return false;" style="position: fixed; right: 28px; bottom: 28px; width: 60px; height: 60px; border-radius: 50%; cursor: pointer; z-index: 1100; filter: drop-shadow(5px 5px 10px rgba(0,0,0,0.3)); transition: transform 0.2s ease;">


        <div id="mySidenav" class="sidenav">
           
            <!-- User Profile Section -->
            <div class="user-profile-section" style="padding: 20px 15px; border-bottom: 1px solid white; border-top: 1px solid white; margin-bottom: 10px;">
                <div style="display: flex; align-items: center; position: relative;">
                    <img src="user.ico" height="auto" class="img-fluid d-block me-2" id="userico" style="width: 35px; margin-top:3px; cursor: pointer; filter: brightness(0) invert(1); position:absolute; left:7px;"/>
                    <div style="margin-left: 50px;">
                        <div style="color: #fff; font-weight: bold; font-size: 14px; margin-bottom: 2px;">
                            <?php 
                                $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
                                // Extract only the username part (before any space or special characters)
                                $displayName = explode(' ', $username)[0];
                                echo htmlspecialchars($displayName); 
                            ?>
                        </div>             
                    </div>

                    <button type="button" id="closeBtn"style="background-color:white; color: white; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);"class="btn-close" aria-label="Close"></button>

                </div>
            </div>
            <a href="#" id="addbtn">Add Tenant</a>
            <a href="#" id="adminlogin" class="change_userpass">Change User & Pass</a>
            <a href="index.php">Exit</a>
            
        </div>
        

        <div id="data_table" style="margin: 5px; margin-top:15px; margin-left: 50px; margin-right: 50px; overflow-y: auto;box-shadow: 0 8px 24px rgba(0,0,0,0.2); border-radius:10px; border:none;">
            <!-- Records will display inside of this tag -->
             
        </div>
        
        <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header">
                    <div class="history-header-flex" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; width:100%; justify-content:space-between;">
                        <h5 class="modal-title mb-0">Payment History</h5>
                        <div class="history-filters">
                            <div>
                                <label for="history_from">From</label>
                                <input type="date" id="history_from" aria-label="History from date">
                            </div>
                            <div>
                                <label for="history_to">To</label>
                                <input type="date" id="history_to" aria-label="History to date">
                            </div>
                            <button type="button" class="btn-filter" id="history_filter_btn">Filter</button>
                        </div>
                    </div>
                    <button style="background-color:white;" type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="historyContent">
                    <!-- Payment history loads here -->
                </div>
                </div>
            </div>
        </div>


        <form autocomplete="off" method="POST" action="leasemain.php">
            <!-- Scrollable modal -->
            <div class="modal fade" data-bs-backdrop="static" id="addtenant" tabindex="-1">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                           <img src="tmclogo.png"  height="auto" class="img-fluid d-block me-2" id="logo" style="width: 35px; height: auto; cursor: pointer;"/>
                            <h1 class="modal-title fs-5" id="exampleModalLabel" style="color:white; margin-left:15px;">ADD TENANT</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" style="background-color: white;"></button>
                        </div>
                        <div class="modal-body">
                            <form>
                                
    
                                <div class="mb-3">
                                    <label for="tname" class="col-form-label" style="color:#672222;">TENANT NAME :</label>
                                    <input type="text" name="tname" class="form-control" id="tname">
                                </div>


                                <div class="mb-3">
                                    <label for="contact" class="col-form-label" style="color:#672222;">CONTACT :</label>
                                    <input type="text"
                                           name="contact"
                                           class="form-control"
                                           id="contact"
                                           inputmode="numeric"
                                           pattern="^09[0-9]{9}$"
                                           maxlength="11"
                                           placeholder="09XXXXXXXXX">
                                </div>

                                <div class="mb-3">
                                    <label for="date" class="col-form-label" style="color:#672222;">DATE :</label>
                                    <input type="date" name="date" class="form-control" id="date">
                                </div>
                                



                                
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="cancel1" class="btn btn-secondary" data-bs-dismiss="modal" style="transition: 0.3s; margin-right:5%; border:none;">CANCEL</button>
                            <button type="button" id="submittenant" class="btn btn-primary" style="transition: 0.3s; background-color:#672222; border:none;">SUBMIT</button>
                        </div>
                    </div>
                </div>
            </div> 
        </form>



        <form autocomplete="off" method="POST" action="leasemain.php">
            <!-- Scrollable modal -->
            <div class="modal fade" data-bs-backdrop="true" id="addpaymod" tabindex="-1" data-bs-keyboard="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                           <img src="tmclogo.png"  height="auto" class="img-fluid d-block me-2" id="logo" style="width: 35px; height: auto; cursor: pointer;"/>
                            <h1 class="modal-title fs-5" id="exampleModalLabel" style="color:white; margin-left:15px;">ADD PAYMENT</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" style="background-color: white;"></button>
                        </div>
                        <div class="modal-body">
                            <form>
                                
                               
                                <!-- TENANT NAME -->
                                
                                 <div class="mb-3">
                                    <label for="tname" class="col-form-label" style="color:#672222;">TENANT NAME :</label>
                                    <select name="tenant_id" class="form-control" id="tenant_id" required>
                                        <option value="" disabled selected>— Select a tenant —</option>
                                        <?= $tenant_options ?>
                                    </select>
                                </div>

                                <!-- PRICE OF UNIT -->
                                <div class="mb-3">
                                    <label for="price_of_unit" class="col-form-label" style="color:#672222;">PRICE OF UNIT (Per Month) :</label>
                                    <input type="number" step="0.01" min="0" name="price_of_unit" class="form-control" id="price_of_unit" required>
                                </div>

                                <!-- PAYMENT -->
                                <div class="mb-3">
                                    <label for="payment" class="col-form-label" style="color:#672222;">PAYMENT (Total Amount) :</label>
                                    <input type="text" name="payment" class="form-control" id="payment" required>
                                    <small class="text-muted" id="months_info" style="display:none;"></small>
                                </div>

                                <!-- COMPENSATION -->
                                <div class="mb-3" style="display: none;">
                                    <label for="compensation" class="col-form-label" style="color:#672222;">COMPENSATION :</label>
                                    <input type="number" step="0.01" min="0" name="compensation" class="form-control" id="compensation">
                                </div>

                                <!-- DATE -->
                                <div class="mb-3">
                                    <label for="pdate" class="col-form-label" style="color:#672222;">DATE :</label>
                                    <input type="date" name="pdate" class="form-control" id="pdate" required>
                                </div>

                       

                                



                                
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="cancel2" class="btn btn-secondary" data-bs-dismiss="modal" onclick="closePaymentModal();" style="transition: 0.3s; margin-right:5%; border:none;">CANCEL</button>
                            <input type="submit" id="submitpayment" name="addpay" class="btn btn-primary" style="transition: 0.3s; background-color:#672222; border:none;" value="SUBMIT">
                        </div>
                    </div>
                </div>
            </div> 
        </form>

        <!-- Change Pass & User Modal -->
        <div class="modal fade" id="changePassModal" tabindex="-1" aria-labelledby="changePassModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(90deg, #672222, #8c2f2f); color: white;">
                        <h5 class="modal-title" id="changePassModalLabel">Change Password & Username</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="changePassForm">
                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="currentUsername" class="form-label">Current Username</label>
                                <input type="text" placeholder="Enter Username" class="form-control" id="currentUsername" required>
                            </div>
                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="currentPassword" class="form-label">Current Password</label>
                                <input type="password" placeholder="Enter Password"  class="form-control" id="currentPassword" required>
                            </div>
                            
                            <hr class="my-4" style="border:0; height:3px; background:#672222; opacity:1;">
                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="newUsername" class="form-label">New Username</label>
                                <input type="text" placeholder="Enter New Username"  class="form-control" id="newUsername" required>
                            </div>

                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;"class="form-label">New Password</label>
                                <div class="d-flex gap-2">
                                    <input type="password" placeholder="Enter New Password"  class="form-control"   id="newPassword"
                                        required
                                        minlength="8"
                                        maxlength="20"
                                        pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&_])[A-Za-z\d@$!%*?&_]{8,20}$"
                                        title="Password must be 8–20 characters, include at least one uppercase letter, one lowercase letter, one number, and one special character.">
                                    <input type="password" placeholder="Confirm New Password" class="form-control" id="confirmPassword" required>
                                </div>
                                <div id="password-strength" style="margin-top:5px; font-weight:bold;"></div>
                                
                                <small style="font-size: 10px;" class="form-text text-muted">
                                    Your password must be 8–20 characters, include at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).
                                </small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn" style="background: #672222; color: white;" id="saveChangesBtn">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>

<script>
    $(document).ready(function(){
        const today = new Date().toISOString().split("T")[0];
        document.getElementById("pdate").value = today;
        $("#date").val(today);
       
        
        fetchUser(); //load data
        function fetchUser() {
            var leasetbl = "LOADLEASEREC";
            $.ajax({
            url: "adminlease_api.php",
            method: "POST",
            data: {leasetbl: leasetbl},
            success: function(data) {
                $('#data_table').html(data);
            }
            });
        }

        



        $("#addbtn").click(function(){
            $("#addtenant").modal("show");
            $("#mySidenav").css("width", "0");
            $("#sidenav").show();
        });
        // Global function to close payment modal and clean up backdrop
        function closePaymentModal() {
            var modalEl = document.getElementById('addpaymod');
            if (modalEl) {
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }
            }
            // Remove any leftover backdrop and reset body styles
            setTimeout(function() {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
                $('body').css('overflow', '');
                $('body').css('padding-right', '');
            }, 300);
        }
        
        // Global function to show payment modal (can be called from onclick)
        let pendingPaymentPrefill = null;
        let currentEditingPayId = null; // Track which payment is being edited
        function applyPaymentPrefill(prefill) {
            if (!prefill) return;
            const d = prefill;
            const $sel = $("#tenant_id");
            if ($sel.find('option[value="'+ d.tenant_id +'"]').length === 0) {
                $sel.append('<option value="'+ d.tenant_id +'">'+ (d.tenant_name || d.tenant_id) +'</option>');
            }
            $sel.val(d.tenant_id || "");
            $("#price_of_unit").val(d.price_of_unit || "");
            $("#payment").val(d.payment || "");
            $("#compensation").val(d.compensation || "");
            $("#pdate").val(d.pdate || "");
            if (d.period_from && d.period_to) {
                $("#months_info").text('Period: ' + d.period_from + ' to ' + d.period_to).show();
            } else {
                $("#months_info").hide();
            }
            // Store pay_id if editing
            currentEditingPayId = d.pay_id || null;
            // Update modal title
            if (currentEditingPayId) {
                $("#addpaymod .modal-title").text("EDIT PAYMENT");
            } else {
                $("#addpaymod .modal-title").text("ADD PAYMENT");
            }
            pendingPaymentPrefill = null;
        }

        function showAddPaymentModal() {
            // Check if modal exists
            var modalEl = document.getElementById('addpaymod');
            if (!modalEl) {
                alert("Modal element not found!");
                return;
            }
            
            // Reset form fields individually
            function resetPaymentForm() {
                $("#tenant_id").val("");
                $("#price_of_unit").val("");
                $("#payment").val("");
                $("#compensation").val("");
                $("#months_info").hide();
                let today = new Date().toISOString().split('T')[0];
                $("#pdate").val(today);
                currentEditingPayId = null; // Clear editing state when adding new
                $("#addpaymod .modal-title").text("ADD PAYMENT"); // Reset modal title
            }
            
            // Load tenants for select before showing modal
            $.ajax({
                url: "adminlease_api.php",
                method: "POST",
                data: { leasetbl: "TENANT_LIST" },
                dataType: "json",
                success: function(list){
                    var $sel = $("#tenant_id");
                    $sel.empty();
                    $sel.append('<option value="" disabled selected>— Select a tenant —</option>');
                    if (Array.isArray(list) && list.length > 0) {
                        list.forEach(function(t){
                            $sel.append('<option value="'+ t.tenant_id +'">'+ t.tname.toUpperCase() +'</option>');
                        });
                    } else {
                        $sel.append('<option disabled>No tenants found</option>');
                    }
                    // Reset form fields
                    resetPaymentForm();
                    applyPaymentPrefill(pendingPaymentPrefill);
                    // Use Bootstrap 5 modal API
                    try {
                        var modal = new bootstrap.Modal(modalEl, {
                            backdrop: true,
                            keyboard: true
                        });
                        modal.show();
                        // Clean up backdrop when modal is hidden
                        modalEl.addEventListener('hidden.bs.modal', function() {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open');
                            $('body').css('overflow', '');
                            $('body').css('padding-right', '');
                        });
                    } catch(err) {
                        // Fallback to jQuery method
                        $("#addpaymod").modal("show");
                        $("#addpaymod").on('hidden.bs.modal', function() {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open');
                        });
                    }
                },
                error: function(xhr, status, error){
                    // Still show modal even if AJAX fails
                    var $sel = $("#tenant_id");
                    $sel.empty();
                    $sel.append('<option value="" disabled selected>— Select a tenant —</option>');
                    // Reset form fields
                    resetPaymentForm();
                    applyPaymentPrefill(pendingPaymentPrefill);
                    // Use Bootstrap 5 modal API
                    try {
                        var modal = new bootstrap.Modal(modalEl, {
                            backdrop: true,
                            keyboard: true
                        });
                        modal.show();
                        // Clean up backdrop when modal is hidden
                        modalEl.addEventListener('hidden.bs.modal', function() {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open');
                            $('body').css('overflow', '');
                            $('body').css('padding-right', '');
                        });
                    } catch(err) {
                        // Fallback to jQuery method
                        $("#addpaymod").modal("show");
                        $("#addpaymod").on('hidden.bs.modal', function() {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open');
                        });
                    }
                }
            });
        }

        $("#addpayment").click(function(){
            showAddPaymentModal();
            $("#mySidenav").css("width", "0");
            $("#sidenav").show();
        });


         // side nav
        $("#sidenav").click(function(){
            $("#mySidenav").css("width", "250px");
            $("#mySidenav").css("border-radius", "10px");
            $("#sidenav").hide();
        });

        $("#closeBtn").click(function(){
            $("#mySidenav").css("width", "0");
            $("#sidenav").show();
        });
        // end side nav

       
         //  View History button click (AJAX)
         var currentHistoryTenant = { id: null, name: "" };

         function loadHistory(filters) {
             if (!currentHistoryTenant.id) return;
             $.ajax({
                 url: "adminlease_api.php",
                 method: "POST",
                 data: { 
                     leasetbl: "HISTORY",
                     tenant_id: currentHistoryTenant.id,
                     from: filters && filters.from ? filters.from : '',
                     to: filters && filters.to ? filters.to : ''
                 },
                 success: function(response) {
                     $("#historyModal .modal-title").text("Payment History - " + currentHistoryTenant.name);
                     $("#historyContent").html(response);
                     $("#historyModal").modal("show");
                 }
             });
         }

         $(document).on("click", ".view-history", function() {
             currentHistoryTenant.id = $(this).data("id");
             currentHistoryTenant.name = $(this).data("name") || "Payment History";
             $('#history_from').val('');
             $('#history_to').val('');
             loadHistory({});
         });

         $('#history_filter_btn').on('click', function(){
             const from = $('#history_from').val();
             const to = $('#history_to').val();
             if (from && to && from > to) {
                 alert('The "From" date cannot be later than the "To" date.');
                 return;
             }
             loadHistory({ from: from, to: to });
         });

         $('#history_from, #history_to').on('change', function(){
             const from = $('#history_from').val();
             const to = $('#history_to').val();
             if (from && to && from > to) {
                 return;
             }
             loadHistory({ from: from, to: to });
         });

        // Add or Update tenant (copied from admin dashboard behavior)
        $('#submittenant').click(function() {
            var tenantId   = $(this).data('tenant-id');
            var tname      = $('#tname').val().trim();
            var contact    = $('#contact').val().trim();
            var ddate      = $('#date').val();

            var phPattern = /^09\d{9}$/; // PH mobile: 11 digits starting with 09

            if (!tname || !contact || !ddate) {
                alert("Please fill all required fields correctly.");
                return;
            }

            if (!phPattern.test(contact)) {
                alert("Contact number must be 11 digits and start with 09.");
                return;
            }

            $.ajax({
                url: 'adminlease_api.php',
                method: 'POST',
                data: {
                    leasetbl: tenantId ? "UPDATE" : "SAVE",
                    id: tenantId,
                    tname: tname,
                    contact: contact,
                    date: ddate
                },
                success: function(response) {
                    alert(response);
                    fetchUser();
                    $('#addtenant').modal('hide');
                    $('#submittenant').removeData('tenant-id');
                },
                error: function(xhr, status, error) {
                    alert("AJAX error: " + error);
                }
            });
        });


        // Handle Change Pass & User click
        $(".change_userpass").click(function(e){
            e.preventDefault();
            $("#changePassModal").modal('show');
        });

        // Handle Save Changes button
        $("#saveChangesBtn").click(function(){
            var currentUsername = $("#currentUsername").val().trim();
            var currentPassword = $("#currentPassword").val().trim();
            var newUsername = $("#newUsername").val().trim();
            var newPassword = $("#newPassword").val().trim();
            var confirmPassword = $("#confirmPassword").val().trim();

            if(newPassword !== confirmPassword) {
                alert("New passwords do not match!");
                return;
            }

            $.ajax({
                url: "leasefunction.php",
                method: "POST",
                data: {
                    action: "CHANGE_PASS_USER",
                    current_username: currentUsername,
                    current_password: currentPassword,
                    new_username: newUsername,
                    new_password: newPassword
                },
                success: function(data) {
                    alert(data);
                    $("#changePassModal").modal('hide');
                    $("#changePassForm")[0].reset();
                },
                error: function() {
                    alert("Error changing password and username!");
                }
            });
        });

        // Password strength indicator
        $('#newPassword').on('input', function() {
            let val = $(this).val();
            let strength = '';
            let color = '';

            if(val.length === 0){
                $('#password-strength').text('');
                return;
            }

            // Conditions
            let hasLower = /[a-z]/.test(val);
            let hasUpper = /[A-Z]/.test(val);
            let hasNumber = /\d/.test(val);
            let hasSpecial = /[@$!%*?&]/.test(val);

            let conditionsMet = [hasLower, hasUpper, hasNumber, hasSpecial].filter(Boolean).length;

            if(val.length >= 8 && conditionsMet === 4){
                strength = 'Strong';
                color = 'green';
            } else if(val.length >= 6 && conditionsMet >= 3){
                strength = 'Medium';
                color = 'orange';
            } else {
                strength = 'Weak';
                color = 'red';
            }

            $('#password-strength').text(strength).css('color', color);
        });

        // Refresh Button Handler
        $(document).on('click', '#refreshBtn', function() {
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();
            fetchUser();
        });

        // Hide loader after 2 seconds
        function hideLoaderAfter3Sec() {
            setTimeout(function() {
                $(".loader-overlay").css("display", "none");
            }, 2000);
        }

        // Auto-calculate months based on payment / price of unit
        $(document).on("input", "#payment, #price_of_unit", function(){
            var priceOfUnit = parseFloat($('#price_of_unit').val()) || 0;
            var payment = parseFloat($('#payment').val()) || 0;
            
            if (priceOfUnit > 0 && payment > 0) {
                var months = Math.floor(payment / priceOfUnit);
                var remainder = payment % priceOfUnit;
                var partialMonths = payment / priceOfUnit;
                
                if (payment < priceOfUnit) {
                    // Partial payment (paunang bayad)
                    var percentage = ((payment / priceOfUnit) * 100).toFixed(1);
                    $('#months_info').text('Partial payment: ' + percentage + '% of one month').show();
                } else if (months > 0) {
                    var infoText = "≈ " + months + " month(s)";
                    if (remainder > 0) {
                        var remainderPercent = ((remainder / priceOfUnit) * 100).toFixed(1);
                        infoText += " + " + remainder.toFixed(2) + " (" + remainderPercent + "% remainder)";
                    }
                    $('#months_info').text(infoText).show();
                } else {
                    $('#months_info').hide();
                }
            } else {
                $('#months_info').hide();
            }
        });

        // Submit Admin Payment
        $(document).on("click", "#submitpayment", function(e){
            e.preventDefault();
            var tenantId = $('#tenant_id').val();
            var priceOfUnit = parseFloat($('#price_of_unit').val()) || 0;
            var payment = parseFloat($('#payment').val()) || 0;
            var compensation = parseFloat($('#compensation').val()) || 0;
            var pdate = $('#pdate').val();

            if (!tenantId || priceOfUnit <= 0 || payment <= 0 || !pdate) {
                alert('Please fill all required fields correctly.');
                return;
            }

            // Check if we're editing an existing payment
            var isEditing = currentEditingPayId !== null && currentEditingPayId !== undefined;
            
            if (isEditing) {
                // Update existing payment
                $.ajax({
                    url: 'adminlease_api.php',
                    method: 'POST',
                    data: {
                        leasetbl: 'UPDATE_PAYMENT',
                        pay_id: currentEditingPayId,
                        tenant_id: tenantId,
                        price_of_unit: priceOfUnit,
                        payment: payment,
                        compensation: compensation,
                        pdate: pdate
                    },
                    success: function(resp){
                        alert(resp);
                        // Hide payment modal and remove backdrop
                        var modal = bootstrap.Modal.getInstance(document.getElementById('addpaymod'));
                        if (modal) {
                            modal.hide();
                        } else {
                            $('#addpaymod').modal('hide');
                        }
                        // Also close history modal if open
                        var historyModal = bootstrap.Modal.getInstance(document.getElementById('historyModal'));
                        if (historyModal) {
                            historyModal.hide();
                        } else {
                            $('#historyModal').modal('hide');
                        }
                        // Remove any leftover backdrop
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                        $('body').css('overflow', '');
                        $('body').css('padding-right', '');
                        // Reset form fields individually
                        $("#tenant_id").val("");
                        $("#price_of_unit").val("");
                        $("#payment").val("");
                        $("#compensation").val("");
                        $("#months_info").hide();
                        let today = new Date().toISOString().split('T')[0];
                        $("#pdate").val(today);
                        currentEditingPayId = null; // Clear editing state
                        fetchUser();
                    },
                    error: function(xhr, status, err){
                        alert('AJAX error: ' + err);
                    }
                });
            } else {
                // Add new payment
                // Calculate months (allow partial payments - can be less than 1 month)
                var months = Math.floor(payment / priceOfUnit);
                var partialPayment = payment < priceOfUnit;
                
                // Allow partial payments (paunang bayad)
                if (payment <= 0) {
                    alert('Payment amount must be greater than zero.');
                    return;
                }

                $.ajax({
                    url: 'adminlease_api.php',
                    method: 'POST',
                    data: {
                        leasetbl: 'ADD_PAYMENT',
                        tenant_id: tenantId,
                        price_of_unit: priceOfUnit,
                        payment: payment,
                        compensation: compensation,
                        pdate: pdate,
                        months: months
                    },
                    success: function(resp){
                        alert(resp);
                        // Hide modal and remove backdrop
                        var modal = bootstrap.Modal.getInstance(document.getElementById('addpaymod'));
                        if (modal) {
                            modal.hide();
                        } else {
                            $('#addpaymod').modal('hide');
                        }
                        // Remove any leftover backdrop
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                        $('body').css('overflow', '');
                        $('body').css('padding-right', '');
                        // Reset form fields individually
                        $("#tenant_id").val("");
                        $("#price_of_unit").val("");
                        $("#payment").val("");
                        $("#compensation").val("");
                        $("#months_info").hide();
                        let today = new Date().toISOString().split('T')[0];
                        $("#pdate").val(today);
                        currentEditingPayId = null; // Clear editing state
                        fetchUser();
                    },
                    error: function(xhr, status, err){
                        alert('AJAX error: ' + err);
                    }
                });
            }
        });

        // Payment history action buttons
        $(document).on('mouseenter', '.history-edit, .history-delete', function() {
            $(this).css('opacity', '0.75');
        }).on('mouseleave', '.history-edit, .history-delete', function() {
            $(this).css('opacity', '1');
        });

        // Delete payment
        $(document).on('click', '.history-delete', function(e){
            e.preventDefault();
            var payId = $(this).data('pay-id');
            var tenantId = $(this).data('tenant-id');
            if (!payId || !tenantId) return;
            if (!confirm('Delete this payment record?')) return;

            $.ajax({
                url: 'adminlease_api.php',
                method: 'POST',
                data: { leasetbl: 'DELETE_PAYMENT', pay_id: payId },
                success: function(resp){
                    alert(resp);
                    $("#historyModal").modal("hide");
                    fetchUser();
                },
                error: function(xhr, status, err){
                    alert('AJAX error: ' + err);
                }
            });
        });

        // Edit payment (prefill add payment modal for now)
        $(document).on('click', '.history-edit', function(e){
            e.preventDefault();
            var payId = $(this).data('pay-id');
            if (!payId) return;

            // Fetch payment details then fill the modal
            $.ajax({
                url: 'adminlease_api.php',
                method: 'POST',
                dataType: 'json',
                data: { leasetbl: 'GET_PAYMENT', pay_id: payId },
                success: function(resp){
                    if (!resp || resp.status !== 'success' || !resp.data) {
                        alert(resp && resp.message ? resp.message : 'Unable to load payment.');
                        return;
                    }
                    var d = resp.data;
                    pendingPaymentPrefill = {
                        pay_id: d.pay_id, // Include pay_id for editing
                        tenant_id: d.tenant_id,
                        tenant_name: d.tenant_name || d.tenant_id,
                        price_of_unit: d.price_of_unit,
                        payment: d.payment,
                        compensation: d.compensation,
                        pdate: d.pdate,
                        period_from: d.period_from,
                        period_to: d.period_to
                    };
                    showAddPaymentModal();
                },
                error: function(xhr, status, err){
                    alert('AJAX error: ' + err);
                }
            });
        });

        // Clean up modal backdrop when payment modal is closed
        $('#addpaymod').on('hidden.bs.modal', function () {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('overflow', '');
            $('body').css('padding-right', '');
        });

        // Also handle when modal is hidden via Bootstrap 5 events
        var addPayModalEl = document.getElementById('addpaymod');
        if (addPayModalEl) {
            addPayModalEl.addEventListener('hidden.bs.modal', function () {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
                $('body').css('overflow', '');
                $('body').css('padding-right', '');
            });
        }
        
    });
</script>
