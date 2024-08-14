<?php
// Handle file upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["excelFile"])) {
    $uploadDir = "uploads/";
    $uploadFile = $uploadDir . basename($_FILES["excelFile"]["name"]);

    if (move_uploaded_file($_FILES["excelFile"]["tmp_name"], $uploadFile)) {
        // Include PhpSpreadsheet library to read Excel file
        require 'vendor/autoload.php';

        // Load the Excel file
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadFile);

        // Get the first worksheet
        $worksheet = $spreadsheet->getActiveSheet();

        // Extract data from the worksheet
        $data = [
            'labels' => [],
            'datasets' => [],
        ];

        foreach ($worksheet->getRowIterator() as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getValue();
            }

            // Assuming the first row contains labels
            if (empty($data['labels'])) {
                $data['labels'] = $rowData;
            } else {
                // Assuming subsequent rows contain dataset values
                $dataset = [
                    'label' => 'Dataset', // You can customize this label
                    'data' => $rowData,
                    'backgroundColor' => 'rgba(75,192,192,0.2)',
                    'borderColor' => 'rgba(75,192,192,1)',
                    'borderWidth' => 1,
                ];

                $data['datasets'][] = $dataset;
            }
        }

        foreach ($worksheet->getRowIterator() as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getValue();
            }

            if (empty($data['labels'])) {
                $data['labels'] = $rowData;
            } else {
                $dataset = [
                    'label' => 'Dataset',
                    'data' => $rowData,
                    'backgroundColor' => 'rgba(75,192,192,0.2)',
                    'borderColor' => 'rgba(75,192,192,1)',
                    'borderWidth' => 1,
                ];

                $data['datasets'][] = $dataset;
            }
        }

        // Display the graph on the website
        echo '<!DOCTYPE html>
              <html lang="en">
              <head>
                  <meta charset="UTF-8">
                  <meta name="viewport" content="width=device-width, initial-scale=1.0">
                  <title>Graph Result</title>
                  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
              </head>
              <body>
                  <canvas id="myChart" width="400" height="400"></canvas>
                  <button onclick="downloadPDF()">Download as PDF</button>
                  <a href="download_excel.php">Download Excel</a>

                  <script>
                      var ctx = document.getElementById("myChart").getContext("2d");
                      var myChart = new Chart(ctx, {
                          type: "bar",
                          data: ' . json_encode($data) . ',
                      });

                      function downloadPDF() {
                          var pdf = new jsPDF();
                          pdf.addImage(myChart.toBase64Image(), "JPEG", 10, 10, 180, 100);
                          pdf.save("graph.pdf");
                      }
                  </script>
              </body>
              </html>';
    } else {
        echo "Error uploading the file.";
    }
} else {
    // Redirect to index.html if accessed directly without a file upload
    header("Location: indexer.php");
    exit();
}
?>
