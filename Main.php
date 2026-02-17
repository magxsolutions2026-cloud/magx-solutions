<?php
// PHPMailer includes
require_once 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Function to send registration confirmation email
function sendRegistrationEmail($email, $name, $businessUnit) {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tmcmultiindustry@gmail.com'; // Your Gmail
        $mail->Password   = 'mvww arpe atsu xyku';         // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->Timeout    = 30; // Increase timeout
        
        // Recipients
        $mail->setFrom('tmcmultiindustry@gmail.com', 'TMC Multi Industry');
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Registration Submitted - TMC Multi Industry';
        $mail->Body    = '
            <html>
            <head>
                <title>Registration Submitted</title>
            </head>
            <body style="font-family: Arial, sans-serif;">
                <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h2 style="color: #672222;">Registration Submitted Successfully!</h2>
                    <p>Dear ' . $name . ',</p>
                    <p>Thank you for registering with TMC Multi Industry for the <strong>' . $businessUnit . '</strong> business unit.</p>
                    <p>Your account is currently pending approval by our administrator. You will receive another email notification once your account has been reviewed and approved.</p>
                    <p>Please wait for the approval process to complete.</p>
                    <hr style="border: 1px solid #672222; margin: 20px 0;">
                    <p style="color: #666; font-size: 12px;">
                        This email was sent from the TMC Multi Industry Management.<br>
                        
                    </p>
                </div>
            </body>
            </html>
        ';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error instead of using fallback
        error_log("PHPMailer Error: " . $e->getMessage());
        return false; // Return false instead of trying fallback
    }
}

// Note: Removed fallback mail() function to avoid local mail server issues

    if(isset($_POST["adlogin"]))
	{
		
		$adminuser = $_POST["aduser"];
		$adminpass = $_POST["adpass"];
        $bu = $_POST["businessunit"];

        date_default_timezone_set("Asia/Manila");
        $log_date = date("Y-m-d");      
        $log_time = date("h:i:s A");
		
		if($adminuser==""){
			echo "<script>alert('Kindly type your username');</script>";
		}
		else if($adminpass==""){
			echo "<script>alert('Kindly type your password');</script>";
		}
		else{
			$con = mysqli_connect('localhost','root','root','magx_db');
			$command = "select * from tbl_acc where adminuser='".$adminuser."'";
			$record = mysqli_fetch_row(mysqli_query($con,$command));
			
			if($record){
			$cmd = "select * from tbl_acc where adminuser='".$adminuser."' and adminpass='".$adminpass."'";
			$record = mysqli_fetch_row(mysqli_query($con,$cmd));
				if($record){
					if($adminuser <> $record[1])
					{
						echo"<script>alert('Username is invalid,type it again...')</script>";
					}
					else if($adminpass <> $record[2])
					{
						echo"<script>alert('Password is invalid,type it again...')</script>";
					}
					else{
						
                      
						$cmd = "INSERT INTO tbl_logss (nameofuser, oras, petsa, unit) VALUES ('$adminuser', '$log_time', '$log_date', '$bu')";

						$result = mysqli_query($con,$cmd);

                        if($bu == "Gasoline Station"){
                            echo"<script> location.href='gasmain.php'</script>";
                        }
                        else if($bu == "Commercial Leasing"){

                        }
                        else if($bu == "Bros Inasal"){
                            echo"<script> location.href='brosmain.php'</script>";
                        }
                        else if($bu == "Admin"){
                            echo"<script> location.href='admain.php'</script>";
                        }
                       	
						
					}
					
				}
				else{
					echo "<script>alert('password does not exist');</script>";
				}
			}
			else{
				echo "<script>alert('username does not exist');</script>";
			}
		}
		
		
	}


	if(isset($_POST["btnlogin"]))
	{
		
		$username = $_POST["txtuser"];
		$userpass = $_POST["txtpass"];

        date_default_timezone_set("Asia/Manila");
        $log_date = date("Y-m-d");      
        $log_time = date("h:i:s A");
        $bu = $_POST["businessunit"];
        $branch = ($bu === "Gasoline Station" && isset($_POST["branch"]) && $_POST["branch"] !== '') ? $_POST["branch"] : null;

		
		if($username==""){
			echo "<script>alert('Kindly type your username');</script>";
		}
		else if($userpass==""){
			echo "<script>alert('Kindly type your password');</script>";
		}
		else if($bu=="Gasoline Station" && ($branch=="" || $branch==null)){
			echo "<script>alert('Kindly select a branch');</script>";
		}
		else{
			$con = mysqli_connect('localhost','root','root','magx_db');
			$command = "select * from tbl_user where username='".$username."'";
			$record = mysqli_fetch_row(mysqli_query($con,$command));
			
			if($record){
			$cmd = "select * from tbl_user where username='".$username."' and password='".$userpass."' and unit='".$bu."'";
			if($bu=="Gasoline Station"){
				$cmd .= " and branch='".$branch."'";
			}
			$record = mysqli_fetch_row(mysqli_query($con,$cmd));
				if($record){
					if($username <> $record[1])
					{
						echo"<script>alert('Username is invalid,type it again...')</script>";
					}
					else if($userpass <> $record[2])
					{
						echo"<script>alert('Password is invalid,type it again...')</script>";
					}
					else{
						$con2 = mysqli_connect('localhost','root','root','magx_db');
						$cmd = "INSERT INTO tbl_logss (nameofuser, oras, petsa, unit) VALUES ('$username', '$log_time', '$log_date', '$bu')";

						$result = mysqli_query($con2,$cmd);

                        if($bu == "Gasoline Station"){
                            session_start();
                            $_SESSION['branch'] = $branch;
                            echo"<script> location.href='gasmain.php'</script>";
                        }
                        else if($bu == "Commercial Leasing"){
                            echo"<script> location.href='leasemain.php'</script>";
                        }
                        else if($bu == "Bros Inasal"){
                            echo"<script> location.href='brosmain.php'</script>";
                        }
                       	
						
					}
					
				}
				else{
					echo "<script>alert('password does not exist');</script>";
				}
			}
			else{
				echo "<script>alert('username does not exist');</script>";
			}
		}
		
		
	}




    if(isset($_POST["btnregister"]))
	{
		$firstname = $_POST["fname"];
        $middlename = $_POST["mname"];
		$lastname = $_POST["lname"];
		$username = $_POST["username"];
		$password = $_POST["password"];
		$passretype = $_POST["retype"];
		$address = $_POST["address"];
        $birthday = $_POST["bdate"];
        $bu = $_POST["businessunit"];
        $branch = isset($_POST["branch"]) ? $_POST["branch"] : "";
        $email = $_POST["email"];
		
		
		
		if($firstname=="" || $lastname=="" || $username=="" || $password=="" || $birthday=="" || $address=="" || $email==""){
			echo "<script>alert('Kindly fill all information');</script>";
		}
		else if($bu=="Gasoline Station" && ($branch=="" || $branch==null)){
			echo "<script>alert('Kindly select a branch');</script>";
		}
		else if($password != $passretype){
			echo "<script>alert('Password Dont Match!!');</script>";
		}
		else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			echo "<script>alert('Please enter a valid email address');</script>";
		}
		else{
			$con = mysqli_connect('localhost','root','root','magx_db');
			$stmt1 = mysqli_prepare($con, "SELECT * FROM tbl_user WHERE username=? OR password=?");
			mysqli_stmt_bind_param($stmt1, "ss", $username, $password);
			mysqli_stmt_execute($stmt1);
			$result = mysqli_stmt_get_result($stmt1);
			$record = mysqli_fetch_row($result);
			
			// Check if email already exists in pending table
			$stmt2 = mysqli_prepare($con, "SELECT * FROM tbl_pending_users WHERE email=?");
			mysqli_stmt_bind_param($stmt2, "s", $email);
			mysqli_stmt_execute($stmt2);
			$emailResult = mysqli_stmt_get_result($stmt2);
			$emailRecord = mysqli_fetch_row($emailResult);
			
			if($record){
				if($username == $record[0])
				{
					echo"<script>alert('Username already use..')</script>";	
				}
				else if($password == $record[1])
				{
					echo"<script>alert('Password already use..')</script>";	
				}	
			}
			else if($emailRecord){
				echo"<script>alert('Email already exists in pending accounts..')</script>";
			}
			else{
				
				
				// Start transaction to ensure all inserts succeed or fail together
				mysqli_begin_transaction($con);
				
				try {
					// Insert into pending users table using prepared statement
					$cmd1 = "INSERT INTO tbl_pending_users(email,username,password,unit,branch,fname,mname,lname,address,bday,status,date_created) VALUES (?,?,?,?,?,?,?,?,?,?,'Pending',NOW())";
					$stmt1 = mysqli_prepare($con, $cmd1);
					mysqli_stmt_bind_param($stmt1, "ssssssssss", $email, $username, $password, $bu, $branch, $firstname, $middlename, $lastname, $address, $birthday);
					$result1 = mysqli_stmt_execute($stmt1);
					
					if(!$result1) {
						throw new Exception("Failed to insert into pending users: " . mysqli_error($con));
					}
					
					// Commit transaction if insert successful
					mysqli_commit($con);
					
					// Send registration confirmation email
					$emailSent = sendRegistrationEmail($email, $firstname, $bu);
					
					if($emailSent) {
						echo"<script>
							alert('Registration submitted successfully!\\n\\nYou will receive an email confirmation shortly.\\nYour account is now pending admin approval.\\n\\nPlease check your email for further instructions.');
							// Close the registration modal and show home
							$('#exampleModal2').modal('hide');
							$('#title').show();
							$('#aboutcon').hide();
							$('#footer').hide();
							$('#contactcontainer').hide();
						</script>";
					} else {
						echo"<script>
							alert('Registration submitted successfully!\\n\\nYour account is now pending admin approval.\\n\\nNote: Email notification could not be sent, but your registration was successful.');
							// Close the registration modal and show home
							$('#exampleModal2').modal('hide');
							$('#title').show();
							$('#aboutcon').hide();
							$('#footer').hide();
							$('#contactcontainer').hide();
						</script>";
					}
					
				} catch (Exception $e) {
					// Rollback transaction on error
					mysqli_rollback($con);
					echo"<script>alert('Database error: " . $e->getMessage() . "');</script>";
					exit;
				}
			}		
		}
	}
?>




<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>TMC MULTI INDUSTRY</title>
        <link rel="icon" type="png" href="tmclogo.png">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        
        <style>
       
        #mainnav{
            
            background: linear-gradient(
                90deg, 
                #8c2f2f 0%, 
                #672222 50%, 
                #8c2f2f 100%
            );
            
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 18px;
            position: fixed;
            width: 100vw;
            height: 40px;
            z-index: 1;
        }
        #mainnav::before {
            content: "";
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            clip-path: inherit;
            background-color: black;
            opacity: 0.3;
            transform: translate(0px, 0px); 
            filter: blur(5px); 
            z-index: -1;
        }

        #btnnav{
            top: 40px;
            align-items: center;
        }
        #btnnav .btn {
            transition: 0.3s;
            border: none;
        }
        #srow {
            margin-right: 50px;  
            gap: 50px; 
        }

        #frow {
            margin-left: 50px;  
            gap: 50px; 
        }

        .bt{ 
            width: 150px; 
            
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
        }
        #adlogin::before,#adcancel::before,.btn::before {
            content: "";
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            clip-path: inherit;
            background-color: black;
            opacity: 0.3;
            transform: translate(0px, 0px); 
            filter: blur(5px); 
            z-index: -1;
        }
        #adlogin:hover,#adcancel:hover,.bt:hover{
            box-shadow: 0 0 20px 4px rgba(103, 34, 34, 0.7),
            0 0 40px 8px rgba(103, 34, 34, 0.4);
            transform: scale(1.05); 
         }

        .bt.active,
        .bt:focus {
            background: linear-gradient(90deg, #672222, #8c2f2f);
            color: #fff;
            box-shadow: 0 0 25px 5px rgba(140, 47, 47, 0.9),
                        0 0 50px 10px rgba(140, 47, 47, 0.6);
            transform: scale(0.98);
        }

        
       


        .sidenav::before {
            color: white;
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(
                circle at var(--x, 50%) var(--y, 50%),
                #672222 0%,     
                #b33939 60%,    
                transparent 80%
            );
            opacity: 0;
            transition: opacity 0.3s;
        }

        .sidenav:hover::before {
            color: white;
            opacity: 1;
        }

        .sidenav .span {
            position: relative;
            z-index: 1;
        }
        
          

        .poly-center {
            cursor: pointer;
            color: white;
            font-size: 50px;
            display: flex;
            justify-content: center; 
            align-items: center; 
            position: fixed;
            left: 50%;
            transform: translateX(-50%);
            width: 250px;
            height: 90px;
            box-shadow: 0 0 5px 3px black;
            background: linear-gradient(
                90deg, 
                #8c2f2f 0%, 
                #672222 50%, 
                #8c2f2f 100%
            );
            backdrop-filter: blur(5px);
            z-index: 2;
            clip-path: path('M0,0 H250 Q212,90 187,90 H63 Q38,90 0,0 Z');
        }
        .poly-center::before {
            content: "";
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            clip-path: inherit;
            background-color: black;
            opacity: 0.3;
            transform: translate(0px, 0px); 
            filter: blur(5px); 
            z-index: -1;
        }

       
         
       #bodycontainer {
            background: url('tmcbackground2.png') center/cover no-repeat;
            color: white;
            font-family: 'Poppins', sans-serif;
            text-align: center;
            padding: 50px 20px;
            padding-bottom: 10px;
            overflow: hidden;
            height: 100vh;
        }

        /* Sections */
        .section {
            
            padding: 45px 20px;
            height: 100vh;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            animation: fadeInUp 1s ease both;
        }
        .section.dark {
            background: linear-gradient(145deg, rgba(103,34,34,0.9), rgba(0,0,0,0.85));
        }
        .section.darker {
            background: linear-gradient(145deg, rgba(50,20,20,0.95), rgba(0,0,0,0.95));
        }

        /* Home */
        #title h2 {
            margin-top: 70px;
            font-weight: 600;
            font-size: 22px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #ffae42;
        }
        #title h1 {
            font-size: 30px;
            margin: 20px 0;
            text-shadow: 0 0 10px rgba(255,255,255,0.3);
        }
        #title hr {
            width: 40%;
            margin: 10px auto;
            border: none;
            height: 3px;
            background: linear-gradient(90deg, #ffae42, #ff4141);
            border-radius: 3px;
        }

        /* Headings with animated underline */
        .heading {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }
        .heading::after {
            content: "";
            position: absolute;
            width: 0%;
            height: 3px;
            left: 0;
            bottom: -5px;
            background: #ffae42;
            transition: width 0.4s ease;
        }
        .heading:hover::after {
            width: 100%;
        }

        /* Developer Cards */
        .dev-team {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 25px;
            margin-top: 30px;


            
        }
        .dev-card {
            background: rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 25px;
            width: 220px;
            transition: 0.4s ease;
            backdrop-filter: blur(6px);
        }
        .dev-card:hover {
            transform: translateY(-10px) scale(1.05);
            background: rgba(255,255,255,0.15);
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
        }
        .circle {
            background: linear-gradient(135deg, #ffae42, #ff4141);
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 24px;
            color: white;
            box-shadow: 0 0 20px rgba(255,100,50,0.6);
            animation: float 3s ease-in-out infinite;
        }
        .tagline {
            margin-top: 25px;
            font-size: 15px;
            opacity: 0.85;
            font-style: italic;
        }

        /* Animations */
        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
            100% { transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
         

        
        
        #footer {
            width: 100%;
            padding: 20px 20px;
            display: none; 
            font-family: 'Poppins', sans-serif;
            color: white;
            border-radius: 0%;
            text-align: center;
            height: 50vh;
        }

       
        .footer-grid {
            margin: 0 auto; 
        }

        /* Footer cards */
        .footer-card {
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            border-radius: 15px;
            padding: 25px;
            transition: 0.3s ease;
            backdrop-filter: blur(6px);
        }
        .footer-card:hover {
            transform: translateY(-8px);
            background: rgba(255,255,255,0.15);
        }

        /* Headings */
        .footer-heading {
            color: #ffae42;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 18px;
            text-shadow: 0 0 6px rgba(0,0,0,0.6);
        }
        .footer-card hr {
            margin: 10px auto 20px;
            border: none;
            height: 2px;
            width: 80%;
            background: linear-gradient(90deg, #ffae42, #ff4141);
            border-radius: 3px;
        }

        /* Paragraphs */
        .footer-card p {
            font-size: 14px;
            line-height: 1.6;
            opacity: 0.9;
             
        }
                

        #copyr{
            bottom: 0; 
            position: fixed;
            display: flex;
            width: 100%;
            height: 40px;
            background: linear-gradient(
                90deg, 
                #8c2f2f 0%, 
                #672222 50%, 
                #8c2f2f 100%
            );
            color:white;
            align-items: center;
            text-align:center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }
        
        

        /* Side Navigation Styles */
         #sidenav {
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .sidenav {
            background-color: #672222;
            height: 100%;
            width: 0;
            position: fixed;
            z-index: 1050;
            top: 0;
            right: 0;
            overflow-x: hidden;
            padding-top: 50px;
            transition: 0.3s;
            
        }

        .sidenav a,#admininput {
            padding: 10px 30px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
            
        }
        .action-buttons {
            display: flex;          
            gap: 10px;             
        }

        .action-buttons input {
            flex: 1;                
        }
        
        #exit:hover {
            background-color: white;
            color:#672222;
            border-radius: 10px 0 0 10px;
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

        /* End Side Navigation Styles */
        
        #droptmc{
            background-color: rgba(255, 255, 255, 0.1);

        }
        .dropdown-item{
            font-family: cursive;
            transition: 0.3s;
            color: white;
        }
        .dropdown-item:hover{
            background-color: #672222; 
            color: white;
        }
        .nav-link{
            margin-right:20px;
            font-family: cursive;
            transition: 0.3s;
            color: white;
            
        }
        #title{
            text-shadow: 0 0 5px black, 0 0 10px black;
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
        }
        .nav-link:hover{
            border-bottom: 3px solid #672222;
            color: white !important;
            position:none;
            text-shadow: 0 0 5px #672222, 0 0 10px #672222;
            
        }
        .nav-link:active {
            border-bottom: 3px solid #672222;
            color: #672222 !important;

            
        }
        .nav-link:focus{
            color: #672222 !important;
            border-bottom: 3px solid #672222;
            text-shadow: 0 0 5px white, 0 0 10px #672222;
            
        }
        .rotated {
             transform: rotate(180deg);
        }



        #modcon{
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        #modcon2{
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        #close:hover,#regcancel:hover,#regcreate:hover,#login:hover{
            transform: scale(1.2);
            box-shadow: inset 0 4px 6px rgba(255, 255, 255, 0.47);
        }
        .acclink{
            text-decoration: none;
            cursor: pointer;
        }
        .acclink:hover{
            text-decoration: underline;
            color: #672222 !important;
            text-shadow: 0 0 5px white, 0 0 10px #672222;
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
            animation: spin 1s infinite;
        }

        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
         /* end ng loader */


         /* for admin float label*/
        .col-form-label {
            position: absolute;
            left: 63px;
            top: 1.5px;
            color: grey;
            font-size: 15px;
            pointer-events: none;
            transition: all 0.2s ease;
            font-family: 'cursive';
            
        }
        
        .form-control:focus {
            border: none;
            box-shadow: 0 0 12px rgba(103, 34, 34, 0.8);
            outline: none ;
            
            
        }

        /* make select mimic input styling */
        .form-select:focus {
            border: none;
            box-shadow: 0 0 12px rgba(103, 34, 34, 0.8);
            outline: none ;
        }

        .form-control:focus ~ .col-form-label,
        .form-control:not(:placeholder-shown) ~ .col-form-label {
            padding-bottom: 0px;
            padding-left: 5px;
            padding-right: 5px;
            top: -30px;
            left: 57px;
            font-size: 15px;
            color: white;
            background: none;
            z-index: 20; 
        }

        /* float label for select */
        .form-select:focus ~ .col-form-label,
        .form-select.has-value ~ .col-form-label {
            padding-bottom: 0px;
            padding-left: 5px;
            padding-right: 5px;
            top: -30px;
            left: 57px;
            font-size: 15px;
            color: #672222 !important;
            background: none;
            z-index: 20;
        }



        /* end user float label*/
        #ulabel,#plabel {
            position: absolute;
            left: 63px;
            top: 1.5px;
            color: grey;
            font-size: 15px;
            pointer-events: none;
            transition: all 0.2s ease;
            font-family: 'cursive';
            
        }

        #u:focus,#p:focus {
            border: none;
            box-shadow: 0 0 12px rgba(103, 34, 34, 0.8);
            outline: none ;
            
            
        }
        
        #u:focus ~ #ulabel,
        #p:focus ~ #plabel,
        #u:not(:placeholder-shown) ~ #ulabel,
        #p:not(:placeholder-shown) ~ #plabel {
            padding-bottom: 0px;
            padding-left: 5px;
            padding-right: 5px;
            top: -30px;
            left: 57px;
            font-size: 15px;
            color: #672222;
            background: none;
            z-index: 20; 
        }
        

        .mb-3 {
            position: relative;
            margin: 20px 0;
        }

        /* Responsive for #btnnav */
        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.35);
        }
       
        #btnnav .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255,255,255, 0.9)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }

        @media (max-width: 992px) {
            /* When collapsed, let items stack nicely */
            #btnnav .navbar-collapse {
                padding: 10px 0;
            }
            #frow, #srow {
                margin: 10px 0;
                gap: 12px;
                width: 100%;
                flex-wrap: wrap;
                justify-content: center;
            }
            #btnnav .bt {
                width: 100%;
            }
            #btnnav .dropdown,
            #btnnav .dropdown > .btn,
            #btnnav .dropdown-menu {
                width: 100%;
            }
            #btnnav .dropdown-menu .dropdown-item {
                white-space: normal;
                text-align: center;
            }
        }

        @media (max-width: 576px) {
            /* Tighter spacing on very small screens */
            #btnnav .bt { 
                padding-top: 8px;
                padding-bottom: 8px;
            }
        }

        </style>
    </head>
    <body>
        <div class="loader-overlay">
          <div class="circle-loader"></div>
        </div>


         <!-- Side Nav -->
        <div id="mySidenav" class="sidenav">
            <div class="span">
                <button type="button" id="closeBtn" style=" margin-left: 25px;  background-color: white !important;" class="btn-close" aria-label="Close"></button>
                
            </br>
            </br>
                <form autocomplete="off" method="POST" action="Main.php">
                    <a href="#" id="adminlogin" style="border-radius:0%; text-align:center; font-weight: 600;font-size: 22px;letter-spacing: 2px;text-transform: uppercase;" >Admin Login</a>
                    <div id="admininput" style="margin-top: 0px; padding-top: 0px;">
                    </br>
                        

                        <div class="input-group mb-4">
                            <span class="input-group-text" style="width:50px; justify-content:center;">
                                <img src="user.ico"  height="auto" class="img-fluid d-block me-2" id="userico" style="width: 35px; margin-top:3px; cursor: pointer; position:absolute; left:7px;"/>
                            </span>
                            <input class="form-control" type="textbox" placeholder="" aria-label="Username" id="admin-user" name="aduser" style="border-radius: 0 6px 6px 0;"/>
                            <label for="admin-user" id="lbl1" class="col-form-label" id="u">Username</label>
                                    
                        </div>
                        
                        <div class="input-group mb-4">
                            <span class="input-group-text" style="width:50px; justify-content:center;">
                                <img src="lock.ico"  height="auto" class="img-fluid d-block me-2" id="lockico" style="width: 35px; margin-top:2px; cursor: pointer; position:absolute; left:7px;"/>
                            </span>
                            <input class="form-control" type="password" placeholder="" aria-label="Password" id="admin-pass" name="adpass" style="border-radius: 0 6px 6px 0;"/>
                            <label for="admin-pass" id="lbl2" class="col-form-label" id="p">Password</label>
                            <input type="hidden" name="businessunit" class="businessunit">
                                    
                        </div>
                        
                        
                        <div class="action-buttons">
                            <input type="submit" id="adlogin" name="adlogin" class="btn btn-primary" style="transition: 0.3s; background-color:#672222;  border:none;" value="LOG IN">
                            <input type="submit" id="adcancel" name="adcancel" class="btn btn-secondary" style="transition: 0.3s;  border:none;" value="CANCEL">
                        </div>
                    </div>
                    <a href="#" style="font-family: cursive;" id="exit">Exit</a>
                    
                </form>
            </div>

            <div class="modal-footer" style="position:absolute; bottom:0px; left:23%;">
                <div style="margin-top: 30%; color:white;" class="text-center">
                   
                        <h6 class="warning"> TMC Copyright © <span class="year"></span></h6>
                  
                </div>
            </div>
        </div>


       
         <div class="poly-center">
            <span>T M C</span>
         </div>

        <nav id="mainnav" class="navbar navbar-expand-lg">
          
                <img src="ico.png" id="sidenav" height="auto" style="position: absolute; right: 2%;"  class="img-fluid" alt="logo"/>

            
        </nav>
        
         <nav id="btnnav" class="navbar navbar-expand-lg fixed-top  px-3">
            <div class="container-fluid">
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarButtons" aria-controls="navbarButtons" aria-expanded="false" aria-label="Toggle navigation" id="navbar-toggler">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse justify-content-between" id="navbarButtons">
        
                    <div id="frow" class="d-flex">
                        <button id="home" type="button" class="btn btn-secondary text-nowrap bt" data-bs-dismiss="modal">HOME</button>
                        <button id="about" type="button" class="btn btn-secondary text-nowrap bt" data-bs-dismiss="modal">ABOUT</button>
                    </div>

             
                    <div id="srow" class="d-flex">
                        <button id="contact" type="button" class="btn btn-secondary text-nowrap bt" data-bs-dismiss="modal">CONTACT</button>
                        
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle text-nowrap bt" type="button" id="unitdrop" data-bs-toggle="dropdown" aria-expanded="false">
                                BUSINESS UNIT
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="unitdrop" id="droptmc">
                                <li><a class="dropdown-item" id="gasunit" href="#">Gasoline Stations</a></li>
                                <li><a class="dropdown-item" id="leaseunit" href="#">Commercial Leasing</a></li>
                                <li><a class="dropdown-item" id="foodunit" href="#">Bro's Inasal</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
       



        <div id="bodycontainer">

            <!-- Home -->
            <div id="title" class="section home" >
                <h2 style="margin-top: 105px;">WELCOME TO</h2>
                <hr>
                <h1>Integrated Record Management And Monitoring System Of Tmc’s Multi-Industry Operations</h1>
                <hr>
                <p>Marana 1st, City of Ilagan, Isabela</p>
            </div>

            <!-- About -->
            <div id="aboutcon" class="section dark" style="height: 100%;">
                <h2 class="heading" style="margin-top:0px;">What is TMC?</h2>
                <p>
                    TMC is a family-founded, multi-industry business based in Ilagan City. It has grown into a trusted name in fuel distribution, commercial leasing, and food service through bro’s Inasal. Guided by values of hard work, integrity, and service, TMC continues to uplift communities with quality and affordable solutions.
                </p>

                <h2 class="heading">VISION</h2>
                <p>
                    TMC aspires to become a premier multi-industry enterprise, not only in the City of Ilagan but also across the Philippines. Anchored in a legacy of dedication and entrepreneurial spirit, the company seeks to be recognized for its resilience, innovation, and unwavering commitment to service excellence empowering communities and enriching the everyday lives of the Filipino people.
                </p>

                <h2 class="heading">MISSION</h2>
                <p>
                    TMC is committed to delivering high-quality and dependable services across its core business segments: fuel distribution, food service, and commercial leasing. Guided by the founding couple’s values of hard work, perseverance, and integrity, the company aims to contribute to local and national development by providing accessible energy solutions, affordable and satisfying meals, and dynamic commercial spaces that support economic growth.
                </p>
            </div>

            <!-- Contact Developers -->
            <div id="contactcontainer" class="section darker">
                <div id="developer" class="container text-center" style="margin-top: 40px;;">
                    <h2>Meet the Proprietors of the Business</h2>

                    <div class="row justify-content-center mt-4 g-4">
                        
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="dev-card h-100 w-100">
                                <div class="circle">WD</div>
                                <h3>Wilhelmina Dela Cruz</h3>
                                <p class="role">Co-Owner</p>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="dev-card h-100 w-100">
                                <div class="circle">TD</div>
                                <h3>Tito dela Cruz</h3>
                                <p class="role">Co-Owner</p>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="dev-card h-100 w-100">
                                <div class="circle">MD</div>
                                <h3>Mark Cyrene Dela cruz</h3>
                                <p class="role">Co-Owner</p>

                            </div>
                        </div>
                        
                    </div>

                    <p class="tagline mt-4">
                       "A trusted provider of fuel, commercial leasing, and Quality Food"

                    </p>
                </div>
            </div>
        </div>



        <div id="footer" class="section darker container-fluid">
            <div class="row g-4"> 

                <div id="gas" class="col-12 col-md-6 col-lg-4">
                    <div class="footer-card h-100">
                        <h2 class="footer-heading">GASOLINE STATIONS</h2>
                        <hr>
                        <p>
                            TMC entered the fuel industry in 2015 with its first gasoline station, marking the official start of
                            the TMC brand. In 2016, the company expanded with two more stations.
                        </p>
                    </div>
                </div>

                <div id="lease" class="col-12 col-md-6 col-lg-4">
                    <div class="footer-card h-100">
                        <h2 class="footer-heading">COMMERCIAL LEASING</h2>
                        <hr>
                        <p>
                            TMC entered the commercial leasing industry in 2021 by offering affordable and strategic business
                            spaces for local entrepreneurs.
                        </p>
                    </div>
                </div>

                <div id="bro" class="col-12 col-md-6 col-lg-4">
                    <div class="footer-card h-100">
                        <h2 class="footer-heading">BRO'S INASAL</h2>
                        <hr>
                        <p>
                            TMC entered the fast-food industry in 2025. Bro’s Inasal is a fast-food chain serving flavorful and
                            affordable grilled chicken meals.
                        </p>
                    </div>
                </div>

            </div>
        </div>

         <div id="copyr" class="text-center">

            <p >
            <h6 style="margin-top: 10px;">TMC Copyright © <span class="year"></span> Terms of Service | Data Privacy Policy</h6>
             </p>
        </div>



        <form autocomplete="off" method="POST" action="Main.php">
            <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" id="modcon">
                        <div class="modal-header" style="background: linear-gradient(90deg, #672222, #8c2f2f);">
                            <img src="tmclogo.png" width="5%" height="auto" class="img-fluid d-block me-2" id="logo" style="width: 35px; height: auto; cursor: pointer;"/>
                            <h1 class="modal-title fs-5" id="exampleModalLabel" style="color:white;">LOGIN FORM</h1>
                            <button type="button" style="background-color:white;" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="input-group mb-3" id="branchRow" style="display:none;">
                                <span class="input-group-text" style="width:50px; justify-content:center;">
                                    <img src="gasicon2.png" height="auto" class="img-fluid d-block me-2" style="width: 35px; margin-top:2px; cursor: default; position:absolute; left:7px;"/>
                                </span>
                                <select class="form-select " name="branch" id="branchSelect" style="border-radius: 0 6px 6px 0;">
                                    <option value="" disabled selected></option>
                                    <option value="Branch 1">Branch 1</option>
                                    <option value="Branch 2">Branch 2</option>
                                    <option value="Branch 3">Branch 3</option>
                                </select>
                                <label for="branchSelect" class="col-form-label">Branch</label>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" style="width:50px; justify-content:center;">
                                    <img src="user.ico"  height="auto" class="img-fluid d-block me-2" id="userico" style="width: 35px; margin-top:3px; cursor: pointer; position:absolute; left:7px;"/>
                                </span>
                                <input type="text" name="txtuser" placeholder="" class="form-control" id="u" style="border-radius: 0 6px 6px 0;">
                                <label for="u" id="ulabel" class="col-form-label" >Username</label>
                                
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" style="width:50px; justify-content:center;">
                                    <img src="lock.ico"  height="auto" class="img-fluid d-block me-2" id="lockico" style="width: 35px; margin-top:2px; cursor: pointer; position:absolute; left:7px;"/>
                                </span>
                                <input type="password" placeholder="" name="txtpass" class="form-control" id="p" style="border-radius: 0 6px 6px 0;">
                                <label for="p" id="plabel" class="col-form-label">Password</label>
                                <input type="hidden" name="businessunit" class="businessunit">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <span style="position:absolute;left:2%;">
                            <a class="acclink" name="acclink" style="transition: 0.3s; color: #672222;">Create Account</a>
                            </span>
                            <button type="button" id="close" class="btn btn-secondary" data-bs-dismiss="modal" style="transition: 0.3s; margin-right:5%; border:none;">CANCEL</button>
                            <input type="submit" id="login" name="btnlogin" class="btn btn-primary" style="transition: 0.3s; background-color:#672222; border:none;" value="LOG IN">
                        </div>
                    </div>
                </div>
            </div>
        </form>


        
        <form autocomplete="off" method="POST" action="Main.php">
    <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="exampleModal2" tabindex="-1" aria-labelledby="regModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"> <!-- removed modal-lg -->
            <div class="modal-content" id="modcon2">
                <div class="modal-header" style="background: linear-gradient(90deg, #672222, #8c2f2f);">
                    <img src="tmclogo.png" class="img-fluid d-block me-2" id="logo" style="width: 35px; height: auto; cursor: pointer;"/>
                    <h1 class="modal-title fs-5" id="regModalLabel" style="color:white;">REGISTER FORM</h1>
                    <button type="button" class="btn-close" style="background-color:white;" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <!-- Branch -->
                    <div class="mb-3" id="regBranchRow" style="display:none;">
                        <label for="regBranchSelect" class="form-label">Branch :</label>
                        <select class="form-select" name="branch" id="regBranchSelect">
                            <option value="" disabled selected> -- Select Branch -- </option>
                            <option value="Branch 1">Branch 1</option>
                            <option value="Branch 2">Branch 2</option>
                            <option value="Branch 3">Branch 3</option>
                        </select>
                    </div>

                    <!-- Firstname -->
                    <div class="mb-3">
                        <label for="fname" class="form-label">Firstname :</label>
                        <input type="text" placeholder="Enter Password" name="fname" class="form-control" id="fname">
                    </div>

                    <!-- Middlename -->
                    <div class="mb-3">
                        <label for="mname" class="form-label">Middlename :</label>
                        <input type="text" placeholder="Enter Middlename" name="mname" class="form-control" id="mname">
                    </div>

                    <!-- Lastname -->
                    <div class="mb-3">
                        <label for="lname" class="form-label">Lastname :</label>
                        <input type="text" placeholder="Enter Lastname" name="lname" class="form-control" id="lname">
                    </div>

                    <!-- Username -->
                    <div class="mb-3">
                        <label for="username" class="form-label">Username :</label>
                        <input type="text" placeholder="Enter Username" name="username" class="form-control" id="username">
                    </div>

                    
                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label">Password :</label>
                        <div class="d-flex gap-2">
                            <input type="password" placeholder="Enter Password" name="password" class="form-control" id="password"
                                required
                                minlength="8"
                                maxlength="20"
                                pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&_])[A-Za-z\d@$!%*?&_]{8,20}$"
                                title="Password must be 8–20 characters, include at least one uppercase letter, one lowercase letter, one number, and one special character.">
                            <input placeholder="Re-enter Password" type="password" name="retype" class="form-control" id="retype">
                        </div>
                        <div id="password-strength" style="margin-top:5px; font-weight:bold;"></div>
                        
                        <small style="font-size: 10px;" class="form-text text-muted">
                            Your password must be 8–20 characters, include at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).
                        </small>
                    </div>

                    

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email :</label>
                        <input type="email" placeholder="example@gmail.com"  name="email" class="form-control" id="email" required>
                    </div>

                    <!-- Address -->
                    <div class="mb-3">
                        <label for="address" class="form-label">Address :</label>
                        <!-- Address -->
                        <select id="province" class="form-control mb-2">
                        <option value="">-- Select Province --</option>
                        </select>

                        <select id="city" class="form-control mb-2">
                        <option value="">-- Select City/Municipality --</option>
                        </select>

                        <select id="barangay" class="form-control mb-2">
                        <option value="">-- Select Barangay --</option>
                        </select>

                        <input type="text" id="street" class="form-control mb-2" placeholder="Street">
                        <textarea id="address" class="form-control" disabled readonly></textarea>
                        <input type="hidden" name="address" id="address_hidden">
                        
                    </div>

                    <!-- Birthdate -->
                    <div class="mb-3">
                        <label for="bdate" class="form-label">Birthdate :</label>
                        <input type="date" name="bdate" class="form-control" id="bdate" style="color:grey;">
                        <input type="hidden" name="businessunit" class="businessunit">
                    </div>
                </div>

                <div class="modal-footer">
                    <span style="position:absolute; left:2%;">
                        <a class="acclink" id="backlog" name="acclink" href="#" style="transition: 0.3s; color:#672222;">Back to Login Page</a>
                    </span>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="margin-right:5%; border:none;" id="regcancel">CANCEL</button>
                    <input type="submit" name="btnregister" class="btn btn-primary" style="background-color:#672222; border:none;" id="regcreate" value="CREATE ACCOUNT">
                </div>
            </div>
        </div>
    </div>
</form>



        






        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    </body>
</html>
<script>
     $(document).ready(function(){
        $('.year').text(new Date().getFullYear());

        $("#home").addClass("active");

        $(".bt").on("click", function() {
            $(".bt").removeClass("active"); // remove active from all
            $(this).addClass("active").focus(); // add active + focus
        });


        
        function hideLoaderAfter3Sec() {
            setTimeout(function(){
                $(".loader-overlay").css("display", "none");
            }, 3000); // 3000ms = 3 seconds
        }

        const btn = document.querySelector('.sidenav');

        btn.addEventListener('mousemove', (e) => {
        const rect = btn.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        btn.style.setProperty('--x', x + 'px');
        btn.style.setProperty('--y', y + 'px');
        });

        
        // side nav
        $("#sidenav").click(function(){
            $("#mySidenav").css("width", "300px");
            $("#mySidenav").css("border-radius", "10px 0 0 10px");
            $("#sidenav").hide();
        });

        $("#closeBtn").click(function(){
            $("#mySidenav").css("width", "0");
            $("#sidenav").show();
        });



        $("#login").click(function(){
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();
        });
        $("#regcreate").click(function(e){
            console.log("Registration button clicked");
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();
            // Don't prevent default - let form submit naturally
        });
         $("#adlogin").click(function(){
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();
        });

        
        $("#regcreate").click(function(){
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();
        });


        $("#gasunit").click(function(){
            $("#exampleModal").modal("show");
            $("#exampleModalLabel").text("GASOLINE STATION LOGIN FORM");
            $('#title').hide();
            $('.businessunit').val("Gasoline Station");
            $('#branchRow').show();
            setTimeout(function(){ $('#branchSelect').trigger('change'); }, 0);
            $("#aboutcon").hide();
            $("#footer").hide();
            $("#contactcontainer").hide();
        });
        $("#leaseunit").click(function(){
            $("#exampleModal").modal("show");
            $("#exampleModalLabel").text("COMMERCIAL LEASING LOGIN FORM");
            $('#title').hide();
            $('.businessunit').val("Commercial Leasing");
            $('#branchRow').hide();
            $('#branchSelect').val('').trigger('change');
            $("#aboutcon").hide();
            $("#footer").hide();
            $("#contactcontainer").hide();
        });
        $("#foodunit").click(function(){
            $("#exampleModal").modal("show");
            $("#exampleModalLabel").text("BRO'S INASAL LOGIN FORM");
            $('#title').hide();
            $('.businessunit').val("Bros Inasal");
            $('#branchRow').hide();
            $('#branchSelect').val('').trigger('change');
            $("#aboutcon").hide();
            $("#footer").hide();
            $("#contactcontainer").hide();
        });
        $("#sidenav").click(function(){    
            $('.businessunit').val("Admin");
            $('#branchRow').hide();
            $('#branchSelect').val('').trigger('change');
        });


        $(".acclink").click(function(){
            // Get the current business unit and set appropriate title
            var currentBU = $('.businessunit').first().val();
            var regTitle = "REGISTER FORM";
            
            if(currentBU === "Gasoline Station"){
                regTitle = "GASOLINE STATION REGISTER FORM";
            } else if(currentBU === "Commercial Leasing"){
                regTitle = "COMMERCIAL LEASING REGISTER FORM";
            } else if(currentBU === "Bros Inasal"){
                regTitle = "BRO'S INASAL REGISTER FORM";
            }
            
            $("#regModalLabel").text(regTitle);
            $("#exampleModal2").modal("show");
            $("#exampleModal").modal("hide");
            $('#title').hide();
            $("#aboutcon").hide();
            $("#footer").hide();
            $("#contactcontainer").hide();
        });

         // Removed problematic handler that was preventing form submission










        $("#backlog").click(function(){
            if ($("#regModalLabel").text() === "BRO'S INASAL REGISTER FORM") {
                $("#exampleModal2").modal("hide");
                $("#exampleModal").modal("show");
                $("#exampleModalLabel").text("BRO'S INASAL LOGIN FORM");
                $('#title').hide();
                $('.businessunit').val("Bros Inasal");
                $('#branchRow').hide();
                $('#branchSelect').val('').trigger('change');
                $("#aboutcon").hide();
                $("#footer").hide();
                $("#contactcontainer").hide();
            }
            else if ($("#regModalLabel").text() === "COMMERCIAL LEASING REGISTER FORM") {
                $("#exampleModal2").modal("hide");
                $("#exampleModal").modal("show");
                $("#exampleModalLabel").text("COMMERCIAL LEASING LOGIN FORM");
                $('#title').hide();
                $('.businessunit').val("Commercial Leasing");
                $('#branchRow').hide();
                $('#branchSelect').val('').trigger('change');
                $("#aboutcon").hide();
                $("#footer").hide();
                $("#contactcontainer").hide();
            }
            else if ($("#regModalLabel").text() === "GASOLINE STATION REGISTER FORM") {
                $("#exampleModal2").modal("hide");
                $("#exampleModal").modal("show");
                $("#exampleModalLabel").text("GASOLINE STATION LOGIN FORM");
                $('#title').hide();
                $('.businessunit').val("Gasoline Station");
                $('#branchRow').show();
                setTimeout(function(){ $('#branchSelect').trigger('change'); }, 0);
                $("#aboutcon").hide();
                $("#footer").hide();
                $("#contactcontainer").hide();
            }
        });


      
        



         $(".btn-close,#regcancel,#close").click(function(){
            $('#title').show();
            $("#aboutcon").hide();
            $("#footer").hide();
            $("#contactcontainer").hide();
            $('#branchRow').hide();
            $('#branchSelect').val('').trigger('change');
        });

        // float label behavior for select (login)
        $('#branchSelect').on('change', function(){
            if($(this).val()){
                $(this).addClass('has-value');
            } else {
                $(this).removeClass('has-value');
            }
        });

        // show/hide registration branch based on business unit
        function updateRegBranchVisibility(){
            var buVal = $('.businessunit').first().val();
            if(buVal === 'Gasoline Station'){
                $('#regBranchRow').show();
            } else {
                $('#regBranchRow').hide();
                $('#regBranchSelect').val('').trigger('change');
            }
        }

        // when opening registration
        $('#exampleModal2').on('shown.bs.modal', function(){
            updateRegBranchVisibility();
        });

        // keep float-like behavior for registration branch select
        $('#regBranchSelect').on('change', function(){
            if($(this).val()){
                $(this).addClass('has-value');
            } else {
                $(this).removeClass('has-value');
            }
        });


       $('#togglerBtn').on('click', function () {
             $('#togglerIcon').toggleClass('rotated');
        });




        $("#home").click(function(){
            $('#title').fadeIn(600); 
            $("#title").show();
            $("#aboutcon").hide();
            $("#footer").hide();
            $("#copyr").css("position", "fixed");
            $("#contactcontainer").hide();
            
        });
        
        $("#navbar-toggler").click(function(){ 
            $("#title").hide();

        });
        $("#unitdrop").click(function(){
            $("#title").hide();
            $("#aboutcon").hide();
            $("#footer").hide();
            $("#contactcontainer").hide();
             $("#copyr").css("position", "fixed");
            
        });
        $("#about").click(function(){
            $('#aboutcon').fadeIn(600); 
            $("#title").hide();
            $("#aboutcon").show();
            $("#footer").show();
            $("#copyr").css("position", "static");
            $("#footer").css("display", "flex");
            $("#contactcontainer").hide();
        });
        $("#contact").click(function(){
            $('#contactcontainer').fadeIn(600); 
            $("#contactcontainer").show();
            $("#contactcontainer").css("display", "flex");
            $("#copyr").css("position", "static");
            $("#title").hide();
            $("#aboutcon").hide();
            $("#footer").hide();
            $("#footer").hide();
        });





        const $province = $("#province");
        const $city = $("#city");
        const $barangay = $("#barangay");
        const $street = $("#street");
        const $address = $("#address");

        // 1. Load provinces
        $.getJSON("https://psgc.gitlab.io/api/provinces/", function (provinces) {
            $.each(provinces, function (_, p) {
                $province.append(`<option value="${p.code}">${p.name}</option>`);
            });
        });

        // 2. When province changes → load cities
        $province.on("change", function () {
            let provinceCode = $(this).val();
            $city.html(`<option value="">-- Select City/Municipality --</option>`);
            $barangay.html(`<option value="">-- Select Barangay --</option>`);

            if (provinceCode) {
                $.getJSON(`https://psgc.gitlab.io/api/provinces/${provinceCode}/cities-municipalities/`, function (cities) {
                    $.each(cities, function (_, c) {
                        $city.append(`<option value="${c.code}">${c.name}</option>`);
                    });
                });
            }
            updateAddress();
        });

        // 3. When city changes → load barangays
        $city.on("change", function () {
            let cityCode = $(this).val();
            $barangay.html(`<option value="">-- Select Barangay --</option>`);

            if (cityCode) {
                $.getJSON(`https://psgc.gitlab.io/api/cities-municipalities/${cityCode}/barangays/`, function (barangays) {
                    $.each(barangays, function (_, b) {
                        $barangay.append(`<option value="${b.name}">${b.name}</option>`);
                    });
                });
            }
            updateAddress();
        });

        // 4. Update full address when barangay/street changes
        $barangay.on("change", updateAddress);
        $street.on("input", updateAddress);

        function updateAddress() {
            const fullAddress = [
                $street.val(),
                $barangay.val(),
                $city.find("option:selected").text(),
                $province.find("option:selected").text()
            ].filter(Boolean).join(", ");
            
            $address.val(fullAddress);
            $("#address_hidden").val(fullAddress);
        }


        $('#password').on('input', function() {
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

            if(val.length >= 8 && conditionsMet === 3){
                strength = 'Strong';
                color = 'green';
            } else if(val.length >= 6 && conditionsMet >= 2){
                strength = 'Medium';
                color = 'orange';
            } else {
                strength = 'Weak';
                color = 'red';
            }

            $('#password-strength').text(strength).css('color', color);
        });
    });
</script>