<?php

error_reporting(0);
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'Ptoptrack@(!!@');
define('DB_DATABASE', 'sitara');
$db = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);


//index.php
ini_set('memory_limit', '-1');
set_time_limit(500);

include('class/class.phpmailer.php');
include('pdf.php');

$user_id = $_GET['id'];



// Check if the current hour is 9 AM
if ($user_id != "") {
    // Execute your PHP script here

    // echo 'Hamza';

    $sql_get_cartraige_no = "SELECT * FROM sitara.users where id = '$user_id';";
    // echo $sql_get_cartraige_no .'<br>';
    $result_contact = mysqli_query($db, $sql_get_cartraige_no);

    $count_contact = mysqli_num_rows($result_contact);
    // echo $count_contact . ' hamza <br>';
    if ($count_contact > 0) {
        while ($row = mysqli_fetch_array($result_contact)) {
            $to = $row["login"];
            $id = $row["id"];

            echo smtp_mailer($to, date('Y-m-d H:i:s'), $id, $db);

        }
    } else {
        echo 'No User Found ';
    }




} else {
    // Do nothing or perform other actions
    echo "Email Required...";
}
$connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");





function smtp_mailer($to, $time, $user_id, $db)
{
    $connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");
    $alert_today = date("Y-m-d");
    $alert_today_time = date("Y-m-d H:i:s");
    $verificationCode = generateVerificationCode();
    // $alert_link = "";








    // require 'class/class.phpmailer.php';
    $mail = new PHPMailer();
    $mail->SMTPDebug = 3;
    $mail->IsSMTP();
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 587;
    $mail->IsHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Username = "sitaras222@gmail.com";
    $mail->Password = "kjyqvamkejoqtbki";
    $mail->SetFrom("sitaras222@gmail.com");
    $mail->AddAddress($to);
    $mail->WordWrap = 50; //Sets word wrapping on the body of the message to a given number of characters
    $mail->IsHTML(true); //Sets message type to HTMl
    $mail->Subject = 'Login Verification ' . $time; //Sets the Subject of the message
    $mail->Body = '<h1>SITARA.<h1><h3>Please use this "' . $verificationCode . '" verification code to verify your identity.</h3><br/>'; //An HTML or plain text message body
    if ($mail->Send()) //Send an Email. Return true on success or false on error
    {
        // echo $message = '<label class="text-success">Verification code have been send successfully on your email...</label>';
        echo 1;

        $verify = "UPDATE `sitara`.`users`
        SET
        `allowed_actions` = '$verificationCode',
        `independent_exist` = '$time'
        WHERE `id` = $user_id;";
        mysqli_query($db, $verify);


    } else {
        echo 0;
    }
}
function generateVerificationCode($length = 6)
{
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';

    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $code;
}



// echo $list;


?>