<?php
include("../config.php");
session_start();
if (isset($_POST)) {
    // Existing code...
    $checkboxValue = $_POST['checkboxValue'];
    $id=$_POST['id'];
    $user_id=$_POST['user_id'];


    $query = "UPDATE `dealers` SET `indent_price`=$checkboxValue WHERE id='$id'";;

    if(mysqli_query($db, $query)){

        logSystemActivity($db, $user_id, 'modified verification ', 'dealers', $id);
        echo 1;
    }
    else{
        
        echo 0;
    }
    

}
function logSystemActivity($db, $user_id, $action, $resource, $resource_id, $old_value = '', $new_value = '') {
    $stmt = mysqli_prepare($db, "INSERT INTO system_logs (user_id, timestamp, action, resource, resource_id, old_value, new_value) 
                                 VALUES (?, NOW(), ?, ?, ?, ?, ?)");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ississ", 
            $user_id,     // i = integer
            $action,      // s = string
            $resource,    // s = string
            $resource_id, // i = integer
            $old_value,   // s = string
            $new_value    // s = string
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing system log statement: " . mysqli_error($db);
    }
}