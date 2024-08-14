<?php
if (isset($_GET['file'])) {
    $filename = "text_files/" . $_GET['file'];
    if (file_exists($filename)) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
            $content = $_POST['content'];
            file_put_contents($filename, $content);
            header('Location: index.php');
        }
        $fileContent = file_get_contents($filename);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit File: <?php echo $_GET['file']; ?></title>
</head>
<body>
    <h1>Edit File: <?php echo $_GET['file']; ?></h1>
    <form action="" method="post">
        <textarea name="content" rows="10" cols="40"><?php echo $fileContent; ?></textarea><br>
        <input type="submit" value="Save Changes">
    </form>
</body>
</html>
<?php
    } else {
        echo "File not found.";
    }
} else {
    echo "Invalid request.";
}
?>
