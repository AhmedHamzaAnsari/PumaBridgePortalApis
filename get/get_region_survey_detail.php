ge<?php
// fetch.php  
include("../config.php");

$access_key = '03201232927';

$pass = $_GET["key"] ?? '';
$pre = $_GET["pre"] ?? '';
$id = $_GET["user_id"] ?? '';
$from = $_GET["from"] ?? '';
$to = $_GET["to"] ?? '';
$region_param = $_GET["region"] ?? '';
$tm_param = $_GET["tm"] ?? '';

if (!empty($pass)) {
    if ($pass === $access_key) {
        $thread = [];

        // Sanitize inputs
        $region = $db->real_escape_string($region_param);
        $tm_ids = array_map('intval', explode(',', $tm_param));
        $tm_in_clause = implode(',', $tm_ids);

        // Fetch TM users
        $sql_tms = "SELECT * FROM users WHERE region = '$region' AND id IN ($tm_in_clause)";
        $result_tm = $db->query($sql_tms) or die("Error: " . $db->error);

        while ($user_tm = $result_tm->fetch_assoc()) {
            $inspection_report = 0;
            $recon_report = 0;
            $total_yes = 0;
            $total_no = 0;
            $total_na = 0;
            $total_questions = 0;
            $total_files = 0;
            $pers = 0;

            $tm_id = intval($user_tm['id']);
            $tm_name = $user_tm['name'];
            $region = $user_tm['region'];

            // Fetch dealers under TM
            $sql_query1 = "SELECT id, name FROM dealers 
                           WHERE privilege = 'Dealer' AND asm = $tm_id AND region = '$region' 
                           ORDER BY name ASC";
            $result1 = $db->query($sql_query1) or die("Error: " . $db->error);

            while ($user = $result1->fetch_assoc()) {
                $dealer_id = intval($user['id']);

                $sql_query2 = "SELECT it.*, us.name AS user_name, dd.name AS dealer_name, us.privilege AS role_name, 
                                      it.created_at AS task_create_time,
                                      CASE
                                          WHEN it.status = 0 THEN 'Pending'
                                          WHEN it.status = 1 THEN 'Complete'
                                          WHEN it.status = 2 THEN 'Cancel'
                                      END AS current_status, 
                                      dd.region, dd.province, dd.city
                               FROM inspector_task AS it
                               LEFT JOIN inspector_task_response AS tr ON tr.task_id = it.id
                               JOIN dealers AS dd ON dd.id = it.dealer_id
                               JOIN users AS us ON us.id = it.user_id 
                               WHERE it.time BETWEEN '$from' AND '$to' 
                                 AND dd.id = $dealer_id 
                                 AND us.region = '$region' 
                                 AND us.id = $tm_id";

                $result2 = $db->query($sql_query2) or die("Error: " . $db->error);

                while ($task = $result2->fetch_assoc()) {
                    if ($task['stock_variations_status'] == '1') $recon_report++;
                    if ($task['inspection'] == '1') $inspection_report++;
                }
            }

            // Get total and distinct counts
            $sql_query3 = "SELECT dl.id,
                                  GREATEST(
                                      COALESCE((SELECT COUNT(DISTINCT rr.task_id) 
                                                FROM inspector_task AS it
                                                JOIN dealers_stock_variations AS rr ON rr.task_id = it.id
                                                WHERE it.dealer_id = dl.id 
                                                  AND DATE(it.time) BETWEEN '$from' AND '$to'), 0),
                                      COALESCE((SELECT COUNT(DISTINCT rr.inspection_id) 
                                                FROM inspector_task AS it
                                                JOIN survey_response_main AS rr ON rr.inspection_id = it.id
                                                WHERE it.dealer_id = dl.id 
                                                  AND DATE(it.time) BETWEEN '$from' AND '$to'), 0)
                                  ) AS total_count,
                                  GREATEST(
                                      COALESCE((SELECT COUNT(DISTINCT it.dealer_id) 
                                                FROM inspector_task AS it
                                                JOIN dealers_stock_variations AS rr ON rr.task_id = it.id
                                                WHERE DATE(it.time) BETWEEN '$from' AND '$to' 
                                                  AND it.dealer_id = dl.id), 0),
                                      COALESCE((SELECT COUNT(DISTINCT it.dealer_id) 
                                                FROM inspector_task AS it
                                                JOIN survey_response_main AS rr ON rr.inspection_id = it.id
                                                WHERE DATE(it.time) BETWEEN '$from' AND '$to' 
                                                  AND it.dealer_id = dl.id), 0)
                                  ) AS distinct_count
                           FROM dealers AS dl 
                           JOIN users AS us ON us.id = dl.asm 
                           WHERE dl.asm = $tm_id";

            $result3 = $db->query($sql_query3) or die("Error: " . $db->error);

            $total_site = 0;
            $total_count = 0;
            $distinct_count = 0;

            while ($row3 = $result3->fetch_assoc()) {
                $total_site++;
                $total_count += intval($row3['total_count']);
                $distinct_count += intval($row3['distinct_count']);
            }

            $per = $total_site > 0 ? ($distinct_count / $total_site) * 100 : 0;

            // Survey response details
            $get_orders = "SELECT rs.*, sq.question, rf.file AS cancel_file, cc.name AS catogory_name,
                                  dl.name AS dealer_name, dl.sap_no AS dealers_sap, us.name AS tm_name, dl.region, it.id AS task_id
                           FROM survey_response AS rs
                           JOIN survey_category_questions AS sq ON sq.id = rs.question_id
                           JOIN survey_category AS cc ON cc.id = rs.category_id
                           LEFT JOIN survey_response_files AS rf 
                               ON (rf.question_id = rs.question_id AND rf.inspection_id = rs.inspection_id)
                           JOIN inspector_task AS it ON it.id = rs.inspection_id
                           LEFT JOIN inspector_task_response AS tr ON tr.task_id = it.id
                           JOIN users AS us ON us.id = it.user_id
                           JOIN dealers AS dl ON dl.id = rs.dealer_id
                           WHERE us.region = '$region' 
                             AND us.id IN ($tm_id)
                             AND DATE(rs.created_at) BETWEEN '$from' AND '$to'";

            $result_orders = $db->query($get_orders);

            if ($result_orders) {
                while ($row_2 = $result_orders->fetch_assoc()) {
                    $total_questions++;
                    $response = $row_2['response'];
                    $cancel_file = $row_2['cancel_file'];

                    if ($response === 'Yes') $total_yes++;
                    elseif ($response === 'No') $total_no++;
                    elseif ($response === 'N/A') $total_na++;

                    if (!empty($cancel_file)) $total_files++;
                }

                $pers = $total_questions > 0 ? (($total_yes + $total_no) / $total_questions) * 100 : 0;
            } else {
                echo json_encode(["error" => "Error fetching survey data: " . $db->error]);
                exit;
            }

            // Final report
            $report_data = [
                "id" => $tm_id,
                "tm_name" => $tm_name,
                "region" => $region,
                "total_questions" => $total_questions,
                "total_yes" => $total_yes,
                "total_no" => $total_no,
                "total_na" => $total_na,
                "total_files" => $total_files,
                "inspection_count" => $inspection_report,
                "recon_count" => $recon_report,
                "total_site" => $total_site,
                "total_count" => $total_count,
                "distinct_count" => $distinct_count,
                "rank" => $pers,
            ];

            $thread[] = $report_data;
        }

        // Sort by rank descending
        usort($thread, fn($a, $b) => $b['rank'] <=> $a['rank']);

        echo json_encode($thread, JSON_PRETTY_PRINT);

    } else {
        echo json_encode(["error" => "Wrong Key"]);
    }
} else {
    echo json_encode(["error" => "Key is Required"]);
}
?>
