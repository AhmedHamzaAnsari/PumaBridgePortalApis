<?php
include("../config.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_id = $_POST['row_id'];
    $category = mysqli_real_escape_string($db, $_POST['category']);
    $question = mysqli_real_escape_string($db, $_POST['questions'][0]);
    $file_req = $_POST['file_req'][0];
    $answer = mysqli_real_escape_string($db, $_POST['answer'][0]);
    $duration = $_POST['action_time'][0];
    $department = $_POST['selectedValues'][0]; // e.g., "1,2"
    $user_id = $_POST['user_id'];
    $date = date('Y-m-d H:i:s');

    // Fetch old values for logging

    // Update survey_category_questions
    $update = "UPDATE survey_category_questions SET
                category_id = '$category',
                question = '$question',
                file = '$file_req',
                answer = '$answer',
                departments = '$department',
                duration = '$duration'
               WHERE id = '$question_id'";

    if (mysqli_query($db, $update)) {
        // Update follow_ups_eng where question_id matches
        $update_followup = "UPDATE follow_ups_eng SET
                                category_id = '$category',
                                answer = '$answer',
                                department = '$department'
                            WHERE question_id = '$question_id'";

        if (mysqli_query($db, $update_followup)) {
            logSystemActivity($db, $user_id, 'Question Updated', 'survey_category_questions', $question_id, '', $question);
            echo 1;
        } else {
            echo 'Error updating follow-up: ' . mysqli_error($db);
        }
    } else {
        echo 'Error updating question: ' . mysqli_error($db);
    }
}

function logSystemActivity($db, $user_id, $action, $resource, $resource_id, $old_value = '', $new_value = '')
{
    $stmt = mysqli_prepare($db, "INSERT INTO system_logs (user_id, timestamp, action, resource, resource_id, old_value, new_value) 
                                 VALUES (?, NOW(), ?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ississ", $user_id, $action, $resource, $resource_id, $old_value, $new_value);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing system log statement: " . mysqli_error($db);
    }
}
?>
