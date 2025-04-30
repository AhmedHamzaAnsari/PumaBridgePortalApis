<?php
//fetch.php  
include("../config.php");

$access_key = '03201232927';
$pass = $_GET["key"];

if (!empty($pass)) {
    if ($pass == $access_key) {
        // $dealer_id = intval($_GET["dealer_id"]);
        // $tm_id = intval($_GET["tm_id"]);
        $region = $_GET["region"];
        $tm = $_GET["tm"] ?? '';

        $from = $db->real_escape_string($_GET["from"]);
        $to = $db->real_escape_string($_GET["to"]);
        $products = $db->real_escape_string($_GET["products"]);
        $product_val = "";
        if ($products != "") {
            $product_val = "AND pp.id='$products'";
        } else {
            $product_val = "";
        }
        // Initialize an array to store the data
        // and tr.recon_approval='1' and tr.approved_status='1'
        $formatted_data = [];

        $get_orders = "SELECT rs.*, pp.name as product_name,us.name as tm_name,dl.sap_no as dealer_sap,dl.name as dealer_name,
        (SELECT id FROM inspector_task 
         WHERE dealer_id = rs.dealer_id 
         AND id != rs.task_id 
         AND id < rs.task_id 
         AND stock_variations_status = 1 
         ORDER BY id DESC LIMIT 1) AS last_visit_id
        FROM dealers_stock_variations AS rs
        JOIN dealers_products AS dp ON dp.id = rs.product_id
        JOIN all_products AS pp ON pp.name = dp.name
        join inspector_task as it on it.id=rs.task_id
        JOIN dealers as dl ON dl.id = it.dealer_id
        left join inspector_task_response as tr on tr.task_id=it.id
        JOIN users as us on us.id = it.user_id
        WHERE us.region='$region' and date(rs.created_at)>='$from' and date(rs.created_at)<='$to' and us.id IN($tm) and rs.gain_loss!=0 and rs.sales_as_per_meter_reading!=0 
        GROUP BY rs.product_id, rs.task_id";

        // $get_orders = "SELECT rs.*, pp.name as product_name,us.name as tm_name,dl.sap_no as dealer_sap,dl.name as dealer_name
        // FROM dealer_stock_recon_new as rs
        // join dealers_products as dp on dp.id=rs.product_id
        // JOIN all_products as pp ON pp.name = dp.name
        // join inspector_task as it on it.id=rs.task_id
        // JOIN dealers as dl ON dl.id = it.dealer_id
        // left join inspector_task_response as tr on tr.task_id=it.id
        //  JOIN users as us on us.id = it.user_id
        // WHERE rs.total_days>0 and us.region='$region' and date(rs.created_at)>='$from' and date(rs.created_at)<='$to' and rs.variance!='0.0' and us.id IN($tm)
        // GROUP BY rs.product_id, rs.task_id";

        $result_orders = $db->query($get_orders);

        if ($result_orders) {
            while ($row_2 = $result_orders->fetch_assoc()) {
                // Prepare the record data
                $variance = $row_2['gain_loss'];
                $book_value = $row_2['book_stock'];
                $physical_stock = $row_2['current_physical_stock'];
                $task_id = $row_2['task_id'];
                $created_at = $row_2['created_at'];
                $last_visit_id = $row_2['last_visit_id'];

                $dealer_sap = $row_2["dealer_sap"];
                $name = $row_2["dealer_name"];
                $tm_name = $row_2["tm_name"];

                $last_recon_date = fetchLastVisitDate($last_visit_id, $task_id, 'stock_variation');

                $total_days = calculateDaysBetweenDates($last_recon_date, $created_at);



                $tank_beharior = ($variance < 1000 && $variance > -1000);
                $external_dumping = !$tank_beharior && $book_value > $physical_stock;
                $external_upliftment = !$tank_beharior && $physical_stock > $book_value;

                $variance_per = ($row_2['gain_loss'] / $row_2['sales_as_per_meter_reading']) * 100;

                if ($total_days != 0) {

                    $record_data = [
                        'task_id' => $task_id,
                        'total_days' => $total_days,
                        'created_at' => $created_at,
                        'site' => $name,
                        'dealer_sap' => $dealer_sap,
                        'tm' => $tm_name,
                        'region' => $region,
                        'product_name' => $row_2['product_name'],
                        'opening_date' => date('Y-m-d', strtotime($last_recon_date)),
                        'closing_date' => date('Y-m-d', strtotime($created_at)),
                        'no_os_days' => $total_days,
                        'daily_sales' => $row_2['sales_as_per_meter_reading'] / $total_days,
                        'opening_stock' => $row_2['opening_stock'],
                        'physical_stock' => $physical_stock,
                        'receipts' => $row_2['purchase_during_inspection_period'],
                        'sales' => $row_2['sales_as_per_meter_reading'],
                        'book_stock' => $book_value,
                        'variance' => $variance,
                        'variance_percentage' => round($variance_per, 2),
                        'remark' => '',
                        'tank_beharior' => $tank_beharior,
                        'external_dumping' => $external_dumping,
                        'external_upliftment' => $external_upliftment



                    ];

                    // Append the record data to the formatted_data array

                    $formatted_data[] = $record_data;
                }
            }
        } else {
            echo "Error fetching stock recon data: " . $db->error;
        }
        header('Content-Type: application/json');

        $formatted_data = utf8ize($formatted_data);
        $json = json_encode($formatted_data, JSON_PRETTY_PRINT);

        if ($json === false) {
            echo json_encode(["error" => "JSON encoding failed", "details" => json_last_error_msg()]);
        } else {
            echo $json;
        }
    } else {
        echo 'Wrong Key...';
    }
} else {
    echo 'Key is Required';
}

function utf8ize($data)
{
    if (is_array($data)) {
        return array_map('utf8ize', $data);
    } elseif (is_string($data)) {
        return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
    }
    return $data;
}

function fetchLastVisitDate($last_visit_id, $current_id, $report)
{
    $t_id = $last_visit_id ? $last_visit_id . "," . $current_id : $current_id;
    $url = "http://151.106.17.246:8080/omCS-CMS-APIS/get/inspection/get_current_second_last_visit_recon.php?key=03201232927&id=$t_id&report=$report";

    $response = @file_get_contents($url);
    if ($response === false) {
        return 0;
    }

    $result = json_decode($response, true);
    return $result[1]['created_at'] ?? $result[0]['created_at'] ?? 0;
}
function calculateDaysBetweenDates($date1, $date2)
{
    // Create DateTime objects for the two dates
    $datetime1 = new DateTime($date1);
    $datetime2 = new DateTime($date2);

    // Calculate the difference between the two dates
    $interval = $datetime1->diff($datetime2);

    // Return the total number of days
    return $interval->days;
}

?>