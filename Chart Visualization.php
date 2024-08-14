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
        .button-container, .chart-container {
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Excel Data Visualization</h1>
    </div>
    <div class="button-container">
        <?php
        // Assuming you have an array of file names
        $excelFiles = ['Lexus_recon.xlsx', 'Honda_recon.xlsx', 'Toyota_recon.xlsx', 'Mercedes_recon.xlsx'];
        foreach ($excelFiles as $file): ?>
            <button class="excel-button" data-file="<?php echo htmlspecialchars($file); ?>">
                <?php echo htmlspecialchars($file); ?>
            </button>
        <?php endforeach; ?>
    </div>
    
    <div class="chart-container">
        <div class="container" id="chart-container"></div>
    </div>
    <div class="button-container"> 
        <button onclick="window.location.href = 'Webscrapersite.php';" class="button">Back to Main Page</button>
    </div>

    <script>
        document.querySelectorAll('.excel-button').forEach(button => {
            button.addEventListener('click', () => {
                const fileName = button.getAttribute('data-file');

                fetch('VisualdataExtract.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ file: fileName })
                })
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('chart-container');
                    container.innerHTML = ''; // Clear previous charts

                    data.forEach((sheetData, index) => {
                        const chartItem = document.createElement('div');
                        chartItem.className = 'chart-item';
                        chartItem.setAttribute('data-sheet-name', sheetData.sheetName);
                        chartItem.setAttribute('data-date-data', JSON.stringify(sheetData.dateData));
                        chartItem.setAttribute('data-minprice', sheetData.minprice);
                        chartItem.setAttribute('data-maxprice', sheetData.maxprice);
                        chartItem.setAttribute('data-desired-data', JSON.stringify(sheetData.desiredData));
                        chartItem.innerHTML = `
                            <h2>${sheetData.sheetName}</h2>
                            <canvas id="chart-${index}" width="400" height="300"></canvas>
                        `;
                        container.appendChild(chartItem);

                        const ctx = document.getElementById(`chart-${index}`).getContext('2d');
                        const datasets = [];
                        const colors = ['rgba(75, 192, 192, 0.2)', 'rgba(255, 99, 132, 0.2)', 'rgba(187, 122, 255, 0.2)', 'rgba(255, 206, 86, 0.2)'];
                        const borderColors = ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)', 'rgba(187, 122, 255, 1)', 'rgba(255, 206, 86, 1)'];
                        let idx = 0;

                        for (let key in sheetData.desiredData) {
                            if (sheetData.desiredData.hasOwnProperty(key)) {
                                datasets.push({
                                    label: 'Model year ' + key,
                                    data: sheetData.desiredData[key],
                                    backgroundColor: colors[idx % colors.length],
                                    borderColor: borderColors[idx % borderColors.length],
                                    borderWidth: 1,
                                    fill: false,
                                });
                                idx++;
                            }
                        }

                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: sheetData.dateData,
                                datasets: datasets
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    title: {
                                        display: true,
                                        text: sheetData.sheetName,
                                    }
                                },
                                scales: {
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'Mean Price (RM)',
                                        },
                                        beginAtZero: false,
                                        min: sheetData.minprice,
                                        max: sheetData.maxprice + (sheetData.maxprice / 4),
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
                    });

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
                            window.open('SingleWindowscript.php?data=' + chartDataStr, '_blank');
                        });
                    });
                })
                .catch(error => console.error('Error fetching data:', error));
            });
        });
    </script>
</body>
</html>
