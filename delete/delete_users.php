<?php
//fetch.php  
include("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
if ($pass != '') {
    if ($pass == $access_key) {
        $id = $_GET['id'];

        $sql = "DELETE FROM users WHERE id = '$id'";

        // echo $sql;

        if(mysqli_query($db, $sql)){
            logSystemActivity($db, $user_id, 'Deleted User', 'users', $id);
        }
        else{
            echo 'Error' . mysqli_error($db) . '<br>' . $query;
        }
      

    } else {
        echo 'Wrong Key...';
    }

} else {
    echo 'Key is Required';
}
function logSystemActivity($db, $user_id, $action, $resource, $resource_id, $old_value = '', $new_value = '') {
    $stmt = mysqli_prepare($db, "INSERT INTO system_logs (user_id, timestamp, action, resource, resource_id, old_value, new_value) 
                                 VALUES (?, NOW(), ?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ississ", 
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