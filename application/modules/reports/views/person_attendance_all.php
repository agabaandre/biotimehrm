<style>
	.cnumber { width: 3%; }
	.cname { text-align: left; padding-left: 1.5em; width: 30%; }
@media only screen and (max-width: 980px) {
		.cnumber { width: 100%; }
		.cname { padding-left: 0; text-align: left; width: 100%; }
		.print { display: none; }
	}
	#personAttendanceAllTable thead th:not(.paa-num-col) { white-space: nowrap; }
	#personAttendanceAllTable th.paa-num-col,
	#personAttendanceAllTable td.paa-num-col {
		width: 72px;
		min-width: 72px;
		max-width: 72px;
		text-align: center;
		box-sizing: border-box;
	}
	#personAttendanceAllTable thead th.paa-num-col {
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
					<form id="personAttendanceAllFiltersForm" class="form-horizontal" style="padding-bottom: 2em;" action="<?php echo base_url(); ?>reports/person_attendance_all" method="get">
						<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="control-group">
									<label class="control-label">Month</label>
									<select class="form-control select2" name="month" id="paa_month">
										<?php for ($m = 1; $m <= 12; $m++) {
											$v = sprintf('%02d', $m);
											$sel = (isset($search->month) && $search->month == $v) ? ' selected' : '';
											echo '<option value="' . $v . '"' . $sel . '>' . strtoupper(date('F', strtotime("2022-$v-01"))) . '</option>';
										} ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="control-group">
									<label class="control-label">Year</label>
									<select class="form-control select2" name="year" id="paa_year">
										<?php for ($i = -3; $i <= 25; $i++) {
											$y = 2017 + $i;
											$sel = (isset($search->year) && $search->year == $y) ? ' selected' : '';
											echo '<option value="' . $y . '"' . $sel . '>' . $y . '</option>';
										} ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="control-group">
									<label class="control-label">Facility</label>
									<select class="form-control select2" name="facility_name" id="paa_facility_name">
                                        <option value="">All</option>
										<?php if (!empty($facilities)) foreach ($facilities as $value) : ?>
											<option value="<?php echo htmlspecialchars($value->facility); ?>"
												<?php echo (isset($search->facility_name) && $search->facility_name == $value->facility) ? ' selected' : ''; ?>>
												<?php echo htmlspecialchars($value->facility); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
							<div class="col-md-3">
                                <div class="control-group">
									<label class="control-label">District</label>
									<select class="form-control select2" name="district" id="paa_district">
                                        <option value="">All</option>
										<?php if (!empty($districts)) foreach ($districts as $value) : ?>
											<option value="<?php echo htmlspecialchars($value->district); ?>"
												<?php echo (isset($search->district) && $search->district == $value->district) ? ' selected' : ''; ?>>
												<?php echo htmlspecialchars($value->district); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
							<div class="col-md-2">
								<div class="control-group" style="margin-top: 1.8em;">
									<button type="button" id="paa_apply" class="btn bg-gray-dark color-pale" style="font-size:12px;"><i class="fa fa-filter"></i> Apply</button>
                        </div>
                            </div>
                                </div>
						<div class="row mt-2">
							<div class="col-md-12">
								<a href="#" id="paa_csv_link" style="font-size:12px; margin-right:8px;" class="btn bg-gray-dark color-pale"><i class="fa fa-file"></i> Export CSV</a>
								<a href="#" id="paa_pdf_link" style="font-size:12px;" class="btn bg-gray-dark color-pale print" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> Export PDF</a>
                        </div>
                </div>
                </form>
        </div>

        <div class="panel-body">
					<div class="col-md-12" style="border: 0;">
						<p id="paa_title" style="font-size: 16px; font-weight:bold; margin:0 auto;">
							MONTHLY ATTENDANCE TO DUTY SUMMARY - <span id="paa_period_label"><?php echo isset($period) ? date('F, Y', strtotime($period . '-01')) : date('F, Y'); ?></span>
                </p>
            </div>
					<div class="table-responsive" style="margin-top: 10px;">
						<table id="personAttendanceAllTable" class="table table-striped table-bordered" style="width:100%;">
							<thead>
								<tr>
									<th>#</th>
									<th>Name</th>
									<th>District</th>
									<th>Facility</th>
									<th>Period</th>
									<th>Present</th>
									<th>Off Duty</th>
									<th>Official Request</th>
									<th>Leave</th>
									<th>Holiday</th>
									<th>Absent</th>
									<th>% Absenteeism</th>
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
		var m = $('#paa_month').val() || month;
		var y = $('#paa_year').val() || year;
		var d = $('#paa_district').val() || '';
		var f = $('#paa_facility_name').val() || '';
		var q = [];
		q.push('month=' + encodeURIComponent(m));
		q.push('year=' + encodeURIComponent(y));
		if (d) q.push('district=' + encodeURIComponent(d));
		if (f) q.push('facility_name=' + encodeURIComponent(f));
		var qs = q.join('&');
		$('#paa_csv_link').attr('href', baseUrl + 'reports/person_attendance_all?' + qs + '&csv=1');
		$('#paa_pdf_link').attr('href', baseUrl + 'reports/person_attendance_all?' + qs + '&pdf=1');
		$('#paa_period_label').text(new Date(y + '-' + m + '-01').toLocaleString('en-US', { month: 'long', year: 'numeric' }));
	}

	$(document).ready(function() {
		if (typeof $.fn.DataTable !== 'function') {
			console.error('DataTables not loaded.');
			return;
		}
		var paaTable = $('#personAttendanceAllTable').DataTable({
			processing: true,
			serverSide: true,
			searching: true,
			ordering: true,
			order: [[2, 'asc'], [3, 'asc'], [1, 'asc']],
			pageLength: 30,
			lengthMenu: [[15, 30, 50, 100, 200], [15, 30, 50, 100, 200]],
			autoWidth: false,
			ajax: {
				url: baseUrl + 'reports/person_attendance_all_ajax',
				type: 'POST',
				data: function(d) {
					d.month = $('#paa_month').val() || month;
					d.year = $('#paa_year').val() || year;
					d.district = $('#paa_district').val() || '';
					d.facility_name = $('#paa_facility_name').val() || '';
					d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
				}
			},
			columns: [
				{ data: 0, orderable: false, width: '40px' },
				{ data: 1, className: 'cname' },
				{ data: 2, className: 'cname' },
				{ data: 3, className: 'cname' },
				{ data: 4 },
				{ data: 5, className: 'paa-num-col', width: '72px' },
				{ data: 6, className: 'paa-num-col', width: '72px' },
				{ data: 7, className: 'paa-num-col', width: '72px' },
				{ data: 8, className: 'paa-num-col', width: '72px' },
				{ data: 9, className: 'paa-num-col', width: '72px' },
				{ data: 10, className: 'paa-num-col', width: '72px' },
				{ data: 11, className: 'paa-num-col', width: '72px' }
			]
		});

		$('#paa_apply').on('click', function() {
			updateExportLinks();
			paaTable.ajax.reload();
		});

		$('#paa_csv_link, #paa_pdf_link').on('click', function(e) {
			updateExportLinks();
			var href = $(this).attr('href');
			if (!href || href === '#') return;
    e.preventDefault();
			if ($(this).attr('id') === 'paa_pdf_link') {
				window.open(href, '_blank', 'noopener');
			} else {
				window.location.href = href;
			}
		});

		updateExportLinks();
	});
})();
</script>
