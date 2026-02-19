<?php
require_once __DIR__ . '/app_bootstrap.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!magx_is_admin_authenticated()) {
    header('Location: /index.php', true, 303);
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        
        <meta charset="utf-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Main Page</title>
        <link rel="icon" type="png" href="logomagx.png">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Poppins:wght@500;600;700;800&display=swap">

        <style>
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



            #btngas, #btncom, #btnbro {
                position: relative;
                border: 8px solid #672222;
                overflow: hidden;
                transition: transform 0.3s ease;
                }

                #btngas::before,
                #btncom::before,
                #btnbro::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-repeat: no-repeat;
                background-size: cover;
                filter: blur(5px);
                z-index: -1;
                }

                #btngas::before {
                background-image: url('gasback.png');
                }

                #btncom::before {
                background-image: url('leaseback.png');
                }
                #btnbro::before {
                background-image: url('broback.png');
                }

                #btngas:hover, #btncom:hover, #btnbro:hover,
                .logos:hover {
                transform: scale(1.1);
                filter: drop-shadow(5px 5px 5px rgba(0, 0, 0, 0.5));
                }
                .BUS{
                    color: white;
                    font-weight: bold;
                    text-shadow:
                        0 0 5px #672222,
                        0 0 10px #672222,
                        0 0 15px #a94444,
                        0 0 20px #d66a6a,
                        0 0 30px #f2bcbc;
                }
            


                




                /* Logs container */
                .logs-container {
                    margin: 20px 15px;
                    padding: 20px;
                    background: #fff;
                    border-radius: 15px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                    overflow-x: auto;
                }

                /* Logs table */
                .logs-table {
                    width: 100%;
                    border-collapse: collapse;
                    border-radius: 12px;
                    overflow: hidden;
                    font-size: 14px;
                }

                /* Title row */
                .logs-table .logs-title {
                    background: linear-gradient(90deg, #672222, #8c2f2f);
                    color: #fff;
                    font-size: 16px;
                    font-weight: bold;
                    padding: 12px;
                    
                    letter-spacing: 1px;
                }
                .logs-table th{
                    text-align: center;
                }

                /* Column headers */
                .logs-table thead tr:nth-child(2) th {
                    background: #672222;
                    color: #fff;
                    padding: 10px;
                    text-transform: uppercase;
                    text-align: center;
                }

                /* Table rows */
                .logs-table tbody tr {
                    background: #f9f9f9;
                    transition: background 0.3s;
                }

                .logs-table tbody tr:nth-child(even) {
                    background: #f1f1f1;
                }

                .logs-table tbody tr:hover {
                    background: #ffe5e5;
                }

                /* Table cells */
                .logs-table td {
                    padding: 10px;
                    text-align: center;
                    color: #333;
                    font-weight: 500;
                }

                /* Footer */
                .logs-footer {
                    margin-top: 10px;
                    font-size: 12px;
                    color: #555;
                    text-align: right;
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

                /* Profile Modal Styles */
                .profile-modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.7);
                    z-index: 9999;
                    justify-content: center;
                    align-items: center;
                }

                .profile-modal {
                    background: white;
                    border-radius: 10px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
                    max-width: 600px;
                    width: 90%;
                    max-height: 80vh;
                    
                    position: relative;
                }

                .profile-header {
                    background: linear-gradient(90deg, #672222, #8c2f2f);
                    color: white;
                    padding: 15px 20px;
                    border-radius: 10px 10px 0 0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .profile-header h3 {
                    margin: 0;
                    font-size: 1.5em;
                }

                .close-modal {
                    background:none;
                    border: none;
                    color: white;
                    font-size: 24px;
                    cursor: pointer;
                    padding: 0;
                    width: 30px;
                    height: 30px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .close-modal:hover {
                    background: rgba(255, 255, 255, 0.2);
                    border-radius: 50%;
                }

                .profile-body {
                    padding: 20px;
                    max-height: 60vh; 
                    overflow-y: auto;

                }

                .profile-section {
                    margin-bottom: 25px;
                    overflow-y: auto;
                }

                .profile-section h4 {
                    color: #672222;
                    border-bottom: 2px solid #672222;
                    padding-bottom: 5px;
                    margin-bottom: 15px;
                    font-size: 1.2em;
                }

                .profile-row {
                    display: flex;
                    margin-bottom: 10px;
                    align-items: center;
                }

                .profile-row label {
                    font-weight: bold;
                    color: #333;
                    min-width: 120px;
                    margin-right: 10px;
                }

                .profile-row span {
                    color: #666;
                    flex: 1;
                }

                .status-pending {
                    color: #ff9800;
                    font-weight: bold;
                }

                .status-approved {
                    color: #4caf50;
                    font-weight: bold;
                }

                .status-rejected {
                    color: #f44336;
                    font-weight: bold;
                }

                

                .btncloseprof {
                    background: #6c757d;
                    color: white;
                    border: none;
                    padding: 8px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                }

                .btncloseprof:hover {
                    background: #5a6268;
                }
                .btn-info:hover {
                    background-color: #92a1acff !important;
                    color: white;
                }

                /* Record cards */
                .records-grid{
                    display:grid;
                    grid-template-columns:repeat(auto-fit, minmax(290px, 1fr));
                    gap:18px;
                }
                .records-grid.list-mode{
                    grid-template-columns:1fr;
                    gap:14px;
                }
                .records-grid.list-mode .record-card{
                    display:grid;
                    grid-template-columns: 280px 1fr;
                    align-items:stretch;
                }
                .records-grid.list-mode .record-media{
                    height:100% !important;
                    min-height:180px;
                }
                .record-toolbar{
                    display:flex;
                    justify-content:space-between;
                    gap:12px;
                    margin-bottom:14px;
                    flex-wrap:wrap;
                }
                .record-search-input{
                    background: linear-gradient(150deg, rgba(255,255,255,0.12), rgba(255,255,255,0.06)) !important;
                    border: 1px solid rgba(95,210,255,0.28) !important;
                    color: #eaf6ff !important;
                    border-radius: 999px !important;
                    min-width: 260px;
                    max-width: 430px;
                }
                .record-search-input::placeholder{
                    color: rgba(208,233,252,0.65) !important;
                }
                .record-view-toggle{
                    border-radius:999px !important;
                    font-weight:700 !important;
                }
                .record-card{
                    background: linear-gradient(160deg, rgba(7, 24, 52, 0.95), rgba(5, 16, 36, 0.96));
                    border: 1px solid rgba(95,210,255,0.22);
                    border-radius: 16px;
                    box-shadow: 0 14px 30px rgba(4, 19, 44, 0.16);
                    overflow: hidden;
                    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
                    opacity: 0;
                    transform: translateY(10px);
                    animation: recordIn .45s ease forwards;
                }
                .record-card:hover{
                    transform: translateY(-4px);
                    box-shadow: 0 22px 42px rgba(4, 19, 44, 0.22);
                    border-color: rgba(29,124,255,0.38);
                }
                .records-grid .record-card:nth-child(2){ animation-delay: .04s; }
                .records-grid .record-card:nth-child(3){ animation-delay: .08s; }
                .records-grid .record-card:nth-child(4){ animation-delay: .12s; }
                .records-grid .record-card:nth-child(5){ animation-delay: .16s; }
                .records-grid .record-card:nth-child(6){ animation-delay: .20s; }
                .records-grid .record-card:nth-child(7){ animation-delay: .24s; }
                .records-grid .record-card:nth-child(8){ animation-delay: .28s; }
                @keyframes recordIn{
                    to{
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                .record-media{
                    position: relative;
                    height: 172px;
                    background: #122745;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                }
                .record-media img, .record-media video{
                    width:100%;
                    height:100%;
                    object-fit:cover;
                }
                .record-chip{
                    position:absolute;
                    top:10px;
                    left:10px;
                    background: rgba(11,28,52,.78);
                    color:#eef5ff;
                    border:1px solid rgba(255,255,255,.22);
                    border-radius:999px;
                    padding:5px 10px;
                    font-size:11px;
                    font-weight:800;
                    letter-spacing:.05em;
                    text-transform:uppercase;
                }
                .record-body{
                    padding:14px 14px 12px;
                    background: linear-gradient(170deg, rgba(8, 24, 49, 0.94), rgba(6, 16, 34, 0.96));
                }
                .record-card .record-title{
                    color:#eef6ff !important;
                    font-weight:800;
                    margin:0 0 2px;
                    font-size:17px;
                }
                .record-card .record-subtitle{
                    color:rgba(215,234,255,0.82) !important;
                    font-size:13px;
                    margin:0 0 12px;
                }
                .record-card .record-meta{
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    margin-bottom:12px;
                    font-size:13px;
                    color:#c6def8 !important;
                }
                .record-actions{
                    border-top:1px solid rgba(95,210,255,0.22);
                    padding-top:12px;
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    gap:10px;
                }
                .record-actions .btn-sm{
                    border-radius:999px;
                    font-weight:700;
                    transition: transform .2s ease, box-shadow .2s ease;
                }
                .record-actions .btn-sm:hover{
                    transform: translateY(-1px);
                    box-shadow: 0 8px 14px rgba(3, 16, 36, 0.2);
                }
                .contact-avatar-wrap{
                    width:104px;
                    height:104px;
                    border-radius:50%;
                    overflow:hidden;
                    border:4px solid rgba(255,255,255,0.55);
                    box-shadow: 0 12px 26px rgba(7,16,32,.32);
                }
                .contact-avatar-wrap img{
                    width:100%;
                    height:100%;
                    object-fit:cover;
                }
                .empty-records{
                    text-align:center;
                    padding:42px 20px;
                    background: linear-gradient(160deg, rgba(8, 24, 49, 0.88), rgba(6, 16, 34, 0.9));
                    border: 1px dashed rgba(95,210,255,0.32);
                    border-radius: 14px;
                    color:#d6ebff;
                    font-weight:700;
                }

                /* Toggle Switch Styles */
                .toggle-switch {
                    position: relative;
                    display: inline-block;
                    width: 50px;
                    height: 20px;
                }

                .toggle-switch input {
                    opacity: 0;
                    width: 0;
                    height: 0;
                }

                .toggle-slider {
                    position: absolute;
                    cursor: pointer;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: #dc3545;
                    transition: .4s;
                    border-radius: 30px;
                }

                .toggle-slider:before {
                    position: absolute;
                    content: "";
                    height: 15px;
                    width: 15px;
                    left: 1.5px;
                    bottom: 2.5px;
                    background-color: white;
                    transition: .4s;
                    border-radius: 50%;
                }

                .toggle-switch input:checked + .toggle-slider {
                    background-color: #28a745;
                }

                .toggle-switch input:checked + .toggle-slider:before {
                    transform: translateX(30px);
                }
           
                :root{
                    --magx-ink:#0b1220;
                    --magx-ink-soft:#22324f;
                    --magx-blue:#1d7cff;
                    --magx-cyan:#00b2ff;
                    --magx-surface:rgba(255,255,255,0.92);
                }
                body{
                    font-family: 'Inter','Poppins',system-ui,sans-serif;
                    background:
                        radial-gradient(circle at 15% 20%, rgba(29,124,255,0.18), transparent 45%),
                        radial-gradient(circle at 85% 10%, rgba(0,178,255,0.18), transparent 40%),
                        linear-gradient(180deg, #0a1222 0%, #0f1b33 100%);
                    color:#eaf0ff;
                    min-height:100vh;
                }
                header{
                    background: linear-gradient(120deg, rgba(13,23,44,0.95), rgba(19,45,86,0.92));
                    border-bottom:1px solid rgba(255,255,255,0.08);
                    box-shadow: 0 20px 50px rgba(3,8,18,0.45);
                    text-shadow: none;
                    font-family: 'Poppins','Inter',system-ui,sans-serif;
                }
                header h2, header h3, header h4{
                    margin:0;
                    color:#f5f8ff;
                    letter-spacing:0.02em;
                }
                .sidenav{
                    background: rgba(7,16,32,0.92);
                    box-shadow: -20px 0 60px rgba(3,8,18,0.5);
                    border-left: 1px solid rgba(255,255,255,0.08);
                }
                .sidenav a{
                    color:#eaf0ff;
                    font-weight:700;
                    letter-spacing:0.04em;
                    text-transform: uppercase;
                    font-size:14px;
                }
                .sidenav a:hover{
                    background: rgba(255,255,255,0.12);
                    color:#fff;
                }
                #data_table, #home_posts, #contacts_section{
                    background: transparent;
                }
                .logs-container{
                    background: var(--magx-surface);
                    box-shadow: 0 24px 60px rgba(3,8,18,0.35);
                }
                .logs-table thead tr:nth-child(2) th{
                    background: linear-gradient(90deg, #102448, #1b3a6b);
                }
                .logs-table .logs-title{
                    background: linear-gradient(90deg, #1d7cff, #00b2ff);
                }
                .logs-table tbody tr:hover{
                    background: rgba(29,124,255,0.08);
                }
                .btn{
                    border-radius: 999px;
                    font-weight: 800;
                }
                .loader-overlay{
                    background: rgba(8,12,24,0.82);
                }
                @media (max-width: 992px){
                    #home_posts > div,
                    #contacts_section > div{
                        margin: 16px 16px !important;
                    }
                    .records-grid{
                        grid-template-columns: 1fr;
                        gap: 14px;
                    }
                    .records-grid.list-mode .record-card{
                        grid-template-columns: 1fr;
                    }
                    .record-media{
                        height: 156px;
                    }
                    .record-search-input{
                        min-width: 100%;
                        max-width: 100%;
                    }
                .record-actions{
                        flex-wrap: wrap;
                    }
                    .record-actions > div{
                        width:100%;
                        display:flex;
                        gap:8px;
                    }
                    .record-actions > div .btn{
                        flex:1;
                    }
                }
        </style>
        <link rel="stylesheet" href="assets/css/admin-unified-theme.css?v=20260217">
    </head>
    <body>
         <header>
            <img src="logomagx.png" width="3%" height="auto" class="img-fluid" alt="logo" id="logo"/>
            <div class="header-title">
                <h1 class="h2 h3-md m-0" style="margin:0;">MAGX Admin Console</h1>
                <div style="font-size:12px; letter-spacing:0.18em; text-transform:uppercase; color:#cfe3ff;">Personal Website Control</div>
            </div>
            <img src="ico.png" id="sidenav" height="auto" style="position: absolute; right: 2%;"  class="img-fluid" alt="logo"/>
        </header>
        
        <!-- Loader Overlay -->
        <div class="loader-overlay">
            <div class="circle-loader"></div>
        </div>

        <div id="mySidenav" class="sidenav">
            <button type="button" id="closeBtn" style="background-color:white; color: white; margin-left: 25px;" class="btn-close" aria-label="Close"></button>
        </br>
            <a href="#" id="homeposts">Home Highlights</a>
            <a href="#" id="contacts">Contacts</a>
            <div class="dropdown" style="display:inline-block; width:100%;">
                <a href="#" class="dropdown-toggle" id="adminchangepass" data-bs-toggle="dropdown" aria-expanded="false">
                    My Account
                </a>
                <ul class="dropdown-menu" aria-labelledby="inventoryDropdown" style="  width:100%;">
                    <li><a style="color:#672222;"class="dropdown-item change_userpass" href="#">Change Pass & User</a></li>
                    <li><a style="color:#672222; " class="dropdown-item adac" href="#">Add Account</a></li>
                    
                </ul>
            </div>
        
            <div id="admininput" style="display: none;">
                <input type="text" placeholder="Password" class="user"/>
                <input type="password" placeholder="Enter Password Again" class="pass"/>
                <div class="action-buttons">
                    <button type="button" id="adlogin">Change</button>
                    <button type="submit" id="adcancel">Cancel</button>
                </div>
            </div>
            <a href="index.php">Exit</a>
            
        </div>





        <!-- Home Posts Section -->
        <div id="home_posts" style="display: none;">
            <div style="margin: 20px 50px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 style="color: #672222;">Manage Home Page Posts</h3>
                    <button type="button" class="btn" style="background: #672222; color: white;" id="addPostBtn">
                        <i class="fas fa-plus"></i> Add New Post
                    </button>
                </div>
                <div class="record-toolbar">
                    <input type="text" class="form-control record-search-input" id="homePostsSearchInput" placeholder="Search posts by title, subtitle, or description...">
                    <button type="button" class="btn btn-outline-light record-view-toggle" id="homePostsViewToggle">
                        <i class="fas fa-list"></i> List View
                    </button>
                </div>
                <div id="home_posts_table" style="overflow-y: auto;">
                    <!-- Posts will display here -->
                </div>
            </div>
        </div>

        <!-- Contacts Section -->
        <div id="contacts_section" style="display: none;">
            <div style="margin: 20px 50px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 style="color: #672222;">Manage Contacts</h3>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn" style="background: #672222; color: white;" id="addContactBtn">
                            <i class="fas fa-plus"></i> Add New Person
                        </button>
                        <button type="button" class="btn" style="background: #672222; color: white;" id="manageFooterBtn">
                            <i class="fas fa-cog"></i> Manage Footer Contact
                        </button>
                    </div>
                </div>
                <div class="record-toolbar">
                    <input type="text" class="form-control record-search-input" id="contactsSearchInput" placeholder="Search contacts by name or position...">
                    <button type="button" class="btn btn-outline-light record-view-toggle" id="contactsViewToggle">
                        <i class="fas fa-list"></i> List View
                    </button>
                </div>
                <div id="contacts_table" style="overflow-x: auto; overflow-y: visible; min-height: 200px;">
                    <!-- Contacts will display here -->
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

        <!-- Add/Edit Contact Modal -->
        <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(90deg, #672222, #8c2f2f); color: white;">
                        <h5 class="modal-title" id="contactModalLabel">Add Contact</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="contactForm" enctype="multipart/form-data">
                            <input type="hidden" id="contactId" name="contactId">
                            
                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="contactName" class="form-label">Name</label>
                                <input type="text" placeholder="Enter Name" class="form-control" id="contactName" required>
                            </div>

                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="contactPosition" class="form-label">Position</label>
                                <input type="text" placeholder="Enter Position" class="form-control" id="contactPosition">
                            </div>

                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="contactPicture" class="form-label">Picture</label>
                                <input type="file" accept="image/*" class="form-control" id="contactPicture" name="picture">
                                <small class="form-text text-muted">Upload profile picture (PNG, JPG, etc.)</small>
                                <div id="contactPicturePreview" class="mt-2" style="display:none;">
                                    <img id="contactPreviewImg" src="" alt="Picture Preview" style="max-width: 150px; max-height: 150px; border-radius: 50%; border: 3px solid #672222;">
                                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="clearContactPicture()">Remove</button>
                                </div>
                                <input type="hidden" id="existingContactPicture" value="">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label style="color: #672222; font-weight:bold;" for="contactDisplayOrder" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="contactDisplayOrder" value="0" min="0">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label style="color: #672222; font-weight:bold;" for="contactIsActive" class="form-label">Status</label>
                                    <select class="form-select" id="contactIsActive">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn" style="background: #672222; color: white;" id="saveContactBtn">Save Contact</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Home Post Modal -->
        <div class="modal fade" id="homePostModal" tabindex="-1" aria-labelledby="homePostModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(90deg, #672222, #8c2f2f); color: white;">
                        <h5 class="modal-title" id="homePostModalLabel">Add Home Post</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="homePostForm" enctype="multipart/form-data">
                            <input type="hidden" id="postId" name="postId">
                            
                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="postTitle" class="form-label">Title</label>
                                <input type="text" placeholder="Enter Title" class="form-control" id="postTitle" required>
                            </div>

                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="postSubtitle" class="form-label">Subtitle</label>
                                <input type="text" placeholder="Enter Subtitle" class="form-control" id="postSubtitle">
                            </div>

                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="postDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="postDescription" rows="5" placeholder="Enter Description" required></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label style="color: #672222; font-weight:bold;" for="postIconImage" class="form-label">Icon Image</label>
                                    <input type="file" accept="image/*" class="form-control" id="postIconImage" name="icon_image">
                                    <small class="form-text text-muted">Upload icon image (PNG, JPG, etc.)</small>
                                    <div id="iconImagePreview" class="mt-2" style="display:none;">
                                        <img id="iconPreviewImg" src="" alt="Icon Preview" style="max-width: 100px; max-height: 100px; border-radius: 5px;">
                                        <button type="button" class="btn btn-sm btn-danger ms-2" onclick="clearIconImage()">Remove</button>
                                    </div>
                                    <input type="hidden" id="existingIconImage" value="">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label style="color: #672222; font-weight:bold;" for="postBgImage" class="form-label">Background Image</label>
                                    <input type="file" accept="image/*" class="form-control" id="postBgImage" name="background_image">
                                    <small class="form-text text-muted">Upload background image (PNG, JPG, etc.)</small>
                                    <div id="bgImagePreview" class="mt-2" style="display:none;">
                                        <img id="bgPreviewImg" src="" alt="Background Preview" style="max-width: 150px; max-height: 100px; border-radius: 5px;">
                                        <button type="button" class="btn btn-sm btn-danger ms-2" onclick="clearBgImage()">Remove</button>
                                    </div>
                                    <input type="hidden" id="existingBgImage" value="">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="postBgVideo" class="form-label">Background Video</label>
                                <input type="file" accept="video/mp4,video/webm,video/ogg" class="form-control" id="postBgVideo" name="background_video">
                                <small class="form-text text-muted">Upload MP4/WebM/OGG video (max 50MB). If set, video will show instead of background image on the homepage.</small>
                                <div id="bgVideoPreview" class="mt-2" style="display:none;">
                                    <video id="bgVideoPreviewEl" src="" controls muted playsinline preload="metadata" style="max-width: 260px; max-height: 150px; border-radius: 8px; background: #000;"></video>
                                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="clearBgVideo()">Remove</button>
                                </div>
                                <input type="hidden" id="existingBgVideo" value="">
                            </div>

                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" class="form-label">Engagement</label>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label style="color: #672222; font-weight:bold;" for="postLikeCount" class="form-label">
                                            <i class="fas fa-heart"></i> Hearts
                                        </label>
                                        <input type="number" class="form-control" id="postLikeCount" value="0" min="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label style="color: #672222; font-weight:bold;" for="postCommentCount" class="form-label">
                                            <i class="fas fa-comment"></i> Comments
                                        </label>
                                        <input type="number" class="form-control" id="postCommentCount" value="0" min="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label style="color: #672222; font-weight:bold;" for="postShareCount" class="form-label">
                                            <i class="fas fa-share"></i> Shares
                                        </label>
                                        <input type="number" class="form-control" id="postShareCount" value="0" min="0">
                                    </div>
                                </div>
                                <small class="form-text text-muted">Counts shown on the home posts cards.</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label style="color: #672222; font-weight:bold;" for="postDisplayOrder" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="postDisplayOrder" value="0" min="0">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label style="color: #672222; font-weight:bold;" for="postIsActive" class="form-label">Status</label>
                                    <select class="form-select" id="postIsActive">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn" style="background: #672222; color: white;" id="savePostBtn">Save Post</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Account Modal -->
        <div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(90deg, #672222, #8c2f2f); color: white;">
                        <h5 class="modal-title" id="addAccountModalLabel">Add Backup Admin Account</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addAccountForm">
                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="adminUsername" class="form-label">Admin Username</label>
                                <input type="text" placeholder="Enter Username" class="form-control" id="adminUsername" required>
                            </div>

                             <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;"class="form-label">Admin Password</label>
                                <div class="d-flex gap-2">
                                    <input type="password" placeholder="Enter Password" class="form-control" id="adminPassword" 
                                        required
                                        minlength="8"
                                        maxlength="20"
                                        pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&_])[A-Za-z\d@$!%*?&_]{8,20}$"
                                        title="Password must be 8–20 characters, include at least one uppercase letter, one lowercase letter, one number, and one special character.">
                                    <input type="password" placeholder="Confirm Password" class="form-control" id="confirmAdminPassword" required>
                                </div>
                                <div id="password-strength2" style="margin-top:5px; font-weight:bold;"></div>
                                
                                <small style="font-size: 10px;" class="form-text text-muted">
                                    Your password must be 8–20 characters, include at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).
                                </small>
                            </div>



                            
                           
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn" style="background: #672222; color: white;" id="addAccountBtn">Add Account</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Contact Modal -->
        <div class="modal fade" id="footerContactModal" tabindex="-1" aria-labelledby="footerContactModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(90deg, #672222, #8c2f2f); color: white;">
                        <h5 class="modal-title" id="footerContactModalLabel">Manage Footer Contact</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="footerContactForm" enctype="multipart/form-data">
                            <input type="hidden" id="footerContactId" name="footerContactId">
                            
                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="footerLogo" class="form-label">Logo</label>
                                <input type="file" accept="image/*" class="form-control" id="footerLogo" name="logo">
                                <small class="form-text text-muted">Upload logo image (PNG, JPG, etc.)</small>
                                <div id="footerLogoPreview" class="mt-2" style="display:none;">
                                    <img id="footerLogoPreviewImg" src="" alt="Logo Preview" style="max-width: 100px; max-height: 100px; border-radius: 5px; border: 3px solid #672222;">
                                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="clearFooterLogo()">Remove</button>
                                </div>
                                <input type="hidden" id="existingFooterLogo" value="">
                            </div>

                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="footerFacebook" class="form-label">Facebook Link</label>
                                <input type="url" placeholder="https://www.facebook.com/..." class="form-control" id="footerFacebook" required>
                            </div>

                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="footerPhone" class="form-label">Phone Number</label>
                                <input type="text" placeholder="+63 917 123 4567" class="form-control" id="footerPhone" required>
                            </div>

                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="footerLocation" class="form-label">Location</label>
                                <input type="text" placeholder="Marana 1st, City of Ilagan, Isabela" class="form-control" id="footerLocation" required>
                            </div>

                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="footerQrCode" class="form-label">QR Code Image</label>
                                <input type="file" accept="image/*" class="form-control" id="footerQrCode" name="qr_code">
                                <small class="form-text text-muted">Upload QR code image (PNG, JPG, etc.)</small>
                                <div id="footerQrPreview" class="mt-2" style="display:none;">
                                    <img id="footerQrPreviewImg" src="" alt="QR Code Preview" style="max-width: 100px; max-height: 100px; border-radius: 5px; border: 3px solid #672222;">
                                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="clearFooterQr()">Remove</button>
                                </div>
                                <input type="hidden" id="existingFooterQr" value="">
                            </div>

                            <div class="mb-3">
                                <label style="color: #672222; font-weight:bold;" for="footerIsActive" class="form-label">Status</label>
                                <select class="form-select" id="footerIsActive">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn" style="background: #672222; color: white;" id="saveFooterContactBtn">Save Footer Contact</button>
                    </div>
                </div>
            </div>
        </div>






        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>

<script>
    const MAGX_SUPABASE_URL = <?php echo json_encode((string)(getenv('SUPABASE_URL') ?: '')); ?>;
    const MAGX_SUPABASE_ANON_KEY = <?php echo json_encode((string)(getenv('SUPABASE_ANON_KEY') ?: '')); ?>;
    const MAGX_HOME_POSTS_BUCKET = <?php echo json_encode((string)(getenv('SUPABASE_STORAGE_BUCKET_HOME_POSTS') ?: 'home-posts-media')); ?>;
    const MAGX_SUPABASE_READY = !!(MAGX_SUPABASE_URL && MAGX_SUPABASE_ANON_KEY && MAGX_HOME_POSTS_BUCKET);
    let homePostsRecords = [];
    let contactsRecords = [];
    let homePostsSearchTerm = "";
    let contactsSearchTerm = "";
    let homePostsViewMode = "card";
    let contactsViewMode = "card";

    function normalizeText(value) {
        return String(value || "").toLowerCase();
    }

    $(document).ready(function(){
        $("#home_posts").show();
        $("#contacts_section").hide();
        loadHomePosts();
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

        // Handle home posts click
        $("#homeposts").click(function(){
            $("#contacts_section").hide();
            $("#services_section").hide();
            $("#portfolio_section").hide();
            $("#home_posts").show();
            loadHomePosts();
        });

        // Handle contacts click
        $("#contacts").click(function(){
            $("#home_posts").hide();
            $("#services_section").hide();
            $("#portfolio_section").hide();
            $("#contacts_section").show();
            loadContacts();
        });

        $("#homePostsSearchInput").on("input", function(){
            homePostsSearchTerm = String($(this).val() || "");
            renderHomePosts();
        });

        $("#contactsSearchInput").on("input", function(){
            contactsSearchTerm = String($(this).val() || "");
            renderContacts();
        });

        $("#homePostsViewToggle").on("click", function(){
            homePostsViewMode = (homePostsViewMode === "card") ? "list" : "card";
            $(this).html(homePostsViewMode === "card"
                ? '<i class="fas fa-list"></i> List View'
                : '<i class="fas fa-th-large"></i> Card View');
            renderHomePosts();
        });

        $("#contactsViewToggle").on("click", function(){
            contactsViewMode = (contactsViewMode === "card") ? "list" : "card";
            $(this).html(contactsViewMode === "card"
                ? '<i class="fas fa-list"></i> List View'
                : '<i class="fas fa-th-large"></i> Card View');
            renderContacts();
        });

        // end side nav

        // Handle Change Pass & User click
        $(".change_userpass").click(function(e){
            e.preventDefault();
            $("#changePassModal").modal('show');
        });

        // Handle Add Account click
        $(".adac").click(function(e){
            e.preventDefault();
            $("#addAccountModal").modal('show');
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
                url: "adminfunction.php",
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

        // Handle Add Account button
        $("#addAccountBtn").click(function(){
            var adminUsername = $("#adminUsername").val();
            var adminPassword = $("#adminPassword").val();
            var confirmAdminPassword = $("#confirmAdminPassword").val();
            

            if(adminPassword !== confirmAdminPassword) {
                alert("Admin passwords do not match!");
                return;
            }

            $.ajax({
                url: "adminfunction.php",
                method: "POST",
                data: {
                    action: "ADD_ADMIN_ACCOUNT",
                    admin_username: adminUsername,
                    admin_password: adminPassword
                    
                },
                success: function(data) {
                    alert(data);
                    $("#addAccountModal").modal('hide');
                    $("#addAccountForm")[0].reset();
                },
                error: function() {
                    alert("Error adding admin account!");
                }
            });
        });

        function hideLoaderAfter3Sec() {
            setTimeout(function(){
                $(".loader-overlay").css("display", "none");
            }, 2000); // 3000ms = 3 seconds
        }

        // Refresh Button Handler
        $(document).on('click', '#refreshBtn', function() {
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();
            loadHomePosts();
        });



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
        $('#adminPassword').on('input', function() {
            let val = $(this).val();
            let strength = '';
            let color = '';

            if(val.length === 0){
                
                $('#password-strength2').text('');
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

        
            $('#password-strength2').text(strength).css('color', color);
        });
    });
    
   
    // Load home posts
    function loadHomePosts() {
        $.ajax({
            url: "home_posts_api.php",
            method: "POST",
            data: {action: "LOAD"},
            dataType: "json",
            success: function(response) {
                if(response.success) {
                    displayHomePosts(response.data);
                } else {
                    alert("Error loading posts: " + response.message);
                }
            },
            error: function() {
                alert("Error loading posts!");
            }
        });
    }

    function getFilteredHomePosts() {
        if (!homePostsSearchTerm) {
            return homePostsRecords;
        }

        var query = normalizeText(homePostsSearchTerm);
        return homePostsRecords.filter(function(post){
            return normalizeText(post.title).indexOf(query) > -1 ||
                normalizeText(post.subtitle).indexOf(query) > -1 ||
                normalizeText(post.description).indexOf(query) > -1;
        });
    }

    // Display home posts as cards/list
    function displayHomePosts(posts) {
        homePostsRecords = Array.isArray(posts) ? posts : [];
        renderHomePosts();
    }

    function renderHomePosts() {
        function resolveMediaUrl(v, fallbackPrefix){
            v = String(v || "").trim();
            if(!v){ return ""; }
            if(/^https?:\/\//i.test(v) || /^data:/i.test(v) || v.indexOf("uploads/") === 0){ return v; }
            return fallbackPrefix + v;
        }
        var posts = getFilteredHomePosts();
        var gridClass = 'records-grid' + (homePostsViewMode === 'list' ? ' list-mode' : '');
        var html = '<div class="' + gridClass + '">';
        
        if(posts.length === 0) {
            html += '<div class="empty-records">' + (homePostsSearchTerm ? 'No home posts found for your search.' : 'No home posts found.') + '</div>';
        } else {
            posts.forEach(function(post) {
                var hasVideo = !!post.background_video;
                var backgroundUrl = post.background_image ? resolveMediaUrl(post.background_image, 'uploads/home_posts/') : 'https://via.placeholder.com/1200x675/18365f/eaf2ff?text=Post+Media';
                var videoUrl = post.background_video ? resolveMediaUrl(post.background_video, 'uploads/home_posts/') : '';
                var isActive = post.is_active == 1;
                html += '<div class="record-card">';
                html += '<div class="record-media">';
                html += '<span class="record-chip">Order #' + (post.display_order || 0) + '</span>';
                if(hasVideo) {
                    html += '<a href="' + videoUrl + '" target="_blank" rel="noopener" style="text-decoration:none; display:flex; align-items:center; justify-content:center; width:100%; height:100%; color:#dbeaff; background:radial-gradient(circle at 50% 35%, rgba(68,130,230,0.35), rgba(8,18,38,0.9));">';
                    html += '<div style="text-align:center;"><i class="fas fa-video" style="font-size:30px;"></i><div style="font-size:12px; font-weight:800; letter-spacing:.08em; margin-top:8px;">VIDEO POST</div></div>';
                    html += '</a>';
                } else {
                    html += '<img src="' + backgroundUrl + '" alt="' + (post.title || '') + '" onerror="this.src=\'https://via.placeholder.com/1200x675/18365f/eaf2ff?text=Post+Media\'; this.onerror=null;">';
                }
                html += '</div>';
                html += '<div class="record-body">';
                html += '<h5 class="record-title">' + (post.title || 'Untitled Post') + '</h5>';
                html += '<p class="record-subtitle">' + (post.subtitle || 'No subtitle provided') + '</p>';
                html += '<div class="record-meta">';
                html += '<span><i class="fas fa-heart" style="color:#d33f49;"></i> ' + (post.like_count || 0) + '</span>';
                html += '<span><i class="fas fa-comment" style="color:#1d7cff;"></i> ' + (post.comment_count || 0) + '</span>';
                html += '<span><i class="fas fa-share" style="color:#00a778;"></i> ' + (post.share_count || 0) + '</span>';
                html += '</div>';
                html += '<div class="record-actions">';
                html += '<label class="toggle-switch">';
                html += '<input type="checkbox" ' + (isActive ? 'checked' : '') + ' onchange="toggleHomePostStatus(' + post.id + ', this.checked)">';
                html += '<span class="toggle-slider"></span>';
                html += '</label>';
                html += '<div>';
                html += '<button style="background-color:#0f3a76 ; color:white;" class="btn btn-sm me-2" onclick="editHomePost(' + post.id + ')"><i class="fas fa-edit"></i> Edit</button>';
                html += '<button class="btn btn-sm btn-danger" onclick="deleteHomePost(' + post.id + ')"><i class="fas fa-trash"></i> Delete</button>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
            });
        }
        
        html += '</div>';
        
        $('#home_posts_table').html(html);
    }

    // Add new post
    $("#addPostBtn").click(function(){
        $("#homePostModalLabel").text("Add Home Post");
        $("#homePostForm")[0].reset();
        $("#postId").val("");
        $("#existingIconImage").val("");
        $("#existingBgImage").val("");
        $("#existingBgVideo").val("");
        $("#postLikeCount").val(0);
        $("#postCommentCount").val(0);
        $("#postShareCount").val(0);
        $("#iconImagePreview").hide();
        $("#bgImagePreview").hide();
        $("#bgVideoPreview").hide();
        $("#bgVideoPreviewEl").attr("src", "");
        $("#homePostModal").modal('show');
    });

    // Preview icon image
    $("#postIconImage").on('change', function(e){
        var file = e.target.files[0];
        if(file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $("#iconPreviewImg").attr('src', e.target.result);
                $("#iconImagePreview").show();
            }
            reader.readAsDataURL(file);
        }
    });

    // Preview background image
    $("#postBgImage").on('change', function(e){
        var file = e.target.files[0];
        if(file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $("#bgPreviewImg").attr('src', e.target.result);
                $("#bgImagePreview").show();
            }
            reader.readAsDataURL(file);
        }
    });

    // Preview background video
    $("#postBgVideo").on('change', function(e){
        var file = e.target.files[0];
        if(file) {
            var url = URL.createObjectURL(file);
            $("#bgVideoPreviewEl").attr('src', url);
            $("#bgVideoPreview").show();
        }
    });

    // Clear icon image
    window.clearIconImage = function() {
        $("#postIconImage").val("");
        $("#iconImagePreview").hide();
        $("#existingIconImage").val("");
    }

    // Clear background image
    window.clearBgImage = function() {
        $("#postBgImage").val("");
        $("#bgImagePreview").hide();
        $("#existingBgImage").val("");
    }

    // Clear background video
    window.clearBgVideo = function() {
        $("#postBgVideo").val("");
        $("#bgVideoPreview").hide();
        $("#bgVideoPreviewEl").attr("src", "");
        $("#existingBgVideo").val("");
    }

    var MAGX_VERCEL_MAX_UPLOAD_BYTES = 4 * 1024 * 1024; // 4MB safe ceiling for Vercel serverless

    function formatMb(bytes){
        return (bytes / (1024 * 1024)).toFixed(2) + " MB";
    }

    function randomHex(len){
        var out = "";
        var chars = "abcdef0123456789";
        for(var i = 0; i < len; i++){ out += chars.charAt(Math.floor(Math.random() * chars.length)); }
        return out;
    }

    function buildSupabaseObjectPath(file){
        var safeName = String((file && file.name) || "video.mp4").toLowerCase().replace(/[^a-z0-9._-]/g, "_");
        var ext = "mp4";
        if (safeName.lastIndexOf(".") > -1) {
            ext = safeName.split(".").pop() || "mp4";
        }
        return "home_posts/" + Date.now() + "_" + randomHex(12) + "." + ext;
    }

    function encodeStoragePath(path){
        return String(path || "").split("/").map(encodeURIComponent).join("/");
    }

    async function uploadVideoToSupabaseStorage(file){
        if(!MAGX_SUPABASE_READY){
            throw new Error("Supabase Storage is not configured (missing SUPABASE_URL/SUPABASE_ANON_KEY).");
        }
        var objectPath = buildSupabaseObjectPath(file);
        var objectPathEncoded = encodeStoragePath(objectPath);
        var bucketEncoded = encodeURIComponent(MAGX_HOME_POSTS_BUCKET);
        var uploadUrl = MAGX_SUPABASE_URL.replace(/\/+$/, "") + "/storage/v1/object/" + bucketEncoded + "/" + objectPathEncoded;

        var response = await fetch(uploadUrl, {
            method: "POST",
            headers: {
                "apikey": MAGX_SUPABASE_ANON_KEY,
                "Authorization": "Bearer " + MAGX_SUPABASE_ANON_KEY,
                "x-upsert": "true",
                "Content-Type": file.type || "application/octet-stream"
            },
            body: file
        });

        if(!response.ok){
            var text = "";
            try { text = await response.text(); } catch(e) {}
            throw new Error("Supabase upload failed (" + response.status + "). " + (text || "Check Storage bucket policies."));
        }

        return MAGX_SUPABASE_URL.replace(/\/+$/, "") + "/storage/v1/object/public/" + bucketEncoded + "/" + objectPathEncoded;
    }

    // Save post (add or edit)
    $("#savePostBtn").click(async function(){
        var $saveBtn = $("#savePostBtn");
        if ($saveBtn.data("busy")) { return; }
        $saveBtn.data("busy", 1).prop("disabled", true);

        var postId = $("#postId").val();
        var action = postId ? "EDIT" : "ADD";

        if(!$("#postTitle").val() || !$("#postDescription").val()) {
            alert("Please fill in required fields!");
            $saveBtn.data("busy", 0).prop("disabled", false);
            return;
        }

        var formData = new FormData();
        formData.append('action', action);
        formData.append('title', $("#postTitle").val());
        formData.append('subtitle', $("#postSubtitle").val());
        formData.append('description', $("#postDescription").val());
        formData.append('like_count', $("#postLikeCount").val());
        formData.append('comment_count', $("#postCommentCount").val());
        formData.append('share_count', $("#postShareCount").val());
        formData.append('display_order', $("#postDisplayOrder").val());
        formData.append('is_active', $("#postIsActive").val());

        var iconFile = $("#postIconImage")[0].files[0];
        if (iconFile && iconFile.size > MAGX_VERCEL_MAX_UPLOAD_BYTES) {
            alert("Icon image is too large (" + formatMb(iconFile.size) + "). Max allowed on Vercel is 4 MB.");
            $saveBtn.data("busy", 0).prop("disabled", false);
            return;
        }
        if(iconFile) {
            formData.append('icon_image', iconFile);
        } else if($("#existingIconImage").val()) {
            formData.append('existing_icon_image', $("#existingIconImage").val());
        }

        var bgFile = $("#postBgImage")[0].files[0];
        if (bgFile && bgFile.size > MAGX_VERCEL_MAX_UPLOAD_BYTES) {
            alert("Background image is too large (" + formatMb(bgFile.size) + "). Max allowed on Vercel is 4 MB.");
            $saveBtn.data("busy", 0).prop("disabled", false);
            return;
        }
        if(bgFile) {
            formData.append('background_image', bgFile);
        } else if($("#existingBgImage").val()) {
            formData.append('existing_background_image', $("#existingBgImage").val());
        }

        var vidFile = $("#postBgVideo")[0].files[0];
        if(vidFile) {
            if (vidFile.size > MAGX_VERCEL_MAX_UPLOAD_BYTES) {
                if(!MAGX_SUPABASE_READY){
                    alert("Background video is " + formatMb(vidFile.size) + ". Configure Supabase Storage env vars to upload videos larger than 4 MB.");
                    $saveBtn.data("busy", 0).prop("disabled", false);
                    return;
                }
                $saveBtn.text("Uploading video...");
                try {
                    var uploadedUrl = await uploadVideoToSupabaseStorage(vidFile);
                    formData.append('background_video_url', uploadedUrl);
                } catch (uploadErr) {
                    alert((uploadErr && uploadErr.message) ? uploadErr.message : "Video upload failed.");
                    $saveBtn.data("busy", 0).prop("disabled", false).text("Save Post");
                    return;
                }
            } else {
                formData.append('background_video', vidFile);
            }
        } else if($("#existingBgVideo").val()) {
            formData.append('existing_background_video', $("#existingBgVideo").val());
        }

        if(postId) {
            formData.append('id', postId);
        }

        $saveBtn.text("Saving...");
        $.ajax({
            url: "home_posts_api.php",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                if(response.success) {
                    alert(response.message);
                    $("#homePostModal").modal('hide');
                    $("#homePostForm")[0].reset();
                    $("#iconImagePreview").hide();
                    $("#bgImagePreview").hide();
                    $("#bgVideoPreview").hide();
                    $("#bgVideoPreviewEl").attr("src", "");
                    $("#existingBgVideo").val("");
                    loadHomePosts();
                } else {
                    alert("Error: " + response.message);
                }
                $saveBtn.data("busy", 0).prop("disabled", false).text("Save Post");
            },
            error: function(xhr) {
                var msg = "Error saving post!";
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    msg = "Error: " + xhr.responseJSON.message;
                } else if (xhr && xhr.responseText) {
                    try {
                        var parsed = JSON.parse(xhr.responseText);
                        if (parsed && parsed.message) {
                            msg = "Error: " + parsed.message;
                        }
                    } catch (e) {
                        if (xhr.status === 413) {
                            msg = "Error: Upload too large for serverless request. Try a smaller file or Supabase Storage.";
                        }
                    }
                } else if (xhr && xhr.status === 413) {
                    msg = "Error: Upload too large for serverless request. Try a smaller file or Supabase Storage.";
                }
                alert(msg);
                $saveBtn.data("busy", 0).prop("disabled", false).text("Save Post");
            }
        });
    });

    // Edit post
    function editHomePost(id) {
        $.ajax({
            url: "home_posts_api.php",
            method: "POST",
            data: {action: "GET_POST", id: id},
            dataType: "json",
            success: function(response) {
                if(response.success) {
                    var post = response.data;
                    $("#postId").val(post.id);
                    $("#postTitle").val(post.title);
                    $("#postSubtitle").val(post.subtitle);
                    $("#postDescription").val(post.description);
                    $("#postLikeCount").val(post.like_count || 0);
                    $("#postCommentCount").val(post.comment_count || 0);
                    $("#postShareCount").val(post.share_count || 0);
                    $("#postDisplayOrder").val(post.display_order);
                    $("#postIsActive").val(post.is_active);
                    
                    // Handle existing media (store original value; use full URL for preview)
                    if(post.icon_image) {
                        $("#existingIconImage").val(String(post.icon_image));
                        $("#iconPreviewImg").attr('src', post.icon_image);
                        $("#iconImagePreview").show();
                    } else {
                        $("#existingIconImage").val("");
                        $("#iconImagePreview").hide();
                    }
                    
                    if(post.background_image) {
                        $("#existingBgImage").val(String(post.background_image));
                        $("#bgPreviewImg").attr('src', post.background_image);
                        $("#bgImagePreview").show();
                    } else {
                        $("#existingBgImage").val("");
                        $("#bgImagePreview").hide();
                    }

                    if(post.background_video) {
                        $("#existingBgVideo").val(String(post.background_video));
                        $("#bgVideoPreviewEl").attr('src', post.background_video);
                        $("#bgVideoPreview").show();
                    } else {
                        $("#existingBgVideo").val("");
                        $("#bgVideoPreviewEl").attr('src', "");
                        $("#bgVideoPreview").hide();
                    }
                    
                    // Clear file inputs
                    $("#postIconImage").val("");
                    $("#postBgImage").val("");
                    $("#postBgVideo").val("");
                    
                    $("#homePostModalLabel").text("Edit Home Post");
                    $("#homePostModal").modal('show');
                } else {
                    alert("Error loading post: " + response.message);
                }
            },
            error: function() {
                alert("Error loading post!");
            }
        });
    }

    // Delete post
    function deleteHomePost(id) {
        if(confirm("Are you sure you want to delete this post?")) {
            $.ajax({
                url: "home_posts_api.php",
                method: "POST",
                data: {action: "DELETE", id: id},
                dataType: "json",
                success: function(response) {
                    if(response.success) {
                        alert(response.message);
                        loadHomePosts();
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function() {
                    alert("Error deleting post!");
                }
            });
        }
    }

    // Toggle home post status
    function toggleHomePostStatus(id, isActive) {
        var status = isActive ? 1 : 0;
        $.ajax({
            url: "home_posts_api.php",
            method: "POST",
            data: {
                action: "TOGGLE_STATUS",
                id: id,
                is_active: status
            },
            dataType: "json",
            success: function(response) {
                if(!response.success) {
                    alert("Error: " + response.message);
                    // Revert toggle on error
                    loadHomePosts();
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = "Error updating status!";
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = "Error: " + xhr.responseJSON.message;
                } else if(xhr.responseText) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if(response.message) {
                            errorMsg = "Error: " + response.message;
                        }
                    } catch(e) {
                        errorMsg = "Error: " + xhr.responseText;
                    }
                }
                alert(errorMsg);
                // Revert toggle on error
                loadHomePosts();
            }
        });
    }

    // Load contacts
    function loadContacts() {
        $.ajax({
            url: "contact_api.php",
            method: "POST",
            data: {action: "LOAD"},
            dataType: "json",
            success: function(response) {
                if(response.success) {
                    displayContacts(response.data);
                } else {
                    alert("Error loading contacts: " + response.message);
                }
            },
            error: function() {
                alert("Error loading contacts!");
            }
        });
    }

    function getFilteredContacts() {
        if (!contactsSearchTerm) {
            return contactsRecords;
        }

        var query = normalizeText(contactsSearchTerm);
        return contactsRecords.filter(function(contact){
            return normalizeText(contact.name).indexOf(query) > -1 ||
                normalizeText(contact.position).indexOf(query) > -1;
        });
    }

    // Display contacts as cards/list
    function displayContacts(contacts) {
        contactsRecords = Array.isArray(contacts) ? contacts : [];
        renderContacts();
    }

    function renderContacts() {
        var contacts = getFilteredContacts();
        var gridClass = 'records-grid' + (contactsViewMode === 'list' ? ' list-mode' : '');
        var html = '<div class="' + gridClass + '">';
        
        if(contacts.length === 0) {
            html += '<div class="empty-records">' + (contactsSearchTerm ? 'No contacts found for your search.' : 'No contacts found.') + '</div>';
        } else {
            contacts.forEach(function(contact) {
                var initials = (contact.name ? contact.name.substring(0, 2).toUpperCase() : 'NA');
                var pictureUrl = contact.picture ? (contact.picture.startsWith('uploads/') ? contact.picture : 'uploads/contacts/' + contact.picture) : 'https://via.placeholder.com/220x220/18365f/eaf2ff?text=' + initials;
                var isActive = contact.is_active == 1;
                html += '<div class="record-card">';
                html += '<div class="record-media" style="height:150px; background:linear-gradient(135deg, #13355f, #0c1f3f);">';
                html += '<span class="record-chip">Order #' + (contact.display_order || 0) + '</span>';
                html += '<div class="contact-avatar-wrap"><img src="' + pictureUrl + '" alt="' + (contact.name || '') + '" onerror="this.src=\'https://via.placeholder.com/220x220/18365f/eaf2ff?text=' + initials + '\'; this.onerror=null;"></div>';
                html += '</div>';
                html += '<div class="record-body">';
                html += '<h5 class="record-title">' + (contact.name || 'No Name') + '</h5>';
                html += '<p class="record-subtitle">' + (contact.position || 'No position provided') + '</p>';
                html += '<div class="record-actions">';
                html += '<label class="toggle-switch">';
                html += '<input type="checkbox" ' + (isActive ? 'checked' : '') + ' onchange="toggleContactStatus(' + contact.id + ', this.checked)">';
                html += '<span class="toggle-slider"></span>';
                html += '</label>';
                html += '<div>';
                html += '<button class="btn btn-sm btn-info me-2" onclick="editContact(' + contact.id + ')"><i class="fas fa-edit"></i> Edit</button>';
                html += '<button class="btn btn-sm btn-danger" onclick="deleteContact(' + contact.id + ')"><i class="fas fa-trash"></i> Delete</button>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
            });
        }
        
        html += '</div>';
        
        $('#contacts_table').html(html);
    }

    // Add new contact
    $("#addContactBtn").click(function(){
        $("#contactModalLabel").text("Add Contact");
        $("#contactForm")[0].reset();
        $("#contactId").val("");
        $("#existingContactPicture").val("");
        $("#contactPicturePreview").hide();
        $("#contactModal").modal('show');
    });

    // Preview contact picture
    $("#contactPicture").on('change', function(e){
        var file = e.target.files[0];
        if(file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $("#contactPreviewImg").attr('src', e.target.result);
                $("#contactPicturePreview").show();
            }
            reader.readAsDataURL(file);
        }
    });

    // Clear contact picture
    function clearContactPicture() {
        $("#contactPicture").val("");
        $("#contactPicturePreview").hide();
        $("#existingContactPicture").val("");
    }

    // Save contact (add or edit)
    $("#saveContactBtn").click(function(){
        var contactId = $("#contactId").val();
        var action = contactId ? "EDIT" : "ADD";
        
        if(!$("#contactName").val()) {
            alert("Please fill in required fields!");
            return;
        }

        // Create FormData for file uploads
        var formData = new FormData();
        formData.append('action', action);
        formData.append('name', $("#contactName").val());
        formData.append('position', $("#contactPosition").val());
        formData.append('display_order', $("#contactDisplayOrder").val());
        formData.append('is_active', $("#contactIsActive").val());
        
        // Handle picture
        var pictureFile = $("#contactPicture")[0].files[0];
        if(pictureFile) {
            formData.append('picture', pictureFile);
        } else if($("#existingContactPicture").val()) {
            formData.append('existing_picture', $("#existingContactPicture").val());
        }

        if(contactId) {
            formData.append('id', contactId);
        }

        $.ajax({
            url: "contact_api.php",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                if(response.success) {
                    alert(response.message);
                    $("#contactModal").modal('hide');
                    $("#contactForm")[0].reset();
                    $("#contactPicturePreview").hide();
                    loadContacts();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function() {
                alert("Error saving contact!");
            }
        });
    });

    // Edit contact
    function editContact(id) {
        $.ajax({
            url: "contact_api.php",
            method: "POST",
            data: {action: "GET_CONTACT", id: id},
            dataType: "json",
            success: function(response) {
                if(response.success) {
                    var contact = response.data;
                    $("#contactId").val(contact.id);
                    $("#contactName").val(contact.name);
                    $("#contactPosition").val(contact.position);
                    $("#contactDisplayOrder").val(contact.display_order);
                    $("#contactIsActive").val(contact.is_active);
                    
                    // Handle existing picture
                    if(contact.picture) {
                        $("#existingContactPicture").val(contact.picture);
                        $("#contactPreviewImg").attr('src', contact.picture);
                        $("#contactPicturePreview").show();
                    } else {
                        $("#existingContactPicture").val("");
                        $("#contactPicturePreview").hide();
                    }
                    
                    // Clear file input
                    $("#contactPicture").val("");
                    
                    $("#contactModalLabel").text("Edit Contact");
                    $("#contactModal").modal('show');
                } else {
                    alert("Error loading contact: " + response.message);
                }
            },
            error: function() {
                alert("Error loading contact!");
            }
        });
    }

    // Delete contact
    function deleteContact(id) {
        if(confirm("Are you sure you want to delete this contact?")) {
            $.ajax({
                url: "contact_api.php",
                method: "POST",
                data: {action: "DELETE", id: id},
                dataType: "json",
                success: function(response) {
                    if(response.success) {
                        alert(response.message);
                        loadContacts();
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function() {
                    alert("Error deleting contact!");
                }
            });
        }
    }

    // Toggle contact status
    function toggleContactStatus(id, isActive) {
        var status = isActive ? 1 : 0;
        $.ajax({
            url: "contact_api.php",
            method: "POST",
            data: {
                action: "TOGGLE_STATUS",
                id: id,
                is_active: status
            },
            dataType: "json",
            success: function(response) {
                if(!response.success) {
                    alert("Error: " + response.message);
                    // Revert toggle on error
                    loadContacts();
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = "Error updating status!";
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = "Error: " + xhr.responseJSON.message;
                } else if(xhr.responseText) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if(response.message) {
                            errorMsg = "Error: " + response.message;
                        }
                    } catch(e) {
                        errorMsg = "Error: " + xhr.responseText;
                    }
                }
                alert(errorMsg);
                // Revert toggle on error
                loadContacts();
            }
        });
    }

    // Load footer contact
    function loadFooterContact() {
        $.ajax({
            url: "footer_contact_api.php",
            method: "POST",
            data: {action: "LOAD"},
            dataType: "json",
            success: function(response) {
                if(response.success) {
                    var footer = response.data;
                    $("#footerContactId").val(footer.id);
                    $("#footerFacebook").val(footer.facebook_link || '');
                    $("#footerPhone").val(footer.phone || '');
                    $("#footerLocation").val(footer.location || '');
                    $("#footerIsActive").val(footer.is_active);
                    
                    // Handle logo
                    if(footer.logo) {
                        $("#existingFooterLogo").val(footer.logo);
                        $("#footerLogoPreviewImg").attr('src', footer.logo);
                        $("#footerLogoPreview").show();
                    } else {
                        $("#existingFooterLogo").val("");
                        $("#footerLogoPreview").hide();
                    }
                    
                    // Handle QR code
                    if(footer.qr_code) {
                        $("#existingFooterQr").val(footer.qr_code);
                        $("#footerQrPreviewImg").attr('src', footer.qr_code);
                        $("#footerQrPreview").show();
                    } else {
                        $("#existingFooterQr").val("");
                        $("#footerQrPreview").hide();
                    }
                } else {
                    // No footer contact found, clear form
                    $("#footerContactForm")[0].reset();
                    $("#footerContactId").val("");
                    $("#existingFooterLogo").val("");
                    $("#existingFooterQr").val("");
                    $("#footerLogoPreview").hide();
                    $("#footerQrPreview").hide();
                }
            },
            error: function() {
                alert("Error loading footer contact!");
            }
        });
    }

    // Handle Manage Footer Contact button click
    $("#manageFooterBtn").click(function(){
        $("#footerContactModalLabel").text("Manage Footer Contact");
        loadFooterContact();
        $("#footerContactModal").modal('show');
    });

    // Preview footer logo
    $("#footerLogo").on('change', function(e){
        var file = e.target.files[0];
        if(file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $("#footerLogoPreviewImg").attr('src', e.target.result);
                $("#footerLogoPreview").show();
            }
            reader.readAsDataURL(file);
        }
    });

    // Preview footer QR code
    $("#footerQrCode").on('change', function(e){
        var file = e.target.files[0];
        if(file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $("#footerQrPreviewImg").attr('src', e.target.result);
                $("#footerQrPreview").show();
            }
            reader.readAsDataURL(file);
        }
    });

    // Clear footer logo
    function clearFooterLogo() {
        $("#footerLogo").val("");
        $("#footerLogoPreview").hide();
        $("#existingFooterLogo").val("");
    }

    // Clear footer QR code
    function clearFooterQr() {
        $("#footerQrCode").val("");
        $("#footerQrPreview").hide();
        $("#existingFooterQr").val("");
    }

    // Save footer contact (add or edit)
    $("#saveFooterContactBtn").click(function(){
        var footerContactId = $("#footerContactId").val();
        var action = footerContactId ? "EDIT" : "ADD";
        
        if(!$("#footerFacebook").val() || !$("#footerPhone").val() || !$("#footerLocation").val()) {
            alert("Please fill in required fields!");
            return;
        }

        // Create FormData for file uploads
        var formData = new FormData();
        formData.append('action', action);
        formData.append('facebook_link', $("#footerFacebook").val());
        formData.append('phone', $("#footerPhone").val());
        formData.append('location', $("#footerLocation").val());
        formData.append('is_active', $("#footerIsActive").val());
        
        // Handle logo
        var logoFile = $("#footerLogo")[0].files[0];
        if(logoFile) {
            formData.append('logo', logoFile);
        } else if($("#existingFooterLogo").val()) {
            formData.append('existing_logo', $("#existingFooterLogo").val());
        }
        
        // Handle QR code
        var qrFile = $("#footerQrCode")[0].files[0];
        if(qrFile) {
            formData.append('qr_code', qrFile);
        } else if($("#existingFooterQr").val()) {
            formData.append('existing_qr_code', $("#existingFooterQr").val());
        }

        if(footerContactId) {
            formData.append('id', footerContactId);
        }

        $.ajax({
            url: "footer_contact_api.php",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                if(response.success) {
                    alert(response.message);
                    $("#footerContactModal").modal('hide');
                    $("#footerContactForm")[0].reset();
                    $("#footerLogoPreview").hide();
                    $("#footerQrPreview").hide();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function() {
                alert("Error saving footer contact!");
            }
        });
    });
</script>
