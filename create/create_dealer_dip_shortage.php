<?php
include("../config.php");
session_start();
if (isset($_POST)) {
    $datetime = date('Y-m-d H:i:s');

    $dealer_id = $_POST['dealer_id'];
    $order_id = $_POST["order_id"];
    $product_json = $_POST["product_json"];
   

    $file = rand(1000, 100000) . "-" . $_FILES['file']['name'];
    $file_loc = $_FILES['file']['tmp_name'];
    $file_size = $_FILES['file']['size'];
    //  $file_type = $_FILES['file']['type'];
    $folder = "../../PumaBridgeFiles/uploads/";
    move_uploaded_file($file_loc, $folder . $file);

    $tdate = date('Y-m-d H:i:s');
    $num = mt_rand(100000, 999999);


    // echo 'HAmza';
    if ($_POST["row_id"] != '') {


    } else {



        $query_main = "INSERT INTO `order_shortage`
        (`order_id`,
        `file`,
        `product_json`,
        `created_at`,
        `created_by`)
        VALUES
        ('$order_id',
        '$file',
        '$product_json',
        '$datetime',
        '$dealer_id');";



        if (mysqli_query($db, $query_main)) {
            $output = 1;
           

            

        } else {
            $output = 'Error' . mysqli_error($db) . '<br>' . $query_main;

        }
    }



    echo $output;
}
?>