<?php
    session_start();

    // Resolve full display name for "Prepared By"
    $preparedByDisplay = isset($_SESSION['username']) ? $_SESSION['username'] : 'System Administrator';
    $rawUser = isset($_SESSION['username']) ? trim($_SESSION['username']) : '';
    if ($rawUser !== '') {
        $conNames = @mysqli_connect('localhost','root','root','tmc_db');
        if ($conNames) {
            // 1) Check pending users
            $uEsc = mysqli_real_escape_string($conNames, $rawUser);
            $sqlPending = "SELECT fname, mname, lname FROM tbl_pending_users WHERE username='$uEsc' LIMIT 1";
            $resPending = mysqli_query($conNames, $sqlPending);
            $full = '';
            if ($resPending && mysqli_num_rows($resPending) > 0) {
                $row = mysqli_fetch_assoc($resPending);
                $fn = isset($row['fname']) ? trim($row['fname']) : '';
                $mn = isset($row['mname']) ? trim($row['mname']) : '';
                $ln = isset($row['lname']) ? trim($row['lname']) : '';
                $full = trim(preg_replace('/\s+/', ' ', $fn . ' ' . $mn . ' ' . $ln));
            } else {
                // 2) Check approved users via tbl_user -> tbl_userinfo
                $sqlUser = "SELECT ui.fname, ui.mname, ui.lname
                            FROM tbl_user u
                            JOIN tbl_userinfo ui ON ui.idnum = u.idnum
                            WHERE u.username='$uEsc'
                            LIMIT 1";
                $resUser = mysqli_query($conNames, $sqlUser);
                if ($resUser && mysqli_num_rows($resUser) > 0) {
                    $row = mysqli_fetch_assoc($resUser);
                    $fn = isset($row['fname']) ? trim($row['fname']) : '';
                    $mn = isset($row['mname']) ? trim($row['mname']) : '';
                    $ln = isset($row['lname']) ? trim($row['lname']) : '';
                    $full = trim(preg_replace('/\s+/', ' ', $fn . ' ' . $mn . ' ' . $ln));
                }
            }
            if ($full !== '') {
                $preparedByDisplay = $full;
            }
        }
    }
?>




<!DOCTYPE html>
<html>
    <head>
        
        <meta charset="utf-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Gas Main Page</title>
        <link rel="icon" type="png" href="tmclogo.png">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">



        <style>
            .check_box{
                display: none;
            }
            header {
                background-color: #672222;
                color: white;
                font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
                text-shadow: 2px 2px 2px black;
                padding: 10px 20px;
                display: flex;
                align-items: center;
                gap: 18px;
            }


            /* Search Container */
            .header-search {
                display: flex;
                align-items: center;
                width: 30%;
                
                max-width: 400px;
                background: rgba(255, 255, 255, 0.15);
                border-radius: 10px;
                padding: 5px 10px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.2);
                backdrop-filter: blur(6px);
                transition: all 0.3s ease;
            }

            /* Input */
            .header-search input {
                flex: 1;
                border: none;
                outline: none;
                background: transparent;
                padding: 10px 15px;
                height: 50%;
                color: white;
                font-size: 14px;
            }

            /* Placeholder color */
            .header-search input::placeholder {
                color: #ddd;
                opacity: 0.8;
            }

            /* Search Button */
            .btn-search {
                background: #ffffff;
                border: none;
                padding: 8px 16px;
                border-radius: 10px;
                cursor: pointer;
                font-weight: bold;
                font-size: 14px;
                color: #672222;
                transition: all 0.3s ease;
                box-shadow: 0 3px 6px rgba(0,0,0,0.2);
            }

            /* Hover effect */
            .btn-search:hover {
                background: #672222;
                color: white;
                transform: scale(1.05);
            }

            /* Responsive */
            @media (max-width: 768px) {
                .header-search {
                    order: 99; /* push it below everything */
                    width: 100%; /* take full width */
                    margin-top: 10px;
                }
            }

















            /* Side Navigation Styles */
            .sidenav {
                background-color: rgba(63, 4, 4, 0.91);
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
                right: 0px;
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


           
            





             /* table design */
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








        /*  Modal Styles */
        .modal-header {
                padding: 1rem 1.5rem;
                border-bottom: none;
                background: linear-gradient(135deg, #672222, #934040);
                color: #fff;
                font-weight: bold;
            }

            .modal-title {
                font-size: 1.1rem;
                letter-spacing: 1px;
                font-weight: bold;
            }

            .modal-body {
                padding: 2rem;
                background-color: #fafafa;
            }

            .modal-footer {
                padding: 1rem 1.5rem;
                background-color: #f8f9fa;
                border-top: none;
            }

            /* Labels */
            .form-label {
                font-weight: 600;
                color: #444;
                font-size: 0.9rem;
            }

            /* Inputs */
            .form-control,
            .form-select {
                border-radius: 10px;
                border: 1px solid #ddd;
                padding: 0.65rem 0.9rem;
                font-size: 0.95rem;
                transition: all 0.3s ease;
            }

            .form-control:focus,
            .form-select:focus {
                border-color: #672222;
                box-shadow: 0 0 0 0.25rem rgba(103, 34, 34, 0.25);
            }

            /* Divider */
            hr {
                border-radius: 4px;
                margin: 1.5rem 0;
            }

            /* Buttons */
            .btn {
                border-radius: 10px;
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            }

            /* Primary Submit */
            #login {
                background: linear-gradient(135deg, #672222, #934040);
                color: #fff;
            }

            #login:hover {
                background: linear-gradient(135deg, #934040, #672222);
            }

            /* Cancel */
            #close {
                background: #e0e0e0;
                color: #333;
            }

            #close:hover {
                background: #d6d6d6;
            }

            /* Add Row */
            #addrow {
                background: #672222 !important;
                color: #fff;
                border-radius: 10px;
            }

            #addrow:hover {
                background: #934040 !important;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }
            /*  End Modal Styles  */







            #data_table {
                margin-left: 90px;
                transition: margin-left 0.3s ease;
            }
            .inventory-card {
                margin: 20px 50px 20px 50px; 
                padding: 20px;
                background: #fff;
                border-radius: 15px;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
                overflow-y: auto;
                border: none;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                position: relative;
                overflow: visible;
            }


            /* addlessbtn*/
            .stock-panel {
                position: absolute; /* stays inside the card */
                top:80px;
                right: 20px;
                display: flex;
                gap: 20px;
                padding: 10px;
                background: #e7e2e2ff;
                border-radius: 16px;
                box-shadow: 0 8px 25px rgba(0,0,0,0.4);
                transition: all 0.3s ease;
                opacity: 1;
                z-index: 10;
            }

            .stock-panel.hidden {
                opacity: 0;
                transform: translateY(-50px);
                pointer-events: none;
            }

            .stock-btn {
                min-width: 120px;
                height: 60px;
                font-size: 1rem;
                font-weight: 600;
                border: none;
                border-radius: 12px;
                cursor: pointer;
                color: #fff;
                position: relative;
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 6px 18px rgba(0,0,0,0.25);
                transition: all 0.3s ease;
            }

            .stock-btn.add {
                background: linear-gradient(145deg, #672222, #8c2f2f);
            }

            .stock-btn.add:hover {
                transform: translateY(-3px) scale(1.05);
                box-shadow: 0 12px 25px rgba(103, 34, 34, 0.5);
            }

            .stock-btn.less {
                background: linear-gradient(145deg, #8c2f2f, #b84a4a);
            }

            .stock-btn.less:hover {
                transform: translateY(-3px) scale(1.05);
                box-shadow: 0 12px 25px rgba(140, 47, 47, 0.5);
            }

            .stock-btn::after {
                content: "";
                position: absolute;
                width: 100%;
                height: 100%;
                top: 0; left: 0;
                background: rgba(255,255,255,0.15);
                opacity: 0;
                transition: opacity 0.3s, transform 0.3s;
                transform: scale(0);
                border-radius: 12px;
            }

            .stock-btn:active::after {
                opacity: 1;
                transform: scale(1.5);
                transition: 0s;
            }

            .toggle-btn {
                position: absolute; 
                width: 150px;
                right: 20px;
                padding: 10px 16px;
                font-size: 1rem;
                border: none;
                border-radius: 12px;
                background: #672222;
                color: #fff;
                cursor: pointer;
                box-shadow: 0 6px 18px rgba(0,0,0,0.25);
            }
            

















            /* Chart styling */
            canvas {
                width: 100% !important;
                max-height: 400px;
            }

          
            .card.modern-card {
                border: none;
                border-radius: 15px;
                background-color: #ffffff;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5); /* visible shadow */
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                margin: 47px;
            }



            .card-header.modern-header {
                background: linear-gradient(90deg, #8c2f2f 0%, #672222 50%, #8c2f2f 100%);
                color: #fff;
                font-weight: 600;
                border-top-left-radius: 15px;
                border-top-right-radius: 15px;
            }

            /* Table tweaks */
            .table-hover tbody tr:hover {
                background-color: rgba(140, 47, 47, 0.1);
            }

            .table th, .table td {
                vertical-align: middle;
            }

            /* Badge styling */
            .badge.bg-secondary {
                background-color: #6c757d !important;
                font-size: 0.85rem;
                padding: 0.35em 0.6em;
            }


           

            .table-hover tbody tr:hover {
                background-color: #f8f9fa;
                transition: background-color 0.2s ease-in-out;
            }
            .badge-sale {
                background-color: #ffc107; /* Bootstrap warning yellow */
                color: #000; /* black text */
            }






             /* lub buttons*/
            .button-container {
                display: flex;
                gap: 15px;             
                margin: 0 50px;        
                max-width: calc(100% - 40px); 
            }

            /* Fuel button + input styling (copied from admin, simplified) */
            .fuel-type-wrapper {
                min-height: 140px;
            }
            .fuel-button-container,
            .fuel-input-container {
                min-height: 120px;
                display: flex;
                flex-direction: column;
                transition: all 0.3s ease;
                border-radius: 12px;
                overflow: hidden;
            }
            .fuel-button-container {
                justify-content: center;
                opacity: 1;
                transform: scale(1);
            }
            .fuel-button-container[style*="display: none"] {
                opacity: 0;
                transform: scale(0.9);
            }
            .fuel-type-btn {
                background: linear-gradient(90deg, #672222, #8c2f2f);
                min-height: 120px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
                font-size: 16px;
                letter-spacing: 1px;
                box-shadow: 0 4px 12px rgba(103, 34, 34, 0.3);
                color: #fff;
                border: none;
            }
            .fuel-type-btn:hover {
                transform: translateY(-2px);
                color: #fff;
            }
            .fuel-input-container {
                background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                border: 2px solid #672222;
                padding: 12px;
                box-shadow: 0 6px 20px rgba(103, 34, 34, 0.15);
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }
            .fuel-input-container:focus-within {
                border-color: #8c2f2f;
                box-shadow: 0 8px 25px rgba(103, 34, 34, 0.2);
            }
            .fuel-input-container[style*="display: none"] {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }
            .fuel-input-container .form-label {
                font-weight: 600;
                color: #672222;
                font-size: 14px;
                text-transform: uppercase;
            }
            .close-fuel-input {
                background: linear-gradient(90deg, #672222, #8c2f2f);
                width: 28px;
                height: 28px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                font-weight: bold;
                padding: 0;
                padding-bottom: 5px;
                color: #fff;
                border: none;
            }
            .fuel-input-container .form-control {
                border: 2px solid #dee2e6;
                border-radius: 8px;
                padding: 10px 14px;
                font-size: 15px;
            }
            .fuel-input-container small { display:block; margin-top:4px; }

            .btnlub {
                flex: 1;
                padding: 15px;
                border: none;
                outline: none;
                color: #672222;
                font-size: 1.1rem;
                cursor: pointer;
                background-color: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(5px);
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
                transition: transform 1.2s ease, box-shadow 0.2s ease, filter 1.3s ease;
                position: relative; /* needed for triangle positioning */
            }

          
            .btnlub:hover {
                filter: brightness(1.1);
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
            }

            
            .btnlub:active {
                transform: translateY(0);
                box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
            }

            
            .btnlub::after {
                content: "";
                position: absolute;
                left: 50%;
                transform: translateX(-50%) translateY(6px);
                bottom: -18px; 
                width: 0;
                height: 0;
                border-left: 12px solid transparent;
                border-right: 12px solid transparent;
                border-top: 12px solid transparent; 
                transition: transform 0.15s ease, border-top-color 0.15s ease;
                pointer-events: none;
            }

            .btnlub:focus,
            .btnlub.active {
                transform: translateY(-6px); /* float the button up */
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35); 
                background: linear-gradient(90deg, #672222, #8c2f2f);
                color: white;
            }
            .btnlub:focus::after,
            .btnlub.active::after {
                border-top-color: #672222; /* triangle color */
                transform: translateX(-50%) translateY(0); /* slide into place */
            }

            /* floating add button */
            #addlubbtn {
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
            #addlubbtn:hover {
                filter: drop-shadow(6px 10px 14px rgba(0,0,0,0.4));
                box-shadow: 0 14px 28px rgba(0,0,0,0.45), inset 0 0 0 2px rgba(255,255,255,0.35);
                transform: scale(1.08);
                opacity: 1;
            }
            #addlubbtn:active {
                transform: scale(0.98);
                box-shadow: 0 6px 14px rgba(0,0,0,0.35), inset 0 0 0 2px rgba(255,255,255,0.2);
            }

            /* Product Search Dropdown Styles for Add Sales Modal */
            #addsales .product-search-container {
                position: relative;
                width: 100%;
            }

            #addsales .product-search-input {
                width: 100%;
                padding: 0.65rem 0.9rem;
                border: 1px solid #ddd;
                border-radius: 10px;
                font-size: 0.95rem;
                transition: all 0.3s ease;
            }

            #addsales .product-search-input:focus {
                border-color: #672222;
                box-shadow: 0 0 0 0.25rem rgba(103, 34, 34, 0.25);
                outline: none;
            }

            #addsales .product-dropdown {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                border: 1px solid #ddd;
                border-radius: 0 0 10px 10px;
                max-height: 200px;
                overflow-y: auto;
                z-index: 1000;
                display: none;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }

            #addsales .product-dropdown-item {
                padding: 10px 12px;
                cursor: pointer;
                border-bottom: 1px solid #f0f0f0;
                transition: background-color 0.2s;
                color: #333;
            }

            #addsales .product-dropdown-item:hover {
                background-color: #f8f9fa;
            }

            #addsales .product-dropdown-item:last-child {
                border-bottom: none;
            }
            
        </style>
        <link rel="stylesheet" href="assets/css/admin-unified-theme.css?v=20260217">
    </head>
    <body>
        <div class="loader-overlay">
          <div class="circle-loader"></div>
        </div>
         <header>
            <img src="tmclogo.png" width="3%" height="auto" class="img-fluid" alt="logo" id="logo"/>
            <div class="header-title">
                <h1 class="h2 h3-md m-0" style="margin:0;">
                    <?php
                        $branchTitle = isset($_SESSION['branch']) && $_SESSION['branch'] !== '' ? (' BRANCH ' . $_SESSION['branch']) : '';
                        $headerTitle = 'GASOLINE STATION' . $branchTitle;
                        echo htmlspecialchars($headerTitle);
                    ?>
                </h1>
                
            </div>
            <img src="ico.png" id="sidenav" height="auto" style="position: absolute; right: 2%;"  class="img-fluid" alt="logo"/>
        </header>

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
                        <div style="color: #ccc; font-size: 12px;">
                            <?php echo isset($_SESSION['branch']) ? htmlspecialchars($_SESSION['branch']) .'': 'Gasoline Station'; ?>
                        </div>
                    </div>

                    <button type="button" id="closeBtn"style="background-color:white; color: white; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);"class="btn-close" aria-label="Close"></button>

                </div>
            </div>
            <a href="#" id="addbtn">Add Daily Sales Report</a>
            <a href="#" id="sales">Sales Report</a>
            <a href="#" id="inventory">Fuel Inventory</a>
            
            <a href="#" id="lub" >
                Lubricant Inventory
            </a>
            <a href="#" class="change_userpass">Change User & Pass</a>
            <a href="index.php">Exit</a>
            
        </div>
        

        <div id="data_table" style="margin: 5px; margin-top:15px; margin-left: 50px; margin-right: 50px; overflow-y: auto;box-shadow: 0 8px 24px rgba(0,0,0,0.2); border-radius:10px; border:none;">
            <!-- daily sales will display inside of this tag -->
             
        </div>

        <!-- Sales Details Modal -->
        <div class="modal fade" id="dateDetailsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="dateDetailsLabel">Sales Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="dateDetailsBody">
                        <p class="text-muted mb-0">Select a date to view details.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" style="border:none; background:#672222;" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>





        <div id="lubcon" style="display: none; margin-top:30px; ">
            
            <div id="data_table2" style="margin: 5px; margin-top:25px; margin-left: 50px; margin-right: 50px; overflow-y: auto;box-shadow: 0 8px 24px rgba(0,0,0,0.2); border-radius:10px; border:none; display:none;">
                <!-- lub inventory will display inside of this tag -->
                
            </div>
            <div id="data_table3" style="margin: 5px; margin-top:25px; margin-left: 50px; margin-right: 50px; overflow-y: auto;box-shadow: 0 8px 24px rgba(0,0,0,0.2); border-radius:10px; border:none; display:none;">
                <!-- lub inventory will display inside of this tag -->
                
            </div>
        </div>



        <div id="fuel_inventory" class="inventory-card" style="display:none;">
            
            <button class="stock-btn add toggle-btn" id="btnAddStock">Add Stock</button>
                
            

           

            <center> <h2 id="inventoryTitle" class="h2 h3-md m-0" style="margin:0; padding:5px;">FUEL INVENTORY</h2></center>
            <canvas id="gasInventoryChart" width="400" height="300"></canvas>
            <!-- inventory will display inside of this tag -->
        </div>
        


         <form autocomplete="off" method="POST" action="admingas.php">
            <!-- Scrollable modal -->
            <div class="modal fade" data-bs-backdrop="static" id="addsales" tabindex="-1">
                <div class="modal-dialog modal-dialog-scrollable modal-lg">
                    <div class="modal-content shadow-lg rounded-3">
                        
                        <!-- Header -->
                        <div class="modal-header border-0">
                            <img src="tmclogo.png" 
                                width="35" height="auto" 
                                class="img-fluid me-2" 
                                id="logo" 
                                style="cursor: pointer;" 
                                alt="Company Logo">
                            <h5 class="modal-title fw-bold text-uppercase flex-grow-1">Gasoline Sales Report</h5>
                            <button type="button" 
                                    class="btn-close" 
                                    data-bs-dismiss="modal" 
                                    aria-label="Close"></button>
                        </div>
                        
                        <!-- Body -->
                        <div class="modal-body">
                            
                            <!-- Branch title + Prepared By + Date -->
                            <div class="mb-4 d-flex align-items-center gap-3">
                                <div class="flex-fill">
                                    <label class="form-label fw-semibold">Add report to branch</label>
                                    <input type="text" class="form-control" id="sessionBranchDisplay" value="<?php echo isset($_SESSION['branch']) ? htmlspecialchars($_SESSION['branch']) : ''; ?>" disabled>
                                </div>
                                <div class="flex-fill">
                                    <label class="form-label fw-semibold">Prepared By</label>
                                    <input type="text" class="form-control" id="preparedBy" name="preparedby" value="<?php echo htmlspecialchars($preparedByDisplay); ?>" readonly>
                                </div>
                                <div>
                                    <label for="date" class="form-label fw-semibold">Date</label>
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>
                            </div>

                            <!-- Fuel Sales -->
                            <hr class="my-4" style="border:0; height:3px; background:#672222; opacity:1;">
                            <h5 class="fw-bold text-uppercase mb-3">Fuel Sales</h5>

                            <div class="row g-3 mb-3" id="fuelTypeButtons">
                                <!-- Diesel -->
                                <div class="col-md-4 fuel-type-wrapper" data-fuel="diesel">
                                    <div class="fuel-button-container" id="dieselButtonContainer">
                                        <button type="button" class="btn fuel-type-btn w-100" data-fuel="diesel">
                                            <strong>DIESEL</strong>
                                        </button>
                                    </div>
                                    <div class="fuel-input-container" id="dieselInputContainer" style="display:none;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label for="dvs" class="form-label mb-0">Diesel Sold</label>
                                            <button type="button" class="btn btn-sm btn-danger close-fuel-input" data-fuel="diesel">×</button>
                                        </div>
                                        <input type="number" name="dvs" class="form-control" id="dvs" placeholder="Liters" step="0.01">
                                        <small id="availableDiesel" class="text-muted">Available: - L</small>
                                        <small id="dieselPriceDisplay" class="text-muted d-block mt-1" style="color: #672222; font-weight: bold;"></small>
                                    </div>
                                </div>

                                <!-- Premium -->
                                <div class="col-md-4 fuel-type-wrapper" data-fuel="premium">
                                    <div class="fuel-button-container" id="premiumButtonContainer">
                                        <button type="button" class="btn fuel-type-btn w-100" data-fuel="premium">
                                            <strong>PREMIUM</strong>
                                        </button>
                                    </div>
                                    <div class="fuel-input-container" id="premiumInputContainer" style="display:none;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label for="pvs" class="form-label mb-0">Premium Sold</label>
                                            <button type="button" class="btn btn-sm btn-danger close-fuel-input" data-fuel="premium">×</button>
                                        </div>
                                        <input type="number" name="pvs" class="form-control" id="pvs" placeholder="Liters" step="0.01">
                                        <small id="availablePremium" class="text-muted">Available: - L</small>
                                        <small id="premiumPriceDisplay" class="text-muted d-block mt-1" style="color: #672222; font-weight: bold;"></small>
                                    </div>
                                </div>

                                <!-- Unleaded -->
                                <div class="col-md-4 fuel-type-wrapper" data-fuel="unleaded">
                                    <div class="fuel-button-container" id="unleadedButtonContainer">
                                        <button type="button" class="btn fuel-type-btn w-100" data-fuel="unleaded">
                                            <strong>UNLEADED</strong>
                                        </button>
                                    </div>
                                    <div class="fuel-input-container" id="unleadedInputContainer" style="display:none;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label for="uvs" class="form-label mb-0">Unleaded Sold</label>
                                            <button type="button" class="btn btn-sm btn-danger close-fuel-input" data-fuel="unleaded">×</button>
                                        </div>
                                        <input type="number" name="uvs" class="form-control" id="uvs" placeholder="Liters" step="0.01">
                                        <small id="availableUnleaded" class="text-muted">Available: - L</small>
                                        <small id="unleadedPriceDisplay" class="text-muted d-block mt-1" style="color: #672222; font-weight: bold;"></small>
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden fields for prices (auto-filled from fuel_prices) -->
                            <input type="hidden" name="dvsp" id="dvsp" value="0">
                            <input type="hidden" name="pvsp" id="pvsp" value="0">
                            <input type="hidden" name="uvsp" id="uvsp" value="0">

                            <!-- Lubricant Sales -->
                          
                            <hr class="my-4" style="border:0; height:3px; background:#672222; opacity:1;">
                            <h5 class="fw-bold text-uppercase mb-3">Lubricant Sales</h5>

                            <div id="fuelContainer">
                                <div class="lubricant_row d-flex gap-2 mb-2 align-items-center">
                                    <div class="product-search-container flex-grow-2">
                                        <input type="text" name="pname[]" class="form-control product-search-input" placeholder="Type to search products..." autocomplete="off">
                                        <div class="product-dropdown"></div>
                                    </div>
                                    <input type="number" name="pqty[]" class="form-control flex-grow-1" placeholder="Enter Quantity">
                                    <input type="number" name="pprice[]" class="form-control flex-grow-1" placeholder="Enter Amount ₱" min="0" step="0.01">
                                    <button type="button" class="btn btn-danger btn-sm removerow">×</button>
                                </div>
                            </div>

                            <button type="button" id="addrow" 
                                    class="btn btn-outline-primary w-100 fw-semibold mb-3"
                                    style="background-color:#672222; border:none; color:#fff;">
                                ✙ Add Lubricant
                            </button>
                                                        

                            <!-- Expenses Section -->
                            <hr class="my-4" style="border:0; height:3px; background:#672222; opacity:1;">
                            <h5 class="fw-bold text-uppercase mb-3">Expenses</h5>

                            <div id="expensesContainer">
                                <div class="expense_row d-flex gap-2 align-items-center mb-2">
                                    <select class="form-select expense_type flex-grow-1" name="expense_type[]">
                                        <option value="">Select Expense</option>
                                        <option value="Electricity Expense">Electricity Expense</option>
                                        <option value="Water Expense">Water Expense</option>
                                        <option value="Salary Expense">Salary Expense</option>
                                        <option value="Others">Others</option>
                                    </select>
                                    <input type="number" name="expense_amount[]" class="form-control flex-grow-1 expense_amount" min="0" step="0.01" placeholder="Enter Amount ₱">
                                    <input type="text" name="other_expense[]" class="form-control other_expense" placeholder="Specify pls..." style="display:none; width:150px;">
                                    <button type="button" class="btn btn-danger btn-sm remove_expense">×</button>
                                </div>
                            </div>
                            <button type="button" id="add_expense" 
                                    class="btn btn-outline-primary w-100 fw-semibold mb-3"
                                    style="background-color:#672222; border:none; color:#fff;">
                                ✙ Add Expense
                            </button>

                            
                            
                            
                            
                            
                        </div>

                        <!-- Footer -->
                        <div class="modal-footer border-0">
                            <button type="button" id="close" 
                                    class="btn btn-secondary px-4" 
                                    data-bs-dismiss="modal" style="border: none;">Cancel</button>
                            
                            <input type="button" id="submit" name="submitreport" class="btn px-4" value="SAVE" 
                             style="background-color:#672222; border:none; color:#fff;">

                        </div>
                    </div>
                </div>
            </div>
        </form>


        <div class="modal fade" id="stockModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                <form id="stockForm">
                    <div class="modal-header">
                    <h5 class="modal-title" id="stockModalTitle">Manage Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                    <input type="hidden" name="action" id="stockAction">
                    <input type="hidden" name="branch" id="stockBranchHidden">
                    <div class="mb-3">
                        <label for="fuel_type" class="form-label">Fuel Type</label>
                        <select class="form-select" name="fuel_type" required>
                        <option value="Diesel">Diesel</option>
                        <option value="Premium">Premium</option>
                        <option value="Unleaded">Unleaded</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity (Liters)</label>
                        <input type="number" class="form-control" name="quantity" min="0.01" step="0.01" required>
                        <small id="availableStock" class="text-muted">Available: - L</small>
                    </div>
                    </div>
                    <div class="modal-footer">
                    
                    <button type="button" class="btn btn-secondary " style=" border:none; color:#fff;" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary " style="background-color:#672222; border:none; color:#fff;" id="stckbtn">Save</button>
                    </div>
                </form>
                </div>
            </div>
        </div>

        <!-- Floating add button for Lubricant Inventory -->
        <img src="add.png" id="addlubbtn" alt="add" style="position: fixed; right: 28px; bottom: 28px; width: 60px; height: 60px; border-radius: 50%; display: none; cursor: pointer; z-index: 1100; filter: drop-shadow(5px 5px 10px rgba(0,0,0,0.3)); transition: transform 0.2s ease;">

        <!-- Add Lubricant Modal -->
        <div class="modal fade" id="addinventorylub" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="lubStockForm">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Lubricant Product</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="SAVELUB">
                            <input type="hidden" name="inv_id" id="inv_id">

                            <!-- Branch title + Date -->
                            <div class="mb-4 d-flex align-items-center gap-3">
                                <div class="flex-fill">
                                    <label class="form-label fw-semibold">Add to branch</label>
                                    <input type="text" class="form-control" id="sessionBranchDisplay" value="<?php echo isset($_SESSION['branch']) ? htmlspecialchars($_SESSION['branch']) : ''; ?>" disabled>
                                </div>
                                <div>
                                    <label for="date" class="form-label fw-semibold">Date</label>
                                    <input type="date" class="form-control" id="lub_date" name="date" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="pname" class="form-label">Product Name</label>
                                <input type="text" class="form-control" name="pname" id="pname" list="productList" placeholder="Type or select a product" required>
                                <datalist id="productList">
                                    <!-- Products will be populated dynamically -->
                                </datalist>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" class="form-control" name="price" id="price" min="0" step="0.01" placeholder="Enter price" required>
                            </div>

                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" name="quantity" id="quantity" min="1" value="0" required>
                                <small id="availableStock" class="text-muted">Available: - L</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" style="border:none; color:#fff;" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="actionLub" style="background-color:#672222; border:none; color:#fff;">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

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

        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>

<script>
    $(document).ready(function(){
        var sessionBranch = "<?php echo isset($_SESSION['branch']) ? $_SESSION['branch'] : ''; ?>";
        
        let today = new Date().toISOString().split('T')[0];
        $("#date").val(today);

        fetchUser(); //load data
        
        // Variable to store products for search functionality
        let allProducts = [];
        


        function hideLoaderAfter3Sec() {
            setTimeout(function(){
                $(".loader-overlay").css("display", "none");
            }, 2000); // 2000ms = 2 seconds
        }
        

        

        // Filter button click (delegated)
        $(document).on('click', '#filterBtn', function(){
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();
            var from_date = $('#from_date').val();
            var to_date   = $('#to_date').val();
            console.log("Sending: ", from_date, to_date); // Debug

            $.ajax({
                url: 'gasfunction.php',
                method: 'POST',
                data: {
                    action: 'LOAD',
                    from_date: from_date,
                    to_date: to_date
                },
                success: function(data){
                    $('#data_table').html(data);
                }
            });
        });

         // Reset button click (delegated)
        $(document).on('click', '#resetBtn', function(){
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();
            $('#from_date').val('');
            $('#to_date').val('');
            $.ajax({
                url: 'gasfunction.php',
                method: 'POST',
                data: {action: 'LOAD'},
                success: function(data){
                    $('#data_table').html(data); // reloads full table
                }
            });
        });





        // Show Add Stock Modal
       
        let availableSpace = 0;

        const maxCapacity = { 'Diesel': 16000, 'Premium': 12000, 'Unleaded': 6000 };

        function handleStockModal(modalBtnId, actionType) {
            $(modalBtnId).click(function(){
                let modalTitle = (actionType === "ADD_STOCK") ? "Add Fuel Stock" : "Lessen Fuel Stock";
                let titleWithBranch = sessionBranch ? (modalTitle + " - " + sessionBranch) : modalTitle;
                $("#stockModalTitle").text(titleWithBranch);
                $("#stockAction").val(actionType);
                $("#stockForm")[0].reset();
                $("#availableStock").text("Available: - L");
                $('input[name="quantity"]').prop('disabled', true); // disable until selection
                $('#stockBranchHidden').val(sessionBranch || '');
                $("#stockModal").modal("show");

                // When fuel type changes (branch fixed from session)
                $('select[name="fuel_type"]').off('change').on('change', function(){
                    let branch = sessionBranch;
                    let fuelType = $('select[name="fuel_type"]').val();

                    if(branch && fuelType){
                        $.ajax({
                            url: 'adminfunction.php',
                            method: 'POST',
                            data: { action: 'GET_INVENTORY', branch: branch },
                            dataType: 'json',
                            success: function(data){
                                let currentStock = 0;
                                switch(fuelType){
                                    case 'Diesel': currentStock = data[0]; break;
                                    case 'Premium': currentStock = data[1]; break;
                                    case 'Unleaded': currentStock = data[2]; break;
                                }

                                let availableSpace = (actionType === "ADD_STOCK") ? maxCapacity[fuelType] - currentStock : currentStock;

                                $('#availableStock').text(`Available: ${availableSpace} L`);

                                if(availableSpace <= 0){
                                    $('input[name="quantity"]').prop('disabled', true).val('');
                                } else {
                                    $('input[name="quantity"]').prop('disabled', false).attr('max', availableSpace).val('');
                                }
                            }
                        });
                    } else {
                        $('input[name="quantity"]').prop('disabled', true).val('');
                        $('#availableStock').text('Available: - L');
                    }
                });

                // Default to Diesel and show its availability on open
                $('select[name="fuel_type"]').val('Diesel').trigger('change');
            });
        }
        // Limit quantity input dynamically
        $('input[name="quantity"]').on('input', function(){
            let qty = parseFloat($(this).val());
            let maxQty = parseFloat($(this).attr('max'));
            if(qty > maxQty){
                $(this).val(maxQty);
            }
        });

        handleStockModal("#btnAddStock", "ADD_STOCK");
        handleStockModal("#btnLessStock", "LESS_STOCK");

        // Handle Submit
        $("#stockForm").submit(function(e){
            e.preventDefault();
            $.ajax({
                url: "adminfunction.php",
                method: "POST",
                data: $(this).serialize(),
                success: function(response){
                    alert(response);
                    $("#stockModal").modal("hide");
                    $("#inventory").click(); // refresh chart
                }
            });
        });







         // Declare chart globally
        let gasInventoryChart;
        let currentBranch = null; // track currently selected branch

        // function to load inventory chart only
        function loadInventoryChart(branch) {
            currentBranch = branch;

            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: { action: 'GET_INVENTORY', branch: branch },
                dataType: 'json',
                success: function(data) {
                    const maxStock = [16000, 12000, 6000]; // Diesel, Premium, Unleaded

                    // Calculate remaining stock for projection effect
                    const remainingStock = maxStock.map((max, i) => max - data[i]);

                    if (!gasInventoryChart) {
                        const ctx = document.getElementById('gasInventoryChart').getContext('2d');
                        gasInventoryChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: ["Diesel", "Premium", "Unleaded"],
                                datasets: [
                                    {
                                        label: 'Current Stock',
                                        data: data,
                                        backgroundColor: ['#672222','#8c2f2f','#b84a4a'],
                                        borderRadius: 10,
                                        stack: 'Stack 0'
                                    },
                                    {
                                        label: 'Available Space',
                                        data: remainingStock,
                                        backgroundColor: 'rgba(0, 0, 0, 0.25)',
                                        borderRadius: 10,
                                        stack: 'Stack 0'
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        labels: {
                                            filter: item => item.text !== "Remaining Stock"
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: ctx => ctx.dataset.label + ": " + ctx.raw + " L"
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: Math.max(...maxStock) * 1.1,
                                        grid: { color: "#eee" },
                                            ticks: {
                                                callback: function(value) {
                                                    return value + " L"; // add "L" to each tick
                                                }
                                            }
                                        },
                                    x: { grid: { display: false } }
                                }
                            }
                        });
                    } else {
                        gasInventoryChart.data.datasets[0].data = data; // Current Stock
                        gasInventoryChart.data.datasets[1].data = remainingStock; // Remaining Stock
                        gasInventoryChart.update();
                    }
                }
            });
        }

        // Fuel Inventory click (session branch)
        $(document).on('click', '#inventory', function(e){
            e.preventDefault();
            var branch = sessionBranch;
            currentBranch = branch;
            $("#inventoryTitle").text(branch + " FUEL INVENTORY");
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();

            $(".inventory-card").fadeIn(500);
            $("#lubcon").hide();
            $("#data_table").hide();
            $("#navbot2").hide();
            $("#navbot").hide();
            $(".transaction").show();
            $(".addless").show();
            $("#addlubbtn").hide();

            loadInventoryChart(branch);
            if (typeof fetchTransactions === 'function') { fetchTransactions(branch); }
        });

        // Remove branch buttons usage; load for session branch when clicking Lubricant Inventory

        // fetch function to include branch
        function fetchLubInventory(branch) {
            $.ajax({
                url: "adminfunction.php",
                method: "POST",
                data: {
                    gastblsales: "LOADLUBINV",
                    branch: branch, // send branch to backend
                    search: $("#data_table2 .header-search input.form-control").val() || ""
                },
                success: function(data) {
                    
                    
                    $('#data_table2').html(data); // populate table
                    $("#data_table2").fadeIn(500);
                }
            });
        }
        // Floating add button interactions
        $('#addlubbtn').on('mouseenter', function(){ $(this).css('transform','scale(1.08)'); });
        $('#addlubbtn').on('mouseleave', function(){ $(this).css('transform','scale(1)'); });
        $('#addlubbtn').click(function(){
            $('#lubStockForm')[0].reset();
            const today = new Date().toISOString().split('T')[0];
            $('#lub_date').val(today);
            loadProducts(sessionBranch);
            $('#addinventorylub').modal('show');
        });

        function loadProducts(branch){
            $.ajax({
                url: 'adminfunction.php',
                type: 'POST',
                data: { gastblsales: 'GET_LUB_PRODUCTS', branch: branch },
                dataType: 'json',
                success: function(products){
                    let options = '';
                    products.forEach(function(p){ options += '<option value="' + p + '">'; });
                    $('#productList').html(options);
                }
            });
        }

        // Load products by branch for search functionality
        function loadProductsByBranch(branch) {
            $.ajax({
                url: "adminfunction.php",
                method: "POST",
                data: { 
                    gastblsales: "GET_LUB_PRODUCTS",
                    branch: branch 
                },
                dataType: "json",
                success: function(products) {
                    allProducts = products; // keep available_stock for inline display
                }
            });
        }

        $('#actionLub').click(function(){
            let branch = sessionBranch;
            let pname  = $('#lubStockForm input[name="pname"]').val();
            let qty    = $('#lubStockForm input[name="quantity"]').val();
            let date   = $('#lubStockForm input[name="date"]').val();
            let price  = $('#lubStockForm input[name="price"]').val();

            $.ajax({
                url: 'adminfunction.php',
                type: 'POST',
                data: {
                    gastblsales: 'SAVELUB',
                    branch: branch,
                    pname: pname,
                    quantity: qty,
                    date: date,
                    price: price
                },
                success: function(resp){
                    alert(resp);
                    $('#addinventorylub').modal('hide');
                    fetchLubInventory(branch);
                    fetchDailyTrans(branch);
                }
            });
        });
        // Delegate search click within lubricant inventory table
        $(document).on('click', '#data_table2 .btn-search', function(){
            const branch = sessionBranch || '';
            fetchLubInventory(branch);
        });
        // Pressing Enter in search field triggers search
        $(document).on('keypress', '#data_table2 .header-search input.form-control', function(e){
            if(e.which === 13){
                e.preventDefault();
                $('#data_table2 .btn-search').click();
            }
        });
        $('#lub').click(function() {
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();
            var branch = sessionBranch;
            fetchDailyTrans(branch);
            $(".inventory-card").hide();
            $("#data_table").hide();
            $("#navbot").hide();
            $(".transaction").hide();
            $(".addless").hide();
            fetchLubInventory(branch);
            $("#navbot2").show();
            $("#lubcon").show();
            $("#addlubbtn").fadeIn(200);
        });


        setInterval(function(){ 
            if(currentBranch){
                loadInventoryChart(currentBranch);  // updates chart
                fetchTransactions(currentBranch);   // updates table
            }
        }, 5000);


        
        function fetchUser() {
            
            var action = "LOAD";
            $.ajax({
            url: "gasfunction.php",
            method: "POST",
            data: {action: action},
            success: function(data) {
                $('#data_table').html(data);
            }
            });
        }

        // ---- Fuel price + UI helpers (aligned with admin) ----
        function resetPriceDisplays() {
            $('#dvsp').val(0); $('#pvsp').val(0); $('#uvsp').val(0);
            $('#dieselPriceDisplay').text('');
            $('#premiumPriceDisplay').text('');
            $('#unleadedPriceDisplay').text('');
        }

        function fetchBranchPrices(branch) {
            if(!branch){ resetPriceDisplays(); return; }
            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: { action: 'GET_BRANCH_PRICE', branch: branch },
                dataType: 'json',
                success: function(data) {
                    if (data && data.diesel_price) {
                        $('#dvsp').val(data.diesel_price);
                        $('#pvsp').val(data.premium_price);
                        $('#uvsp').val(data.unleaded_price);

                        $('#dieselPriceDisplay').text('Price: ₱' + parseFloat(data.diesel_price).toFixed(2) + ' per liter');
                        $('#premiumPriceDisplay').text('Price: ₱' + parseFloat(data.premium_price).toFixed(2) + ' per liter');
                        $('#unleadedPriceDisplay').text('Price: ₱' + parseFloat(data.unleaded_price).toFixed(2) + ' per liter');
                    } else {
                        resetPriceDisplays();
                        alert('Warning: No fuel prices found for ' + branch + '. Please set prices first.');
                    }
                },
                error: function() {
                    resetPriceDisplays();
                    console.error('Error fetching fuel prices');
                }
            });
        }

        function resetFuelButtons() {
            $('.fuel-button-container').css({'display':'flex','opacity':'1','transform':'scale(1)'});
            $('.fuel-input-container').css({'display':'none','opacity':'0','transform':'scale(0.95) translateY(-10px)'});
            $('#dvs,#pvs,#uvs').val('');
        }

        function updateFuelFieldStates() {
            if (!window.currentStock) return;
            const fuels = [
                { id: 'dvs', label: '#availableDiesel', stock: window.currentStock.diesel },
                { id: 'pvs', label: '#availablePremium', stock: window.currentStock.premium },
                { id: 'uvs', label: '#availableUnleaded', stock: window.currentStock.unleaded }
            ];
            fuels.forEach(f => {
                const input = $('#' + f.id);
                if (f.stock <= 0) {
                    input.prop('disabled', true).val('').attr('placeholder', 'No stock available');
                    $(f.label).html('<span style="color: red;">Available: 0 L (OUT OF STOCK)</span>');
                } else {
                    input.prop('disabled', false).attr('placeholder', 'Liters').attr('max', f.stock);
                    $(f.label).html('Available: ' + f.stock + ' L');
                }
            });
        }

        function validateFuelInput(input, fuelKey) {
            if (!window.currentStock) return;
            var inputValue = parseFloat(input.val()) || 0;
            var availableStock = window.currentStock[fuelKey];
            if (inputValue > availableStock) {
                alert('Warning: You cannot sell more than ' + availableStock + ' liters of ' + fuelKey);
                input.val(availableStock);
            }
        }

        // Fuel buttons show inputs
        $(document).on('click', '.fuel-type-btn', function() {
            const wrapper = $(this).closest('.fuel-type-wrapper');
            wrapper.find('.fuel-button-container').hide();
            wrapper.find('.fuel-input-container').show().css({'opacity':'1','transform':'scale(1) translateY(0)'});
        });
        $(document).on('click', '.close-fuel-input', function() {
            const fuelType = $(this).data('fuel');
            const wrapper = $(this).closest('.fuel-type-wrapper');
            wrapper.find('.fuel-input-container').hide();
            wrapper.find('.fuel-button-container').show().css({'opacity':'1','transform':'scale(1)'});
            const inputId = fuelType === 'diesel' ? 'dvs' : (fuelType === 'premium' ? 'pvs' : 'uvs');
            $('#' + inputId).val('');
        });

        // Real-time validation
        $(document).on('input', '#dvs', function(){ validateFuelInput($(this), 'diesel'); });
        $(document).on('input', '#pvs', function(){ validateFuelInput($(this), 'premium'); });
        $(document).on('input', '#uvs', function(){ validateFuelInput($(this), 'unleaded'); });

        // Sales Details (per-date) modal
        $(document).on('click', '.date-details-btn', function(){
            const date = $(this).data('date');
            const branchName = sessionBranch || '';
            const label = branchName ? (branchName + ' | ' + date) : date;
            $('#dateDetailsLabel').text('Sales Details - ' + label);
            $('#dateDetailsBody').html('<p class="text-center my-3 text-muted">Loading...</p>');
            $('#dateDetailsModal').modal('show');

            $.ajax({
                url: 'gasfunction.php',
                method: 'POST',
                data: {
                    action: 'DATE_DETAILS',
                    date: date,
                    branch: branchName
                },
                success: function(data){
                    $('#dateDetailsBody').html(data);
                },
                error: function(){
                    $('#dateDetailsBody').html('<p class="text-center text-danger mb-0">Unable to load details. Please try again.</p>');
                }
            });
        });
        function fetchlub() {
            
            var gastblsales = "LOADLUBINV";
            $.ajax({
            url: "adminfunction.php",
            method: "POST",
            data: {gastblsales: gastblsales},
            success: function(data) {
                $('#data_table2').html(data);
            }
            });
        }
        function fetchDailyTrans(branch) {
            $.ajax({
                url: "adminfunction.php",
                method: "POST",
                data: {
                    gastblsales: "LOADDAILYTRANS",
                    branch: branch 
                },
                success: function(data) {
                    $('#data_table3').html(data); 
                    $("#data_table3").fadeIn(500);
                }
            });
        }
























        $("#sales").click(function(){
            $(".inventory-card").hide();
            $("#navbot").show();
            $("#navbot2").hide();
            $(".transaction").hide();
            $(".addless").hide();
            $("#lubcon").hide();
            $("#data_table").fadeIn(500);
        });



        $("#addbtn").click(function(){
            $("#addsales").modal("show");
            $("#mySidenav").css("width", "0");
            $("#sidenav").show();
            resetFuelButtons();
            resetPriceDisplays();
            // Load available stock using session branch
            var branchVal = sessionBranch;
            if(branchVal){
                $.ajax({
                    url: 'adminfunction.php',
                    method: 'POST',
                    data: { action: 'GET_INVENTORY', branch: branchVal },
                    dataType: 'json',
                    success: function(data){
                        $('#availableDiesel').text('Available: ' + data[0] + ' L');
                        $('#availablePremium').text('Available: ' + data[1] + ' L');
                        $('#availableUnleaded').text('Available: ' + data[2] + ' L');
                        
                        // Store current stock for validation
                        window.currentStock = {
                            diesel: data[0],
                            premium: data[1],
                            unleaded: data[2]
                        };
                        updateFuelFieldStates();
                    }
                });
                fetchBranchPrices(branchVal);
                // Load products for search functionality
                loadProductsByBranch(branchVal);
            } else {
                $('#availableDiesel').text('Available: - L');
                $('#availablePremium').text('Available: - L');
                $('#availableUnleaded').text('Available: - L');
            }
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

        let rowCount = 0;



        // Populate default products for first row
        function populateProducts(selectElement){
            $.ajax({
                url: "adminfunction.php",
                method: "POST",
                data: { gastblsales: "GET_LUB_PRODUCTS" },
                dataType: "json",
                success: function(products){
                    var optionsHtml = '<option value="" disabled selected>Select product...</option>';
                    products.forEach(function(p){ optionsHtml += '<option value="'+p+'">'+p+'</option>'; });
                    selectElement.html(optionsHtml);
                }
            });
        }
        populateProducts($(".default_select"));

        // Product search functionality for Add Sales modal
        $(document).on('input', '#addsales .product-search-input', function() {
            const searchTerm = $(this).val().toLowerCase();
            const dropdown = $(this).siblings('.product-dropdown');
            const container = $(this).closest('.product-search-container');
            
            if (searchTerm.length < 1) {
                dropdown.hide();
                return;
            }

            // Filter products
            const filteredProducts = allProducts.filter(product => 
                product.product_name.toLowerCase().includes(searchTerm)
            );

            // Build dropdown HTML with stock inline
            let dropdownHTML = '';
            filteredProducts.forEach(product => {
                const stock = typeof product.available_stock !== 'undefined' ? product.available_stock : '';
                const stockLabel = stock !== '' ? ` - (Stock: ${stock})` : '';
                dropdownHTML += `<div class="product-dropdown-item" data-product="${product.product_name}" data-stock="${stock}">${product.product_name}${stockLabel}</div>`;
            });

            dropdown.html(dropdownHTML).show();
        });

        // Handle dropdown item selection for Add Sales modal
        $(document).on('click', '#addsales .product-dropdown-item', function() {
            const container = $(this).closest('.product-search-container');
            const input = container.find('.product-search-input');
            const dropdown = container.find('.product-dropdown');
            
            // Select existing product
            const productName = $(this).data('product');
            input.val(productName);
            dropdown.hide();
        });

        // Hide dropdown when clicking outside for Add Sales modal
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#addsales .product-search-container').length) {
                $('#addsales .product-dropdown').hide();
            }
        });

        // Add Lubricant Row
        $("#addrow").click(function(){
            let newRow = $(`
                <div class="lubricant_row d-flex gap-2 mb-2 align-items-center">
                    <div class="product-search-container flex-grow-2">
                        <input type="text" name="pname[]" class="form-control product-search-input" placeholder="Type to search products..." autocomplete="off">
                        <div class="product-dropdown"></div>
                    </div>
                    <input type="number" name="pqty[]" class="form-control flex-grow-1" placeholder="Enter Quantity">
                    <input type="number" name="pprice[]" class="form-control flex-grow-1" placeholder="Enter Amount ₱" min="0" step="0.01">
                    <button type="button" class="btn btn-danger btn-sm removerow">×</button>
                </div>
            `);
            $("#fuelContainer").append(newRow);
        });

        // Remove Lubricant Row
        $(document).on("click", ".removerow", function(){
            $(this).closest('.lubricant_row').remove();
        });

        // Branch selection removed; availability tied to sessionBranch only

        // Add Expense Row
        $(document).on('click', '#add_expense', function(){
            var row = '<div class="expense_row d-flex gap-2 align-items-center mb-2">\
                <select class="form-select expense_type flex-grow-1" name="expense_type[]">\
                    <option value="">Select Expense</option>\
                    <option value="Electricity Expense">Electricity Expense</option>\
                    <option value="Water Expense">Water Expense</option>\
                    <option value="Salary Expense">Salary Expense</option>\
                    <option value="Others">Others</option>\
                </select>\
                <input type="number" name="expense_amount[]" class="form-control flex-grow-1 expense_amount" min="0" step="0.01" placeholder="Enter Amount ₱">\
                <input type="text" name="other_expense[]" class="form-control other_expense" placeholder="Specify pls..." style="display:none; width:150px;">\
                <button type="button" class="btn btn-danger btn-sm remove_expense">×</button>\
            </div>';
            $('#expensesContainer').append(row);
        });

        // Remove Expense Row
        $(document).on('click', '.remove_expense', function(){
            $(this).closest('.expense_row').remove();
        });

        // Toggle Other Expense field
        $(document).on('change', '.expense_type', function(){
            var row = $(this).closest('.expense_row');
            var other = row.find('.other_expense');
            if($(this).val() === 'Others'){ other.show(); } else { other.hide().val(''); }
        });

        // Submit handler (save sales + transactions like admin page)
        $('#submit').click(function(){
            var branch = sessionBranch;
            var date = $('#date').val();
            var preparedby = $('#preparedBy').val();
            var dvs = parseFloat($('#dvs').val()) || 0;
            var dvsp = parseFloat($('#dvsp').val()) || 0;
            var pvs = parseFloat($('#pvs').val()) || 0;
            var pvsp = parseFloat($('#pvsp').val()) || 0;
            var uvs = parseFloat($('#uvs').val()) || 0;
            var uvsp = parseFloat($('#uvsp').val()) || 0;
            
            // Validate that prices are set
            if (dvsp <= 0 || pvsp <= 0 || uvsp <= 0) {
                alert("Error: Fuel prices are not set for this branch. Please set prices first.");
                return;
            }
            
            // Validate fuel volumes against available stock
            if (window.currentStock) {
                if (dvs > window.currentStock.diesel) {
                    alert('Error: Diesel volume (' + dvs + ' L) exceeds available stock (' + window.currentStock.diesel + ' L)');
                    return;
                }
                if (pvs > window.currentStock.premium) {
                    alert('Error: Premium volume (' + pvs + ' L) exceeds available stock (' + window.currentStock.premium + ' L)');
                    return;
                }
                if (uvs > window.currentStock.unleaded) {
                    alert('Error: Unleaded volume (' + uvs + ' L) exceeds available stock (' + window.currentStock.unleaded + ' L)');
                    return;
                }
            }

            // Lubricant rows - updated to use input fields instead of select
            let pname  = $('input[name="pname[]"]').map(function(){ return $(this).val(); }).get();
            let pprice = $('input[name="pprice[]"]').map(function(){ return parseFloat($(this).val()) || 0; }).get();
            let pqty   = $('input[name="pqty[]"]').map(function(){ return parseInt($(this).val()) || 0; }).get();

            // Expense rows
            let expense_type   = $('select[name="expense_type[]"]').map(function(){ return $(this).val(); }).get();
            let expense_amount = $('input[name="expense_amount[]"]').map(function(){ return parseFloat($(this).val()) || 0; }).get();
            let other_expense  = $('input[name="other_expense[]"]').map(function(){ return $(this).val(); }).get();

            if (!branch || !date || !preparedby) {
                alert("Please fill all required fields");
                return;
            }

            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: {
                    gastblsales: 'SAVE',
                    date: date,
                    branch: branch,
                    preparedby: preparedby,
                    dvs: dvs,
                    dvsp: dvsp,
                    pvs: pvs,
                    pvsp: pvsp,
                    uvs: uvs,
                    uvsp: uvsp,
                    pname: pname,
                    pprice: pprice,
                    pqty: pqty,
                    expense_type: expense_type,
                    expense_amount: expense_amount,
                    other_expense: other_expense
                },
                success: function(response) {
                    $("#addsales").modal("hide");
                    alert(response);

                    // Check if the response indicates an error (duplicate report)
                    if (response.includes("You already submitted a report for this branch today")) {
                        // Don't proceed with inventory updates or table refresh
                        return;
                    }

                    // Refresh table
                    fetchUser();
                    $(".loader-overlay").css("display", "flex");
                    hideLoaderAfter3Sec();
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
                url: "gasfunction.php",
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
