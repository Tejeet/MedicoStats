<?php
// MinIO Configuration
$minioEndpoint = 'http://192.168.5.189:9001'; // MinIO server URL
$accessKey = 'admin'; // Default access key
$secretKey = 'password'; // Default secret key
$bucketName = 'demo'; // Your bucket name

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload'])) {
    $fileName = basename($_FILES['upload']['name']);
    $fileTempPath = $_FILES['upload']['tmp_name'];
    $fileSize = $_FILES['upload']['size'];
    $fileType = $_FILES['upload']['type'];

    // Generate S3 signature
    $date = gmdate('D, d M Y H:i:s T');
    $contentType = $fileType;
    $resourcePath = "/$bucketName/$fileName";
    
    $stringToSign = "PUT\n\n$contentType\n$date\n$resourcePath";
    $signature = base64_encode(hash_hmac('sha1', $stringToSign, $secretKey, true));

    // Upload to MinIO using cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$minioEndpoint$resourcePath");
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_INFILE, fopen($fileTempPath, 'rb'));
    curl_setopt($ch, CURLOPT_INFILESIZE, $fileSize);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Date: $date",
        "Content-Type: $contentType",
        "Authorization: AWS $accessKey:$signature"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        echo "<p style='color: green;'>File uploaded to MinIO successfully: <strong>$fileName</strong></p>";
    } else {
        echo "<p style='color: red;'>Error uploading file (HTTP $httpCode)</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>MinIO File Upload</title>
</head>
<body>
    <h2>Upload a File to MinIO</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <label>Select file to upload:</label>
        <input type="file" name="upload" required>
        <button type="submit">Upload</button>
    </form>

    <hr>
    <h3>Files in MinIO Bucket:</h3>
    <ul>
        <?php
        // List files using MinIO API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$minioEndpoint/$bucketName/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $xml = simplexml_load_string($response);
            foreach ($xml->Contents as $content) {
                $fileName = (string)$content->Key;
                $fileUrl = "$minioEndpoint/$bucketName/$fileName";
                echo "<li><a href='$fileUrl' target='_blank'>$fileName</a></li>";
            }
        } else {
            echo "<li>No files found in bucket</li>";
        }
        ?>
    </ul>
</body>
</html>