<?php
include ("../config.php");
session_start();
if (isset($_POST)) {


    $task_id = $_POST['task_id'];

    $recon_approval = isset($_POST['recon_approval']) && $_POST['recon_approval'] == 1 ? 1 : 0;
    $inspection = isset($_POST['inspection']) && $_POST['inspection'] == 1 ? 1 : 0;
    $status = $_POST['status'];
    
    $comment = $_POST['comment'];
    $rm_id = $_POST['rm_id'];

    $date = date('Y-m-d H:i:s');
    $val = '';

    // echo 'HAmza';



    $query = "UPDATE `inspector_task_response`
    SET
    `recon_approval` = '$recon_approval',
    `inspection` = '$inspection',
    `comment` = '$comment',
    `approved_status` = '$status',
    `approved_at` = '$date',
    `approved_by` = '$rm_id'
    WHERE `task_id` = '$task_id';";


    if (mysqli_query($db, $query)) {

        $query1 = "UPDATE `inspector_task`
            SET
            `approve_status` = '$status',
            `approved_decline_time` = '$date'
            WHERE `id` = '$task_id';";


        if (mysqli_query($db, $query1)) {


            $output = 1;

        } else {
            $output = 'Error' . mysqli_error($db) . '<br>' . $query1;

        }


        // $output = 1;

    } else {
        $output = 'Error' . mysqli_error($db) . '<br>' . $query;

    }




    echo $output;
}
?>