<?php
// fetch.php  
include("../config.php");

$access_key = '03201232927';

$pass = $_GET["key"] ?? '';
$pre = $_GET["pre"] ?? '';
$id = $_GET["user_id"] ?? '';
$from = $_GET["from"] ?? '';
$to = $_GET["to"] ?? '';
$region = $_GET["region"] ?? '';
$tm = $_GET["tm"] ?? '';


if (!empty($pass)) {
    if ($pass === $access_key) {
        $thread = [];

        // Fetch TM users
        $sql_tms = "SELECT * FROM users where region='$region' and id IN($tm); ";

        $result_tm = $db->query($sql_tms) or die("Error: " . $db->error);

        while ($user_tm = $result_tm->fetch_assoc()) {
            $inspection_report = 0;
            $ehs_report = 0;
            $recon_report = 0;
            $profitability_report = 0;
            $decant_report = 0;
            $total_dumping = 0;
            $total_external = 0;
            $tm_id = intval($user_tm['id']);
            $tm_name = $user_tm['name'];
            $region = $user_tm['region'];

            // Fetch total counts per dealer

            $sql_query3 = "SELECT 
             dl.id, 
            dl.sap_no, 
            dl.name,
            
            -- Checking all four conditions and picking the max count
            GREATEST(
                COALESCE(
                    (SELECT COUNT(DISTINCT rr.task_id) 
                     FROM inspector_task AS it
                     JOIN dealers_stock_variations AS rr ON rr.task_id = it.id
                     join users as us on us.id=it.user_id
                     WHERE it.dealer_id = dl.id AND DATE(it.time) BETWEEN '$from' AND '$to' and us.region='$region' and us.id IN($tm_id)
                    ), 0
                ),
                COALESCE(
                    (SELECT COUNT(DISTINCT rr.inspection_id) 
                     FROM inspector_task AS it
                     JOIN survey_response_main AS rr ON rr.inspection_id = it.id
                     join users as us on us.id=it.user_id
                     WHERE it.dealer_id = dl.id AND DATE(it.time) BETWEEN '$from' AND '$to' and us.region='$region' and us.id IN($tm_id)
                    ), 0
                )
            ) AS total_count,
        
            -- Getting distinct dealer count by checking all four tables
            GREATEST(
                COALESCE(
                    (SELECT COUNT(DISTINCT it.dealer_id) 
                     FROM inspector_task AS it
                     JOIN dealers_stock_variations AS rr ON rr.task_id = it.id
                     join users as us on us.id=it.user_id
                     WHERE DATE(it.time) BETWEEN '$from' AND '$to' and us.region='$region' and us.id IN($tm_id)
                     AND it.dealer_id = dl.id
                    ), 0
                ),
                COALESCE(
                    (SELECT COUNT(DISTINCT it.dealer_id) 
                     FROM inspector_task AS it
                     JOIN survey_response_main AS rr ON rr.inspection_id = it.id
                     join users as us on us.id=it.user_id
                     WHERE DATE(it.time) BETWEEN '$from' AND '$to' and us.region='$region' and us.id IN($tm_id)
                     AND it.dealer_id = dl.id
                    ), 0
                )
                
            ) AS distinct_count
        
        FROM dealers AS dl 
        JOIN users as us ON us.id = dl.asm 
        WHERE us.id IN($tm_id);";

            $result3 = $db->query($sql_query3) or die("Error: " . $db->error);

            $total_site = 0;
            $total_count = 0;
            $distinct_count = 0;

            while ($user3 = $result3->fetch_assoc()) {
                $total_site++;
                $total_count += $user3['total_count'];
                $distinct_count += $user3['distinct_count'];
            }
            $per = ($total_site > 0) ? ($distinct_count / $total_site) * 100 : 0;

          

            // $get_orders = "SELECT rs.*, pp.name as product_name
            //            FROM dealers_stock_variations as rs
            //            join dealers_products as dp on dp.id=rs.product_id
            //            JOIN all_products as pp ON pp.name = dp.name
            //            join inspector_task as it on it.id=rs.task_id
            //            left join inspector_task_response as tr on tr.task_id=it.id
            //             JOIN users as us on us.id = it.user_id
            //            WHERE rs.total_days>0 and us.region='$region' and date(rs.created_at)>='$from' and date(rs.created_at)<='$to' and rs.variance!='0.0' and us.id='$tm_id'
            //            GROUP BY rs.product_id, rs.task_id";




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
            WHERE us.region='$region' and date(rs.created_at)>='$from' and date(rs.created_at)<='$to' and us.id IN($tm_id) and rs.gain_loss!=0 and rs.sales_as_per_meter_reading!=0 
            GROUP BY rs.product_id, rs.task_id";

            $result_orders = $db->query($get_orders);

            if ($result_orders) {
                while ($row_2 = $result_orders->fetch_assoc()) {
                    // Prepare the record data
                    $tank_beharior = false;
                    $external_dumping = false;
                    $external_upliftment = false;
                    $variance = floatval($row_2['gain_loss']);
                    $book_value = floatval($row_2['book_stock']);
                    $physical_stock = floatval($row_2['current_physical_stock']);
                    $task_id = $row_2['task_id'];

                    $created_at = $row_2['created_at'];
                    $last_visit_id = $row_2['last_visit_id'];
                    $last_recon_date = fetchLastVisitDate($last_visit_id, $task_id, 'stock_variation');

                    $total_days = calculateDaysBetweenDates($last_recon_date, $created_at);

                    $created_at = $row_2['created_at'];
                    if ($total_days != 0) {

                        if ($variance < 1000 && $variance > -1000) {
    
                        } else {
    
    
                            if ($book_value > $physical_stock) {
                                // Convert variance to float, take absolute value, and add to total_dumping
                                // echo $variance .'<br>';
                                $total_dumping += abs($variance);
                            }
    
                            if ($physical_stock > $book_value) {
                                // echo "Variance is less than 1000 and greater than -1000.";
                                $total_external += abs($variance);
    
                            }
    
                        }
                    }



                }
            } else {
                echo "Error fetching stock recon data: " . $db->error;
            }

            // Prepare final response array
            $report_data = [
                "id" => $tm_id,
                "tm_name" => $tm_name,
                "region" => $region,
                "total_dumping" => $total_dumping,
                "total_external" => $total_external,
                "total_site" => $total_site,
                "total_count" => $total_count,
                "distinct_count" => $distinct_count,
                "rank" => $per,
            ];

            $thread[] = $report_data;
        }

        usort($thread, function ($a, $b) {
            return $b['rank'] - $a['rank']; // Descending Order (Greatest rank first)
        });


        echo json_encode($thread, JSON_PRETTY_PRINT);

    } else {
        echo json_encode(["error" => "Wrong Key"]);
    }
} else {
    echo json_encode(["error" => "Key is Required"]);
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