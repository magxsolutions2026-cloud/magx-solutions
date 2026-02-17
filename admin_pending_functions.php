<?php
// PHPMailer version - requires vendor/autoload.php
require_once 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if(isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if($action == 'LOAD_PENDING') {
        loadPendingAccounts();
    }
    elseif($action == 'LOAD_VERIFIED') {
        loadVerifiedAccounts();
    }
    
    elseif($action == 'APPROVE') {
        approveAccount();
    }
    elseif($action == 'REJECT') {
        rejectAccount();
    }
    elseif($action == 'DELETE_REJECTED') {
        deleteRejectedAccounts();
    }
    elseif($action == 'VIEW_PROFILE') {
        viewProfile();
    }
}

function loadPendingAccounts() {
    $con = mysqli_connect('localhost','root','root','tmc_db');
    $query = "SELECT * FROM tbl_pending_users WHERE status = 'Pending' ORDER BY date_created DESC";
    $result = mysqli_query($con, $query);
    
    if(mysqli_num_rows($result) > 0) {
        echo '<div class="logs-container">
                <table class="logs-table">
                    <tr>
                        <td colspan="8" class="logs-title">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>PENDING ACCOUNTS</span>
                                <button title="Refresh" id="refreshPendingBtn" style="padding:2px; background:none; border:none; margin-right:5px;">
                                    <img src="reficon.png" alt="refresh" style="width:25px; height:25px;">
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Date Created</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Unit</th>
                        
                        
                        <th>Actions</th>
                    </tr>';
        
        while($row = mysqli_fetch_assoc($result)) {
            $fullName = $row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname'];
            echo '<tr>
                    <td>' . $row['date_created'] . '</td>
                    <td>' . $fullName . '</td>
                    <td>' . $row['email'] . '</td>
                    <td>' . $row['username'] . '</td>
                    <td>' . $row['unit'] . '</td>
                    
                    
                    <td>
                        <button class="btn btn-info btn-sm" onclick="viewProfile(' . $row['pending_id'] . ')" style="margin-right: 5px; background-color:#EEEEEE; border:none;">View Profile</button>
                        <button class="btn btn-success btn-sm" onclick="approveAccount(' . $row['pending_id'] . ')" style="margin-right: 5px;">Approve</button>
                        <button class="btn btn-danger btn-sm" onclick="rejectAccount(' . $row['pending_id'] . ')">Reject</button>
                    </td>
                  </tr>';
        }
        echo '</table></div>';
    } else {
        echo '<div class="logs-container">
                <table class="logs-table">
                    <tr>
                        <td colspan="8" class="logs-title">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>PENDING ACCOUNTS</span>
                                <button title="Refresh" id="refreshPendingBtn" style="padding:2px; background:none; border:none; margin-right:5px;">
                                    <img src="reficon.png" alt="refresh" style="width:25px; height:25px;">
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px;">No pending accounts found</td>
                    </tr>
                </table>
              </div>';
    }
}

function loadVerifiedAccounts() {
    $con = mysqli_connect('localhost','root','root','tmc_db');
    $query = "SELECT * FROM tbl_pending_users WHERE status = 'Approved' ORDER BY date_created DESC";
    $result = mysqli_query($con, $query);
    
    if(mysqli_num_rows($result) > 0) {
        echo '<div class="logs-container">
                <table class="logs-table">
                    <tr>
                        <td colspan="8" class="logs-title">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>VERIFIED ACCOUNTS</span>
                                <button title="Refresh" id="refreshVerifiedBtn" style="padding:2px; background:none; border:none; margin-right:5px;">
                                    <img src="reficon.png" alt="refresh" style="width:25px; height:25px;">
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Date Created</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Unit</th>
                        
                        
                        <th>Action</th>
                    </tr>';
        
        while($row = mysqli_fetch_assoc($result)) {
            $fullName = $row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname'];
            echo '<tr>
                     <td>' . $row['date_created'] . '</td>
                    <td>' . $fullName . '</td>
                    <td>' . $row['email'] . '</td>
                    <td>' . $row['username'] . '</td>
                    <td>' . $row['unit'] . '</td>
                    
                    
                    
                    <td  style=" padding:10px;">
                        <center>
                            <button type="button" class="btn btn-info btn-sm" onclick="viewProfile(' . $row['pending_id'] . ')" style="margin-right: 5px; background-color:#EEEEEE; border:none;" title="View Profile">
                                <img src="view.png" alt="view" style="width:20px; height:20px; vertical-align:middle;">
                            </button>
                            <button type="button" class="btn btn-danger btn-sm delete-stock" onclick="rejectAccount(' . $row['pending_id'] . ')" title="Delete">
                                <img src="deleteicon.png" alt="delete" style="width:20px; height:20px; vertical-align:middle;">
                            </button>
                        </center>
                    </td>
                  </tr>';
        }
        echo '</table></div>';
    } else {
        echo '<div class="logs-container">
                <table class="logs-table">
                    <tr>
                        <td colspan="8" class="logs-title">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>VERIFIED ACCOUNTS</span>
                                <button title="Refresh" id="refreshVerifiedBtn" style="padding:2px; background:none; border:none; margin-right:5px;">
                                    <img src="reficon.png" alt="refresh" style="width:25px; height:25px;">
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px;">No verified accounts found</td>
                    </tr>
                </table>
              </div>';
    }
}



function approveAccount() {
    $pendingId = $_POST['pending_id'];
    $con = mysqli_connect('localhost','root','root','tmc_db');
    
    // Get pending user data
    $query = "SELECT * FROM tbl_pending_users WHERE pending_id = '$pendingId'";
    $result = mysqli_query($con, $query);
    $userData = mysqli_fetch_assoc($result);
    
    if($userData) {
        // Update status to approved
        $updateQuery = "UPDATE tbl_pending_users SET status = 'Approved' WHERE pending_id = '$pendingId'";
        mysqli_query($con, $updateQuery);
        
        // Insert into main user tables
        $insertUser = "INSERT INTO tbl_user(username, password, unit, branch) VALUES('".$userData['username']."', '".$userData['password']."', '".$userData['unit']."', '".$userData['branch']."')";
        mysqli_query($con, $insertUser);
        
        $insertUserInfo = "INSERT INTO tbl_userinfo(fname, mname, lname, address, bday) VALUES('".$userData['fname']."', '".$userData['mname']."', '".$userData['lname']."', '".$userData['address']."', '".$userData['bday']."')";
        mysqli_query($con, $insertUserInfo);
        
        // Send approval email
        sendApprovalEmail($userData['email'], $userData['fname']);
        
        echo "Account approved successfully";
    }
}

function rejectAccount() {
    $pendingId = $_POST['pending_id'];
    $con = mysqli_connect('localhost','root','root','tmc_db');
    
    // Get pending user data before deletion
    $query = "SELECT * FROM tbl_pending_users WHERE pending_id = '$pendingId'";
    $result = mysqli_query($con, $query);
    $userData = mysqli_fetch_assoc($result);
    
    if($userData) {
        // Send rejection email first
        sendRejectionEmail($userData['email'], $userData['fname']);
        
        // Delete the account from pending users table
        $deleteQuery = "DELETE FROM tbl_pending_users WHERE pending_id = '$pendingId'";
        $deleteResult = mysqli_query($con, $deleteQuery);
        
        if($deleteResult) {
            echo "Account rejected and deleted successfully";
        } else {
            echo "Account rejected but deletion failed";
        }
    } else {
        echo "Account not found";
    }
}

function sendApprovalEmail($email, $name) {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tmcmultiindustry@gmail.com'; // CHANGE THIS
        $mail->Password   = 'mvww arpe atsu xyku';     // CHANGE THIS
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('tmcmultiindustry@gmail.com', 'TMC Admin'); // CHANGE THIS
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Account Approved - TMC Multi Industry';
        $mail->Body    = '
            <html>
            <head>
                <title>Account Approved</title>
            </head>
            <body style="font-family: Arial, sans-serif;">
                <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #672222;">Account Approved!</h2>
                    <p>Dear ' . $name . ',</p>
                    <p>Your account has been approved by the administrator. You can now login to the system.</p>
                    <p>Thank you for registering with TMC Multi Industry!</p>
                    <hr style="border: 1px solid #672222; margin: 20px 0;">
                    <p style="color: #666; font-size: 12px;">
                        This email was sent from the TMC Multi Industry Management System.
                    </p>
                </div>
            </body>
            </html>
        ';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Fallback to simple mail if PHPMailer fails
        return sendSimpleApprovalEmail($email, $name);
    }
}

function sendRejectionEmail($email, $name) {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com'; // CHANGE THIS
        $mail->Password   = 'your-app-password';     // CHANGE THIS
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('tmcmultiindustry@gmail.com', 'TMC Admin'); // CHANGE THIS
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Account Rejected - TMC Multi Industry';
        $mail->Body    = '
            <html>
            <head>
                <title>Account Rejected</title>
            </head>
            <body style="font-family: Arial, sans-serif;">
                <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #672222;">Account Rejected</h2>
                    <p>Dear ' . $name . ',</p>
                    <p>Unfortunately, your account registration has been rejected by the administrator.</p>
                    <p>If you believe this is an error, please contact our support team.</p>
                    <hr style="border: 1px solid #672222; margin: 20px 0;">
                    <p style="color: #666; font-size: 12px;">
                        This email was sent from the TMC Multi Industry Management System.
                    </p>
                </div>
            </body>
            </html>
        ';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Fallback to simple mail if PHPMailer fails
        return sendSimpleRejectionEmail($email, $name);
    }
}

// Fallback functions using simple mail()
function sendSimpleApprovalEmail($email, $name) {
    $subject = 'Account Approved - TMC Multi Industry';
    $message = "
        <html>
        <head>
            <title>Account Approved</title>
        </head>
        <body>
            <h2>Account Approved!</h2>
            <p>Dear $name,</p>
            <p>Your account has been approved by the administrator. You can now login to the system.</p>
            <p>Thank you for registering with TMC Multi Industry!</p>
            <br>
            <p>Best regards,<br>TMC Admin Team</p>
        </body>
        </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: TMC Admin <noreply@tmc.com>' . "\r\n";
    
    return mail($email, $subject, $message, $headers);
}

function sendSimpleRejectionEmail($email, $name) {
    $subject = 'Account Rejected - TMC Multi Industry';
    $message = "
        <html>
        <head>
            <title>Account Rejected</title>
        </head>
        <body>
            <h2>Account Rejected</h2>
            <p>Dear $name,</p>
            <p>Unfortunately, your account registration has been rejected by the administrator.</p>
            <p>If you believe this is an error, please contact our support team.</p>
            <br>
            <p>Best regards,<br>TMC Admin Team</p>
        </body>
        </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: TMC Admin <noreply@tmc.com>' . "\r\n";
    
    return mail($email, $subject, $message, $headers);
}

function deleteRejectedAccounts() {
    $con = mysqli_connect('localhost','root','root','tmc_db');
    
    // Delete all rejected accounts
    $deleteQuery = "DELETE FROM tbl_pending_users WHERE status = 'Rejected'";
    $result = mysqli_query($con, $deleteQuery);
    
    if($result) {
        $deletedCount = mysqli_affected_rows($con);
        echo "Successfully deleted $deletedCount rejected accounts";
    } else {
        echo "Failed to delete rejected accounts";
    }
}

function viewProfile() {
    $pendingId = $_POST['pending_id'];
    $con = mysqli_connect('localhost','root','root','tmc_db');
    
    // Get user profile data
    $query = "SELECT * FROM tbl_pending_users WHERE pending_id = '$pendingId'";
    $result = mysqli_query($con, $query);
    $userData = mysqli_fetch_assoc($result);
    
    if($userData) {
        $fullName = $userData['fname'] . ' ' . $userData['mname'] . ' ' . $userData['lname'];
        
        echo '<div class="profile-modal-content">
                <div class="profile-header">
                    <h3>User Profile</h3>
                    <button class="close-modal" onclick="closeProfileModal()">&times;</button>
                </div>
                <div class="profile-body">
                    <div class="profile-section">
                        <h4>Personal Information</h4>
                        <div class="profile-row">
                            <label>Full Name:</label>
                            <span>' . $fullName . '</span>
                        </div>
                        <div class="profile-row">
                            <label>Email:</label>
                            <span>' . $userData['email'] . '</span>
                        </div>
                        <div class="profile-row">
                            <label>Username:</label>
                            <span>' . $userData['username'] . '</span>
                        </div>
                        <div class="profile-row">
                            <label>Birthday:</label>
                            <span>' . $userData['bday'] . '</span>
                        </div>
                        <div class="profile-row">
                            <label>Address:</label>
                            <span>' . $userData['address'] . '</span>
                        </div>
                    </div>
                    <div class="profile-section">
                        <h4>Account Information</h4>
                        <div class="profile-row">
                            <label>Unit:</label>
                            <span>' . $userData['unit'] . '</span>
                        </div>
                        <div class="profile-row">
                            <label>Branch:</label>
                            <span>' . $userData['branch'] . '</span>
                        </div>
                        <div class="profile-row">
                            <label>Status:</label>
                            <span class="status-' . strtolower($userData['status']) . '">' . $userData['status'] . '</span>
                        </div>
                        <div class="profile-row">
                            <label>Date Registered:</label>
                            <span>' . $userData['date_created'] . '</span>
                        </div>
                    </div>
                    <button class="btncloseprof btn-secondary" onclick="closeProfileModal()">Close</button>
                </div>
                
              </div>';
    } else {
        echo '<div class="profile-modal-content">
                <div class="profile-header">
                    <h3>Error</h3>
                    <button class="close-modal" onclick="closeProfileModal()">&times;</button>
                </div>
                <div class="profile-body">
                    <p>User profile not found.</p>
                </div>
                <div class="profile-footer">
                    <button class="btn btn-secondary" onclick="closeProfileModal()">Close</button>
                </div>
              </div>';
    }
}
?>