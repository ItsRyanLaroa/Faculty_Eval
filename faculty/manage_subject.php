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

// Fetch active academic IDs from academic_list where status is 1
$academic_query = "SELECT id, year FROM academic_list WHERE status = 1";
$academic_result = $conn->query($academic_query);

$c_arr = [];
$s_arr = [];
$a_arr = [];

while ($row = $classes_and_subjects->fetch_assoc()) {
    $c_arr[$row['class_id']] = $row['class_name'];
    $s_arr[$row['subject_id']] = $row['subject_name'];
}

// Populate academic options
while ($row = $academic_result->fetch_assoc()) {
    $a_arr[$row['id']] = $row['year'];
}
?>
<div class="container-fluid">
    <form action="" id="manage-restriction-<?php echo htmlspecialchars($faculty_id); ?>">
        <input type="hidden" name="faculty_id" value="<?php echo htmlspecialchars($faculty_id); ?>">
        
        <!-- Modal Header -->
        <div class="modal-header">
            <h5 class="modal-title">Manage Restrictions</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <div class="row">
                <div id="msg" class="form-group"></div>

                <!-- Faculty Name Display -->
                <div class="form-group">
                    <label for="faculty_name" class="control-label">Faculty</label>
                    <input type="text" class="form-control form-control-sm" id="faculty_name-<?php echo htmlspecialchars($faculty_id); ?>" value="<?php echo htmlspecialchars($faculty_name); ?>" readonly>
                </div>

                <!-- Academic Dropdown -->
                <div class="form-group">
                    <label for="academic_id" class="control-label">Academic</label>
                    <select name="academic_id" id="academic_id-<?php echo htmlspecialchars($faculty_id); ?>" class="form-control form-control-sm select2" required>
                        <option value="">Select Academic</option>
                        <?php foreach ($a_arr as $academic_id => $academic_name): ?>
                            <option value="<?php echo htmlspecialchars($academic_id); ?>">
                                <?php echo htmlspecialchars($academic_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Class Dropdown -->
                <div class="form-group">
                    <label for="class_id" class="control-label">Class</label>
                    <select name="class_id" id="class_id-<?php echo htmlspecialchars($faculty_id); ?>" class="form-control form-control-sm select2" required>
                        <option value="">Select Class</option>
                        <?php foreach ($c_arr as $class_id => $class): ?>
                            <option value="<?php echo htmlspecialchars($class_id); ?>" <?php echo isset($selected_class_id) && $selected_class_id == $class_id ? 'selected' : ''; ?>>
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
                            <option value="<?php echo htmlspecialchars($subject_id); ?>" <?php echo isset($selected_subject_id) && $selected_subject_id == $subject_id ? 'selected' : ''; ?>>
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
    $('#manage-restriction-<?php echo htmlspecialchars($faculty_id); ?>').submit(function(e){
        e.preventDefault();
        $('input, select').removeClass("border-danger");
        start_load();
        $('#msg').html('');

        // Ensure required fields are filled out
        if (!$('#academic_id-<?php echo htmlspecialchars($faculty_id); ?>').val() || 
            !$('#class_id-<?php echo htmlspecialchars($faculty_id); ?>').val() || 
            !$('#subject_id-<?php echo htmlspecialchars($faculty_id); ?>').val()) {
            $('#msg').html("<div class='alert alert-danger'>Please select an academic, class, and subject.</div>");
            $('select[name="academic_id"], select[name="class_id"], select[name="subject_id"]').addClass("border-danger");
            end_load();
            return false;
        }

        $.ajax({
            url: 'ajax.php?action=save_restriction',
            data: new FormData($(this)[0]),
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
                    $('#msg').html("<div class='alert alert-danger'>This restriction already exists.</div>");
                    $('select[name="academic_id"], select[name="class_id"], select[name="subject_id"]').addClass("border-danger");
                    end_load();
                } else {
                    $('#msg').html("<div class='alert alert-danger'>An error occurred. Please try again.</div>");
                    end_load();
                }
            },
            error: function() {
                $('#msg').html("<div class='alert alert-danger'>An error occurred while saving the restriction.</div>");
                end_load();
            }
        });
    });
</script>
