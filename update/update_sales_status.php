
<?php
include("../config.php");
session_start();
if (isset($_POST)) {
    // Existing code...
    $id=$_POST['id'];


    $query = "UPDATE `dealers_nozzels_sales`
    SET
    `status` = 1 where id='$id'";

    mysqli_query($db, $query);

    echo 1;
}
