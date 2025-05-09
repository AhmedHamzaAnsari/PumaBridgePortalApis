<?php
include("../config.php");
session_start();

if (isset($_POST)) {
    $user_id = mysqli_real_escape_string($db, $_POST['user_id']);
    $users = mysqli_real_escape_string($db, $_POST["users"]);
    $date = date('Y-m-d H:i:s');

    if (!empty($_POST["dealers"]) && is_array($_POST["dealers"])) {

        // $delete = "DELETE FROM `eng_users_dealers`
        // WHERE user_id='$users';";
        // mysqli_query($db, $delete);
        foreach ($_POST["dealers"] as $dealer) {
            $dealer = mysqli_real_escape_string($db, $dealer);

            $query = "INSERT INTO `eng_users_dealers`
            (`dealer_id`,
            `user_id`,
            `created_at`,
            `created_by`)
            VALUES
            ('$dealer',
            '$users',
            '$date',
            '$user_id');";

            if (!mysqli_query($db, $query)) {
                echo 'Error: ' . mysqli_error($db) . '<br>' . $query;
                
                exit; // Stop execution if an error occurs
            }
        }
        logSystemActivity($db, $user_id, 'Eng Created', 'eng_users_dealers', $dealer );

        echo 1; // Success
    } else {
        echo 'Error: No dealers provided.';
    }
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
?>