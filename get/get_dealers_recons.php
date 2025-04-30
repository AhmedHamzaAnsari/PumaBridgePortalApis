<?php
//fetch.php  
include("../config.php");

$access_key = '03201232927';

$pass = $_GET["key"];
if ($pass != '') {
    if ($pass == $access_key) {

        $dealer_id = $_GET["dealer_id"];
        $from = $_GET["from"];
        $to = $_GET["to"];

        // Initialize an array to store the data
        $data = [];
        $month_series = 1;

        $sql = "SELECT * FROM dealers WHERE id=$dealer_id;";
        $result = $db->query($sql);

        $dealerProductCounts = [];
        $myArray = [];

        while ($row = $result->fetch_assoc()) {
            $id = $row["id"];
            $name = $row["name"];
            $terr = $row["name"];
            $region = $row["region"];

            $dealerProductCounts = [];
            $myArray = [];

            $get_orders = "SELECT rs.*, us.name, pp.name as product_name
            FROM dealers_stock_variations as rs
            JOIN dealers_products as pp ON pp.id=rs.product_id
            JOIN users as us ON us.id=rs.created_by
                           WHERE rs.dealer_id = $id 
                           AND DATE(rs.created_at) >= '$from' 
                           AND DATE(rs.created_at) <= '$to'
                           GROUP BY rs.product_id, rs.task_id
                           ORDER BY id ASC";
            
            $result_orders = $db->query($get_orders);

            while ($row_2 = $result_orders->fetch_assoc()) {
                $myArray[] = $row_2;
            }

            if (count($myArray) > 0) {
                $dealerProductCounts = [
                    "id" => $id,
                    "name" => $name,
                    "terr" => $terr,
                    "region" => $region,
                    "recon" => $myArray,
                ];

                $data[] = $dealerProductCounts;
            }
        }

        // Convert the array to a JSON string
        $jsonData = json_encode($data);

        // Output the JSON string
        echo $jsonData;

    } else {
        echo 'Wrong Key...';
    }
} else {
    echo 'Key is Required';
}
?>
