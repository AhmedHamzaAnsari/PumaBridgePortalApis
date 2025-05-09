<?php
include("../config.php");
session_start();

if (isset($_POST)) {
    $user_id = $_POST['user_id'];
    $name = mysqli_real_escape_string($db, $_POST["name"]);
    $date = date('Y-m-d H:i:s');

    // Check if row_id is provided (for updating an existing record)
    if ($_POST["row_id"] != '') {
        $row_id = $_POST['row_id'];
        // Update existing record
        $query = "UPDATE `survey_category_rm` 
                  SET `name` = '$name', `updated_at` = '$date', `updated_by` = '$user_id' 
                  WHERE `id` = '$row_id'";

        if (mysqli_query($db, $query)) {
            logSystemActivity($db, $user_id, 'Survey Update', 'survey_category_rm', $row_id);
            $output = 1;  // Success
        } else {
            $output = 'Error: ' . mysqli_error($db);
        }
    } else {
        // Insert new record
        $query = "INSERT INTO `survey_category_rm` (`name`, `created_at`, `created_by`)
                  VALUES ('$name', '$date', '$user_id')";

        if (mysqli_query($db, $query)) {
            $last_insert_id = mysqli_insert_id($db);  // Get the ID of the newly inserted row
            logSystemActivity($db, $user_id, 'Survey Created', 'survey_category_rm', $last_insert_id);
            $output = 1;  // Success
        } else {
            $output = 'Error: ' . mysqli_error($db);
        }
    }

    echo $output;
}

// Function to log system activity (creation or update)
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