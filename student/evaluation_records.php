<?php
include 'db_connect.php';

function ordinal_suffix($num) {
    $num = $num % 100;
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
        <h3 class="text-center">Student Evaluations</h3>
        
        <!-- Rows per page selector -->
        <div class="mb-3" style="width: 100px; margin-left: auto;float:left">
            <select id="rows-per-page" class="form-control">
                <option value="5">5 rows</option>
                <option value="10">10 rows</option>
                <option value="15">15 rows</option>
                <option value="20">20 rows</option>
            </select>
        </div>
        <!-- Search Bar -->
        <div class="input-group mb-3" style="max-width: 20%; margin-left: auto;">
            <input type="text" class="form-control" id="search-input" placeholder="Search..." aria-label="Search">
            <div class="input-group-append">
                <span class="input-group-text"><i class="fa fa-search"></i></span>
            </div>
        </div>

        <table class="table table-bordered styled-table" id="list">
            <thead class="bg-gradient-secondary">
                <tr>
                    <th>Faculty</th>
                    <th>Subject</th>
                    <th>Academic Year</th>
                    <th>Class</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $student_id = $_SESSION['login_id'];

                // Fetch unique evaluations for the logged-in student
                $evaluations = $conn->query("SELECT DISTINCT 
                    CONCAT(f.lastname, ', ', f.firstname) AS faculty_name,
                    sl.subject,
                    a.year AS academic_year,
                    CONCAT(cl.level, ' - ', cl.section) AS class_details,
                    cl.curriculum
                FROM evaluation_list r
                LEFT JOIN subject_list sl ON r.subject_id = sl.id
                LEFT JOIN faculty_list f ON r.faculty_id = f.id
                LEFT JOIN class_list cl ON r.class_id = cl.id
                LEFT JOIN academic_list a ON r.academic_id = a.id
                WHERE r.student_id = '$student_id'
                GROUP BY r.student_id, f.id, sl.subject, a.year, cl.id
                ORDER BY f.lastname ASC");

                // Display each unique evaluation entry
                while ($row = $evaluations->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo ucwords($row['faculty_name']); ?></td>
                    <td><?php echo $row['subject']; ?></td>
                    <td><?php echo $row['academic_year'] . ' ' . ordinal_suffix($_SESSION['academic']['semester']) . ' Semester'; ?></td>
                    <td><?php echo $row['curriculum'] . ' (' . $row['class_details'] . ')'; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div id="pagination-controls" class="mt-3 d-flex justify-content-end"></div>
    </div>
</div>


<style>
    .bg-gradient-secondary {
        background: #B31B1C linear-gradient(182deg, #b31b1b, #dc3545) repeat-x !important;
        color: #fff;
    }
    .styled-table tbody tr:nth-of-type(even) {
        background-color: #f3f3f3;
    }
    .input-group, #rows-per-page {
        margin-bottom: 10px;
    }
    #pagination-controls {
        display: flex;
        align-items: center;
    }
    #pagination-controls button {
        margin: 0 5px;
        border: none;
        padding: 5px 10px;
        background-color: #007bff;
        color: #fff;
        border-radius: 3px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    #pagination-controls button.active {
        background-color: #0056b3;
    }
    #pagination-controls button:disabled {
        background-color: #d6d6d6;
        cursor: not-allowed;
    }
</style>

<script>
    $(document).ready(function() {
        let rowsPerPage = 5;
        let currentPage = 1;

        $('#search-input').on('keyup', function() {
            let value = $(this).val().toLowerCase();
            filterTable(value);
        });

        $('#rows-per-page').on('change', function() {
            rowsPerPage = parseInt($(this).val());
            currentPage = 1;
            paginateTable();
        });

        function filterTable(query) {
            $('#list tbody tr').each(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(query) > -1);
            });
            paginateTable();
        }

        function paginateTable() {
            const rows = $('#list tbody tr');
            const totalRows = rows.length;
            const totalPages = Math.ceil(totalRows / rowsPerPage);
            rows.hide();

            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            rows.slice(start, end).show();

            renderPaginationControls(totalPages);
        }

        function renderPaginationControls(totalPages) {
            $('#pagination-controls').empty();

            const prevButton = $('<button></button>')
                .text('Previous')
                .prop('disabled', currentPage === 1)
                .on('click', function() {
                    if (currentPage > 1) {
                        currentPage--;
                        paginateTable();
                    }
                });

            const nextButton = $('<button></button>')
                .text('Next')
                .prop('disabled', currentPage === totalPages)
                .on('click', function() {
                    if (currentPage < totalPages) {
                        currentPage++;
                        paginateTable();
                    }
                });

            $('#pagination-controls').append(prevButton);

            for (let i = 1; i <= totalPages; i++) {
                const btn = $('<button></button>')
                    .text(i)
                    .addClass(i === currentPage ? 'active' : '')
                    .on('click', function() {
                        currentPage = i;
                        paginateTable();
                    });
                $('#pagination-controls').append(btn);
            }

            $('#pagination-controls').append(nextButton);
        }

        paginateTable();
    });
</script>
