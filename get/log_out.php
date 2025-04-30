<?php
//fetch.php  
include("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
if ($pass != '') {
    if ($pass == $access_key) {
        $user_id = $_GET['user_id'];
        // echo $count;
        $log = "INSERT INTO `user_login_log`
        (`type`,
        `user_id`,
        `created_at`,
        `created_by`)
        VALUES
        ('Logout',
        '$user_id',
        Now(),
        '$user_id');";

        mysqli_query($db, $log);

    } else {
        echo 'Wrong Key...';
    }

} else {
    echo 'Key is Required';
}


?>