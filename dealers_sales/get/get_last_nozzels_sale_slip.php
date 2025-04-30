<?php
//fetch.php  
include("../../config.php");


$access_key = '03201232927';
$pass = $_GET["key"];
$dealer_sap = $_GET["dealer_sap"];
if ($pass != '') {
    if ($pass == $access_key) {
        $sql_query1 = "SELECT IFNULL(
            (SELECT SlipNumber 
             FROM dealers_nozzels_sales 
             WHERE dealers_sap = '$dealer_sap' 
             ORDER BY id DESC 
             LIMIT 1), 
            0
        ) AS SlipNumber;";

        $result1 = $db->query($sql_query1) or die("Error :" . mysqli_error());

        $thread = array();
        while ($user = $result1->fetch_assoc()) {
            $thread[] = $user;
        }
        echo json_encode($thread);

    } else {
        echo 'Wrong Key...';
    }

} else {
    echo 'Key is Required';
}


?>