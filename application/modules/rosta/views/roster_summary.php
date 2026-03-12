<style>
	.cnumber { width: 3%; }
	.cname { text-align: left; padding-left: 1.5em; width: 30%; }
	@media only screen and (max-width: 980px) {
		.cnumber { width: 100%; }
		.cname { padding-left: 0; text-align: left; width: 100%; }
		.print { display: none; }
	}
	#rosterSummaryTable thead th:not(.rs-num-col) { white-space: nowrap; }
	#rosterSummaryTable th.rs-num-col,
	#rosterSummaryTable td.rs-num-col {
		width: 56px;
		min-width: 56px;
		max-width: 56px;
		text-align: center;
		box-sizing: border-box;
	}
	#rosterSummaryTable thead th.rs-num-col {
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
					<form id="rosterSummaryFiltersForm" class="form-horizontal" style="padding-bottom: 2em;" action="<?php echo base_url(); ?>rosta/summary" method="post">
						<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
						<div class="row">
							<div class="col-md-2">
								<div class="control-group">
									<label class="control-label">Month</label>
									<select class="form-control select2" name="month" id="rs_month">
										<?php for ($m = 1; $m <= 12; $m++) {
											$v = sprintf('%02d', $m);
											$sel = (isset($month) && $month == $v) ? ' selected' : '';
											echo '<option value="' . $v . '"' . $sel . '>' . strtoupper(date('F', mktime(0, 0, 0, $m, 10))) . '</option>';
										} ?>
									</select>
								</div>
							</div>
							<div class="col-md-2">
								<div class="control-group">
									<label class="control-label">Year</label>
									<select class="form-control select2" name="year" id="rs_year">
										<?php for ($i = -5; $i <= 25; $i++) {
											$y = 2017 + $i;
											$sel = (isset($year) && $year == $y) ? ' selected' : '';
											echo '<option value="' . $y . '"' . $sel . '>' . $y . '</option>';
										} ?>
									</select>
								</div>
							</div>
							<div class="col-md-4">
								<div class="control-group">
									<label class="control-label">Employee</label>
									<?php $employees = Modules::run("employees/get_employees"); ?>
									<select class="form-control select2" name="empid" id="rs_empid">
										<option value="">All</option>
										<?php if (!empty($employees)) foreach ($employees as $employee) : ?>
											<option value="<?php echo htmlspecialchars($employee->ihris_pid); ?>"
												<?php echo (isset($empid) && $empid === $employee->ihris_pid) ? ' selected' : ''; ?>>
												<?php echo htmlspecialchars($employee->surname . ' ' . $employee->firstname . ' ' . $employee->othername); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							<div class="col-md-4">
								<div class="control-group" style="margin-top: 1.8em;">
									<button type="button" id="rs_apply" class="btn bg-gray-dark color-pale" style="font-size:12px;"><i class="fa fa-filter"></i> Apply</button>
									<a href="#" id="rs_print_link" style="font-size:12px; margin-left:8px;" class="btn bg-gray-dark color-pale print" target="_blank" rel="noopener"><i class="fa fa-print"></i> Print</a>
									<a href="#" id="rs_csv_link" style="font-size:12px; margin-left:8px;" class="btn bg-gray-dark color-pale"><i class="fa fa-file"></i> Export CSV</a>
								</div>
							</div>
						</div>
					</form>
				</div>

				<div class="panel-body">
					<div class="col-md-12" style="border: 0;">
						<p id="roster_summary_title" style="font-size: 16px; font-weight:bold; margin:0 auto;">
							DUTY ROSTER SUMMARY - <span id="rs_period_label"><?php echo isset($month) && isset($year) ? date('F, Y', strtotime($year . '-' . $month . '-01')) : date('F, Y'); ?></span>
						</p>
					</div>
					<div class="table-responsive" style="margin-top: 10px;">
						<table id="rosterSummaryTable" class="table table-striped table-bordered" style="width:100%;">
							<thead>
								<tr>
									<th>#</th>
									<th>Name</th>
									<th>Job</th>
									<th>Day</th>
									<th>Evening</th>
									<th>Night</th>
									<th>Off</th>
									<th>Annual</th>
									<th>Study</th>
									<th>Maternity</th>
									<th>Other</th>
									<th>Total</th>
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
	var month = '<?php echo isset($month) ? addslashes($month) : date("m"); ?>';
	var year = '<?php echo isset($year) ? addslashes($year) : date("Y"); ?>';

	function updateExportLinks() {
		var m = $('#rs_month').val() || month;
		var y = $('#rs_year').val() || year;
		var e = $('#rs_empid').val() || '';
		var range = y + '-' + m;
		var qs = e ? '?empid=' + encodeURIComponent(e) : '';
		$('#rs_print_link').attr('href', baseUrl + 'rosta/print_summary/' + range + qs);
		$('#rs_csv_link').attr('href', baseUrl + 'rosta/bundleCsv/' + range + qs);
		$('#rs_period_label').text(new Date(y + '-' + m + '-01').toLocaleString('en-US', { month: 'long', year: 'numeric' }));
	}

	$(document).ready(function() {
		if (typeof $.fn.DataTable !== 'function') {
			console.error('DataTables not loaded.');
			return;
		}
		var rsTable = $('#rosterSummaryTable').DataTable({
			processing: true,
			serverSide: true,
			searching: true,
			ordering: true,
			order: [[1, 'asc']],
			pageLength: 30,
			lengthMenu: [[15, 30, 50, 100, 200], [15, 30, 50, 100, 200]],
			autoWidth: false,
			ajax: {
				url: baseUrl + 'rosta/summary_ajax',
				type: 'POST',
				data: function(d) {
					d.month = $('#rs_month').val() || month;
					d.year = $('#rs_year').val() || year;
					d.empid = $('#rs_empid').val() || '';
					d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
				}
			},
			columns: [
				{ data: 0, orderable: false, width: '40px' },
				{ data: 1, className: 'cname' },
				{ data: 2, className: 'cname' },
				{ data: 3, className: 'rs-num-col', width: '56px' },
				{ data: 4, className: 'rs-num-col', width: '56px' },
				{ data: 5, className: 'rs-num-col', width: '56px' },
				{ data: 6, className: 'rs-num-col', width: '56px' },
				{ data: 7, className: 'rs-num-col', width: '56px' },
				{ data: 8, className: 'rs-num-col', width: '56px' },
				{ data: 9, className: 'rs-num-col', width: '56px' },
				{ data: 10, className: 'rs-num-col', width: '56px' },
				{ data: 11, className: 'rs-num-col', width: '56px' }
			]
		});

		$('#rs_apply').on('click', function() {
			updateExportLinks();
			rsTable.ajax.reload();
		});

		$('#rs_print_link, #rs_csv_link').on('click', function(e) {
			updateExportLinks();
			var href = $(this).attr('href');
			if (!href || href === '#') return;
			e.preventDefault();
			if ($(this).attr('id') === 'rs_print_link') {
				window.open(href, '_blank', 'noopener');
			} else {
				window.location.href = href;
			}
		});

		updateExportLinks();
	});
})();
</script>
