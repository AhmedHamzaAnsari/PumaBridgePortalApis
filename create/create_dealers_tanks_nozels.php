<?php
include("../config.php");
// session_start();


if (isset($_POST)) {
    $user_id = $_POST['user_id'];
    $nozel_counts = count($_POST['nozzels_id']);
    $dealer_id = mysqli_real_escape_string($db, $_POST["dealer_id"]);
    $tank_id = mysqli_real_escape_string($db, $_POST["tank_id"]);
    $date = date('Y-m-d H:i:s');
    $output = '';
    // echo 'HAmza';
    if ($_POST["row_id"] != '') {


    } else {

        if ($nozel_counts > 0) {
            for ($i = 0; $i < $nozel_counts; $i++) {
                $nozzels_id = $_POST["nozzels_id"][$i];

                $query = "INSERT INTO `dealers_tanks_nozels`
                (`tank_id`,
                `nozel_id`,
                `dealer_id`,
                `created_at`,
                `created_by`)
                VALUES
                ('$tank_id',
                '$nozzels_id',
                '$dealer_id',
                '$date',
                '$user_id');";
                if (mysqli_query($db, $query)) {
                    logSystemActivity($db, $user_id, 'Created Tank', 'dealers_tanks_nozels', $tank_id);



                    $output = 1;

                } else {
                    $output = 'Error' . mysqli_error($db) . '<br>' . $query;

                }
            }
        }





    }



    echo $output;
}
function logSystemActivity($db, $user_id, $action, $resource, $resource_id, $old_value = '', $new_value = '')
{
    $stmt = mysqli_prepare($db, "INSERT INTO system_logs (user_id, timestamp, action, resource, resource_id, old_value, new_value) 
                                     VALUES (?, NOW(), ?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param(
            $stmt,
            "ississ",
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