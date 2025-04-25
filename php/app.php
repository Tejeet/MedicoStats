<?php
include("config.php");

// Setup
$uploadDir = __DIR__ . '/media/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$timestamp = date('Ymd_His');

// Capture rawBody
$rawBody = file_get_contents("php://input");
$contentType = $_SERVER["CONTENT_TYPE"] ?? '';
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Decide how to parse
$data = [];
if ($requestMethod === 'POST') {
    if (stripos($contentType, 'application/json') !== false) {
        $data = json_decode($rawBody, true) ?? [];
    } elseif (!empty($_POST)) {
        $data = $_POST;
        $rawBody = http_build_query($_POST);  // Store readable raw input for form-data
    }
} else {
    $data = [];
}

// Save rawBody to DB regardless
$stmt = $con->prepare("INSERT INTO logs (data) VALUES (?)");
$stmt->bind_param("s", $rawBody);
$stmt->execute();
$stmt->close();

// Extract data safely
$type = $data['type'] ?? '';
$mode = $data['mode'] ?? '';
$plate_num = $data['plate_num'] ?? '';
$plate_color = $data['plate_color'] ?? '';
$plate_val = $data['plate_val'] ?? '';
$confidence = $data['confidence'] ?? '';
$car_logo = $data['car_logo'] ?? '';
$car_color = $data['car_color'] ?? '';
$start_time = $data['start_time'] ?? '';
$park_id = $data['park_id'] ?? '';
$cam_id = $data['cam_id'] ?? '';
$cam_ip = $data['cam_ip'] ?? '';
$vdc_type = $data['vdc_type'] ?? '';
$is_whitelist = $data['is_whitelist'] ?? '';
$triger_type = $data['triger_type'] ?? '';
$picture_base64 = $data['picture'] ?? '';
$closeup_base64 = $data['closeup_pic'] ?? '';


// Save images
if ($picture_base64) {
    file_put_contents($uploadDir . "panorama_{$timestamp}.jpg", base64_decode($picture_base64));
}
if ($closeup_base64) {
    file_put_contents($uploadDir . "closeup_{$timestamp}.jpg", base64_decode($closeup_base64));
}

// Save log JSON (optional file backup)
$logData = array_merge($data, ['raw' => $rawBody]);
file_put_contents($uploadDir . "log_{$timestamp}.json", json_encode($logData, JSON_PRETTY_PRINT));

// Prepare and send JSON response with proper Content-Length
$response = json_encode([
    "error_num" => 0,
    "error_str" => "noerror",
    "gpio_data" => [
        [
            "ionum" => "io1",
            "action" => ($plate_num == "HR67A9100") ? "off" : "on"
        ]
    ]
]);

header('Content-Type: application/json; charset=utf-8');
header('Content-Length: ' . strlen($response));
echo $response;
