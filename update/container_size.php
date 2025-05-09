<?php
include("../config.php");
session_start();
if (isset($_POST)) {


    $row_id = $_POST['row_id'];
    $name = $_POST['name'];
    $id = $_POST['id'];

    // echo 'HAmza';



    $query = "UPDATE `containers_sizes` SET `sizes`='$name' WHERE id='$row_id';";


    if (mysqli_query($db, $query)) {
        logSystemActivity($db, $user_id, $stat, 'Update Container', 'containers_sizes', $id);

        $output = 1;
    } else {
        $output = 'Error' . mysqli_error($db) . '<br>' . $query;

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