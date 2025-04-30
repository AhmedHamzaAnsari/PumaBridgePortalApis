<?php
// fetch.php  
include("../../config.php");
set_time_limit(500);

$access_key = '03201232927';
$pass = $_GET["key"];
$date = date('Y-m-d H:i:s');

if (!empty($pass)) {
    if ($pass === $access_key) {

        $sql_query1 = "SELECT * FROM dealer_stock_recon_new WHERE total_recipt = ''";
        $result1 = $db->query($sql_query1) or die("Error: " . mysqli_error($db));

        if ($result1->num_rows > 0) {

            while ($user = $result1->fetch_assoc()) {
                $id = $user['id'];
                $task_id = $user['task_id'];
                $dealer_id = $user['dealer_id'];
                $product_id = $user['product_id'];
                $sum_of_closing = (float)$user['sum_of_closing'];
                $total_sales = (float)$user['total_sales'];

                $recon_product = "SELECT rr.*, pp.name 
                    FROM dealers_stock_variations AS rr 
                    JOIN dealers_products AS pp ON pp.id = rr.product_id 
                    WHERE rr.task_id = '$task_id' 
                    AND rr.dealer_id = '$dealer_id' 
                    AND rr.product_id = '$product_id' 
                    GROUP BY rr.product_id";

                $result_recon_product = $db->query($recon_product) or die("Error: " . mysqli_error($db));

                while ($row = $result_recon_product->fetch_assoc()) {
                    $total_recipt = (float)$row['purchase_during_inspection_period'];
                    $book_stock = (float)$row['book_stock'];
                    $variance = $sum_of_closing - $book_stock;
                    $created_at = $row['created_at'];

                    // Avoid division by zero
                    $var_per = ($total_sales != 0) ? ($variance / $total_sales) * 100 : 0;

                    $update_query = "UPDATE `dealer_stock_recon_new`
                        SET 
                            `total_recipt` = '$total_recipt',
                            `book_value` = '$book_stock',
                            `variance` = '$variance',
                            `shortage_claim` = '0',
                            `variance_of_sales` = '$var_per',
                            `created_at` = '$created_at'
                        WHERE `id` = '$id'";

                    if (mysqli_query($db, $update_query)) {
                        echo "✅ Updated recon ID: $id — Total Receipt: $total_recipt, Book: $book_stock, Var%: $var_per<br>";
                    } else {
                        echo "❌ Failed to update ID: $id — " . mysqli_error($db) . "<br>";
                    }
                }
            }

        } else {
            echo "ℹ️ No records with empty total_recipt found.<br>";
        }

    } else {
        echo '❌ Wrong Key.';
    }

} else {
    echo '❗ Key is Required.';
}
?>
