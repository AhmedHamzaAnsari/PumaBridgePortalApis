<?php
include("../config.php");
session_start();
if (isset($_POST)) {


    $user_id = $_POST['user_id'];
    $ledger_amount = $_POST['ledger_amount'];

    $ledger_old_value = mysqli_real_escape_string($db, $_POST['ledger_old_value']);
    $dealer_id = mysqli_real_escape_string($db, $_POST['dealer_id']);
    $ledger_description = mysqli_real_escape_string($db, $_POST['ledger_description']);

    $datetime = date('Y-m-d H:i:s');

    // echo 'HAmza';



    $query = "UPDATE `dealers` SET 
    `acount`='$ledger_amount' WHERE id=$dealer_id";


    if (mysqli_query($db, $query)) {


        $log = "INSERT INTO `dealer_ledger_log`
        (`dealer_id`,
        `old_ledger`,
        `new_ledger`,
        `datetime`,
        `description`,
        `created_at`,
        `created_by`)
        VALUES
        ('$dealer_id',
        '$ledger_old_value',
        '$ledger_amount',
        '$datetime',
        '$ledger_description',
        '$datetime',
        '$user_id');";
        if (mysqli_query($db, $log)) {
            logSystemActivity($db, $user_id, 'Updated Dealer', 'dealer_ledger_log', $dealer_id, $ledger_old_value, $ledger_amount );

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