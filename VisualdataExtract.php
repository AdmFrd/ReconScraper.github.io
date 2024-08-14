<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet library

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

// Get the posted file name
$request = json_decode(file_get_contents('php://input'), true);
$file = $request['file'];

// Load the Excel file
$spreadsheet = IOFactory::load($file);

$allSheetsData = [];

// Iterate over all sheets in the spreadsheet
foreach ($spreadsheet->getAllSheets() as $sheetIndex => $worksheet) {
    $sheetName = $worksheet->getTitle();
    $dateData = [];
    $pricehold = [];
    $data = [];

    // Define the columns you want to extract (e.g., column 'F' for dates, 'E' for prices)
    $columns = ['F', 'E'];

    // Get the highest row number
    $highestRow = $worksheet->getHighestRow();

    // Extract the data from the specified columns
    for ($row = 2; $row <= $highestRow; $row++) {
        $dateData[] = $worksheet->getCell($columns[0] . $row)->getValue();
        $pricehold[] = $worksheet->getCell($columns[1] . $row)->getValue();
    }

    $minprice = min($pricehold);
    $maxprice = max($pricehold);
    $dateData = array_unique($dateData);
    $dateData = array_values($dateData);

    // Initialize array for unique indices
    $uniqueIndices = [];

    // First pass: collect unique indices from the first column
    for ($row = 2; $row <= $highestRow; $row++) {
        $index = $worksheet->getCell('A' . $row)->getValue();
        if (!in_array($index, $uniqueIndices)) {
            $uniqueIndices[] = $index;
        }
    }

    // Second pass: organize data based on the unique indices
    foreach ($worksheet->getRowIterator(2) as $row) {
        $rowData = [];
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        foreach ($cellIterator as $cell) {
            $rowData[] = $cell->getValue();
        }

        $index = $rowData[0];
        $data[$index][] = $rowData[4];
    }

    // Extract data for each unique index
    $desiredData = [];
    foreach ($uniqueIndices as $index) {
        if (isset($data[$index])) {
            $desiredData[$index] = array_values($data[$index]);
        }
    }

    // Store sheet data in an array for later use in JavaScript
    $allSheetsData[] = [
        'sheetName' => $sheetName,
        'dateData' => $dateData,
        'minprice' => $minprice,
        'maxprice' => $maxprice,
        'desiredData' => $desiredData
    ];
}

// Return data as JSON
echo json_encode($allSheetsData);
?>
