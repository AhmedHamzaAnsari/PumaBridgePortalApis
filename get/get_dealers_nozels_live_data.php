<?php
//fetch.php  
include("../config.php");

$access_key = '03201232927';

$pass = $_GET["key"] ?? '';
$dealer_id = $_GET["dealer_id"] ?? '';

if ($pass != '') {
    if ($pass == $access_key) {
        if ($dealer_id == '') {
            echo json_encode(["error" => "Dealer ID is Required"]);
            exit;
        }

        $data = [];
        $month_series = 1;

        // ✅ NozzleNo نکالیں  
        $query = "SELECT DISTINCT ns.NozzleNo FROM dealers_nozzels_sales AS ns
                  JOIN dealers AS dl ON dl.sap_no = ns.dealers_sap 
                  WHERE dl.id = '$dealer_id' ORDER BY ns.NozzleNo ASC";

        $result = mysqli_query($db, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            $NozzleNo = $row["NozzleNo"];

            $dealerProductCounts = [];
            $myArray = [];

            // ✅ تازہ ترین سیل نکالیں  
            $get_orders = "SELECT ns.*,dl.name,
            (SELECT sum(Quantity) FROM dealers_nozzels_sales where NozzleNo=$NozzleNo order by id desc) as total_qty_sale, 
            (SELECT sum(Amount) FROM dealers_nozzels_sales where NozzleNo=$NozzleNo order by id desc) as total_amount_sale 
            FROM dealers_nozzels_sales AS ns
                           JOIN dealers AS dl ON dl.sap_no = ns.dealers_sap 
                           WHERE ns.NozzleNo = '$NozzleNo' AND dl.id = '$dealer_id'
                           ORDER BY ns.id DESC LIMIT 1";

            $result_orders = mysqli_query($db, $get_orders);

            while ($row_2 = mysqli_fetch_assoc($result_orders)) {
                $myArray[] = $row_2;
            }

            $dealerProductCounts = [
                "NozzleNo" => $NozzleNo,
                "Last_sale" => $myArray,
            ];
            $data[] = $dealerProductCounts;
        }

        $month_series++;

        // ✅ JSON میں آؤٹ پٹ  
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);

    } else {
        echo json_encode(["error" => "Wrong Key"]);
    }
} else {
    echo json_encode(["error" => "Key is Required"]);
}
?>
