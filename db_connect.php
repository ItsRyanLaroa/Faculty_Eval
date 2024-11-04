<?php
// Fetch database credentials from environment variables
$host = getenv('DB_HOST');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$database = getenv('DB_DATABASE');

// Connect to the database
$conn = new mysqli($host, $username, $password, $database) or die("Could not connect to MySQL: " . mysqli_error($conn));
