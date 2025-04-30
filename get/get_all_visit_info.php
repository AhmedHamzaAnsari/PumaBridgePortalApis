<?php
//fetch.php  
include("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
if ($pass != '') {
    $id = $_GET["id"];
    $from = $_GET["from"];
    $to = $_GET["to"];


    if ($pass == $access_key) {
        $sql_query1 = "SELECT 
        dd.id,
        dd.sap_no,
        dd.name as dealer_name,
        GROUP_CONCAT(DISTINCT DATE(it.time) ORDER BY it.time SEPARATOR ', ') AS dates,  -- Collect unique dates as comma-separated values
        COUNT(DISTINCT it.id) AS visit_count,  -- Count unique tasks
        us.name, 
        us.privilege 
    FROM inspector_task AS it
    JOIN users AS us ON us.id = it.user_id  
    JOIN dealers AS dd ON dd.id = it.dealer_id 
    WHERE DATE(it.time) >= '$from' 
      AND DATE(it.time) <= '$to' 
      AND (it.sales_status = 1 
           OR it.measurement_status = 1 
           OR it.wet_stock_status = 1 
           OR it.dispensing_status = 1 
           OR it.stock_variations_status = 1 
           OR it.inspection = 1)
           AND it.type='Inpection'
GROUP BY dd.id;";

        $result1 = $db->query($sql_query1) or die("Error :" . mysqli_error($db));

        $thread = array();
        while ($user = $result1->fetch_assoc()) {
            $thread[] = $user;
        }
        echo json_encode($thread);

    } else {
        echo 'Wrong Key...';
    }

} 
else 
{
    echo 'Key is Required';
}


?>