<?php
include("../config.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs to prevent SQL injection
    $nozel_last_recon = mysqli_real_escape_string($db, $_POST['nozel_last_recon']);
    $nozel_new_reading = mysqli_real_escape_string($db, $_POST['nozel_new_reading']);
    $recon_id = mysqli_real_escape_string($db, $_POST['recon_id']);
    $nozel_id = mysqli_real_escape_string($db, $_POST['nozel_id']);
    $user_id = mysqli_real_escape_string($db, $_POST['user_id']);
    $dealer_id = mysqli_real_escape_string($db, $_POST['dealer_id']);
    $last_task_id = mysqli_real_escape_string($db, $_POST['last_task_id']);
    $recon_product_id = mysqli_real_escape_string($db, $_POST['recon_product_id']);
    $recon_description = mysqli_real_escape_string($db, $_POST['recon_description']);
    $datetime = date('Y-m-d H:i:s');

    // Update dealer reconciliation with the new nozzle reading
    $query = "UPDATE `dealer_reconcilation`
              SET `new_reading` = '$nozel_new_reading'
              WHERE `id` = '$recon_id' AND `nozle_id` = '$nozel_id';";

    if (mysqli_query($db, $query)) {
        // Log the update in dealer_reconcilation_update_log
        $log = "INSERT INTO `dealer_reconcilation_update_log`
                (`recon_id`, `nozel_id`, `dealer_id`, `task_id`, `product_id`, 
                `old_reading`, `new_reading`, `description`, `created_at`, `created_by`)
                VALUES ('$recon_id', '$nozel_id', '$dealer_id', '$last_task_id', '$recon_product_id',
                '$nozel_last_recon', '$nozel_new_reading', '$recon_description', '$datetime', '$user_id');";

        if (mysqli_query($db, $log)) {
            $output = 1;

            // Call the recon_sales_update function to update the sales info
            recon_sales_update($last_task_id, $recon_product_id, $dealer_id, $db);
        } else {
            $output = 'Error in log insertion: ' . mysqli_error($db);
        }
    } else {
        $output = 'Error in update: ' . mysqli_error($db);
    }

    echo $output;
}

// Function to update sales in dealer_stock_variations table
function recon_sales_update($task_id, $product_id, $dealer_id, $db) {
    $get_recon = "SELECT SUM(new_reading - old_reading) AS sales 
                  FROM dealer_reconcilation 
                  WHERE task_id = '$task_id' 
                  AND product_id = '$product_id' 
                  AND dealer_id = '$dealer_id';";

    $result = mysqli_query($db, $get_recon);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $sales = $row['sales'];

        // Update dealer stock variations with the calculated sales
        $update_sql = "UPDATE `dealers_stock_variations`
                       SET `sales_as_per_meter_reading` = '$sales'
                       WHERE task_id = '$task_id' 
                       AND product_id = '$product_id' 
                       AND dealer_id = '$dealer_id';";

        if (!mysqli_query($db, $update_sql)) {
            echo 'Error in updating stock variations: ' . mysqli_error($db);
        }
    } else {
        echo 'Error in calculating sales: ' . mysqli_error($db);
    }
}
?>
