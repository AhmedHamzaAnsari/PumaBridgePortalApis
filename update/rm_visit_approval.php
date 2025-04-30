<?php
include ("../config.php");
session_start();
if (isset($_POST)) {


    $task_id = $_POST['task_id'];
    $sales_approval = isset($_POST['sales_approval']) && $_POST['sales_approval'] == 1 ? 1 : 0;;
    $measurement_approval = isset($_POST['measurement_approval']) && $_POST['measurement_approval'] == 1 ? 1 : 0;;
    $wet_stock_approval = isset($_POST['wet_stock_approval']) && $_POST['wet_stock_approval'] == 1 ? 1 : 0;;
    $dispensing_approval = isset($_POST['dispensing_approval']) && $_POST['dispensing_approval'] == 1 ? 1 : 0;;
    $stock_variations_approval = isset($_POST['stock_variations_approval']) && $_POST['stock_variations_approval'] == 1 ? 1 : 0;;
    $inspection = isset($_POST['inspection']) && $_POST['inspection'] == 1 ? 1 : 0;
    
    $comment = $_POST['comment'];
    $rm_id = $_POST['rm_id'];

    $date = date('Y-m-d H:i:s');
    $val = '';

    // echo 'HAmza';



    $query = "UPDATE `inspector_task_response`
    SET
    `sales_approval` = '$sales_approval',
    `measurement_approval` = '$measurement_approval',
    `wet_stock_approval` = '$wet_stock_approval',
    `dispensing_approval` = '$dispensing_approval',
    `stock_variations_approval` = '$stock_variations_approval',
    `inspection` = '$inspection',
    `comment` = '$comment',
    `approved_status` = '1',
    `approved_at` = '$date',
    `approved_by` = '$rm_id'
    WHERE `task_id` = '$task_id';";


    if (mysqli_query($db, $query)) {

        $query1 = "UPDATE `inspector_task`
            SET
            `approve_status` = '1',
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