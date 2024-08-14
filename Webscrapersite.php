<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recon Car Web Scraper</title>
    <style>
        /* Global styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        .header {
            background-color: #333;
            color: #fff;
            width: 100%;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            max-width: 1200px;
            width: 100%;
        }

        h1 {
            margin: 20px 0;
            color: #FFF;
        }

        h2 {
            margin-top: 10px;
            margin-bottom: 20px;
            color: #333;
        }

        .text-file-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            width: 100%;
        }

        .text-file-window {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            overflow: auto;
            max-height: 200px;
        }

        .text-file-window h3 {
            margin: 0 0 10px 0;
            font-size: 1.25rem;
            color: #0077b6;
        }

        .text-file-content {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #333;
        }

        .model_name, .year_range {
            font-weight: bold;
            width: 45%;
        }

        .first-data, .second-data {
            width: 45%;
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

        #progress-container {
            width: 100%;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            height: 20px;
            display: none;
            margin-top: 20px;
        }

        #progress-bar {
            width: 0;
            height: 100%;
            background-color: #3cb371;
        }

        #notification {
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: #4CAF50;
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            display: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Recon Car Web Scraper</h1>
    </div>
    <div class="container">
        <h2>Cars in Inventory</h2>

        <div class="text-file-container">
            <?php
            $textFilesDirectory = 'C:/xampp/htdocs/MyWebsite/'; // Update this to your text files directory
            $files = scandir($textFilesDirectory);

            foreach ($files as $file) {
                if (is_file($textFilesDirectory . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'txt') {
                    $fileContent = file_get_contents($textFilesDirectory . $file);

                    echo '<div class="text-file-window">';
                    echo '<h3>' . pathinfo($file, PATHINFO_FILENAME) . '</h3>';
                    echo '<div class="text-file-content">';
                    echo '<span class="model_name">Model</span>';
                    echo '<span class="year_range">Year</span>';
                    echo '</div>';

                    // Split the file content into rows
                    $rows = explode("\n", $fileContent);

                    foreach ($rows as $row) {
                        // Split each row by commas to get the first two data elements
                        $data = explode(',', $row);

                        // Check if there are at least two data elements in the row
                        if (count($data) >= 2) {
                            $firstData = trim($data[0]);
                            $secondData = trim($data[1]);
                            echo '<div class="text-file-content">';
                            echo '<span class="first-data">' . $firstData . '</span>';
                            echo '<span class="second-data">' . $secondData . '</span>';
                            echo '</div>';
                        }
                    }
                    echo '</div>';
                }
            }
            ?>
        </div>

        <div class="button-container">
            <button onclick="openFileManager()">File Manager</button>
            <button onclick="updateFiles()">Update Files</button>
            <button onclick="downloadFiles()">Download Files</button>
            <button onclick="dataVisual()">Data Visualization</button>
        </div>

        <div id="progress-container">
            <div id="progress-bar"></div>
        </div>

        <div id="notification">
            <span id="notification-text"></span>
        </div>

        <div id="update-status" style="display: none;">Please wait...</div>
    </div>

    <script>
        function openFileManager() {
            window.location.href = 'filemanager.php';
        }
        
        function updateFiles() {
            var progressContainer = document.getElementById('progress-container');
            var progressBar = document.getElementById('progress-bar');
            var notificationText = document.getElementById('notification-text');
            var updateStatus = document.getElementById('update-status');

            // Show the "Please Wait" message
            updateStatus.style.display = 'block';
            notificationText.innerHTML = ''; // Clear any existing notification

            // Show the progress bar
            progressContainer.style.display = 'block';

            // Initialize the XMLHttpRequest
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'WebScraperScriptmanual.php', true);

            // Define a function to track the progress of the request
            xhr.onprogress = function (event) {
                if (event.lengthComputable) {
                    var percentComplete = (event.loaded / event.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                }
            };

            // Handle the request completion
            xhr.onload = function () {
                if (xhr.status === 200) {
                    showNotification('Files updated');
                } else {
                    showNotification('Error updating files');
                }

                // Hide the progress bar when the process is done
                progressContainer.style.display = 'none';
                updateStatus.style.display = 'none';
            };

            xhr.onerror = function () {
                showNotification('Error updating files');
                progressContainer.style.display = 'none';
                updateStatus.style.display = 'none';
            };

            xhr.send();
        }

        function downloadFiles() {
            var excelFileUrls = [
                'Honda_recon.xlsx',
                'Toyota_recon.xlsx',
                'Lexus_recon.xlsx',
                'Mercedes_recon.xlsx'
            ];

            function downloadFile(url) {
                var a = document.createElement('a');
                a.href = url;
                a.download = url.split('/').pop();
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }

            excelFileUrls.forEach(function (url) {
                downloadFile(url);
            });

            showNotification('Downloaded files');
        }

        function dataVisual(){
            window.location.href = 'Chart Visualization.php';
        }

        function showNotification(message) {
            var notification = document.getElementById('notification');
            var notificationText = document.getElementById('notification-text');
            notificationText.innerHTML = message;
            notification.style.display = 'block';
            setTimeout(function () {
                notification.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>
