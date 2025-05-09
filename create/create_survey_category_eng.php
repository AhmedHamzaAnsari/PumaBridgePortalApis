<?php
include("../config.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $name = mysqli_real_escape_string($db, $_POST["name"]);
    $date = date('Y-m-d H:i:s');

    if (!empty($_POST["row_id"])) {
        // You can handle update logic here if needed
        // $output = 'Update logic not implemented.';
    } else {
        $query = "INSERT INTO survey_category_eng (name, created_at, created_by)
                  VALUES ('$name', '$date', '$user_id')";

        if (mysqli_query($db, $query)) {
            $inserted_id = mysqli_insert_id($db); // get the last inserted ID
            logSystemActivity($db, $user_id, 'Created', 'survey_category_eng', $inserted_id);

            $output = 1;
        } else {
            $output = 'Error: ' . mysqli_error($db) . '<br>' . $query;
        }
    }

    echo $output;
}

// Activity Logger Function
function logSystemActivity($db, $user_id, $action, $resource, $resource_id, $old_value = '', $new_value = '') {
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
        error_log("Error preparing system log statement: " . mysqli_error($db));
    }
}
?>