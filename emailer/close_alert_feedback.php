
<?php
error_reporting(0);
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

$time = date('Y-m-d H:i:s');

$message = '';

$connect = new PDO("mysql:host=localhost;dbname=ptoptrack", "root", "Ptoptrack@(!!@");




echo smtp_mailer($email,$time,$db);






function smtp_mailer($to,$time,$db){
	$connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");

	$file_name = md5(rand()) . '.pdf';
	$html_code = '<div class="container">
    <div class="row">
        <div class="col-md-12">
        <h2 style="font-weight: bold;    color: #3e3ea7;font-size: 72px;font-style: italic;font-weight: bold;text-decoration: underline">Sitara</h2>
        
        </div>
            <h6>Forgot password</h6>
            <br/>
            <h6>Time : '.$time.'</h6>
        

        

    </div>
</div>';

	
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
	$mail->IsHTML(true);   				//Adds an attachment from a path on the filesystem
	$mail->Subject = 'Response created '.$time;			//Sets the Subject of the message
	$mail->Body = '<h1>Sitara.</h1> <h3>Hi '.$to.'</h3><p> Thank You For Submitting Your Response </p>';				//An HTML or plain text message body
	if($mail->Send())								//Send an Email. Return true on success or false on error
	{
		echo $message = 'Email Send Successfully to '.$to.' . Please Check your email ';
        
	}
    else{
        echo 'Mail not send . '.$to.' this email address is not correct . Please enter correct email addreess.<br>';
    }
	unlink($file_name);
}

?>






