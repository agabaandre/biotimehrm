<style>
	.cnumber { width: 3%; }
	.cname { text-align: left; padding-left: 1.5em; width: 30%; }
	@media only screen and (max-width: 980px) {
		.cnumber { width: 100%; }
		.cname { padding-left: 0; text-align: left; width: 100%; }
		.print { display: none; }
	}
	#attendanceSummaryTable thead th:not(.ats-num-col) { white-space: nowrap; }
	/* Uniform width for numeric/summary columns (Off Duty through % Present) */
	#attendanceSummaryTable th.ats-num-col,
	#attendanceSummaryTable td.ats-num-col {
		width: 72px;
		min-width: 72px;
		max-width: 72px;
		text-align: center;
		box-sizing: border-box;
	}
	/* Allow header text to wrap in numeric columns (Official Request, Total Days Expected, etc.) */
	#attendanceSummaryTable thead th.ats-num-col {
		white-space: normal;
		word-wrap: break-word;
		line-height: 1.2;
		padding: 6px 4px;
	}
</style>
<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="callout callout-success">
					<form id="attendanceSummaryFiltersForm" class="form-horizontal" style="padding-bottom: 2em;" action="<?php echo base_url(); ?>attendance/attendance_summary" method="get">
						<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
						<div class="row">
							<div class="col-md-2">
								<div class="control-group">
									<label class="control-label">Month</label>
									<select class="form-control select2" name="month" id="ats_month">
										<option value="01" <?php echo ($month == '01') ? 'selected' : ''; ?>>JANUARY</option>
										<option value="02" <?php echo ($month == '02') ? 'selected' : ''; ?>>FEBRUARY</option>
										<option value="03" <?php echo ($month == '03') ? 'selected' : ''; ?>>MARCH</option>
										<option value="04" <?php echo ($month == '04') ? 'selected' : ''; ?>>APRIL</option>
										<option value="05" <?php echo ($month == '05') ? 'selected' : ''; ?>>MAY</option>
										<option value="06" <?php echo ($month == '06') ? 'selected' : ''; ?>>JUNE</option>
										<option value="07" <?php echo ($month == '07') ? 'selected' : ''; ?>>JULY</option>
										<option value="08" <?php echo ($month == '08') ? 'selected' : ''; ?>>AUGUST</option>
										<option value="09" <?php echo ($month == '09') ? 'selected' : ''; ?>>SEPTEMBER</option>
										<option value="10" <?php echo ($month == '10') ? 'selected' : ''; ?>>OCTOBER</option>
										<option value="11" <?php echo ($month == '11') ? 'selected' : ''; ?>>NOVEMBER</option>
										<option value="12" <?php echo ($month == '12') ? 'selected' : ''; ?>>DECEMBER</option>
									</select>
								</div>
							</div>
							<div class="col-md-2">
								<div class="control-group">
									<label class="control-label">Year</label>
									<select class="form-control select2" name="year" id="ats_year">
										<?php
										$cy = (int) date('Y');
										for ($i = -5; $i <= 25; $i++) {
											$y = 2017 + $i;
											$sel = ($year == $y) ? ' selected' : '';
											echo '<option value="' . $y . '"' . $sel . '>' . $y . '</option>';
										}
										?>
									</select>
								</div>
							</div>
							<div class="col-md-2">
								<div class="control-group">
									<label class="control-label">Employee</label>
									<?php $employees = Modules::run("employees/get_employees"); ?>
									<select class="form-control select2" name="empid" id="ats_empid">
										<option value="">All</option>
										<?php foreach ($employees as $employee) {
											$sel = (isset($empid) && $empid === $employee->ihris_pid) ? ' selected' : '';
										?>
											<option value="<?php echo htmlspecialchars($employee->ihris_pid); ?>"<?php echo $sel; ?>><?php echo htmlspecialchars($employee->surname . ' ' . $employee->firstname . ' ' . $employee->othername); ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-md-2">
								<div class="control-group">
									<label class="control-label">Department</label>
									<select class="form-control select2" name="department" id="ats_department">
										<option value="">All</option>
										<?php
										$dopts = Modules::run("departments/departments");
										echo preg_replace('/<option value=\'\'>Select Department<\\/option>/i', '', $dopts);
										?>
									</select>
								</div>
							</div>
							<div class="col-md-4">
								<div class="control-group" style="margin-top: 1.8em;">
									<button type="button" id="ats_apply" class="btn bg-gray-dark color-pale" style="font-size:12px;"><i class="fa fa-filter"></i> Apply</button>
									<?php
									$range = $year . '-' . $month;
									$qs = (isset($empid) && $empid !== '' || isset($department) && $department !== '') ? '?' . (isset($empid) && $empid !== '' ? 'empid=' . rawurlencode($empid) : '') . (isset($department) && $department !== '' ? (isset($empid) && $empid !== '' ? '&' : '') . 'department=' . rawurlencode($department) : '') : '';
									$print_url = base_url() . 'attendance/print_attsummary/' . $range . $qs;
									$csv_url = base_url() . 'attendance/attsums_csv/' . $range . '/' . $year . '/' . $month . $qs;
									?>
									<a href="<?php echo htmlspecialchars($print_url); ?>" id="ats_print_link" style="font-size:12px; margin-left:8px;" class="btn bg-gray-dark color-pale print" target="_blank" rel="noopener"><i class="fa fa-print"></i> Print</a>
									<a href="<?php echo htmlspecialchars($csv_url); ?>" id="ats_csv_link" style="font-size:12px; margin-left:8px;" class="btn bg-gray-dark color-pale" download><i class="fa fa-file"></i> Export CSV</a>
								</div>
							</div>
						</div>
					</form>
		</div>

		<div class="panel-body">
					<div class="col-md-12" style="border: 0;">
						<p id="attendance_summary_title" style="font-size: 16px; font-weight:bold; margin:0 auto;">
							MONTHLY ATTENDANCE TO DUTY SUMMARY - <?php echo htmlspecialchars(isset($_SESSION['facility_name']) ? $_SESSION['facility_name'] : ''); ?> <span id="ats_period_label"><?php echo date('F, Y', strtotime($year . '-' . $month . '-01')); ?></span>
				</p>
			</div>
					<div class="table-responsive" style="margin-top: 10px;">
						<table id="attendanceSummaryTable" class="table table-striped table-bordered" style="width:100%;">
							<thead>
								<tr>
									<th>#</th>
									<th>Name</th>
									<th>Job</th>
									<th>Department</th>
									<th>Off Duty</th>
									<th>Official Request</th>
									<th>Leave</th>
									<th>Holiday</th>
									<th>Total Days Expected</th>
									<th>Total Days Worked</th>
									<th>Total Days Absent</th>
									<th>% Present</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
	</div>
	</div>
	</div>
</section>

<script type="text/javascript">
(function() {
	var baseUrl = '<?php echo base_url(); ?>';
	var month = '<?php echo $month; ?>';
	var year = '<?php echo $year; ?>';
	var empid = '<?php echo isset($empid) ? addslashes($empid) : ''; ?>';
	var department = '<?php echo isset($department) ? addslashes($department) : ''; ?>';

	function updateExportLinks() {
		var m = $('#ats_month').val() || month;
		var y = $('#ats_year').val() || year;
		var e = $('#ats_empid').val() || '';
		var d = $('#ats_department').val() || '';
		var range = y + '-' + m;
		var qs = (e || d) ? '?' + (e ? 'empid=' + encodeURIComponent(e) : '') + (d ? (e ? '&' : '') + 'department=' + encodeURIComponent(d) : '') : '';
		$('#ats_print_link').attr('href', baseUrl + 'attendance/print_attsummary/' + range + qs);
		$('#ats_csv_link').attr('href', baseUrl + 'attendance/attsums_csv/' + range + '/' + y + '/' + m + qs);
		$('#ats_period_label').text(new Date(y + '-' + m + '-01').toLocaleString('en-US', { month: 'long', year: 'numeric' }));
	}

	// Run after footer (and DataTables) is loaded — same approach as rosta/tabular
	$(document).ready(function() {
		if (typeof $.fn.DataTable !== 'function') {
			console.error('DataTables not loaded. Ensure footer scripts load before this view.');
			return;
		}
		var atsTable = $('#attendanceSummaryTable').DataTable({
			processing: true,
			serverSide: true,
			searching: true,
			ordering: true,
			order: [[1, 'asc']],
			pageLength: 30,
			lengthMenu: [[15, 30, 50, 100, 200], [15, 30, 50, 100, 200]],
			autoWidth: false,
			ajax: {
				url: baseUrl + 'attendance/attendanceSummaryAjax',
				type: 'POST',
				data: function(d) {
					d.month = $('#ats_month').val() || month;
					d.year = $('#ats_year').val() || year;
					d.empid = $('#ats_empid').val() || '';
					d.department = $('#ats_department').val() || '';
					d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
				}
			},
			columns: [
				{ data: 0, orderable: false, width: '40px' },
				{ data: 1, className: 'cname' },
				{ data: 2, className: 'cname' },
				{ data: 3, className: 'cname' },
				{ data: 4, className: 'ats-num-col', width: '72px' },
				{ data: 5, className: 'ats-num-col', width: '72px' },
				{ data: 6, className: 'ats-num-col', width: '72px' },
				{ data: 7, className: 'ats-num-col', width: '72px' },
				{ data: 8, className: 'ats-num-col', width: '72px' },
				{ data: 9, className: 'ats-num-col', width: '72px' },
				{ data: 10, className: 'ats-num-col', width: '72px' },
				{ data: 11, className: 'ats-num-col', width: '72px' }
			]
		});

		$('#ats_apply').on('click', function() {
			updateExportLinks();
			atsTable.ajax.reload();
		});

		$('#ats_print_link, #ats_csv_link').on('click', function(e) {
			updateExportLinks();
			var href = $(this).attr('href');
			if (!href || href === '#') return;
		e.preventDefault();
			if ($(this).attr('id') === 'ats_print_link') {
				window.open(href, '_blank', 'noopener');
			} else {
				window.location.href = href;
			}
		});

		if (department) {
			$('#ats_department').val(department);
		}
		updateExportLinks();
	});
})();
</script>
