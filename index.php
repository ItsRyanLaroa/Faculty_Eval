<!DOCTYPE html>
<html lang="en">
<?php 

if (!isset($_SESSION['login_id'])) {
    header('location:homepage.php');
    exit; // Ensure no further code is executed after redirect
}

include 'db_connect.php'; // Include your database connection
ob_start(); // Start output buffering

// Initialize system settings if not already set
if (!isset($_SESSION['system'])) {
    $query = $conn->query("SELECT * FROM system_settings");
    if ($query && $query->num_rows > 0) {
        $system = $query->fetch_array();
        foreach ($system as $k => $v) {
            $_SESSION['system'][$k] = $v;
        }
    } else {
        // Handle case where no system settings are found
        $_SESSION['system'] = []; // or set default values
    }
}
ob_end_flush();
?>
<style>
  .border-primary {
    border-color: #dc143c !important; 
  }
</style>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
  <?php include 'topbar.php'; ?>
  <?php include $_SESSION['login_view_folder'].'sidebar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <div class="toast" id="alert_toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body text-white"></div>
    </div>
    <div id="toastsContainerTopRight" class="toasts-top-right fixed"></div>
    
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"><?php echo isset($title) ? $title : 'Default Title'; ?></h1>
          </div><!-- /.col -->
        </div><!-- /.row -->
        <hr class="border-primary">
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
         <?php 
            $page = isset($_GET['page']) ? $_GET['page'] : 'home';
            $pagePath = $_SESSION['login_view_folder'] . $page . ".php";
            if (!file_exists($pagePath)) {
                include '404.html'; // Custom 404 error page
            } else {
                include $pagePath; // Load the requested page
            }
         ?>
      </div><!--/. container-fluid -->
    </section>
    <!-- /.content -->

    <!-- Modals -->
    <div class="modal fade" id="confirm_modal" role='dialog'>
      <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmation</h5>
          </div>
          <div class="modal-body">
            <div id="delete_content"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" id='confirm' onclick="">Continue</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="uni_modal" role='dialog'>
      <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"></h5>
          </div>
          <div class="modal-body"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="uni_modal_right" role='dialog'>
      <div class="modal-dialog modal-full-height modal-md" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span class="fa fa-arrow-right"></span>
            </button>
          </div>
          <div class="modal-body"></div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="viewer_modal" role='dialog'>
      <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
          <button type="button" class="btn-close" data-dismiss="modal"><span class="fa fa-times"></span></button>
          <img src="" alt="">
        </div>
      </div>
    </div>
  </div>
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer">
    <div class="float-right d-none d-sm-inline-block">
      <span style="color: black;"><b><?php echo $_SESSION['system']['name'] ?? 'System Name'; ?></b></span>
    </div>
  </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<!-- Bootstrap -->
<?php include 'footer.php'; ?>
</body>
</html>
