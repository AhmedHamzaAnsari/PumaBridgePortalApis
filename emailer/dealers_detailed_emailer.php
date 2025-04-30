<?php
ini_set('max_execution_time', '0');
$url1 = $_SERVER['REQUEST_URI'];
header("Refresh: 60; URL=$url1");

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'Ptoptrack@(!!@');
define('DB_DATABASE', 'omcs');
$db = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

ini_set('memory_limit', '-1');
set_time_limit(500);

echo '<h1>Dealers Dashboard Report Emailer</h1>';

require 'class/class.phpmailer.php';
require 'pdf.php';

$today = date("Y-m-d");
$_to_today = date("Y-m-d H:i:s");
echo $_to_today . ' Run time <br>';

$report_time = 1;
$report = 'vehicle';
$user_id = null;
$privilege = 'Admin';
$time_1 = "";
$black_1 = "";
$cartraige_name = "";
$report_timing = "";

// smtp_mailer($email_addresses, date('Y-m-d H:i:s'), 'PUMA Daily Status Report', $db);

date_default_timezone_set('Asia/Karachi');

// Check if current time is 10:00 AM
if (date('H:i') == '10:00') {
  // Include necessary files and functions (already defined in your script)


  $email_addresses = ['ahmed.arif@pumapakistan.com', 'Zeeshan.Uquaily@pumapakistan.com', 'usman.hameed@p2ptrack.com', 'abasit9119@gmail.com', 'usmanhameed@gmail.com']; // Add more email addresses as needed
  smtp_mailer($email_addresses, date('Y-m-d H:i:s'), 'PUMA Daily Status Report', $db);

  // Additional code if needed after sending the email
} else {
  echo 'Current TIme ' . date('H:i');
}

$connect = new PDO("mysql:host=localhost;dbname=omcs", "root", "Ptoptrack@(!!@");

function dealers_details($connect)
{
  $query = "SELECT 
  COUNT(*) AS total_dealers,
  SUM(CASE WHEN indent_price = 1 THEN 1 ELSE 0 END) AS verified_dealers,
  SUM(CASE WHEN indent_price = 0 THEN 1 ELSE 0 END) AS non_verified_dealers 
  FROM dealers where privilege='Dealer' and sap_no!=''";
  $statement = $connect->prepare($query);
  $statement->execute();
  $result = $statement->fetchAll(PDO::FETCH_ASSOC);
  $output = '
    <div class="table-responsive">
   
    <h3>Dealers Detail</h3>
    <table style="border: 1px solid black; border-collapse: collapse; width: 100%; margin-bottom: 15px">

    <tr>
    <th style="border: 1px solid black; padding: 10px; text-align: center;">Total Dealers</th>
    <th style="border: 1px solid black; padding: 10px; text-align: center;">Verified</th>
    <th style="border: 1px solid black; padding: 10px; text-align: center;">Not-Verified</th>
</tr>';
  foreach ($result as $row) {
    $output .= '
        <tr>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["total_dealers"] . '</td>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["verified_dealers"] . '</td>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["non_verified_dealers"] . '</td>
        </tr>';
  }
  $output .= '</table></div>';
  return $output;
}

function order_detailed($connect)
{
  $query = "SELECT 
                COUNT(*) AS total_orders,
                COUNT(DISTINCT dealer_sap) AS distinct_dealers
              FROM 
                order_main
              WHERE 
                MONTH(created_at) = MONTH(CURDATE()) 
                AND YEAR(created_at) = YEAR(CURDATE())";
  $statement = $connect->prepare($query);
  $statement->execute();
  $result = $statement->fetchAll(PDO::FETCH_ASSOC);
  $output = '
    <div class="table-responsive">
   
    <h3>Order Detail</h3>
    <table style="border: 1px solid black; border-collapse: collapse; width: 100%; margin-bottom: 15px">
        <tr>
            <th class="text-center" style="font-weight: bold;">Total Orders</th>
            <th class="text-center" style="font-weight: bold;">Dealers App Orders</th>
        </tr>';
  foreach ($result as $row) {
    $output .= '
        <tr>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["total_orders"] . '</td>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["distinct_dealers"] . '</td>';
  }
  $output .= '</table></div>';
  return $output;
}

function dealers_orders($connect)
{
  $query = "SELECT 
                COUNT(om.dealer_sap) AS order_count,
                dl.name 
              FROM 
                order_main AS om
              JOIN 
                dealers AS dl 
              ON 
                dl.sap_no = om.dealer_sap
              WHERE 
                MONTH(om.created_at) = MONTH(CURDATE()) 
                AND YEAR(om.created_at) = YEAR(CURDATE())
              GROUP BY 
                om.dealer_sap, dl.name";
  $statement = $connect->prepare($query);
  $statement->execute();
  $result = $statement->fetchAll(PDO::FETCH_ASSOC);
  $output = '
    <div class="table-responsive">
    <h3>Orders by Dealers</h3>
    <table style="border: 1px solid black; border-collapse: collapse; width: 100%; margin-bottom: 15px">
        <tr>
            <th class="text-center" style="font-weight: bold;">Dealer Name</th>
            <th class="text-center" style="font-weight: bold;">Total Orders</th>
        </tr>';
  foreach ($result as $row) {
    $output .= '
        <tr>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["name"] . '</td>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["order_count"] . '</td>';
  }
  $output .= '</table></div>';
  return $output;
}

function error_log_locations($connect)
{
  $query = "SELECT 
                COUNT(*) AS record_count,
                name 
              FROM 
                omcs_dealers
              WHERE 
                MONTH(created_at) = MONTH(CURDATE()) 
                AND YEAR(created_at) = YEAR(CURDATE())
              GROUP BY 
                old_dealer_id, name";
  $statement = $connect->prepare($query);
  $statement->execute();
  $result = $statement->fetchAll(PDO::FETCH_ASSOC);
  $output = '
    <div class="table-responsive">
    
    <h3>Dealers Location Error Logs</h3>
    <table style="border: 1px solid black; border-collapse: collapse; width: 100%; margin-bottom: 15px">
        <tr>
            <th class="text-center" style="font-weight: bold;">Dealers</th>
        </tr>';
  foreach ($result as $row) {
    $output .= '
        <tr>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["name"] . '</td>';
    // <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["record_count"] . '</td>';
  }
  $output .= '</table></div>';
  return $output;
}

function rm_tm_order_log($connect)
{
  $query = "SELECT 
    us.name as user_name,
    us.privilege,
    CASE 
        WHEN us.privilege = 'TM' THEN (SELECT COUNT(id) FROM dealers WHERE tm = us.id)
        WHEN us.privilege = 'ASM' THEN (SELECT COUNT(id) FROM dealers WHERE asm = us.id)
        ELSE 0
    END AS total_dealers,
    SUM(it.status = 0 AND DATE(it.time) = CURDATE()) AS sum_pending,
    SUM(it.status = 0 
        AND it.sales_status = 0 
        AND it.measurement_status = 0 
        AND it.wet_stock_status = 0
        AND it.dispensing_status = 0 
        AND it.stock_variations_status = 0 
        AND it.inspection = 0 
        AND DATE(it.time) < CURDATE()) AS sum_Late,
    SUM(it.status = 0 AND DATE(it.time) > CURDATE()) AS sum_Upcoming,
    SUM(it.status = 1) AS sum_Complete,
    SUM(it.status = 0 
        AND (it.sales_status = 1 
         OR it.measurement_status = 1 
         OR it.wet_stock_status = 1 
         OR it.dispensing_status = 1 
         OR it.stock_variations_status = 1 
         OR it.inspection = 1)) AS only_visited,
    COUNT(*) AS total_visits
FROM 
    inspector_task AS it
JOIN 
    dealers AS dd ON dd.id = it.dealer_id
JOIN 
    users AS us ON us.id = it.user_id
JOIN 
    users AS usz ON usz.id = dd.zm
JOIN 
    users AS ust ON ust.id = dd.tm
JOIN 
    users AS usa ON usa.id = dd.asm 
              WHERE 
                MONTH(it.time) = MONTH(CURDATE()) 
                AND YEAR(it.time) = YEAR(CURDATE())
              GROUP BY 
                us.name, us.privilege";
  $statement = $connect->prepare($query);
  $statement->execute();
  $result = $statement->fetchAll(PDO::FETCH_ASSOC);
  $output = '
    <div class="table-responsive">
    
    <h3>Visits Detail</h3>
    <table style="border: 1px solid black; border-collapse: collapse; width: 100%; margin-bottom: 15px">
        <tr>
            <th class="text-center" style="font-weight: bold;">Username</th>
            <th class="text-center" style="font-weight: bold;">Role</th>
            <th class="text-center" style="font-weight: bold;">Total Sites</th>
            <th class="text-center" style="font-weight: bold;">Total Visit</th>
            <th class="text-center" style="font-weight: bold;">Pending</th>
            <th class="text-center" style="font-weight: bold;">Overdue</th>
            <th class="text-center" style="font-weight: bold;">Upcoming</th>
            <th class="text-center" style="font-weight: bold;">Complete</th>
            <th class="text-center" style="font-weight: bold;">Only visit not complete</th>
        </tr>';
  foreach ($result as $row) {
    $output .= '
        <tr>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["user_name"] . '</td>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["privilege"] . '</td>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["total_dealers"] . '</td>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["total_visits"] . '</td>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["sum_pending"] . '</td>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["sum_Late"] . '</td>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["sum_Upcoming"] . '</td>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["sum_Complete"] . '</td>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["only_visited"] . '</td>';
  }
  $output .= '</table></div>';
  return $output;
}

function user_logs($connect)
{
  $query = "SELECT ul.*, us.name as username
              FROM user_log AS ul
              LEFT JOIN users AS us ON us.id = ul.created_by
              WHERE MONTH(ul.created_at) = MONTH(CURDATE()) 
              AND YEAR(ul.created_at) = YEAR(CURDATE()) 
              ORDER BY ul.id DESC";
  $statement = $connect->prepare($query);
  $statement->execute();
  $result = $statement->fetchAll(PDO::FETCH_ASSOC);
  $output = '
    <div class="table-responsive">
    
    <h3>User Logs</h3>
    <table style="border: 1px solid black; border-collapse: collapse; width: 100%; margin-bottom: 15px">
        <tr>
            <th class="text-center" style="font-weight: bold;">Activity</th>
            <th class="text-center" style="font-weight: bold;">Created At</th>
            <th class="text-center" style="font-weight: bold;">Created By</th>
        </tr>';
  foreach ($result as $row) {
    $output .= '
        <tr>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["message"] . '</td>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["created_at"] . '</td>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $row["username"] . '</td>';
  }
  $output .= '</table></div>';
  return $output;
}

function report_detail($connect)
{
  $currentDate = new DateTime();
  $firstDateOfMonth = new DateTime('first day of this month');
  $currentDateFormatted = $currentDate->format('Y-m-d');
  $firstDateOfMonthFormatted = $firstDateOfMonth->format('Y-m-d');
  $datesCombined = $firstDateOfMonthFormatted . " - " . $currentDateFormatted;

  $output = '
    <div class="table-responsive">
    
    <table style="border: 1px solid black; border-collapse: collapse; width: 100%; margin-bottom: 15px">
        <tr>
            <th class="text-center" style="font-weight: bold;">Report Name</th>
            <th class="text-center" style="font-weight: bold;">Date (From - To)</th>
        </tr>';
  $output .= '
        <tr>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">Dealers Dashboard Detail</td>
            <th style="border: 1px solid black; padding: 10px; text-align: center;">' . $datesCombined . '</td>';
  $output .= '</table></div>';
  return $output;
}

function smtp_mailer($email_addresses, $time, $dealer_name, $db)
{
  $connect = new PDO("mysql:host=localhost;dbname=omcs", "root", "Ptoptrack@(!!@");
  $file_name = 'files/Dealers_Detail_' . md5(rand()) . '.pdf';
  $html_code = report_detail($connect);

  $html_code .= dealers_details($connect);
  $html_code .= order_detailed($connect);
  $html_code .= dealers_orders($connect);
  $html_code .= error_log_locations($connect);
  $html_code .= rm_tm_order_log($connect);
  // $html_code .= user_logs($connect);

  // $pdf = new Pdf();
  // $pdf->load_html($html_code);
  // $pdf->render();
  // $file = $pdf->output();

  // file_put_contents($file_name, $file);

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
  // $mail->AddAttachment($file_name);

  // Inline CSS styles for table elements
  $table_style = 'style="border: 1px solid black; border-collapse: collapse;"';
  $th_td_style = 'style="border: 1px solid black; padding: 10px;"';

  $html_code = str_replace('<table', '<table ' . $table_style, $html_code);
  $html_code = str_replace('<th', '<th ' . $th_td_style, $html_code);
  $html_code = str_replace('<td', '<td ' . $th_td_style, $html_code);

  foreach ($email_addresses as $to) {
    $mail->ClearAddresses();
    $mail->AddAddress($to);
    $mail->Subject = $dealer_name . ' ' . $time;
    $mail->Body = $html_code;

    if (!$mail->Send()) {
      echo 'Failed to send email to ' . $to . '<br>';
    } else {
      echo 'Email sent to ' . $to . '<br>';
    }
  }

  // unlink($file_name);
}

?>