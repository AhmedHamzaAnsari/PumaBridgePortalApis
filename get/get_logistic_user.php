<?php
//fetch.php  
include("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
$id=$_GET['id'];
if ($pass != '') {
    if ($pass == $access_key) {
        $sql_query1 = "SELECT u.privilege,ul.logistics_id,ul.role as l_privilege FROM users_logistics as ul JOIN users u ON ul.logistics_id=u.id  WHERE ul.logistics_id=$id";

        $result1 = $db->query($sql_query1) or die("Error :" . mysqli_error($db));

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

