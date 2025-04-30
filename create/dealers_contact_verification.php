<?php
include ("../config.php");
session_start();
if (isset($_POST)) {
    $user_id = $_POST['user_id'];
    $dealer_id = mysqli_real_escape_string($db, $_POST["dealer_id"]);


    $contact = mysqli_real_escape_string($db, $_POST["contact"]);

    $date = date('Y-m-d H:i:s');

    // echo 'HAmza';
    if ($_POST["row_id"] != '') {


    } else {

        $query = "INSERT INTO `dealers_verification`
        (`contact`,
        `dealer_id`,
        `status`,
        `created_at`,
        `created_by`)
        VALUES
        ('$contact',
        '$dealer_id',
        '0',
        '$date',
        '$user_id');";


        if (mysqli_query($db, $query)) {
            $main_id = mysqli_insert_id($db);


            $output = 1;

            logs($main_id,$user_id,$db);

        } else {
            $output = 'Error' . mysqli_error($db) . '<br>' . $query;

        }
    }



    echo $output;
}
function logs($id,$user_id,$db){
    $date= date('Y-m-d H:i:s');
    $query = "INSERT INTO `user_log`
    (`table_name`,
    `table_id`,
    `message`,
    `created_at`,
    `created_by`)
    VALUES
    ('dealers_verification',
    '$id',
    'Request for Dealer Contact Update',
    '$date',
    '$user_id');";
    mysqli_query($db, $query);
}
?>