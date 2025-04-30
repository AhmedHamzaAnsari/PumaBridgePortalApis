<?php

// error_reporting(0);
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'Ptoptrack@(!!@');
define('DB_DATABASE', 'omcs');
$db = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

error_reporting(0);
//index.php
ini_set('memory_limit', '-1');
set_time_limit(500);

include('class/class.phpmailer.php');
include('pdf.php');

$today = date("Y-m-d");
$_to_today = date("Y-m-d H:i:s");
$_to_today . 'Run time <br>';
$report_time = 1;
$email = 'ahmedhamzaansari.99@gmail.com';
$report = 'vehicle';
$user_id;
$privilege = 'Admin';
$time_1 = "";
$black_1 = "";
$cartraige_name = "";
$report_timing = "";

$dealer_id = $_GET['dealer_id'];
$task_id = $_GET['task_id'];




// Check if the current hour is 9 AM
if ($dealer_id != "" || $task_id != "") {
    // Execute your PHP script here

    $sql_get_cartraige_no = "SELECT * FROM dealers where id='$dealer_id';";
    // echo $sql_get_cartraige_no .'<br>';
    $result_contact = mysqli_query($db, $sql_get_cartraige_no);

    $count_contact = mysqli_num_rows($result_contact);
    // echo $count_contact . ' hamza <br>';

    if ($count_contact > 0) {
        while ($row = mysqli_fetch_array($result_contact)) {
            $name = $row["name"];
            $email = $row["email"];
            echo smtp_mailer($email, date('Y-m-d H:i:s'), $name, $dealer_id, $task_id, $db);

        }
    }



} else {
    // Do nothing or perform other actions
    echo "IO Required.";
}
$connect = new PDO("mysql:host=localhost;dbname=omcs", "root", "Ptoptrack@(!!@");




function get_task_sales_performance($connect, $task_id, $dealer_id)
{


    $query = "SELECT rr.*,pp.name,tt.lorry_no FROM dealer_wet_stock as rr 
    join dealers_products as pp on pp.id=rr.product_id 
    join dealers_lorries as tt on tt.id=rr.tank_id where rr.task_id=$task_id and rr.dealer_id=$dealer_id;";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();
    $output = '
	<div class="table-responsive">
	<style>
    table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
    }
    th, td {
        padding:10px;
    }
    </style>
		<table >
			<tr>
            <th class="text-center">S.No</th>
            <th class="text-center">Product</th>
            <th class="text-center">Tank #</th>
            <th class="text-center">Old Dip</th>
            <th class="text-center">New Dip</th>
            <th class="text-center">Time</th>

			</tr>
	';
    $wet_stock = 1;
    foreach ($result as $row) {

        $output .= '
			<tr>
			<td class="text-center">' . $wet_stock . '</td>
			<td >' . $row["name"] . '</td>
			<td >' . $row["lorry_no"] . '</td>
			<td>' . $row["dip_old"] . '</td>
			<td>' . $row["dip_new"] . '</td>
			<td>' . $row["created_at"] . '</td>



            
			</tr>
		';
        $wet_stock++;
    }
    $output .= '
		</table>
	</div>
	';
    return $output;
}

function get_task_wet_stock($connect, $task_id, $dealer_id)
{


    $query = "SELECT rr.*,pp.name,tt.lorry_no FROM dealer_wet_stock as rr 
    join dealers_products as pp on pp.id=rr.product_id 
    join dealers_lorries as tt on tt.id=rr.tank_id where rr.task_id=$task_id and rr.dealer_id=$dealer_id;";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();
    $output = '
	<div class="table-responsive">
	<style>
    table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
    }
    th, td {
        padding:10px;
    }
    </style>
		<table >
			<tr>
            <th class="text-center">S.No</th>
            <th class="text-center">Product</th>
            <th class="text-center">Tank #</th>
            <th class="text-center">Old Dip</th>
            <th class="text-center">New Dip</th>
            <th class="text-center">Time</th>

			</tr>
	';
    $wet_stock = 1;
    foreach ($result as $row) {

        $output .= '
			<tr>
			<td class="text-center">' . $wet_stock . '</td>
			<td >' . $row["name"] . '</td>
			<td >' . $row["lorry_no"] . '</td>
			<td>' . $row["dip_old"] . '</td>
			<td>' . $row["dip_new"] . '</td>
			<td>' . $row["created_at"] . '</td>



            
			</tr>
		';
        $wet_stock++;
    }
    $output .= '
		</table>
	</div>
	';
    return $output;
}

function get_task_dispenser_unit_reading($connect, $task_id, $dealer_id)
{


    $query = "SELECT rr.*,pp.name as product_name,tt.name as nozle_name FROM dealer_reconcilation as rr 
    join dealers_products as pp on pp.id=rr.product_id 
    join dealers_nozzel as tt on tt.id=rr.nozle_id where rr.task_id=$task_id and rr.dealer_id=$dealer_id;";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();
    $output = '
	<div class="table-responsive">
	<style>
    table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
    }
    th, td {
        padding:10px;
    }
    </style>
		<table >
			<tr>
            <th class="text-center">S.No</th>
            <th class="text-center">Product</th>
            <th class="text-center">Nozel #</th>
            <th class="text-center">Old Dip</th>
            <th class="text-center">New Dip</th>
            <th class="text-center">Time</th>

			</tr>
	';
    $wet_stock = 1;
    foreach ($result as $row) {

        $output .= '
			<tr>
			<td class="text-center">' . $wet_stock . '</td>
			<td >' . $row["product_name"] . '</td>
			<td >' . $row["nozle_name"] . '</td>
			<td>' . $row["old_reading"] . '</td>
			<td>' . $row["new_reading"] . '</td>
			<td>' . $row["created_at"] . '</td>



            
			</tr>
		';
        $wet_stock++;
    }
    $output .= '
		</table>
	</div>
	';
    return $output;
}

function get_task_stock_variation($connect, $task_id, $dealer_id)
{


    $query = "SELECT rr.*,pp.name FROM dealers_stock_variations as rr 
    join dealers_products as pp on pp.id=rr.product_id where rr.task_id=$task_id and rr.dealer_id=$dealer_id;";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();
    $output = '
	<div class="table-responsive">
	<style>
    table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
    }
    th, td {
        padding:10px;
    }
    </style>
		<table >
			<tr>
                <th class="text-center">S.No</th>
                <th class="text-center">Product</th>
                <th class="text-center">Opening Stock</th>
                <th class="text-center">Purchase During Inspection period</th>
                <th class="text-center">Total Product Available for Sale</th>
                <th class="text-center">Sales as per meter reading</th>
                <th class="text-center">Book Stock</th>
                <th class="text-center">Current Physical Stock</th>
                <th class="text-center">Gain/Loss</th>
                <th class="text-center">Time</th>

			</tr>
	';
    $wet_stock = 1;
    foreach ($result as $row) {

        $output .= '
			<tr>
			<td class="text-center">' . $wet_stock . '</td>
			<td >' . $row["name"] . '</td>
			<td >' . $row["opening_stock"] . '</td>
			<td>' . $row["purchase_during_inspection_period"] . '</td>
			<td>' . $row["total_product_available_for_sale"] . '</td>
			<td>' . $row["sales_as_per_meter_reading"] . '</td>
			<td>' . $row["book_stock"] . '</td>
			<td>' . $row["current_physical_stock"] . '</td>
			<td>' . $row["gain_loss"] . '</td>
			<td>' . $row["created_at"] . '</td>



            
			</tr>
		';
        $wet_stock++;
    }
    $output .= '
		</table>
	</div>
	';
    return $output;
}

function get_task_measurement_price($connect, $task_id, $dealer_id, $db)
{


    $query = "SELECT * FROM dealer_measurement_pricing_action where task_id=$task_id";

    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $id = $row['id'];

    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();


    $query1 = "SELECT mp.*,dc.name as dispensor_name FROM dealer_measurement_pricing as mp 
    join dealers_dispenser as dc on dc.id=mp.dispenser_id where mp.main_id='$id'";

    $statement1 = $connect->prepare($query1);
    $statement1->execute();
    $result1 = $statement1->fetchAll();
    $output = '
	<div class="table-responsive">
	<style>
    table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
    }
    th, td {
        padding:10px;
    }
    </style>
		<table >
			<tr>
            <th>Appreation Of Dealer </th>
            <th>Measure taken to overcome shortage</th>
            <th>Warning</th>
            <th>PMG ogra price</th>
            <th>PMG pump price</th>
            <th>PMG Variance</th>
            <th>HSD ogra price</th>
            <th>HSD pump price</th>
            <th>HSD Variance</th>

			</tr>
	';
    $wet_stock = 1;
    foreach ($result as $row) {

        $output .= '
			<tr>
			<td >' . $row["appreation"] . '</td>
			<td >' . $row["measure_taken"] . '</td>
			<td>' . $row["warning"] . '</td>
			<td>' . $row["pmg_ogra_price"] . '</td>
			<td>' . $row["pmg_pump_price"] . '</td>
			<td>' . $row["pmg_variance"] . '</td>
			<td>' . $row["hsd_ogra_price"] . '</td>
			<td>' . $row["hsd_pump_price"] . '</td>
			<td>' . $row["hsd_variance"] . '</td>



            
			</tr>
		';
        $wet_stock++;
    }
    $output .= '
		</table>


        <table >
			<tr>
            <th>S # </th>
            <th>Dispenser</th>
            <th>PMG Accurate</th>
            <th>PMG Shorage (%)</th>
            <th>HSD Accurate</th>
            <th>HSD Shorage (%)</th>

			</tr>
	';
    $wet_stock = 1;
    foreach ($result1 as $row) {

        $output .= '
			<tr>
			<td class="text-center">' . $wet_stock . '</td>
			<td >' . $row["dispensor_name"] . '</td>
			<td >' . $row["pmg_accurate"] . '</td>
			<td>' . $row["shortage_pmg"] . '</td>
			<td>' . $row["hsd_accurate"] . '</td>
			<td>' . $row["shortage_hsd"] . '</td>



            
			</tr>
		';
        $wet_stock++;
    }
    $output .= '
		</table>
	</div>
	';
    return $output;
}

function get_task_inspection_response($connect, $task_id, $dealer_id, $db)
{


    $query = "SELECT * FROM survey_category order by id asc;";

    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $id = $row['id'];

    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();




    $output = '
	<div class="table-responsive">
	<style>
    table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
    }
    th, td {
        padding:10px;
    }
    </style>

        
		
	';
    foreach ($result as $row) {

        $cat_id = $row['id'];

        $query1 = "SELECT sr.*,sq.question,rf.file as cancel_file FROM survey_response as sr 
        join survey_category_questions as sq on sq.id=sr.question_id
        LEFT JOIN survey_response_files rf ON (rf.question_id = sr.question_id and rf.inspection_id=sr.inspection_id)
        where sr.category_id=$cat_id and sr.inspection_id='$task_id' and sr.dealer_id='$dealer_id';";
        $statement1 = $connect->prepare($query1);
        $statement1->execute();
        $result1 = $statement1->fetchAll();
        $output .= '<h3>' . $row["name"] . '</h3>';

        $output .= ' <table >
        <tr>
        <th>S # </th>
        <th>Question</th>
        <th>Response</th>
        <th>Comments</th>
       

        </tr>
        ';
        $wet_stock = 1;
        foreach ($result1 as $row1) {

            $output .= '
            <tr>
            <td class="text-center">' . $wet_stock . '</td>
            <td >' . $row1["question"] . '</td>
            <td >' . $row1["response"] . '</td>
            <td >' . $row1["comment"] . '</td>
            



        
        </tr>
         ';
            $wet_stock++;
        }
        $output .= '
    </table>
    ';

    }
    $output .= '
		

       	</div>
	';
    return $output;
}


function smtp_mailer($to, $time, $dealer_name, $dealer_id, $task_id, $db)
{
    $connect = new PDO("mysql:host=localhost;dbname=omcs", "root", "Ptoptrack@(!!@");
    $alert_today = date("Y-m-d");
    $alert_today_time = date("Y-m-d H:i:s");
    // $verificationCode = generateVerificationCode();
    // $alert_link = "";
    // $alert_link = "http://151.106.17.246:8080/sitara/email_alert_link.php?id=" . $cartraige_id . "&from=" . $alert_today . "&name=" . $cartraige_name . "&interval=" . $currentHour . "&e_id=" . $to . "";
    $file_name = 'files/Sales Performance_' . md5(rand()) . '.pdf';
    $html_code = '<div class="container">
                    <div class="row">
                        <div class="col-md-12">
                        <h2 style="font-weight: bold;color: #3e3ea7;font-size: 72px;font-style: italic;font-weight: bold;text-decoration: underline">PUMA</h2>
                        
                        </div>
                            <h6>Report Name : Sales Performance </h6>
                            <br/>
                            <h6>Time : ' . $time . '</h6>
                        
    
                        
    
                    </div>
                </div>';

    $html_code .= get_task_sales_performance($connect, $task_id, $dealer_id);

    $pdf = new Pdf();
    $pdf->load_html($html_code);
    $pdf->render();
    $file = $pdf->output();

    file_put_contents($file_name, $file);

    // ---------------------------------------------Sales Performance End----------------------------------------------------------

    $file_name1 = 'files/Wet Stock Management_' . md5(rand()) . '.pdf';
    $html_code1 = '<div class="container">
                    <div class="row">
                        <div class="col-md-12">
                        <h2 style="font-weight: bold;color: #3e3ea7;font-size: 72px;font-style: italic;font-weight: bold;text-decoration: underline">PUMA</h2>
                        
                        </div>
                            <h6>Report Name : Wet Stock Management </h6>
                            <br/>
                            <h6>Time : ' . $time . '</h6>
                        
    
                        
    
                    </div>
                </div>';

    $html_code1 .= get_task_wet_stock($connect, $task_id, $dealer_id);

    $pdf1 = new Pdf();
    $pdf1->load_html($html_code1);
    $pdf1->render();
    $file1 = $pdf1->output();
    file_put_contents($file_name1, $file1);

    // ---------------------------------------------Wet Stock End----------------------------------------------------------

    $file_name2 = 'files/Dispensing Unit Meter Reading_' . md5(rand()) . '.pdf';
    $html_code2 = '<div class="container">
                <div class="row">
                    <div class="col-md-12">
                    <h2 style="font-weight: bold;color: #3e3ea7;font-size: 72px;font-style: italic;font-weight: bold;text-decoration: underline">PUMA</h2>
                    
                    </div>
                        <h6>Report Name : Dispensing Unit Meter Reading </h6>
                        <br/>
                        <h6>Time : ' . $time . '</h6>
                    

                    

                </div>
            </div>';

    $html_code2 .= get_task_dispenser_unit_reading($connect, $task_id, $dealer_id);

    $pdf2 = new Pdf();
    $pdf2->load_html($html_code2);
    $pdf2->render();
    $file2 = $pdf2->output();
    file_put_contents($file_name2, $file2);

    // ---------------------------------------------Dispensing Unit Meter Reading End----------------------------------------------------------

    $file_name3 = 'files/Stock Variations_' . md5(rand()) . '.pdf';
    $html_code3 = '<div class="container">
                <div class="row">
                    <div class="col-md-12">
                    <h2 style="font-weight: bold;color: #3e3ea7;font-size: 72px;font-style: italic;font-weight: bold;text-decoration: underline">PUMA</h2>
                    
                    </div>
                        <h6>Report Name : Stock Variations </h6>
                        <br/>
                        <h6>Time : ' . $time . '</h6>
                    

                    

                </div>
            </div>';

    $html_code3 .= get_task_stock_variation($connect, $task_id, $dealer_id);

    $pdf3 = new Pdf();
    $pdf3->load_html($html_code3);
    $pdf3->render();
    $file3 = $pdf3->output();
    file_put_contents($file_name3, $file3);

    // ---------------------------------------------Stock Variations End----------------------------------------------------------

    $file_name4 = 'files/Measurement & Price_' . md5(rand()) . '.pdf';
    $html_code4 = '<div class="container">
                <div class="row">
                    <div class="col-md-12">
                    <h2 style="font-weight: bold;color: #3e3ea7;font-size: 72px;font-style: italic;font-weight: bold;text-decoration: underline">PUMA</h2>
                    
                    </div>
                        <h6>Report Name : Measurement & Price </h6>
                        <br/>
                        <h6>Time : ' . $time . '</h6>
                    

                    

                </div>
            </div>';

    $html_code4 .= get_task_measurement_price($connect, $task_id, $dealer_id, $db);

    $pdf4 = new Pdf();
    $pdf4->load_html($html_code4);
    $pdf4->render();
    $file4 = $pdf4->output();
    file_put_contents($file_name4, $file4);

    // ---------------------------------------------Stock Variations End----------------------------------------------------------

    $file_name5 = 'files/Survey Response_' . md5(rand()) . '.pdf';
    $html_code5 = '<div class="container">
                <div class="row">
                    <div class="col-md-12">
                    <h2 style="font-weight: bold;color: #3e3ea7;font-size: 72px;font-style: italic;font-weight: bold;text-decoration: underline">PUMA</h2>
                    
                    </div>
                        <h6>Report Name : Survey Response </h6>
                        <br/>
                        <h6>Time : ' . $time . '</h6>
                    

                    

                </div>
            </div>';

    $html_code5 .= get_task_inspection_response($connect, $task_id, $dealer_id, $db);

    $pdf5 = new Pdf();
    $pdf5->load_html($html_code5);
    $pdf5->render();
    $file5 = $pdf5->output();
    file_put_contents($file_name5, $file5);

    // ---------------------------------------------Stock Variations End----------------------------------------------------------


    // require 'class/class.phpmailer.php';
    $mail = new PHPMailer();
    $mail->SMTPDebug = 3;
    $mail->IsSMTP();
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 587;
    $mail->IsHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Username = "sitaras222@gmail.com";
    $mail->Password = "kjyqvamkejoqtbki";
    $mail->SetFrom("sitaras222@gmail.com");
    $mail->AddAddress($to);
    $mail->WordWrap = 50; //Sets word wrapping on the body of the message to a given number of characters
    $mail->IsHTML(true); //Sets message type to HTML				
    $mail->AddAttachment($file_name5); //Adds an attachment from a path on the filesystem
    $mail->AddAttachment($file_name); //Adds an attachment from a path on the filesystem
    $mail->AddAttachment($file_name1); //Adds an attachment from a path on the filesystem
    $mail->AddAttachment($file_name2); //Adds an attachment from a path on the filesystem
    $mail->AddAttachment($file_name3); //Adds an attachment from a path on the filesystem
    $mail->AddAttachment($file_name4); //Adds an attachment from a path on the filesystem
    $mail->Subject = $dealer_name . ' Inspection Report ' . $time; //Sets the Subject of the message
    $mail->Body = '<h1>PUMA.<h1><h3>Please Find details report of Inspectin in attach PDF File.</h3>'; //An HTML or plain text message body
    if ($mail->Send()) //Send an Email. Return true on success or false on error
    {

        $sql_update = "UPDATE `inspector_task`
        SET 
        `email_status` = 1
        WHERE `id` = $task_id;";

        if (mysqli_query($db, $sql_update)) {
            echo 1;

        } else {
            echo 0;

        }


    } else {
        echo 0;
    }
    unlink($file_name);
}
function generateVerificationCode($length = 6)
{
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';

    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $code;
}



// echo $list;


?>