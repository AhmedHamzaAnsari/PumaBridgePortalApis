<?php
include("../config.php");
session_start();

if (isset($_POST)) {
    $user_id = mysqli_real_escape_string($db, $_POST["user_id"]);
    $response = $_POST["response"];
    $dealer_id = $_POST["dealer_id"];
    $inspection_id = $_POST["inspection_id"];


    $datetime = date('Y-m-d H:i:s');

    $query_main = "INSERT INTO `survey_response_main_rm`
    (`dealer_id`,
    `inspection_id`,
    `data`,
    `created_at`,
    `created_by`)
    VALUES
    ('$dealer_id',
    '$inspection_id',
    '$response',
    '$datetime',
    '$user_id');";

    if (mysqli_query($db, $query_main)) {
        $active = mysqli_insert_id($db);
        $data = json_decode($response, true);

        // Check if decoding was successful
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            echo "Error decoding JSON: " . json_last_error_msg();
        } else {

            // Iterate through the outer array
            // foreach ($data as $section) {
            //     // Iterate through the inner arrays
            //     foreach ($section as $area => $questions) {
            //         $category_id = $area;
            //         // echo "$category_id:\n";

            //         // Iterate through the questions
            //         foreach ($questions as $question) {
            //             // Print each question and its value
            //             foreach ($question as $q => $value) {
            //                 $question_id = $q;
            //                 $answers = $value;

            //                 // echo $category_id . ' => ' . "  $question_id: $answers\n";

            //                 $sql1 = "INSERT INTO `survey_response`
            //                 (`category_id`,
            //                 `inspection_id`,
            //                 `main_id`,
            //                 `question_id`,
            //                 `response`,
            //                 `comment`,
            //                 `dealer_id`,
            //                 `created_at`,
            //                 `created_by`)
            //                 VALUES
            //                 ('$category_id',
            //                 '$inspection_id',
            //                 '$active',
            //                 '$question_id',
            //                 '$answers',
            //                 '',
            //                 '$dealer_id',
            //                 '$datetime',
            //                 '$user_id');";

            //                 if (mysqli_query($db, $sql1)) {
            //                     $output = 1;

            //                 } else {
            //                     $output = 0;
            //                 }
            //             }
            //         }
            //     }
            // }
            $data = $response;

            $arrayData = json_decode($data, true);

            foreach ($arrayData as $item) {
                foreach ($item as $key => $values) {
                    $category_id = $key;
                    foreach ($values as $innerKey => $innerValues) {
                        // echo "  Inner Key: $innerKey\n";
                        foreach ($innerValues as $innerInnerKey => $innerInnerValue) {
                            $jj = json_encode($innerInnerValue);
                            // echo   $innerInnerKey;
                            $arrayData = json_decode($jj, true);
                            $question_id = $innerInnerKey;
                            $answers = $arrayData['response'];
                            $comment = $arrayData['comment'];

                            // Print response and comment
                            // echo "$category_id:\n";
                            // echo "question_id " . $question_id. "\n";
                            // echo "Response: " . $arrayData['response'] . "\n";
                            // echo "Comment: " . $arrayData['comment'] . "\n";

                            $sql1 = "INSERT INTO `survey_response_rm`
                            (`category_id`,
                            `inspection_id`,
                            `main_id`,
                            `question_id`,
                            `response`,
                            `comment`,
                            `dealer_id`,
                            `created_at`,
                            `created_by`)
                            VALUES
                            ('$category_id',
                            '$inspection_id',
                            '$active',
                            '$question_id',
                            '$answers',
                            '$comment',
                            '$dealer_id',
                            '$datetime',
                            '$user_id');";

                            if (mysqli_query($db, $sql1)) {
                                $output = 1;

                            } else {
                                $output = 0;
                            }
                        }
                    }
                    // echo "\n";
                }
            }


            echo $output;
        }
    }





}


?>