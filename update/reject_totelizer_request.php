<?php
include("../config.php");

$response = []; // Initialize response array

// Check if required POST parameters are set
if (isset($_POST['task_id']) && isset($_POST['dealer_id']) && isset($_POST['user_id'])) {
    $task_id = mysqli_real_escape_string($db, $_POST['task_id']);
    $dealer_id = mysqli_real_escape_string($db, $_POST['dealer_id']);
    $user_id = mysqli_real_escape_string($db, $_POST['user_id']);

    // Check if the task exists, belongs to the provided dealer, and is not already approved
    $checkQuery = "SELECT * FROM totelizer_change_request WHERE id = '$task_id' AND dealer_id = '$dealer_id' AND status != 1";
    $result = mysqli_query($db, $checkQuery);

    // If a matching request is found
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $nozzel_id = $row['nozzel_id']; // Get nozzle ID
        $old_status = $row['status']; // Capture old status value

        // Reject the request by updating the status to 'rejected'
        $updateQuery = "UPDATE totelizer_change_request SET status = 'rejected' WHERE id = '$task_id' AND dealer_id = '$dealer_id'";
        $updateResult = mysqli_query($db, $updateQuery);

        if ($updateResult) {
            // New status after the update
            $new_status = 'rejected'; 

            // Log the action to system logs (track old and new status)
            logSystemActivity($db, $user_id, 'Rejected  ', 'totelizer_change_request', $task_id, $old_status, $new_status);

            // Update the nozzle status based on nozzle ID
            $updateNozzleQuery = "UPDATE dealers_nozzel SET is_change = 'rejected' WHERE id = '$nozzel_id'";
            mysqli_query($db, $updateNozzleQuery);

            // Success response
            $response = ['status' => true, 'message' => 'Request has been rejected.'];
        } else {
            // If failed to update request
            $response = ['status' => false, 'message' => 'Failed to reject request.'];
        }
    } else {
        // If no matching task found or already approved
        $response = ['status' => false, 'message' => 'Invalid task ID or dealer ID.'];
    }
} else {
    // Missing parameters
    $response = ['status' => false, 'message' => 'Missing required parameters.'];
}

// Output the response in JSON format
echo json_encode($response);

// Log function to track system activity (old value and new value)
function logSystemActivity($db, $user_id, $action, $resource, $resource_id, $old_value = '', $new_value = '')
{
    $stmt = mysqli_prepare($db, "INSERT INTO system_logs (user_id, timestamp, action, resource, resource_id, old_value, new_value) 
                                 VALUES (?, NOW(), ?, ?, ?, ?, ?)");

    if ($stmt) {
        // Binding parameters
        mysqli_stmt_bind_param(
            $stmt,
            "ississ",
            $user_id,       // User ID
            $action,        // Action performed
            $resource,      // Resource (table)
            $resource_id,   // Resource ID (task_id)
            $old_value,     // Old value (before update)
            $new_value      // New value (after update)
        );

        // Execute the statement
        mysqli_stmt_execute($stmt);
        // Close the prepared statement
        mysqli_stmt_close($stmt);
    }
}
?>