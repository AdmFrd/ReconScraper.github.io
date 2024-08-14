<?php

require 'functions.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Font;

function data_update_check($ws){
		
	$worksheet = $ws;

	// Get the highest row and column in the worksheet
	$highestRow = $worksheet->getHighestRow();
	$highestColumn = $worksheet->getHighestColumn();

	// Convert the column name (e.g., 'A', 'B', 'C') to a numeric index
	$lastColumnIndex = PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

	// Get the value in the last row and last column
	$lastValue = $worksheet->getCellByColumnAndRow($lastColumnIndex, $highestRow)->getValue();
	$date_test = get_date();

	if($lastValue == $date_test){
		return true;
	}else{
		return false;
	}

}

$file_titles = array("Mercedes_recon","Lexus_recon","Honda_recon","Toyota_recon");

for($counttest = 0;$counttest < count($file_titles);$counttest++){

	//The text file checking and data extracting part

	$filename = $file_titles[$counttest] . ".txt"; // Replace with the path to your text file

	// Initialize arrays to store data for each column
	$Modelname = [];
	$Year = [];
	$Car_links = [];

	// Open the text file for reading
	$file = fopen($filename, "r");

	if ($file) {
	    $count1 = 0;
	    // Read and process the data
	    while (($line = fgets($file)) !== false) {
	        // Split the line into an array of values using a comma as the delimiter
	        $data = explode(",", $line);

	        // Store each column in its respective array
	        $Modelname[] = $data[0];
	        $Year[] = $data[1];
	        $Car_links[] = $data[2];
	        $count1 ++;
	    }

	    // Close the file when you're done
	    fclose($file);

	} else {
	    //Used for error handling of missing file.Leave empty
	}

	//The Excel checking/creating part

	$Xfilename = $file_titles[$counttest] . '.xlsx'; 
	$Xfilename_ex = str_replace(".xlsx", "", $Xfilename);

	if (file_exists($Xfilename)) {
	   //Used for error handling of missing file.Leave empty
	} else {
	    // Create a new Excel spreadsheet
	    $spreadsheet = new Spreadsheet();

	    for($xcount = 0; $xcount < count($Modelname) ; $xcount++){ //condition limit needs to change
	        if($xcount == 0){
	            // Create first worksheet
	            $sheet1 = $spreadsheet->getActiveSheet();
	            $sheet1->setTitle($Modelname[$xcount]);

	            // Add title to the worksheet
	            $sheet1->setCellValue('A1', 'Year');
	            $sheet1->setCellValue('B1', 'Units');
	            $sheet1->setCellValue('C1', 'Min Price');
	            $sheet1->setCellValue('D1', 'Max Price');
	            $sheet1->setCellValue('E1', 'Mean Price');
	            $sheet1->setCellValue('F1', 'Month taken');

	            // Apply formatting (make the entire first row bold and change the font size)
	            $boldFont = [
	                'bold' => true,
	                'size' => 12,
	            ];

	            $sheet1->getStyle('A1:F1')->getFont()->applyFromArray($boldFont);

	            // Adjust the width of columns A, B, and C
	            $columns = ['A', 'B', 'C', 'D', 'E', 'F'];
	            $columnWidth = 10; // Set the width (adjust as needed)

	            foreach ($columns as $column) {
	            	if($column == 'C' || $column == 'D'|| $column == 'E' || $column == 'F'){
	            		$sheet1->getColumnDimension($column)->setWidth(17);
	            	}else{
	            		$sheet1->getColumnDimension($column)->setWidth($columnWidth);
	            	}
	                
	            }

	        }else{

	            // Create additional sheets ( start index 1)
	            $sheetnew = $spreadsheet->createSheet();
	            $sheetnew->setTitle($Modelname[$xcount]);

	            // Add title to the worksheet
	            $sheetnew->setCellValue('A1', 'Year');
	            $sheetnew->setCellValue('B1', 'Units');
	            $sheetnew->setCellValue('C1', 'Min Price');
	            $sheetnew->setCellValue('D1', 'Max Price');
	            $sheetnew->setCellValue('E1', 'Mean Price');
	            $sheetnew->setCellValue('F1', 'Month taken');

	            // Apply formatting (make the entire first row bold and change the font size)
	            $boldFont = [
	                'bold' => true,
	                'size' => 12,
	            ];

	            $sheetnew->getStyle('A1:F1')->getFont()->applyFromArray($boldFont);

	            // Adjust the width of columns A, B, and C
	            $columns = ['A', 'B', 'C', 'D', 'E', 'F'];
	            $columnWidth = 10; // Set the width (adjust as needed)

	            foreach ($columns as $column) {
	            	if($column == 'C' || $column == 'D' || $column == 'E' || $column == 'F'){
	            		$sheetnew->getColumnDimension($column)->setWidth(17);
	            	}else{
	            		$sheetnew->getColumnDimension($column)->setWidth($columnWidth);
	            	}
	                
	            }
	        }
	    }
	    

	    // Save the spreadsheet to a file
	    $writer = new Xlsx($spreadsheet);
	    $writer->save($Xfilename_ex = $Xfilename_ex . ".xlsx");

	   
	}

	//The filling of excel file

	$spreadsheetin = IOFactory::load($file_titles[$counttest] . '.xlsx');

	for($scount = 0; $scount < count($Modelname); $scount++){
		//Open spreadsheet and pick specific sheet using 'scount'
		$worksheet = $spreadsheetin->getSheet($scount);

		if(data_update_check($worksheet) == false){
			//Load year or year range for the model
			$Yrange = $Year[$scount];
			//Declare what character to target for 'Yrange'
			$dash = "-";

			//Check if '-' is present in 'Yrange'
			if($result = stristr($Yrange, $dash)){
			$years = explode("-", $Yrange);

			$years[0] = intval($years[0]);
			$years[1] = intval($years[1]);

			//Get difference between 2 years and specify start year
			$diff = $years[1] - $years[0];
			$yearstart = $years[0];

			//Modify the links, get the data using functions, and enter into excel file
			for($x = 0; $x <= $diff; $x++){
				//Modified link for one year ready for function
				$link_for_function = str_replace("targetyr",(string)$yearstart,$Car_links[$scount]);

				//Array to hold data from called function
				$current_data = Get_data($link_for_function);

				// Determine the last used row
				$lastRow = $worksheet->getHighestRow();

				//Enter data into excel file section below

				// If there is data already present, start from the next row
				if ($lastRow > 1) {
					$rowIndex = $lastRow + 1;
				} else {
					// If no data is present, start from the second row
					$rowIndex = 2;
				}

				// Insert data into different rows and columns
				$columnIndex = 1;
				foreach ($current_data as $cellValue) {
					$worksheet->setCellValueByColumnAndRow($columnIndex, $rowIndex, $cellValue);
					$columnIndex++;
				}
					
				$yearstart++;
			}

			}else {

				$yearstart = $Yrange;
				//Modified link for one year ready for function
				$link_for_function = str_replace("targetyr",(string)$yearstart,$Car_links[$x]);

				//Array to hold data from called function
				$current_data = Get_data($link_for_function);
				print_r($current_data);

				//Enter data into excel file section below

				// Determine the last used row
				$lastRow = $worksheet->getHighestRow();

				// If there is data already present, start from the next row
				if ($lastRow > 1) {
					$rowIndex = $lastRow + 1;
				} else {
					// If no data is present, start from the second row
					$rowIndex = 2;
				}

				// Insert data into different rows and columns
				foreach ($current_data as $rowData) {
					$columnIndex = 1;
					foreach ($rowData as $cellValue) {
						$worksheet->setCellValueByColumnAndRow($columnIndex, $rowIndex, $cellValue);
						$columnIndex++;
					}
					$rowIndex++;
				}

			}
				
			// Save the modified Excel file
			$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheetin, 'Xlsx');
			$writer->save($file_titles[$counttest] . '.xlsx');
		}
		
		
	}
}



?>


