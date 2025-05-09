<?php
include("../config.php");
session_start();
if (isset($_POST)) {
    $user_id = $_POST['user_id'];
    $name = mysqli_real_escape_string($db, $_POST["name"]);
    $date = date('Y-m-d H:i:s');
    $id = $_POST['id'];


    // echo 'HAmza';
    if ($_POST["row_id"] != '') {


    } else {

        $query = "INSERT INTO `containers_sizes`
        (`sizes`,
        `created_at`,
        `created_by`)
        VALUES
        ('$name',
        '$date',
        '$user_id');";


        if (mysqli_query($db, $query)) {

            logSystemActivity($db, $user_id, $stat, 'Log User','containers_sizes', $id);

            $output = 1;

        } else {
            $output = 'Error' . mysqli_error($db) . '<br>' . $query;

        }
    }



    echo $output;
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