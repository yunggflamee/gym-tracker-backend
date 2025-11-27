<?php
// InfinityFree MySQL Credentials
$host = "sql213.infinityfree.com";
$dbname = "if0_40525148_gymtrackerdb";
$username = "if0_40525148";
$password = "adityarokade20";

// Create MySQL connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . mysqli_connect_error()
    ]));
}

// Allowed users (same as before)
define('ALLOWED_USERS', ['Aditya', 'Anurag', 'Aryan', 'Harshwardhan']);
?>
