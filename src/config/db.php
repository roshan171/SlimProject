<?php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'slimapp');

// Create a new database connection
function connectDB() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check the connection
    if (!$conn) {
        return null;
    }

    return $conn;
}
