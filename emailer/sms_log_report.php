
<?php
// error_reporting(0);
   define('DB_SERVER', 'localhost');
   define('DB_USERNAME', 'root');
   define('DB_PASSWORD', 'Ptoptrack@(!!@');
   define('DB_DATABASE', 'sitara');
   $db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);


//index.php
ini_set('memory_limit', '-1');
set_time_limit(500);

include('class/class.phpmailer.php');
include('pdf.php');
$today = date("Y-m-d");
$email=$_GET['email'];
$report=$_GET['report'];



$message = '';

$connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");

function fetch_customer_data($connect)
{
    $curr_date = date('Y-m-d');
$next_date = new dateTime($curr_date);
$next_date -> modify('-1 day');
$tommorrow = $next_date->format('Y-m-d');


    $query = "SELECT * from sitara.trip_start_sms_log where time >='$tommorrow 15:00:00' or time <='$curr_date 15:00:00'";
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
            <th >Number </th>
            <th >Msg</th>
            <th >Time</th>
            <th >Trip id</th>

			</tr>
	';
	foreach($result as $row)
	{
		$output .= '
			<tr>
            <td class="text-center">'.$row["send_to"].'</td>
			<td >'.$row["message"].'</td>
			<td >'.$row["time"].'</td>
			<td>'.$row["sub_id"].'</td>
			</tr>
		';
	}
	$output .= '
		</table>
	</div>
	';

    
	return $output ;
}
function fetch_customer_data_close($connect)
{
    $curr_date = date('Y-m-d');
$next_date = new dateTime($curr_date);
$next_date -> modify('-1 day');
$tommorrow = $next_date->format('Y-m-d');
    
    $query_close = "SELECT * from sitara.trip_close_sms_log where time >='$tommorrow 15:00:00' or time <='$curr_date 15:00:00';";
	$statement_close = $connect->prepare($query_close);
	$statement_close->execute();
	$result_close = $statement_close->fetchAll();
	$output_close = '
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
            <th >Number </th>
            <th >Msg</th>
            <th >Time</th>
            <th >Trip id</th>

			</tr>
	';
	foreach($result_close as $row)
	{
		$output_close .= '
			<tr>
            <td class="text-center">'.$row["send_to"].'</td>
			<td >'.$row["message"].'</td>
			<td >'.$row["time"].'</td>
			<td>'.$row["sub_id"].'</td>
			</tr>
		';
	}
	$output_close.= '
		</table>
	</div>
	';

	return $output_close ;
}

// $sql__="SELECT * FROM `email_scedule`";
// $result__ = mysqli_query($db,$sql__);

// while( $row = mysqli_fetch_array($result__) ){
// 	// $userid = $row['id'];
// 	$report = $row['report'];
// 	$time = $row['time'];
// 	$email = $row['email'];
// 	echo "Hamza ".$time;
	echo smtp_mailer($email,$today);
	$sql_update = "UPDATE `email_scedule` SET `status`='1' WHERE email='$email' and report='$report'";
        echo $sql_update;
        if(mysqli_query($db, $sql_update)){
            echo "Status were updated successfully.";
            // header("location: manageGroup.php");
        } else {
            echo $sql_update;
            echo "ERROR: Could not able to execute $sql_update. " . mysqli_error($db);
        }
	

// }
$myObj = new stdClass();
$myObj->status = 200;
$myObj->response = "success";


$myJSON = json_encode($myObj);

echo $myJSON;

function smtp_mailer($to,$time){
	$connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");

	$file_name = md5(rand()) . '.pdf';
	$html_code = '<div class="container">
    <div class="row">
        <div class="col-md-12">
        <h2 style="font-weight: bold;    color: #3e3ea7;font-size: 72px;font-style: italic;font-weight: bold;text-decoration: underline">SITARA</h2>
        
        </div>
            <h6>Report Name : SMS Log</h6>
            <br/>
            <h6>Time : '.$time.'</h6>
        

        

    </div>
</div>';
    $html_code .= '<h2>Trip Start SMS</h2>';

	$html_code .= fetch_customer_data($connect);
	$html_code .= '<h2>Trip Close SMS</h2>';
	$html_code .= fetch_customer_data_close($connect);
	$pdf = new Pdf();
	$pdf->load_html($html_code);
	$pdf->render();
	$file = $pdf->output();
	file_put_contents($file_name, $file);
	
	// require 'class/class.phpmailer.php';
	$mail = new PHPMailer(); 
	$mail->SMTPDebug  = 3;
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
	$mail->WordWrap = 50;							//Sets word wrapping on the body of the message to a given number of characters
	$mail->IsHTML(true);							//Sets message type to HTML				
	$mail->AddAttachment($file_name);     				//Adds an attachment from a path on the filesystem
	$mail->Subject = 'SMS log Report '.$time;			//Sets the Subject of the message
	$mail->Body = '<h1>SITARA.<h1><h3>Please Find details report of SMS log in attach PDF File.</h3>';				//An HTML or plain text message body
	if($mail->Send())								//Send an Email. Return true on success or false on error
	{
		$message = '<label class="text-success">Customer Details has been send successfully...</label>';
	}
	unlink($file_name);
}

?>






