<?php
$date_from = isset($date_from) ? $date_from : date("Y-m-d", strtotime("-1 month"));
$date_to = isset($date_to) ? $date_to : date('Y-m-d');
?>
<section class="col-lg-12">
  <!-- Custom tabs (Charts with tabs)-->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Monthly Time Log Report</h3>
      <div class="card-tools">
        <form id="groupedTimeLogsFiltersForm" class="form-horizontal">
          <div class="row">
            <div class="form-group col-md-3">
              <label>Date From:</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">
                    <i class="far fa-calendar-alt"></i>
                  </span>
                </div>
                <input type="text" name="date_from" id="gtl_date_from" class="form-control datepicker" value="<?php echo $date_from; ?>" autocomplete="off">
              </div>
            </div>
            <div class="form-group col-md-3">
              <label>Date To:</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">
                    <i class="far fa-calendar-alt"></i>
                  </span>
                </div>
                <input type="text" name="date_to" id="gtl_date_to" class="form-control datepicker" value="<?php echo $date_to; ?>" autocomplete="off">
              </div>
            </div>
            <div class="form-group col-md-3">
              <label for="gtl_name">Name</label>
              <input class="form-control" type="text" name="name" id="gtl_name" placeholder="Name">
            </div>
            <div class="form-group col-md-3">
              <label for="gtl_job">Position</label>
              <select name="job" id="gtl_job" class="form-control select2">
                <option value="">ALL</option>
                <?php 
                $jobs = Modules::run("jobs/getJobs");
                foreach ($jobs as $element) {
                ?>
                  <option value="<?php echo $element->job; ?>"><?php echo $element->job; ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="form-group col-md-12">
              <button type="button" id="gtl_apply" class="btn btn-sm bg-gray-dark color-pale">
                <i class="fa fa-tasks" aria-hidden="true"></i> Apply Filters
              </button>
              <a href="#" id="gtl_csv_link" target="_blank" class="btn btn-sm bg-gray-dark color-pale">
                <i class="fa fa-file-excel"></i> CSV
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="card-body">
      <p class="panel-title" style="font-weight:bold; font-size:16px; text-align:center;">
        MONTHLY ATTENDANCE LOG FOR <?php echo $_SESSION['facility_name']; ?> BETWEEN 
        <span id="gtl_date_range"><?php echo $date_from; ?> AND <?php echo $date_to; ?></span>
      </p>
      <table class="table table-striped" id="groupedTimeLogsTable">
        <thead>
          <tr>
            <th>#</th>
            <th>NAME</th>
            <th>POSITION</th>
            <th>FACILITY</th>
            <th>DEPARTMENT</th>
            <th>DATE</th>
            <th width="30%">HOURS WORKED</th>
          </tr>
        </thead>
        <tbody>
          <!-- DataTables will populate this -->
        </tbody>
      </table>
    </div><!-- /.card-body -->
  </div>
  <!-- /.card -->
</section>

<script type="text/javascript">
$(document).ready(function() {
  var table;
  var csrfTokenName = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrfTokenHash = '<?php echo $this->security->get_csrf_hash(); ?>';

  function getFilters() {
    return {
      date_from: $('#gtl_date_from').val(),
      date_to: $('#gtl_date_to').val(),
      name: $('#gtl_name').val(),
      job: $('#gtl_job').val()
    };
  }

  function updateDateRange() {
    var f = getFilters();
    $('#gtl_date_range').text(f.date_from + ' AND ' + f.date_to);
  }

  function updateCsvLink() {
    var f = getFilters();
    var csvUrl = '<?php echo base_url(); ?>employees/attCsv/' + 
                 encodeURIComponent(f.date_from) + '/' + 
                 encodeURIComponent(f.date_to) + '/' + 
                 'person' + encodeURIComponent(f.name || '') + '/' + 
                 'position-' + encodeURIComponent(f.job || '');
    $('#gtl_csv_link').attr('href', csvUrl);
  }

  function initTable() {
    if (table) {
      table.destroy();
    }

    table = $('#groupedTimeLogsTable').DataTable({
      processing: true,
      serverSide: true,
      pageLength: 20,
      lengthMenu: [[10, 20, 25, 50, 100, 200], [10, 20, 25, 50, 100, 200]],
      scrollX: true,
      order: [[1, 'asc']], // Sort by name
      ajax: {
        url: '<?php echo base_url("employees/groupedTimeLogsAjax"); ?>',
        type: 'POST',
        data: function(d) {
          var f = getFilters();
          d[csrfTokenName] = csrfTokenHash;
          d.date_from = f.date_from;
          d.date_to = f.date_to;
          d.name = f.name;
          d.job = f.job;
        },
        error: function(xhr, error, thrown) {
          console.error('Grouped Time Logs DataTables error', { xhr: xhr, error: error, thrown: thrown, responseText: xhr.responseText });
        }
      },
      columns: [
        { data: 0, orderable: false },
        { data: 1 },
        { data: 2 },
        { data: 3 },
        { data: 4 },
        { data: 5 },
        { data: 6 }
      ],
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
           '<"row"<"col-sm-12"tr>>' +
           '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      language: {
        processing: '<i class="fa fa-spinner fa-spin"></i> Loading...'
      }
    });
  }

  // Prevent form submission
  $('#groupedTimeLogsFiltersForm').on('submit', function(e) {
    e.preventDefault();
    return false;
  });

  // Apply filters button
  $('#gtl_apply').on('click', function() {
    updateDateRange();
    updateCsvLink();
    table.ajax.reload();
  });

  // Initialize date range and CSV link
  updateDateRange();
  updateCsvLink();

  // Initialize table
  initTable();

  // Update CSV link when filters change
  $('#gtl_date_from, #gtl_date_to, #gtl_name, #gtl_job').on('change', function() {
    updateCsvLink();
  });
});
</script>
