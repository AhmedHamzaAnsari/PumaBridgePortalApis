<?php
include("../config.php");
session_start();

function logSystemActivity($db, $user_id, $action, $resource, $resource_id, $old_value = '', $new_value = '') {
    $stmt = mysqli_prepare($db, "INSERT INTO system_logs (user_id, timestamp, action, resource, resource_id, old_value, new_value) 
                                 VALUES (?, NOW(), ?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "isssss", 
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $datetime = date('Y-m-d H:i:s');
    $dealer_name = $_POST["dealer_name"];
    $emails = $_POST["emails"];
    $call_no = $_POST["call_no"];
    $location = $_POST["location"];
    $lati = $_POST["lati"];
    $housekeeping = $_POST["housekeeping"];
    $password = $_POST["password"];
    $type = $_POST["type"];
    $dealer_sap_no = $_POST['dealer_sap_no'];
    $account_balanced = $_POST["account_balanced"];
    $carss = $_POST['depots'] ?? null;
    $district = $_POST['district'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $region = $_POST['region'];

    $zm = $_POST['zm'];
    $tm = $_POST['tm'];
    $asm = $_POST['asm'];

    // Banner Upload
    $file = '';
    if (isset($_FILES['banner_img']) && $_FILES['banner_img']['error'] === 0) {
        $file = rand(1000, 100000) . "-" . basename($_FILES['banner_img']['name']);
        $file_loc = $_FILES['banner_img']['tmp_name'];
        move_uploaded_file($file_loc, "../../PumaBridgeFiles/uploads/" . $file);
    }

    // Logo Upload
    $file1 = '';
    if (isset($_FILES['logo_img']) && $_FILES['logo_img']['error'] === 0) {
        $file1 = rand(1000, 100000) . "-" . basename($_FILES['logo_img']['name']);
        $file_loc1 = $_FILES['logo_img']['tmp_name'];
        move_uploaded_file($file_loc1, "../../PumaBridgeFiles/uploads/" . $file1);
    }

    $tdate = date('Y-m-d H:i:s');

    if (!empty($_POST["row_id"])) {
        // Update logic can go here (not implemented)
        echo "Update not handled yet.";
    } else {
        $query_main = "INSERT INTO `dealers`
            (`name`, `contact`, `email`, `password`, `location`, `co-ordinates`, `housekeeping`, `no_lorries`, `sap_no`, `type`, `zm`, `tm`, `asm`, `district`, `city`, `region`, `province`, `banner`, `logo`, `acount`, `created_at`, `created_by`)
            VALUES
            ('$dealer_name', '$call_no', '$emails', '$password', '$location', '$lati', '$housekeeping', '0', '$dealer_sap_no', '$type', '$zm', '$tm', '$asm', '$district', '$city', '$region', '$province', '$file', '$file1', '$account_balanced', '$datetime', '$user_id')";

        if (mysqli_query($db, $query_main)) {
            $active = mysqli_insert_id($db);

            // Initial Ledger Log
            $log = "INSERT INTO `dealer_ledger_log`
            (`dealer_id`, `old_ledger`, `new_ledger`, `datetime`, `description`, `doc_no`, `debit_no`, `assignment_no`, `document_type`, `sap_no`, `ledger_balance`, `created_at`, `created_by`)
            VALUES
            ('$active', '$account_balanced', '$account_balanced', '$datetime', 'Initial Credit Limit', '', '', '', '', '$dealer_sap_no', '$account_balanced', '$datetime', '$user_id')";
                    mysqli_query($db, $log) or die("Error creating ledger log: " . mysqli_error($db));

            // System Log
            logSystemActivity($db, $user_id, 'Created Dealer', 'dealers', $active);

            // Depot Assignments
            if (is_array($carss)) {
                foreach ($carss as $assign) {
                    $sql1 = "INSERT INTO `dealers_depots`
                        (`dealers_id`, `depot_id`, `created_at`, `created_by`)
                        VALUES
                        ('$active', '$assign', '$datetime', '$user_id')";
                    mysqli_query($db, $sql1);
                }
            }

            echo 1;
        } else {
            echo 'Error: ' . mysqli_error($db) . '<br>' . $query_main;
        }
    }
}
?>
