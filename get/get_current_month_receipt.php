<?php
//fetch.php  
include("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
if ($pass != '') {
    $dealer_id = $_GET["dealer_id"];
    if ($pass == $access_key) {
        $sql_query1 = "SELECT dp.name as product_name,sum(purchase_during_inspection_period) as total_purchase
        FROM dealers_stock_variations as ss
        join dealers_products as dp on dp.id=ss.product_id
        WHERE ss.dealer_id = '$dealer_id' 
          AND YEAR(ss.created_at) = YEAR(CURDATE()) 
          AND MONTH(ss.created_at) = MONTH(CURDATE()) 
          group by ss.product_id
        ORDER BY ss.id DESC;";

        $result1 = $db->query($sql_query1) or die("Error :" . mysqli_error($db));

        $thread = array();
        while ($user = $result1->fetch_assoc()) {
            $thread[] = $user;
        }
        echo json_encode($thread);

    } else {
        echo 'Wrong Key...';
    }

} else {
    echo 'Key is Required';
}


?>