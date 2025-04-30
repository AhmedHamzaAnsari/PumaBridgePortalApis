<?php
//fetch.php  
include ("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
$pre = $_GET["pre"];
$id = $_GET["user_id"];
if ($pass != '') {
    if ($pass == $access_key) {
       

        if ($pre == 'ZM') {

            $sql_query1 = "SELECT dp.*,dl.name as dealer_name FROM dealers_products as dp
            join dealers as dl on dl.id=dp.dealer_id
            join users as usz on usz.id=dl.zm
            join users as ust on ust.id=dl.tm
            join users as usa on usa.id=dl.asm
             order by dl.id desc";
        } elseif ($pre == 'TM') {

            $sql_query1 = "SELECT dp.*,dl.name as dealer_name FROM dealers_products as dp
            join dealers as dl on dl.id=dp.dealer_id
            join users as usz on usz.id=dl.zm
            join users as ust on ust.id=dl.tm
            join users as usa on usa.id=dl.asm
            where dl.tm=$id order by dl.id desc";
        } elseif ($pre == 'ASM') {
            $sql_query1 = "SELECT dp.*,dl.name as dealer_name FROM dealers_products as dp
            join dealers as dl on dl.id=dp.dealer_id
            join users as usz on usz.id=dl.zm
            join users as ust on ust.id=dl.tm
            join users as usa on usa.id=dl.asm
            where dl.asm=$id order by dl.id desc";

        } else {

            $sql_query1 = "SELECT dp.*,dl.name as dealer_name FROM dealers_products as dp
            join dealers as dl on dl.id=dp.dealer_id
            join users as usz on usz.id=dl.zm
            join users as ust on ust.id=dl.tm
            join users as usa on usa.id=dl.asm
            order by dl.id desc ;";
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