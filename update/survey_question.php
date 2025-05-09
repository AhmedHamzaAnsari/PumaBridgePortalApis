<?php
include("../config.php");
session_start();

if (isset($_POST)) {
    // Get the checkbox value (status) and ID
    $checkboxValue = $_POST['checkboxValue'];
    $id = $_POST['id'];

    // Fetch the current status before updating
    $query = "SELECT `status` FROM `survey_category_questions` WHERE `id` = '$id'";
    $result = mysqli_query($db, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        $old_status = $row['status'];  // The old status before update
    }

    // Update the status based on checkbox value
    $update_query = "UPDATE `survey_category_questions` SET `status` = $checkboxValue WHERE `id` = '$id'";

    // Check if the query runs successfully
    if (mysqli_query($db, $update_query)) {
        // Log the activity: status change (old_value to new_value)
        logSystemActivity($db, $_SESSION['user_id'], 'Status Changed', 'survey_category_questions', $id, $old_status, $checkboxValue);

        echo 1;  // Indicating success
    } else {
        // Log any errors during the update
        echo 'Error: ' . mysqli_error($db);
    }
}

// Function to log system activity (status change)
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