<?php
//fetch.php  
include ("../../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
if ($pass != '') {
    if ($pass == $access_key) {
        $id = $_GET["id"];
        $pre = $_GET["pre"];
        $sql = "SELECT * FROM users WHERE id=$id";

        // echo $sql;

        $result = mysqli_query($db, $sql);
        $row = mysqli_fetch_array($result);

        $rol = $row['privilege'];

        if ($rol != 'ASM Disabled') {
            if ($pre == 'ZM') {

                $sql_query1 = "SELECT dl.*, usz.name as zm_name, ust.name as tm_name, usa.name as asm_name  
                FROM dealers as dl 
                JOIN users as usz ON usz.id = dl.zm
                JOIN users as ust ON ust.id = dl.tm
                JOIN users as usa ON usa.id = dl.asm
                WHERE dl.id NOT IN (
                SELECT it.dealer_id
                FROM inspector_task as it
                WHERE MONTH(it.time) = MONTH(CURDATE()) AND YEAR(it.time) = YEAR(CURDATE()) and it.created_by=$id
            ) and dl.zm=$id order by dl.name asc";
            } elseif ($pre == 'TM') {

                $sql_query1 = "SELECT dl.*, usz.name as zm_name, ust.name as tm_name, usa.name as asm_name  
                FROM dealers as dl 
                JOIN users as usz ON usz.id = dl.zm
                JOIN users as ust ON ust.id = dl.tm
                JOIN users as usa ON usa.id = dl.asm
                WHERE dl.id NOT IN (
                SELECT it.dealer_id
                FROM inspector_task as it
                WHERE MONTH(it.time) = MONTH(CURDATE()) AND YEAR(it.time) = YEAR(CURDATE()) and it.created_by=$id
            ) and dl.tm=$id order by dl.name asc";
            } else {
                $sql_query1 = "SELECT dl.*, usz.name as zm_name, ust.name as tm_name, usa.name as asm_name  
                FROM dealers as dl 
                JOIN users as usz ON usz.id = dl.zm
                JOIN users as ust ON ust.id = dl.tm
                JOIN users as usa ON usa.id = dl.asm
                WHERE dl.id NOT IN (
                SELECT it.dealer_id
                FROM inspector_task as it
                WHERE MONTH(it.time) = MONTH(CURDATE()) AND YEAR(it.time) = YEAR(CURDATE()) and it.created_by=$id
            ) and dl.asm=$id order by dl.name asc";

            }


            $result1 = $db->query($sql_query1) or die("Error :" . mysqli_error());

            $thread = array();
            while ($user = $result1->fetch_assoc()) {
                $thread[] = $user;
            }
            echo json_encode($thread);
        }

    } else {
        echo 'Wrong Key...';
    }

} else {
    echo 'Key is Required';
}


?>