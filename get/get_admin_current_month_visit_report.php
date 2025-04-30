<?php
header("Content-Type: application/json");

// Connect to your MySQL database
include("../config.php");

// Ensure the connection is established
if (!$db) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$access_key = '03201232927';
$pass = $_GET["key"] ?? '';

if (!empty($pass) && $pass === $access_key) {
    $months = $_GET["months"] ?? '';

    if (empty($months)) {
        echo json_encode(["error" => "No month provided"]);
        exit();
    }

    // Initialize an array to store the formatted data
    $formatted_data = [];

    // Get days of the month
    $daysOfMonth = getDaysOfMonth($months);

    // SQL query to fetch dealer records
    $startDate = $months . '-01';
    $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));

    $sql = "SELECT DISTINCT dl.id, dl.sap_no, dl.name, dl.region, dl.city, 
    usz.name as gm_name, ust.name as rm_name, usa.name as tm_name
    FROM dealers AS dl
    JOIN inspector_task AS it ON it.dealer_id = dl.id
    JOIN users AS usz ON usz.id = dl.zm
    JOIN users AS ust ON ust.id = dl.tm
    JOIN users AS usa ON usa.id = dl.asm
    WHERE it.time >= DATE_FORMAT(NOW(), '%Y-%m-01') and it.status=1 and it.dealer_id=1177;";

    $result = $db->query($sql);

    if ($result === false) {
        echo json_encode(["error" => "Query failed: " . $db->error]);
        exit();
    }

    $tm_color = "rgb(213, 234, 248)";
    $rm_color = "rgb(22, 149, 217)";
    $gm_color = "rgb(255, 255, 31)";

    // $tm_color = '<img width="25" src="http://151.106.17.246:8080/omCS-CMS-APIS/uploads/recon/tm.jpeg" alt="Description of image" class="my-image" />';
    // $rm_color = '<img width="25" src="http://151.106.17.246:8080/omCS-CMS-APIS/uploads/recon/rm.jpeg" alt="Description of image" class="my-image" />';
    // $gm_color = '<img width="25" src="http://151.106.17.246:8080/omCS-CMS-APIS/uploads/recon/gm.jpeg" alt="Description of image" class="my-image" />';

    // Array to accumulate counts for each dealer
    $dealerCounts = [];

    while ($row = $result->fetch_assoc()) {
        $dealer_id = $row["id"];
        $sap_no = $row["sap_no"];
        $name = $row["name"];
        $region = $row["region"];
        $city = $row["city"];
        $gm_name = $row["gm_name"];
        $rm_name = $row["rm_name"];
        $tm_name = $row["tm_name"];

        // Initialize counts for this dealer
        $dealerCounts[$dealer_id] = [
            'site' => $name,
            'dealer_sap' => $sap_no,
            'region' => $region,
            'city' => $city,
            'tm_name' => $tm_name,
            'rm_name' => $rm_name,
            'gm_name' => $gm_name,
            'plan_data' => '',
            'gm_count' => 0,
            'rm_count' => 0,
            'tm_count' => 0,
            'date_info' => []
        ];

        foreach ($daysOfMonth as $day) {
            // Create a DateTime object from the input date
            $date = DateTime::createFromFormat('d-M-Y', $day);

            if ($date === false) {
                continue; // Skip if date conversion failed
            }

            // Format the date to the desired format
            $formattedDate = $date->format('Y-m-d');

            // Prepare the SQL query for user visits
            $user_visit = "SELECT it.*, us.name, us.privilege 
                           FROM inspector_task AS it
                           JOIN users AS us ON us.id = it.user_id
                           WHERE it.dealer_id = $dealer_id 
                             AND it.time = '$formattedDate';";

            $result_user_visit = $db->query($user_visit);

            if ($result_user_visit === false) {
                echo json_encode(["error" => "Query failed: " . $db->error]);
                exit();
            }

            $gm_color_present = '';
            $rm_color_present = '';
            $tm_color_present = '';
            
            while ($row_2 = $result_user_visit->fetch_assoc()) {
                $task_id = $row_2['id'];
                $privilege = $row_2['privilege'];
                $dealerCounts[$dealer_id]['plan_data'] = $day;

                if ($privilege === 'ZM') {
                    $gm_color_present = $gm_color;
                    $dealerCounts[$dealer_id]['gm_count']++;
                } elseif ($privilege === 'TM') {
                    $rm_color_present = $rm_color;
                    $dealerCounts[$dealer_id]['rm_count']++;
                } elseif ($privilege === 'ASM') {
                    $tm_color_present = $tm_color;
                    $dealerCounts[$dealer_id]['tm_count']++;
                }
            }

            // Add date info for each date
            $dealerCounts[$dealer_id]['date_info'][] = [
                'date' => $formattedDate,
                'gm_color' => $gm_color_present,
                'rm_color' => $rm_color_present,
                'tm_color' => $tm_color_present
            ];
        }
    }

    // Convert dealerCounts array to a simple indexed array
    $formatted_data = array_values($dealerCounts);

    // Output the JSON string
    echo json_encode($formatted_data, JSON_PRETTY_PRINT);

} else {
    echo json_encode(["error" => "Invalid or missing key"]);
}

function getDaysOfMonth($selectedMonth)
{
    // Extract year and month from the selected month (format: YYYY-MM)
    list($year, $month) = explode('-', $selectedMonth);

    // Calculate the number of days in the selected month
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    // Generate an array of all days in the selected month
    $daysArray = [];
    for ($day = 1; $day <= $daysInMonth; $day++) {
        // Format date as 'd-M-Y'
        $daysArray[] = sprintf('%02d-%s-%04d', $day, date('M', mktime(0, 0, 0, $month, $day, $year)), $year);
    }

    return $daysArray;
}
?>