<?php
header("Content-Type: application/json");

// Connect to your MySQL database
include("../config.php");

$access_key = '03201232927';
$pass = $_GET["key"];

if (!empty($pass) && $pass == $access_key) {
    $dealer_id = intval($_GET["dealer_id"]);
    $from = $db->real_escape_string($_GET["from"]);
    $to = $db->real_escape_string($_GET["to"]);

    // Initialize an array to store the formatted data
    $formatted_data = [];

    // Query to fetch records
    $sql = "SELECT it.*, dl.name as dealer_name, dl.`co-ordinates` as co_ordinates, usz.name as zm_name, ust.name as tm_name, usa.name as asm_name, dl.region, dl.id as dealer_id, dl.sap_no as dealer_sap
            FROM inspector_task as it
            JOIN dealers as dl ON dl.id = it.dealer_id
            JOIN users as usz ON usz.id = dl.zm
            JOIN users as ust ON ust.id = dl.tm
            JOIN users as usa ON usa.id = dl.asm
            WHERE dl.id = $dealer_id
            AND DATE(it.time) >= '$from'
            AND DATE(it.time) <= '$to';";

    $result = $db->query($sql);

    while ($row = $result->fetch_assoc()) {
        $task_id = $row["id"];
        $dealer_sap = $row["dealer_sap"];

        // Query to get stock recon data
        $get_orders = "SELECT rs.*, us.name, pp.name as product_name,
                        (SELECT id FROM inspector_task WHERE dealer_id = it.dealer_id AND id != it.id AND id < it.id AND stock_variations_status = 1 ORDER BY id DESC LIMIT 1) as last_visit_id
                        FROM dealers_stock_variations as rs
                        JOIN dealers_products as pp ON pp.id = rs.product_id
                        JOIN users as us ON us.id = rs.created_by
                        JOIN inspector_task as it ON it.id = rs.task_id
                        WHERE rs.dealer_id = $dealer_id 
                        AND DATE(rs.created_at) >= '$from' 
                        AND DATE(rs.created_at) <= '$to'
                        AND rs.task_id='$task_id'
                        GROUP BY rs.product_id, rs.task_id
                        ORDER BY rs.id ASC";

        $result_orders = $db->query($get_orders);
        $row_count = mysqli_num_rows($result_orders);

        if ($row_count > 0) {
            while ($row_2 = $result_orders->fetch_assoc()) {
                $record_data = [
                    'dealer_sap' => $row["dealer_sap"],
                    'site' => $row["dealer_name"],
                    'zm_name' => $row["zm_name"],
                    'tm_name' => $row["tm_name"],
                    'asm_name' => $row["asm_name"],
                    'region' => $row["region"],
                    'plan_time' => $row["time"],
                    'no_of_days' => '---',
                    'product_name' => '---',
                    'inspection_date_current' => '---',
                    'inspection_date_last' => '----',
                    'opening_stock' => '----',
                    'current_physical_stock' => '----',
                    'book_stock' => '----',
                    'sales' => '---',
                    'daily_sales' => '---',
                    'monthly_sales' => '---',
                    'receipts' => '---',
                    'loss_gain' => '---',
                    'DU1' => '---',
                    'DU2' => '---',
                    'DU3' => '---',
                    'DU4' => '---',
                    'DU5' => '---',
                    'DU6' => '---',
                    'DU7' => '---',
                    'DU8' => '---',
                    'ogra_price' => '---',
                    'pump_price' => '---',
                    'variance' => '---',
                ];

                $last_visit_id = $row_2['last_visit_id'];
                $product_id = $row_2['product_id'];
                $current_recon_date = $row_2['created_at'];
                $total_sales = $row_2['sales_as_per_meter_reading'];
                $product_name = $row_2['product_name'];

                $sql_last = "SELECT * FROM dealers_stock_variations 
                             WHERE task_id = '$last_visit_id' AND product_id = $product_id 
                             GROUP BY product_id";

                $result_sql_last = $db->query($sql_last);
                $row_sql_last = $result_sql_last->fetch_assoc();

                $last_visit_date = '';
                $no_of_days = 0;
                $avg_daily_sale = 0;
                $avg_month_sale = 0;

                if ($row_sql_last) {
                    $last_visit_date = $row_sql_last['created_at'];

                    // Calculate the difference in days
                    $date1 = new DateTime($last_visit_date);
                    $date2 = new DateTime($current_recon_date);
                    $interval = $date1->diff($date2);
                    $no_of_days = $interval->days;

                    if ($no_of_days != 0) {
                        $avg_daily_sale = $total_sales / $no_of_days;
                        $avg_month_sale = $avg_daily_sale * 30;
                    }
                } else {
                    $last_visit_date = 'First Time';
                }

                // Query to get the measurement pricing details
                $sql_price = "SELECT * FROM dealer_measurement_pricing_action WHERE task_id='$task_id';";
                $result_price = mysqli_query($db, $sql_price);
                $row_price = mysqli_fetch_assoc($result_price);

                // Update the record data based on the product type
                $measurement_id = $row_price['id'];
                if ($product_name == 'PMG') {
                    $record_data['ogra_price'] = $row_price['pmg_ogra_price'];
                    $record_data['pump_price'] = $row_price['pmg_pump_price'];
                    $record_data['variance'] = $row_price['pmg_variance'];
                } else {
                    $record_data['ogra_price'] = $row_price['hsd_ogra_price'];
                    $record_data['pump_price'] = $row_price['hsd_pump_price'];
                    $record_data['variance'] = $row_price['hsd_variance'];
                }

                // Fetch detailed measurement data
                $sql_query1 = "SELECT * FROM dealer_measurement_pricing WHERE main_id='$measurement_id';";
                $result1 = $db->query($sql_query1) or die("Error :" . mysqli_error($db));

                $du = 1;
                while ($user = $result1->fetch_assoc()) {
                    if ($product_name == 'PMG') {
                        $record_data['DU' . $du] = $user['pmg_accurate'];
                    } else {
                        $record_data['DU' . $du] = $user['hsd_accurate'];
                    }
                    $du++;
                }

                // Update the rest of the record data
                $record_data['no_of_days'] = $no_of_days;
                $record_data['inspection_date_current'] = $current_recon_date;
                $record_data['product_name'] = $product_name;
                $record_data['inspection_date_last'] = $last_visit_date;
                $record_data['daily_sales'] = round($avg_daily_sale);
                $record_data['monthly_sales'] = round($avg_month_sale);
                $record_data['sales'] = round($row_2['sales_as_per_meter_reading']);
                $record_data['receipts'] = round($row_2['purchase_during_inspection_period']);
                $record_data['loss_gain'] = round($row_2['gain_loss']);
                $record_data['opening_stock'] = round($row_2['opening_stock']);
                $record_data['current_physical_stock'] = round($row_2['current_physical_stock']);
                $record_data['book_stock'] = round($row_2['book_stock']);



                // Add the record to the formatted data array
                $formatted_data[] = $record_data;
            }
        }
    }

    // Output the JSON string
    echo json_encode($formatted_data, JSON_PRETTY_PRINT);

} else {
    echo json_encode(["error" => "Invalid or missing key"]);
}
?>