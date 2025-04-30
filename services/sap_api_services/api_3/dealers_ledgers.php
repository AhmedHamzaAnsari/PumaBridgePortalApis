<?php
//fetch.php  
ini_set('max_execution_time', '0');
$url1 = $_SERVER['REQUEST_URI'];
header("Refresh: 600; URL=$url1");
include("../../../config.php");
set_time_limit(5000); // 
// file_put_contents('reload_log.txt', 'Page reloaded at ' . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);

echo 'Start Time '.date('Y-m-d');
$access_key = '03201232927';

$pass = $_GET["key"];
$date = date('Y-m-d H:i:s');

if ($pass != '') {
    if ($pass == $access_key) {

        $sql_query1 = "SELECT * FROM dealers where privilege='Dealer';";

        $result1 = $db->query($sql_query1) or die("Error :" . mysqli_error());

        $thread = array();
        while ($user = $result1->fetch_assoc()) {

            $dealers_id = $user['id'];
            $sap = $user['sap_no'];

            // echo '<br>';

            $acount = $user['acount'];

            // echo 'http://151.106.17.246:8080/OMCS-CMS-APIS/services/get_dealer_ledger.php?sap='.$sap.'' . '<br>';

            $url = 'http://151.106.17.246:8080/OMCS-CMS-APIS/services/sap_api_services/api_3/api_3_data_check.php?sap=' . $sap . ''; // Replace '...' with the provided data string
            $data = file_get_contents($url);

            // Extracting the relevant information from the data string
            $startPos = strpos($data, '[');
            $endPos = strrpos($data, ']');

            $jsonData = substr($data, $startPos, $endPos - $startPos + 1);

            // Decoding the JSON string into a PHP array
            $arrayData = json_decode($jsonData, true);
            // print_r($arrayData);
            // Encoding the array as JSON for better formatting
            // $jsonFormatted = json_encode($arrayData, JSON_PRETTY_PRINT);

            // Output the formatted JSON
            // echo $jsonFormatted;
            if ($arrayData) {
                foreach ($arrayData as $item) {
                    $DOC_NO = $item['DOC_NO'];
                    // echo '<br>';
                    $RETAIL_SITE_NAME = $item['RETAIL_SITE_NAME'];
                    $DEBIT_CREDIT = $item['DEBIT_CREDIT'];
                    $LEDGER_BALANCE = $item['LEDGER_BALANCE'];
                    $DATE = $item['ZDATE'];
                    // echo '<br>';

                    $ASSIGNMENT_NO = $item['ASSIGNMENT_NO'];
                    $DOCUMENT_TYPE = $item['DOCUMENT_TYPE'];
                    $CUSTOMER = $item['CUSTOMER'];




                    $log = "INSERT INTO `dealer_ledger_log`
                        (`dealer_id`,
                        `old_ledger`,
                        `new_ledger`,
                        `datetime`,
                        `description`,
                        `doc_no`,
                        `debit_no`,
                        `assignment_no`,
                        `document_type`,
                        `sap_no`,
                        `ledger_balance`,
                        `created_at`,
                        `created_by`)
                        VALUES
                        ('$dealers_id',
                        '$acount',
                        '$LEDGER_BALANCE',
                        '$DATE',
                        '',
                        '$DOC_NO',
                        '$DEBIT_CREDIT',
                        '$ASSIGNMENT_NO',
                        '$DOCUMENT_TYPE',
                        '$sap',
                        '$LEDGER_BALANCE',
                        '$date',
                        '1');
                        ";
                    if (mysqli_query($db, $log)) {
                        $output = 1;

                    } else {
                        $output = 'Error' . mysqli_error($db) . '<br>';

                    }

                }


                // $updated = "SELECT * FROM dealer_ledger_log where dealer_id='$dealers_id' and sap_no='$sap' order by datetime desc limit 1;";

                // $resultupdated = $db->query($updated) or die("Error :" . mysqli_error($db));

                // $thread = array();
                // while ($user3 = $resultupdated->fetch_assoc()) {
                //     // $thread[] = $user;
                //     $ledgers = $user3['new_ledger'];

                //     $query = "UPDATE `dealers` SET 
                //     `acount`='$ledgers' WHERE id=$dealers_id and sap_no='$sap'";


                //     if (mysqli_query($db, $query)) {
                //         // echo 'Dealer Ledger Updated <br>';
                //     } else {
                //         $output = 'Error' . mysqli_error($db) . '<br>' . $query;

                //     }
                // }

                end($arrayData);

                // Get the key (index) of the last element
                $lastIndex = key($arrayData);

                // Get the values of the last element
                $lastValues = current($arrayData);

                // Print the last index and values
                // echo "Last index: " . $lastIndex . "<br>";
                // echo "Last values: ";
                // print_r($lastValues);
                $lastValues_ledger = $lastValues['LEDGER_BALANCE'];

                $query = "UPDATE `dealers` SET 
                `acount`='$lastValues_ledger' WHERE id=$dealers_id and sap_no='$sap'";


                if (mysqli_query($db, $query)) {
                    // echo 'Dealer Ledger Updated <br>';
                } else {
                    $output = 'Error' . mysqli_error($db) . '<br>' . $query;

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