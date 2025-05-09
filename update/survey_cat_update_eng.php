<?php
include("../config.php");
session_start();
if (isset($_POST)) {
    // Existing code...
    $checkboxValue = $_POST['checkboxValue'];
    $id=$_POST['id'];
    $user_id = $_SESSION['user_id']; // Ensure session contains user_id

        // Get old status before update
        $oldResult = mysqli_query($db, "SELECT status FROM `survey_category_eng` WHERE id = '$id'");
        $oldRow = mysqli_fetch_assoc($oldResult);
        $oldValue = $oldRow ? $oldRow['status'] : '';

    $query = "UPDATE `survey_category_eng` SET `status`=$checkboxValue WHERE id='$id'";;

    mysqli_query($db, $query);
    logSystemActivity($db, $user_id, 'Updated', 'survey_category_eng', $id, $oldValue, $checkboxValue);

    echo 1;
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