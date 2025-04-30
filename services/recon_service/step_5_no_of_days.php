<?php
// fetch.php  
include("../../config.php");
set_time_limit(500);

$access_key = '03201232927';
$pass = $_GET["key"];
$date = date('Y-m-d H:i:s');

if (!empty($pass)) {
    if ($pass === $access_key) {

        $sql_query1 = "SELECT 
                it.*,
                (
                    SELECT id 
                    FROM inspector_task 
                    WHERE dealer_id = it.dealer_id 
                        AND id < it.task_id 
                        AND stock_variations_status = 1 
                    ORDER BY id DESC 
                    LIMIT 1
                ) AS last_visit_id  
            FROM dealer_stock_recon_new AS it 
            WHERE it.total_days = '' 
            GROUP BY it.product_id
        ";

        $result1 = $db->query($sql_query1) or die("Error: " . mysqli_error($db));

        if ($result1->num_rows > 0) {
            while ($user = $result1->fetch_assoc()) {
                $id = $user['id'];
                $task_id = $user['task_id'];
                $dealer_id = $user['dealer_id'];
                $product_id = $user['product_id'];
                $last_visit_id = $user['last_visit_id'];
                $current_recon_date = $user['created_at'];
                $total_sales = (float)$user['total_sales'];

                if (empty($last_visit_id)) {
                    echo "⚠️ No previous visit found for Recon ID: $id<br>";
                    continue;
                }

                // Get the last recon date from dealers_stock_variations
                $recon_product = "
                    SELECT created_at 
                    FROM dealers_stock_variations 
                    WHERE task_id = '$last_visit_id' AND product_id = '$product_id' 
                    ORDER BY id DESC 
                    LIMIT 1
                ";
                $result_recon_product = $db->query($recon_product) or die("Error: " . mysqli_error($db));

                if ($result_recon_product->num_rows > 0) {
                    $row = $result_recon_product->fetch_assoc();
                    $last_recon_date = $row['created_at'];

                    $datetime1 = new DateTime($current_recon_date);
                    $datetime2 = new DateTime($last_recon_date);

                    $interval = $datetime1->diff($datetime2);
                    $no_of_days = max(1, $interval->days); // prevent division by 0

                    $average_daily_sales = round($total_sales / $no_of_days, 2);

                    $update_query = "
                        UPDATE dealer_stock_recon_new
                        SET 
                            total_days = '$no_of_days',
                            last_recon_date = '$last_recon_date',
                            average_daily_sales = '$average_daily_sales'
                        WHERE id = '$id'
                    ";

                    if ($db->query($update_query)) {
                        echo "✅ Updated recon ID: $id — Days: $no_of_days, Avg Daily Sales: $average_daily_sales<br>";
                    } else {
                        echo "❌ Failed to update ID: $id — " . $db->error . "<br>";
                    }

                } else {
                    echo "⚠️ No previous stock variation found for Task ID $last_visit_id and Product $product_id<br>";
                }
            }
        } else {
            echo "ℹ️ No records with empty total_days found.<br>";
        }

    } else {
        echo '❌ Wrong Key.';
    }
} else {
    echo '❗ Key is Required.';
}
?>
