
<!DOCTYPE html>
<html>
    <head>
        
        <meta charset="utf-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Bro Page </title>
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
            /* Search Container2 */
            .header-search2 {
                display: flex;
                align-items: center;
                width: 30%;
                
               
                
                
                padding: 5px 10px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.2);
                backdrop-filter: blur(6px);
                transition: all 0.3s ease;
            }
            #search_product::placeholder {
                color: #d2c9c9ff; 
            }

            /* Input */
            .header-search2 {
                
                font-family: 'Segoe UI', sans-serif;
                position: relative;
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

            

            

           
            /* Responsive */
            @media (max-width: 768px) {
                .header-search {
                    order: 99; /* push it below everything */
                    width: 100%; /* take full width */
                    margin-top: 10px;
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
            #tool {
                filter: drop-shadow(5px 5px 5px rgba(0, 0, 0, 0.2));
                width: 60px;
                margin-top: 0;
                position: absolute;
                transition: 0.3s;
                left: 13px;
                top: 4px;
            }

            #tool:hover {
                transform: scale(1.2);
                filter: drop-shadow(5px 5px 5px rgba(0, 0, 0, 0.5));
            }

            .crud-nav {
                position: relative;
                width: 90px;
                height: 0px;
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

            .crud {
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

            .crud:hover {
                transform: scale(1.15) translateY(-4px);
                background-image: linear-gradient(145deg, #7a2b2b, #672222);
                box-shadow: 0 8px 18px rgba(0, 0, 0, 0.35);
            }

            .btncrud {
                position: absolute;
                left: 10px;
                top: 15%;
            }

            #closenavbot {
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




        #stockRowsContainer .row:first-child .btn-danger {
            align-self: flex-start !important;
            margin-top: 32px; /* adjust to align with input label */
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




             /* === Tools & CRUD === */
            #tool,#tool2 {
                filter: drop-shadow(5px 5px 5px rgba(0, 0, 0, 0.2));
                width: 60px;
                margin-top: 0;
                position: absolute;
                transition: 0.3s;
                left: 13px;
                top: 4px;
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









            
            /* Cash Breakdown Table */
            .table-cash {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
                border-radius: 12px;
                overflow: hidden;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                box-shadow: 0 6px 15px rgba(0,0,0,0.1);
                margin-bottom: 1.5rem;
            }

            .table-cash thead th {
                color: #fff;
                font-weight: 700;
                font-size: 1rem;
                padding: 12px 15px;
                text-align: center;
            }

            .table-cash tbody td {
                text-align: center;
                padding: 10px 15px;
                font-size: 0.95rem;
            }


            .table-cash tbody tr:hover {
                background-color: #ccc;
                transition: 0.2s;
            }

            .table-cash input.cash_qty {
                width: 100%;
                padding: 5px 8px;
                text-align: center;
                border-radius: 6px;
                border: 1px solid #ccc;
                font-weight: 500;
            }

            .table-cash .cash_total {
                color: #672222;
                font-size: 1rem;
            }

            .table-cash tfoot td {
                background-color: #e3e1e1ff;
                color: #672222;
                font-size: 1.1rem;
                padding: 12px 12px;
                
            }

            /* Searchable Product Input Styles */
            .product-search-container {
                position: relative;
                width: 100%;
            }

            .product-search-input {
                width: 100%;
                padding: 8px 12px;
                border: 1px solid #ddd;
                border-radius: 6px;
                font-size: 14px;
                outline: none;
                transition: border-color 0.3s;
            }

            .product-search-input:focus {
                border-color: #672222;
                box-shadow: 0 0 0 0.25rem rgba(103, 34, 34, 0.25);
            }

            .product-dropdown {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                border: 1px solid #ddd;
                border-top: none;
                border-radius: 0 0 6px 6px;
                max-height: 200px;
                overflow-y: auto;
                z-index: 1000;
                display: none;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }

            .product-dropdown-item {
                padding: 10px 12px;
                cursor: pointer;
                border-bottom: 1px solid #f0f0f0;
                transition: background-color 0.2s;
            }

            .product-dropdown-item:hover {
                background-color: #f8f9fa;
            }

            .product-dropdown-item:last-child {
                border-bottom: none;
            }

            .add-new-product {
                background-color: #e3f2fd;
                color: #1976d2;
                font-weight: 500;
                border-left: 3px solid #1976d2;
            }

            .add-new-product:hover {
                background-color: #bbdefb;
            }

            /* Inventory search dropdown styles */
            #data_table .product-search-container {
                position: relative;
                width: 100%;
            }

            #data_table .product-search-input {
                width: 100%;
                padding: 8px 12px;
                border: 1px solid #ddd;
                border-radius: 6px;
                font-size: 14px;
                outline: none;
                transition: border-color 0.3s;
                background: rgba(255, 255, 255, 0.15) !important;
                color: white !important;
                border: none !important;
            }

            #data_table .product-search-input:focus {
                border-color: #672222;
                box-shadow: 0 0 0 0.25rem rgba(103, 34, 34, 0.25);
            }

            #data_table .product-search-input::placeholder {
                color: #d2c9c9ff;
            }

            #data_table .product-dropdown {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                border: 1px solid #ddd;
                border-top: none;
                border-radius: 0 0 6px 6px;
                max-height: 200px;
                overflow-y: auto;
                z-index: 1000;
                display: none;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }

            #data_table .product-dropdown-item {
                padding: 10px 12px;
                cursor: pointer;
                border-bottom: 1px solid #f0f0f0;
                transition: background-color 0.2s;
                color: #333;
            }

            #data_table .product-dropdown-item:hover {
                background-color: #f8f9fa;
            }

            #data_table .product-dropdown-item:last-child {
                border-bottom: none;
            }

            #data_table .add-new-product {
                background-color: #e3f2fd;
                color: #1976d2;
                font-weight: 500;
                border-left: 3px solid #1976d2;
            }

            #data_table .add-new-product:hover {
                background-color: #bbdefb;
            }
             /* floating add button */
            #stockbtn {
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
            #stockbtn:hover,#addbtn:hover {
                filter: drop-shadow(6px 10px 14px rgba(0,0,0,0.4));
                box-shadow: 0 14px 28px rgba(0,0,0,0.45), inset 0 0 0 2px rgba(255,255,255,0.35);
                transform: scale(1.08);
                opacity: 1;
            }
            #stockbtn:active,#addbtn:active {
                transform: scale(0.98);
                box-shadow: 0 6px 14px rgba(0,0,0,0.35), inset 0 0 0 2px rgba(255,255,255,0.2);
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
                <h1 class="h2 h3-md m-0" style="margin:0;">BRO'S INASAL (ADMIN DASHBOARD)</h1>
                
            </div>
          

            <img src="ico.png" id="sidenav" height="auto" style="position: absolute; right: 2%;"  class="img-fluid" alt="logo"/>
        </header>
    
      

        <div id="mySidenav" class="sidenav">
            <button type="button" id="closeBtn" style="background-color:white; color: white; margin-left: 25px;" class="btn-close" aria-label="Close"></button>
        </br>
            <a href="#" id="sales">Sales Report</a>
            <a href="#" id="inventory">Inventory</a>
            <a href="#" id="stock">Stock Movement</a>
            

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
        
        
        <nav class="navbar crud-nav" id="navbot" style="display: none;">
            
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


         <nav class="navbar2 crud-nav-2" style="height: 25px;" id="navbot2">
            
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
         



      




        <div style=" margin-top: 15px; margin-left:140px; margin-right: 50px;">
            
            <div id="data_table" style="flex: 1; margin: 5px; overflow-y: auto; box-shadow: 0 8px 24px rgba(0,0,0,0.2); border-radius:10px; border:none; display:none;">
                <!-- inventory will display inside of this tag -->
            </div>

            
            <div id="data_table3" style="flex: 1; margin: 5px; overflow-y: auto; box-shadow: 0 8px 24px rgba(0,0,0,0.2); border-radius:10px; border:none;">
                <!-- daily sales will display inside of this tag -->
            </div>
        </div>
        <div id="data_table2" style="flex: 1; margin: 5px; overflow-y: auto; box-shadow: 0 8px 24px rgba(0,0,0,0.2); border-radius:10px; border:none; display: none;">
                <!-- stock movements will display inside of this tag -->
        </div>

        <!-- Daily Sales Details Modal -->
        <div class="modal fade" id="broDateDetailsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="broDateDetailsLabel">Sales Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="broDateDetailsBody">
                        <p class="text-muted mb-0">Select a report to view details.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Stock Movement Modal (Dynamic Rows) -->
        <div class="modal fade" data-bs-backdrop="static" id="stockModal" tabindex="-1" style="z-index: 100000;">
            <div class="modal-dialog modal-dialog-scrollable modal-fullscreen">
                <div class="modal-content shadow-lg rounded-3">
                    <div class="modal-header border-0">
                        <img src="tmclogo.png" width="35" height="auto" class="img-fluid me-2" alt="Company Logo">
                        <h5 id="stockModalTitle" class="modal-title fw-bold text-uppercase flex-grow-1">Manage Stock</h5>
                       
                           
                                
                                DATE : <input style="margin-left:15px; margin-right:20px; width: 30%;" type="date" id="batch_movement_date" class="form-control">
                            
                      
                        <button style="background-color: white;" type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        

                        <h6 class="fw-bold mb-2">Items</h6>
                        <div id="stockRowsContainer"></div>
                        <button type="button" id="addStockRow" 
                                class="btn btn-outline-primary w-100 fw-semibold mt-2"
                                style="background-color:#672222; border:none; color:#fff;">
                            ✙ Add Item
                        </button>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="saveBatchStock" class="btn px-4" style="background-color:#672222; border:none; color:#fff;">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Add Product Modal -->
        <div class="modal fade" data-bs-backdrop="static" id="quickAddProductModal" tabindex="-1" style="z-index: 30000000;">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content shadow-lg rounded-3">
                    <div class="modal-header border-0">
                        <img src="tmclogo.png" width="35" height="auto" class="img-fluid me-2" alt="Company Logo">
                        <h5 class="modal-title fw-bold text-uppercase flex-grow-1">Add New Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="qa_label" class="form-label fw-semibold">Category / Label</label>
                            <select class="form-select" id="qa_label" required>
                                <option value="" selected disabled>Select label...</option>
                                <option value="SUPPLIES">SUPPLIES</option>
                                <option value="FROZEN FOODS">FROZEN FOODS</option>
                                <option value="MILKSHAKE/FLOAT/COFFEE">MILKSHAKE/FLOAT/COFFEE</option>
                                <option value="CONDIMENTS/DRINKS">CONDIMENTS/DRINKS</option>
                                <option value="HALO-HALO">HALO-HALO</option>
                                <option value="FROSTY ICE CREAM">FROSTY ICE CREAM</option>
                                <option value="OTHERS">OTHERS</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="qa_product_name" class="form-label fw-semibold">Product Name</label>
                            <input type="text" class="form-control" id="qa_product_name" placeholder="Product Name" required>
                        </div>
                        <div class="mb-3">
                            <label for="qa_classification" class="form-label fw-semibold">Classification</label>
                            <input type="text" class="form-control" id="qa_classification" placeholder="e.g., Pack, Bottle">
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="quick_add_submit" class="btn px-4" style="background-color:#672222; border:none; color:#fff;">Add Product</button>
                    </div>
                </div>
            </div>
        </div>


        <form autocomplete="off" method="POST" action="adminbro.php" style="z-index: 30000000;">
            <div class="modal fade" data-bs-backdrop="static" id="addProductModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-scrollable modal-lg">
                    <div class="modal-content shadow-lg rounded-3">

                        <!-- Header -->
                        <div class="modal-header border-0">
                            <img src="tmclogo.png" width="35" height="auto" class="img-fluid me-2" id="logo" style="cursor:pointer;" alt="Logo">
                            <h5 class="modal-title fw-bold text-uppercase flex-grow-1">Add New Product</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body">
                            <!-- Hidden fields -->
                            <input type="hidden" id="product_brotbl" name="brotbl" value="">
                            <input type="hidden" id="product_id" name="id" value="">

                            <!-- Category / Label -->
                            <div class="mb-3">
                                <label for="label" class="form-label fw-semibold">Category / Label</label>
                                <select class="form-select" name="label" id="label" required>
                                    <option value="" selected disabled>Select label...</option>
                                    <option value="SUPPLIES">SUPPLIES</option>
                                    <option value="FROZEN FOODS">FROZEN FOODS</option>
                                    <option value="MILKSHAKE/FLOAT/COFFEE">MILKSHAKE/FLOAT/COFFEE</option>
                                    <option value="CONDIMENTS/DRINKS">CONDIMENTS/DRINKS</option>
                                    <option value="HALO-HALO">HALO-HALO</option>
                                    <option value="FROSTY ICE CREAM">FROSTY ICE CREAM</option>
                                    <option value="OTHERS">OTHERS</option>
                                </select>
                            </div>

                            <!-- Product Name -->
                            <div class="mb-3">
                                <label for="product_name" class="form-label fw-semibold">Product Name</label>
                                <input type="text" class="form-control" name="product_name" id="product_name" placeholder="Product Name" required>
                            </div>

                            <!-- Classification -->
                            <div class="mb-3">
                                <label for="classification" class="form-label fw-semibold">Classification</label>
                                <input type="text" class="form-control" name="classification" id="classification" placeholder="e.g., Pack, Bottle">
                            </div>

                        </div>

                        <!-- Footer -->
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border:none;">Cancel</button>
                            <button type="button" id="action" class="btn px-4" style="background-color:#672222; border:none; color:#fff;">Save Product</button>
                        </div>

                    </div>
                </div>
            </div>
        </form>



        <form autocomplete="off" id="dailyReportForm" method="POST" action="adminbro.php">
            <div class="modal fade" data-bs-backdrop="static" id="addDailyReportModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-scrollable modal-lg">
                    <div class="modal-content shadow-lg rounded-3">

                        <!-- Header -->
                        <div class="modal-header border-0">
                        <img src="tmclogo.png" width="35" class="img-fluid me-2" alt="Logo">
                        <h5 class="modal-title fw-bold text-uppercase flex-grow-1">Add Daily Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body">
                        <input type="hidden" id="daily_brotbl" name="brotbl" value="SAVE_DAILY">
                        <input type="hidden" id="daily_id" name="id" value="">

                        <!-- Report Date -->
                        <div class="mb-3">
                            <label for="report_date" class="form-label fw-semibold">Report Date</label>
                            <input type="date" class="form-control" name="report_date" id="report_date" required>
                        </div>

                         <!-- Cash Breakdown Section -->
                        <hr class="my-4" style="border:0; height:3px; background:#672222; opacity:1;">
                        <h5 class="fw-bold text-uppercase mb-3">Cash Breakdown</h5>
                        <table class="table-cash">
                            <thead class="table-dark" style="background: linear-gradient(90deg, #672222, #8c2f2f);">
                            <tr>
                                <th>CASH (₱)</th>
                                <th>Quantity</th>
                                <th>Total (₱)</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>1000</td>
                                <td><input type="number" name="cash_1000" class="form-control cash_qty" data-value="1000" min="0" placeholder="0"></td>
                                <td class="cash_total">0</td>
                            </tr>
                            <tr>
                                <td>500</td>
                                <td><input type="number" name="cash_500" class="form-control cash_qty" data-value="500" min="0" placeholder="0" ></td>
                                <td class="cash_total">0</td>
                            </tr>
                            <tr>
                                <td>200</td>
                                <td><input type="number" name="cash_200" class="form-control cash_qty" data-value="200" min="0" placeholder="0" ></td>
                                <td class="cash_total">0</td>
                            </tr>
                            <tr>
                                <td>100</td>
                                <td><input type="number" name="cash_100" class="form-control cash_qty" data-value="100" min="0" placeholder="0" ></td>
                                <td class="cash_total">0</td>
                            </tr>
                            <tr>
                                <td>50</td>
                                <td><input type="number" name="cash_50" class="form-control cash_qty" data-value="50" min="0" placeholder="0" ></td>
                                <td class="cash_total">0</td>
                            </tr>
                            <tr>
                                <td>20</td>
                                <td><input type="number" name="cash_20" class="form-control cash_qty" data-value="20" min="0" placeholder="0" ></td>
                                <td class="cash_total">0</td>
                            </tr>
                            <tr>
                                <td>10</td>
                                <td><input type="number" name="cash_10" class="form-control cash_qty" data-value="10" min="0" placeholder="0" ></td>
                                <td class="cash_total">0</td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td><input type="number" name="cash_5" class="form-control cash_qty" data-value="5" min="0" placeholder="0" ></td>
                                <td class="cash_total">0</td>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td><input type="number" name="cash_1" class="form-control cash_qty" data-value="1" min="0" placeholder="0" ></td>
                                <td class="cash_total">0</td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr> <td colspan="4" style="padding-top:15px; background:none;"></td></tr>
                            <tr >
                                <td colspan="2">Total Cash (₱)</td>
                                <td style="align-items: center; padding:0;">
                                    <div style="display: flex; justify-content: center; align-items: center;">
                                        <input style="width:150px; text-align:center; text-decoration: underline;" class="form-control" type="number"  id="total_cash"step="0.01" placeholder="0.00" value="0.00"  readonly>
                                    </div>
                                </td>
                            </tr>
                            </tfoot>
                        </table>


                        <!-- Cash & Sales Section -->
                        <hr class="my-4" style="border:0; height:3px; background:#672222; opacity:1;">
                        <h5 class="fw-bold text-uppercase mb-3">Cash & Sales</h5>

                        <table class="table-cash">
                            <thead style="background: linear-gradient(90deg, #672222, #8c2f2f);">
                            <tr>
                                <th>Type</th>
                                <th>Amount (₱)</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td >Cash on Counter</td>
                                <td><input type="number" name="cash_on_counter" class="form-control cash_sales_input" id="cash_on_counter" step="0.01" placeholder="0.00" value="0.00" readonly></td>
                            </tr>
                            <tr>
                                <td>Cash In</td>
                                <td><input type="number" name="cash_in" class="form-control cash_sales_input" id="cash_in" step="0.01" placeholder="0.00" value="0.00"></td>
                            </tr>
                            <tr>
                                <td>GCash Sales</td>
                                <td><input type="number" name="gcash_sales" class="form-control cash_sales_input" id="gcash_sales" step="0.01" placeholder="0.00" value="0.00"></td>
                            </tr>
                            <tr>
                                <td>Credit Sales</td>
                                <td><input type="number" name="credit_sales" class="form-control cash_sales_input" id="credit_sales" step="0.01" placeholder="0.00" value="0.00"></td>
                            </tr>
                            
                            <tr>
                                <td>Total Sales(POS)</td>
                                <td><input type="number" name="Totalsales" class="form-control cash_sales_input" id="Totalsales" step="0.01" placeholder="0.00" value="0.00"></td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr> <td colspan="4" style="padding-top:15px; background:none;"></td></tr>
                            <tr style="border-collapse: collapse;">
                                <td>Total Cash & Sales (₱)</td>
                                <td id="total_cash_sales" style="text-align:right; text-decoration: underline;">0.00</td>
                            </tr>
                            </tfoot>
                        </table>

                        <!-- Expenses Section -->
                        <hr class="my-4" style="border:0; height:3px; background:#672222; opacity:1;">
                        <h5 class="fw-bold text-uppercase mb-3">Expenses</h5>

                        <table class="table-cash" id="expenses_table">
                            <thead style="background: linear-gradient(90deg, #672222, #8c2f2f);">
                            <tr>
                                <th>Expense Type</th>
                                <th id="wide" colspan="2">Amount (₱)</th>
                                <th id="hid">Specification</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="expense_row">
                                <td>
                                <select class="form-select expense_type" name="expense_type[]">
                                    <option value="">Select Expense</option>
                                    <option value="Electricity Expense">Electricity Expense</option>
                                    <option value="Water Expense">Water Expense</option>
                                    <option value="Salary Expense">Salary Expense</option>
                                    <option value="Others">Others</option>
                                </select>
                                </td>
                                <td><input type="number" name="expense_amount[]" class="form-control expense_amount" min="0" step="0.01" placeholder="0.00" value="0.00"></td>
                                <td><input type="text" name="other_expense[]" class="form-control other_expense" style="display:none;" placeholder="Specify pls..."></td>
                                <td><button type="button" class="btn btn-danger btn-sm remove_expense">×</button></td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr> <td colspan="4" style="padding-top:15px; background:none;"></td></tr>
                            <tr >
                                <td colspan="4" >Total Expenses (₱)</td>
                                <td colspan="1" id="total_expenses" style="text-decoration: underline;">0.00</td>
                            </tr>
                            </tfoot>
                        </table>
                        <button type="button" class="btn btn-outline-dark mt-2" id="add_expense">+ Add Expense</button>

                        <!-- Other Sales (Dynamic Rows) -->
                        <hr class="my-4" style="border:0; height:3px; background:#672222; opacity:1;">
                        <h5 class="fw-bold text-uppercase mb-3">Other Sales</h5>
                        <table class="table-cash" id="other_sales_table">
                            <thead style="background: linear-gradient(90deg, #672222, #8c2f2f);">
                                <tr>
                                    <th>Product</th>
                                    <th>Unit Price (₱)</th>
                                    <th>Quantity</th>
                                    
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- rows inserted dynamically -->
                            </tbody>
                            <tfoot>
                                <tr> <td colspan="5" style="padding-top:15px; background:none;"></td></tr>
                                <tr>
                                    <td colspan="3">Total Other Sales (₱)</td>
                                    <td colspan="2" id="total_other_sales" style="text-decoration: underline; text-align:right;">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                        <button type="button" class="btn btn-outline-dark mt-2" id="add_other_sale">+ Add Other Sale</button>

                       

                        </div>

                        <!-- Footer -->
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="action2" class="btn px-4" style="background-color:#672222; color:#fff;">Save Report</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>











            




    
        








        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>

<script>
    $(document).ready(function(){
        
        fetchDailyReport();
        $('#expense_type').on('change', function () {
            if ($(this).val() === "Others") {
                $('#other_expense_div').slideDown(); // smooth show
            } else {
                $('#other_expense_div').slideUp();
                $('#other_expense').val(''); // clear value if hidden
            }
        });

        // View daily sales details (opens modal)
        $(document).on('click', '.date-details-btn', function(){
            const id = $(this).data('id');
            const date = $(this).data('date') || '';
            if (!id) { alert('Unable to load details: missing id.'); return; }
            $('#broDateDetailsLabel').text('Sales Details - ' + date);
            $('#broDateDetailsBody').html('<p class="text-center my-3 text-muted">Loading...</p>');
            $('#broDateDetailsModal').modal('show');
            $.ajax({
                url: 'bros_admin_api.php',
                method: 'POST',
                data: { brotbl: 'BRO_DATE_DETAILS', id: id },
                success: function(html){
                    $('#broDateDetailsBody').html(html);
                },
                error: function(){
                    $('#broDateDetailsBody').html('<p class="text-center text-danger mb-0">Unable to load details. Please try again.</p>');
                }
            });
        });

        
        $("#sales").click(function(){
            $("#navbot2").show();
            $("#navbot").hide();
            $("#data_table3").fadeIn(500);
            $("#data_table").hide();
            $("#data_table2").hide();
        });


        fetchInventory('SUPPLIES'); //load data
        // Load products for search functionality
        loadAllProducts();


        // When the label dropdown changes in bro inventory (scoped to inventory table)
        $(document).on('change', '#data_table #label_filter', function() {
            var selectedLabel = $(this).val();
            var searchTerm = $('#data_table #search_product').val();
            fetchInventory(selectedLabel, searchTerm);
            
        });

        // When the search button is clicked in bro inventory (scoped to inventory table)
        $(document).on('click', '#data_table #search_btn', function() {
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter2Sec();
            var searchTerm = $('#data_table #search_product').val();
            var selectedLabel = $('#data_table #label_filter').val();
            fetchInventory(selectedLabel, searchTerm);
        });
        
        // Allow Enter key to trigger search
        $(document).on('keypress', '#data_table #search_product', function(e) {
            if (e.which === 13) { // Enter key
                $(".loader-overlay").css("display", "flex");
                hideLoaderAfter2Sec();
                var searchTerm = $(this).val();
                var selectedLabel = $('#data_table #label_filter').val();
                fetchInventory(selectedLabel, searchTerm);
            }
        });

        // Search products and show dropdown for inventory search
        $(document).on('input', '#data_table .product-search-input', function() {
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

            // Build dropdown HTML
            let dropdownHTML = '';
            
            // Add matching products
            filteredProducts.forEach(product => {
                dropdownHTML += `<div class="product-dropdown-item" data-product="${product.product_name}">${product.product_name}</div>`;
            });

            // Add "Add new product" option if no exact match
            const exactMatch = allProducts.some(product => 
                product.product_name.toLowerCase() === searchTerm.toLowerCase()
            );
            
            if (!exactMatch && searchTerm.length > 0) {
                dropdownHTML += `<div class="product-dropdown-item add-new-product" data-action="add-new">+ Add "${searchTerm}" as new product</div>`;
            }

            dropdown.html(dropdownHTML).show();
        });

        // Handle dropdown item selection for inventory search
        $(document).on('click', '#data_table .product-dropdown-item', function() {
            const container = $(this).closest('.product-search-container');
            const input = container.find('.product-search-input');
            const dropdown = container.find('.product-dropdown');
            
            if ($(this).data('action') === 'add-new') {
                // Show quick add modal
                const productName = input.val();
                $('#qa_product_name').val(productName);
                $('#quickAddProductModal').modal('show');
                dropdown.hide();
            } else {
                // Select existing product - just fill the input, user needs to click search
                const productName = $(this).data('product');
                input.val(productName);
                dropdown.hide();
            }
        });

        // Hide dropdown when clicking outside for inventory search
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#data_table .product-search-container').length) {
                $('#data_table .product-dropdown').hide();
            }
        });
        // When the report type dropdown changes in bro daily reports (scoped to daily table)
        $(document).on('change', '#data_table3 #label_filter', function() {
            const report_type = $(this).val();
            const from_date = $('#data_table3 #from_date').val();
            const to_date = $('#data_table3 #to_date').val();
            $.ajax({
                url: 'bros_admin_api.php',
                method: 'POST',
                data: { brotbl: 'LOADBRODAILY', from_date: from_date, to_date: to_date, report_type: report_type },
                success: function(html){
                    $('#data_table3').html(html);
                }
            });
        });

        // When the report type dropdown changes in bro stock movement (scoped to stock table)
        $(document).on('change', '#data_table2 #label_filter', function() {
            const report_type = $(this).val();
            const from_date = $('#data_table2 #from_date').val();
            const to_date = $('#data_table2 #to_date').val();
            fetchStockMovement(from_date, to_date, report_type);
        });

        // When the filter button is clicked in bro stock movement (scoped to stock table)
        $(document).on('click', '#data_table2 #filterBtn', function() {
            const report_type = $('#data_table2 #label_filter').val();
            const from_date = $('#data_table2 #from_date').val();
            const to_date = $('#data_table2 #to_date').val();
            fetchStockMovement(from_date, to_date, report_type);
        });

        // When the reset button is clicked in bro stock movement (scoped to stock table)
        $(document).on('click', '#data_table2 #resetBtn', function() {
            $('#data_table2 #from_date').val('');
            $('#data_table2 #to_date').val('');
            $('#data_table2 #label_filter').val('daily');
            fetchStockMovement('', '', 'daily');
        });

        fetchStockMovement();
        $(".inventory-card").hide();


        let today = new Date().toISOString().split('T')[0];
        $("#movement_date").val(today);

        $('#addDailyReportModal').on('shown.bs.modal', function () {
            let today = new Date().toISOString().split('T')[0];
            $(this).find('#report_date').val(today); // scoped to this modal
        });
        

  

        function hideLoaderAfter2Sec() {
            setTimeout(function(){
                $(".loader-overlay").css("display", "none");
            }, 2000); // 2000ms = 2 seconds
        }


        function updateCashTotals() {
            let totalCash = 0;
            $('.cash_qty').each(function() {
                let qty = parseInt($(this).val()) || 0;
                let denom = parseInt($(this).data('value'));
                let total = qty * denom;
                $(this).closest('tr').find('.cash_total').text(total.toLocaleString());
                totalCash += total;
            });
            
            $('#total_cash').val(totalCash);
            $("#cash_on_counter").val(totalCash);
        }

        $('.cash_qty').on('input', updateCashTotals);

        //calculate total Cash & Sales
        function calculateCashSalesTotal() {
            let cashOnCounter = parseFloat($('#cash_on_counter').val()) || 0;
            let cashIn = parseFloat($('#cash_in').val()) || 0;
            let gcash = parseFloat($('#gcash_sales').val()) || 0;
            let credit = parseFloat($('#credit_sales').val()) || 0;
            let other  = parseFloat($('#other_sales').val()) || 0;
            // Keep old display for counter total
            let total = cashOnCounter + gcash + credit - cashIn; // display-only counter total

            $('#total_cash_sales').text(total.toFixed(2));
        }

        // Trigger calculation on input change
        $(document).on('input', '.cash_sales_input', function() {
            calculateCashSalesTotal();
        });

        /* ----------------- OTHER SALES (Dynamic) ----------------- */
        function recalcOtherSales() {
            let sum = 0;
            $('#other_sales_table tbody tr').each(function(){
                const price = parseFloat($(this).find('.os_price').val()) || 0;
                const qty   = parseInt($(this).find('.os_qty').val()) || 0;
                const amt   = price * qty;
                sum += amt;
            });
            $('#total_other_sales').text(sum.toFixed(2));
            $('#other_sales').val(sum.toFixed(2)).trigger('input'); // keep in sync for backend calc
        }

       function buildOtherSaleRow(name = "", price = "", qty = ""){
            return `
                <tr class="other_sale_row">
                    <td><input type="text" class="form-control os_name" placeholder="Enter Product" value="${name}"></td>
                    <td><input type="number" step="0.01" class="form-control os_price" placeholder="price" value="${price}"></td>
                    <td><input type="number" min="0" class="form-control os_qty" placeholder="quantity" value="${qty}"></td>
                    
                    <td><button type="button" class="btn btn-danger btn-sm remove_other_sale" >×</button></td>
                </tr>
            `;
        }

        $(document).on('click', '#add_other_sale', function(){
            $('#other_sales_table tbody').append(buildOtherSaleRow());
            recalcOtherSales();
        });

        $(document).on('click', '.remove_other_sale', function(){
            $(this).closest('tr').remove();
            recalcOtherSales();
        });

        $(document).on('input', '.os_price, .os_qty', function(){
            recalcOtherSales();
        });










        $(document).on("click", "#border tr", function(e) {
            // Ignore if clicking directly on the checkbox (so it doesn't double toggle)
            if (!$(e.target).is("input[type=checkbox]")) {
                var checkbox = $(this).find(".check_box"); // adjust selector if needed
                checkbox.prop("checked", !checkbox.prop("checked"));
                // Notify listeners so selectedId/id update
                checkbox.trigger('change');
            }
        });




        $(document).ready(function(){
            let today = new Date().toISOString().split('T')[0];
            $("#date").val(today);
        });

       


      
         
        $(document).on("click", "#stockbtn", function(){
            $('#stockModalTitle').text('Record Stock Movement');
            const today = new Date().toISOString().split('T')[0];
            $('#batch_movement_date').val(today);
            $('#stockRowsContainer').empty();
            $('#stockModal').modal('show');
            $.ajax({
                url: 'bros_admin_api.php',
                method: 'POST',
                data: { brotbl: 'GET_PRODUCTS' },
                dataType: 'json',
                success: function(products){
                    $('#stockRowsContainer').append(buildStockRowV2(products, true));
                }
            });
        });

        // Dynamic stock rows: load products and add row
        function buildStockRowV2(products, showLabels){
            return $(`
                <div class="stock_row row g-2 mb-2 w-100 align-items-center">
                    
                    <div class="col-md-3">
                        ${showLabels ? '<label class="form-label mb-1">Product</label>' : ''}
                        <div class="product-search-container">
                            <input type="text" class="form-control product-search-input stock_product" placeholder="Type to search products..." autocomplete="off">
                            <div class="product-dropdown"></div>
                        </div>
                    </div>

                   
                    <div class="col-md-1">
                        ${showLabels ? '<label class="form-label mb-1">Qty In</label>' : ''}
                        <input type="number" class="form-control qty_in" min="0" value="0">
                    </div>

                   
                    <div class="col-md-1">
                        ${showLabels ? '<label class="form-label mb-1">Qty Out</label>' : ''}
                        <input type="number" class="form-control qty_out" min="0" value="0">
                    </div>

                    
                    <div class="col-md-3">
                        ${showLabels ? '<label class="form-label mb-1">Unit Cost (₱)</label>' : ''}
                        <input type="number" class="form-control stock_cost" placeholder="Enter Amount ₱" step="0.01" min="0" value="0.00">
                    </div>

                    <div class="col-md-3">
                        ${showLabels ? '<label class="form-label mb-1">Shipping Fee (₱)</label>' : ''}
                        <input type="number" class="form-control ship_cost" placeholder="Enter Amount ₱" step="0.01" min="0" value="0.00">
                    </div>

                    
                    <div class="col-md-1 d-flex justify-content-center align-items-center">
                        <button type="button" class="btn btn-danger btn-sm removerow">×</button>
                    </div>
                </div>





            `);
        }

        $(document).on('click', '#addStockRow', function(){
            $.ajax({
                url: 'bros_admin_api.php',
                method: 'POST',
                data: { brotbl: 'GET_PRODUCTS' },
                dataType: 'json',
                success: function(products){
                    const showLabels = $('#stockRowsContainer .stock_row').length === 0;
                    $('#stockRowsContainer').append(buildStockRowV2(products, showLabels));
                }
            });
        });

        // Remove stock row
        $(document).on('click', '.removerow', function(){
            $(this).closest('.stock_row').remove();
        });

        // Product search functionality
        let allProducts = [];
        
        // Load all products when modal opens
        function loadAllProducts() {
            $.ajax({
                url: 'bros_admin_api.php',
                method: 'POST',
                data: { brotbl: 'GET_PRODUCTS' },
                dataType: 'json',
                success: function(products) {
                    allProducts = products;
                }
            });
        }

        // Search products and show dropdown
        $(document).on('input', '.product-search-input', function() {
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

            // Build dropdown HTML
            let dropdownHTML = '';
            
            // Add matching products
            filteredProducts.forEach(product => {
                dropdownHTML += `<div class="product-dropdown-item" data-product="${product.product_name}">${product.product_name}</div>`;
            });

            // Add "Add new product" option if no exact match
            const exactMatch = allProducts.some(product => 
                product.product_name.toLowerCase() === searchTerm.toLowerCase()
            );
            
            if (!exactMatch && searchTerm.length > 0) {
                dropdownHTML += `<div class="product-dropdown-item add-new-product" data-action="add-new">+ Add "${searchTerm}" as new product</div>`;
            }

            dropdown.html(dropdownHTML).show();
        });

        // Handle dropdown item selection
        $(document).on('click', '.product-dropdown-item', function() {
            const container = $(this).closest('.product-search-container');
            const input = container.find('.product-search-input');
            const dropdown = container.find('.product-dropdown');
            
            if ($(this).data('action') === 'add-new') {
                // Show quick add modal
                const productName = input.val();
                $('#qa_product_name').val(productName);
                $('#quickAddProductModal').modal('show');
                dropdown.hide();
            } else {
                // Select existing product
                const productName = $(this).data('product');
                input.val(productName);
                dropdown.hide();
            }
        });

        // Hide dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.product-search-container').length) {
                $('.product-dropdown').hide();
            }
        });

        // Load products when stock modal opens
        $(document).on('shown.bs.modal', '#stockModal', function() {
            loadAllProducts();
        });

        // Save batch stock
        $(document).on('click', '#saveBatchStock', function(){
            const movement_date = $('#batch_movement_date').val();
            if (!movement_date){ alert('Please select date'); return; }

            let rows = [];
            $('#stockRowsContainer .stock_row').each(function(){
                const product = $(this).find('.stock_product').val().trim();
                const rowDate = movement_date;
                const qtyIn  = parseInt($(this).find('.qty_in').val())  || 0;
                const qtyOut = parseInt($(this).find('.qty_out').val()) || 0;
                const cost = parseFloat($(this).find('.stock_cost').val()) || 0;
                const shippingCost = parseFloat($(this).find('.ship_cost').val()) || 0;
                if (product && (qtyIn > 0 || qtyOut > 0) && cost >= 0){
                    rows.push({ product: product, movement_date: rowDate, qty_in: qtyIn, qty_out: qtyOut, unit_cost: cost, shipping_cost: shippingCost });
                }
            });
            if (rows.length === 0){ alert('Please add at least one valid row.'); return; }

            // Submit sequentially using existing SAVE2 to avoid big backend change
            let i = 0; let errs = 0;
            function next(){
                if (i >= rows.length){
                    $('#stockModal').modal('hide');
                    fetchStockMovement();
                    let lbl = $('#label_filter').val() || '';
                    let currentSearch = $('#search_product').val() || '';
                    fetchInventory(lbl, currentSearch);
                    
                    return;
                }
                const r = rows[i++];
                $.ajax({
                    url: 'bros_admin_api.php',
                    method: 'POST',
                    data: { brotbl: 'SAVE2', product: r.product, qty_in: r.qty_in, qty_out: r.qty_out, unit_cost: r.unit_cost, shipping_cost: r.shipping_cost, movement_date: r.movement_date },
                    success: function(resp){
                        try {
                            if (typeof resp === 'string' && resp.toLowerCase().startsWith('error')) { errs++; alert(resp); }
                        } catch(e) {}
                        next();
                    },
                    error: function(){ errs++; next(); }
                });
            }
            next();
        });

        // Delete stock movement with confirmation
        $(document).on('click', '.delete-stock', function(){
            const rowId = $(this).data('id');
            if (!rowId) return;
            if (!confirm('Are you sure you want to delete this stock movement?')) return;
            $.ajax({
                url: 'bros_admin_api.php',
                method: 'POST',
                data: { brotbl: 'DELETE_STOCK', id: rowId },
                success: function(resp){
                    alert(resp);
                    fetchStockMovement();
                },
                error: function(){ alert('Failed to delete'); }
            });
        });

        // No beginning/ending/date fields within the row; they are shown only in the report table


     
     








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
            
            $(".crud").css("display", "block");
            $("#closenavbot").css("display", "block");
            $("#tool").css("margin-top", "535px");
        });

        $("#closenavbot").click(function(){
            $(".crud-nav").css("height", "0");
           
            $(".crud").css("display", "none");
            $("#closenavbot").css("display", "none");
            $("#tool").css("margin-top", "0px");
        });



        $("#tool2").click(function(){
            $(".crud-nav-2").css("height", "100vh");
            
            $(".crud2").css("display", "block");
            $("#closenavbot2").css("display", "block");
            $("#tool2").css("margin-top", "535px");
        });

        $("#closenavbot2").click(function(){
            $(".crud-nav-2").css("height", "0");
           
            $(".crud2").css("display", "none");
            $("#closenavbot2").css("display", "none");
            $("#tool2").css("margin-top", "0px");
        });



        $("#inventory").click(function(){
            $("#navbot").show();
            $("#navbot2").hide();
            $("#data_table").show();
            $("#data_table2").hide();
            $("#data_table3").hide();
            // Load products for search functionality when switching to inventory
            if (allProducts.length === 0) {
                loadAllProducts();
            }
        });
        $("#stock").click(function(){
           $("#data_table2").show();
            $("#data_table").hide();
            $("#data_table3").hide();
            $("#navbot2").hide();
            $("#navbot").hide();
            $("#inv_quick_add").hide();
        });



       

        
        function fetchInventory(label = "", searchTerm = "") {
            var brotbl = "LOADBROINV";
            $.ajax({
                url: "bros_admin_api.php",
                method: "POST",
                data: { brotbl: brotbl, label_filter: label, search_term: searchTerm },
                success: function(data) {
                    $('#data_table').html(data);
                    // Load products for search functionality if not already loaded
                    if (allProducts.length === 0) {
                        loadAllProducts();
                    }
                }
            });
        }

        function fetchStockMovement(from_date = "", to_date = "", report_type = "") {
            var brotbl = "LOADBROSTCK";
            $.ajax({
                url: "bros_admin_api.php",
                method: "POST",
                data: { brotbl: brotbl, from_date: from_date, to_date: to_date, report_type: report_type },
                success: function(data) {
                    $('#data_table2').html(data);
                }
            });
        }
        



       

        







       

        
        // Fetch transactions function
        function fetchTransactions(branch) {
            $.ajax({
                url: 'bros_admin_api.php',
                method: 'POST',
                data: { action: 'BI_GET_TRANSACTIONS', branch: branch },
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
                                    <td>${tx.product}</td>
                                    <td><span class="badge ${badgeClass}">${action}</span></td>
                                    <td>${tx.quantity}</td>
                                </tr>
                            `;
                        });
                    } else {
                        rows = '<tr><td colspan="4" class="text-center py-3">No transactions yet</td></tr>';
                    }
                    $('#transactionTableBody').html(rows); // <-- corrected ID
                },
                error: function(err) {
                    console.error(err);
                }
            });
        }






        let selectedId = null;

        // Track selected row
        $(document).on('change', '.check_box', function() {
            $('.check_box').not(this).prop('checked', false);
            selectedId = $(this).is(':checked') ? $(this).val() : null;
        });

        // ADD new product
        $('#addbtn').click(function() {
            $('#addProductModal').modal('show');
            $('.modal-title').text("Add New Product");
            $('#action').text("Save Product");
            $('#product_brotbl').val("SAVE");
            $('#product_id').val("");
            $('#label').val("");
            $('#product_name').val("");
            $('#classification').val("");
        });

       
        // EDIT selected product
        $('#btnedit').click(function() {
            if (!selectedId) { 
                alert("Please select a product to edit."); 
                return; 
            }

            $.ajax({
                url: "bros_admin_api.php", 
                method: "POST",
                data: { 
                    id: selectedId, 
                    brotbl: "SELECT"  
                },
                dataType: "json",
                success: function(data) {
                    if(!data) {
                        alert("Product data not found.");
                        return;
                    }
                    $('#addProductModal').modal('show');
                    $('.modal-title').text("Update Product");
                    $('#action').text("Update Product");
                    $('#product_brotbl').val("UPDATE");
                    $('#product_id').val(data.id);
                    $('#label').val(data.label);
                    $('#product_name').val(data.product_name);
                    $('#classification').val(data.classification);
                },
                error: function(xhr, status, error) {
                    alert("AJAX Error: " + error);
                }
            });
        });
        let currentLabel = $('#label').val();

        // DELETE selected product
        $('#btndelete').click(function() {
            if (!selectedId) { 
                alert("Please select a product to delete."); 
                return; 
            }
            if (!confirm("Are you sure you want to delete this product?")) return;

            $.ajax({
                url: "bros_admin_api.php", 
                method: "POST",
                data: { id: selectedId, brotbl: "DELETE" },
                success: function(data) {
                    alert(data);
                    let currentSearch = $('#search_product').val() || '';
                    fetchInventory(currentLabel, currentSearch);
                    fetchStockMovement(); // Also refresh stock movement table
                },
                error: function(xhr, status, error) {
                    alert("AJAX Error: " + error);
                }
            });
        });

        $('#action').click(function() {
            let label = $('#label').val().trim();
            let product_name = $('#product_name').val().trim();
            let classification = $('#classification').val().trim();
            let actionType = $('#product_brotbl').val();
            let product_id = $('#product_id').val();

            if (!label || !product_name) {
                alert("Please fill in all required fields");
                return;
            }

            $.ajax({
                url: "bros_admin_api.php", 
                method: "POST",
                data: {
                    id: product_id,
                    label: label,
                    product_name: product_name,
                    classification: classification,
                    unit_price: 0.00,
                    quantity: 0,
                    brotbl: actionType
                },
                success: function(response) {
                    alert(response);
                    $('#addProductModal').modal('hide');
                    let currentSearch = $('#search_product').val() || '';
                    fetchInventory(label, currentSearch);
                    fetchStockMovement(); // Also refresh stock movement table
                },
                error: function(xhr, status, error) {
                    alert("AJAX Error: " + error);
                }
            });
        });

        // Refresh inventory list button
        $('#btnref').click(function(){
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter2Sec();
            let current = $('#label_filter').val() || '';
            let searchTerm = $('#search_product').val() || '';
            fetchInventory(current, searchTerm);
        });

        // Quick Add submit
        $(document).on('click', '#quick_add_submit', function(){
            let label = $('#qa_label').val();
            let product_name = $('#qa_product_name').val().trim();
            let classification = $('#qa_classification').val().trim();

            if (!label || !product_name) {
                alert('Please complete the required fields.');
                return;
            }

            $.ajax({
                url: 'bros_admin_api.php',
                method: 'POST',
                data: {
                    brotbl: 'SAVE',
                    label: label,
                    product_name: product_name,
                    classification: classification,
                    unit_price: 0.00,
                    quantity: 0
                },
                success: function(resp){
                    alert(resp);
                    // Close the quick add modal
                    $('#quickAddProductModal').modal('hide');
                    
                    // Reload all products for the search functionality
                    loadAllProducts();
                    
                    // clear inputs
                    $('#qa_product_name').val('');
                    $('#qa_classification').val('');
                    $('#qa_label').val('');
                    
                    // Refresh inventory and stock movement tables
                    let current = $('#label_filter').val() || label || '';
                    let currentSearch = $('#search_product').val() || '';
                    fetchInventory(current, currentSearch);
                    fetchStockMovement();
                },
                error: function(xhr, status, error){
                    alert('AJAX Error: ' + error);
                }
            });
        });


       











        function fetchDailyReport() {
    $.ajax({
        url: "bros_admin_api.php",
        method: "POST",
        data: { brotbl: "LOADBRODAILY" },
        success: function (data) {
            $('#data_table3').html(data);
        }
    });
}

        // Daily report filter/reset
        $(document).on('click', '#filterBtn', function(e){
            e.preventDefault();
            const from_date = $('#data_table3 #from_date').val();
            const to_date = $('#data_table3 #to_date').val();
            const report_type = $('#data_table3 #label_filter').val();
            $.ajax({
                url: 'bros_admin_api.php',
                method: 'POST',
                data: { brotbl: 'LOADBRODAILY', from_date: from_date, to_date: to_date, report_type: report_type },
                success: function(html){
                    $('#data_table3').html(html);
                }
            });
        });
        $(document).on('click', '#resetBtn', function(e){
            e.preventDefault();
            $('#data_table3 #from_date').val('');
            $('#data_table3 #to_date').val('');
            $('#data_table3 #label_filter').val('');
            fetchDailyReport();
        });

        // Refresh list in daily section
        $('#btnref2').click(function(){
            $(".loader-overlay").css("display", "flex");
            hideLoaderAfter2Sec();
            fetchDailyReport();
        });

let id = null;

/* ----------------- EXPENSES ----------------- */
// Add expense row
$(document).on('click', '#add_expense', function() {
    let row = `
    <tr class="expense_row">
        <td>
            <select class="form-select expense_type">
                <option value="">-- Select Expense --</option>
                <option value="Utility Expense">Utility Expense</option>
                <option value="Salary Expense">Salary Expense</option>
                <option value="Supplies Expense">Supplies Expense</option>
                <option value="Royalty Fee">Royalty Fee</option>
                <option value="Internet Expense">Internet Expense</option>
                <option value="Direct Operating Expense">Direct Operating Expense</option>
                <option value="Miscellaneous Expense">Miscellaneous Expense</option>
                
            </select>
        </td>
        <td colspan="2">
            <div class="d-flex gap-2">
                <input type="number" class="form-control expense_amount flex-grow-1" min="0" step="0.01" placeholder="0.00">
                
            </div>
        </td>
        <td>
        <input type="text" class="form-control other_expense flex-grow-1" placeholder="Please Specify.." >
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm remove_expense">×</button>
        </td>
    </tr>`;
    $('#expenses_table tbody').append(row);
});

// Remove expense row
$(document).on('click', '.remove_expense', function() {
    $(this).closest('tr').remove();
    updateTotalExpenses();
});




// Recalculate expenses
$(document).on('input', '.expense_amount', updateTotalExpenses);

function updateTotalExpenses() {
    let total = 0;
    $('.expense_amount').each(function() {
        total += parseFloat($(this).val()) || 0;
    });
    $('#total_expenses').text(total.toFixed(2));
}

/* ----------------- CASH BREAKDOWN ----------------- */
$(document).on('input', '.cash_qty', function() {
    let row = $(this).closest('tr');
    let denom = parseFloat($(this).data('value'));
    let qty = parseInt($(this).val()) || 0;
    let total = denom * qty;
    row.find('.cash_total').text(total.toFixed(2));

    updateTotalCash();
});

function updateTotalCash() {
    let total = 0;
    $('.cash_total').each(function() {
        total += parseFloat($(this).text()) || 0;
    });
    $('#total_cash').text(total.toFixed(2));
}

/* ----------------- SAVE / UPDATE ----------------- */
$('#action2').click(function(e) {
    e.preventDefault();

    let report_date     = $('#report_date').val();
    let cash_on_counter = $('#cash_on_counter').val();
    let cash_in         = $('#cash_in').val();
    let gcash_sales     = $('#gcash_sales').val();
    let credit_sales    = $('#credit_sales').val();
    let other_sales     = $('#other_sales').val();
    let action          = $('#daily_brotbl').val(); // SAVE_DAILY or UPDATE_DAILY

    // Collect expenses
    let expenses = [];
    $('.expense_row').each(function() {
        let type   = $(this).find('.expense_type').val();
        let other  = $(this).find('.other_expense').val();
        let amount = $(this).find('.expense_amount').val();
        if (type || other || amount) {
            expenses.push({ type: type, other: other, amount: amount });
        }
    });

    // ✅ Collect cash breakdown as object for PHP
    let cash_breakdown = {};
    $('.cash_qty').each(function() {
        let denom = $(this).data('value'); // 1000, 500, 200...
        let qty   = parseInt($(this).val()) || 0;
        cash_breakdown[denom] = qty;
    });

    $.ajax({
        url: "bros_admin_api.php",
        method: "POST",
        data: {
            brotbl: action, // still brotbl for SAVE/UPDATE
            id: $('#daily_id').val(),
            report_date: report_date,
            cash_on_counter: cash_on_counter,
            cash_in: cash_in,
            gcash_sales: gcash_sales,
            credit_sales: credit_sales,
                Totalsales: $('#Totalsales').val(),
            other_sales: other_sales,
            expenses: JSON.stringify(expenses),
            cash_breakdown: JSON.stringify(cash_breakdown),
            other_sales_rows: (function(){
                let rows = [];
                $('#other_sales_table tbody tr').each(function(){
                    const pname = $(this).find('.os_name').val().trim();
                    const pprice = parseFloat($(this).find('.os_price').val()) || 0;
                    const quantity = parseInt($(this).find('.os_qty').val()) || 0;
                    if (pname && quantity > 0) {
                        rows.push({ pname: pname, pprice: pprice, quantity: quantity });
                    }
                });
                return JSON.stringify(rows);
            })()
        },
        success: function(data) {
            alert(data);
            $('#addDailyReportModal').modal('hide');
            fetchDailyReport();
            
            // Reset id for next add
            id = null;
            
            // Also clear any checked checkboxes
            $('.check_box').prop('checked', false);
        }
    });
});

/* ----------------- ADD MODAL ----------------- */
$('#addbtn2').click(function() {
    $('.modal-title').text("Add New Daily Report");
    $('#addDailyReportModal').modal('show'); 
    
    // Keep date field with today's date
    $('#report_date').val(new Date().toISOString().split('T')[0]); 
    
    // Clear all other input fields
    $('#cash_on_counter').val("");
    $('#cash_in').val("");
    $('#gcash_sales').val("");
    $('#credit_sales').val("");
    $('#Totalsales').val("");
    
    // Clear cash breakdown inputs
    $('.cash_qty').val(""); 
    $('.cash_total').text("0.00"); 
    $('#total_cash').val("0.00");
    
    // Clear expenses table
    $('#expenses_table tbody').empty();
    
    // Clear other sales table
    $('#other_sales_table tbody').empty();
    
    // Reset totals
    $('#total_cash_sales').text("0.00");
    $('#total_expenses').text("0.00");
    $('#total_other_sales').text("0.00");

    $('#daily_brotbl').val("SAVE_DAILY"); // for saving
    $('#daily_id').val(""); // clear the ID field
    
    // Clear global id variable for new add
    id = null;
    
    // Also clear any checked checkboxes to ensure clean state
    $('.check_box').prop('checked', false);
});

/* ----------------- CHECKBOX ----------------- */
$(document).on('change', '.check_box', function() {
    $('.check_box').not(this).prop('checked', false);
    id = $(this).is(':checked') ? $(this).val() : null;
});

/* ----------------- EDIT ----------------- */
$(document).on('click', '#btnedit2', function() {
    if (!id) {
        alert("Please select a record to edit.");
        return;
    }
    $.ajax({
        url: "bros_admin_api.php",
        method: "POST",
        data: {brotbl: "SELECT_DAILY", id: id}, // ✅ now using brotbl
        dataType: "json",
        success: function(data) {
            $('#addDailyReportModal').modal('show');
            $('.modal-title').text("Update Daily Report");
            $('#daily_brotbl').val("UPDATE_DAILY"); // so Save/Update handler knows
            $('#daily_id').val(data.id); // set the ID for update
            $('#report_date').val(data.report_date);
            $('#cash_on_counter').val(data.cash_on_counter);
            $('#cash_in').val(data.cash_in);
            $('#gcash_sales').val(data.gcash_sales);
            $('#credit_sales').val(data.credit_sales);
            $('#other_sales').val(data.other_sales);
            // POS Total
            try { $('#Totalsales').val(parseFloat(data.total_sales_pos||0).toFixed(2)); } catch(e) {}

            // Expenses
            $('#expenses_table tbody').empty();
            if (data.expense_list && data.expense_list.length > 0) {
                data.expense_list.forEach(function(exp) {
                    let isOthers = exp.expense_type==="Others";
                    let row = `
                    <tr class="expense_row">
                        <td>
                            <select class="form-select expense_type">
                                <option value="">-- Select Expense --</option>
                                <option value="Electricity Expense" ${exp.expense_type==="Electricity Expense"?"selected":""}>Electricity Expense</option>
                                <option value="Water Expense" ${exp.expense_type==="Water Expense"?"selected":""}>Water Expense</option>
                                <option value="Salary Expense" ${exp.expense_type==="Salary Expense"?"selected":""}>Salary Expense</option>
                                <option value="Others" ${isOthers?"selected":""}>Others</option>
                            </select>
                        </td>
                        <td><input type="number" class="form-control expense_amount" value="${exp.amount}" step="0.01"></td>
                        <td><input type="text" class="form-control other_expense" value="${exp.other??""}" style="${isOthers?"":"display:none;"}"></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove_expense">×</button></td>
                    </tr>`;
                    $('#expenses_table tbody').append(row);
                });
            }
            updateTotalExpenses();

            // ✅ Cash breakdown
            if (data.cash_breakdown) {
                $('.cash_qty').val("0");
                $('.cash_total').text("0.00");
                for (let denom in data.cash_breakdown) {
                    if (denom.startsWith("cash_")) {
                        let d = denom.replace("cash_", "");
                        let input = $('.cash_qty[data-value="'+d+'"]');
                        if (input.length) {
                            input.val(data.cash_breakdown[denom]);
                            input.trigger('input'); // auto update total per row
                        }
                    }
                }
            }
            // ✅ Other sales list
            $('#other_sales_table tbody').empty();
            if (data.other_sales_list && data.other_sales_list.length > 0) {
                data.other_sales_list.forEach(function(item){
                    $('#other_sales_table tbody').append(
                        buildOtherSaleRow(item.pname, parseFloat(item.pprice).toFixed(2), parseInt(item.quantity))
                    );
                });
            }
            recalcOtherSales();
            // Recompute totals to reflect loaded values
            try { updateTotalExpenses(); } catch(e) {}
            try { updateTotalCash(); } catch(e) {}
            try { calculateCashSalesTotal(); } catch(e) {}
        }
    });
});

/* ----------------- DELETE ----------------- */
$(document).on('click', '#btndelete2', function() {
    if (!id) {
        alert("Please select a record to delete.");
        return;
    }
    if (confirm("Are you sure you want to delete this daily report?")) {
        $.ajax({
            url: "bros_admin_api.php",
            method: "POST",
            data: {brotbl: "DELETE_DAILY", id: id}, // ✅ now using brotbl
            success: function(data) {
                alert(data);
                fetchDailyReport();
            }
        });
    }
});

/* ----------------- MODAL RESET ----------------- */
// Reset state when modal is closed (cancel button, X button, or clicking outside)
$('#addDailyReportModal').on('hidden.bs.modal', function() {
    // Reset id variable
    id = null;
    
    // Clear any checked checkboxes
    $('.check_box').prop('checked', false);
    
    // Reset form fields
    $('#daily_brotbl').val("SAVE_DAILY");
    $('#daily_id').val("");
});



        
    });
</script>
