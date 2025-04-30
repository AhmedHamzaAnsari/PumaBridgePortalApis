<?php
//fetch.php  
include("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
if ($pass != '') {
    if ($pass == $access_key) {
        $pin = $_GET['pin'];
        $imei = $_GET['imei'];
        $email = $_GET['email'];

        $sql_query1 = "SELECT * FROM users where description='$pin' and login_imei='$imei' and login='$email' and is_verify=1;";

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