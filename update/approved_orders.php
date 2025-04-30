<?php
include("../config.php");
session_start();
if (isset($_POST)) {


    $user_id = $_POST['user_id'];
    $order_approval = $_POST['order_approval'];
    $approved_order_status = mysqli_real_escape_string($db, $_POST['approved_order_status']);
    $approved_order_description = mysqli_real_escape_string($db, $_POST['approved_order_description']);
    $datetime = date('Y-m-d H:i:s');
    $val = '';

    // echo 'HAmza';



    $query = "UPDATE `order_main` SET 
    `status`='$approved_order_status',
    `comment`='$approved_order_description',
    `approved_time`='$datetime' WHERE id=$order_approval";


    if (mysqli_query($db, $query)) {

        if($approved_order_status!=1){
            $val = 'Cancelled';
        }
        else{
            $val = 'Approved';
            
        }
        $log = "INSERT INTO `order_detail_log`
        (`order_id`,
        `status`,
        `status_value`,
        `description`,
        `created_at`,
        `created_by`)
        VALUES
        ('$order_approval',
        '$approved_order_status',
        '$val',
        '$approved_order_description',
        '$datetime',
        '$user_id');";
        if (mysqli_query($db, $log)) {
            $output = 1;

        } else {
            $output = 'Error' . mysqli_error($db) . '<br>' . $log;

        }

    } else {
        $output = 'Error' . mysqli_error($db) . '<br>' . $query;

    }




    echo $output;
}
?>