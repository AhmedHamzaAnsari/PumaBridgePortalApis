<?php
include ("../config.php");
session_start();
if (isset($_POST)) {


    $user_id = $_POST['user_id'];
    $verification_status = $_POST['verification_status'];
    $row_id = mysqli_real_escape_string($db, $_POST['spe_order']);
    $dealers_ids = mysqli_real_escape_string($db, $_POST['dealers_ids']);
    $verification_contact = mysqli_real_escape_string($db, $_POST['verification_contact']);

    $datetime = date('Y-m-d H:i:s');
    $val = '';

    // echo 'HAmza';


    $query = "UPDATE `puma_testing_db`.`dealers_verification`
    SET
    `status` = '$verification_status',
    `approved_at` = '$datetime',
    `approved_by` = '$user_id'
    WHERE `id` = '$row_id';";




    if (mysqli_query($db, $query)) {

        if ($verification_status != 1) {
            $val = 'Cancelled';
            $output = 1;
            $msg = "Dealers verification " . $val . " to contact " . $verification_contact;

            logs($row_id, $user_id, $db, $msg);
        } else {
            $val = 'Approved';

            $update_dealers_contac = "UPDATE `dealers`
            SET
            `contact` = '0$verification_contact',
            `indent_price` = '1'
            WHERE `id` = '$dealers_ids';";

            if (mysqli_query($db, $update_dealers_contac)) {
                $output = 1;
                $msg = "Dealers verification " . $val . " to contact " . $verification_contact;

                logs($row_id, $user_id, $db, $msg);

            } else {
                $output = 'Error' . mysqli_error($db) . '<br>' . $update_dealers_contac;

            }

        }






    } else {
        $output = 'Error' . mysqli_error($db) . '<br>' . $query;

    }




    echo $output;
}

function logs($id, $user_id, $db, $msg)
{
    $date = date('Y-m-d H:i:s');
    $query = "INSERT INTO `user_log`
    (`table_name`,
    `table_id`,
    `message`,
    `created_at`,
    `created_by`)
    VALUES
    ('dealers_verification',
    '$id',
    '$msg',
    '$date',
    '$user_id');";
    mysqli_query($db, $query);
}
?>