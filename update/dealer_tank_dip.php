<?php
include("../config.php");
session_start();
if (isset($_POST)) {


    $user_id = $_POST['user_id'];
    $dealer_id = $_POST['dealer_id'];
    $tank_id = $_POST['tank_id'];
    $old_dip = $_POST['old_dip'];
    $new_dip = $_POST['dip_input'];
    $dip_time = $_POST['date_time'];


    $dip_description = mysqli_real_escape_string($db, $_POST['dip_description']);

    $datetime = date('Y-m-d H:i:s');

    // echo 'HAmza';

    $query = "UPDATE `dealers_lorries` SET 
    `current_dip`='$new_dip',
    `update_time`='$datetime'
    WHERE id=$tank_id";


    if (mysqli_query($db, $query)) {

        logSystemActivity($db, $user_id, 'Updated Dip', 'dealers_lorries', $tank_id, $old_dip,$new_dip );

        $log = "INSERT INTO `dealer_dip_log`
        (`dealer_id`,
        `tank_id`,
        `previous_dip`,
        `current_dip`,
        `datetime`,
        `description`,
        `created_at`,
        `created_by`)
        VALUES
        ('$dealer_id',
        '$tank_id',
        '$old_dip',
        '$new_dip',
        '$dip_time',
        '$dip_description',
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
function logSystemActivity($db, $user_id, $action, $resource, $resource_id, $old_value = '', $new_value = '') {
    $stmt = mysqli_prepare($db, "INSERT INTO system_logs (user_id, timestamp, action, resource, resource_id, old_value, new_value) 
                                 VALUES (?, NOW(), ?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ississ", 
            $user_id, $action, $resource, $resource_id, $old_value, $new_value
        );
        mysqli_stmt_execute($stmt); 
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing system log statement: " . mysqli_error($db);
    }
}
?>