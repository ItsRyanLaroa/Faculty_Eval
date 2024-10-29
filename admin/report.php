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
        <div class="input-group mb-3" style="max-width: 40%; margin-left: auto;">
            <input type="text" id="search-input" class="form-control" placeholder="Search...">
            <div class="input-group-append">
                <span class="input-group-text">Search</span>
            </div>
        </div>

        <div id="evaluation-cards" class="row">
            <?php 
            $faculty = $conn->query("SELECT f.id, 
                                            CONCAT(f.firstname, ' ', f.lastname) AS faculty_name, 
                                            f.avatar,  -- Ensure you have an avatar column
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
                $avatar = !empty($row['avatar']) ? 'assets/uploads/' . $row['avatar'] : 'assets/uploads/default_avatar.png';
            ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="user-icon" style="flex-shrink: 0;">
                        <img src="<?php echo $avatar; ?>" alt="Avatar" class="user-img border" style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%;">
                    </div>
                    <div class="ml-3"> <!-- Added margin-left for spacing -->
                        <h5 class="card-title font-weight-bold"><?php echo ucwords($row['faculty_name']); ?></h5>
                        <p class="card-text">
                            <strong>Academic Year:</strong> <?php echo $row['academic_year'] . ' ' . ordinal_suffix($_SESSION['academic']['semester']) . ' Semester'; ?><br>
                            <strong>Subject:</strong> <?php echo $row['subject']; ?><br>
                            <strong>Student Evaluated:</strong> <?php echo ucwords($row['student_name']); ?><br>
                            <strong>Class:</strong> <?php echo $row['curriculum'] . ' (' . $row['class_details'] . ')'; ?>
                        </p>
                        <button class="btn btn-info view-report" data-id="<?php echo $row['id']; ?>" data-subject-id="<?php echo $row['subject_id']; ?>" data-class-id="<?php echo $row['class_id']; ?>">
                            <i class="fa fa-eye"></i> View Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
                    <?php endwhile; ?>
        </div>

        <!-- Pagination Controls -->
        <div id="pagination-controls" class="text-center mt-3">
            <button id="prev-page" class="btn btn-secondary">Previous</button>
            <button id="next-page" class="btn btn-secondary">Next</button>
        </div>
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
                <div id="report-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Your existing styles and scripts follow... -->

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

    .user-icon {
    display: flex;
    align-items: center;
    margin-right: 15px; /* Space between the image and text */
}

.card-body {
    display: flex; /* Make card body a flex container */
    align-items: center; /* Center items vertically */
}

.font-weight-bold {
    font-weight: bold; /* Make the name bold */
}

.card-text {
    margin: 0; /* Remove default margin to ensure spacing looks good */
}


    .card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .btn-info {
        color: #fff;
        background-color: #17a2b8;
        border-color: #17a2b8;
    }

    .btn-info:hover {
        background-color: #138496;
        border-color: #117a8b;
    }
</style>
<script>
    $(document).ready(function() {
        const itemsPerPage = 6; // Number of items per page
        let currentPage = 1;

        // Function to show/hide cards based on pagination
        function showPage(page) {
            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;

            // Hide all cards
            $('.card').hide();

            // Show only the cards for the current page
            $('.card').slice(start, end).show();

            // Update pagination buttons
            $('#prev-page').toggle(page > 1);
            $('#next-page').toggle($('.card:visible').length === itemsPerPage);
        }

        // Initial display
        showPage(currentPage);

        // Previous button event
        $('#prev-page').click(function() {
            if (currentPage > 1) {
                currentPage--;
                showPage(currentPage);
            }
        });

        // Next button event
        $('#next-page').click(function() {
            currentPage++;
            showPage(currentPage);
        });

        // Filter function for the search bar
        $('#search-input').on('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            $('.card').each(function() {
                const facultyName = $(this).find('.card-title').text().toLowerCase();
                $(this).toggle(facultyName.includes(searchTerm));
            });
            currentPage = 1; // Reset to the first page on search
            showPage(currentPage);
        });

        // Handle View Report button click
        $('.view-report').click(function() {
            var faculty_id = $(this).data('id');
            var subject_id = $(this).data('subject-id');
            var class_id = $(this).data('class-id');

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

        // Function to display the report in the modal
        function displayReport(data) {
            var reportHtml = `<h4>Total Students Evaluated: ${data.tse}</h4>`;
            reportHtml += `<table class="table table-bordered"><thead><tr><th>Question</th><th>Rating 1</th><th>Rating 2</th><th>Rating 3</th><th>Rating 4</th><th>Rating 5</th></tr></thead><tbody>`;

            for (const question in data.data) {
                reportHtml += `<tr>
                                    <td>${question}</td>`;
                for (let rating = 1; rating <= 5; rating++) {
                    reportHtml += `<td>${data.data[question][rating] ? data.data[question][rating].toFixed(2) : 0}</td>`;
                }
                reportHtml += `</tr>`;
            }

            reportHtml += '</tbody></table>';
            $('#report-content').html(reportHtml);
            $('#report-modal').modal('show');
        }
    });
</script>

