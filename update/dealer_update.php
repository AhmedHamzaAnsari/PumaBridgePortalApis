<?php
include("../config.php");
session_start();

function logSystemActivity($db, $user_id, $action, $resource, $resource_id, $old_value = '', $new_value = '') {
    $stmt = mysqli_prepare($db, "INSERT INTO system_logs (user_id, timestamp, action, resource, resource_id, old_value, new_value) 
                                 VALUES (?, NOW(), ?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ississ", 
            $user_id, $action, $resource, $resource_id, $old_value, $new_value
        );
        mysqli_stmt_execute($stmt); 
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing system log statement: " . mysqli_error($db);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logo = "";
    $banner = "";
    $logo_img_hidden = $_POST['logo_img_hidden'];
    $banner_img_hidden = $_POST['banner_img_hidden'];
    $dealer_id = $_POST['row_id'];
    $user_id = $_POST['user_id'];

    // New data
    $new_data = [
        'name' => $_POST['dealer_name'],
        'sap_no' => $_POST['dealer_sap_no'],
        'email' => $_POST['emails'],
        'password' => $_POST['password'],
        'contact' => $_POST['call_no'],
        'location' => $_POST['location'],
        'co-ordinates' => $_POST['lati'],
        'acount' => $_POST['account_balanced'],
        'housekeeping' => $_POST['housekeeping'],
        'zm' => $_POST['zm'],
        'tm' => $_POST['tm'],
        'asm' => $_POST['asm'],
        'district' => $_POST['district'],
        'city' => $_POST['city'],
        'province' => $_POST['province'],
        'region' => $_POST['region'],
    ];

    $depots = $_POST['depots'] ?? [];
    $start_time = date("Y-m-d H:i:s");

    // Get current data
    $result = mysqli_query($db, "SELECT * FROM dealers WHERE id = '$dealer_id'");
    $current_data = mysqli_fetch_assoc($result);

    // Handle image uploads
    if ($_FILES['banner_img']['name'] !== "") {
        $file = rand(1000, 100000) . "-" . $_FILES['banner_img']['name'];
        $file_loc = $_FILES['banner_img']['tmp_name'];
        $folder = "../../PumaBridgeFiles/uploads/";
        if (move_uploaded_file($file_loc, $folder . $file)) {
            $new_data['banner'] = $file;
        }
    } else {
        $new_data['banner'] = $banner_img_hidden;
    }

    if ($_FILES['logo_img']['name'] !== "") {
        $file1 = rand(1000, 100000) . "-" . $_FILES['logo_img']['name'];
        $file_loc1 = $_FILES['logo_img']['tmp_name'];
        $folder1 = "../../PumaBridgeFiles/uploads/";
        if (move_uploaded_file($file_loc1, $folder1 . $file1)) {
            $new_data['logo'] = $file1;
        }
    } else {
        $new_data['logo'] = $logo_img_hidden;
    }

    // Prepare update query
    $updates = [];
    foreach ($new_data as $key => $value) {
        if ($current_data[$key] != $value) {
            $updates[] = "`$key` = '" . mysqli_real_escape_string($db, $value) . "'";
            logSystemActivity($db, $user_id, 'Updated', 'dealers', $dealer_id, $current_data[$key], $value);
        }
    }

    if (!empty($updates)) {
        $update_query = "UPDATE dealers SET " . implode(', ', $updates) . " WHERE id = '$dealer_id'";
        mysqli_query($db, $update_query);
    }

    // Handle depots
    if (!empty($depots)) {
        mysqli_query($db, "DELETE FROM dealers_depots WHERE dealers_id = $dealer_id");
        foreach ($depots as $assign) {
            $sql1 = "INSERT INTO dealers_depots (dealers_id, depot_id, created_at, created_by)
                     VALUES ('$dealer_id', '$assign', '$start_time', '$user_id')";
            mysqli_query($db, $sql1);
        }
    }

    echo 1;
}
?>