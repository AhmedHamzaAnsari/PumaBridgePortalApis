<?php
include("../config.php");
session_start();
if (isset($_POST)) {
    $user_id = $_POST['user_id'];
    $dealer_id = mysqli_real_escape_string($db, $_POST["dealer_id"]);

    $usernames = mysqli_real_escape_string($db, $_POST["usernames"]);
    $user_email = mysqli_real_escape_string($db, $_POST["user_email"]);
    $user_phone = mysqli_real_escape_string($db, $_POST["user_phone"]);
    $user_password = mysqli_real_escape_string($db, $_POST["user_password"]);

    $date = date('Y-m-d H:i:s');

    // echo 'HAmza';
    if ($_POST["row_id"] != '') {

        $ids = $_POST["row_id"];

        $query = "UPDATE `dealers`
        SET
        `name` = '$usernames',
        `contact` = '$user_phone',
        `email` = '$user_email',
        `password` = '$user_password'
        WHERE `id` = '$ids';";


        if (mysqli_query($db, $query)) {
            logSystemActivity($db, $user_id, 'Updated Dealer User', 'dealers', $ids);

            $output = 1;

        } else {
            $output = 'Error' . mysqli_error($db) . '<br>' . $query;

        }


    } else {

        $query = "INSERT INTO `dealers`
        (`parent_id`,
        `name`,
        `privilege`,
        `email`,
        `password`,
        `contact`,
        `created_at`,
        `created_by`)
        VALUES
        ('$dealer_id',
        '$usernames',
        'Manager',
        '$user_email',
        '$user_password',
        '$user_phone',
        '$date',
        '$user_id');";


        if (mysqli_query($db, $query)) {
            $active = mysqli_insert_id($db);
            logSystemActivity($db, $user_id, 'Created Dealer User', 'dealers', $dealer_id);


            $output = 1;

        } else {
            $output = 'Error' . mysqli_error($db) . '<br>' . $query;

        }
    }



    echo $output;
}
function logSystemActivity($db, $user_id, $action, $resource, $resource_id, $old_value = '', $new_value = '') {
    $stmt = mysqli_prepare($db, "INSERT INTO system_logs (user_id, timestamp, action, resource, resource_id, old_value, new_value) 
                                 VALUES (?, NOW(), ?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ississ", 
            $user_id,
            $action,
            $resource,
            $resource_id,
            $old_value,
            $new_value
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing system log statement: " . mysqli_error($db);
    }
}
?>
  
 