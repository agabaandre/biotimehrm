<?php

$userdata = $this->session->get_userdata();
if (!isset($userdata['names'])) {
  redirect('auth');
}
$permissions = $userdata['permissions'];

date_default_timezone_set('Africa/Kampala');
require_once("includes/header.php");
require_once("includes/navtop.php");
require_once("includes/sidenav.php");
//db connection
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <!-- <h1>Fixed Layout</h1> -->
        </div>
      </div>
    </div>
  </section>
  <!-- /.container-fluid -->
  <!-- Main content -->
  <div id="preloader">
    <div id="status">
    </div>
  </div>
  <section class="content">
    <div class="container-fluid" style="font-size:12px; min-height:940px; margin-top:40px;">
      <div class="row">
        <div class="col-12" style="margin-bottom:3px;">

          <!-- Nav -->
          <section class="content-header">
            <div class="container-fluid">
              <div class="row mb-2">
                <div class="col-sm-6">
                </div>
                <div class="col-sm-6">
                  <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?php echo base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item active"><?php echo isset($uptitle) ? $uptitle : 'Page' ?></li>
                  </ol>
                </div>
              </div>
            </div>
          </section>

          <?php
              $page_child_data = array();
              if (isset($view)) $page_child_data['view'] = $view;
              if (isset($module)) $page_child_data['module'] = $module;
              if (isset($uptitle)) $page_child_data['uptitle'] = $uptitle;
              if (isset($title)) $page_child_data['title'] = $title;
              if (isset($can_mark_disabled)) {
                  $page_child_data['can_mark_disabled'] = $can_mark_disabled;
              } else {
                  $page_child_data['can_mark_disabled'] = (is_array($permissions) && (in_array('15', $permissions) || in_array(15, $permissions)));
              }
              if (isset($filter_options)) {
                  $page_child_data['filter_options'] = $filter_options;
              }
              if (isset($setting)) $page_child_data['setting'] = $setting;
              if (isset($districts)) $page_child_data['districts'] = $districts;
              if (isset($import_template_headers)) $page_child_data['import_template_headers'] = $import_template_headers;
              if (isset($facilities)) $page_child_data['facilities'] = $facilities;
              if (isset($facilities_json)) $page_child_data['facilities_json'] = $facilities_json;
              if (isset($jobs)) $page_child_data['jobs'] = $jobs;
              if (isset($jobs_json)) $page_child_data['jobs_json'] = $jobs_json;
              if (isset($cadres)) $page_child_data['cadres'] = $cadres;
              $page_child_data['permissions'] = is_array($permissions) ? $permissions : [];
              $this->load->view($module . "/" . $view, $page_child_data);
          ?>

        </div> 


        </div>

      </div>
    </div>
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php
require_once("includes/footer.php");
if (!empty($module) && isset($view) && $view === 'staff_all_ihris') {
    $this->load->view($module . '/staff_all_ihris_scripts', isset($page_child_data) ? $page_child_data : array());
}
?>