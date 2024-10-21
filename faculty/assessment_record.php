<?php 
// Include database connection
include 'db_connect.php'; // Update this line with your actual database connection file

// Fetch evaluations for the logged-in teacher
$evaluations = [];
if (isset($_SESSION['teacher_id'])) { // Assuming you have the teacher ID in the session
    $teacher_id = $_SESSION['teacher_id']; // Get the logged-in teacher's ID

    // Query to get students who evaluated subjects taught by the logged-in teacher
    $query = "
        SELECT e.evaluation_id, e.subject_id, 
               sub.subject AS subject_name,
               s.lastname AS student_name  -- Fetching the student's last name
        FROM evaluation_list e 
        JOIN subject_list sub ON e.subject_id = sub.id -- Assuming you have a subject_list table
        JOIN student_list s ON e.student_id = s.id -- Assuming you have a student_list table
        WHERE sub.faculty_id = $teacher_id  -- Filter by the logged-in teacher's ID
        ORDER BY e.date_taken DESC
    ";

    // Execute the query
    if ($result = $conn->query($query)) {
        while ($row = $result->fetch_assoc()) {
            $evaluations[] = $row; // Store the evaluation record
        }
    } else {
        // If the query fails, output the error
        echo "Query Error: " . $conn->error;
    }
}

// Display evaluated records in a table format
if (!empty($evaluations)): ?>
    <div class="container">
        <h2>Students Evaluated for Your Subjects</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Subject</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($evaluations as $evaluation): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($evaluation['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($evaluation['subject_name']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">No evaluations have been completed for your subjects yet.</div>
<?php endif; ?>
