<?php
//fetch.php  
include("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
$dealer_id = $_GET["dealer_id"];
if ($pass != '') {
    if ($pass == $access_key) {
        $sql_query1 = "SELECT dl.*,zm.name as zm_name,tm.name as tm_name,asm.name as asm_name FROM dealers as dl
        left join users as zm on zm.id=dl.zm
        left join users as tm on tm.id=dl.tm
        left join users as asm on asm.id=dl.asm where dl.id=$dealer_id;";

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