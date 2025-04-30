<?php 
$consignee_name='CoCo # 5';
$vehicle_name='TMD-17-914';
 $msg = 'Dear ' . $consignee_name . ' ' .$vehicle_name . ' had reached at your Location With Your  Order. Your Order has been complete succesfully ';

                                $text = 'Track Your Trip';
                                // $url = 'http://151.106.17.246:8080/sitara/view_link.php?c='.$enc_ID;
                                // $f_url = $msg .' '. $url;
                                $curl = curl_init();
                    
                                curl_setopt_array($curl, array(
                                CURLOPT_URL => 'https://connect.jazzcmt.com/sendsms_url.html?Username=03202538075&Password=Jazz@123&From=SitaraLive&To=03137152168&Message='.urlencode($msg),
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => '',
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 0,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => 'GET',
                                ));
                        
                                $response = curl_exec($curl);
                        
                                curl_close($curl);
?>