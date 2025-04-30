<?php
//fetch.php  
include ("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
if ($pass != '') {
    if ($pass == $access_key) {
        $pre = $_GET["pre"];
        $id = $_GET["user_id"];
        $from = $_GET["from"];
        $to = $_GET["to"];
        if ($pre == 'ZM') {

            $sql_query1 = "SELECT at.*,us.name as manager_name,dl.name as dealer_name,CASE
            WHEN at.status = 0 THEN 'Pending'
            WHEN at.status = 1 THEN 'Complete'
            WHEN at.status = 2 THEN 'Cancel'
            END AS current_status,dl.`co-ordinates` as co_ordinates FROM inspector_task as at 
            join users as us on us.id=at.user_id
            join dealers as dl on dl.id=at.dealer_id where  at.time>='$from' and at.time<='$to' and dl.zm=2 group by at.id
            order by at.id desc;";
        } elseif ($pre == 'TM') {

            $sql_query1 = "SELECT at.*,us.name as manager_name,dl.name as dealer_name,CASE
            WHEN at.status = 0 THEN 'Pending'
            WHEN at.status = 1 THEN 'Complete'
            WHEN at.status = 2 THEN 'Cancel'
            END AS current_status,dl.`co-ordinates` as co_ordinates FROM inspector_task as at 
            join users as us on us.id=at.user_id
            join dealers as dl on dl.id=at.dealer_id where  at.time>='$from' and at.time<='$to' and dl.tm=$id group by at.id
            order by at.id desc";
        } elseif ($pre == 'ASM') {
            $sql_query1 = "SELECT at.*,us.name as manager_name,dl.name as dealer_name,CASE
            WHEN at.status = 0 THEN 'Pending'
            WHEN at.status = 1 THEN 'Complete'
            WHEN at.status = 2 THEN 'Cancel'
            END AS current_status,dl.`co-ordinates` as co_ordinates FROM inspector_task as at 
            join users as us on us.id=at.user_id
            join dealers as dl on dl.id=at.dealer_id where  at.time>='$from' and at.time<='$to' and dl.asm=$id group by at.id
            order by at.id desc;";

        } else {

            // $sql_query1 = "SELECT at.*,us.name as manager_name,dl.name as dealer_name,CASE
            // WHEN at.status = 0 THEN 'Pending'
            // WHEN at.status = 1 THEN 'Complete'
            // WHEN at.status = 2 THEN 'Cancel'
            // END AS current_status,tm_ns.name as tm_name FROM inspector_task as at 
            // join users as us on us.id=at.user_id
            // join dealers as dl on dl.id=at.dealer_id 
            // join users_asm_tm as uzt on uzt.asm_id=at.created_by 
            // join users as tm_ns on tm_ns.id=uzt.tm_id
            // where  at.time>='$from' and at.time<='$to' group by at.id
            // order by at.id desc;";

            $sql_query1 = "SELECT at.*,us.name as manager_name,dl.name as dealer_name,CASE
            WHEN at.status = 0 THEN 'Pending'
            WHEN at.status = 1 THEN 'Complete'
            WHEN at.status = 2 THEN 'Cancel'
            END AS current_status,tm_ns.name as tm_name,dl.`co-ordinates` as co_ordinates FROM inspector_task as at 
            join users as us on us.id=at.user_id
            join dealers as dl on dl.id=at.dealer_id 
            join users_asm_tm as uzt on uzt.asm_id=at.created_by 
            join users as tm_ns on tm_ns.id=uzt.tm_id
            where  at.time>='$from' and at.time<='$to' group by at.id
            order by at.id desc;";
        }

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