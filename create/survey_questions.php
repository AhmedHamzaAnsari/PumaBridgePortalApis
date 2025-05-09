<?php
include("../config.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $category = mysqli_real_escape_string($db, $_POST["category"]);
    $date = date('Y-m-d H:i:s');
    $userData = count($_POST["questions"]);
    $output = '';

    for ($i = 0; $i < $userData; $i++) {
        $question = $_POST['questions'][$i];
        $file_req = $_POST['file_req'][$i];
        $answer = $_POST['answer'][$i];
        $duration = $_POST['action_time'][$i];
        $department = $_POST['selectedValues'][$i]; // 1, 2, or 3

        // Insert into survey_category_questions
        $query = "INSERT INTO `survey_category_questions`
                  (`category_id`, `question`, `file`, `answer`, `departments`, `duration`, `created_at`, `created_by`, `status`)
                  VALUES
                  ('$category', '$question', '$file_req', '$answer', '$department', '$duration', '$date', '$user_id', '0')";

        if (mysqli_query($db, $query)) {
            $last_question_id = mysqli_insert_id($db);
            logSystemActivity($db, $user_id, 'Question Created', 'survey_category_questions', $last_question_id, '', $question);
            $followup_status = '';
            $department_id = '';
            $dpt_users = ''; // This will hold the users for follow-up (e.g., RM, TM, ZM, etc.)

            // Fetch the latest task to get dealer ID (assuming 1 dealer per inspector currently active)
            $task_query = mysqli_query($db, "SELECT dealer_id FROM eng_inspector_task WHERE user_id='$user_id' ORDER BY id DESC LIMIT 1");
            $dealer_id = 0;
            if ($task_row = mysqli_fetch_assoc($task_query)) {
                $dealer_id = $task_row['dealer_id'];
            }

            if ($department == '1') { // Dealer
                $followup_status = 'Waiting for Dealer Response';
                $department_id = $dealer_id;
                $dpt_users = $dealer_id; // Use dealer_id for follow-up users

            } elseif ($department == '3') { // Retail
                $followup_status = 'Waiting for Retail Response';
                $dealer_info = mysqli_fetch_assoc(mysqli_query($db, "SELECT zm, tm, asm FROM dealers WHERE id = '$dealer_id'"));
                $rm_id = $dealer_info['asm'] ?? null;
                $tm_id = $dealer_info['tm'] ?? null;
                $zm_id = $dealer_info['zm'] ?? null;
                $department_id = $rm_id . ',' . $tm_id . ',' . $zm_id;
                $dpt_users = $rm_id . ',' . $tm_id . ',' . $zm_id; // Use RM, TM, ZM for follow-up users

            } elseif ($department == '2') { // Engineer
                $eng_query = mysqli_query($db, "SELECT id FROM users WHERE privilege='Engineer' LIMIT 1");
                if ($eng = mysqli_fetch_assoc($eng_query)) {
                    $department_id = $eng['id'];
                    $followup_status = 'Waiting for Engineer Response';
                    $dpt_users = $eng['id']; // Use engineer's ID for follow-up
                }
            }

            // Insert into follow_ups_eng if department_id and dpt_users are set
            if (!empty($department_id) && !empty($dpt_users)) {
                $cat_table = 'survey_category'; // Replace with your actual category table
                $ques_table = 'survey_category_questions';
                $task_id = '';  // You can set this as needed, or leave it empty if not used
                $form_id = '';  // You can set this as needed, or leave it empty if not used
                $response_id = '';  // You can set this as needed, or leave it empty if not used
                $action_user_id = '';  // You can set this as needed, or leave it empty if not used
                $action_files = '';  // You can set this as needed, or leave it empty if not used
                $action_time = '';  // You can set this as needed, or leave it empty if not used
                $action_description = '';  // You can set this as needed, or leave it empty if not used

                // Insert follow-up entry into follow_ups_eng table
                $ins_followup = "INSERT INTO follow_ups_eng 
                                 (`category_id`, `question_id`, `answer`, `task_id`, `form_id`, `dpt_id`, `form_name`, `dpt_users`, `department`, `response_id`, 
                                  `action_user_id`, `action_files`, `action_time`, `action_description`, `table_name`, `cat_table`, `ques_table`, `status`, 
                                  `created_by`, `created_at`)
                                 VALUES 
                                 ('$category', '$last_question_id', '$answer', '$task_id', '$form_id', '$department_id', '', '$dpt_users', '$department', 
                                  '$response_id', '$action_user_id', '$action_files', '$action_time', '$action_description', '', '$cat_table', '$ques_table', 
                                  '0', '$user_id', '$date')";

                if (mysqli_query($db, $ins_followup)) {
                    $output = 1;  // Follow-up insertion successful
                } else {
                    // Log any errors during the insert into follow_ups_eng
                    $output = 'Error: ' . mysqli_error($db);
                }
            } else {
                $output = 'Error: Missing department or department users for follow-up.';
            }
        } else {
            $output = 'Error inserting question: ' . mysqli_error($db);
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