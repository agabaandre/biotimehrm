<?php
$date_from = isset($date_from) ? $date_from : date("Y-m-d", strtotime("-1 month"));
$date_to = isset($date_to) ? $date_to : date('Y-m-d');
$base = base_url();
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();
$facility_name = isset($_SESSION['facility_name']) ? $_SESSION['facility_name'] : '';
?>
<style>
  /* Force header and body columns to align: fixed layout + same widths (target main table and scroll clone) */
  table.gtl-table,
  .dataTables_scrollHead table.gtl-table,
  .dataTables_scrollBody table.gtl-table,
  #groupedTimeLogsTable,
  .dataTables_scrollHead #groupedTimeLogsTable,
  .dataTables_scrollBody #groupedTimeLogsTable {
    table-layout: fixed !important;
    width: 100% !important;
  }
  #groupedTimeLogsTable thead th,
  #groupedTimeLogsTable tbody td,
  .dataTables_scrollHead table thead th,
  .dataTables_scrollBody table tbody td {
    box-sizing: border-box;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  /* Column widths — same for th and td so header/body line up (apply to any table in scroll areas) */
  .dataTables_wrapper table.gtl-table thead th:nth-child(1),
  .dataTables_wrapper table.gtl-table tbody td:nth-child(1),
  #groupedTimeLogsTable thead th:nth-child(1),
  #groupedTimeLogsTable tbody td:nth-child(1) { width: 42px !important; min-width: 42px; max-width: 42px; }
  .dataTables_wrapper table.gtl-table thead th:nth-child(2),
  .dataTables_wrapper table.gtl-table tbody td:nth-child(2),
  #groupedTimeLogsTable thead th:nth-child(2),
  #groupedTimeLogsTable tbody td:nth-child(2) { width: 18% !important; }
  .dataTables_wrapper table.gtl-table thead th:nth-child(3),
  .dataTables_wrapper table.gtl-table tbody td:nth-child(3),
  #groupedTimeLogsTable thead th:nth-child(3),
  #groupedTimeLogsTable tbody td:nth-child(3) { width: 18% !important; }
  .dataTables_wrapper table.gtl-table thead th:nth-child(4),
  .dataTables_wrapper table.gtl-table tbody td:nth-child(4),
  #groupedTimeLogsTable thead th:nth-child(4),
  #groupedTimeLogsTable tbody td:nth-child(4) { width: 15% !important; }
  .dataTables_wrapper table.gtl-table thead th:nth-child(5),
  .dataTables_wrapper table.gtl-table tbody td:nth-child(5),
  #groupedTimeLogsTable thead th:nth-child(5),
  #groupedTimeLogsTable tbody td:nth-child(5) { width: 12% !important; }
  .dataTables_wrapper table.gtl-table thead th:nth-child(6),
  .dataTables_wrapper table.gtl-table tbody td:nth-child(6),
  #groupedTimeLogsTable thead th:nth-child(6),
  #groupedTimeLogsTable tbody td:nth-child(6) { width: 12% !important; }
  .dataTables_wrapper table.gtl-table thead th:nth-child(7),
  .dataTables_wrapper table.gtl-table tbody td:nth-child(7),
  #groupedTimeLogsTable thead th:nth-child(7),
  #groupedTimeLogsTable tbody td:nth-child(7) { width: 95px !important; min-width: 95px; max-width: 95px; }
  /* Numeric columns: center-aligned */
  #groupedTimeLogsTable thead th:nth-child(1),
  #groupedTimeLogsTable tbody td:nth-child(1),
  #groupedTimeLogsTable thead th:nth-child(7),
  #groupedTimeLogsTable tbody td:nth-child(7),
  .dataTables_wrapper table.gtl-table thead th:nth-child(1),
  .dataTables_wrapper table.gtl-table tbody td:nth-child(1),
  .dataTables_wrapper table.gtl-table thead th:nth-child(7),
  .dataTables_wrapper table.gtl-table tbody td:nth-child(7) {
    text-align: center !important;
    vertical-align: middle;
  }
  /* Text columns: left-aligned */
  #groupedTimeLogsTable thead th:nth-child(2),
  #groupedTimeLogsTable thead th:nth-child(3),
  #groupedTimeLogsTable thead th:nth-child(4),
  #groupedTimeLogsTable thead th:nth-child(5),
  #groupedTimeLogsTable thead th:nth-child(6),
  #groupedTimeLogsTable tbody td:nth-child(2),
  #groupedTimeLogsTable tbody td:nth-child(3),
  #groupedTimeLogsTable tbody td:nth-child(4),
  #groupedTimeLogsTable tbody td:nth-child(5),
  #groupedTimeLogsTable tbody td:nth-child(6) { text-align: left; }
  #groupedTimeLogsTable thead th,
  .dataTables_scrollHead table thead th { white-space: nowrap; padding: 8px 6px; }
  #groupedTimeLogsTable thead th:nth-child(7),
  .dataTables_scrollHead table thead th:nth-child(7) { white-space: normal; word-wrap: break-word; line-height: 1.2; }
  #groupedTimeLogsTable tbody td,
  .dataTables_scrollBody table tbody td { padding: 6px; vertical-align: middle; }

  /* Filter row: align buttons with bottom of inputs */
  #groupedTimeLogsFiltersForm .gtl-buttons-wrap {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 8px;
    margin-top: 0;
  }
  #groupedTimeLogsFiltersForm .gtl-buttons-wrap .btn {
    margin: 0;
    white-space: nowrap;
  }
</style>
<section class="col-lg-12">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Monthly Time Log Report</h3>
        <div class="card-tools">
        <form id="groupedTimeLogsFiltersForm" class="form-horizontal">
          <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>">
          <div class="row align-items-end">
            <div class="form-group col-md-2 mb-2 mb-md-0">
              <label class="mb-1">Date From</label>
              <div class="input-group input-group-sm">
                  <div class="input-group-prepend">
                  <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                </div>
                <input type="text" name="date_from" id="gtl_date_from" class="form-control datepicker" value="<?php echo htmlspecialchars($date_from); ?>" autocomplete="off">
              </div>
            </div>
            <div class="form-group col-md-2 mb-2 mb-md-0">
              <label class="mb-1">Date To</label>
              <div class="input-group input-group-sm">
                  <div class="input-group-prepend">
                  <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                </div>
                <input type="text" name="date_to" id="gtl_date_to" class="form-control datepicker" value="<?php echo htmlspecialchars($date_to); ?>" autocomplete="off">
              </div>
            </div>
            <div class="form-group col-md-2 mb-2 mb-md-0">
              <label class="mb-1" for="gtl_name">Name</label>
              <input class="form-control form-control-sm" type="text" name="name" id="gtl_name" placeholder="Name">
              </div>
            <div class="form-group col-md-2 mb-2 mb-md-0">
              <label class="mb-1" for="gtl_job">Position</label>
              <select name="job" id="gtl_job" class="form-control form-control-sm select2">
                <option value="">All</option>
                <?php
                $jobs = Modules::run("jobs/getJobs");
                if (!empty($jobs)) {
                  foreach ($jobs as $element) {
                    echo '<option value="' . htmlspecialchars($element->job) . '">' . htmlspecialchars($element->job) . '</option>';
                  }
                }
                  ?>
                </select>
              </div>
            <div class="form-group col-md-2 mb-2 mb-md-0 gtl-buttons-wrap">
              <?php
              $qs = http_build_query(array(
                'date_from' => $date_from,
                'date_to' => $date_to,
                'name' => '',
                'job' => ''
              ));
              $print_url = $base . 'employees/print_grouped_timelogs?' . $qs;
              $csv_url = $base . 'employees/groupedTimeLogsCsv?' . $qs;
              ?>
              <button type="button" id="gtl_apply" class="btn btn-sm bg-gray-dark color-pale"><i class="fa fa-filter"></i> Apply</button>
              <a href="<?php echo htmlspecialchars($print_url); ?>" id="gtl_print_link" class="btn btn-sm bg-gray-dark color-pale" target="_blank" rel="noopener"><i class="fa fa-print"></i> Print</a>
              <a href="<?php echo htmlspecialchars($csv_url); ?>" id="gtl_csv_link" class="btn btn-sm bg-gray-dark color-pale"><i class="fa fa-file-excel"></i> CSV</a>
            </div>
              </div>
          </form>
      </div>
  </div>
  <div class="card-body">
      <p class="panel-title" style="font-weight:bold; font-size:16px; text-align:center;">
        MONTHLY ATTENDANCE LOG — <?php echo htmlspecialchars($facility_name); ?> — <span id="gtl_date_range"><?php echo htmlspecialchars($date_from); ?> to <?php echo htmlspecialchars($date_to); ?></span>
      </p>
      <div class="table-responsive" style="margin-top: 10px;">
        <table class="table table-striped table-bordered gtl-table" id="groupedTimeLogsTable" style="width:100%;">
          <colgroup>
            <col style="width: 42px;">
            <col style="width: 18%;">
            <col style="width: 18%;">
            <col style="width: 15%;">
            <col style="width: 12%;">
            <col style="width: 12%;">
            <col style="width: 95px;">
          </colgroup>
      <thead>
        <tr>
          <th>#</th>
              <th>Name</th>
              <th>Position</th>
              <th>Facility</th>
              <th>Department</th>
              <th>Date</th>
              <th>Hours Worked</th>
        </tr>
      </thead>
          <tbody></tbody>
    </table>
      </div>
    </div>
  </div>
</section>

<script type="text/javascript">
(function() {
  var baseUrl = '<?php echo addslashes($base); ?>';
  var csrfTokenName = '<?php echo addslashes($csrf_name); ?>';
  var csrfTokenHash = '<?php echo addslashes($csrf_hash); ?>';
  var defaultDateFrom = '<?php echo addslashes($date_from); ?>';
  var defaultDateTo = '<?php echo addslashes($date_to); ?>';

  function getFilters() {
    return {
      date_from: $('#gtl_date_from').val() || defaultDateFrom,
      date_to: $('#gtl_date_to').val() || defaultDateTo,
      name: $('#gtl_name').val() || '',
      job: $('#gtl_job').val() || ''
    };
  }

  function buildQueryString(f) {
    var params = {
      date_from: f.date_from,
      date_to: f.date_to,
      name: f.name,
      job: f.job
    };
    return $.param(params);
  }

  function updateExportLinks() {
    var f = getFilters();
    var qs = buildQueryString(f);
    $('#gtl_print_link').attr('href', baseUrl + 'employees/print_grouped_timelogs?' + qs);
    $('#gtl_csv_link').attr('href', baseUrl + 'employees/groupedTimeLogsCsv?' + qs);
    $('#gtl_date_range').text(f.date_from + ' to ' + f.date_to);
  }

  $(document).ready(function() {
    if (typeof $.fn.DataTable !== 'function') {
      console.error('DataTables not loaded.');
      return;
    }

    var table = $('#groupedTimeLogsTable').DataTable({
      processing: true,
      serverSide: true,
      searching: true,
      ordering: true,
      order: [[1, 'asc']],
      pageLength: 20,
      lengthMenu: [[10, 20, 25, 50, 100, 200], [10, 20, 25, 50, 100, 200]],
      scrollX: false,
      autoWidth: false,
      ajax: {
        url: baseUrl + 'employees/groupedTimeLogsAjax',
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
          console.error('Grouped Time Logs DataTables error', { xhr: xhr, error: error, thrown: thrown });
        }
      },
      columns: [
        { data: 0, orderable: false, className: 'gtl-num-col', width: '42px' },
        { data: 1, width: '18%' },
        { data: 2, width: '18%' },
        { data: 3, width: '15%' },
        { data: 4, width: '12%' },
        { data: 5, width: '12%' },
        { data: 6, className: 'gtl-num-col', width: '95px' }
      ],
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
           '<"row"<"col-sm-12"tr>>' +
           '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      language: { processing: '<i class="fa fa-spinner fa-spin"></i> Loading...' },
      initComplete: function() {
        $('.dataTables_scrollHead table').addClass('gtl-table');
      }
    });

    $('#groupedTimeLogsFiltersForm').on('submit', function(e) {
      e.preventDefault();
      return false;
    });

    $('#gtl_apply').on('click', function() {
      updateExportLinks();
      table.ajax.reload();
    });

    $('#gtl_print_link, #gtl_csv_link').on('click', function(e) {
      updateExportLinks();
      var href = $(this).attr('href');
      if (!href || href === '#') return;
      e.preventDefault();
      if ($(this).attr('id') === 'gtl_print_link') {
        window.open(href, '_blank', 'noopener');
      } else {
        window.location.href = href;
      }
    });

    $('#gtl_date_from, #gtl_date_to, #gtl_name, #gtl_job').on('change', function() {
      updateExportLinks();
    });

    updateExportLinks();
  });
})();
</script>
