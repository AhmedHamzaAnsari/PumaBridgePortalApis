<?php
include("../config.php");

$response = []; // Initialize response array

if (isset($_POST['task_id']) && isset($_POST['dealer_id']) && isset($_POST['user_id'])) {
    $task_id = mysqli_real_escape_string($db, $_POST['task_id']);
    $dealer_id = mysqli_real_escape_string($db, $_POST['dealer_id']);
    $user_id = mysqli_real_escape_string($db, $_POST['user_id']);

    // Check if the task exists and its status
    $checkQuery = "SELECT * FROM totelizer_change_request WHERE id = '$task_id' AND dealer_id = '$dealer_id' AND status != 1";
    $result = mysqli_query($db, $checkQuery);

    if (mysqli_num_rows($result) === 0) {
        // Check if the request exists but is already approved
        $checkIfExists = mysqli_query($db, "SELECT id FROM totelizer_change_request WHERE id = '$task_id' AND dealer_id = '$dealer_id'");
        
        if (mysqli_num_rows($checkIfExists) > 0) {
            // The request exists but is already approved
            $response = ['status' => false, 'message' => 'This request is already approved.'];
        } else {
            // The request doesn't exist or dealer_id doesn't match
            $response = ['status' => false, 'message' => 'Invalid request: ID or Dealer ID does not match.'];
        }
    } else {
        // Proceed with approval logic
        $row = mysqli_fetch_assoc($result);
        $nozzel_id = $row['nozzel_id']; // Get nozzle ID

        // Approve the request
        $updateQuery = "UPDATE totelizer_change_request SET status = 1 WHERE id = '$task_id' AND dealer_id = '$dealer_id'";
        $updateResult = mysqli_query($db, $updateQuery);

        if ($updateResult) {
            // Log the action
            logSystemActivity($db, $user_id, 'Approved Request', 'totelizer_change_request', $task_id);

            // Update the nozzle status
            $updateNozzleQuery = "UPDATE dealers_nozzel SET is_change = 1 WHERE id = '$nozzel_id'";
            mysqli_query($db, $updateNozzleQuery);

            // Return success response
            $response = ['status' => true, 'message' => 'Request approved and updated.'];
        } else {
            $response = ['status' => false, 'message' => 'Failed to approve the request.'];
        }
    }
} else {
    $response = ['status' => false, 'message' => 'Missing required parameters.'];
}

// Output the response as JSON
echo json_encode($response);

// Log function
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
    }
}
?>