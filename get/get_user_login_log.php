<?php
//fetch.php  
include("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
if ($pass != '') {
    $id = $_GET["id"];
    if ($pass == $access_key) {
        $sql_query1 = "SELECT ul.*, us.name AS username
        FROM user_login_log AS ul
        JOIN users AS us ON us.id = ul.created_by
        WHERE DATE_FORMAT(ul.created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') and us.id!=1
        ORDER BY ul.id DESC; ";

        $result1 = $db->query($sql_query1) or die("Error :" . mysqli_error($db));

        $thread = array();
        while ($user = $result1->fetch_assoc()) {
            $thread[] = $user;
        }
        echo json_encode($thread);

    } else {
        echo 'Wrong Key...';
    }

} 
else 
{
    echo 'Key is Required';
}


?>