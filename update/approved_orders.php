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
        logSystemActivity($db, $user_id, $stat,'Delete Container', 'containers_sizes', $user_id, $approved_order_status, $val);

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
function logSystemActivity($db, $user_id, $action, $resource, $resource_id, $old_value = '', $new_value = '')
{
    $stmt = mysqli_prepare($db, "INSERT INTO system_logs (user_id, timestamp, action, resource, resource_id, old_value, new_value) 
                                     VALUES (?, NOW(), ?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param(
            $stmt,
            "ississ",
            $user_id,
            $action,
            $resource,
            $resource_id,
            $old_value,
            $new_value
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing system log statement: " . mysqli_error($db);
    }
}

?>