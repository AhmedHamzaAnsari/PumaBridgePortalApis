<?php
//fetch.php  
include ("../config.php");

$access_key = '03201232927';
error_reporting(0);

$pass = $_GET["key"];
if ($pass != '') {
    if ($pass == $access_key) {

        $dealer_id = $_GET["dealer_id"];
        $from = $_GET["from"];
        $to = $_GET["to"];

        // Initialize an array to store the data
        $data = [];
        $month_series = 1;

        $sql = "SELECT dl.*,dl.`co-ordinates` as co_ordinates,usz.name as zm_name,ust.name tm_name,usa.name as asm_name  FROM dealers as dl 
        join users as usz on usz.id=dl.zm
        join users as ust on ust.id=dl.tm
        join users as usa on usa.id=dl.asm
        where dl.privilege='Dealer' and dl.id=$dealer_id order by dl.id desc;";
        $result = $db->query($sql);

        $dealerProductCounts = [];
        $myArray = [];

        while ($row = $result->fetch_assoc()) {
            $id = $row["id"];
            $name = $row["name"];
            $terr = $row["name"];
            $region = $row["region"];
            $zm_name = $row["zm_name"];
            $tm_name = $row["tm_name"];
            $asm_name = $row["asm_name"];

            $dealerProductCounts = [];
            $myArray = [];

            $get_orders = "SELECT rs.*, us.name, pp.name as product_name,
            (SELECT id FROM inspector_task where dealer_id=it.dealer_id and id!=it.id and id<it.id and stock_variations_status=1 order by id desc limit 1) as last_visit_id
           FROM dealers_stock_variations as rs
           JOIN dealers_products as pp ON pp.id=rs.product_id
           JOIN users as us ON us.id=rs.created_by
           join inspector_task as it on it.id=rs.task_id
           WHERE rs.dealer_id = $id 
           AND DATE(rs.created_at) >= '$from' 
           AND DATE(rs.created_at) <= '$to'
           GROUP BY rs.product_id, rs.task_id
           ORDER BY id ASC";

            $result_orders = $db->query($get_orders);

            while ($row_2 = $result_orders->fetch_assoc()) {

                $last_visit_id = $row_2['last_visit_id'];
                $product_id = $row_2['product_id'];
                $current_recon_date = $row_2['created_at'];
                $total_sales = $row_2['sales_as_per_meter_reading'];

                $sql_last = "SELECT * FROM dealers_stock_variations where task_id='$last_visit_id' and product_id=$product_id";

                // echo $sql;

                $result_sql_last = mysqli_query($db, $sql_last);
                $row_sql_last = mysqli_fetch_array($result_sql_last);

                $count_sql_last = mysqli_num_rows($result_sql_last);
                $last_visit_date = '';
                $no_of_days = '';
                $avg_daily_sale = '';
                $avg_month_sale = '';
                if ($count_sql_last > 0) {
                    $last_visit_date = $row_sql_last['created_at'];

                    $datetime1 = $last_visit_date;
                    $datetime2 = $current_recon_date;

                    // Create DateTime objects
                    $date1 = new DateTime($datetime1);
                    $date2 = new DateTime($datetime2);

                    // Calculate the difference
                    $interval = $date1->diff($date2);

                    // Get the difference in days
                    $no_of_days = $interval->days;

                    if($no_of_days!=0){
                        $avg_daily_sale = $total_sales/$no_of_days;
                        $avg_month_sale = $avg_daily_sale*30;

                    }
                    else{
                        $avg_daily_sale = 0;
                        $avg_month_sale = 0;
                    }

                } else {
                    $last_visit_date = 'First Time';
                    $no_of_days = 0;
                    $avg_daily_sale = 0;
                    $avg_month_sale = 0;

                }

                
                //  $no_of_days .'-';
                $row_2['last_visit_date'] = $last_visit_date;
                $row_2['no_of_days'] = $no_of_days;
                $row_2['avg_daily_sale'] = $avg_daily_sale;
                $row_2['avg_month_sale'] = $avg_month_sale;
                $myArray[] = $row_2;
            }

            if (count($myArray) > 0) {
                $dealerProductCounts = [
                    "id" => $id,
                    "name" => $name,
                    "terr" => $terr,
                    "region" => $region,
                    "zm_name" => $zm_name,
                    "tm_name" => $tm_name,
                    "asm_name" => $asm_name,
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