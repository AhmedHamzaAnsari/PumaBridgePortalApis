<?php
//fetch.php  
include ("../../../config.php");
set_time_limit(5000); // 
// file_put_contents('reload_log.txt', 'Page reloaded at ' . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);


$access_key = '03201232927';

$pass = $_GET["key"];
echo $date = date('Y-m-d H:i:s');
echo '<br>';
if ($pass != '') {
    if ($pass == $access_key) {

        $sql_query1 = "SELECT * FROM dealers where privilege='Dealer' and sap_no!='' and sap_no=25860374;";

        $result1 = $db->query($sql_query1) or die("Error :" . mysqli_error());

        $thread = array();
        while ($user = $result1->fetch_assoc()) {

            $dealers_id = $user['id'];
            $sap = $user['sap_no'];

            // echo '<br>';

            $acount = $user['acount'];

            // echo 'http://151.106.17.246:8080/OMCS-CMS-APIS/services/get_dealer_ledger.php?sap='.$sap.'' . '<br>';

            $url = 'http://151.106.17.246:8080/OMCS-CMS-APIS/services/sap_api_services/api_5/api_5_data_check.php?sap=' . $sap . ''; // Replace '...' with the provided data string
            $data = file_get_contents($url);

            // Extracting the relevant information from the data string
            $startPos = strpos($data, '[');
            $endPos = strrpos($data, ']');

            $jsonData = substr($data, $startPos, $endPos - $startPos + 1);

            // Decoding the JSON string into a PHP array
            $arrayData = json_decode($jsonData, true);
            print_r($arrayData);
            // Encoding the array as JSON for better formatting
            // $jsonFormatted = json_encode($arrayData, JSON_PRETTY_PRINT);

            // Output the formatted JSON
            // echo $jsonFormatted;
            if ($arrayData) {
                foreach ($arrayData as $item) {
                    $MAT_DESCRIPTION = $item['MAT_DESCRIPTION'];
                    // echo '<br>';
                    $NET_VALUE = $item['NET_VALUE'];
                    $FREIGHT_VALUE = $item['FREIGHT_VALUE'];
                    $MATERIAL_ID = $item['MATERIAL_ID'];
                    $nozel_price = 0;
                    $from_date = '2024-07-15 00:00:00';
                    $to_date = '2024-07-31 23:59:59';
                   



                }





            }

            // echo $output;

        }


    } else {
        echo 'Wrong Key...';
    }

} else {
    echo 'Key is Required';
}

echo date('Y-m-d H:i:s');
?>