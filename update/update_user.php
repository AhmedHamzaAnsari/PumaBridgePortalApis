<?php
include("../config.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($db, $_POST['name']);
    $user_id = mysqli_real_escape_string($db, $_POST['user_id']);
    $id = mysqli_real_escape_string($db, $_POST['row_id']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $confirm_password = mysqli_real_escape_string($db, $_POST['confirm_password']);
    $encriped = md5($confirm_password); // Still recommend switching to password_hash()
    $desc = mysqli_real_escape_string($db, $_POST['confirm_password']);
    $number = mysqli_real_escape_string($db, $_POST['number']);
    $sales_role = mysqli_real_escape_string($db, $_POST['sales_role']);
    $role = mysqli_real_escape_string($db, $_POST['role']);
    $sales_role_hide = mysqli_real_escape_string($db, $_POST['sales_role_hide']);
    $tm_hide = mysqli_real_escape_string($db, $_POST['tm_hide']);
    $zm_hide = mysqli_real_escape_string($db, $_POST['zm_hide']);

    $date = date("Y-m-d H:i:s");
    $output = "";

    // If role is one of these, override sales_role
    if (in_array($role, ['Order', 'Logistics', 'Reporting', 'Monitoring', 'Inspection Monitoring'])) {
        $sales_role = $role;
    }

    $query = "UPDATE users SET 
                name='$name',
                privilege='$sales_role',
                password='$encriped',
                description='$desc',
                telephone='$number',
                login='$email',
                email='$email'
              WHERE id='$id'";

    if (mysqli_query($db, $query)) {
        // ✅ Log System Activity for update
        logSystemActivity($db, $user_id, 'Updated User', 'users', $id);

        // Handling special roles
        if (!in_array($sales_role, ['Order', 'Reporting', 'Monitoring', 'Inspection Monitoring'])) {
            switch ($sales_role_hide) {
                case 'TM':
                    $delete_tm = "DELETE FROM `users_zm_tm` WHERE `tm_id`='$id' AND zm_id='$zm_hide'";
                    mysqli_query($db, $delete_tm);
                    break;
                case 'ASM':
                    $delete_asm = "DELETE FROM `users_asm_tm` WHERE `asm_id`='$id' AND tm_id='$tm_hide'";
                    mysqli_query($db, $delete_asm);
                    break;
                case 'Logistics':
                    $delete_logistics = "DELETE FROM `users_logistics` WHERE `logistics_id`='$id'";
                    mysqli_query($db, $delete_logistics);
                    break;
            }
            
            // Insert according to new role
            if ($sales_role == 'ZM') {
                // why not working
                $privilege = "UPDATE `users` SET `privilege`='ZM' WHERE id='$id'";
                mysqli_query($db, $privilege);
            } elseif ($sales_role == 'TM') {
                $zm = mysqli_real_escape_string($db, $_POST['zm']);
                $insert_tm = "INSERT INTO `users_zm_tm` (zm_id, tm_id, created_by, created_at) 
                              VALUES ('$zm', '$id', '$user_id', '$date')";
                mysqli_query($db, $insert_tm);
            } elseif ($sales_role == 'ASM') {
                $tm = mysqli_real_escape_string($db, $_POST['tm']);
                $insert_asm = "INSERT INTO `users_asm_tm` (tm_id, asm_id, created_by, created_at) 
                               VALUES ('$tm', '$id', '$user_id', '$date')";
                mysqli_query($db, $insert_asm);
            } elseif ($sales_role == 'Logistics') {
                $logistics_role = mysqli_real_escape_string($db, $_POST['logistics_role']);
                $insert_logistics = "INSERT INTO `users_logistics` (role, logistics_id, created_by, created_at) 
                                     VALUES ('$logistics_role', '$id', '$user_id', '$date')";
                mysqli_query($db, $insert_logistics);
            }
        }
        
        // ✅ Log User update separately
        logs($id, $user_id, $db);

        $output = 1;
    } else {
        $output = "Error: " . mysqli_error($db);
    }

    echo $output;
}

// ✅ User Logs table
function logs($id, $user_id, $db) {
    $date = date('Y-m-d H:i:s');
    $query = "INSERT INTO `user_log` (table_name, table_id, message, created_at, created_by) 
              VALUES ('users', '$id', 'User Update', '$date', '$user_id')";
    mysqli_query($db, $query);
}

// ✅ System Activity logs (for auditing)
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