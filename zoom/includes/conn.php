<?php
// Database configuration
$servername = "localhost";     // Change if your DB is on another host
$username   = "root";          // Change to your DB username
$password   = "Password@123!";              // Change to your DB password
$database   = "osr6db"; // Change to your actual DB name

// reCAPTCHA
$sitekey = "6Lc_KXIpAAAAAJquXM-qgJ4H7jKPFsCDz3S5joEe";
$secretkey = "6Lc_KXIpAAAAAMYDDuq15u75kawnYE7dVVzhLlkP";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set character set to utf8mb4
$conn->set_charset("utf8mb4");

// Uncomment below line for debugging
// echo "Connected successfully!";
?>
