<?php
include("../config.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkboxValue = isset($_POST['checkboxValue']) ? (int)$_POST['checkboxValue'] : 0;
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $user_id = $_POST['user_id'];


    if ($id > 0) {
        $query = "UPDATE dealers_nozzel SET status = $checkboxValue WHERE id = $id";
        $result = mysqli_query($db, $query);
        // echo $query;
        // exit;
        if ($result) {
            logSystemActivity($db, $user_id, 'UPDATED dealer Nozzel status', 'dealers_nozzel', $id, '', $checkboxValue);

            echo 1;
        } else {
            echo 0; // update failed
        }
    } else {
        echo 0; // invalid ID
    }
} else {
    echo 0; // invalid method
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