<?php $faculty_id = $_SESSION['login_id'] ?>
<?php 
function ordinal_suffix($num){
    $num = $num % 100;
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
    <div class="row">
        <div class="col-md-12 mb-1">
            <div class="d-flex justify-content-end w-100">
                <button class="btn btn-sm btn-success bg-gradient-success" style="display:none" id="print-btn"><i class="fa fa-print"></i> Print</button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="callout callout-info">
                <div class="list-group" id="class-list">
                    
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="callout callout-info" id="printable">
                <div>
                    <h3 class="text-center">Evaluated Students Report</h3>
                    <hr>
                    <table width="100%">
                        <tr>
                            <td width="50%"><p><b>Academic Year: <span id="ay"><?php echo $_SESSION['academic']['year'].' '.(ordinal_suffix($_SESSION['academic']['semester'])) ?> Semester</span></b></p></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td width="50%"><p><b>Class: <span id="classField"></span></b></p></td>
                            <td width="50%"><p><b>Subject: <span id="subjectField"></span></b></p></td>
                        </tr>
                    </table>
                    <p class=""><b>Total Students Evaluated: <span id="tse"></span></b></p>
                </div>
                <fieldset class="border border-info p-2 w-100">
                    <legend class="w-auto">Evaluated Students List</legend>
                    <table class="table table-bordered">
                        <thead>
                            <tr class="bg-gradient-secondary">
                                <th>Name</th>
                                <th>Student ID</th>
                                <th>Evaluation Date</th>
                            </tr>
                        </thead>
                        <tbody id="evaluated-students-list">
                            <!-- Dynamically filled via AJAX -->
                        </tbody>
                    </table>
                </fieldset>
            </div>
        </div>
    </div>
</div>
<style>
    .list-group-item:hover{
        color: black !important;
        font-weight: 700 !important;
    }
</style>
<noscript>
    <style>
        table{
            width:100%;
            border-collapse: collapse;
        }
        table.wborder tr,table.wborder td,table.wborder th{
            border:1px solid gray;
            padding: 3px;
        }
        table.wborder thead tr{
            background: #6c757d linear-gradient(180deg,#828a91,#6c757d) repeat-x!important;
            color: #fff;
        }
        .text-center{
            text-align:center;
        } 
    </style>
</noscript>
<script>
    $(document).ready(function(){
        load_class()
    });

    function load_class(){
        start_load()
        $.ajax({
            url:"ajax.php?action=get_class",
            method:'POST',
            data:{fid:<?php echo $faculty_id ?>},
            error:function(err){
                console.log(err)
                alert_toast("An error occured",'error');
                end_load();
            },
            success:function(resp){
                if(resp){
                    resp = JSON.parse(resp);
                    if(Object.keys(resp).length <= 0 ){
                        $('#class-list').html('<a href="javascript:void(0)" class="list-group-item list-group-item-action disabled">No data to be display.</a>')
                    }else{
                        $('#class-list').html('');
                        Object.keys(resp).map(k=>{
                            $('#class-list').append('<a href="javascript:void(0)" data-json=\''+JSON.stringify(resp[k])+'\' data-id="'+resp[k].id+'" class="list-group-item list-group-item-action show-result">'+resp[k].class+' - '+resp[k].subj+'</a>');
                        });
                    }
                }
            },
            complete:function(){
                end_load();
                anchor_func();
                if('<?php echo isset($_GET['rid']) ?>' == 1){
                    $('.show-result[data-id="<?php echo isset($_GET['rid']) ? $_GET['rid'] : '' ?>"]').trigger('click');
                }else{
                    $('.show-result').first().trigger('click');
                }
            }
        });
    }

    function anchor_func(){
        $('.show-result').click(function(){
            var data = $(this).attr('data-json');
            data = JSON.parse(data);
            window.history.pushState({}, null, './index.php?page=evaluated_students&rid='+data.id);
            load_evaluated_students(<?php echo $faculty_id ?>, data.sid, data.id);
            $('#subjectField').text(data.subj);
            $('#classField').text(data.class);
            $('.show-result.active').removeClass('active');
            $(this).addClass('active');
        });
    }

    function load_evaluated_students($faculty_id, $subject_id, $class_id){
        start_load();
        $.ajax({
            url: 'ajax.php?action=get_evaluated_students',
            method: 'POST',
            data: {faculty_id: $faculty_id, subject_id: $subject_id, class_id: $class_id},
            error: function(err){
                console.log(err);
                alert_toast("An error occurred.", "error");
                end_load();
            },
            success: function(resp){
                if(resp){
                    resp = JSON.parse(resp);
                    $('#evaluated-students-list').html('');
                    if(Object.keys(resp).length <= 0){
                        $('#evaluated-students-list').html('<tr><td colspan="3" class="text-center">No students have evaluated yet.</td></tr>');
                        $('#tse').text('0');
                        $('#print-btn').hide();
                    } else {
                        $('#print-btn').show();
                        $('#tse').text(resp.tse);
                        resp.data.forEach(function(student){
                            $('#evaluated-students-list').append('<tr><td>'+student.name+'</td><td>'+student.student_id+'</td><td>'+student.evaluation_date+'</td></tr>');
                        });
                    }
                }
            },
            complete: function(){
                end_load();
            }
        });
    }

    $('#print-btn').click(function(){
        start_load();
        var ns = $('noscript').clone();
        var content = $('#printable').html();
        ns.append(content);
        var nw = window.open("Evaluated Students Report", "_blank", "width=900,height=700");
        nw.document.write(ns.html());
        nw.document.close();
        nw.print();
        setTimeout(function(){
            nw.close();
            end_load();
        }, 750);
    });
</script>
