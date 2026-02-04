<?php
$date_from = isset($date_from) ? $date_from : date("Y-m-d", strtotime("-1 month"));
$date_to = isset($date_to) ? $date_to : date('Y-m-d');
?>
<style>
#viewTimeLogsPage {
  min-height: calc(100vh - 120px);
  display: flex;
  flex-direction: column;
}
#viewTimeLogsPage .card {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 0;
}
#viewTimeLogsPage .card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 0;
  overflow: hidden;
}
#viewTimeLogsPage #timeLogsTable_wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 0;
}
#viewTimeLogsPage #timeLogsTable_wrapper .dataTables_scroll {
  flex: 1;
  min-height: 200px;
  display: flex;
  flex-direction: column;
}
#viewTimeLogsPage #timeLogsTable_wrapper .dataTables_scrollBody {
  flex: 1;
  min-height: 0;
  overflow: auto !important;
}
#viewTimeLogsPage #timeLogsTable_wrapper .dataTables_scrollBody table {
  width: 100% !important;
}
</style>
<section id="viewTimeLogsPage" class="col-12">
  <!-- Custom tabs (Charts with tabs)-->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Staff Time Log Report</h3>
      <div class="card-tools">
        <form id="timeLogsFiltersForm" class="form-horizontal">
          <div class="row">
            <div class="form-group col-md-3">
              <label>Date From:</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">
                    <i class="far fa-calendar-alt"></i>
                  </span>
                </div>
                <input type="text" name="date_from" id="tl_date_from" class="form-control datepicker" value="<?php echo $date_from; ?>" autocomplete="off">
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
                <input type="text" name="date_to" id="tl_date_to" class="form-control datepicker" value="<?php echo $date_to; ?>" autocomplete="off">
              </div>
            </div>
            <div class="form-group col-md-3">
              <label for="tl_name">Name</label>
              <input class="form-control" type="text" name="name" id="tl_name" placeholder="Name">
            </div>
            <div class="form-group col-md-3">
              <label for="tl_job">Position</label>
              <select name="job" id="tl_job" class="form-control select2">
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
              <button type="button" id="tl_apply" class="btn btn-sm bg-gray-dark color-pale">
                <i class="fa fa-tasks" aria-hidden="true"></i> Apply Filters
              </button>
              <a href="#" id="tl_csv_link" target="_blank" class="btn btn-sm bg-gray-dark color-pale" style="margin-right:2px;">
                <i class="fa fa-file-excel"></i> CSV
              </a>
              <a href="#" id="tl_pdf_link" target="_blank" class="btn btn-sm bg-gray-dark color-pale">
                <i class="fa fa-file-pdf"></i> PDF
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="card-body">
      <div class="panel-title" style="font-weight:bold; font-size:16px; text-align:center;">
        ATTENDANCE LOG FOR <?php echo $_SESSION['facility_name']; ?> BETWEEN 
        <span id="tl_date_range"><?php echo $date_from; ?> AND <?php echo $date_to; ?></span>
      </div>
      <table class="table table-striped" id="timeLogsTable">
        <thead>
          <tr>
            <th>#</th>
            <th>NAME</th>
            <th>POSITION</th>
            <th style="width:20%;">DATE</th>
            <th>TIME IN</th>
            <th>TIME OUT</th>
            <th width="10%;">HOURS WORKED</th>
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
      date_from: $('#tl_date_from').val(),
      date_to: $('#tl_date_to').val(),
      name: $('#tl_name').val(),
      job: $('#tl_job').val()
    };
  }

  function updateDateRange() {
    var f = getFilters();
    $('#tl_date_range').text(f.date_from + ' AND ' + f.date_to);
  }

  function updateExportLinks() {
    var f = getFilters();
    var jobEncoded = encodeURIComponent(f.job || '').replace(/\s+/g, '_');
    var csvUrl = '<?php echo base_url(); ?>employees/attCsv/' + 
                 encodeURIComponent(f.date_from) + '/' + 
                 encodeURIComponent(f.date_to) + '/' + 
                 'person' + encodeURIComponent(f.name || '') + '/' + 
                 'position-' + jobEncoded;
    var pdfUrl = '<?php echo base_url(); ?>employees/print_timelogs/' + 
                 encodeURIComponent(f.date_from) + '/' + 
                 encodeURIComponent(f.date_to) + '/' + 
                 'person' + encodeURIComponent(f.name || '') + '/' + 
                 'position-' + jobEncoded;
    $('#tl_csv_link').attr('href', csvUrl);
    $('#tl_pdf_link').attr('href', pdfUrl);
  }

  function initTable() {
    if (table) {
      table.destroy();
    }

    table = $('#timeLogsTable').DataTable({
      processing: true,
      serverSide: true,
      pageLength: 20,
      lengthMenu: [[10, 20, 25, 50, 100, 200], [10, 20, 25, 50, 100, 200]],
      scrollX: true,
      order: [[1, 'asc'], [3, 'asc']], // Sort by name, then date
      ajax: {
        url: '<?php echo base_url("employees/viewTimeLogsAjax"); ?>',
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
          console.error('Time Logs DataTables error', { xhr: xhr, error: error, thrown: thrown, responseText: xhr.responseText });
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
  $('#timeLogsFiltersForm').on('submit', function(e) {
    e.preventDefault();
    return false;
  });

  // Apply filters button
  $('#tl_apply').on('click', function() {
    updateDateRange();
    updateExportLinks();
    table.ajax.reload();
  });

  // Initialize date range and export links
  updateDateRange();
  updateExportLinks();

  // Initialize table
  initTable();

  // Update export links when filters change
  $('#tl_date_from, #tl_date_to, #tl_name, #tl_job').on('change', function() {
    updateExportLinks();
  });
});
</script>
