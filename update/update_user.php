<?php
include ("../config.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize inputs
    $name = mysqli_real_escape_string($db, $_POST['name']);
    $user_id = mysqli_real_escape_string($db, $_POST['user_id']);
    $id = mysqli_real_escape_string($db, $_POST['row_id']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $confirm_password = mysqli_real_escape_string($db, $_POST['confirm_password']);
    $encriped = md5($confirm_password); // Not recommended for secure password hashing
    $desc = mysqli_real_escape_string($db, $_POST['confirm_password']);
    $number = mysqli_real_escape_string($db, $_POST['number']);
    $sales_role = mysqli_real_escape_string($db, $_POST['sales_role']);
    $role = mysqli_real_escape_string($db, $_POST['role']);
    $sales_role_hide = mysqli_real_escape_string($db, $_POST['sales_role_hide']);
    $tm_hide = mysqli_real_escape_string($db, $_POST['tm_hide']);
    $zm_hide = mysqli_real_escape_string($db, $_POST['zm_hide']);

    $output = "";

    // Existing code...
    $date = date("Y-m-d H:i:s");

    if ($role == 'Order' || $role == 'Logistics' || $role == 'Reporting' || $role == 'Monitoring' || $role == 'Inspection Monitoring') {
        $sales_role = $role;
    }

    $query = "UPDATE users SET name='$name',
     privilege='$sales_role',
     password='$encriped',
     description='$desc',
     telephone='$number',
     login='$email',
    email='$email'
     WHERE id='$id'";

    if (mysqli_query($db, $query)) {
        // echo $sales_role;
        if ($sales_role == 'Order' || $sales_role == 'Reporting' || $sales_role == 'Monitoring' || $sales_role == 'Inspection Monitoring') {
            $output = 1;
        }
        if ($sales_role != 'Order' || $sales_role != 'Reporting' || $sales_role != 'Monitoring' || $sales_role != 'Inspection Monitoring') {
            switch ($sales_role_hide) {
                case 'TM':
                    $delete_tm = "DELETE FROM `users_zm_tm` WHERE `tm_id`='$id' AND zm_id='$zm_hide'";
                    if (!mysqli_query($db, $delete_tm)) {
                        $output = 'Error' . mysqli_error($db) . '<br>' . $delete_tm;
                    }
                    break;
                case 'ASM':
                    $delete_asm = "DELETE FROM `users_asm_tm` WHERE `asm_id`='$id' AND tm_id='$tm_hide'";
                    if (!mysqli_query($db, $delete_asm)) {
                        $output = 'Error' . mysqli_error($db) . '<br>' . $delete_asm;
                    }
                    break;
                case 'Logistics':
                    $delete_alo = "DELETE FROM `users_logistics` WHERE `logistics_id`='$id'";
                    if (!mysqli_query($db, $delete_alo)) {
                        $output = 'Error' . mysqli_error($db) . '<br>' . $delete_alo;
                    }
                    break;
                default:
                    break;
            }

            // Handle role-specific privilege updates
            if ($sales_role == 'ZM') {
                $privilege = "UPDATE `users` SET `privilege`='$sales_role' WHERE id='$id'";
                if (!mysqli_query($db, $privilege)) {
                    $output = 'Error' . mysqli_error($db) . '<br>' . $privilege;
                } else {
                    $output = 1;
                }
            } else if ($sales_role == 'TM') {
                $zm = mysqli_real_escape_string($db, $_POST['zm']);
                $query1 = "INSERT INTO `users_zm_tm`
                (`zm_id`,
                `tm_id`,
                `created_by`,
                `created_at`)
                VALUES
                ('$zm',
                '$id',
                '$date',
                '$user_id')";

                if (!mysqli_query($db, $query1)) {
                    $output = 'Error' . mysqli_error($db) . '<br>' . $query1;
                } else {
                    $output = 1;
                }
            } else if ($sales_role == 'ASM') {
                $tm = mysqli_real_escape_string($db, $_POST['tm']);
                $query2 = "INSERT INTO `users_asm_tm`
                        (`tm_id`,
                        `asm_id`,
                        `created_by`,
                        `created_at`)
                        VALUES
                        ('$tm',
                        '$id',
                        '$date',
                        '$user_id')";

                if (!mysqli_query($db, $query2)) {
                    $output = 'Error' . mysqli_error($db) . '<br>' . $query2;
                } else {
                    $output = 1;
                }
            } else if ($sales_role == 'Logistics') {
                $logistics_role = mysqli_real_escape_string($db, $_POST['logistics_role']);
                $query3 = "INSERT INTO `users_logistics`
                (`role`,
                `logistics_id`,
                `created_by`,
                `created_at`)
                VALUES
                ('$logistics_role',
                '$id',
                '$date',
                '$user_id')";

                if (!mysqli_query($db, $query3)) {
                    $output = 'Error' . mysqli_error($db) . '<br>' . $query3;
                } else {
                    $output = 1;
                }
            }
        } else {
            $output = 1;
        }
    } else {
        $output = 'Error' . mysqli_error($db) . '<br>' . $query;
    }
    logs($id, $user_id, $db);
    echo $output;
}

function logs($id, $user_id, $db)
{
    $date = date('Y-m-d H:i:s');
    $query = "INSERT INTO `user_log`
    (`table_name`,
    `table_id`,
    `message`,
    `created_at`,
    `created_by`)
    VALUES
    ('users',
    '$id',
    'User Update',
    '$date',
    '$user_id');";
    mysqli_query($db, $query);
}
?>