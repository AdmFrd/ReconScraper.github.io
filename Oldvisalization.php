<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet library

use PhpOffice\PhpSpreadsheet\IOFactory;

// Load the Excel file
$spreadsheet = IOFactory::load('Lexus_recon.xlsx');

$allSheetsData = [];

// Iterate over all sheets in the spreadsheet
foreach ($spreadsheet->getAllSheets() as $sheetIndex => $worksheet) {
    $sheetName = $worksheet->getTitle();
    $dateData = [];
    $minprice = 0;
    $maxprice = 0;
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
        'dateData' => json_encode($dateData),
        'minprice' => json_encode($minprice),
        'maxprice' => json_encode($maxprice),
        'desiredData' => json_encode($desiredData)
    ];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Visualization</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .header {
            background-color: #333;
            color: #fff;
            padding: 1rem;
            text-align: center;
        }
        .container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .chart-item {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .chart-item:hover {
            background-color: #e0e0e0;
        }
        .chart-item h2 {
            margin: 0 0 10px 0;
            font-size: 1.5rem;
            color: #333;
        }
        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 20px 0;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #0077b6;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #005f8d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Excel Data Visualization</h1>
    </div>
    <div class="container">
        <?php foreach ($allSheetsData as $index => $sheetData): ?>
            <div class="chart-item"
                 data-sheet-name="<?php echo htmlspecialchars($sheetData['sheetName']); ?>"
                 data-date-data="<?php echo htmlspecialchars($sheetData['dateData']); ?>"
                 data-minprice="<?php echo htmlspecialchars($sheetData['minprice']); ?>"
                 data-maxprice="<?php echo htmlspecialchars($sheetData['maxprice']); ?>"
                 data-desired-data="<?php echo htmlspecialchars($sheetData['desiredData']); ?>">
                <h2><?php echo htmlspecialchars($sheetData['sheetName']); ?></h2>
                <canvas id="chart-<?php echo $index; ?>" width="400" height="300"></canvas>
                <script>
                    var ctx = document.getElementById('chart-<?php echo $index; ?>').getContext('2d');
                    var dateData = <?php echo $sheetData['dateData']; ?>;
                    var minprice = <?php echo $sheetData['minprice']; ?>;
                    var maxprice = <?php echo $sheetData['maxprice']; ?>;
                    var desiredData = <?php echo $sheetData['desiredData']; ?>;

                    var datasets = [];
                    var colors = ['rgba(75, 192, 192, 0.2)', 'rgba(255, 99, 132, 0.2)', 'rgba(187, 122, 255, 0.2)', 'rgba(255, 206, 86, 0.2)'];
                    var borderColors = ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)', 'rgba(187, 122, 255, 1)', 'rgba(255, 206, 86, 1)'];
                    var idx = 0;

                    for (var key in desiredData) {
                        if (desiredData.hasOwnProperty(key)) {
                            datasets.push({
                                label: 'Model year ' + key,
                                data: desiredData[key],
                                backgroundColor: colors[idx % colors.length],
                                borderColor: borderColors[idx % borderColors.length],
                                borderWidth: 1,
                                fill: false,
                            });
                            idx++;
                        }
                    }

                    var myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: dateData,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                title: {
                                    display: true,
                                    text: '<?php echo addslashes($sheetData['sheetName']); ?>',
                                }
                            },
                            scales: {
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Mean Price (RM)',
                                    },
                                    beginAtZero: false,
                                    min: minprice,
                                    max: maxprice + (maxprice / 4),
                                    ticks: {
                                        stepSize: 10000
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Date taken',
                                    }
                                }
                            }
                        }
                    });
                </script>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="button-container"> 
        <button onclick="window.location.href = 'Webscrapersite.php';" class="button">Back to Main Page</button>
    </div>

    <script>
        document.querySelectorAll('.chart-item').forEach(item => {
            item.addEventListener('click', () => {
                const sheetName = item.getAttribute('data-sheet-name');
                const dateData = item.getAttribute('data-date-data');
                const minprice = item.getAttribute('data-minprice');
                const maxprice = item.getAttribute('data-maxprice');
                const desiredData = item.getAttribute('data-desired-data');

                const chartData = {
                    sheetName: sheetName,
                    dateData: JSON.parse(dateData),
                    minprice: parseFloat(minprice),
                    maxprice: parseFloat(maxprice),
                    desiredData: JSON.parse(desiredData)
                };

                const chartDataStr = encodeURIComponent(JSON.stringify(chartData));
                window.open('script.php?data=' + chartDataStr, '_blank');
            });
        });
    </script>
</body>
</html>
