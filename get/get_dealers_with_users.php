<?php
//fetch.php  
include("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
if ($pass != '') {
    if ($pass == $access_key) {
        $sql_query1 = "SELECT 
        dl.id,dl.name as dealer_name,us.name as zm,tm.name as tm,asm.name as asm,dl.sap_no FROM dealers as dl 
        join users as us on us.id=dl.zm
        join users as tm on tm.id=dl.tm
        join users as asm on asm.id=dl.asm;";

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