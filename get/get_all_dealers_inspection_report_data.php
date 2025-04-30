<?php
// fetch.php  
include("../config.php");

$access_key = '03201232927';
$pass = $_GET["key"] ?? '';
$from = $_GET["from"] ?? '';
$to = $_GET["to"] ?? '';
$pre = $_GET["pre"] ?? '';
$id = $_GET["id"] ?? '';

if (!$pass) {
    die('Key is Required');
}

if ($pass !== $access_key) {
    die('Wrong Key...');
}

// یوزر رول کے مطابق فلٹرنگ


switch ($pre) {
    case 'ZM':
        $user_column = 'dd.zm';
        break;
    case 'TM':
        $user_column = 'dd.tm';
        break;
    case 'ASM':
        $user_column = 'dd.asm';
        break;
    default:
        $user_column = '';
}

// بیسک SQL کوئری
$sql_query = "SELECT it.*, us.name, dd.name AS dealer_name, dd.privilege, dd.sap_no, 
    CASE WHEN it.status = 0 THEN 'Pending' 
         WHEN it.status = 1 THEN 'Complete' 
         WHEN it.status = 2 THEN 'Cancel' 
    END AS current_status,
    tr.created_at AS visit_close_time,
    (SELECT id FROM inspector_task WHERE dealer_id = it.dealer_id AND id < it.id 
     AND inspection = 1 ORDER BY id DESC LIMIT 1) AS last_visit_id,
    tr.dealer_sign
    FROM inspector_task AS it 
    JOIN users us ON us.id = it.user_id 
    LEFT JOIN inspector_task_response tr ON tr.task_id = it.id
    JOIN dealers AS dd ON dd.id = it.dealer_id
    WHERE it.time >= '$from' AND it.time <= '$to'";

// اگر یوزر کا مخصوص رول ہو تو اس کا **فِلٹر** شامل کریں
if ($user_column) {
    $sql_query .= " AND $user_column = '$id'";
}

$sql_query .= " GROUP BY it.id ORDER BY it.id DESC";

$result = $db->query($sql_query) or die("Error: " . mysqli_error($db));

$thread = [];
while ($user = $result->fetch_assoc()) {
    $thread[] = $user;
}

echo json_encode($thread);
?>