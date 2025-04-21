<?php
// MinIO Configuration
$minioEndpoint = 'http://192.168.5.189:9040';
$accessKey = 'admin';
$secretKey = 'password';
$bucketName = 'demo';

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

    // Upload to MinIO
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "$minioEndpoint$resourcePath",
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_INFILE => fopen($fileTempPath, 'rb'),
        CURLOPT_INFILESIZE => $fileSize,
        CURLOPT_HTTPHEADER => [
            "Date: $date",
            "Content-Type: $contentType",
            "Authorization: AWS $accessKey:$signature"
        ],
        CURLOPT_TIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($error) {
        echo "<p style='color: red;'>cURL Error: $error</p>";
    } elseif ($httpCode === 200) {
        echo "<p style='color: green;'>File uploaded successfully: <strong>$fileName</strong></p>";
    } else {
        echo "<p style='color: red;'>MinIO Error (HTTP $httpCode)</p>";
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
        <input type="file" name="upload" required>
        <button type="submit">Upload</button>
    </form>

    <hr>
    <h3>Files in Bucket:</h3>
    <ul>
        <?php
        $ch = curl_init("$minioEndpoint/$bucketName/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($response);
            if ($xml !== false) {
                foreach ($xml->Contents as $content) {
                    $fileName = (string)$content->Key;
                    $fileUrl = "$minioEndpoint/$bucketName/$fileName";
                    echo "<li><a href='$fileUrl' target='_blank'>$fileName</a></li>";
                }
            } else {
                echo "<li>Error parsing bucket contents</li>";
            }
        } else {
            echo "<li>No files found or connection error</li>";
        }
        ?>
    </ul>
</body>
</html>