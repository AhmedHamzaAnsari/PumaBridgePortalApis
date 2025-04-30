
<?php
ini_set('memory_limit', '-1');
set_time_limit(500);
error_reporting(0);
   define('DB_SERVER', 'localhost');
   define('DB_USERNAME', 'root');
   define('DB_PASSWORD', 'Ptoptrack@(!!@');
   define('DB_DATABASE', 'sitara');
   $db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);


//index.php
include('class/class.phpmailer.php');
include('pdf.php');
$today = date("Y-m-d");
$email_arr = $_POST['email_arr'];







$message = '';

$connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");

function fetch_customer_data($connect)
{

	$privilege=$_GET['privilege'];

	if($privilege=='Cartraige'){
		$user_id=$_GET['user_id'];

	}
	else{
		$user_id='1';

	}
	$query = "SELECT * FROM `vehicle_location`";
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
                <th class="text-center">Plate.No</th>
                <th class="text-center">Location</th>
                <th class="text-center">Speed</th>
                <th class="text-center">Time</th>
			</tr>
	';
	foreach($result as $row)
	{
		$output .= '
			<tr>
			<td class="text-center">'.$row["car_name"].'</td>
			<td >'.$row["vlocation"].'</td>
			<td>'.$row["speed"].'</td>
			<td>'.$row["time"].'</td>


            
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
	    echo smtp_mailer($value,$today);

    }



function smtp_mailer($to,$time){
	$connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");

	$file_name = md5(rand()) . '.pdf';
	$html_code = '<div class="container">
    <div class="row">
        <div class="col-md-12">
        <h2 style="font-weight: bold;    color: #3e3ea7;font-size: 72px;font-style: italic;font-weight: bold;text-decoration: underline">SITARA</h2>
        
        </div>
            <h6>Report Name : Current Location</h6>
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
	$mail->Subject = 'Overspeed Report '.$time;			//Sets the Subject of the message
	$mail->Body = '<h1>SITARA.<h1><h3>Please Find details report of Overspeed in attach PDF File.</h3>';				//An HTML or plain text message body
	if($mail->Send())								//Send an Email. Return true on success or false on error
	{
		$message = '<label class="text-success">Customer Details has been send successfully...</label>';
	}
	unlink($file_name);
}

?>






