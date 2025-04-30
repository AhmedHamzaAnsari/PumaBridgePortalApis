<?php
// fetch.php  
include("../../config.php");
set_time_limit(500);

$access_key = '03201232927';
$pass = $_GET["key"];
$date = date('Y-m-d H:i:s');

if (!empty($pass)) {
    if ($pass === $access_key) {

        // Step 1: Fetch Dealer
        $dealer_query = "SELECT * FROM dealers WHERE privilege = 'Dealer' AND id = 1229";
        $dealer_result = $db->query($dealer_query) or die("Error fetching dealer: " . mysqli_error($db));

        while ($dealer = $dealer_result->fetch_assoc()) {
            $dealer_id = $dealer['id'];

            // Step 2: Get Inspection Tasks
            $task_query = "SELECT * FROM inspector_task 
                           WHERE dealer_id = '$dealer_id' 
                           AND type = 'Inpection' 
                           ORDER BY id DESC";

            $task_result = $db->query($task_query) or die("Error fetching tasks: " . mysqli_error($db));

            if ($task_result->num_rows > 0) {
                while ($task = $task_result->fetch_assoc()) {
                    $task_id    = $task['id'];
                    $user_id    = $task['user_id'];
                    $created_at = $task['created_at'];

                    // Step 3: Check if recon already exists
                    $check_recon = "SELECT * FROM dealer_stock_recon_new WHERE task_id = '$task_id'";
                    $recon_result = $db->query($check_recon) or die("Error checking recon: " . mysqli_error($db));

                    if ($recon_result->num_rows > 0) {
                        echo "✅ Recon data found for Task ID $task_id<br>";
                        while ($recon = $recon_result->fetch_assoc()) {
                            echo "<pre>" . print_r($recon, true) . "</pre>";
                        }
                    } else {
                        echo "⏳ No recon found for Task ID $task_id — creating new rows...<br>";

                        // Step 4: Fetch product IDs from target return
                        $product_query = "SELECT rr.product_id 
                                          FROM dealer_target_response_return AS rr 
                                          JOIN dealers_products AS pp ON pp.id = rr.product_id 
                                          WHERE rr.task_id = '$task_id' AND rr.dealer_id = '$dealer_id' 
                                          GROUP BY rr.product_id";

                        $product_result = $db->query($product_query) or die("Error fetching products: " . mysqli_error($db));

                        while ($product = $product_result->fetch_assoc()) {
                            $product_id = $product['product_id'];

                            // Step 5: Insert new recon row
                            $insert_recon = "INSERT INTO dealer_stock_recon_new 
                                             (task_id, product_id, dealer_id, created_by, created_at)
                                             VALUES 
                                             ('$task_id', '$product_id', '$dealer_id', '$user_id', '$created_at')";

                            if ($db->query($insert_recon)) {
                                echo "✅ Recon row created for Product ID $product_id (Task $task_id)<br>";
                            } else {
                                echo "❌ Error inserting recon for Product ID $product_id: " . $db->error . "<br>";
                            }
                        }
                    }
                }
            } else {
                echo "ℹ️ No inspection tasks found for Dealer ID $dealer_id<br>";
            }
        }

    } else {
        echo '❌ Wrong Key.';
    }
} else {
    echo '❗ Key is Required.';
}
?>
