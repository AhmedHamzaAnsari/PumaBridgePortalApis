<?php
include("../config.php");
session_start();
if (isset($_POST)) {
    $user_id = $_POST['user_id'];
    $month_name = mysqli_real_escape_string($db, $_POST["month_name"]);
    $targeted_amount = mysqli_real_escape_string($db, $_POST["targeted_amount"]);
    $targeted_product = mysqli_real_escape_string($db, $_POST["targeted_product"]);
    $products_description = mysqli_real_escape_string($db, $_POST["products_description"]);
    $dealer_id = mysqli_real_escape_string($db, $_POST["dealer_id"]);
    $date = date('Y-m-d H:i:s');

    // echo 'HAmza';
    if ($_POST["row_id"] != '') {


    } else {

        $query = "INSERT INTO `dealers_monthly_targets`
        (`date_month`,
        `target_amount`,
        `product_id`,
        `dealer_id`,
        `description`,
        `created_at`,
        `created_by`)
        VALUES
        ('$month_name',
        '$targeted_amount',
        '$targeted_product',
        '$dealer_id',
        '$products_description',
        '$date',
        '$user_id');";


        if (mysqli_query($db, $query)) {
            logSystemActivity($db, $user_id, 'Products', 'dealers_monthly_targets', $dealer_id);

            $output = 1;

        } else {
            $output = 'Error' . mysqli_error($db) . '<br>' . $query;

        }
    }



    echo $output;
}
function logSystemActivity($db, $user_id, $action, $resource, $resource_id, $old_value = '', $new_value = '')
{
    $stmt = mysqli_prepare($db, "INSERT INTO system_logs (user_id, timestamp, action, resource, resource_id, old_value, new_value) 
                                     VALUES (?, NOW(), ?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param(
            $stmt,
            "ississ",
            $user_id,
            $action,
            $resource,
            $resource_id,
            $old_value,
            $new_value
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing system log statement: " . mysqli_error($db);
    }
}
?>