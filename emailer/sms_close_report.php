
<?php
error_reporting(0);
ini_set('memory_limit', '-1');
set_time_limit(500);

   define('DB_SERVER', 'localhost');
   define('DB_USERNAME', 'root');
   define('DB_PASSWORD', 'Ptoptrack@(!!@');
   define('DB_DATABASE', 'sitara');
   $db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);


//index.php
include('class/class.phpmailer.php');
include('pdf.php');
// $today = date("Y-m-d");
$email_arr = $_POST['email_arr'];
$from = $_POST['from'];
$to = $_POST['to'];
$sms_name ;

$today = $from . ' to ' . $to ;
$check = $_POST['check'];

if($check==='close'){
    $sms_name ='close';
}
else{
    $sms_name ='start';

}




$message = '';

$connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");

function fetch_customer_data($connect)
{
	// $today = date("Y-m-d");vehicle_name
    $query;
    $check = $_POST['check'];

    $from = $_POST['from'];
    $to = $_POST['to'];

    $today = $from . ' to ' . $to ;

    
    // $email_r = implode($email,',');
    if($check==='close'){
	    $query = "SELECT * FROM sitara.trip_close_sms_log  where time >='$from' and time <='$to' order by id desc;";
        

    }
    else{

        $query = "SELECT * FROM sitara.trip_start_sms_log  where time >='$from' and time <='$to' order by id desc;";
    
    }

    


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
	return $output;
}


    foreach ($email_arr as $value) {
        // echo "$value <br>";
	    echo smtp_mailer($value,$today,$sms_name);

    }



function smtp_mailer($to,$time,$sms_name){
	$connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");

	$file_name = md5(rand()) . '.pdf';
	$html_code = '<div class="container">
    <div class="row">
        <div class="col-md-12">
        <h2 style="font-weight: bold;    color: #3e3ea7;font-size: 72px;font-style: italic;font-weight: bold;text-decoration: underline">SITARA</h2>
        
        </div>
            <h6>Report Name : Trip '.$sms_name.' SMS Report</h6>
            <br/>
            <h6>Time : '.$time.'</h6>
        

        

    </div>
</div>';
	$html_code .= fetch_customer_data($connect);
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
	$mail->Subject = 'Trip '.$sms_name.' SMS Report '.$time;			//Sets the Subject of the message
	$mail->Body = '<h1>SITARA.<h1><h3>Please Find details of trip '.$sms_name.' SMS Report in attach PDF File.</h3>';				//An HTML or plain text message body
	if($mail->Send())								//Send an Email. Return true on success or false on error
	{
		echo $message = '<label class="text-success">Report Details has been send successfully...</label>';
	}
    else {
        echo ' Mail Not Send';
    }
	unlink($file_name);
}

?>






