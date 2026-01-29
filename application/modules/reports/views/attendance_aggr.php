<style>
	.cnumber {
		width: 3%;
	}

	.cname {
		text-align: left;
		padding-left: 1.5em;
		width: 30%;
	}

	@media only screen and (max-width: 980px) {
		.cnumber {
			width: 100%;
		}

		.cname {
			padding-left: 0em;
			text-align: left;
			width: 100%;
		}

		.print {
			display: none;
		}
	}
</style>
<script>
	function limitSelection(select) {
		var selectedOptions = select.selectedOptions;
		if (selectedOptions.length > 3) {
			select.options[select.selectedIndex].selected = false;
		}
	}
</script>
<section class="content">
	<div class="container-fluid">
		<!-- Main row -->
		<div class="row">
			<div class="col-md-12">
				<div class="callout callout-success">
					<form id="aggregateFiltersForm" class="form-horizontal" style="padding-bottom: 2em;">
						<div class="row">
							<div class="col-md-4">
								<div class="control-group">
									<label for="month">Select Month and Year:(Max value is 4 months)</label>
									<select name="duty_date[]" id="aa_duty_date" multiple="multiple" size="3"
									onchange="limitSelection(this)" class="form-control select2">
									<?php
									$currentYear = date("Y");
										$currentMonth = date("n");
									for ($year = $currentYear; $year >= ($currentYear - 20); $year--) {
										for ($month = 1; $month <= 12; $month++) {
											$monthName = date("F", mktime(0, 0, 0, $month, 1, $year));
											$valuef = "$year-$month";
											$timestamp = strtotime($valuef);
												$value = date("Y-m", $timestamp);
											$label = "$monthName $year";
												$isSelected = ($value == $period) ? "selected" : "";
											echo "<option value='$value' $isSelected>$label</option>";
										}
									}
									?>
								</select>
								</div>
							</div>

							<div class="col-md-4">
								<div class="control-group">
									<label>Region</label>
									<select class="form-control select2" name="region[]" id="aa_region" multiple>
										<option value="">All</option>
										<?php foreach ($regions as $key => $value): ?>
											<option value="<?php echo $value->region; ?>">
												<?php echo $value->region; ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>

							<div class="col-md-4">
								<div class="control-group">
									<label>District</label>
									<select class="form-control select2" name="district" id="aa_district">
										<option value="">All</option>
										<?php foreach ($districts as $key => $value): ?>
											<option value="<?php echo $value->district; ?>">
												<?php echo $value->district; ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
						</div>

						<div class="row mt-2">
							<div class="col-md-4">
								<div class="control-group">
									<label>Facility</label>
									<select class="form-control select2" name="facility_name" id="aa_facility_name">
										<option value="">All</option>
										<?php foreach ($facilities as $key => $value): ?>
											<option value="<?php echo $value->facility; ?>">
												<?php echo $value->facility; ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>

							<div class="col-md-4">
								<div class="control-group">
									<label>Institution Type</label>
									<select class="form-control select2" name="institution_type[]" id="aa_institution_type" multiple>
										<option value="">All</option>
										<?php foreach ($institutiontypes as $institution): ?>
											<option value="<?php echo $institution->institutiontype_name; ?>">
												<?php echo $institution->institutiontype_name; ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>

							<div class="col-md-4">
								<div class="control-group">
									<label>Group By Column</label>
									<select class="form-control select2" name="group_by" id="aa_group_by">
										<?php foreach ($aggregations as $key => $value): ?>
											<option value="<?php echo $value; ?>" <?php echo ($grouped_by == $value) ? "selected" : ""; ?>>
												<?php echo ucwords(str_replace("_", " ", $value)); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
				</div>

				<div class="row mt-2">
							<div class="col-md-12">
								<button type="button" id="aa_apply" class="btn bg-gray-dark color-pale" style="font-size:12px;">
									Apply Filters
								</button>
								<a href="#" id="aa_csv_link" style="font-size:12px;" class="btn bg-gray-dark color-pale">
									<i class="fa fa-file"></i> Export CSV
								</a>
					</div>
						</div>
					</form>
				</div>
			</div>
	</div>

	<div class="panel-body">
			<div class="col-md-3" style="border-right: 0; border-left: 0; border-top: 0;">
				<img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px">
		</div>
		<div class="col-md-12" style="border-right: 0; border-left: 0; border-top: 0;">
				<p style="font-size: 16px; font-weight:bold; margin:0 auto;">
					AGGREGATED ATTENDANCE TO DUTY SUMMARY BY <span id="aa_group_by_label"><?php echo ucwords(str_replace("_", " ", $grouped_by)); ?></span>
				</p>
			</div>

			<div class="table-responsive mt-3">
				<table id="attendanceAggregateTable" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th>#</th>
							<th id="group_by_header"><?php echo ucwords(str_replace("_", " ", $grouped_by)); ?></th>
							<th>Period</th>
							<th>Present</th>
							<th>Off Duty</th>
							<th>Official Request</th>
							<th>Leave</th>
							<th>Holiday</th>
							<th>Absent</th>
							<th>Days Worked</th>
							<th>Days Scheduled</th>
							<th>% Accounted</th>
							<th>% Absenteeism</th>
						</tr>
					</thead>
					<tbody>
						<!-- DataTables will populate this -->
					</tbody>
				</table>
	</div>
	</div>
	</div>
</section>

<script type="text/javascript">
$(document).ready(function() {
	var table;
	var csrfTokenName = '<?php echo $this->security->get_csrf_token_name(); ?>';
	var csrfTokenHash = '<?php echo $this->security->get_csrf_hash(); ?>';
	var baseUrl = '<?php echo base_url(); ?>';

	function getFilters() {
		var dutyDates = $('#aa_duty_date').val() || [];
		if (dutyDates.length === 0) {
			dutyDates = ['<?php echo $period; ?>'];
		}
		return {
			duty_date: dutyDates,
			district: $('#aa_district').val() || '',
			facility_name: $('#aa_facility_name').val() || '',
			region: $('#aa_region').val() || [],
			institution_type: $('#aa_institution_type').val() || [],
			group_by: $('#aa_group_by').val() || 'district'
		};
	}

	function updateCsvLink() {
		var f = getFilters();
		var params = new URLSearchParams();
		params.append('csv', '1');
		params.append('group_by', f.group_by);
		if (f.duty_date && f.duty_date.length > 0) {
			f.duty_date.forEach(function(date) {
				params.append('duty_date[]', date);
			});
		}
		if (f.district) params.append('district', f.district);
		if (f.facility_name) params.append('facility_name', f.facility_name);
		if (f.region && f.region.length > 0) {
			f.region.forEach(function(region) {
				params.append('region[]', region);
			});
		}
		if (f.institution_type && f.institution_type.length > 0) {
			f.institution_type.forEach(function(type) {
				params.append('institution_type[]', type);
			});
		}
		$('#aa_csv_link').attr('href', baseUrl + 'reports/attendance_aggregate?' + params.toString());
	}

	function updateGroupByLabel() {
		var groupBy = $('#aa_group_by').val();
		var label = groupBy.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
		$('#aa_group_by_label').text(label);
		$('#group_by_header').text(label);
	}

	function initTable() {
		if (table) {
			table.destroy();
		}

		var f = getFilters();

		table = $('#attendanceAggregateTable').DataTable({
			processing: true,
			serverSide: true,
			pageLength: 20,
			lengthMenu: [[10, 20, 25, 50, 100, 200], [10, 20, 25, 50, 100, 200]],
			scrollX: true,
			order: [[2, 'asc'], [1, 'asc']], // Sort by period, then grouped_by
			ajax: {
				url: baseUrl + 'reports/attendanceAggregateAjax',
				type: 'POST',
				data: function(d) {
					var filters = getFilters();
					d[csrfTokenName] = csrfTokenHash;
					d.district = filters.district;
					d.facility_name = filters.facility_name;
					d.region = filters.region;
					d.institution_type = filters.institution_type;
					d.duty_date = filters.duty_date;
					d.group_by = filters.group_by;
				},
				error: function(xhr, error, thrown) {
					console.error('Attendance Aggregate DataTables error', { xhr: xhr, error: error, thrown: thrown, responseText: xhr.responseText });
				}
			},
			columns: [
				{ data: 0, orderable: false },
				{ data: 1 },
				{ data: 2 },
				{ data: 3 },
				{ data: 4 },
				{ data: 5 },
				{ data: 6 },
				{ data: 7 },
				{ data: 8 },
				{ data: 9 },
				{ data: 10 },
				{ data: 11 },
				{ data: 12 }
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
	$('#aggregateFiltersForm').on('submit', function(e) {
		e.preventDefault();
		return false;
	});

	// Apply filters button
	$('#aa_apply').on('click', function() {
		updateGroupByLabel();
		updateCsvLink();
		table.ajax.reload();
	});

	// Group by change
	$('#aa_group_by').on('change', function() {
		updateGroupByLabel();
		table.ajax.reload();
	});

	// Initialize
	updateGroupByLabel();
	updateCsvLink();
	initTable();

	// Update CSV link when filters change
	$('#aa_duty_date, #aa_district, #aa_facility_name, #aa_region, #aa_institution_type').on('change', function() {
		updateCsvLink();
	});
});
</script>
