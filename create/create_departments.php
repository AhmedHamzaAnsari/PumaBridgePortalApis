<?php
include("../config.php");
session_start();

if (isset($_POST)) {
    $user_id = $_POST['user_id'];  // User who is inserting the data
    $name = mysqli_real_escape_string($db, $_POST["name"]);  // Department name
    $date = date('Y-m-d H:i:s');  // Current timestamp for created_at

    // Insert query for new department
    $query = "INSERT INTO `department` (`name`, `created_at`, `created_by`)
              VALUES ('$name', '$date', '$user_id');";

    // Execute the query
    if (mysqli_query($db, $query)) {
        $dip_id = mysqli_insert_id($db);
        logSystemActivity($db, $user_id, 'Created', 'department', $dip_id);
} else {
        $output = 'Error: ' . mysqli_error($db) . '<br>' . $query;  // Error message
    }

    echo $output;  // Return the result (1 for success, error message for failure)
}function logSystemActivity($db, $user_id, $action, $resource, $resource_id, $old_value = '', $new_value = '')
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