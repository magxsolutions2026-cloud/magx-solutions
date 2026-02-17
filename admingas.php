
<!DOCTYPE html>
<html>
    <head>
        
        <meta charset="utf-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Gas Page</title>
        <link rel="icon" type="png" href="tmclogo.png">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">



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
                flex-wrap: wrap; 
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

            /* Input */
            .header-search {
                width: 250px; /* adjust as needed */
                font-family: 'Segoe UI', sans-serif;
                position: relative;
            }

            /* Style the select box */
            .header-search select.form-control {
                width: 100%;
                padding: 10px 40px 10px 15px; /* space for custom arrow */
                border: none;
                border-radius: 12px;
                background: linear-gradient(90deg, #672222, #8c2f2f);
                color: #ffffff;
                font-size: 14px;
                outline: none;
                cursor: pointer;
                appearance: none; /* remove default arrow */
                -webkit-appearance: none;
                -moz-appearance: none;
                transition: background 0.3s, box-shadow 0.3s;
            }

            /* Hover and focus */
            .header-search select.form-control:hover,
            .header-search select.form-control:focus {
                background: linear-gradient(90deg, #8c2f2f, #672222);
                box-shadow: 0 0 0 2px rgba(255,255,255,0.2);
            }

            /* Custom arrow */
            .header-search::after {
                content: '▼';
                position: absolute;
                right: 15px;
                top: 50%;
                transform: translateY(-50%);
                pointer-events: none;
                color: #ffffffcc;
                font-size: 12px;
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

             /* Search Container */
            .header-search-filter {
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
            .header-search-filter input {
                flex: 1;
                border: none;
                outline: none;
                background: transparent;
                padding: 10px 15px;
                height: 50%;
                color: white;
                font-size: 14px;
            }

            /* dtatable1 */
            @media (max-width: 768px) {
                .header-search {
                    order: 99; /* push it below everything */
                    width: 100%; /* take full width */
                    margin-top: 10px;
                }
                
                /* Mobile: data table fills width by default */
                #data_table {
                    margin-top: 50px !important; 
                    margin-left: 5px !important;
                    margin-right: 5px !important;
                    width: calc(100% - 10px) !important;
                }
                /* Make report tables scrollable on small screens */
                #data_table table,
                #data_table2 table,
                #data_table3 table {
                    display: block;
                    overflow-x: auto;
                    width: 100%;
                    -webkit-overflow-scrolling: touch;
                    white-space: nowrap;
                }
                /* Lubricant containers responsive margins */
                #lubcon {
                    margin-left: 5px !important;
                    margin-right: 5px !important;
                }
                #data_table2,
                #data_table3 {
                    margin-left: 5px !important;
                    margin-right: 5px !important;
                    width: calc(100% - 10px) !important;
                }
                
                /* When tool is clicked, adjust for crud nav */
                .crud-nav.expanded ~ #data_table {
                    margin-left: 100px !important;
                    width: calc(100% - 110px) !important;
                }
                /* Push lubricant view when second tool is open */
                .crud-nav-2.expanded ~ #lubcon {
                    margin-left: 100px !important;
                    width: calc(100% - 110px) !important;
                }
            }


            /* === Side Navigation Styles === */
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

            .sidenav a,
            #admininput {
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

            #sidenav:hover {
                transform: scale(1.2);
            }

            #sidenav {
                cursor: pointer;
                transition: transform 0.3s ease;
            }
            .dbranch:hover{
                background-color: #672222 !important;
                color: white !important;
            }
            /* === End Side Navigation Styles === */

            #addsales {
                display: none;
            }

            /* === Loader Animation === */
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
            /* === End Loader === */

            /* === Modern Modal Styles === */
            .modal-content {
                border-radius: 16px;
                border: none;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
                overflow: hidden;
                background: #ffffff;
                animation: fadeIn 0.4s ease-in-out;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: scale(0.95); }
                to { opacity: 1; transform: scale(1); }
            }

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
            /* === End Modal Styles === */

            /* === Tools & CRUD === */
            #tool,#tool2 {
                filter: drop-shadow(5px 5px 5px rgba(0, 0, 0, 0.2));
                width: 60px;
                margin-top: 0;
                position: absolute;
                transition: 0.3s;
                left: 13px;
                top: 4px;
                z-index: 1000;
            }

            #tool:hover,#tool2:hover {
                transform: scale(1.2);
                filter: drop-shadow(5px 5px 5px rgba(0, 0, 0, 0.5));
            }

            .crud-nav,.crud-nav-2 {
                position: relative;
                width: 90px;
                height: 20px;
                float: left;
                background-color: #CCCCCC;
                margin-left: 15px;
                transition: all 0.3s ease;
                transition: height 0.8s ease;
            }

            #data_table {
                margin-left: 90px;
                transition: margin-left 0.3s ease;
            }

            .crud,.crud2 {
                display: none;
                margin: 5px;
                height: 60px;
                margin-bottom: 40px;
                width: 60px;
                border: none;
                border-radius: 14px;
                background: linear-gradient(145deg, #6e2323, #5a1d1d);
                color: white;
                font-size: 20px;
                cursor: pointer;
                transition:
                    transform 0.25s ease,
                    box-shadow 0.25s ease,
                    background-image 0.4s ease;
            }

            .crud:hover,.crud2:hover {
                transform: scale(1.15) translateY(-4px);
                background-image: linear-gradient(145deg, #7a2b2b, #672222);
                box-shadow: 0 8px 18px rgba(0, 0, 0, 0.35);
            }

            .btncrud,.btncrud2 {
                position: absolute;
                left: 10px;
                top: 15%;
            }

            #closenavbot,#closenavbot2 {
                position: absolute;
                left: 30px;
                top: 30px;
            }











        

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

            /* Disabled fuel input styling */
            .form-control:disabled {
                background-color: #f8f9fa !important;
                color: #6c757d !important;
                cursor: not-allowed !important;
                opacity: 0.6 !important;
                border-color: #dee2e6 !important;
            }
            
            .form-control:disabled::placeholder {
                color: #dc3545 !important;
                font-weight: bold !important;
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

            /* Fuel button and input container height matching */
            .fuel-button-container,
            .fuel-input-container {
                min-height: 120px;
                display: flex;
                flex-direction: column;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
            }

            .fuel-type-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(103, 34, 34, 0.4);
            }

            .fuel-type-btn:active {
                transform: translateY(0);
                box-shadow: 0 2px 8px rgba(103, 34, 34, 0.3);
            }

            /* Enhanced input container styling */
            .fuel-input-container {
                background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                border: 2px solid #672222;
                padding: 12px;
                box-shadow: 0 6px 20px rgba(103, 34, 34, 0.15);
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
                animation: slideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
                transition: border-color 0.3s ease, box-shadow 0.3s ease;
            }

            .fuel-input-container:focus-within {
                border-color: #8c2f2f;
                box-shadow: 0 8px 25px rgba(103, 34, 34, 0.2);
            }

            .fuel-input-container[style*="display: none"] {
                animation: slideOut 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: scale(0.95) translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }

            @keyframes slideOut {
                from {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
                to {
                    opacity: 0;
                    transform: scale(0.95) translateY(-10px);
                }
            }

            /* Enhanced label styling */
            .fuel-input-container .form-label {
                font-weight: 600;
                color: #672222;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            /* Enhanced close button */
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
                transition: all 0.3s ease;
                box-shadow: 0 2px 6px rgba(220, 53, 69, 0.3);
            }

            .close-fuel-input:hover {
                transform: rotate(90deg) scale(1.1);
                box-shadow: 0 4px 10px rgba(220, 53, 69, 0.4);
                background-color: #dc3545 !important;
            }

            /* Enhanced input field */
            .fuel-input-container .form-control {
                border: 2px solid #dee2e6;
                border-radius: 8px;
                padding: 10px 14px;
                font-size: 15px;
                transition: all 0.3s ease;
                background-color: #fff;
            }

            .fuel-input-container .form-control:focus {
                border-color: #672222;
                box-shadow: 0 0 0 0.25rem rgba(103, 34, 34, 0.15);
                background-color: #fff;
            }

            /* Enhanced small text styling */
            .fuel-input-container small {
                font-size: 12px;
                margin-top: 4px;
                display: block;
            }

            .fuel-input-container small.text-muted {
                color: #6c757d !important;
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
            <div class="header-title" style="margin-right: 150px;">
                <h1 class="h2 h3-md m-0" style="margin:0;">GASOLINE STATION (ADMIN DASHBOARD)</h1>
                
            </div>

           
            



            <img src="ico.png" id="sidenav" height="auto" style="position: absolute; right: 2%;"  class="img-fluid" alt="logo"/>
        </header>
    
      

        <div id="mySidenav" class="sidenav">
            <button type="button" id="closeBtn" style="background-color:white; color: white; margin-left: 25px;" class="btn-close" aria-label="Close"></button>
        </br>
            <a href="#" id="sales">Daily Sales Report</a>
            <div class="dropdown" style="display:inline-block; width:100%;">
                <a href="#" class="dropdown-toggle" id="inventoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Fuel Inventory
                </a>
                <ul class="dropdown-menu" aria-labelledby="inventoryDropdown" style="  width:100%;">
                    <li><a style="color:#672222; " class="dropdown-item branch-inventory dbranch" href="#" data-branch="Branch 1">Branch 1</a></li>
                    <li><a style="color:#672222;"class="dropdown-item branch-inventory dbranch" href="#" data-branch="Branch 2">Branch 2</a></li>
                    <li><a style="color:#672222;"class="dropdown-item branch-inventory dbranch" href="#" data-branch="Branch 3">Branch 3</a></li>
                </ul>
            </div>
            
            <a href="#" id="lub" >
                Lubricant Inventory
            </a>
            
            <a href="#" id="fuelPrices">
                Fuel Prices
            </a>
           
            
            
            <div id="admininput" style="display: none;">
                <input type="text" placeholder="Password" class="user"/>
                <input type="password" placeholder="Enter Password Again" class="pass"/>
                <div class="action-buttons">
                    <button type="button" id="adlogin">Change</button>
                    <button type="submit" id="adcancel">Cancel</button>
                </div>
            </div>
            <a href="admain.php">Exit</a>
            
        </div>
        
        

         



        <nav class="navbar crud-nav" id="navbot">
            
            <button type="button" id="closenavbot" style="background-color:#672222; color: white;  display:none;" class="btn-close" aria-label="Close"></button>
            <div class="btncrud"  >
                <button type="button" class="btn btn-primary crud" id="addbtn">
                    <img src="addicon.png" alt="icon" id="eye" style="width:30px; height:30px; vertical-align:middle;">

                </button>
                <button type="button" class="btn btn-primary  crud" id="btnedit">
                    <img src="editicon.png" alt="icon" id="eye" style="width:25px; height:25px; vertical-align:middle;">
                </button>
                <button type="button" class="btn btn-primary  crud" id="btndelete">
                    <img src="deleteicon.png" alt="icon" id="eye" style="width:25px; height:25px; vertical-align:middle;">
                </button>
                <button type="button" class="btn btn-primary  crud" id="btnref">
                    <img src="reficon.png" alt="icon" id="eye" style="width:25px; height:25px; vertical-align:middle;">
                </button>
                
            </div>
            <img src="tool.png" id="tool" height="auto" class="img-fluid" alt="logo"/>
            <p style="transform: rotate(-90deg); position:fixed; left:-40px; top:250px; letter-spacing: 5px; white-space: nowrap; z-index: -1;">Click to Slide...</p>
        </nav>

        <nav class="navbar2 crud-nav-2" id="navbot2" style="display: none;">
            
            <button type="button" id="closenavbot2" style="background-color:#672222; color: white;  display:none;" class="btn-close" aria-label="Close"></button>
            <div class="btncrud2"  >
                <button type="button" class="btn btn-primary crud2" id="addbtn2">
                    <img src="addicon.png" alt="icon" id="eye" style="width:30px; height:30px; vertical-align:middle;">

                </button>
                <button type="button" class="btn btn-primary  crud2" id="btnedit2">
                    <img src="editicon.png" alt="icon" id="eye" style="width:25px; height:25px; vertical-align:middle;">
                </button>
                <button type="button" class="btn btn-primary  crud2" id="btndelete2">
                    <img src="deleteicon.png" alt="icon" id="eye" style="width:25px; height:25px; vertical-align:middle;">
                </button>
                <button type="button" class="btn btn-primary  crud2" id="btnref2">
                    <img src="reficon.png" alt="icon" id="eye" style="width:25px; height:25px; vertical-align:middle;">
                </button>
                
            </div>
            <img src="tool.png" id="tool2" height="auto" class="img-fluid" alt="logo"/>
            <p style="transform: rotate(-90deg); position:fixed; left:-40px; top:250px; letter-spacing: 5px; white-space: nowrap; z-index: -1;">Click to Slide...</p>
        </nav>
        




        <div id="data_table" style="margin: 5px; margin-top:15px; margin-left: 140px; margin-right: 50px; overflow-y: auto;box-shadow: 0 8px 24px rgba(0,0,0,0.2); border-radius:10px; border:none;">
            <!-- daily sales will display inside of this tag -->
             
        </div>

        <!-- Fuel Prices Container -->
        <div id="fuelPricesContainer" style="display: none; margin: 5px; margin-top:15px; margin-left: 140px; margin-right: 50px; overflow-y: auto;box-shadow: 0 8px 24px rgba(0,0,0,0.2); border-radius:10px; border:none; padding: 20px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Fuel Price Management</h4>
                <div>
                    <button type="button" class="btn btn-primary me-2" id="btnChangePrice" style="background-color:#672222; border:none; color:#fff;">Change Prices</button>
                    <button type="button" class="btn btn-secondary" id="btnViewHistory" style="background-color:#8c2f2f; border:none; color:#fff;">View History</button>
                </div>
            </div>
            <div id="currentPricesDisplay" class="row">
                <!-- Current prices will be displayed here -->
            </div>
        </div>





        <div id="lubcon" style="display: none; margin-top:30px; margin-left:100px;">
            <div class="button-container">
                <button data-branch="Branch 1" class="btnlub lub-branch-inventory" autofocus>BRANCH 1</button>
                <button data-branch="Branch 2" class="btnlub lub-branch-inventory">BRANCH 2</button>
                <button data-branch="Branch 3" class="btnlub lub-branch-inventory">BRANCH 3</button>
            </div>
            <div id="data_table2" style="margin: 5px; margin-top:25px; margin-left: 50px; margin-right: 50px; overflow-y: auto;box-shadow: 0 8px 24px rgba(0,0,0,0.2); border-radius:10px; border:none; display:none;">
                <!-- lub inventory will display inside of this tag -->
                
            </div>
            <div id="data_table3" style="margin: 5px; margin-top:25px; margin-left: 50px; margin-right: 50px; overflow-y: auto;box-shadow: 0 8px 24px rgba(0,0,0,0.2); border-radius:10px; border:none; display:none;">
                <!-- lub inventory will display inside of this tag -->
                
            </div>
        </div>
        
        

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
                    <div class="mb-3">
                        <label for="branch" class="form-label">Branch</label>
                        <select class="form-select" name="branch" required>
                        <option value="">Select Branch</option>
                        <option value="Branch 1">Branch 1</option>
                        <option value="Branch 2">Branch 2</option>
                        <option value="Branch 3">Branch 3</option>
                        </select>
                    </div>
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
                        <input type="number" class="form-control" name="quantity" min="1" required>
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



        




        <div id="fuel_inventory" class="inventory-card" style="display:none;">
            <div class="stock-panel hidden addless">
                <button class="stock-btn add" id="btnAddStock">Add Stock</button>
                <button class="stock-btn less" id="btnLessStock">Less Stock</button>
            </div>

            <button class="toggle-btn" >Manage Stock</button>

            <center> <h2 id="inventoryTitle" class="h2 h3-md m-0" style="margin:0; padding:5px;">FUEL INVENTORY</h2></center>
            <canvas id="gasInventoryChart" width="400" height="300"></canvas>
            <!-- inventory will display inside of this tag -->
        </div>
        


        <div class="card mt-4 modern-card transaction" style="display: none;">
            <div class="card-header modern-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Inventory Transactions</h5>
                <span id="transactionBranch" class="badge bg-secondary"></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead >
                            <tr >
                                <th scope="col" style="background-color: #CCCCCC;">Date</th>
                                <th scope="col" style="background-color: #CCCCCC;">Fuel Type</th>
                                <th scope="col" style="background-color: #CCCCCC;">Last Stock</th>
                                <th scope="col" style="background-color: #CCCCCC;">Action</th>
                                <th scope="col" style="background-color: #CCCCCC;">Quantity</th>
                               
                                <th scope="col" style="background-color: #CCCCCC;">Available Stock</th>
                            </tr>
                        </thead>
                        <tbody id="transactionTableBody">
                            <tr>
                                <td colspan="6" class="text-center py-3">No transactions yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
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
                            
                            <!-- Branch -->
                            <div class="mb-4 d-flex align-items-center gap-3 flex-wrap">
                                
                                <div class="flex-fill">
                                    <label for="branch" class="form-label fw-semibold">Gasoline Station Branch</label>
                                    <select class="form-select" name="branch" id="branch" required>
                                        <option value="" selected disabled>Select branch here...</option>
                                        <option value="Branch 1">Branch 1 ()</option>
                                        <option value="Branch 2">Branch 2 ()</option>
                                        <option value="Branch 3">Branch 3 ()</option>
                                    </select>
                                </div>

                                <!-- Date Input -->
                                <div>
                                    <label for="date" class="form-label fw-semibold">Date</label>
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>
                            </div>


                            <!-- Fuel Sales -->
                            <hr class="my-4" style="border:0; height:3px; background:#672222; opacity:1;">
                            <h5 class="fw-bold text-uppercase mb-3">Fuel Sales</h5>

                            <!-- Fuel Type Buttons and Inputs in Same Position -->
                            <div class="row g-3 mb-3" id="fuelTypeButtons">
                                <!-- Diesel -->
                                <div class="col-md-4 fuel-type-wrapper" data-fuel="diesel">
                                    <div class="fuel-button-container" id="dieselButtonContainer">
                                        <button type="button" class="btn btn-outline-primary w-100 fuel-type-btn" data-fuel="diesel" style="background-color:#672222; border:none; color:#fff;">
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
                                        <button type="button" class="btn btn-outline-primary w-100 fuel-type-btn" data-fuel="premium" style="background-color:#672222; border:none; color:#fff;">
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
                                        <button type="button" class="btn btn-outline-primary w-100 fuel-type-btn" data-fuel="unleaded" style="background-color:#672222; border:none; color:#fff;">
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
                            
                            <!-- Hidden fields for prices (auto-filled from fuel_prices table) -->
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

                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" name="date" id="date" required>
                            </div>

                            <div class="mb-3">
                                <label for="branch" class="form-label">Branch</label>
                                <select class="form-select" name="branch" id="branch" required>
                                    <option value="">Select Branch</option>
                                    <option value="Branch 1">Branch 1</option>
                                    <option value="Branch 2">Branch 2</option>
                                    <option value="Branch 3">Branch 3</option>
                                </select>
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







        <div class="modal fade" id="dateDetailsModal" tabindex="-1">
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

        <!-- Per-row Sales Edit Modal (Sales Details) -->
        <div class="modal fade" id="saleRowEditModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Sale (Single Row)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="saleRowEditForm">
                            <input type="hidden" id="saleRowId">
                            <input type="hidden" id="saleRowBranch">
                            
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="text" class="form-control" id="saleRowDate" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Time</label>
                                <input type="text" class="form-control" id="saleRowTime" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fuel Type</label>
                                <input type="text" class="form-control" id="saleRowFuelType" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Volume Sold (L)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="saleRowVolume">
                            </div>
                            <div class="mb-3" style="display:none;">
                                <label class="form-label">Price per Liter (₱)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="saleRowPrice">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Total Amount (₱)</label>
                                <input type="text" class="form-control" id="saleRowTotal" readonly>
                            </div>
                            
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveSaleRowBtn" style="background-color:#672222;border:none;">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fuel Price Management Modal -->
        <div class="modal fade" id="fuelPriceModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Manage Fuel Prices</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="fuelPriceForm">
                            <div class="mb-3">
                                <label for="priceBranch" class="form-label">Branch</label>
                                <select class="form-select" name="branch" id="priceBranch" required>
                                    <option value="">Select Branch</option>
                                    <option value="Branch 1">Branch 1</option>
                                    <option value="Branch 2">Branch 2</option>
                                    <option value="Branch 3">Branch 3</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="priceDate" class="form-label">Effective Date</label>
                                <input type="date" class="form-control" name="effective_date" id="priceDate" required>
                            </div>
                            <div class="mb-3">
                                <label for="priceTime" class="form-label">Effective Time</label>
                                <input type="time" class="form-control" name="effective_time" id="priceTime" required>
                            </div>
                            <hr>
                            <h6 class="mb-3">Fuel Prices (₱ per Liter)</h6>
                            <div class="mb-3">
                                <label for="dieselPrice" class="form-label">Diesel Price</label>
                                <input type="number" class="form-control" name="diesel_price" id="dieselPrice" step="0.01" min="0" placeholder="Enter price" required>
                            </div>
                            <div class="mb-3">
                                <label for="premiumPrice" class="form-label">Premium Price</label>
                                <input type="number" class="form-control" name="premium_price" id="premiumPrice" step="0.01" min="0" placeholder="Enter price" required>
                            </div>
                            <div class="mb-3">
                                <label for="unleadedPrice" class="form-label">Unleaded Price</label>
                                <input type="number" class="form-control" name="unleaded_price" id="unleadedPrice" step="0.01" min="0" placeholder="Enter price" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" style="border:none; color:#fff;" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveFuelPrice" style="background-color:#672222; border:none; color:#fff;">Save Prices</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fuel Price History Modal -->
        <div class="modal fade" id="fuelPriceHistoryModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Fuel Price History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="historyBranch" class="form-label">Filter by Branch</label>
                            <select class="form-select" id="historyBranch" style="max-width: 300px;">
                                <option value="">All Branches</option>
                                <option value="Branch 1">Branch 1</option>
                                <option value="Branch 2">Branch 2</option>
                                <option value="Branch 3">Branch 3</option>
                            </select>
                        </div>
                        <div id="priceHistoryTable" class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Branch</th>
                                        <th>Effective Date</th>
                                        <th>Effective Time</th>
                                        <th>Diesel (₱/L)</th>
                                        <th>Premium (₱/L)</th>
                                        <th>Unleaded (₱/L)</th>
                                        <th>Changed By</th>
                                    </tr>
                                </thead>
                                <tbody id="priceHistoryBody">
                                    <tr>
                                        <td colspan="7" class="text-center">Loading history...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" style="border:none; background:#672222;" data-bs-dismiss="modal">Close</button>
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
        fetchlub();
        fetchUser(); //load data
        $(".inventory-card").hide();
        
        // Variable to store products for search functionality
        let allProducts = [];
        

        


        function formatDateLabel(dateStr) {
            const dateObj = new Date(dateStr);
            if (isNaN(dateObj)) {
                return dateStr;
            }
            return dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        

        function hideLoaderAfter3Sec() {
            setTimeout(function(){
                $(".loader-overlay").css("display", "none");
            }, 2000); // 2000ms = 2 seconds
        }
        

        



        function fetchUser() {
            var gastblsales = "LOADGASSALES";
            var report_type = $('#label_filter').val();   
            var from_date   = $('#from_date').val();         
            var to_date     = $('#to_date').val();       

            $.ajax({
                url: "adminfunction.php",
                method: "POST",
                data: {
                    gastblsales: gastblsales,
                    report_type: report_type,
                    from_date: from_date,
                    to_date: to_date
                },
                success: function(data) {
                    $('#data_table').html(data);
                }
            });
        }

        
        $(document).on('change', '#label_filter', function() {
            fetchUser();
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();
            
        });


        $(document).on('click', '.date-details-btn', function(){
            const date = $(this).data('date');
            const branchName = $(this).data('branch') || '';
            const detailScope = branchName ? 'branch' : 'all';
            const branch = detailScope === 'branch' ? branchName : '';
            const label = detailScope === 'branch'
                ? `${branchName.toUpperCase()} | ${formatDateLabel(date)}`
                : formatDateLabel(date);
            $('#dateDetailsLabel').text('Sales Details - ' + label);
            $('#dateDetailsBody').html('<p class="text-center my-3 text-muted">Loading...</p>');
            $('#dateDetailsModal').modal('show');

            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: {
                    gastblsales: 'DATE_DETAILS',
                    date: date,
                    branch: branch,
                    detail_scope: detailScope
                },
                success: function(data){
                    $('#dateDetailsBody').html(data);
                },
                error: function(){
                    $('#dateDetailsBody').html('<p class="text-center text-danger mb-0">Unable to load details. Please try again.</p>');
                }
            });
        });





        // Filter button click (delegated)
        $(document).on('click', '#filterBtn', function(e){
            e.preventDefault(); // prevent default form submission
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();

            var from_date = $('#from_date').val();
            var to_date   = $('#to_date').val();
            var report_type = $('#label_filter').val(); // <-- get selected report type

            console.log("Sending: ", report_type, from_date, to_date); // Debug

            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: {
                    gastblsales: 'LOADGASSALES',
                    report_type: report_type, // <-- send report type
                    from_date: from_date,
                    to_date: to_date
                },
                success: function(data){
                    $('#data_table').html(data);
                }
            });
        });

        // Reset button click (delegated)
        $(document).on('click', '#resetBtn', function(e){
            e.preventDefault(); // prevent default action
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();

            // Reset inputs
            $('#from_date').val('');
            $('#to_date').val('');
            $('#label_filter').val(''); // <-- reset report type dropdown

            // Reload table with defaults
            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: { 
                    gastblsales: 'LOADGASSALES',
                    report_type: '',   // send empty report_type
                    from_date: '',     // send empty dates
                    to_date: ''
                },
                success: function(data){
                    $('#data_table').html(data); // reloads full table
                }
            });
        });




        















        $(document).ready(function(){
            let today = new Date().toISOString().split('T')[0];
            $("#date").val(today);
        });

        $('#fuel_inventory .toggle-btn').click(function(){
            var panel = $(this).siblings('.stock-panel');

            // Toggle the panel
            panel.toggleClass('hidden');

            // Change button text based on panel state
            if(panel.hasClass('hidden')){
                $(this).text("Manage Stock");  // default text when hidden
            } else {
                $(this).text("Hide");  // text when visible
            }
        });


        $("#sales").click(function(){
            $(".inventory-card").hide();
            $("#navbot").show();
            $("#navbot2").hide();
            $(".transaction").hide();
            $(".addless").hide();
            $("#lubcon").hide();
            $("#fuelPricesContainer").hide();
            $("#data_table").fadeIn(500);
        });

        // Fuel Prices Menu Click
        $("#fuelPrices").click(function(){
            $(".inventory-card").hide();
            $("#navbot").hide();
            $("#navbot2").hide();
            $(".transaction").hide();
            $(".addless").hide();
            $("#lubcon").hide();
            $("#data_table").hide();
            $("#fuelPricesContainer").fadeIn(500);
            $("#mySidenav").css("width", "0");
            $("#sidenav").show();
            loadCurrentPrices();
        });

        // Load Current Prices
        function loadCurrentPrices() {
            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: { action: 'GET_CURRENT_PRICES' },
                dataType: 'json',
                success: function(data) {
                    let html = '';
                    if (data.length > 0) {
                        data.forEach(function(branch) {
                            html += `
                                <div class="col-md-4 mb-3">
                                    <div class="card" style="box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                        <div class="card-header" style="background: linear-gradient(90deg, #672222, #8c2f2f); color: white;">
                                            <h5 class="mb-0">${branch.branch}</h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-2"><strong>Diesel:</strong> ₱${parseFloat(branch.diesel_price).toFixed(2)}/L</p>
                                            <p class="mb-2"><strong>Premium:</strong> ₱${parseFloat(branch.premium_price).toFixed(2)}/L</p>
                                            <p class="mb-0"><strong>Unleaded:</strong> ₱${parseFloat(branch.unleaded_price).toFixed(2)}/L</p>
                                            <small class="text-muted">Updated: ${branch.effective_date} ${branch.effective_time}</small>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        html = '<div class="col-12"><p class="text-center text-muted">No price records found. Please set prices for each branch.</p></div>';
                    }
                    $('#currentPricesDisplay').html(html);
                },
                error: function() {
                    $('#currentPricesDisplay').html('<div class="col-12"><p class="text-center text-danger">Error loading prices.</p></div>');
                }
            });
        }

        // Change Price Button
        $("#btnChangePrice").click(function(){
            $("#fuelPriceModal").modal("show");
            // Set today's date and current time as default
            const today = new Date().toISOString().split('T')[0];
            const now = new Date().toTimeString().slice(0, 5);
            $("#priceDate").val(today);
            $("#priceTime").val(now);
            $("#fuelPriceForm")[0].reset();
            $("#priceDate").val(today);
            $("#priceTime").val(now);
        });

        // Save Fuel Price
        $("#saveFuelPrice").click(function(){
            const branch = $("#priceBranch").val();
            const date = $("#priceDate").val();
            const time = $("#priceTime").val();
            const dieselPrice = $("#dieselPrice").val();
            const premiumPrice = $("#premiumPrice").val();
            const unleadedPrice = $("#unleadedPrice").val();

            if (!branch || !date || !time || !dieselPrice || !premiumPrice || !unleadedPrice) {
                alert("Please fill all fields");
                return;
            }

            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: {
                    action: 'SAVE_FUEL_PRICE',
                    branch: branch,
                    effective_date: date,
                    effective_time: time,
                    diesel_price: dieselPrice,
                    premium_price: premiumPrice,
                    unleaded_price: unleadedPrice
                },
                success: function(response) {
                    alert(response);
                    $("#fuelPriceModal").modal("hide");
                    loadCurrentPrices();
                },
                error: function() {
                    alert("Error saving fuel prices");
                }
            });
        });

        // View History Button
        $("#btnViewHistory").click(function(){
            $("#fuelPriceHistoryModal").modal("show");
            loadPriceHistory();
        });

        // Load Price History
        function loadPriceHistory(branch = '') {
            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: {
                    action: 'GET_PRICE_HISTORY',
                    branch: branch
                },
                dataType: 'json',
                success: function(data) {
                    let html = '';
                    if (data.length > 0) {
                        data.forEach(function(record) {
                            html += `
                                <tr>
                                    <td>${record.branch}</td>
                                    <td>${record.effective_date}</td>
                                    <td>${record.effective_time}</td>
                                    <td>₱${parseFloat(record.diesel_price).toFixed(2)}</td>
                                    <td>₱${parseFloat(record.premium_price).toFixed(2)}</td>
                                    <td>₱${parseFloat(record.unleaded_price).toFixed(2)}</td>
                                    <td>${record.changed_by || 'System'}</td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="7" class="text-center">No price history found</td></tr>';
                    }
                    $('#priceHistoryBody').html(html);
                },
                error: function() {
                    $('#priceHistoryBody').html('<tr><td colspan="7" class="text-center text-danger">Error loading history</td></tr>');
                }
            });
        }

        // Filter history by branch
        $("#historyBranch").change(function(){
            loadPriceHistory($(this).val());
        });
        
        // When opening Add Sales, prefill availability for selected branch
        $(document).on('show.bs.modal', '#addsales', function(){
            var b = $('#branch').val();
            if(!b){
                $('#availableDiesel').text('Available: - L');
                $('#availablePremium').text('Available: - L');
                $('#availableUnleaded').text('Available: - L');
                resetPriceDisplays();
                return;
            }
            // Fetch inventory and prices
            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: { action: 'GET_INVENTORY', branch: b },
                dataType: 'json',
                success: function(data){
                    $('#availableDiesel').text('Available: ' + data[0] + ' L');
                    $('#availablePremium').text('Available: ' + data[1] + ' L');
                    $('#availableUnleaded').text('Available: ' + data[2] + ' L');
                    
                    // Store current stock levels for validation
                    window.currentStock = {
                        diesel: data[0],
                        premium: data[1],
                        unleaded: data[2]
                    };
                    
                    // Enable/disable fields based on stock
                    updateFuelFieldStates();
                }
            });
            
            // Fetch and display fuel prices for the branch
            if(b) {
                fetchBranchPrices(b);
                loadProductsByBranch(b);
            }
        });
        
        // Update availability when branch changes in modal
        $(document).on('change', '#branch', function(){
            var b = $(this).val();
            if(!b){
                $('#availableDiesel').text('Available: - L');
                $('#availablePremium').text('Available: - L');
                $('#availableUnleaded').text('Available: - L');
                // Reset stock levels and enable all fields
                window.currentStock = { diesel: 0, premium: 0, unleaded: 0 };
                updateFuelFieldStates();
                resetPriceDisplays();
                return;
            }
            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: { action: 'GET_INVENTORY', branch: b },
                dataType: 'json',
                success: function(data){
                    $('#availableDiesel').text('Available: ' + data[0] + ' L');
                    $('#availablePremium').text('Available: ' + data[1] + ' L');
                    $('#availableUnleaded').text('Available: ' + data[2] + ' L');
                    
                    // Store current stock levels for validation
                    window.currentStock = {
                        diesel: data[0],
                        premium: data[1],
                        unleaded: data[2]
                    };
                    
                    // Enable/disable fields based on stock
                    updateFuelFieldStates();
                }
            });
            
            // Fetch and display fuel prices for the branch
            fetchBranchPrices(b);
            
            // Load products for the selected branch
            loadProductsByBranch(b);
        });
        
        // Function to fetch fuel prices for a specific branch
        function fetchBranchPrices(branch) {
            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: { action: 'GET_BRANCH_PRICE', branch: branch },
                dataType: 'json',
                success: function(data) {
                    if (data && data.diesel_price) {
                        // Set hidden price fields
                        $('#dvsp').val(data.diesel_price);
                        $('#pvsp').val(data.premium_price);
                        $('#uvsp').val(data.unleaded_price);
                        
                        // Display prices
                        $('#dieselPriceDisplay').text('Price: ₱' + parseFloat(data.diesel_price).toFixed(2) + ' per liter');
                        $('#premiumPriceDisplay').text('Price: ₱' + parseFloat(data.premium_price).toFixed(2) + ' per liter');
                        $('#unleadedPriceDisplay').text('Price: ₱' + parseFloat(data.unleaded_price).toFixed(2) + ' per liter');
                    } else {
                        resetPriceDisplays();
                        alert('Warning: No fuel prices found for ' + branch + '. Please set prices in Fuel Prices section first.');
                    }
                },
                error: function() {
                    resetPriceDisplays();
                    console.error('Error fetching fuel prices');
                }
            });
        }
        
        // Function to reset price displays
        function resetPriceDisplays() {
            $('#dvsp').val(0);
            $('#pvsp').val(0);
            $('#uvsp').val(0);
            $('#dieselPriceDisplay').text('');
            $('#premiumPriceDisplay').text('');
            $('#unleadedPriceDisplay').text('');
        }
        
        // Function to update fuel field states based on stock levels
        function updateFuelFieldStates() {
            if (!window.currentStock) return;
            
            // Diesel field
            if (window.currentStock.diesel <= 0) {
                $('#dvs').prop('disabled', true).val('').attr('placeholder', 'No stock available');
                $('#availableDiesel').html('<span style="color: red;">Available: 0 L (OUT OF STOCK)</span>');
            } else {
                $('#dvs').prop('disabled', false).attr('placeholder', 'Liters').attr('max', window.currentStock.diesel);
                $('#availableDiesel').html('Available: ' + window.currentStock.diesel + ' L');
            }
            
            // Premium field
            if (window.currentStock.premium <= 0) {
                $('#pvs').prop('disabled', true).val('').attr('placeholder', 'No stock available');
                $('#availablePremium').html('<span style="color: red;">Available: 0 L (OUT OF STOCK)</span>');
            } else {
                $('#pvs').prop('disabled', false).attr('placeholder', 'Liters').attr('max', window.currentStock.premium);
                $('#availablePremium').html('Available: ' + window.currentStock.premium + ' L');
            }
            
            // Unleaded field
            if (window.currentStock.unleaded <= 0) {
                $('#uvs').prop('disabled', true).val('').attr('placeholder', 'No stock available');
                $('#availableUnleaded').html('<span style="color: red;">Available: 0 L (OUT OF STOCK)</span>');
            } else {
                $('#uvs').prop('disabled', false).attr('placeholder', 'Liters').attr('max', window.currentStock.unleaded);
                $('#availableUnleaded').html('Available: ' + window.currentStock.unleaded + ' L');
            }
        }
        
        // Real-time validation for fuel volume inputs
        $(document).on('input', '#dvs', function() {
            validateFuelInput($(this), 'diesel');
        });
        
        $(document).on('input', '#pvs', function() {
            validateFuelInput($(this), 'premium');
        });
        
        $(document).on('input', '#uvs', function() {
            validateFuelInput($(this), 'unleaded');
        });
        
        // Function to validate fuel input against available stock
        function validateFuelInput(input, fuelType) {
            if (!window.currentStock) return;
            
            var inputValue = parseFloat(input.val()) || 0;
            var availableStock = window.currentStock[fuelType];
            
            if (inputValue > availableStock) {
                // Show alert and reset to max available
                alert('Warning: You cannot sell more than ' + availableStock + ' liters of ' + fuelType.charAt(0).toUpperCase() + fuelType.slice(1) + '. Available stock: ' + availableStock + ' L');
                input.val(availableStock);
            }
            
            // Visual feedback for approaching stock limit
            var stockElement = $('#available' + fuelType.charAt(0).toUpperCase() + fuelType.slice(1));
            if (inputValue > availableStock * 0.8) {
                stockElement.css('color', '#ffc107'); // Yellow warning
            } else if (inputValue > availableStock * 0.5) {
                stockElement.css('color', '#17a2b8'); // Blue info
            } else {
                stockElement.css('color', ''); // Default color
            }
        }


        $("#stckbtn").click(function(){
            $("#stockModal").modal("hide");
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();
        });

        // Show Add Stock Modal
       
        let availableSpace = 0;

        const maxCapacity = { 'Diesel': 16000, 'Premium': 12000, 'Unleaded': 6000 };

        function handleStockModal(modalBtnId, actionType) {
            $(modalBtnId).click(function(){
                let modalTitle = (actionType === "ADD_STOCK") ? "Add Fuel Stock" : "Lessen Fuel Stock";
                $("#stockModalTitle").text(modalTitle);
                $("#stockAction").val(actionType);
                $("#stockForm")[0].reset();
                $("#availableStock").text("Available: - L");
                $('input[name="quantity"]').prop('disabled', true); // disable until selection
                $("#stockModal").modal("show");

                // When branch or fuel type changes
                $('select[name="branch"], select[name="fuel_type"]').off('change').on('change', function(){
                    let branch = $('select[name="branch"]').val();
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
            });
        }

        // Limit quantity input dynamically
        $('input[name="quantity"]').on('input', function(){
            let qty = parseInt($(this).val());
            let maxQty = parseInt($(this).attr('max'));
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

        // Branch inventory click
        $(document).on('click', '.branch-inventory', function(e){
            e.preventDefault();
            let branch = $(this).data('branch');
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

            loadInventoryChart(branch); // initial load
            fetchTransactions(branch);
        });

        $(document).on('click', '.lub-branch-inventory', function(e) {
            e.preventDefault();
            // Get the branch from data attribute
            let branch = $(this).data('branch');
            fetchDailyTrans(branch);
            fetchLubInventory(branch);
        });

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
        // Delegate search click within lubricant inventory table
        $(document).on('click', '#data_table2 .btn-search', function(){
            const activeBranchBtn = $('.button-container .btnlub.active');
            const branch = activeBranchBtn.length ? activeBranchBtn.data('branch') : 'Branch 1';
            fetchLubInventory(branch);
        });
        // Pressing Enter in search field triggers search
        $(document).on('keypress', '#data_table2 .header-search input.form-control', function(e){
            if(e.which === 13){
                e.preventDefault();
                $('#data_table2 .btn-search').click();
            }
        });
        $('.btnlub').click(function() {
            $('.btnlub').removeClass('active'); // remove from all
            $(this).addClass('active');          // add to clicked
        });
        $('#lub').click(function() {
            
            $('.button-container .btnlub:first').addClass('active');
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();
            let branch = $(this).data('branch');
            fetchDailyTrans('Branch 1');
            $(".inventory-card").hide();
            $("#data_table").hide();
            $("#navbot").hide();
            $(".transaction").hide();
            $(".addless").hide();
            fetchLubInventory('Branch 1');
            $("#navbot2").show();
            $("#lubcon").show();
        });


        setInterval(function(){ 
            if(currentBranch){
                loadInventoryChart(currentBranch);  // updates chart
                fetchTransactions(currentBranch);   // updates table
            }
        }, 5000);







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

       








        $("#tool").click(function(){
            $(".crud-nav").css("height", "100vh");
            $(".crud-nav").addClass("expanded"); // Add class for mobile CSS
            
            $(".crud").css("display", "block");
            $("#closenavbot").css("display", "block");
            $("#tool").css("margin-top", "535px");
        });
         $("#tool2").click(function(){
            $(".crud-nav-2").css("height", "100vh");
            $(".crud-nav-2").addClass("expanded"); // Add class for mobile CSS
            $(".crud2").css("display", "block");
            $("#closenavbot2").css("display", "block");
            $("#tool2").css("margin-top", "535px");
        });

        $("#closenavbot").click(function(){
            $(".crud-nav").css("height", "20px");
            $(".crud-nav").removeClass("expanded"); // Remove class for mobile CSS
           
            $(".crud").css("display", "none");
            $("#closenavbot").css("display", "none");
            $("#tool").css("margin-top", "0px");
        });
         $("#closenavbot2").click(function(){
            $(".crud-nav-2").css("height", "20px");
            $(".crud-nav-2").removeClass("expanded"); // Remove class for mobile CSS
            $(".crud2").css("display", "none");
            $("#closenavbot2").css("display", "none");
            $("#tool2").css("margin-top", "0px");
        });







        var id; 

        
        
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





        // Function to reset fuel buttons and inputs
        function resetFuelButtons() {
            // Show all button containers and hide all input containers with smooth transition
            $('.fuel-button-container').each(function() {
                $(this).css({
                    'display': 'flex',
                    'opacity': '1',
                    'transform': 'scale(1)'
                });
            });
            
            $('.fuel-input-container').each(function() {
                $(this).css({
                    'display': 'none',
                    'opacity': '0',
                    'transform': 'scale(0.95) translateY(-10px)'
                });
            });
            
            // Clear all fuel input values
            $('#dvs').val('');
            $('#pvs').val('');
            $('#uvs').val('');
        }

        // Handle fuel type button clicks
        $(document).on('click', '.fuel-type-btn', function() {
            const fuelType = $(this).data('fuel');
            const wrapper = $(this).closest('.fuel-type-wrapper');
            const buttonContainer = wrapper.find('.fuel-button-container');
            const inputContainer = wrapper.find('.fuel-input-container');
            
            // Animate button out
            buttonContainer.css({
                'opacity': '0',
                'transform': 'scale(0.9)'
            });
            
            setTimeout(function() {
                buttonContainer.css('display', 'none');
                
                // Show and animate input in
                inputContainer.css({
                    'display': 'flex',
                    'opacity': '0',
                    'transform': 'scale(0.95) translateY(-10px)'
                });
                
                // Trigger animation
                setTimeout(function() {
                    inputContainer.css({
                        'opacity': '1',
                        'transform': 'scale(1) translateY(0)'
                    });
                }, 10);
            }, 200);
        });

        // Handle close fuel input button clicks
        $(document).on('click', '.close-fuel-input', function() {
            const fuelType = $(this).data('fuel');
            const wrapper = $(this).closest('.fuel-type-wrapper');
            const buttonContainer = wrapper.find('.fuel-button-container');
            const inputContainer = wrapper.find('.fuel-input-container');
            
            // Animate input out
            inputContainer.css({
                'opacity': '0',
                'transform': 'scale(0.95) translateY(-10px)'
            });
            
            // Clear the input value
            const inputId = fuelType === 'diesel' ? 'dvs' : (fuelType === 'premium' ? 'pvs' : 'uvs');
            $('#' + inputId).val('');
            
            setTimeout(function() {
                inputContainer.css('display', 'none');
                
                // Show and animate button in
                buttonContainer.css({
                    'display': 'flex',
                    'opacity': '0',
                    'transform': 'scale(0.9)'
                });
                
                // Trigger animation
                setTimeout(function() {
                    buttonContainer.css({
                        'opacity': '1',
                        'transform': 'scale(1)'
                    });
                }, 10);
            }, 200);
        });

        // Show Modal Dialog
        $('#addbtn').click(function() {
            $("#submit").text("SAVE");
            $("#submit").val("SAVE");
            $("#addsales").modal("show");
            $("#mySidenav").css("width", "0");
            $("#sidenav").show();
            $('#branch').val("");
            $('#dvs').val("");
            $('#pvs').val("");
            $('#uvs').val("");
            $('#product').val("");
            $('#price').val("");
            $('.expense_amount').val("");
            $('.other_expense').val("");
            $('.modal-title').text("Add New Sales");
            $('#gastblsales').val('SAVE');
            
            // Reset price displays
            resetPriceDisplays();
            
            // Reset fuel buttons and inputs
            resetFuelButtons();
            
            // Clear global id variable
            id = null;
            
            // Clear existing lubricant rows and add one empty row
            $('#fuelContainer').empty();
            $("#addrow").trigger('click');
        });
        

        // Add expense row
        $(document).on('click', '#add_expense', function() {
            let row = `
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
            </div>`;
            $('#expensesContainer').append(row);
        });

        // Remove expense row
        $(document).on('click', '.remove_expense', function() {
            $(this).closest('.expense_row').remove();
        });

        // Show "Specify" input inline when Others is selected
        $(document).on('change', '.expense_type', function() {
            let row = $(this).closest('.expense_row');
            let otherInput = row.find('.other_expense');
            
            if ($(this).val() === 'Others') {
                otherInput.show();
            } else {
                otherInput.hide().val('');
            }
        });








       

        
        // Fetch transactions function
        function fetchTransactions(branch) {
            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: { action: 'GET_TRANSACTIONS', branch: branch },
                dataType: 'json',
                success: function(transactions) {
                    let rows = '';
                    if (transactions.length > 0) {
                        transactions.forEach(function(tx) {
                            let action = tx.action.trim().toUpperCase();
                            let badgeClass = action === "SOLD" ? "badge-sale" :
                                            action === "ADD"  ? "bg-success" :
                                            action === "LESS" ? "bg-danger" :
                                                                "bg-secondary";
                            
                            rows += `
                                <tr>
                                    <td>${tx.created_at}</td>
                                    <td>${tx.fuel_type}</td>
                                    <td>${tx.last_stock || 0} L</td>
                                    <td><span class="badge ${badgeClass}">${action}</span></td>
                                    <td>${tx.quantity} L</td>
                                    
                                    <td>${tx.available_stock || 0} L</td>
                                </tr>
                            `;
                        });
                    } else {
                        rows = '<tr><td colspan="6" class="text-center py-3">No transactions yet</td></tr>';
                    }
                    $('#transactionTableBody').html(rows);
                },
                error: function(err) {
                    console.error('Error fetching transactions:', err);
                }
            });
        }






        $('#submit').click(function() {
            var branch = $('#branch').val();
            var date = $('#date').val();

            // Fuel volumes sold
            var dvs = parseFloat($('#dvs').val()) || 0;
            var dvsp = parseFloat($('#dvsp').val()) || 0;
            var pvs = parseFloat($('#pvs').val()) || 0;
            var pvsp = parseFloat($('#pvsp').val()) || 0;
            var uvs = parseFloat($('#uvs').val()) || 0;
            var uvsp = parseFloat($('#uvsp').val()) || 0;
            
            // Validate required fields
            if (!branch || !date) {
                alert("Please fill all required fields");
                return;
            }
            
            // Validate that prices are set (from fuel_prices table)
            if (dvsp <= 0 || pvsp <= 0 || uvsp <= 0) {
                alert("Error: Fuel prices are not set for this branch. Please set prices in the Fuel Prices section first.");
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

            // Lubricant rows
            let pname  = $('input[name="pname[]"]').map(function(){ return $(this).val(); }).get();
            let pprice = $('input[name="pprice[]"]').map(function(){ return parseFloat($(this).val()) || 0; }).get();
            let pqty   = $('input[name="pqty[]"]').map(function(){ return parseInt($(this).val()) || 0; }).get();

            // Expense rows
            let expense_type   = $('select[name="expense_type[]"]').map(function(){ return $(this).val(); }).get();
            let expense_amount = $('input[name="expense_amount[]"]').map(function(){ return parseFloat($(this).val()) || 0; }).get();
            let other_expense  = $('input[name="other_expense[]"]').map(function(){ return $(this).val(); }).get();

            // Determine if this is SAVE or UPDATE
            var actionType = id ? "UPDATE" : "SAVE";

            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: {
                    gastblsales: actionType,
                    id: id, // will be undefined for SAVE, used for UPDATE
                    date: date,
                    branch: branch,
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

                    // Reset id for next add
                    id = null;

                    // Refresh table
                    fetchUser();
                    $(".loader-overlay").css("display", "flex");
                    hideLoaderAfter3Sec();
                    fetchTransactions(branch);
                }
            });
        });


        // Row click listener
        $(document).on('click', '.report-table tr', function(e) {
            if ($(this).hasClass('no-hover')) return;

            // Find the checkbox in the first column
            var checkbox = $(this).find("td:first-child input[type='checkbox']");
            if (checkbox.length) {
                if (!$(e.target).is("input[type='checkbox']")) {
                    checkbox.prop("checked", !checkbox.prop("checked"));
                }

                $('.report-table .check_box').not(checkbox).prop('checked', false);

                // Set or clear the global id variable based on checkbox state
                if (checkbox.is(':checked')) {
                    id = checkbox.val();
                } else {
                    id = null;
                }
            }
        });

        // CheckBox Listener, Record Selected
        $(document).on('click', '.check_box', function() {
            // Toggle the current checkbox
            $(this).prop('checked', !$(this).prop('checked'));
   
            // Uncheck all other checkboxes
            $('.check_box').not(this).prop('checked', false);

            // Set or clear the global id variable
            if ($(this).is(':checked')) {
                id = $(this).val();
            } else {
                id = null;
            }
        });

        $(document).on('click', '#btnref', function() { 
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();
            fetchUser();
        });

        $(document).on('click', '#btnref2', function() { 
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter3Sec();
            // Get the active branch button to determine which table to refresh
            const activeBranchBtn = $('.button-container .btnlub.active');
            const branch = activeBranchBtn.length ? activeBranchBtn.data('branch') : 'Branch 1';
            fetchLubInventory(branch);
        });

        $(document).on('click', '#btndelete', function() {
            if (!id) {
                alert("Please select a record to delete.");
                return;
            }
            if (confirm("Are you sure you want to delete this record?")) {
                $.ajax({
                    url: "adminfunction.php",
                    method: "POST",
                    data: { id: id, gastblsales: "DELETE" }, // <-- change here
                    success: function(data) {
                        alert(data);
                        fetchUser(); // reloads the table
                    }
                });
            } else {
                return false;
            }
        });

        // ---------- Per-row actions inside Sales Details (independent from main CRUD) ----------
        // Open per-row edit modal
        $(document).on('click', '.sale-edit-btn', function () {
            const rowId = $(this).data('id');
            if (!rowId) {
                alert('Invalid record selected.');
                return;
            }

            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    gastblsales: 'DATE_DETAILS_SELECT_ROW',
                    id: rowId
                },
                success: function (data) {
                    if (!data || !data.id) {
                        alert('Unable to load sale details for editing.');
                        return;
                    }

                    $('#saleRowId').val(data.id);
                    $('#saleRowBranch').val(data.branch);
                    $('#saleRowDate').val(data.log_date);
                    $('#saleRowTime').val(data.oras);
                    $('#saleRowFuelType').val(data.fuel_type);
                    $('#saleRowVolume').val(data.volume_sold);
                    $('#saleRowPrice').val(data.fuel_price);
                    $('#saleRowTotal').val(parseFloat(data.total_amount).toFixed(2));

                    $('#saleRowEditModal').modal('show');
                },
                error: function () {
                    alert('Error loading sale row.');
                }
            });
        });

        // Auto-recompute total when editing volume/price
        $(document).on('input', '#saleRowVolume, #saleRowPrice', function () {
            const vol = parseFloat($('#saleRowVolume').val()) || 0;
            const price = parseFloat($('#saleRowPrice').val()) || 0;
            $('#saleRowTotal').val((vol * price).toFixed(2));
        });

        // Save per-row edit
        $(document).on('click', '#saveSaleRowBtn', function () {
            const idRow = $('#saleRowId').val();
            const branch = $('#saleRowBranch').val();
            const volume = parseFloat($('#saleRowVolume').val()) || 0;
            const price = parseFloat($('#saleRowPrice').val()) || 0;

            if (!idRow) {
                alert('Missing record id.');
                return;
            }
            if (volume <= 0 || price <= 0) {
                alert('Please enter valid volume and price.');
                return;
            }

            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: {
                    gastblsales: 'DATE_DETAILS_UPDATE_ROW',
                    id: idRow,
                    volume_sold: volume,
                    fuel_price: price
                },
                success: function (resp) {
                    alert(resp);
                    $('#saleRowEditModal').modal('hide');

                    // Refresh transactions and inventory if branch inventory is open
                    if (branch && currentBranch === branch) {
                        fetchTransactions(branch);
                        loadInventoryChart(branch);
                    }

                    // Refresh Sales Details modal if open
                    if ($('#dateDetailsModal').hasClass('show')) {
                        const dateBtn = $('.date-details-btn').first();
                        if (dateBtn.length) {
                            dateBtn.trigger('click');
                        }
                    }

                    // Refresh main sales table
                    fetchUser();
                },
                error: function () {
                    alert('Error saving sale row.');
                }
            });
        });

        // Per-row delete (Sales Details modal only)
        $(document).on('click', '.sale-delete-btn', function () {
            const rowId = $(this).data('id');
            const saleBranch = $(this).data('branch');
            
            if (!rowId) {
                alert('Invalid record selected.');
                return;
            }
            if (!confirm('Are you sure you want to delete this sale row? This will restore the fuel to inventory and delete the transaction.')) {
                return;
            }

            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: {
                    gastblsales: 'DATE_DETAILS_DELETE_ROW',
                    id: rowId
                },
                success: function (resp) {
                    alert(resp);
                    
                    // Refresh transactions and inventory if branch inventory is open
                    if (saleBranch && currentBranch === saleBranch) {
                        fetchTransactions(saleBranch);
                        loadInventoryChart(saleBranch);
                    }
                    
                    // Refresh Sales Details modal if open
                    if ($('#dateDetailsModal').hasClass('show')) {
                        const dateBtn = $('.date-details-btn').first();
                        if (dateBtn.length) {
                            dateBtn.trigger('click');
                        }
                    }
                    
                    // Refresh main sales table
                    fetchUser();
                },
                error: function () {
                    alert('Error deleting sale row.');
                }
            });
        });

        // btnedit handler
        $(document).on('click', '#btnedit', function() {
            if (!id) { 
                alert("Please select a record to edit."); 
                return; 
            }

            $.ajax({
                url: 'adminfunction.php',
                method: 'POST',
                data: { gastblsales: "SELECT", id: id },
                dataType: 'json',
                success: function(data) {
                    $('#addsales').modal('show');
                    $('.modal-title').text("Update Sale");
                    $('#submit').val("UPDATE");

                    // Fill fuel fields
                    $('#branch').val(data.branch);
                    $('#date').val(data.log_date);
                    $('#dvs').val(data.dvs);
                    $('#pvs').val(data.pvs);
                    $('#uvs').val(data.uvs);
                    
                    // Show/hide fuel inputs based on values with smooth transitions
                    // Diesel
                    if (data.dvs && parseFloat(data.dvs) > 0) {
                        $('#dieselButtonContainer').css({
                            'display': 'none',
                            'opacity': '0',
                            'transform': 'scale(0.9)'
                        });
                        $('#dieselInputContainer').css({
                            'display': 'flex',
                            'opacity': '1',
                            'transform': 'scale(1) translateY(0)'
                        });
                    } else {
                        $('#dieselButtonContainer').css({
                            'display': 'flex',
                            'opacity': '1',
                            'transform': 'scale(1)'
                        });
                        $('#dieselInputContainer').css({
                            'display': 'none',
                            'opacity': '0',
                            'transform': 'scale(0.95) translateY(-10px)'
                        });
                    }
                    
                    // Premium
                    if (data.pvs && parseFloat(data.pvs) > 0) {
                        $('#premiumButtonContainer').css({
                            'display': 'none',
                            'opacity': '0',
                            'transform': 'scale(0.9)'
                        });
                        $('#premiumInputContainer').css({
                            'display': 'flex',
                            'opacity': '1',
                            'transform': 'scale(1) translateY(0)'
                        });
                    } else {
                        $('#premiumButtonContainer').css({
                            'display': 'flex',
                            'opacity': '1',
                            'transform': 'scale(1)'
                        });
                        $('#premiumInputContainer').css({
                            'display': 'none',
                            'opacity': '0',
                            'transform': 'scale(0.95) translateY(-10px)'
                        });
                    }
                    
                    // Unleaded
                    if (data.uvs && parseFloat(data.uvs) > 0) {
                        $('#unleadedButtonContainer').css({
                            'display': 'none',
                            'opacity': '0',
                            'transform': 'scale(0.9)'
                        });
                        $('#unleadedInputContainer').css({
                            'display': 'flex',
                            'opacity': '1',
                            'transform': 'scale(1) translateY(0)'
                        });
                    } else {
                        $('#unleadedButtonContainer').css({
                            'display': 'flex',
                            'opacity': '1',
                            'transform': 'scale(1)'
                        });
                        $('#unleadedInputContainer').css({
                            'display': 'none',
                            'opacity': '0',
                            'transform': 'scale(0.95) translateY(-10px)'
                        });
                    }
                    
                    // Fetch and use current prices for the branch (when editing, use current prices)
                    fetchBranchPrices(data.branch);
                    
                    // Update stock levels and field states for editing
                    if (window.currentStock) {
                        updateFuelFieldStates();
                    }

                    // Clear existing lubricant rows
                    $('#fuelContainer').empty();

                    // Load products for the selected branch before populating rows
                    loadProductsByBranch(data.branch);

                    // Populate all lubricant sales for this branch and date
                    if (data.lubricants && data.lubricants.length > 0) {
                        data.lubricants.forEach(function(lub, index) {
                            let rowId = `lub_${index}`;
                            let newRow = `
                                <div class="lubricant_row d-flex gap-2 mb-2 align-items-center" id="row_${rowId}">
                                    <div class="product-search-container flex-grow-2">
                                        <input type="text" name="pname[]" class="form-control product-search-input" 
                                            value="${lub.pname}" placeholder="Type to search products..." autocomplete="off">
                                        <div class="product-dropdown"></div>
                                    </div>
                                    <input type="number" name="pqty[]" class="form-control flex-grow-1" 
                                        value="${lub.qty_in}" min="0" placeholder="Enter Quantity">
                                    <input type="number" name="pprice[]" class="form-control flex-grow-1" 
                                        value="${lub.pprice}" placeholder="Enter Amount ₱" min="0" step="0.01">
                                    <button type="button" class="btn btn-danger btn-sm removerow">×</button>
                                </div>`;
                            $('#fuelContainer').append(newRow);
                        });
                    } else {
                        $("#addrow").trigger('click'); // add empty row if none
                    }

                    // --- Expenses ---
                    $('#expensesContainer').empty();
                    if (data.expenses && data.expenses.length > 0) {
                        data.expenses.forEach(function(exp, index){
                            let rowId = `exp_${index}`;
                            let newRow = `
                                <div class="expense_row d-flex gap-2 align-items-center mb-2" id="row_${rowId}">
                                    <select name="expense_type[]" class="form-select expense_type flex-grow-1">
                                        <option value="" ${!exp.type ? "selected" : ""}>Select Expense</option>
                                        <option value="Electricity Expense" ${exp.type=="Electricity Expense" ? "selected" : ""}>Electricity Expense</option>
                                        <option value="Water Expense" ${exp.type=="Water Expense" ? "selected" : ""}>Water Expense</option>
                                        <option value="Salary Expense" ${exp.type=="Salary Expense" ? "selected" : ""}>Salary Expense</option>
                                        <option value="Others" ${exp.type=="Others" ? "selected" : ""}>Others</option>
                                    </select>
                                    <input type="number" name="expense_amount[]" class="form-control flex-grow-1 expense_amount" min="0" step="0.01" 
                                        value="${exp.amount}">
                                    <input type="text" name="other_expense[]" class="form-control other_expense" 
                                        style="display:${exp.type=='Others' ? 'block':'none'}; width:150px;" 
                                        placeholder="Specify pls..." value="${exp.desc || ''}">
                                    <button type="button" class="btn btn-danger btn-sm remove_expense">×</button>
                                </div>`;
                            $('#expensesContainer').append(newRow);
                        });
                    } else {
                        $("#add_expense").trigger('click'); // optional: add empty expense row if none
                    }
                }
            });
        });






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
        
        // Legacy function for backward compatibility (not used in new implementation)
        function populateProducts(selectElement) {
            $.ajax({
                url: "adminfunction.php",
                method: "POST",
                data: { gastblsales: "GET_LUB_PRODUCTS" },
                dataType: "json",
                success: function(products) {
                    let optionsHtml = '<option value="" disabled selected>Select product...</option>';
                    products.forEach(function(p){
                        optionsHtml += `<option value="${p}">${p}</option>`;
                    });
                    selectElement.html(optionsHtml);
                }
            });
        }

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

        // Add Row Button
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

        // Remove row
        $(document).on("click", ".removerow", function () {
            $(this).closest('.lubricant_row').remove();
        });










        var selectedId = null; 

        // Show modal when Add button clicked
        $('#addbtn2').on('click', function() {
            // Reset form state
            $('#lubStockForm')[0].reset(); // clear fields
            $('#lubStockForm input[name="inv_id"]').val(""); // clear id
            $('#lubStockForm input[name="action"]').val("SAVELUB");
            $('.modal-title').text("Add Lubricant Product");
            $('#actionLub').text("Save");
            
            // Clear global selectedId variable
            selectedId = null;
            
            // Set today's date as default
            const today = new Date().toISOString().split('T')[0];
            $('#lubStockForm input[name="date"]').val(today);
            
            $('#addinventorylub').modal('show');
        });

        // Reload products when branch changes
        $('#addinventorylub select[name="branch"]').on('change', function() {
            let branch = $(this).val();
            loadProducts(branch);
        });

        // Load products from DB
        function loadProducts(branch) {
            $.ajax({
                url: 'adminfunction.php',
                type: 'POST',
                data: { gastblsales: 'GET_LUB_PRODUCTS', branch: branch },
                dataType: 'json',
                success: function(products) {
                    let options = '';
                    products.forEach(function(product) {
                        const name = product.product_name || product; // backward compatible
                        options += '<option value="' + name + '">';
                    });
                    $('#productList').html(options);
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching products: ", error);
                }
            });
        }

        // Save/Update button click
        $('#actionLub').click(function() {
            let action = $('#lubStockForm input[name="action"]').val();
            let branch = $('#lubStockForm select[name="branch"]').val();
            let pname  = $('#lubStockForm input[name="pname"]').val();
            let qty    = $('#lubStockForm input[name="quantity"]').val();
            let date   = $('#lubStockForm input[name="date"]').val();
            let price  = $('#lubStockForm input[name="price"]').val();

            // Build payload based on action to match backend expectations
            let formData = {
                gastblsales: action,
                id: $('#lubStockForm input[name="inv_id"]').val(),
                branch: branch,
                pname: pname
            };

            if (action === 'SAVELUB') {
                formData.quantity = qty; // backend expects 'quantity' for SAVELUB
                formData.date = date;
                formData.price = price;
            } else if (action === 'UPDATELUB') {
                formData.qty_in = qty; // backend expects 'qty_in' for UPDATELUB
                formData.qty_out = 0;
            }

            $.ajax({
                url: 'adminfunction.php',
                type: 'POST',
                data: formData,
                success: function(data) {
                    alert(data);
                    $('#addinventorylub').modal('hide');
                    fetchLubInventory(branch);
                    // Also refresh Daily Transactions to reflect QTY IN entries
                    fetchDailyTrans(branch);
                }
            });
        });

        // CheckBox Listener to select a record (for lubricant section)
        $(document).on('click', '.check_box', function() {
            // Toggle the current checkbox
            $(this).prop('checked', !$(this).prop('checked'));
            
            // Uncheck all other checkboxes
            $('.check_box').not(this).prop('checked', false);
            
            // Set or clear the selectedId variable
            selectedId = $(this).is(':checked') ? $(this).val() : null;
        });

        // Edit Button Listener
        $(document).on('click', '#btnedit2', function() {
            if (!selectedId) {
                alert("Please select a record to edit.");
                return;
            }

            $.ajax({
                url: "adminfunction.php",
                method: "POST",
                data: { id: selectedId, gastblsales: "SELECTLUB" },
                dataType: "json",
                success: function(data) {
                    $('#addinventorylub').modal('show');
                    $('.modal-title').text("Update Lubricant Stock");
                    $('#actionLub').text("Update");

                    // set form action & id
                    $('#lubStockForm input[name="action"]').val("UPDATELUB");
                    $('#lubStockForm input[name="inv_id"]').val(data.inv_id);

                    // fill form fields correctly
                    $('#lubStockForm select[name="branch"]').val(data.branch);
                    $('#lubStockForm input[name="pname"]').val(data.pname);
                    $('#lubStockForm input[name="quantity"]').val(data.qty_in);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", error);
                }
            });
        });

        // Delete Button Listener
        $(document).on('click', '#btndelete2', function() {
            if (!selectedId) {
                alert("Please select a record to delete.");
                return;
            }
            if (confirm("Are you sure you want to delete this record?")) {
                $.ajax({
                    url: "adminfunction.php",
                    method: "POST",
                    data: { id: selectedId, gastblsales: "DELETELUB" },
                    success: function(data) {
                        alert(data);
                        fetchLubInventory($('#branch').val());
                    }
                });
            }
        });

        

        

        
    });
</script>
