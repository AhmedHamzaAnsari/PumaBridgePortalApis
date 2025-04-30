<?php
// Database connection settings
ini_set('max_execution_time', '0');
$url1 = $_SERVER['REQUEST_URI'];
header("Refresh: 60; URL=$url1");
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'Ptoptrack@(!!@');
define('DB_DATABASE', 'omcs');

// Establish the database connection
$db = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
if ($db === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Set execution and memory limits
ini_set('memory_limit', '-1');
set_time_limit(500);

// Display the dashboard title
echo '<h1>Dealers Report Emailer</h1>';

// Include necessary files
require 'class/class.phpmailer.php';
require 'pdf.php';

// Set timezone
date_default_timezone_set('Asia/Karachi');

// Get current date and time
$today = date("Y-m-d");
$_to_today = date("Y-m-d H:i:s");
echo $_to_today . ' Run time <br>';

// Initialize variables
$report_time = 1;
$report = 'vehicle';
$user_id = null;
$privilege = 'Admin';
$time_1 = "";
$black_1 = "";
$cartraige_name = "";
$report_timing = "";

// Get task and dealer IDs from URL parameters
// $task_id = $_GET['task_id'] ?? null;
// $dealer_id = $_GET['dealer_id'] ?? null;

// Function to send SMTP mailer
function smtp_mailer($email_addresses, $time, $dealer_name, $db, $task_id, $dealer_id, $row_id)
{
    $connect = new PDO("mysql:host=localhost;dbname=omcs", "root", "Ptoptrack@(!!@");
    $file_name = 'files/Dealers_Detail_' . md5(rand()) . '.pdf';
    $html_code = report_detail($connect, $db, $task_id, $dealer_id);

    $html_code .= get_task_inspection_response($connect, $db, $task_id, $dealer_id);
    $html_code .= get_task_sales_performance($connect, $db, $task_id, $dealer_id);
    $html_code .= get_task_measurement_price($connect, $db, $task_id, $dealer_id);
    $html_code .= get_task_wet_stock($connect, $db, $task_id, $dealer_id);
    $html_code .= get_task_despensing_unit($connect, $db, $task_id, $dealer_id);
    $html_code .= get_task_stock_variations($connect, $db, $task_id, $dealer_id);






    $mail = new PHPMailer();
    $mail->SMTPDebug = 3;
    $mail->IsSMTP();
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 587;
    $mail->IsHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Username = "puma.alertinfo@gmail.com";
    $mail->Password = "nmfihrazglubjkts";
    $mail->SetFrom("puma.alertinfo@gmail.com");
    $mail->WordWrap = 50;
    $mail->IsHTML(true);

    // Inline CSS styles for table elements
    $table_style = 'style="border: 1px solid black; border-collapse: collapse;"';
    $th_td_style = 'style="border: 1px solid black; padding: 10px;"';

    $html_code = str_replace('<table', '<table ' . $table_style, $html_code);
    $html_code = str_replace('<th', '<th ' . $th_td_style, $html_code);
    $html_code = str_replace('<td', '<td ' . $th_td_style, $html_code);
    // echo $html_code;

    foreach ($email_addresses as $to) {
        $mail->ClearAddresses();
        $mail->AddAddress($to);
        $mail->Subject = $dealer_name;
        $mail->Body = $html_code;

        if (!$mail->Send()) {
            echo 'Failed to send email to ' . $to . '<br>';
            echo 'Mailer Error: ' . $mail->ErrorInfo . '<br>';
        } else {
            echo 'Email sent to ' . $to . '<br>';



        }
    }
    $date_time = date('Y-m-d H:i:s');
    $query_update = "UPDATE `reports_emailers`
        SET
        `status` = '1',
        `updated_at` = '$date_time'
        WHERE `id` = '$row_id';";

    if (mysqli_query($db, $query_update)) {
        echo 1;

    } else {
        echo 0;

    }
}

// Check if current time is 10:00 AM

$sql_get_cartraige_no = "SELECT re.*,dl.name as dealer_name,us_tm.login as tm_email,us_rm.login as rm_email,us_grm.login as grm_email FROM reports_emailers as re
    join dealers as dl on dl.id=re.dealer_id
    join users as us_tm on us_tm.id=dl.asm
    join users as us_rm on us_rm.id=dl.tm
    join users as us_grm on us_grm.id=dl.zm where re.status=0 and tm_id!='';";
// echo $sql_get_cartraige_no .'<br>';
$result_contact = mysqli_query($db, $sql_get_cartraige_no);

$count_contact = mysqli_num_rows($result_contact);
// echo $count_contact . ' hamza <br>';

if ($count_contact > 0) {
    while ($row = mysqli_fetch_array($result_contact)) {

        $id = $row['id'];
        $task_id = $row['task_id'];
        $dealer_id = $row['dealer_id'];
        $tm_id = $row['tm_id'];
        $dealers_name = $row["dealer_name"];
        $grm_email = $row["grm_email"];
        $rm_email = $row["rm_email"];
        $tm_email = $row["tm_email"];
        $time = $row["created_at"];

        $sub = 'PUMA Reconciliation Report of ' . $dealers_name . ' ' . $time;

        $email_addresses = [$grm_email, $rm_email, $tm_email, 'abasit9119@gmail.com', 'usman.hameed@p2ptrack.com']; // Add more email addresses as needed
        // $email_addresses = ['abasit9119@gmail.com']; // Add more email addresses as needed
        // $email_addresses = ['abasit9119@gmail.com','usman.hameed@p2ptrack.com']; // Add more email addresses as needed
        // $email_addresses = [$grm_email, $rm_email, 'abasit9119@gmail.com', 'usman.hameed@p2ptrack.com']; // Add more email addresses as needed
        // $email_addresses = ['abasit9119@gmail.com','usman.hameed@p2ptrack.com']; // Add more email addresses as needed

        $check_inspection = "SELECT * 
        FROM survey_response
        WHERE inspection_id = $task_id
        AND (
            SELECT COUNT(*) 
            FROM survey_response
            WHERE inspection_id = $task_id
        ) >= (
            SELECT COUNT(*) 
            FROM survey_category_questions
        )";

        $check_result = mysqli_query($db, $check_inspection);
        $check_count = mysqli_num_rows($check_result);

        if ($check_count > 0) {
            smtp_mailer($email_addresses, date('Y-m-d H:i:s'), $sub, $db, $task_id, $dealer_id, $id);
        }
    }
}




// PDO connection
$connect = new PDO("mysql:host=localhost;dbname=omcs", "root", "Ptoptrack@(!!@");

function get_task_inspection_response($connect, $db, $task_id, $dealer_id)
{
    $query = "SELECT * FROM survey_category ORDER BY id ASC;";
    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();

    $output = '<h2 style="text-align: center;padding: 3px 11px;background: #f2f2f2;">Inspection</h2>';
    $output .= count_per($connect, $task_id, $dealer_id, $db);

    $output .= '<div class="table-responsive">
    <style>
    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }
    th, td {
        padding: 10px;
    }
    </style>';

    foreach ($result as $row) {
        $cat_id = $row['id'];
        $query1 = "SELECT sr.*, sq.question, rf.file AS cancel_file 
                   FROM survey_response AS sr 
                   JOIN survey_category_questions AS sq ON sq.id = sr.question_id
                   LEFT JOIN survey_response_files rf ON (rf.question_id = sr.question_id AND rf.inspection_id = sr.inspection_id)
                   WHERE sr.category_id = $cat_id AND sr.inspection_id = '$task_id' AND sr.dealer_id = '$dealer_id';";
        $statement1 = $connect->prepare($query1);
        $statement1->execute();
        $result1 = $statement1->fetchAll();

        $output .= '<h3>' . $row["name"] . '</h3>';
        $output .= '<table>
        <tr>
        <th>S #</th>
        <th>Question</th>
        <th>Response</th>
        <th>Comments</th>
        </tr>';

        $wet_stock = 1;
        foreach ($result1 as $row1) {
            $output .= '<tr>
            <td class="text-center">' . $wet_stock . '</td>
            <td>' . $row1["question"] . '</td>
            <td>' . $row1["response"] . '</td>
            <td>' . $row1["comment"] . '</td>
            </tr>';
            $wet_stock++;
        }
        $output .= '</table>';
    }
    $output .= '</div>';
    return $output;
}

function count_per($connect, $task_id, $dealer_id, $db)
{
    $get_orders = "SELECT count(*) AS total_count, response 
                   FROM survey_response 
                   WHERE inspection_id = $task_id AND dealer_id = $dealer_id 
                   GROUP BY response;";
    $result_orders = $db->query($get_orders);
    $total_ques = 0;
    $r_yes = 0;
    $r_no = 0;
    $r_n_a = 0;

    while ($row_2 = $result_orders->fetch_assoc()) {
        $total_count = $row_2['total_count'];
        $response = $row_2['response'];
        if ($response == 'N/A') {
            $r_n_a = $total_count;
        } else if ($response == 'No') {
            $r_no = $total_count;
        } else if ($response == 'Yes') {
            $r_yes = $total_count;
        }
    }

    $total_sum = $r_yes + $r_no + $r_n_a;

    $percentage = ($total_sum > 0) ? ($r_yes/($total_sum - $r_n_a)) * 100 : 0;

    $table1 = '<table class="dynamic_table" id="questions_total">
    <thead>
        <tr>
            <th>Total Questions</th>
            <th>Yes</th>
            <th>No</th>
            <th>N/A</th>
            <th>%</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>' . $total_sum . '</td>
            <td>' . $r_yes . '</td>
            <td>' . $r_no . '</td>
            <td>' . $r_n_a . '</td>
            <td>' . round($percentage) . '</td>
        </tr>
    </tbody>
    </table>';

    return $table1;
}

function report_detail($connect, $db, $task_id, $dealer_id)
{
    $query = "SELECT it.*,us.name, dd.name as dealer_name, CASE
    WHEN it.status = 0 THEN 'Pending'
    WHEN it.status = 1 THEN 'Complete'
    WHEN it.status = 2 THEN 'Cancel'
    
    END AS current_status,
    CASE
    WHEN us.privilege = 'ZM' THEN 'GRM'
    WHEN us.privilege = 'TM' THEN 'RM'
    WHEN us.privilege = 'ASM' THEN 'TM'
    
    END AS privilege,
    tr.created_at as visit_close_time,
    (SELECT id FROM inspector_task where dealer_id=it.dealer_id and id!=it.id and id<it.id and stock_variations_status=1 order by id desc limit 1) as last_visit_id,
    tr.dealer_sign
    FROM inspector_task as it 
    join users us on us.id=it.user_id  
    left join inspector_task_response as tr on tr.task_id=it.id
    JOIN 
        dealers AS dd ON dd.id = it.dealer_id where it.id = :task_id AND it.dealer_id = :dealer_id group by it.id
    order by it.id desc";

    $statement = $connect->prepare($query);
    $statement->execute(['task_id' => $task_id, 'dealer_id' => $dealer_id]);
    $result = $statement->fetchAll();

    $table = '';
    if (count($result) > 0) {
        $first = $result[0];
        $last_dates = last_visits_dates($first['last_visit_id'], $first['visit_close_time'], $task_id);
        // Start building the HTML table
        $table .= '<h2 style="text-align: center;padding: 3px 11px;background: #f2f2f2;">Visit Detail</h2>
        <div class="container-fluid" >
            <div class="row">
                <div class="col-md-12">
                    Planned Date : <span id="survey_time">' . htmlspecialchars($first['time']) . '</span>
                </div>
               
                <div id="last_recon">' . $last_dates . '</div>
                <div class="col-md-12">
                    Site Name : <span id="survey_dealer_name">' . htmlspecialchars($first['dealer_name']) . '</span>
                </div>
                <div class="col-md-12">
                    TM Name : <span id="survey_ispector_name">' . htmlspecialchars($first['name']) . '</span>
                </div>
                <div class="col-md-12 d-none">
                    Planned Type : <span id="survey_type">' . htmlspecialchars($first['type']) . '</span>
                </div>
            </div>
        </div>';

        // End of HTML table generation
    }

    return $table;
}


function last_visits_dates($last_visit_id, $comp_date, $current_id)
{
    // Initialize output variable
    // Determine the ID parameter based on last_visit_id
    if (!is_null($last_visit_id)) {
        $t_id = $last_visit_id . "," . $current_id;
    } else {
        $t_id = $current_id;
    }

    // Construct the API URL
    $url = "http://151.106.17.246:8080/omCS-CMS-APIS/get/inspection/get_current_second_last_visit_recon.php?key=03201232927&id=" . $t_id . "&report=stock_variation";

    // Fetch the data from the API
    $response = file_get_contents($url);
    $result = json_decode($response, true);
    $output = '';
    // Check the result and process accordingly
    if (count($result) === 2) {
        $lastTime = $result[1]['created_at'];
        $completeTimeStr = $result[0]['created_at'];
        $lastVisitDateStr = $result[1]['created_at'];

        // Calculate the difference in days
        $completeTime = strtotime($completeTimeStr);
        $lastVisitDate = strtotime($lastVisitDateStr);
        $differenceMs = $completeTime - $lastVisitDate;
        $differenceDays = round($differenceMs / (60 * 60 * 24));

        // Output the result
        $output .= "
            <div class=\"col-md-12\">
                Completion Date : <span id=\"\">$completeTimeStr</span>
            </div>
            <div class=\"col-md-12\">
                Last Visit Date: <span id=\"\">$lastTime</span>
            </div>
            <div class=\"col-md-12\">
                Days Since Last Visit: <span id=\"\">$differenceDays</span>
            </div>";

    } elseif (count($result) === 1) {
        $completeTimeStr = $result[0]['created_at'];
        $output .= "
            <div class=\"col-md-12\">
                Completion Date : <span id=\"\">$completeTimeStr</span>
            </div>
            <div class=\"col-md-12\">
                Last Visit Date: <span id=\"\">First Time</span>
            </div>";

    } else {
        $output .= "
            <div class=\"col-md-12\">
                Last Visit Date: <span id=\"\">First Time</span>
            </div>";

    }

    return $output;
}


function get_task_sales_performance($connect, $db, $task_id, $dealer_id)
{
    $query = "SELECT * FROM dealer_target_response_return as rr 
              JOIN dealers_products as pp ON pp.id = rr.product_id 
              WHERE rr.task_id = :task_id AND rr.dealer_id = :dealer_id";

    $statement = $connect->prepare($query);
    $statement->execute(['task_id' => $task_id, 'dealer_id' => $dealer_id]);
    $result = $statement->fetchAll();

    $table = '';
    if (count($result) > 0) {
        $first = count($result) > 0 ? $result[0] : null;
        $second = count($result) > 1 ? $result[1] : null;

        $table .= '<h2 style="text-align: center;padding: 3px 11px;background: #f2f2f2;">Sales Performance</h2>
        <style>
            table, th, td {
                border: 1px solid black;
                border-collapse: collapse;
            }
            th, td {
                padding: 10px;
            }
        </style>
        <table class="dynamic_table" style="width:100%">
            <tr>
                <th></th>
                <th>' . ($first ? htmlspecialchars($first['name']) : '---') . '</th>
                <th>' . ($second ? htmlspecialchars($second['name']) : '---') . '</th>
                <th>---</th>
                <th>---</th>
            </tr>
            <tr>
                <th>Target For the month (Ltr)</th>
                <td>' . ($first ? number_format($first['monthly_target'], 0) : '---') . '</td>
                <td>' . ($second ? number_format($second['monthly_target'], 0) : '---') . '</td>
                <td>---</td>
                <td>---</td>
            </tr>
            <tr>
                <th>Actual todate (Ltr)</th>
                <td>' . ($first ? number_format($first['target_achieved'], 0) : '---') . '</td>
                <td>' . ($second ? number_format($second['target_achieved'], 0) : '---') . '</td>
                <td>---</td>
                <td>---</td>
            </tr>
            <tr>
                <th>Variance (Ltr)</th>
                <td>' . ($first ? number_format($first['difference'], 0) : '---') . '</td>
                <td>' . ($second ? number_format($second['difference'], 0) : '---') . '</td>
                <td>---</td>
                <td>---</td>
            </tr>
            <tr>
                <th>Reason For Variation</th>
                <td>' . ($first ? htmlspecialchars($first['reason']) : '---') . '</td>
                <td>' . ($second ? htmlspecialchars($second['reason']) : '---') . '</td>
                <td>---</td>
                <td>---</td>
            </tr>
        </table>';

        // echo $table;
    }

    return $table;
}


function get_task_measurement_price($connect, $db, $task_id, $dealer_id)
{

    // $db = new PDO("mysql:host=localhost;dbname=omcs", "root", "Ptoptrack@(!!@");

    $data = [];

    // Query to fetch main data
    $sql = "SELECT * FROM dealer_measurement_pricing_action where task_id=$task_id";

    $result = $db->query($sql);


    $dealerProductCounts = [];
    $myArray = [];

    while ($row = $result->fetch_assoc()) {
        $id = $row["id"];

        $dealerProductCounts = [];
        $myArray = [];

        $get_orders = "SELECT mp.*,dc.name as dispensor_name FROM dealer_measurement_pricing as mp 
            join dealers_dispenser as dc on dc.id=mp.dispenser_id where mp.main_id='$id'";
        // echo $get_orders .'<br>';
        $result_orders = $db->query($get_orders);

        while ($row_2 = $result_orders->fetch_assoc()) {


            // Push the values into the array
            // $myArray[$productType] = $count;
            $myArray[] = $row_2;
        }

        $dealerProductCounts = [

            "main_data" => $row,
            "sub_data" => $myArray,
        ];
        $data[] = $dealerProductCounts;
    }

    // Construct HTML output


    $output = '<style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
        }
    </style>
    <h2 style="text-align: center;padding: 3px 11px;background: #f2f2f2;">Measurement & Price</h2>
    <table class="dynamic_table" style="width:100%">
        <tr>
            <th></th>
            <th>' . htmlspecialchars(isset($data[0]['sub_data'][0]['dispensor_name']) ? $data[0]['sub_data'][0]['dispensor_name'] : '---') . '</th>
            <th>' . htmlspecialchars(isset($data[0]['sub_data'][1]['dispensor_name']) ? $data[0]['sub_data'][1]['dispensor_name'] : '---') . '</th>
            <th>' . htmlspecialchars(isset($data[0]['sub_data'][2]['dispensor_name']) ? $data[0]['sub_data'][2]['dispensor_name'] : '---') . '</th>
            <th>' . htmlspecialchars(isset($data[0]['sub_data'][3]['dispensor_name']) ? $data[0]['sub_data'][3]['dispensor_name'] : '---') . '</th>
            <th>' . htmlspecialchars(isset($data[0]['sub_data'][4]['dispensor_name']) ? $data[0]['sub_data'][4]['dispensor_name'] : '---') . '</th>
            <th>' . htmlspecialchars(isset($data[0]['sub_data'][5]['dispensor_name']) ? $data[0]['sub_data'][5]['dispensor_name'] : '---') . '</th>
            <th>' . htmlspecialchars(isset($data[0]['sub_data'][6]['dispensor_name']) ? $data[0]['sub_data'][6]['dispensor_name'] : '---') . '</th>
            <th>' . htmlspecialchars(isset($data[0]['sub_data'][7]['dispensor_name']) ? $data[0]['sub_data'][7]['dispensor_name'] : '---') . '</th>
        </tr>
        <tr>
            <th>PMG Accurate (Y/N)</th>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][0]['pmg_accurate']) ? $data[0]['sub_data'][0]['pmg_accurate'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][1]['pmg_accurate']) ? $data[0]['sub_data'][1]['pmg_accurate'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][2]['pmg_accurate']) ? $data[0]['sub_data'][2]['pmg_accurate'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][3]['pmg_accurate']) ? $data[0]['sub_data'][3]['pmg_accurate'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][4]['pmg_accurate']) ? $data[0]['sub_data'][4]['pmg_accurate'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][5]['pmg_accurate']) ? $data[0]['sub_data'][5]['pmg_accurate'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][6]['pmg_accurate']) ? $data[0]['sub_data'][6]['pmg_accurate'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][7]['pmg_accurate']) ? $data[0]['sub_data'][7]['pmg_accurate'] : '---') . '</td>
        </tr>
        <tr>
            <th>Shortage %</th>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][0]['shortage_pmg']) ? $data[0]['sub_data'][0]['shortage_pmg'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][1]['shortage_pmg']) ? $data[0]['sub_data'][1]['shortage_pmg'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][2]['shortage_pmg']) ? $data[0]['sub_data'][2]['shortage_pmg'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][3]['shortage_pmg']) ? $data[0]['sub_data'][3]['shortage_pmg'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][4]['shortage_pmg']) ? $data[0]['sub_data'][4]['shortage_pmg'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][5]['shortage_pmg']) ? $data[0]['sub_data'][5]['shortage_pmg'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][6]['shortage_pmg']) ? $data[0]['sub_data'][6]['shortage_pmg'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][7]['shortage_pmg']) ? $data[0]['sub_data'][7]['shortage_pmg'] : '---') . '</td>
        </tr>
        <tr>
            <th>HSD Accurate (Y/N)</th>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][0]['hsd_accurate']) ? $data[0]['sub_data'][0]['hsd_accurate'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][1]['hsd_accurate']) ? $data[0]['sub_data'][1]['hsd_accurate'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][2]['hsd_accurate']) ? $data[0]['sub_data'][2]['hsd_accurate'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][3]['hsd_accurate']) ? $data[0]['sub_data'][3]['hsd_accurate'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][4]['hsd_accurate']) ? $data[0]['sub_data'][4]['hsd_accurate'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][5]['hsd_accurate']) ? $data[0]['sub_data'][5]['hsd_accurate'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][6]['hsd_accurate']) ? $data[0]['sub_data'][6]['hsd_accurate'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][7]['hsd_accurate']) ? $data[0]['sub_data'][7]['hsd_accurate'] : '---') . '</td>
        </tr>
        <tr>
            <th>Shortage %</th>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][0]['shortage_hsd']) ? $data[0]['sub_data'][0]['shortage_hsd'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][1]['shortage_hsd']) ? $data[0]['sub_data'][1]['shortage_hsd'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][2]['shortage_hsd']) ? $data[0]['sub_data'][2]['shortage_hsd'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][3]['shortage_hsd']) ? $data[0]['sub_data'][3]['shortage_hsd'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][4]['shortage_hsd']) ? $data[0]['sub_data'][4]['shortage_hsd'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][5]['shortage_hsd']) ? $data[0]['sub_data'][5]['shortage_hsd'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][6]['shortage_hsd']) ? $data[0]['sub_data'][6]['shortage_hsd'] : '---') . '</td>
            <td>' . htmlspecialchars(isset($data[0]['sub_data'][7]['shortage_hsd']) ? $data[0]['sub_data'][7]['shortage_hsd'] : '---') . '</td>
        </tr>
    </table>';

    $output .= '<h2 ></h2>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
        }
    </style>
    <table class="dynamic_table" style="width:100%">
        <tr>
            <th>Appreciation Of Dealer if correct</th>
            <th>' . htmlspecialchars($data[0]['main_data']['appreation']) . '</th>
            <th></th>
            <th>OGRA Price</th>
            <th>Pump Price</th>
            <th>Variance</th>
        </tr>
        <tr>
            <th>Measure taken to overcome shortage</th>
            <td>' . htmlspecialchars($data[0]['main_data']['measure_taken']) . '</td>
            <th>PMG</th>
            <td>' . htmlspecialchars($data[0]['main_data']['pmg_ogra_price']) . '</td>
            <td>' . htmlspecialchars($data[0]['main_data']['pmg_pump_price']) . '</td>
            <td>' . htmlspecialchars($data[0]['main_data']['pmg_variance']) . '</td>
        </tr>
        <tr>
            <th>Warning</th>
            <td>' . htmlspecialchars($data[0]['main_data']['warning']) . '</td>
            <th>HSD</th>
            <td>' . htmlspecialchars($data[0]['main_data']['hsd_ogra_price']) . '</td>
            <td>' . htmlspecialchars($data[0]['main_data']['hsd_pump_price']) . '</td>
            <td>' . htmlspecialchars($data[0]['main_data']['hsd_variance']) . '</td>
        </tr>
    </table>';

    return $output;
}
function get_task_wet_stock($connect, $db, $task_id, $dealer_id)
{
    $query = "SELECT rr.*,pp.name,tt.lorry_no,tt.min_limit,tt.max_limit FROM dealer_wet_stock as rr 
    join dealers_products as pp on pp.id=rr.product_id 
    join dealers_lorries as tt on tt.id=rr.tank_id WHERE rr.task_id = :task_id AND rr.dealer_id = :dealer_id;";

    $statement = $connect->prepare($query);
    $statement->execute(['task_id' => $task_id, 'dealer_id' => $dealer_id]);
    $result = $statement->fetchAll();

    $table = '';
    if (count($result) > 0) {
        $t1_1 = count($result) > 1 ? $result[0] : null;
        $t1_2 = count($result) > 1 ? $result[1] : null;
        $t1_3 = count($result) > 1 ? $result[2] : null;
        $t1_4 = count($result) > 1 ? $result[3] : null;

        $sumPMG = 0;
        $sumHSD = 0;
        $limitPMG = 0;
        $limitHSD = 0;

        // Iterate through the JSON data
        foreach ($result as $item) {
            // Calculate the difference (dip_new - dip_old)
            $difference = intval($item['dip_new']);
            // Check the product name
            if ($item['name'] === "PMG") {
                $sumPMG += $difference; // Add the difference to PMG sum
            } else if ($item['name'] === "HSD") {
                $sumHSD += $difference; // Add the difference to HSD sum
            }
        }

        $PMGArray = array_fill(0, 4, '---');
        $HSDArray = array_fill(0, 4, '---');
        $PMGArraylimit = array_fill(0, 4, '---');
        $HSDArraylimit = array_fill(0, 4, '---');

        // Iterate through the JSON data
        foreach ($result as $index => $item) {
            // Calculate the difference (dip_new - dip_old)
            $difference = intval($item['dip_new']);
            // Check the product name and store the difference in the corresponding array
            if ($item['name'] === "PMG") {
                $PMGArray[$index] = number_format($difference); // Convert to string to keep consistency with empty strings
                $PMGArraylimit[$index] = number_format($item['max_limit']);
            } else if ($item['name'] === "HSD") {
                $HSDArray[$index] = number_format($difference); // Convert to string to keep consistency with empty strings
                $HSDArraylimit[$index] = number_format($item['max_limit']);
            }
        }

        $sumPMG = 0;
        foreach ($PMGArray as $value) {
            if ($value !== '---') {
                $sumPMG += floatval(str_replace(',', '', $value));
            }
        }

        $sumHSD = 0;
        foreach ($HSDArray as $value) {
            if ($value !== '---') {
                $sumHSD += floatval(str_replace(',', '', $value));
            }
        }

        $table = '
        <h2 style="text-align: center;padding: 3px 11px;background: #f2f2f2;">Wet Stock Management</h2>
        <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
        }
    </style>
        <table class="dynamic_table" style="width:100%">
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Tank-1</th>
                <th>Tank-2</th>
                <th>Tank-3</th>
                <th>Tank-4</th>
            </tr>
            <tr>
                <td>' . ($t1_1 ? $t1_1['created_at'] : '---') . '</td>
                <th>PMG</th>
                <td>' . $PMGArraylimit[0] . '</td>
                <td>' . $PMGArraylimit[1] . '</td>
                <td>' . $PMGArraylimit[2] . '</td>
                <td>' . $PMGArraylimit[3] . '</td>
            </tr>
            <tr>
                <td></td>
                <th>HSD</th>
                <td>' . $HSDArraylimit[0] . '</td>
                <td>' . $HSDArraylimit[1] . '</td>
                <td>' . $HSDArraylimit[2] . '</td>
                <td>' . $HSDArraylimit[3] . '</td>
            </tr>
        </table>
        <h4>Total Stock available</h4>
        <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
        }
    </style>
        <table class="dynamic_table" style="width:100%">
            <tr>
                <th>Product</th>
                <th>SUM</th>
                <th>Tank-1</th>
                <th>Tank-2</th>
                <th>Tank-3</th>
                <th>Tank-4</th>
            </tr>
            <tr>
                <td>PMG</td>
                <td>' . number_format($sumPMG) . '</td>
                <td>' . $PMGArray[0] . '</td>
                <td>' . $PMGArray[1] . '</td>
                <td>' . $PMGArray[2] . '</td>
                <td>' . $PMGArray[3] . '</td>
            </tr>
            <tr>
                <td>HSD</td>
                <td>' . number_format($sumHSD) . '</td>
                <td>' . $HSDArray[0] . '</td>
                <td>' . $HSDArray[1] . '</td>
                <td>' . $HSDArray[2] . '</td>
                <td>' . $HSDArray[3] . '</td>
            </tr>
        </table>
        ';

        // echo $table;

    }

    return $table;
}


function get_task_despensing_unit($connect, $db, $task_id, $dealer_id)
{
    $query = "SELECT rr.*,pp.name as product_name,tt.name as nozle_name,dp.name dispensor_name FROM dealer_reconcilation as rr 
    join dealers_products as pp on pp.id=rr.product_id 
    join dealers_nozzel as tt on tt.id=rr.nozle_id 
    join dealers_dispenser as dp on dp.id=rr.dispenser_id WHERE rr.task_id = :task_id AND rr.dealer_id = :dealer_id;";

    $statement = $connect->prepare($query);
    $statement->execute(['task_id' => $task_id, 'dealer_id' => $dealer_id]);
    $result = $statement->fetchAll();

    $table = '';
    if (count($result) > 0) {
        $sub_data = $result;
        $PMGArray = array_fill(0, 8, '---');
        $HSDArray = array_fill(0, 8, '---');

        // Iterate through the JSON data
        foreach ($result as $index => $item) {
            $difference = $item;

            if ($item['product_name'] === "PMG") {
                $PMGArray[$index] = $difference;
            } else if ($item['product_name'] === "HSD") {
                $HSDArray[$index] = $difference;
            }
        }

        // Prepare dis_ variables
        $dis_0 = count($sub_data) > 1 ? $sub_data[0] : null;
        $dis_1 = count($sub_data) > 1 ? $sub_data[1] : null;
        $dis_2 = count($sub_data) > 1 ? $sub_data[2] : null;
        $dis_3 = count($sub_data) > 1 ? $sub_data[3] : null;
        $dis_4 = count($sub_data) > 1 ? $sub_data[4] : null;
        $dis_5 = count($sub_data) > 1 ? $sub_data[5] : null;
        $dis_6 = count($sub_data) > 1 ? $sub_data[6] : null;
        $dis_7 = count($sub_data) > 1 ? $sub_data[7] : null;

        $table = '
        <h2 style="text-align: center;padding: 3px 11px;background: #f2f2f2;">Dispensing Unit Meter Reading</h2>
        <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
        }
    </style>
        <table class="dynamic_table" style="width:100%">
            <tr>
                <th></th>
                <th></th>
                <th><small>' . ($dis_0 ? $dis_0['dispensor_name'] . " (" . $dis_0['nozle_name'] . ")" : '---') . '</small></th>
                <th><small>' . ($dis_1 ? $dis_1['dispensor_name'] . " (" . $dis_1['nozle_name'] . ")" : '---') . '</small></th>
                <th><small>' . ($dis_2 ? $dis_2['dispensor_name'] . " (" . $dis_2['nozle_name'] . ")" : '---') . '</small></th>
                <th><small>' . ($dis_3 ? $dis_3['dispensor_name'] . " (" . $dis_3['nozle_name'] . ")" : '---') . '</small></th>
                <th><small>' . ($dis_4 ? $dis_4['dispensor_name'] . " (" . $dis_4['nozle_name'] . ")" : '---') . '</small></th>
                <th><small>' . ($dis_5 ? $dis_5['dispensor_name'] . " (" . $dis_5['nozle_name'] . ")" : '---') . '</small></th>
                <th><small>' . ($dis_6 ? $dis_6['dispensor_name'] . " (" . $dis_6['nozle_name'] . ")" : '---') . '</small></th>
                <th><small>' . ($dis_7 ? $dis_7['dispensor_name'] . " (" . $dis_7['nozle_name'] . ")" : '---') . '</small></th>
            </tr>
            <tr>
                <th>Date - P</th>
                <th></th>
                <td>' . ($PMGArray[0] !== '---' ? number_format($PMGArray[0]['new_reading']) : '---') . '</td>
                <td>' . ($PMGArray[1] !== '---' ? number_format($PMGArray[1]['new_reading']) : '---') . '</td>
                <td>' . ($PMGArray[2] !== '---' ? number_format($PMGArray[2]['new_reading']) : '---') . '</td>
                <td>' . ($PMGArray[3] !== '---' ? number_format($PMGArray[3]['new_reading']) : '---') . '</td>
                <td>' . ($PMGArray[4] !== '---' ? number_format($PMGArray[4]['new_reading']) : '---') . '</td>
                <td>' . ($PMGArray[5] !== '---' ? number_format($PMGArray[5]['new_reading']) : '---') . '</td>
                <td>' . ($PMGArray[6] !== '---' ? number_format($PMGArray[6]['new_reading']) : '---') . '</td>
                <td>' . ($PMGArray[7] !== '---' ? number_format($PMGArray[7]['new_reading']) : '---') . '</td>
            </tr>
            <tr>
                <th>Date - L</th>
                <th></th>
                <td>' . ($PMGArray[0] !== '---' ? number_format($PMGArray[0]['old_reading']) : '---') . '</td>
                <td>' . ($PMGArray[1] !== '---' ? number_format($PMGArray[1]['old_reading']) : '---') . '</td>
                <td>' . ($PMGArray[2] !== '---' ? number_format($PMGArray[2]['old_reading']) : '---') . '</td>
                <td>' . ($PMGArray[3] !== '---' ? number_format($PMGArray[3]['old_reading']) : '---') . '</td>
                <td>' . ($PMGArray[4] !== '---' ? number_format($PMGArray[4]['old_reading']) : '---') . '</td>
                <td>' . ($PMGArray[5] !== '---' ? number_format($PMGArray[5]['old_reading']) : '---') . '</td>
                <td>' . ($PMGArray[6] !== '---' ? number_format($PMGArray[6]['old_reading']) : '---') . '</td>
                <td>' . ($PMGArray[7] !== '---' ? number_format($PMGArray[7]['old_reading']) : '---') . '</td>
            </tr>
            <tr>
                <th>Net Sales</th>
                <th>PMG</th>
                <td>' . ($PMGArray[0] !== '---' ? number_format($PMGArray[0]['new_reading'] - $PMGArray[0]['old_reading']) : '---') . '</td>
                <td>' . ($PMGArray[1] !== '---' ? number_format($PMGArray[1]['new_reading'] - $PMGArray[1]['old_reading']) : '---') . '</td>
                <td>' . ($PMGArray[2] !== '---' ? number_format($PMGArray[2]['new_reading'] - $PMGArray[2]['old_reading']) : '---') . '</td>
                <td>' . ($PMGArray[3] !== '---' ? number_format($PMGArray[3]['new_reading'] - $PMGArray[3]['old_reading']) : '---') . '</td>
                <td>' . ($PMGArray[4] !== '---' ? number_format($PMGArray[4]['new_reading'] - $PMGArray[4]['old_reading']) : '---') . '</td>
                <td>' . ($PMGArray[5] !== '---' ? number_format($PMGArray[5]['new_reading'] - $PMGArray[5]['old_reading']) : '---') . '</td>
                <td>' . ($PMGArray[6] !== '---' ? number_format($PMGArray[6]['new_reading'] - $PMGArray[6]['old_reading']) : '---') . '</td>
                <td>' . ($PMGArray[7] !== '---' ? number_format($PMGArray[7]['new_reading'] - $PMGArray[7]['old_reading']) : '---') . '</td>
            </tr>
            <tr>
                <th>Date - P</th>
                <th></th>
                <td>' . ($HSDArray[0] !== '---' ? number_format($HSDArray[0]['new_reading']) : '---') . '</td>
                <td>' . ($HSDArray[1] !== '---' ? number_format($HSDArray[1]['new_reading']) : '---') . '</td>
                <td>' . ($HSDArray[2] !== '---' ? number_format($HSDArray[2]['new_reading']) : '---') . '</td>
                <td>' . ($HSDArray[3] !== '---' ? number_format($HSDArray[3]['new_reading']) : '---') . '</td>
                <td>' . ($HSDArray[4] !== '---' ? number_format($HSDArray[4]['new_reading']) : '---') . '</td>
                <td>' . ($HSDArray[5] !== '---' ? number_format($HSDArray[5]['new_reading']) : '---') . '</td>
                <td>' . ($HSDArray[6] !== '---' ? number_format($HSDArray[6]['new_reading']) : '---') . '</td>
                <td>' . ($HSDArray[7] !== '---' ? number_format($HSDArray[7]['new_reading']) : '---') . '</td>
            </tr>
            <tr>
                <th>Date - L</th>
                <th></th>
                <td>' . ($HSDArray[0] !== '---' ? number_format($HSDArray[0]['old_reading']) : '---') . '</td>
                <td>' . ($HSDArray[1] !== '---' ? number_format($HSDArray[1]['old_reading']) : '---') . '</td>
                <td>' . ($HSDArray[2] !== '---' ? number_format($HSDArray[2]['old_reading']) : '---') . '</td>
                <td>' . ($HSDArray[3] !== '---' ? number_format($HSDArray[3]['old_reading']) : '---') . '</td>
                <td>' . ($HSDArray[4] !== '---' ? number_format($HSDArray[4]['old_reading']) : '---') . '</td>
                <td>' . ($HSDArray[5] !== '---' ? number_format($HSDArray[5]['old_reading']) : '---') . '</td>
                <td>' . ($HSDArray[6] !== '---' ? number_format($HSDArray[6]['old_reading']) : '---') . '</td>
                <td>' . ($HSDArray[7] !== '---' ? number_format($HSDArray[7]['old_reading']) : '---') . '</td>
            </tr>
            <tr>
                <th>Net Sales</th>
                <th>HSD</th>
                <td>' . ($HSDArray[0] !== '---' ? number_format($HSDArray[0]['new_reading'] - $HSDArray[0]['old_reading']) : '---') . '</td>
                <td>' . ($HSDArray[1] !== '---' ? number_format($HSDArray[1]['new_reading'] - $HSDArray[1]['old_reading']) : '---') . '</td>
                <td>' . ($HSDArray[2] !== '---' ? number_format($HSDArray[2]['new_reading'] - $HSDArray[2]['old_reading']) : '---') . '</td>
                <td>' . ($HSDArray[3] !== '---' ? number_format($HSDArray[3]['new_reading'] - $HSDArray[3]['old_reading']) : '---') . '</td>
                <td>' . ($HSDArray[4] !== '---' ? number_format($HSDArray[4]['new_reading'] - $HSDArray[4]['old_reading']) : '---') . '</td>
                <td>' . ($HSDArray[5] !== '---' ? number_format($HSDArray[5]['new_reading'] - $HSDArray[5]['old_reading']) : '---') . '</td>
                <td>' . ($HSDArray[6] !== '---' ? number_format($HSDArray[6]['new_reading'] - $HSDArray[6]['old_reading']) : '---') . '</td>
                <td>' . ($HSDArray[7] !== '---' ? number_format($HSDArray[7]['new_reading'] - $HSDArray[7]['old_reading']) : '---') . '</td>
            </tr>
        </table> <h6>P=Present</h6><h6>L=Last</h6>';


    }

    return $table;
}

function get_task_stock_variations($connect, $db, $task_id, $dealer_id)
{
    $query = "SELECT rr.*,pp.name FROM dealers_stock_variations as rr 
    join dealers_products as pp on pp.id=rr.product_id WHERE rr.task_id = :task_id AND rr.dealer_id = :dealer_id;";

    $statement = $connect->prepare($query);
    $statement->execute(['task_id' => $task_id, 'dealer_id' => $dealer_id]);
    $result = $statement->fetchAll();

    $table = '';
    if (count($result) > 0) {
        $first = $result[0];
        $second = count($result) > 1 ? $result[1] : null;

        $table = '<h2 style="text-align: center;padding: 3px 11px;background: #f2f2f2;">Stock Variations</h2>
        <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
        }
    </style>
        <table class="dynamic_table" style="width:100%">
    <tr>
        <th></th>
        <th></th>
        <th>' . htmlspecialchars($first['name']) . '</th>
        <th>' . ($second ? htmlspecialchars($second['name']) : '') . '</th>
    </tr>
    <tr>
        <th>A</th>
        <th>Opening Stock (Total of all tanks)</th>
        <td>' . number_format($first['opening_stock']) . '</td>
        <td>' . ($second ? number_format($second['opening_stock']) : '') . '</td>
    </tr>
    <tr>
        <th>B</th>
        <th>Purchase during inspection period</th>
        <td>' . number_format($first['purchase_during_inspection_period']) . '</td>
        <td>' . ($second ? number_format($second['purchase_during_inspection_period']) : '') . '</td>
    </tr>
    <tr>
        <th>C=A+B</th>
        <th>Total Product available for sale</th>
        <td>' . number_format($first['total_product_available_for_sale']) . '</td>
        <td>' . ($second ? number_format($second['total_product_available_for_sale']) : '') . '</td>
    </tr>
    <tr>
        <th>D</th>
        <th>Sales As Per Meter Reading (Nozzle Sale)</th>
        <td>' . number_format($first['sales_as_per_meter_reading']) . '</td>
        <td>' . ($second ? number_format($second['sales_as_per_meter_reading']) : '') . '</td>
    </tr>
    <tr>
        <th>E=C-D</th>
        <th>Book Stock</th>
        <td>' . number_format($first['book_stock']) . '</td>
        <td>' . ($second ? number_format($second['book_stock']) : '') . '</td>
    </tr>
    <tr>
        <th>F</th>
        <th>Current Physical Stock</th>
        <td>' . number_format($first['current_physical_stock']) . '</td>
        <td>' . ($second ? number_format($second['current_physical_stock']) : '') . '</td>
    </tr>
    <tr>
        <th>G=F-E</th>
        <th>Gain/Loss</th>
        <td>' . number_format($first['gain_loss']) . '</td>
        <td>' . ($second ? number_format($second['gain_loss']) : '') . '</td>
    </tr>
</table>';

    }

    return $table;
}
?>