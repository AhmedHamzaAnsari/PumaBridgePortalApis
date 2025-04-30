<?php
// fetch.php  
include("../../config.php");
set_time_limit(500);

$access_key = '03201232927';
$pass = $_GET["key"];
$date = date('Y-m-d H:i:s');

if (!empty($pass)) {
    if ($pass === $access_key) {

        $sql_query1 = "SELECT * FROM dealer_stock_recon_new WHERE nozzel = ''";
        $result1 = $db->query($sql_query1) or die("Error: " . mysqli_error($db));

        if ($result1->num_rows > 0) {

            while ($user = $result1->fetch_assoc()) {
                $id = $user['id'];
                $task_id = $user['task_id'];
                $dealer_id = $user['dealer_id'];
                $product_id = $user['product_id'];

                $json_data = [];
                $sum_sales = 0;
                $sum_old = 0;
                $sum_new = 0;

                $recon_product = "SELECT rr.*, pp.name AS product_name, tt.name AS nozle_name, dp.name AS dispensor_name 
                    FROM dealer_reconcilation AS rr 
                    JOIN dealers_products AS pp ON pp.id = rr.product_id 
                    JOIN dealers_nozzel AS tt ON tt.id = rr.nozle_id 
                    JOIN dealers_dispenser AS dp ON dp.id = rr.dispenser_id 
                    WHERE rr.task_id = '$task_id' 
                    AND rr.dealer_id = '$dealer_id' 
                    AND rr.product_id = '$product_id' 
                    GROUP BY rr.nozle_id";

                $result_recon_product = $db->query($recon_product) or die("Error: " . mysqli_error($db));

                while ($row = $result_recon_product->fetch_assoc()) {
                    $nozle_id = $row['nozle_id'];
                    $old_reading = (float)$row['old_reading'];
                    $new_reading = (float)$row['new_reading'];
                    $nozle_name = $row['nozle_name'];
                    $dispensor_name = $row['dispensor_name'];
                    $dispenser_id = $row['dispenser_id'];

                    $sales = $new_reading - $old_reading;

                    // Add to totals
                    $sum_old += $old_reading;
                    $sum_new += $new_reading;
                    $sum_sales += $sales;

                    $json_data[] = [
                        "id" => (string)$nozle_id,
                        "name" => $nozle_name,
                        "opening" => (string)$old_reading,
                        "closing" => (string)$new_reading,
                        "dispencer_id" => (string)$dispenser_id,
                        "dispenser_name" => $dispensor_name
                    ];
                }

                $final_json = $db->real_escape_string(json_encode($json_data, JSON_UNESCAPED_UNICODE));

                $update_tank_data = "UPDATE `dealer_stock_recon_new`
                    SET 
                        `nozzel` = '$final_json',
                        `is_totalizer_data` = '[]',
                        `total_sales` = '$sum_sales'
                    WHERE `id` = '$id'";

                if (mysqli_query($db, $update_tank_data)) {
                    echo "✅ Updated recon ID: $id — Sum Old: $sum_old, Sum New: $sum_new<br>";
                } else {
                    echo "❌ Failed to update ID: $id — " . mysqli_error($db) . "<br>";
                }
            }

        } else {
            echo "ℹ️ No records with empty nozzel found.<br>";
        }

    } else {
        echo '❌ Wrong Key.';
    }

} else {
    echo '❗ Key is Required.';
}
?>
