<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chart Viewer</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .chart-container {
            width: 80%;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .chart-container h2 {
            text-align: center;
        }
        .download-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .download-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="chart-container">
        <h2 id="chart-title"></h2>
        <canvas id="chart" width="800" height="600"></canvas>
        <button class="download-btn" id="download-btn">Download as PDF</button>
    </div>
    <script>
        function getQueryParam(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        const chartDataStr = decodeURIComponent(getQueryParam('data'));
        const chartData = JSON.parse(chartDataStr);

        document.getElementById('chart-title').textContent = chartData.sheetName;

        var ctx = document.getElementById('chart').getContext('2d');

        var datasets = [];
        var colors = ['rgba(75, 192, 192, 0.2)', 'rgba(255, 99, 132, 0.2)', 'rgba(187, 122, 255, 0.2)', 'rgba(255, 206, 86, 0.2)'];
        var borderColors = ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)', 'rgba(187, 122, 255, 1)', 'rgba(255, 206, 86, 1)'];
        var idx = 0;

        for (var key in chartData.desiredData) {
            if (chartData.desiredData.hasOwnProperty(key)) {
                datasets.push({
                    label: 'Model year ' + key,
                    data: chartData.desiredData[key],
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
                labels: chartData.dateData,
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: chartData.sheetName,
                    }
                },
                scales: {
                    y: {
                        title: {
                            display: true,
                            text: 'Mean Price (RM)',
                        },
                        beginAtZero: false,
                        min: chartData.minprice,
                        max: chartData.maxprice + (chartData.maxprice / 4),
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

        // Download chart as PDF
        document.getElementById('download-btn').addEventListener('click', function() {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF();
            pdf.text(chartData.sheetName, 10, 10);
            pdf.addImage(myChart.toBase64Image(), 'PNG', 10, 20, 180, 160);
            const filename = chartData.sheetName.replace(/[^a-zA-Z0-9]/g, '_') + '.pdf'; // Clean up the sheet name for a valid file name
            pdf.save(filename);
        });
    </script>
</body>
</html>
