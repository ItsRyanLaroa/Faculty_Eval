
<?php $faculty_id = $_SESSION['login_id'] ?>
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
			<span style="color: #dc143c;"><h3 class="text-center" style="font-weight: bold;">Evaluation Report</h3></span>
			<hr>
			<table width="100%">
					<tr>
						<td width="50%"><p><b>Academic Year: <span id="ay"><?php echo '<span style="color: #dc143c;">'.$_SESSION['academic']['year'].'</span> ('.'<span style="color: #dc143c;">'.(ordinal_suffix($_SESSION['academic']['semester'])).' Semester</span>)'; ?></span></b></p></td>
						<td></td>
					</tr>
					<tr>
						<td width="50%"><p><b>Class: <span id="classField" style="color: #dc143c;"></span></b></p></td>
						<td width="50%"><p><b>Subject: <span id="subjectField" style="color: #dc143c;"></span></b></p></td>
					</tr>
			</table>
				<p class=""><b>Total Student Evaluated: <span id="tse" style="color: #dc143c;"></span></b></p>
			</div>
				<fieldset class="border border-info p-2 w-100">
				   <span style="font-weight: bold;"><legend  class="w-auto">Rating Legend</legend></span>
				   			<span style="color: #dc143c; font-weight: bold;">5</span> - Strongly Agree <span style="color: #007bff; font-weight: bold;"> | </span>
							<span style="color: #dc143c; font-weight: bold;">4</span> - Agree <span style="color: #007bff; font-weight: bold;"> | </span>
							<span style="color: #dc143c; font-weight: bold;">3</span> - Uncertain <span style="color: #007bff; font-weight: bold;"> | </span>
							<span style="color: #dc143c; font-weight: bold;">2</span> - Disagree <span style="color: #007bff; font-weight: bold;"> | </span>
							<span style="color: #dc143c; font-weight: bold;">1</span> - Strongly Disagree
				</fieldset>
				<?php 
							$q_arr = array();
						$criteria = $conn->query("SELECT * FROM criteria_list where id in (SELECT criteria_id FROM question_list where academic_id = {$_SESSION['academic']['id']} ) order by abs(order_by) asc ");
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
							$questions = $conn->query("SELECT * FROM question_list where criteria_id = {$crow['id']} and academic_id = {$_SESSION['academic']['id']} order by abs(order_by) asc ");
							while($row=$questions->fetch_assoc()):
							$q_arr[$row['id']] = $row;
							?>
							<tr class="bg-white">
								<td class="p-1" width="40%">
									<?php echo $row['question'] ?>
								</td>
								<?php for($c=1;$c<=5;$c++): ?>
								<td class="text-center">
									<span class="rate_<?php echo $c.'_'.$row['id'] ?> rates"></span>
			                      </div>
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
	.list-group-item:hover{
		color: black !important;
		font-weight: 700 !important;
	}

	.list-group-item.active{
		border: #dc143c;
	}

	.callout.callout-info{
    border-left-color: #dc143c;
  }

  .btn{
	background: #dc143c !important;
	border: #dc143c;
  }

  .btn:hover{
	background-color: #007bff !important;
  }

  .border-info{
	border-color: #dc143c !important;
	margin-bottom: 20px;
	margin-top: 20px;
  }

  .bg-gradient-secondary {
		background: #007bff !important;
		color: #fff;
	}

	.p-1{
		justify-content: center;
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
			padding: 3px
		}
		table.wborder thead tr{
			background: #6c757d linear-gradient(180deg,#828a91,#6c757d) repeat-x!important;
    		color: #fff;
		}
		.text-center{
			text-align:center;
		} 
		.text-right{
			text-align:right;
		} 
		.text-left{
			text-align:left;
		} 
	</style>
</noscript>
<script>
	$(document).ready(function(){
		load_class()
	})
	function load_class(){
		start_load()
		$.ajax({
			url:"ajax.php?action=get_class",
			method:'POST',
			data:{fid:<?php echo $faculty_id ?>},
			error:function(err){
				console.log(err)
				alert_toast("An error occured",'error')
				end_load()
			},
			success:function(resp){
				if(resp){
					resp = JSON.parse(resp)
					if(Object.keys(resp).length <= 0 ){
						$('#class-list').html('<a href="javascript:void(0)" class="list-group-item list-group-item-action disabled">No data to be display.</a>')
					}else{
						$('#class-list').html('')
						Object.keys(resp).map(k=>{
						$('#class-list').append('<a href="javascript:void(0)" data-json=\''+JSON.stringify(resp[k])+'\' data-id="'+resp[k].id+'" class="list-group-item list-group-item-action show-result">'+resp[k].class+' - '+resp[k].subj+'</a>')
						})

					}
				}
			},
			complete:function(){
				end_load()
				anchor_func()
				if('<?php echo isset($_GET['rid']) ?>' == 1){
					$('.show-result[data-id="<?php echo isset($_GET['rid']) ? $_GET['rid'] : '' ?>"]').trigger('click')
				}else{
					$('.show-result').first().trigger('click')
				}
			}
		})
	}
	function anchor_func(){
		$('.show-result').click(function(){
			var vars = [], hash;
			var data = $(this).attr('data-json')
				data = JSON.parse(data)
			var _href = location.href.slice(window.location.href.indexOf('?') + 1).split('&');
			for(var i = 0; i < _href.length; i++)
				{
					hash = _href[i].split('=');
					vars[hash[0]] = hash[1];
				}
			window.history.pushState({}, null, './index.php?page=result&rid='+data.id);
			load_report(<?php echo $faculty_id ?>,data.sid,data.id);
			$('#subjectField').text(data.subj)
			$('#classField').text(data.class)
			$('.show-result.active').removeClass('active')
			$(this).addClass('active')
		})
	}
	function load_report($faculty_id, $subject_id,$class_id){
		if($('#preloader2').length <= 0)
		start_load()
		$.ajax({
			url:'ajax.php?action=get_report',
			method:"POST",
			data:{faculty_id: $faculty_id,subject_id:$subject_id,class_id:$class_id},
			error:function(err){
				console.log(err)
				alert_toast("An Error Occured.","error");
				end_load()
			},
			success:function(resp){
				if(resp){
					resp = JSON.parse(resp)
					if(Object.keys(resp).length <= 0){
						$('.rates').text('')
						$('#tse').text('')
						$('#print-btn').hide()
					}else{
						$('#print-btn').show()
						$('#tse').text(resp.tse)
						$('.rates').text('-')
						var data = resp.data
						Object.keys(data).map(q=>{
							Object.keys(data[q]).map(r=>{
								console.log($('.rate_'+r+'_'+q),data[q][r])
								$('.rate_'+r+'_'+q).text(data[q][r]+'%')
							})
						})
					}
					
				}
			},
			complete:function(){
				end_load()
			}
		})
	}
	$('#print-btn').click(function(){
		start_load()
		var ns =$('noscript').clone()
		var content = $('#printable').html()
		ns.append(content)
		var nw = window.open("Report","_blank","width=900,height=700")
		nw.document.write(ns.html())
		nw.document.close()
		nw.print()
		setTimeout(function(){
			nw.close()
			end_load()
		},750)
	})
</script>