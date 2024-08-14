<?php
if (isset($_GET['file'])) {
    $filename = "text_files/" . $_GET['file'];
    if (file_exists($filename)) {
        unlink($filename);
        header('Location: index.php');
    } else {
        echo "File not found.";
    }
} else {
    echo "Invalid request.";
}
?>
