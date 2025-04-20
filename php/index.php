<!DOCTYPE html>
<html>
<head>
    <title>PHP File Upload</title>
</head>
<body>
    <h2>Upload a File</h2>

    <?php
    // Handle file upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload'])) {
        $targetDir = __DIR__ . '/media/';
        $fileName = basename($_FILES['upload']['name']);
        $targetFile = $targetDir . $fileName;

        // Create the media directory if it doesn't exist
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (move_uploaded_file($_FILES['upload']['tmp_name'], $targetFile)) {
            echo "<p style='color: green;'>File uploaded successfully: <strong>$fileName</strong></p>";
        } else {
            echo "<p style='color: red;'>Error uploading file.</p>";
        }
    }
    ?>

    <form action="" method="post" enctype="multipart/form-data">
        <label>Select file to upload:</label>
        <input type="file" name="upload" required>
        <button type="submit">Upload</button>
    </form>

    <hr>

    <h3>Uploaded Files:</h3>
    <ul>
        <?php
        $mediaPath = __DIR__ . '/media/';
        if (is_dir($mediaPath)) {
            $files = array_diff(scandir($mediaPath), array('.', '..'));
            foreach ($files as $file) {
                echo "<li><a href='media/$file' target='_blank'>$file</a></li>";
            }
        } else {
            echo "<li>No files uploaded yet.</li>";
        }
        ?>
    </ul>
</body>
</html>
