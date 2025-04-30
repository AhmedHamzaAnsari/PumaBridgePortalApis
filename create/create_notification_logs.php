<?php
include ("../config.php");
session_start();
if (isset($_GET)) {
    $user_id = $_GET['user_id'];
    $dealers_id = mysqli_real_escape_string($db, $_GET["dealers_id"]);
    $msg = mysqli_real_escape_string($db, $_GET["msg"]);


    $date = date('Y-m-d H:i:s');



    $query = "INSERT INTO `user_log`
        (`table_name`,
        `table_id`,
        `message`,
        `created_at`,
        `created_by`)
        VALUES
        ('dealers',
        '$dealers_id',
        '$msg',
        '$date',
        '$user_id');";


    if (mysqli_query($db, $query)) {


        $output = 1;

        $query = "UPDATE `dealers` SET `no_of_msg_send`=`no_of_msg_send` + 1 WHERE id='$dealers_id'";

        if (mysqli_query($db, $query)) {

            $output = 1;
        } else {

            $output = 0;
        }

    } else {
        $output = 'Error' . mysqli_error($db) . '<br>' . $query;

    }




    echo $output;
}
?>