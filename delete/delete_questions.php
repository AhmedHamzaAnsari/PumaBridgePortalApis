<?php
// delete_survey_question.php
include("../config.php");

$access_key = '03201232927';

$pass = $_GET["key"];
$id = $_GET['id'];

if ($pass != '') {
    if ($pass == $access_key) {
        if (isset($id) && !empty($id)) {

            $sql = "DELETE FROM `survey_category_questions` WHERE id='$id'";

            if (mysqli_query($db, $sql)) {
                logSystemActivity($db, $$_SESSION['user_id'], 'Deleted Questions', 'survey_category_questions', $id);
            } else {
                echo 'Error: ' . mysqli_error($db);
            }

        } else {
            echo 'ID is Required';
        }

    } else {
        echo 'Wrong Key...';
    }

} else {
    echo 'Key is Required';
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