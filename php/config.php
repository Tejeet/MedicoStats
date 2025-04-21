<?php
define('DB_SERVER', '94.136.185.134'); // Change to your MySQL server's hostname or IP address
define('DB_PORT', '3306');    // Default MySQL port is 3306
define('DB_USER', 'root');
define('DB_PASS', 'myroot');
define('DB_NAME', 'echo.fleetsapi.com');

// Create a new mysqli connection
$con = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
} 
?>