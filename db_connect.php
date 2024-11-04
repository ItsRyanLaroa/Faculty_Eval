<?php
// Establish a database connection using environment variables
$conn = mysqli_connect(
    getenv('DB_HOST'),        // Database host (e.g., localhost or Coolify server)
    getenv('DB_USERNAME'),    // Database username from environment
    getenv('DB_PASSWORD'),    // Database password from environment
    getenv('DB_DATABASE'),    // Database name from environment
    getenv('DB_PORT') ?: '3306' // Database port, default to 3306 if not set
);

// Check if the connection was successful
if (!$conn) {
    // Output the connection error and stop the script
    die("Connection failed: " . mysqli_connect_error());
}
?>
