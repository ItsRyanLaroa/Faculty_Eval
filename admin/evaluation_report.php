<?php 
include 'db_connection.php'; // Include your database connection file
$faculty_id = isset($_GET['fid']) ? $_GET['fid'] : ''; 
?>

<?php 
function ordinal_suffix($num) {
    $num = $num % 100; // protect against large numbers
    if($num < 11 || $num > 13) {
        switch($num % 10) {
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
        <div class="d-flex w-100 justify-content-center align-items-center">
            <label for="faculty">Select Faculty</label>
            <div class="mx-2 col-md-4">
                <select name="" id="faculty_id" class="form-control form-control-sm select2">
                    <option value=""></option>
                    <?php 
                    $faculty = $conn->query("SELECT *, CONCAT(firstname, ' ', lastname) as name FROM faculty_list ORDER BY CONCAT(firstname, ' ', lastname) ASC");
                    $f_arr = array();
                    $fname = array();
                    while($row = $faculty->fetch_assoc()):
                        $f_arr[$row['id']] = $row;
                        $fname[$row['id']] = ucwords($row['name']);
                    ?>
                    <option value="<?php echo $row['id'] ?>" <?php echo isset($faculty_id) && $faculty_id == $row['id'] ? "selected" : "" ?>><?php echo ucwords($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 mb-1">
            <div class="d-flex justify-content-end w-100">
                <button class="btn btn-sm btn-success bg-gradient-success" style="display:none" id="print-btn"><i class="fa fa-print"></i> Print</button>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="callout callout-info" id="printable">
                <div>
                    <h3 class="text-center">Evaluation Report</h3>
                    <hr>
                    <table width="100%">
                        <tr>
                            <td width="50%"><p><b>Faculty: <span id="fname"></span></b></p></td>
                            <td width="50%"><p><b>Academic Year: <span id="ay"><?php echo $_SESSION['academic']['year'].' '.(ordinal_suffix($_SESSION['academic']['semester'])) ?> Semester</span></b></p></td>
                        </tr>
                    </table>
                    <p class=""><b>Total Students Evaluated: <span id="tse"></span></b></p>
                </div>
                <fieldset class="border border-info p-2 w-100">
                    <legend class="w-auto">Rating Legend</legend>
                    <p>5 = Strongly Agree, 4 = Agree, 3 = Uncertain, 2 = Disagree, 1 = Strongly Disagree</p>
                </fieldset>
                <?php 
                $q_arr = array();
                $criteria = $conn->query("SELECT * FROM criteria_list WHERE id IN (SELECT criteria_id FROM question_list WHERE academic_id = {$_SESSION['academic']['id']}) ORDER BY ABS(order_by) ASC");
                while($crow = $criteria->fetch_assoc()): 
                ?>
                <table class="table table-condensed wborder">
                    <thead>
                        <tr class="bg-gradient-secondary">
                            <th class="p-1"><b><?php echo $crow['criteria'] ?></b></th>
                            <th width="5%" class="text-center">1</th>
                            <th width="5%" class="text-center">2</th>
                            <th width="5%" class="text-center">3</th>
                            <th width="5%" class="text-center">4</th>
                            <th width="5%" class="text-center">5</th>
                        </tr>
                    </thead>
                    <tbody class="tr-sortable">
                        <?php 
                        $questions = $conn->query("SELECT * FROM question_list WHERE criteria_id = {$crow['id']} AND academic_id = {$_SESSION['academic']['id']} ORDER BY ABS(order_by) ASC");
                        while($row = $questions->fetch_assoc()): 
                        $q_arr[$row['id']] = $row;
                        ?>
                        <tr class="bg-white">
                            <td class="p-1" width="40%">
                                <?php echo $row['question'] ?>
                            </td>
                            <?php for($c = 1; $c <= 5; $c++): ?>
                            <td class="text-center">
                                <span class="rate_<?php echo $c.'_'.$row['id'] ?> rates"></span>
                            </td>
                            <?php endfor; ?>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-secondary {
        background: #B31B1C linear-gradient(182deg, #b31b1b, #dc3545) repeat-x !important;
        color: #fff;
    }
    .list-group-item:hover {
        color: black !important;
        font-weight: 700 !important;
    }
    .list-group-item.active {
        z-index: 2;
        color: #fff;
        background-color: #b31b1b;
        border-color: black;
    }
</style>

<noscript>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table.wborder tr, table.wborder td, table.wborder th {
            border: 1px solid gray;
            padding: 3px;
        }
        table.wborder thead tr {
            background: #6c757d linear-gradient(180deg, #828a91, #6c757d) repeat-x!important;
            color: #fff;
        }
        .text-center {
            text-align: center;
        } 
        .text-right {
            text-align: right;
        } 
        .text-left {
            text-align: left;
        } 
    </style>
</noscript>

<script>
$(document).ready(function() {
    $('#faculty_id').change(function() {
        if ($(this).val() > 0)
            window.history.pushState({}, null, './index.php?page=report&fid=' + $(this).val());
        load_report_data();
    });

    if ($('#faculty_id').val() > 0)
        load_report_data();
});

function load_report_data() {
    start_load();
    var fname = <?php echo json_encode($fname) ?>;
    $('#fname').text(fname[$('#faculty_id').val()]);

    $.ajax({
        url: "ajax.php?action=get_report_data",
        method: 'POST',
        data: {fid: $('#faculty_id').val()},
        error: function(err) {
            console.log(err);
            alert_toast("An error occurred", 'error');
            end_load();
        },
        success: function(resp) {
            if (resp) {
                resp = JSON.parse(resp);
                $('#tse').text(resp.total_students_evaluated);
                Object.keys(resp.ratings).forEach(function(questionId) {
                    $('.rate_1_' + questionId).text(resp.ratings[questionId][1] + '%');
                    $('.rate_2_' + questionId).text(resp.ratings[questionId][2] + '%');
                    $('.rate_3_' + questionId).text(resp.ratings[questionId][3] + '%');
                    $('.rate_4_' + questionId).text(resp.ratings[questionId][4] + '%');
                    $('.rate_5_' + questionId).text(resp.ratings[questionId][5] + '%');
                });
            }
        },
        complete: function() {
            end_load();
        }
    });
}

$('#print-btn').click(function() {
    start_load();
    var ns = $('noscript').clone();
    var content = $('#printable').html();
    ns.append(content);
    var nw = window.open("Report", "_blank", "width=900,height=700");
    nw.document.write(ns.html());
    nw.document.close();
    nw.print();
    setTimeout(function() {
        nw.close();
        end_load();
    }, 750);
});
</script>
