<?php
include("../config.php");
session_start();
if (isset($_POST)) {
    $user_id = $_POST['user_id'];
    $dealer_id = mysqli_real_escape_string($db, $_POST["dealer_id"]);
    $lorry_no = mysqli_real_escape_string($db, $_POST["lorry_no"]);
    $products = mysqli_real_escape_string($db, $_POST["products"]);
    $min_limit = mysqli_real_escape_string($db, $_POST["min_limit"]);
    $max_limit = mysqli_real_escape_string($db, $_POST["max_limit"]);
    $date = date('Y-m-d H:i:s');

    // echo 'HAmza';
    if ($_POST["row_id"] != '') {


    } else {

        $query = "INSERT INTO `dealers_lorries`
                        (`dealer_id`,
                        `lorry_no`,
                        `product`,
                        `min_limit`,
                        `max_limit`,
                        `created_at`,
                        `created_by`)
                        VALUES
                        ('$dealer_id',
                        '$lorry_no',
                        '$products',
                        '$min_limit',
                        '$max_limit',
                        '$date',
                        '$user_id');";


        if (mysqli_query($db, $query)) {


            $output = 1;

        } else {
            $output = 'Error' . mysqli_error($db) . '<br>' . $query;

        }
    }



    echo $output;
}
?>