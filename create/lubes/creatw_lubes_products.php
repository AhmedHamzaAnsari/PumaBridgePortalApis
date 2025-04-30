<?php
include("../../config.php");
session_start();
if (isset($_POST)) {
    $user_id = $_POST['user_id'];
    $name = mysqli_real_escape_string($db, $_POST["name"]);
    $code = mysqli_real_escape_string($db, $_POST["code"]);
    $category = mysqli_real_escape_string($db, $_POST["category"]);
    $sizes = mysqli_real_escape_string($db, $_POST["sizes"]);
    $price = mysqli_real_escape_string($db, $_POST["price"]);
    $date = date('Y-m-d H:i:s');

    // echo 'HAmza';
    if ($_POST["row_id"] != '') {


    } else {

        $query = "INSERT INTO `lubes_product`
        (`code`,
        `name`,
        `cat_id`,
        `size_id`,
        `price`,
        `created_at`,
        `created_by`)
        VALUES
        ('$code',
        '$name',
        '$category',
        '$sizes',
        '$price',
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