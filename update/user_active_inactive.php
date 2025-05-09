<?php
include("../config.php");
session_start();

if (isset($_POST['checkboxValue']) && isset($_POST['id'])) {
    $checkboxValue = mysqli_real_escape_string($db, $_POST['checkboxValue']);
    $id = mysqli_real_escape_string($db, $_POST['id']);
    $user_id = $_SESSION['user_id']; // Assuming the user ID is stored in session

    // Ensure the value is either 0 or 1
    if ($checkboxValue == 1 || $checkboxValue == 0) {
        // Get the current status before updating
        $oldStatusQuery = "SELECT status FROM `users` WHERE id='$id'";
        $result = mysqli_query($db, $oldStatusQuery);
        $oldStatus = mysqli_fetch_assoc($result)['status'];

        // Update the status in the database
        $query = "UPDATE `users` SET `status`='$checkboxValue' WHERE id='$id'";

        if (mysqli_query($db, $query)) {
            // Log the system activity after the update
            logSystemActivity($db, $user_id, 'Updated Status', 'users', $id, $oldStatus, $checkboxValue);
            echo 1; // Success
        } else {
            echo "Error updating status: " . mysqli_error($db);
        }
    } else {
        echo "Invalid checkbox value.";
    }
} else {
    echo "Missing required parameters.";
}

// Function to log system activity
function logSystemActivity($db, $user_id, $action, $resource, $resource_id, $old_value = '', $new_value = '') {
    $stmt = mysqli_prepare($db, "INSERT INTO system_logs (user_id, timestamp, action, resource, resource_id, old_value, new_value) 
                                 VALUES (?, NOW(), ?, ?, ?, ?, ?)");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ississ", 
            $user_id,     // i = integer
            $action,      // s = string
            $resource,    // s = string
            $resource_id, // i = integer
            $old_value,   // s = string
            $new_value    // s = string
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing system log statement: " . mysqli_error($db);
    }
}
?>