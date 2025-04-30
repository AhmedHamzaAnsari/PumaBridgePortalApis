<?php
include("../../config.php");
session_start();
ini_set('max_execution_time', 5000);  // Allow 5000 seconds (or adjust as needed)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data from the raw POST body
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    // Check if JSON data was decoded successfully
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        echo "Error decoding JSON: " . json_last_error_msg();
        exit;
    }

    // Get 'dealer_sap' from the decoded JSON data
    if (isset($data['dealer_sap'])) {
        $dealer_sap = mysqli_real_escape_string($db, $data['dealer_sap']);
    } else {
        echo "Error: 'dealer_sap' is missing.";
        exit;
    }

    // Get 'sales_records' from the decoded JSON data
    if (isset($data['sales_records']) && is_array($data['sales_records'])) {
        // Loop through each sales record and process
        foreach ($data['sales_records'] as $record) {
            // Escape values for safety before database interaction
            $ID = mysqli_real_escape_string($db, $record['ID']);
            $SlipNumber = mysqli_real_escape_string($db, $record['SlipNumber']);
            $SaleDateTime = mysqli_real_escape_string($db, $record['SaleTime']['date']);
            $ShiftNumber = mysqli_real_escape_string($db, $record['ShiftNumber']);
            $NozzleNo = mysqli_real_escape_string($db, $record['NozzleNo']);
            $ProductCode = mysqli_real_escape_string($db, $record['ProductCode']);
            $Quantity = mysqli_real_escape_string($db, $record['Quantity']);
            $Rate = mysqli_real_escape_string($db, $record['Rate']);
            $Amount = mysqli_real_escape_string($db, $record['Amount']);
            $CashReceived = mysqli_real_escape_string($db, $record['CashReceived']);
            $BalanceReturned = mysqli_real_escape_string($db, $record['BalanceReturned']);
            $UserID = mysqli_real_escape_string($db, $record['UserID']);
            $VehicleRegNo = mysqli_real_escape_string($db, $record['VehicleRegNo']);
            $DriverName = mysqli_real_escape_string($db, $record['DriverName']);
            $DriveCellNumber = mysqli_real_escape_string($db, $record['DriveCellNumber']);
            $VehicleOwnerName = mysqli_real_escape_string($db, $record['VehicleOwnerName']);
            $VehicleOwnerCellNumber = mysqli_real_escape_string($db, $record['VehicleOwnerCellNumber']);
            $CopiesPrinted = mysqli_real_escape_string($db, $record['CopiesPrinted']);
            $SMS = mysqli_real_escape_string($db, $record['SMS']);
            $CashOrCredit = mysqli_real_escape_string($db, $record['CashOrCredit']);
            $TankNo = mysqli_real_escape_string($db, $record['TankNo']);
            $created_at = mysqli_real_escape_string($db, date('Y-m-d H:i:s'));  // Timestamp of when the record is created

            // Construct the SQL INSERT query
            $query = "INSERT INTO `dealers_nozzels_sales`
                      (`dealers_sap`, `SlipNumber`, `slipDateTime`, `ShiftNumber`, `NozzleNo`, `ProductCode`,
                      `Quantity`, `Rate`, `Amount`, `CashReceived`, `BalanceReturned`, `UserID`, `VehicleRegNo`,
                      `DriverName`, `DriveCellNumber`, `VehicleOwnerName`, `VehicleOwnerCellNumber`, `CopiesPrinted`,
                      `SMS`, `CashOrCredit`, `TankNo`, `created_at`)
                      VALUES
                      ('$dealer_sap', '$SlipNumber', '$SaleDateTime', '$ShiftNumber', '$NozzleNo',
                      '$ProductCode', '$Quantity', '$Rate', '$Amount', '$CashReceived', '$BalanceReturned', '$UserID',
                      '$VehicleRegNo', '$DriverName', '$DriveCellNumber', '$VehicleOwnerName', '$VehicleOwnerCellNumber',
                      '$CopiesPrinted', '$SMS', '$CashOrCredit', '$TankNo', '$created_at');";

            // Execute the query
            if (mysqli_query($db, $query)) {
                // echo "Record for SlipNumber $SlipNumber inserted successfully.<br>";
            } else {
                echo "Error inserting record for SlipNumber $SlipNumber: " . mysqli_error($db) . "<br>";
            }
        }
    } else {
        echo "Error: 'sales_records' is missing or not an array.";
        exit;
    }

    echo "Data received successfully and processed.";
} else {
    echo "Invalid request method.";
}

?>