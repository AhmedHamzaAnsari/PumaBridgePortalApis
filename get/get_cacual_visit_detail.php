<?php
//fetch.php  
include("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
if ($pass != '') {
    $dealer_id = $_GET["dealer_id"];
    $task_id = $_GET["task_id"];
    if ($pass == $access_key) {
        $sql_query1 = "SELECT * 
        FROM inspector_task AS it
        JOIN dealer_casual_visits AS cv ON DATE(cv.visit_time) = DATE(it.time)
        WHERE it.id = $task_id AND cv.dealer_id = $dealer_id AND it.dealer_id = $dealer_id
        ORDER  BY cv.visit_time DESC;";

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