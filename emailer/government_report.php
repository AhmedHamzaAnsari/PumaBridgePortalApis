
<?php
ini_set('memory_limit', '-1');
set_time_limit(500);
// error_reporting(0);
   define('DB_SERVER', 'localhost');
   define('DB_USERNAME', 'root');
   define('DB_PASSWORD', 'Ptoptrack@(!!@');
   define('DB_DATABASE', 'sitara');
   $db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);


//index.php
include('class/class.phpmailer.php');
include('pdf.php');
$today = date("Y-m-d");
$email_arr = $_GET['email_arr'];
$com_no = $_GET['com_no'];
// $ui_data = $_GET['ui_data'];

echo $com_no;







$message = '';

$connect = new PDO("mysql:host=localhost;dbname=complains", "root","Ptoptrack@(!!@");

function fetch_customer_data($connect)
{
    $com_no = $_GET['com_no'];


	$query = "SELECT comp.*,us.name,us.login FROM complains.complaint as comp join complains.users as us on comp.user_id=us.id where comp.id='$com_no'";
	$statement = $connect->prepare($query);
	$statement->execute();
	$result = $statement->fetchAll();
	$output = '<table role="presentation" style="width:100%;border:none;border-spacing:0;">
    <style>
    .label {
         
    }

    .db_info {
         
        text-decoration: underline;
        float: right;
        margin-left:15px;
    }
    </style>
    <tr>
        <td align="center" style="padding:0;">
           <table role="presentation"
                style="width:94%;max-width:600px;border:none;border-spacing:0;text-align:left;font-family:Arial,sans-serif;font-size:16px;line-height:22px;color:#363636;">
                   <tr>
                    <td
                        style="padding:30px;text-align:center;font-size:12px;background-color:#404040;color:#cccccc;">
                        
                        <p style="margin:0;font-size:30px;line-height:20px; color: white;">Complain</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:30px;background-color:#ffffff;">
                         <img src="https://www.shrc.org.pk/images/logo-loading.png" width="115" alt=""
                                style="width:80%;max-width:115px;margin-bottom:20px;"> 
                                
                                <p style="margin:0;"><b>Dear Sir:</b> Hamza</p>
                                <p style="margin:0;"><b>Name:</b> </p>
                                <p style="margin:0;"><b>Email:</b> </p>
                        
                    </td>
                </tr>'
                ;
                foreach($result as $row)
                {
                    $output .= '
                <tr>
                    <td style="padding:30px;background-color:#ffffff;">
                       
                    <span class="label">Complainte Name : </span>
                    <span class="db_info ml-5" style=" text-decoration: underline;float: right;margin-left:15px">'.$row["c_name"].'</span>

                    <br>
                    <hr>
                    <span class="label">Address : </span>
                    <span class="db_info ml-5" style=" text-decoration: underline;float: right;margin-left:15px">'.$row["address"].'</span>

                    <br>
                    <hr>
                    <span class="label">C.N.I.C : </span>
                    <span class="db_info ml-5" style=" text-decoration: underline;float: right;margin-left:15px">'.$row["nic"].'</span>

                    <br>
                    <hr>
                    <span class="label">District : </span>
                    <span class="db_info ml-5" style=" text-decoration: underline;float: right;margin-left:15px">'.$row["district"].'</span>

                    <br>
                    <hr>
                    <span class="label">Province : </span>
                    <span class="db_info ml-5" style=" text-decoration: underline;float: right;margin-left:15px">'.$row["province"].'</span>

                    <br>
                    <hr>
                    <span class="label">Status : </span>
                    <span class="db_info ml-5" style=" text-decoration: underline;float: right;margin-left:15px">'.$row["status"].'</span>


                    <br>
                    <hr>
                    <span class="label">
                        Sub Category : </span>
                    <span class="db_info ml-5" style=" text-decoration: underline;float: right;margin-left:15px">'.$row["subcat"].'</span>

                    <br>
                    <hr>
                    <span class="label">Response : </span>
                    <span class="db_info ml-5" style=" text-decoration: underline;float: right;margin-left:15px">'.$row["pro"].'</span>

                    <br>
                    <hr>
                    <span class="label">References : </span>
                    <span class="db_info ml-5" style=" text-decoration: underline;float: right;margin-left:15px">'.$row["ref"].'</span>

                    <br>
                    <hr>
                    <span class="label">Email : </span>
                    <span class="db_info ml-5" style=" text-decoration: underline;float: right;margin-left:15px">'.$row["email"].'</span>

                    <br>
                    <hr>
                    <span class="label">Whatsapp : </span>
                    <span class="db_info ml-5" style=" text-decoration: underline;float: right;margin-left:15px">'.$row["whatsapp"].'</span>

                    <br>
                    <hr>
                    <span class="label">Facebook : </span>
                    <span class="db_info ml-5" style=" text-decoration: underline;float: right;margin-left:15px">'.$row["fb"].'</span>

                    </td>
                </tr>
                ';
	}
	$output .= '

          


                <tr>
                    <td
                        style="padding:30px;text-align:center;font-size:12px;background-color:#404040;color:#cccccc;">
                        <p style="margin:0;font-size:14px;line-height:20px;">&reg;Sindh Hunza Rights Commision </p>
                        <p style="margin:0;font-size:20px;line-height:20px;">Government Of Sindh</p>
                    </td>
                </tr>
            </table>
            
        </td>
    </tr>
</table> ';
	return $output;
}




    // foreach ($email_arr as $value) {
    //     // echo "$value <br>";
	//     echo smtp_mailer($value,$today);

    // }

    echo smtp_mailer($email_arr,$today);





function smtp_mailer($to,$time){
	$connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");

	$file_name = md5(rand()) . '.pdf';
	$html_code = '<div class="container">
    <div class="row">
        <div class="col-md-12">
        <h2 style="font-weight: bold;color: #3e3ea7;font-size: 72px;font-style: italic;font-weight: bold;text-decoration: underline">Government</h2>
        
        </div>
            <h6>Report Name : Current Location</h6>
            <br/>
            <h6>Time : '.$time.'</h6>
        

        

    </div>
</div>';
	// $html_code .= fetch_customer_data($connect);
	// $pdf = new Pdf();
	// $pdf->load_html($html_code);
	// $pdf->render();
	// $file = $pdf->output();
	// file_put_contents($file_name, $file);
	
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
	// $mail->AddAttachment($file_name);     				//Adds an attachment from a path on the filesystem
	$mail->Subject = 'Complaint '.$time;			//Sets the Subject of the message
	$mail->Body = fetch_customer_data($connect);				//An HTML or plain text message body
	if($mail->Send())								//Send an Email. Return true on success or false on error
	{
		$message = '<label class="text-success">Customer Details has been send successfully...</label>';
        echo "mail Send";
	}
    else{
        echo "Mail Not Send";
    }
	unlink($file_name);
}

?>






