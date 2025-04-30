<?php
//fetch.php  
include("../../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
if ($pass != '') {
    if ($pass == $access_key) {
        $id = $_GET["id"];
        $pre = $_GET["pre"];

        if($pre == 'ZM'){

            $sql_query1 = "SELECT dd.*,dl.name as dealer_name
            FROM dealers as dl 
            join dealers_dispenser as dd on dd.dealer_id=dl.id
            where dl.zm=$id order by dl.id desc";
        }
        elseif($pre == 'TM'){
            
            $sql_query1 = "SELECT dd.*,dl.name as dealer_name
            FROM dealers as dl 
            join dealers_dispenser as dd on dd.dealer_id=dl.id
            where dl.tm=$id order by dl.id desc";
        }
        else{
            $sql_query1 = "SELECT dd.*,dl.name as dealer_name
            FROM dealers as dl 
            join dealers_dispenser as dd on dd.dealer_id=dl.id
            where dl.asm=$id order by dl.id desc";

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