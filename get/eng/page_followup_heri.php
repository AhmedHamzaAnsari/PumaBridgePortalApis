<?php
include("../../config.php");
error_reporting(0);

$access_key = '03201232927';

$pass = $_GET["key"];
if ($pass != '') {
    $from = $_GET["from"];
    $to = $_GET["to"];
    $pre = $_GET["pre"];
    $datetimes = date('Y-m-d H:i:s');
    $thread = [];

    if ($pass == $access_key) {
        $survey_form_sql = "SELECT * FROM follow_ups_eng
                            WHERE status = '0'
                            AND created_at >= '$from'
                            AND created_at <= '$to'";
        $survey_form_result = $db->query($survey_form_sql) or die("Error: 01 " . mysqli_error($db));
        
        while ($user_f = $survey_form_result->fetch_assoc()) {
            $idds = $user_f['id'];
            $cat_table = mysqli_real_escape_string($db, $user_f['cat_table']);
            $ques_table = mysqli_real_escape_string($db, $user_f['ques_table']);

            $survey_sql = "
            SELECT 
                dl.id AS dealer_id,
                fu.*, 
                it.type AS task_type, 
                it.time AS tas_time, 
                it.description AS task_des, 
                us.name AS inspector_name, 
                dl.name AS dealers_name, 
                sc.name AS cat_name, 
                sq.question AS ques_name, 
                sq.duration AS hours_duration, 
                dl.region, dl.province, dl.city,
                CASE 
                    WHEN fu.status = 0 THEN 'Pending'
                    ELSE 'Complete'
                END AS status_val,
                (SELECT COUNT(*) FROM followup_notification_eng WHERE followup_id = $idds) AS chat_counts,
                dep.name AS department_name
            FROM follow_ups_eng AS fu
            JOIN eng_inspector_task AS it ON it.id = fu.task_id
            JOIN users AS us ON us.id = it.user_id
            JOIN dealers AS dl ON dl.id = it.dealer_id
            JOIN $cat_table AS sc ON sc.id = fu.category_id
            JOIN $ques_table AS sq ON sq.id = fu.question_id
            LEFT JOIN department AS dep ON dep.id = CAST(fu.department AS UNSIGNED)
            WHERE fu.id = $idds 
              AND fu.created_at >= '$from' 
              AND fu.created_at <= '$to'";
              
            $survey_result = $db->query($survey_sql) or die("Error: 02 " . mysqli_error($db));

            while ($user = $survey_result->fetch_assoc()) {
                $hours_duration = $user['hours_duration'];
                $created_at = $user['created_at'];
                $dealer_id = $user['dealer_id'];
                $diff = diferr($created_at, $datetimes);

                // Get ZM, TM, RM info from dealer
                $sql_dpt_heri = "
                SELECT 
                    zm.id AS zm_id, zm.name AS zm_name, zm.privilege AS zm_pre,
                    tm.id AS tm_id, tm.name AS tm_name, tm.privilege AS tm_pre,
                    rm.id AS rm_id, rm.name AS rm_name, rm.privilege AS rm_pre
                FROM dealers AS dl 
                JOIN users AS zm ON zm.id = dl.zm 
                JOIN users AS tm ON tm.id = dl.tm 
                JOIN users AS rm ON rm.id = dl.asm 
                WHERE dl.id = '$dealer_id'";

                // echo $sql_dpt_heri;
                // exit;
                $sql_dpt_heri_result = $db->query($sql_dpt_heri) or die("Error: " . mysqli_error($db));
                // echo 1;
                if ($dpt = $sql_dpt_heri_result->fetch_assoc()) {
                    $rm_name = $dpt['rm_name'];
                    $tm_name = $dpt['tm_name'];
                    $zm_name = $dpt['zm_name'];

                    $rm_pre = ($dpt['rm_pre'] == 'TM') ? 'RM' : $dpt['rm_pre'];
                    $tm_pre = ($dpt['tm_pre'] == 'ASM') ? 'TM' : $dpt['tm_pre'];
                    $zm_pre = ($dpt['zm_pre'] == 'ZM') ? 'GM' : $dpt['zm_pre'];

                    if ($user['department_name'] == 'Dealer') {
                        $user['waiting'] = 'Waiting For Dealer Response';
                    } elseif ($user['department_name'] == 'Engineer') {
                        $user['waiting'] = 'Waiting For Engineer Response';
                    } elseif ($user['department_name'] == 'Retail') {
                        if ($diff <= $hours_duration) {
                            $user['waiting'] = 'Waiting For ' . $tm_pre . ' ' . $tm_name;
                        } elseif ($diff > $hours_duration && $diff <= $hours_duration * 2) {
                            $user['waiting'] = 'Waiting For ' . $rm_pre . ' ' . $rm_name;
                        } elseif ($diff > $hours_duration * 2) {
                            $user['waiting'] = 'Waiting For ' . $zm_pre . ' ' . $zm_name;
                        }
                    }
                }

                $thread[] = $user;

            }
        }

        echo json_encode($thread);
        
    } else {
        echo 'Wrong Key...';
    }
} else {
    echo 'Key is Required';
}

function diferr($d1, $d2)
{
       
    $datetime1 = new DateTime($d1);
    $datetime2 = new DateTime($d2);
    $interval = $datetime1->diff($datetime2);
    $hours = $interval->h + ($interval->days * 24);
    return $hours;
}
?>
