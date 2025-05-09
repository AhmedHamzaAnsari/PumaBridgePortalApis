<?php
include("../config.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize & validate inputs
    $checkboxValue = intval($_POST['checkboxValue']);
    $dealers_id = intval($_POST['dealer_id']);
    $disp_id = intval($_POST['disp_id']);
    $user_id = $_POST['user_id']; // Assuming the user is logged in and user_id is stored in session

    // 1. Update Dispenser
    $query_dis = "UPDATE dealers_dispenser 
                  SET status = $checkboxValue 
                  WHERE dealer_id = $dealers_id AND id = $disp_id";
    if (!mysqli_query($db, $query_dis)) {
        die("Error update dealers_dispenser: " . mysqli_error($db));
    }

    // Log dispenser update
    logSystemActivity($db, $user_id, 'UPDATE dealer dispenser  status', 'dealers_dispenser', $disp_id, '', $checkboxValue);

    // 2. Update Nozzles
    $query_nozzel = "UPDATE dealers_nozzel 
                     SET status = $checkboxValue 
                     WHERE dealer_id = $dealers_id AND dispenser_id = $disp_id";
    if (!mysqli_query($db, $query_nozzel)) {
        die("Error update dealers_nozzel: " . mysqli_error($db));
    }

    // Log nozzles update
    logSystemActivity($db, $user_id, 'UPDATED dealer nozzel status', 'dealers_nozzel', $disp_id, '', $checkboxValue);


    echo 1;
} else {
    echo 0;
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
