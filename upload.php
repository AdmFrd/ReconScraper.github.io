<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    if ($file['error'] === 0) {
        $uploadDir = 'text_files/';
        $filename = $uploadDir . basename($file['name']);

        if (move_uploaded_file($file['tmp_name'], $filename)) {
            header('Location: index.php'); // Redirect back to the main page.
        } else {
            echo "File upload failed.";
        }
    } else {
        echo "Error uploading the file.";
    }
} else {
    echo "Invalid request.";
}
?>
