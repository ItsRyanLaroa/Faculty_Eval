<?php
// Start the session to access the session variables
session_start();
include '../db_connect.php';

// Check if the login ID is set in the session
if (!isset($_SESSION['login_id'])) {
    echo 'Login ID not found. Please log in.';
    exit;
}

// Get the faculty ID from the session
$faculty_id = $_SESSION['login_id'];

// Fetch the faculty details using the login ID safely
$stmt = $conn->prepare("SELECT *, CONCAT(firstname, ' ', lastname) AS name FROM faculty_list WHERE id = ?");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();
$faculty = $result->fetch_assoc();
$faculty_name = $faculty['name'];

// Fetch classes and subjects for the specific teacher safely
$query = "
    SELECT cl.id AS class_id, 
           CONCAT(cl.curriculum, ' ', cl.level, ' - ', cl.section) AS class_name,
           sl.id AS subject_id,
           CONCAT(sl.code, ' - ', sl.subject) AS subject_name
    FROM class_list cl
    JOIN subject_list sl ON cl.subject_id = sl.id
    WHERE cl.teacher_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$classes_and_subjects = $stmt->get_result();

$c_arr = [];
$s_arr = [];

// Prepare classes and subjects arrays
while ($row = $classes_and_subjects->fetch_assoc()) {
    $c_arr[$row['class_id']] = $row['class_name'];
    $s_arr[$row['subject_id']] = $row['subject_name'];
}

// Fetch active academic year with status = 1
$active_academic_query = "SELECT id, year FROM academic_list WHERE status = 1";
$active_academic_result = $conn->query($active_academic_query);
$active_academic = $active_academic_result->fetch_assoc(); // Get the active academic year

$active_academic_id = isset($active_academic['id']) ? $active_academic['id'] : '';
$active_academic_year = isset($active_academic['year']) ? $active_academic['year'] : 'No Active Academic Year';
?>
<style>
    .container-fluid {
        padding: 20px;
    }


    .modal-body {
        padding: 20px;
    }

    .form-group {
      
        padding: 5px;
        width: 150px;
    }

  
    .border-danger {
        border-color: #dc3545 !important; /* Bootstrap danger color for errors */
    }

    .error-message {
        display: none; /* No longer displaying alert messages */
    }
</style>

<div class="container-fluid">
    <form action="" id="manage-restriction-<?php echo htmlspecialchars($faculty_id); ?>">
        <input type="hidden" name="faculty_id" value="<?php echo htmlspecialchars($faculty_id); ?>">
        
        <!-- Modal Header -->
        <div class="modal-header">
            <h5>Add subject</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <div class="row">
                <!-- Faculty Name Display (Hidden) -->
                <div class="form-group" style="display: none;">
                    <label for="faculty_name" class="control-label" id="faculty_name" data-faculty="<?php echo htmlspecialchars($faculty_name); ?>">
                        Faculty: <?php echo htmlspecialchars($faculty_name); ?>
                    </label>
                </div>

                <!-- Academic ID (Hidden) -->
                <div class="form-group" style="display: none;">
                    <label for="academic_id" class="control-label" id="academic_id" data-academic="<?php echo htmlspecialchars($active_academic_id); ?>">
                        Academic: <?php echo htmlspecialchars($active_academic_year); ?>
                    </label>
                </div>

                <!-- Class Dropdown -->
                <div class="form-group">
                    <label for="class_id" class="control-label">Class</label>
                    <select name="class_id" id="class_id-<?php echo htmlspecialchars($faculty_id); ?>" class="form-control form-control-sm select2" required>
                        <option value="">Select Class</option>
                        <?php foreach ($c_arr as $class_id => $class): ?>
                            <option value="<?php echo htmlspecialchars($class_id); ?>">
                                <?php echo htmlspecialchars($class); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Subject Dropdown -->
                <div class="form-group">
                    <label for="subject_id" class="control-label">Subject</label>
                    <select name="subject_id" id="subject_id-<?php echo htmlspecialchars($faculty_id); ?>" class="form-control form-control-sm select2" required>
                        <option value="">Select Subject</option>
                        <?php foreach ($s_arr as $subject_id => $subject): ?>
                            <option value="<?php echo htmlspecialchars($subject_id); ?>">
                                <?php echo htmlspecialchars($subject); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    $('#manage-restriction-<?php echo htmlspecialchars($faculty_id); ?>').submit(function(e) {
        e.preventDefault();
        $('input, select').removeClass("border-danger");
        start_load();

        // Retrieve values from data attributes of the hidden labels
        const facultyName = $('#faculty_name').data('faculty');
        const academicId = $('#academic_id').data('academic');

        // Debugging output
        console.log('Faculty Name:', facultyName);
        console.log('Academic ID:', academicId);

        // Ensure required fields are filled out
        let hasError = false;
        if (!$('#class_id-<?php echo htmlspecialchars($faculty_id); ?>').val()) {
            $('#class_id-<?php echo htmlspecialchars($faculty_id); ?>').addClass("border-danger");
            hasError = true;
        }
        if (!$('#subject_id-<?php echo htmlspecialchars($faculty_id); ?>').val()) {
            $('#subject_id-<?php echo htmlspecialchars($faculty_id); ?>').addClass("border-danger");
            hasError = true;
        }

        // Prevent submission if there are errors
        if (hasError) {
            end_load();
            return false;
        }

        // Ensure hidden values are present
        if (!facultyName || !academicId) {
            end_load();
            return false;
        }

        // Add academic ID to form data
        const formData = new FormData($(this)[0]);
        formData.append('academic_id', academicId); // Append academic_id to the form data

        $.ajax({
            url: 'ajax.php?action=save_restriction',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            type: 'POST',
            success: function(resp) {
                if (resp == 1) {
                    alert_toast('Restriction successfully saved.', "success");
                    setTimeout(function() {
                        location.replace('index.php?page=subject');
                    }, 750);
                } else if (resp == 2) {
                    $('#class_id-<?php echo htmlspecialchars($faculty_id); ?>, #subject_id-<?php echo htmlspecialchars($faculty_id); ?>').addClass("border-danger");
                    end_load();
                } else {
                    end_load();
                }
            },
            error: function() {
                end_load();
            }
        });
    });
</script>
