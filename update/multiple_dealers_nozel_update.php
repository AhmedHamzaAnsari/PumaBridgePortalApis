<?php
include("../config.php");
// session_start();


if (isset($_POST)) {
    $user_id = $_POST['user_id'];

    $dealers_count = count($_POST['dealers']);


    $products_name = $_POST['products_name'];
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $indent_price = $_POST['indent_price'];
    $nozel_price = $_POST['nozel_price'];
    $products_description = $_POST['products_description'];

    $date = date('Y-m-d H:i:s');
    $output = '';
    // echo 'HAmza';
    if ($dealers_count > 0) {
        for ($i = 0; $i < $dealers_count; $i++) {
            $dealers = $_POST["dealers"][$i];

            $sql = "SELECT * FROM dealers_products where dealer_id=$dealers and name='$products_name'";

            // echo $sql;

            $result = mysqli_query($db, $sql);
            $row = mysqli_fetch_array($result);
            $count = mysqli_num_rows($result);

            if($count>0){
                $product_id = $row['id'];
                $query = "UPDATE `dealers_products`
                SET 
                `from` = '$from_date',
                `to` = '$to_date',
                `indent_price` = '$indent_price',
                `nozel_price` = '$nozel_price',
                `update_time` = '$date'
                WHERE `id` = $product_id;";
    
                if (mysqli_query($db, $query)) {
    
                     $backlog = "INSERT INTO `dealer_nozel_price_log`
                    (`dealer_id`,
                    `product_id`,
                    `indent_price`,
                    `nozel_price`,
                    `from`,
                    `to`,
                    `description`,
                    `created_at`,
                    `created_by`)
                    VALUES
                    ('$dealers',
                    '$product_id',
                    '$indent_price',
                    '$nozel_price',
                    '$from_date',
                    '$to_date',
                    '$products_description',
                    '$date',
                    '$user_id');";
                    if (mysqli_query($db, $backlog)) {
                        $output = 1;
    
                    } else {
    
                        $output = 'Error' . mysqli_error($db) . '<br>' . $backlog;
                    }
    
    
                } else {
                    $output = 'Error' . mysqli_error($db) . '<br>' . $query;
    
                }
            }

        }
    }




    echo $output;
}
?>