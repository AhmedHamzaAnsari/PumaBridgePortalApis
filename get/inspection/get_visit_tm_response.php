<?php
//fetch.php  
include("../../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
if ($pass != '') {
    $task_id = $_GET["task_id"];
    if ($pass == $access_key) {
        $sql_query1 = "SELECT tr.*,us.name as rm_name FROM inspector_task_response as tr
        join users as us on us.id=tr.approved_by where task_id='$task_id';";

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