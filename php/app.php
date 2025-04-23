<?php
include("config.php");

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get all form fields
    $type = $_POST['type'] ?? '';
    $mode = $_POST['mode'] ?? '';
    $plate_num = $_POST['plate_num'] ?? '';
    $plate_color = $_POST['plate_color'] ?? '';
    $plate_val = $_POST['plate_val'] ?? '';
    $confidence = $_POST['confidence'] ?? '';
    $car_logo = $_POST['car_logo'] ?? '';
    $car_color = $_POST['car_color'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $park_id = $_POST['park_id'] ?? '';
    $cam_id = $_POST['cam_id'] ?? '';
    $cam_ip = $_POST['cam_ip'] ?? '';
    $vdc_type = $_POST['vdc_type'] ?? '';
    $is_whitelist = $_POST['is_whitelist'] ?? '';
    $triger_type = $_POST['triger_type'] ?? '';

    $picture_base64 = $_POST['picture'] ?? '';
    $closeup_base64 = $_POST['closeup_pic'] ?? '';

    // Create uploads directory if not exists
    $uploadDir = __DIR__ . '/media/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $timestamp = date('Ymd_His');

    // Save base64 images if available
    if ($picture_base64) {
        $picture_path = $uploadDir . "panorama_{$timestamp}.jpg";
        file_put_contents($picture_path, base64_decode($picture_base64));
    }

    if ($closeup_base64) {
        $closeup_path = $uploadDir . "closeup_{$timestamp}.jpg";
        file_put_contents($closeup_path, base64_decode($closeup_base64));
    }

    // Log other data (optional)
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
        'triger_type' => $triger_type
    ];

    if ($db_status) {
        try {
            $sql = "INSERT INTO users (user, createdon) VALUES ('$randomUser', '$randomUser')";
            $con->query($sql);
            echo "Random user $randomUser added successfully.<br>";
        } catch (mysqli_sql_exception $e) {
            echo "Insert error: " . $e->getMessage() . "<br>";
        }
    }

    file_put_contents($uploadDir . "log_{$timestamp}.json", json_encode($logData, JSON_PRETTY_PRINT));

    // Response to client
    echo json_encode([
        "error_num" => 0,
        "error_str" => "noerror",
        "gpio_data" => [
            [
                "ionum" => "io1",
                "action" => "on"
            ]
        ]
    ]);
} else {
    
    echo json_encode([
        "error_num" => 1,
        "error_str" => "Invalid request method",
        "gpio_data" => []
    ]);
}
?>
