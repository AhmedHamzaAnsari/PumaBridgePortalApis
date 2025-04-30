
<?php
include("../config.php");
session_start();
if (isset($_POST)) {
    // Existing code...
    $checkboxValue = $_POST['checkboxValue'];
    $id=$_POST['id'];


    $query = "UPDATE `dealers` SET `indent_price`=$checkboxValue WHERE id='$id'";;

    if(mysqli_query($db, $query)){

        echo 1;
    }
    else{
        
        echo 0;
    }
    

}
