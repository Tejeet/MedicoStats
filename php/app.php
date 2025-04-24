<?php
include("config.php");

// Setup
$uploadDir = __DIR__ . '/media/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$timestamp = date('Ymd_His');

// Read raw body + Content Type
$rawBody = file_get_contents("php://input");
$contentType = $_SERVER["CONTENT_TYPE"] ?? '';

// Parse data
$data = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (stripos($contentType, 'application/json') !== false) {
        $data = json_decode($rawBody, true) ?? [];
    } else {
        $data = $_POST;
    }
} else {
    $data = []; // Still save error to DB later
}

$stmt = $con->prepare("INSERT INTO logs (data) VALUES (?)");
$stmt->bind_param("s", $rawBody);
$stmt->execute();
$stmt->close();

// Extract fields
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

// Save images if provided
if ($picture_base64) {
    $picture_path = $uploadDir . "panorama_{$timestamp}.jpg";
    file_put_contents($picture_path, base64_decode($picture_base64));
}

if ($closeup_base64) {
    $closeup_path = $uploadDir . "closeup_{$timestamp}.jpg";
    file_put_contents($closeup_path, base64_decode($closeup_base64));
}

// Log data to store in DB
$logData = [
    'type' => $type,
    'mode' => $mode,
    'plate_num' => $plate_num,
    'plate_color' => $plate_color,
    'plate_val' => $plate_val,
    'confidence' => $confidence,
    'car_logo' => $car_logo,
    'car_color' => $car_color,
    'start_time' => $start_time,
    'park_id' => $park_id,
    'cam_id' => $cam_id,
    'cam_ip' => $cam_ip,
    'vdc_type' => $vdc_type,
    'is_whitelist' => $is_whitelist,
    'triger_type' => $triger_type,
    'raw' => $rawBody // Store raw for full trace
];

// Save JSON file (optional log backup)
file_put_contents($uploadDir . "log_{$timestamp}.json", json_encode($logData, JSON_PRETTY_PRINT));

// Save to DB
try {
    // $jsonData = json_encode($logData);
    // $stmt = $con->prepare("INSERT INTO logs (data) VALUES (?)");
    // $stmt->bind_param("s", $jsonData);
    // $stmt->execute();
    // $stmt->close();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode([
            "error_num" => 0,
            "error_str" => "noerror"
        ]);
    } else {
        echo json_encode([
            "error_num" => 0,
            "error_str" => "Invalid request method",
            "gpio_data" => []
        ]);
    }
} catch (mysqli_sql_exception $e) {
    echo json_encode([
        "error_num" => 1,
        "error_str" => "DB insert failed: " . $e->getMessage(),
        "gpio_data" => []
    ]);
}
?>
