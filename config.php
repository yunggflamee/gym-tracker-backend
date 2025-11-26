<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Your DB username
define('DB_PASSWORD', '');     // Your DB password
define('DB_NAME', 'gym_tracker_db');

// Create a new database connection
 $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Allowed users
define('ALLOWED_USERS', ['Aditya', 'Anurag', 'Aryan', 'Harshwardhan']);
?>