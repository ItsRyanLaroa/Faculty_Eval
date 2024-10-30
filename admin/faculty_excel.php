<?php
require '../vendor/autoload.php'; // Adjust the path as necessary for PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Error handling function
function handleError($message) {
    error_log($message, 3, '../logs/error_log.txt'); // Logs the error message to a specified file
    header('Location: ../index.php?page=faculty_list&error=1'); // Redirect with an error code
    exit;
}

if (isset($_FILES['excel_file'])) {
    // Check if the file is uploaded
    if ($_FILES['excel_file']['error'] == UPLOAD_ERR_OK) {
        $fileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($_FILES['excel_file']['tmp_name']);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($fileType);
        
        try {
            $spreadsheet = $reader->load($_FILES['excel_file']['tmp_name']);
        } catch (Exception $e) {
            handleError("Error loading spreadsheet: " . $e->getMessage());
        }
        
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        
        // Connect to the database
        include '../db_connect.php'; // Include your database connection file

        // Loop through each row of the spreadsheet
        foreach ($sheetData as $row) {
            $school_id = $row['A']; // Adjust column index as per your Excel file
            $firstname = $row['B'];
            $lastname = $row['C'];
            $position = $row['D']; 
            $email = $row['E']; // Assuming column E holds the email
            $password = $row['F']; // Assuming column F holds the plain-text password

            // Hash the password using md5 (Note: md5 is not recommended for security purposes)
            $hashedPassword = md5($password);

            // Check if the school_id already exists in the database to prevent duplication
            $checkStmt = $conn->prepare("SELECT id FROM faculty_list WHERE school_id = ?");
            if (!$checkStmt) {
                handleError("Prepare failed: " . $conn->error);
            }
            $checkStmt->bind_param("s", $school_id);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                // If a record with the same school_id already exists, skip the insertion
                error_log("Duplicate entry for school_id: $school_id", 3, '../logs/error_log.txt');
            } else {
                // Retrieve the next available id for faculty_list
                $result = $conn->query("SELECT IFNULL(MAX(id), 0) + 1 AS next_id FROM faculty_list");
                $next_id = $result->fetch_assoc()['next_id'];

                // Prepare and bind for insertion
                $stmt = $conn->prepare("INSERT INTO faculty_list (id, school_id, firstname, lastname, position, email, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    handleError("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("issssss", $next_id, $school_id, $firstname, $lastname, $position, $email, $hashedPassword);

                // Execute the statement
                if (!$stmt->execute()) {
                    // Handle errors
                    handleError("Error inserting data for school_id $school_id: " . $stmt->error);
                }
                $stmt->close(); // Close the statement after execution
            }

            // Close the check statement
            $checkStmt->close();
        }

        // Redirect or return success message
        header('Location: ../index.php?page=faculty_list&success=1');
        exit;
    } else {
        // Handle file upload error
        handleError("File upload error: " . $_FILES['excel_file']['error']);
    }
} else {
    // Handle missing file
    handleError("No file was uploaded.");
}
?>
