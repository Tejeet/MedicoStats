<?php
define('DB_SERVER', '94.136.185.134'); // Change to your MySQL server's hostname or IP address
define('DB_PORT', '3306');             // Default MySQL port is 3306
define('DB_USER', 'root');
define('DB_PASS', 'myroot');
define('DB_NAME', 'echo.fleetsapi.com');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $con = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $con->set_charset("utf8mb4");
    $db_status = true;
} catch (mysqli_sql_exception $e) {
    $db_status = false;
}
?>
