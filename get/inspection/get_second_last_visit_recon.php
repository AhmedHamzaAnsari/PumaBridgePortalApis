<?php
//fetch.php  
include("../../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
$id = $_GET["id"];
$report = $_GET["report"];
// $id=$_GET["id"];
if ($pass != '') {
    if ($pass == $access_key) {
        $sql_query1 = "";
      
        if($report=='despensing_unit'){
            $sql_query1 = "SELECT * FROM dealer_reconcilation where task_id=$id  order by id desc";
            
        }else if($report=='inspection'){
            $sql_query1 = "SELECT * FROM survey_response_main where inspection_id=$id order by id desc";

        }
        else if($report=='sales_performance'){
            $sql_query1 = "SELECT * FROM dealer_target_response_return where task_id=$id order by id desc";

        } else if($report=='price_measurement'){
            $sql_query1 = "SELECT * FROM dealer_measurement_pricing_action where task_id=$id order by id desc";

        }
        else if($report=='wet_stock'){
            $sql_query1 = "SELECT * FROM dealer_wet_stock where task_id=$id order by id desc";

        }else if($report=='stock_variation'){
            $sql_query1 = "SELECT * FROM dealers_stock_variations where task_id=$id order by id desc";

        }

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