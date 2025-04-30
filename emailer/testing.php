<?php
require 'vendor/autoload.php'; // Include the PhpSpreadsheet autoloader

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// HTML content
$html = '
<table>
  <tr>
    <th>Name</th>
    <th>Email</th>
  </tr>
  <tr>
    <td>John Doe</td>
    <td>johndoe@example.com</td>
  </tr>
  <tr>
    <td>Jane Smith</td>
    <td>janesmith@example.com</td>
  </tr>
</table>
';

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Load HTML content into the worksheet
$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
$worksheet = $reader->loadFromString($html);
$sheet->fromArray($worksheet->getActiveSheet()->toArray());

// Save the Excel file
$writer = new Xlsx($spreadsheet);
$writer->save('output.xlsx');

echo "HTML converted to Excel successfully!";
?>
