<?php
header("Content-Type: application/json");

// Connect to your MySQL database
include("../config.php");
set_time_limit(300); // Set to 5 minutes (300 seconds)

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
    join inspector_task_response as tr on tr.task_id=it.id
    WHERE tr.created_at >= '$startDate' AND tr.created_at < '$endDate'  group by it.id;";

    $result = $db->query($sql);

    if ($result === false) {
        echo json_encode(["error" => "Query failed: " . $db->error]);
        exit();
    }

    $tm_color = "rgb(213, 234, 248)";
    $rm_color = "rgb(22, 149, 217)";
    $gm_color = "rgb(255, 255, 31)";

    // $tm_color = '<span style="display:inline-block; width: 33%; height: 100%; background-color:' . $tm_color_value . ';">.</span>';
    // $rm_color = '<span style="display:inline-block; width: 33%; height: 100%; background-color:' . $rm_color_value . ';">.</span>';
    // $gm_color = '<span style="display:inline-block; width: 33%; height: 100%; background-color:' . $gm_color_value . ';">.</span>';



    // Initialize total counters for summary data
    $total_counts = [];
    foreach ($daysOfMonth as $day) {
        $total_counts[$day] = [
            'gm_color' => 0,
            'rm_color' => 0,
            'tm_color' => 0
        ];
    }

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
                           Join users us on us.id=it.user_id  
                            join inspector_task_response as tr on tr.task_id=it.id
                            JOIN  dealers AS dd ON dd.id = it.dealer_id 
                           WHERE it.dealer_id = $dealer_id 
                           AND date(tr.created_at) = '$formattedDate'  group by it.id;";


            // $user_visit = "SELECT it.*,us.name, dd.name as dealer_name, CASE
            // WHEN it.status = 0 THEN 'Pending'
            // WHEN it.status = 1 THEN 'Complete'
            // WHEN it.status = 2 THEN 'Cancel'

            // END AS current_status,
            // CASE
            // WHEN us.privilege = 'ZM' THEN 'GRM'
            // WHEN us.privilege = 'TM' THEN 'RM'
            // WHEN us.privilege = 'ASM' THEN 'TM'

            // END AS privilege,
            // tr.created_at as visit_close_time,
            // (SELECT id FROM inspector_task where dealer_id=it.dealer_id and id!=it.id and id<it.id and stock_variations_status=1 order by id desc limit 1) as last_visit_id,
            // tr.dealer_sign
            // FROM inspector_task as it 
            // join users us on us.id=it.user_id  
            // left join inspector_task_response as tr on tr.task_id=it.id
            // JOIN 
            //     dealers AS dd ON dd.id = it.dealer_id WHERE it.dealer_id = $dealer_id 
            //                AND date(tr.created_at) = '$formattedDate' group by it.id
            // order by it.id desc";

            $result_user_visit = $db->query($user_visit);

            if ($result_user_visit === false) {
                echo json_encode(["error" => "Query failed: " . $db->error]);
                exit();
            }

            $gm_color_present = '';
            $rm_color_present = '';
            $tm_color_present = '';

            while ($row_2 = $result_user_visit->fetch_assoc()) {
                $privilege = $row_2['privilege'];
                $dealerCounts[$dealer_id]['plan_data'] = $day;

                if ($privilege === 'ZM') {
                    $gm_color_present = $gm_color;
                    $dealerCounts[$dealer_id]['gm_count']++;
                    $total_counts[$day]['gm_color']++;  // Update the total count for this day
                } elseif ($privilege === 'TM') {
                    $rm_color_present = $rm_color;
                    $dealerCounts[$dealer_id]['rm_count']++;
                    $total_counts[$day]['rm_color']++;  // Update the total count for this day
                } elseif ($privilege === 'ASM') {
                    $tm_color_present = $tm_color;
                    $dealerCounts[$dealer_id]['tm_count']++;
                    $total_counts[$day]['tm_color']++;  // Update the total count for this day
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

    // Create summary record for total counts
    $summary_record = [
        "site" => "---",
        "dealer_sap" => "---",
        "region" => "---",
        "city" => "---",
        "tm_name" => "---",
        "rm_name" => "---",
        "gm_name" => "---",
        "plan_data" => "Trip per day",
        "gm_count" => 0,
        "rm_count" => 0,
        "tm_count" => 0,
        "date_info" => []
    ];

    foreach ($daysOfMonth as $day) {
        $summary_record['gm_count'] += $total_counts[$day]['gm_color'];
        $summary_record['rm_count'] += $total_counts[$day]['rm_color'];
        $summary_record['tm_count'] += $total_counts[$day]['tm_color'];

        $summary_record['date_info'][] = [
            'date' => $day,
            'gm_color' => $total_counts[$day]['gm_color'],
            'rm_color' => $total_counts[$day]['rm_color'],
            'tm_color' => $total_counts[$day]['tm_color']
        ];
    }

    // Append the summary record to the formatted data
    $formatted_data[] = $summary_record;

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