<?php
//fetch.php  
include("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
$from = $_GET["from"];
$to = $_GET["to"];
$tm_id = $_GET["tm_id"];
if ($pass != '') {
    // $id = $_GET["id"];
    if ($pass == $access_key) {
        $sql_query1 = "SELECT it.*,us.name, dd.name as dealer_name, CASE
        WHEN it.status = 0 THEN 'Pending'
        WHEN it.status = 1 THEN 'Complete'
        WHEN it.status = 2 THEN 'Cancel'
        
        END AS current_status,
        CASE
        WHEN us.privilege = 'ZM' THEN 'GRM'
        WHEN us.privilege = 'TM' THEN 'RM'
        WHEN us.privilege = 'ASM' THEN 'TM'
        
        END AS privilege,
        tr.created_at as visit_close_time,
        (SELECT id FROM inspector_task where dealer_id=it.dealer_id and id!=it.id and id<it.id and stock_variations_status=1 order by id desc limit 1) as last_visit_id,
        tr.dealer_sign
        FROM inspector_task as it 
        join users us on us.id=it.user_id  
        left join inspector_task_response as tr on tr.task_id=it.id
        JOIN 
            dealers AS dd ON dd.id = it.dealer_id where it.user_id = '$tm_id' and it.time>='$from' and it.time<='$to' group by it.id
        order by it.id desc";

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