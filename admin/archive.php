<?php
include 'db_connect.php';

function ordinal_suffix($num) {
    $num = $num % 100; // Protect against large numbers
    if ($num < 11 || $num > 13) {
        switch ($num % 10) {
            case 1: return $num . 'st';
            case 2: return $num . 'nd';
            case 3: return $num . 'rd';
        }
    }
    return $num . 'th';
}
?>
<div class="col-lg-12">
    <div class="callout callout-info">
        <h3 class="text-center">Evaluation Report History</h3>
        <hr>

        <!-- Search Bar -->
        <div class="input-group mb-3" style="max-width: 40%; margin-left: auto;">
            <input type="text" class="form-control" id="search-input" placeholder="Search..." aria-label="Search">
            <div class="input-group-append">
                <span class="input-group-text"><i class="fa fa-search"></i></span>
            </div>
        </div>
        <table class="table table-bordered styled-table" id="list">
    <thead class="bg-gradient-secondary">
        <tr>
            <th>Faculty Name</th>
            <th>Academic Year</th>
            <th>Subject</th>
            <th>Student Evaluated</th>
            <th>Class</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
            <?php 
$faculty = $conn->query("SELECT f.id, 
                                CONCAT(f.firstname, ' ', f.lastname) AS faculty_name, 
                                r.academic_id, 
                                a.year AS academic_year, 
                                r.class_id, 
                                cl.curriculum, 
                                CONCAT(cl.level, ' - ', cl.section) AS class_details, 
                                r.subject_id, 
                                sl.subject,
                                CONCAT(st.firstname, ' ', st.lastname) AS student_name
                         FROM faculty_list f
                         LEFT JOIN evaluation_list r ON r.faculty_id = f.id
                         LEFT JOIN class_list cl ON r.class_id = cl.id
                         LEFT JOIN subject_list sl ON r.subject_id = sl.id
                         LEFT JOIN student_list st ON r.student_id = st.id
                         LEFT JOIN academic_list a ON r.academic_id = a.id
                         WHERE r.academic_id = {$_SESSION['academic']['id']}
                         ORDER BY CONCAT(f.firstname, ' ', f.lastname) ASC");

while ($row = $faculty->fetch_assoc()): 
?>
<tr>
    <td><?php echo ucwords($row['faculty_name']); ?></td>
    <td><?php echo $row['academic_year'] . ' ' . ordinal_suffix($_SESSION['academic']['semester']) . ' Semester'; ?></td>
    <td data-subject-id="<?php echo $row['subject_id']; ?>"><?php echo $row['subject']; ?></td>
    <td><?php echo ucwords($row['student_name']); ?></td>
    <td data-class-id="<?php echo $row['class_id']; ?>">
        <?php echo $row['curriculum'] . ' (' . $row['class_details'] . ')'; ?>
    </td>
    <td>
        <a class="btn btn-sm btn-info view-report" data-id="<?php echo $row['id']; ?>" href="javascript:void(0)">
            <i class="fa fa-eye"></i> View Report
        </a>
    </td>
</tr>
<?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for displaying the report -->
<div class="modal fade" id="report-modal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Evaluation Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Report will be populated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-secondary {
        background: #B31B1C linear-gradient(182deg, #b31b1b, #dc3545) repeat-x !important;
        color: #fff;
    }

    .modal.fade .modal-dialog {
        transition: transform 0.3s ease-out, opacity 0.3s ease-out;
        transform: translateY(-30px);
        opacity: 0;
    }

    .modal.show .modal-dialog {
        transform: translateY(0);
        opacity: 1;
    }

    .modal-content {
        position: fixed; /* Use fixed positioning to keep it centered on the viewport */
        top: 50%; /* Center vertically */
        left: 50%; /* Center horizontally */
        transform: translate(-47%, 1%);
        display: flex;
        flex-direction: column;
        width: 200%; /* Make it responsive */
        max-width: 1000px; /* Set a max-width for larger screens */
        pointer-events: auto;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid rgba(0, 0, 0, .2);
        border-radius: .3rem;
        box-shadow: 0 .25rem .5rem rgba(0, 0, 0, .5);
        outline: 0;
        padding: 20px; /* Add padding for better spacing */
        box-sizing: border-box; 
    }

    /* Additional Styles for the Search Bar */
    .input-group {
        margin-bottom: 20px; /* Space between search bar and table */
    }
    table.table-bordered.dataTable tbody th, table.table-bordered.dataTable tbody td {
    border-bottom-width: 0;
    border: none;
    color: #333;
    font-weight: 500; /* Add slight boldness */
}
/* General table styles */
table.table-bordered {
    border-collapse: collapse;
    width: 100%;
    margin: 20px 0;
    font-size: 0.95rem;
    background-color: #fff;
    color: #333;
}

table.table-bordered th, 
table.table-bordered td {
    padding: 12px 15px;
    border: 1px solid #ddd;
    text-align: left;
    vertical-align: middle;
}

/* Header style */
thead th {
    background: #dc143c; /* Red background for table headers */
    color: #f3f3f3; /* Light text color for contrast */
    font-weight: bold;
    border-bottom: 2px solid #b31b1c;
    text-transform: uppercase;
}

/* Styled table rows */
tbody tr {
    border-bottom: 1px solid #ddd; /* Light gray borders between rows */
    transition: background-color 0.3s ease; /* Smooth hover transition */
}

tbody tr:nth-of-type(even) {
    background-color: #f3f3f3; /* Light gray for alternate rows */
}

tbody tr:last-of-type {
    border-bottom: 2px solid #009879; /* Add a distinctive bottom border */
}

/* Hover effect */
tbody tr:hover {
    background-color: #f1f1f1; /* Slightly darker gray on hover */
}

/* Search bar styling */
.input-group {
    margin-bottom: 20px;
    max-width: 400px;
}

.input-group .form-control {
    border-radius: 0;
    box-shadow: none;
}

.input-group-text {
    background-color: #b31b1b;
    color: #fff;
    border: none;
    border-radius: 0;
}

/* Modal content style */
.modal-content {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Button styles */
.btn-info {
    color: #fff;
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.btn-info:hover {
    background-color: #138496;
    border-color: #117a8b;
}

/* Adjust table header */
.bg-gradient-secondary {
    background: #B31B1C linear-gradient(182deg, #b31b1b, #dc3545);
    color: #fff;
}

/* Card header */
.card-header {
    background-color: transparent;
    border-bottom: none;
    padding: .75rem 1.25rem;
    position: relative;
    border-top-left-radius: .25rem;
    border-top-right-radius: .25rem;
}

</style>

<script>
    $(document).ready(function() {
        // Filter function for the search bar
        $('#search-input').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#list tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        $('.view-report').click(function() {
            var faculty_id = $(this).data('id');
            var subject_id = $(this).closest('tr').find('td[data-subject-id]').data('subject-id');
            var class_id = $(this).closest('tr').find('td[data-class-id]').data('class-id');

            $.ajax({
                url: 'ajax.php?action=view_report',
                method: 'POST',
                data: {
                    faculty_id: faculty_id,
                    subject_id: subject_id,
                    class_id: class_id
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    displayReport(data);
                },
                error: function(err) {
                    console.error('Error fetching report:', err);
                }
            });
        });

        function displayReport(data) {
            var reportHtml = `<h4>Total Students Evaluated: ${data.tse}</h4>`;
            reportHtml += `<table class="table table-bordered"><thead><tr><th>Question</th><th>Rating 1</th><th>Rating 2</th><th>Rating 3</th><th>Rating 4</th><th>Rating 5</th></tr></thead><tbody>`;

            $.each(data.data, function(question, ratings) {
                reportHtml += `<tr>`;
                reportHtml += `<td>${question}</td>`;
                for (var i = 1; i <= 5; i++) {
                    reportHtml += `<td>${ratings[i] ? ratings[i].toFixed(2) + '%' : '0%'}</td>`;
                }
                reportHtml += `</tr>`;
            });

            reportHtml += `</tbody></table>`;

            // Display the report in a modal
            $('#report-modal .modal-body').html(reportHtml);
            $('#report-modal').modal('show');
        }
    });
</script>
