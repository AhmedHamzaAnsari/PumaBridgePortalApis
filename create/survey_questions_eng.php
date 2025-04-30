<?php
include("../config.php");
session_start();
if (isset($_POST)) {
    $user_id = $_POST['user_id'];
    $category = mysqli_real_escape_string($db, $_POST["category"]);
    $date = date('Y-m-d H:i:s');
    $userData = count($_POST["questions"]);
    // echo 'HAmza';
    if ($_POST["row_id"] != '') {


    } else {


        for ($i = 0; $i < $userData; $i++) {

            $questions = $_POST['questions'][$i];
            $file_req = $_POST['file_req'][$i];
            $answer = $_POST['answer'][$i];
            $action_time = $_POST['action_time'][$i];

            $query_count = "INSERT INTO `survey_category_questions_eng`
            (`category_id`,
            `question`,
            `file`,
            `answer`,
            `duration`,
            `created_at`,
            `created_by`)
            VALUES
            ('$category',
            '$questions',
            '$file_req',
            '$answer',
            '$action_time',
            '$date',
            '$user_id');";
            if (mysqli_query($db, $query_count)) {
                $output = 1;

            }else {
                $output = 'Error' . mysqli_error($db) . '<br>' . $query_count;
    
            }

        }

        
    }



    echo $output;
}
?>