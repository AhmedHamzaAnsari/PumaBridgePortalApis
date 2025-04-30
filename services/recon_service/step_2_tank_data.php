<?php
// fetch.php  
include("../../config.php");
set_time_limit(500);

$access_key = '03201232927';
$pass = $_GET["key"];
$date = date('Y-m-d H:i:s');

if (!empty($pass)) {
    if ($pass === $access_key) {

        $sql_query1 = "SELECT * FROM dealer_stock_recon_new WHERE tanks = ''";
        $result1 = $db->query($sql_query1) or die("Error: " . mysqli_error($db));

        if ($result1->num_rows > 0) {

            while ($user = $result1->fetch_assoc()) {
                $id = $user['id'];
                $task_id = $user['task_id'];
                $dealer_id = $user['dealer_id'];
                $product_id = $user['product_id'];

                $json_data = [];
                $sum_old = 0;
                $sum_new = 0;

                $recon_product = "SELECT rr.*, pp.name AS product_name, tt.lorry_no, tt.min_limit, tt.max_limit,tt.id
                    FROM dealer_wet_stock AS rr
                    JOIN dealers_products AS pp ON pp.id = rr.product_id 
                    JOIN dealers_lorries AS tt ON tt.id = rr.tank_id 
                    WHERE rr.task_id = '$task_id' 
                    AND rr.dealer_id = '$dealer_id' 
                    AND rr.product_id = '$product_id' 
                    GROUP BY rr.tank_id";

                $result_recon_product = $db->query($recon_product) or die("Error: " . mysqli_error($db));

                while ($row = $result_recon_product->fetch_assoc()) {
                    $tank_id = $row['tank_id'];
                    $dip_old = (float)$row['dip_old'];
                    $dip_new = (float)$row['dip_new'];
                    $lorry_no = $row['lorry_no'];

                    // Add to totals
                    $sum_old += $dip_old;
                    $sum_new += $dip_new;

                    $json_data[] = [
                        "id" => (string) $tank_id,
                        "name" => $lorry_no,
                        "opening" => (string) $dip_old,
                        "closing" => (string) $dip_new,
                        "opening_dip" => "0",
                        "closing_dip" => "0"
                    ];
                }

                $final_json = $db->real_escape_string(json_encode($json_data, JSON_UNESCAPED_UNICODE));

                $update_tank_data = "UPDATE `dealer_stock_recon_new`
                    SET 
                        `tanks` = '$final_json',
                        `sum_of_opening` = '$sum_old',
                        `sum_of_closing` = '$sum_new'
                    WHERE `id` = '$id'";

                if (mysqli_query($db, $update_tank_data)) {
                    echo "✅ Updated recon ID: $id — Sum Old: $sum_old, Sum New: $sum_new<br>";
                } else {
                    echo "❌ Failed to update ID: $id — " . mysqli_error($db) . "<br>";
                }
            }

        } else {
            echo "ℹ️ No records with empty tanks found.<br>";
        }

    } else {
        echo '❌ Wrong Key.';
    }

} else {
    echo '❗ Key is Required.';
}
?>
