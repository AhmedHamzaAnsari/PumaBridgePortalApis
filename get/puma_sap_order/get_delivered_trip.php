<?php
//fetch.php  
include("../../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
// $id=$_GET["id"];
if ($pass != '') {
    if ($pass == $access_key) {
        $salesOrders = $_GET["salesOrders"];
        $id = $_GET["id"];


        $sql_query1 = "SELECT *,ps.created_at as trip_start_time FROM order_main as om 
        join puma_sap_data_trips as ps on ps.salesapNo=om.SaleOrder
        where om.SaleOrder=$salesOrders and ps.id=$id";

        $result1 = $db->query($sql_query1) or die("Error :" . mysqli_error());

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