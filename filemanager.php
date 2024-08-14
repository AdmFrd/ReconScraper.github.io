<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>File Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
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
            margin: 20px 0;
            color: #333;
        }

        ul {
            list-style: none;
            padding: 0;
            width: 100%;
        }

        li {
            background-color: #fff;
            margin: 10px 0;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .button-container {
            display: flex;
            gap: 10px;
        }

        .button {
            background-color: #3498db;
            color: #fff;
            padding: 10px 20px;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #2980b9;
        }

        form {
            margin: 20px 0;
            width: 100%;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        form input[type="text"], form textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        form button {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>File Management</h1>
    </div>
    <div class="container">
        <h2>Available Text Files:</h2>
        <ul>
            <?php
            $directory = 'C:/xampp/htdocs/MyWebsite/';
            $files = scandir($directory);

            foreach ($files as $file) {
                if (is_file($directory . $file) && pathinfo($file, PATHINFO_EXTENSION) == 'txt') {
                    echo '<li>';
                    echo '<span>' . $file . '</span>';
                    echo '<div class="button-container">';
                    echo '<a href="?action=edit&file=' . $file . '" class="button">Edit</a>';
                    echo '<a href="?action=delete&file=' . $file . '" class="button">Delete</a>';
                    echo '</div>';
                    echo '</li>';
                }
            }
            ?>
        </ul>

        <h2>Create a New Text File:</h2>
        <form method="post" action="?action=create">
            <input type="text" id="newFileName" name="newFileName" placeholder="Enter file name">
            <button type="submit" class="button">Create</button>
        </form>

        <?php
        if (isset($_GET['action'])) {
            if ($_GET['action'] === 'edit' && isset($_GET['file'])) {
                $fileToEdit = $_GET['file'];
                $filePath = $directory . $fileToEdit;
                if (file_exists($filePath)) {
                    $content = file_get_contents($filePath);
                    echo "<h2>Edit File: $fileToEdit</h2>";
                    echo "<form method='post' action='?action=save&file=$fileToEdit'>";
                    echo "<textarea name='fileContent' rows='10'>$content</textarea>";
                    echo "<button type='submit' class='button'>Save</button>";
                    echo "</form>";
                }
            } elseif ($_GET['action'] === 'save' && isset($_GET['file']) && isset($_POST['fileContent'])) {
                $fileToSave = $_GET['file'];
                $contentToSave = $_POST['fileContent'];
                $filePath = $directory . $fileToSave;
                file_put_contents($filePath, $contentToSave);
                echo "<p>File $fileToSave has been saved.</p>";
            } elseif ($_GET['action'] === 'delete' && isset($_GET['file'])) {
                $fileToDelete = $_GET['file'];
                $filePath = $directory . $fileToDelete;
                if (file_exists($filePath) && unlink($filePath)) {
                    echo "<p>File $fileToDelete has been deleted.</p>";
                }
            } elseif ($_GET['action'] === 'create' && isset($_POST['newFileName'])) {
                $newFileName = $_POST['newFileName'] . '.txt'; // Add the ".txt" extension
                $newFilePath = $directory . $newFileName;
                if (!file_exists($newFilePath)) {
                    file_put_contents($newFilePath, '');
                    echo "<p>New file $newFileName has been created.</p>";
                } else {
                    echo "<p>File $newFileName already exists.</p>";
                }
            }
        }
        ?>

        <button onclick="window.location.href = 'Webscrapersite.php';" class="button">Back to Main Page</button>
    </div>
</body>
</html>
