<?php
    $conn = mysqli_connect('localhost','root','root','tmc_admin_db');

    if (isset($_POST["action"])) {
        if ($_POST["action"] == "LOAD") {
            $sql = "SELECT * FROM gas_sales_tbl ORDER BY id DESC";
            $result = mysqli_query($conn, $sql);
            $ctr = 0;
            $output = "";

            $output .= '
            
            
            <table style="width: 100%; border: 1px solid #CCC; border-collapse: collapse; border-radius: 20px;">
            <tr><td style="padding: 10px;">
            <table style="width: 100%; border-collapse: collapse;" id="border">

            <tr>
            <td colspan="9" id="border" style="background: #333; color: #FFF; border-radius: 5px 5px 0 0; padding: 10px; font-weight: bold;">
            <font size="2"><strong>BROS INASAL SALES REPORT</strong></font>
            </td>
            </tr>

            <tr>
            <td id="border" style="width: 2%; background: #CCCCCC; padding: 8px; vertical-align: top; "></td>
            <td id="border" style="width: 45%; background: #CCCCCC; padding: 8px; vertical-align: middle;"><center><strong>BRANCH</strong></center></td>
            <td id="border" style="width: 45%; background: #CCCCCC; padding: 8px; vertical-align: middle;"><center><strong>DAILY SALES</strong></center></td>
            
            <td id="border" style="width: 8%; background: #CCCCCC; padding: 8px; vertical-align: middle;"><center><strong></strong></center></td>
            </tr>';
            


            if (mysqli_num_rows($result) > 0) {
            $current_date = null;
            while ($row = mysqli_fetch_array($result)) {
                if ($current_date !== $row["log_date"]) {
                    $current_date = $row["log_date"];
                    $output .= '
                    <tr>
                        <td colspan="4" style="background:#ddd; font-weight:bold; padding:10px;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">

                            <!-- Left side -->
                            <span>Date: ' . strtoupper($current_date) . '</span>

                            <!-- Right side -->
                            <div>
                                <form method="POST" action="export_pdf.php" target="_blank" style="display:inline;">
                                <input type="hidden" name="row_id" value="'.$row['id'].'">
                                <button type="submit" name="action" value="view" class="btn btn-primary" style="padding:2px; background:none; border:none;">
                                    <img src="view.png" alt="icon" style="width:25px; height:25px; vertical-align:middle;">
                                </button>
                                </form>

                                <form method="POST" action="export_pdf.php" style="display:inline;">
                                <button type="submit" name="action" value="download" class="btn btn-danger" style="padding:2px; background:none; border:none;">
                                    <img src="down.png" alt="icon" style="width:25px; height:25px; vertical-align:middle;">
                                </button>
                                </form>
                            </div>

                            </div>
                        </td>
                    </tr>';
                }


                $output .= '
                <tr>
                <td id="border" style="background: #EEEEEE; text-align:center; padding:10px;"><input type="checkbox" class="check_box" value="' . $row["id"] . '"></td>
                <td id="border" style="background: #EEEEEE; padding:10px;"><center>' . strtoupper($row["branch"]) . '</center></td>
                <td id="border" style="background: #EEEEEE; padding:10px;"><center>₱' . number_format($row["netsales"], 2) . '</center></td>
                <td id="border" style="background: #EEEEEE; padding:10px;"> 
                    
                </td>
                </tr>';
                                $ctr++;
                            }
                        } else {
                            $output .= '<tr><td colspan="9" align="center">No records found</td></tr>';
                        }

                        $output .= '<tr><td colspan="9" style="padding: 2px;"><font size="1">Total Number of Records: ' . $ctr . '</font></td></tr>
                </table>
                </td></tr>
                </table>';

                        echo $output;
                    }

                  
    }




    




    



?>