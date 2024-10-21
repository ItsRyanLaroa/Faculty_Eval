<?php 
function ordinal_suffix($num){
    $num = $num % 100; // protect against large numbers
    if($num < 11 || $num > 13){
         switch($num % 10){
            case 1: return $num.'st';
            case 2: return $num.'nd';
            case 3: return $num.'rd';
        }
    }
    return $num.'th';
}
?>
<div class="col-lg-12">
    <div class="callout callout-info">
        <h3 class="text-center">Evaluation Report History</h3>
        <hr>
        <table class="table table-bordered" id="list">
            <thead class="bg-gradient-secondary">
                <tr>
                    <th>Faculty Name</th>
                    <th>Academic Year</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Student Name</th>
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

                while($row = $faculty->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo ucwords($row['faculty_name']) ?></td>
                    <td><?php echo $row['academic_year'] . ' ' . ordinal_suffix($_SESSION['academic']['semester']) . ' Semester' ?></td>
                    <td><?php echo $row['curriculum'] ?></td>
                    <td><?php echo $row['subject'] ?></td>
                    <td><?php echo ucwords($row['student_name']) ?></td>
                    <td>
                        <a href="javascript:void(0)" 
                           class="btn btn-block btn-sm btn-default btn-flat border-primary view-report" 
                           data-id="<?php echo $row['id'] ?>" 
                           data-name="<?php echo $row['faculty_name'] ?>">
                            <i class="fa fa-eye"></i> View Report
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .bg-gradient-secondary {
        background: #B31B1C linear-gradient(182deg, #b31b1b, #dc3545) repeat-x !important;
        color: #fff;
    }
    .table-bordered {
        border: 1px solid #ddd;
    }
    .table-bordered th, .table-bordered td {
        border: 1px solid #ddd;
        padding: 8px;
    }
    .view-report {
        text-decoration: none;
        display: block;
        width: 100%;
        padding: .25rem 1rem;
        color: #212529;
        text-align: left;
        background-color: transparent;
        border: 1px solid #007bff;
        color: #007bff;
        border-radius: 3px;
    }
    .view-report:hover {
        background-color: #007bff;
        color: #fff;
    }
</style>

<script>
$(document).ready(function() {
    // Initialize the DataTable
    $('#list').dataTable();

    // Handler for viewing a report
    $('.view-report').click(function() {
        const facultyId = $(this).attr('data-id');
        window.location.href = `evaluation_report.php?fid=${facultyId}`;
    });
});
</script>
