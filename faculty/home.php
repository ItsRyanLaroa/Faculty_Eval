<?php include('db_connect.php'); ?>
<?php 
function ordinal_suffix1($num){
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
$astat = array("Not Yet Started","On-going","Closed");
?>
<style>
  h3{
    font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
  }
</style>
<div class="col-12">
    <div class="card">
      <div class="card-body">
        <h3>Welcome <?php echo $_SESSION['login_name'] ?>!</h3>
        <br>
        <div class="col-md-5">
          <div class="callout callout-info">
            <h5><b>Academic Year: <?php echo $_SESSION['academic']['year'].' '.(ordinal_suffix1($_SESSION['academic']['semester'])) ?> Semester</b></h5>
            <h6><b>Evaluation Status: <?php echo $astat[$_SESSION['academic']['status']] ?></b></h6>
          </div>
        </div>
      </div>
    </div>
</div>
<div class="row">
  <div class="col-12 col-sm-6 col-md-4">
    <div class="small-box bg-light shadow-sm border">
      <div class="inner">
        <!-- Get the total unique evaluated students -->
        <h3><?php 
          $evaluated_students_query = "SELECT COUNT(DISTINCT student_id) AS total_evaluated FROM evaluation_list"; 
          $result = $conn->query($evaluated_students_query);
          $row = $result->fetch_assoc();
          echo $row['total_evaluated']; 
        ?></h3>
        <p>Total Persons Evaluated</p>
      </div>
      <div class="icon">
        <i class="fa fa-star"></i> <!-- Change icon as needed -->
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-md-4">
    <div class="small-box bg-light shadow-sm border">
      <div class="inner">
        <h3><?php echo $conn->query("SELECT * FROM student_list")->num_rows; ?></h3>
        <p>Total Students</p>
      </div>
      <div class="icon">
        <i class="fa ion-ios-people-outline"></i>
      </div>
    </div>
  </div>
</div>
