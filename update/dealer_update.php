<?php
include("../config.php");
session_start();
if (isset($_POST)) {
    $logo = "";
    $banner = "";
    $logo_img_hidden = $_POST['logo_img_hidden'];
    $banner_img_hidden = $_POST['banner_img_hidden'];
    $dealer_id = $_POST['row_id'];
    $user_id = $_POST['user_id'];
    $dealer_name = $_POST['dealer_name'];
    $dealer_sap_no = $_POST['dealer_sap_no'];
    $emails = $_POST['emails'];
    $password = $_POST['password'];
    $call_no = $_POST['call_no'];
    $location = $_POST['location'];
    $lati = $_POST['lati'];
    $account_balanced = $_POST['account_balanced'];
    $housekeeping = $_POST['housekeeping'];
    $zm = $_POST['zm'];
    $tm = $_POST['tm'];
    $asm = $_POST['asm'];
    $depots = isset($_POST['depots']);
    $district = $_POST['district'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $region = $_POST['region'];
    // Existing code...

    $logocheck = $_FILES['logo_img']['name'];
    $bannercheck = $_FILES['banner_img']['name'];



    if ($bannercheck == "") {

        $banner = $banner_img_hidden;
    } else {
        $file = rand(1000, 100000) . "-" . $_FILES['banner_img']['name'];
        $file_loc = $_FILES['banner_img']['tmp_name'];
        $file_size = $_FILES['banner_img']['size'];
        $folder = "../../PumaBridgeFiles/uploads/";

        // Check if the file was successfully uploaded
        if (move_uploaded_file($file_loc, $folder . $file)) {
            $banner = $file; // Update $logo with the new filename
        }
    }


    if ($logocheck == "") {
        $logo = $logo_img_hidden;
    } else {
        $file1 = rand(1000, 100000) . "-" . $_FILES['logo_img']['name'];
        $file_loc1 = $_FILES['logo_img']['tmp_name'];
        $file_size1 = $_FILES['logo_img']['size'];
        $folder1 = "../../PumaBridgeFiles/uploads/";

        // Check if the file was successfully uploaded
        if (move_uploaded_file($file_loc1, $folder1 . $file1)) {
            $logo = $file1; // Update $logo with the new filename
        }
    }

    // Existing code...

    $query = "UPDATE `dealers` SET 
     `name`='$dealer_name',
     `sap_no`='$dealer_sap_no',
     `contact`='$call_no',
     `email`='$emails',
     `password`='$password',
     `location`='$location',
     `co-ordinates`='$lati',
     `housekeeping`='$housekeeping',
     `zm`='$zm',
     `tm`='$tm',
     `asm`='$asm',
     `district`='$district',
     `city`='$city',
     `region`='$region',
     `province`='$province',
     `banner`='$banner',
     `logo`='$logo',
     `acount`='$account_balanced'
     WHERE id='$dealer_id'";

    $start_time = date("Y-m-d H:i:s");

    if (mysqli_query($db, $query)) {

        if($depots!=""){
            $delete_depot = "DELETE FROM `dealers_depots` WHERE dealers_id= $dealer_id";
            mysqli_query($db, $delete_depot);
            foreach ($depots as $assign) {
                $sql1 = "INSERT INTO `dealers_depots`
                    (`dealers_id`,
                    `depot_id`,
                    `created_at`,
                    `created_by`)
                    VALUES
                    ('$dealer_id',
                    '$assign',
                    '$start_time',
                    '$user_id');";
    
                if (mysqli_query($db, $sql1)) {
                    $output = 1;
                }
            }

        }else{
            $output = 1;
        }
    } else {
        $output = 'Error' . mysqli_error($db) . '<br>' . $query;
    }




    echo $output;
}