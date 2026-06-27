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

	.aa-filter-s2 + .select2-container {
		width: 100% !important;
		max-width: 100%;
	}

	#aa-cache-notice {
		border-radius: 8px;
		font-size: 0.88rem;
		margin-bottom: 1rem;
	}
	#aa-cache-notice.aa-cache-warn {
		background: #fff8e6;
		border: 1px solid #ffe08a;
		color: #856404;
	}
	#aa-cache-notice.aa-cache-info {
		background: #e8f6f3;
		border: 1px solid #b8e6d8;
		color: #005662;
	}
	#aaViewTabs .nav-link {
		font-weight: 600;
		color: #005662;
	}
	#aaViewTabs .nav-link.active {
		color: #fff;
		background: #005662;
	}
	#aa_chart_container {
		width: 100%;
		min-height: 420px;
	}
	#aaTabCharts .aa-chart-toolbar {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 0.75rem;
		margin-bottom: 1rem;
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
				<div class="card card-outline card-success collapsed-card mb-3">
					<div class="card-header">
						<h3 class="card-title"><i class="fas fa-filter mr-2"></i>Report Filters</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
						</div>
					</div>
					<div class="card-body">
					<form id="aggregateFiltersForm" class="form-horizontal" style="padding-bottom: 1em;">
						<div class="row">
							<div class="col-md-4">
								<div class="control-group">
									<label for="month">Select Month and Year:(Max value is 4 months)</label>
									<select name="duty_date[]" id="aa_duty_date" multiple="multiple" size="3"
									onchange="limitSelection(this)" class="form-control aa-filter-s2">
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
									<select class="form-control aa-filter-s2 aa-multi-s2" name="region[]" id="aa_region" multiple data-placeholder="All regions">
										<?php foreach ($regions as $key => $value): ?>
											<?php if (empty($value->region)) { continue; } ?>
											<option value="<?php echo htmlspecialchars($value->region, ENT_QUOTES, 'UTF-8'); ?>">
												<?php echo htmlspecialchars($value->region, ENT_QUOTES, 'UTF-8'); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>

							<div class="col-md-4">
								<div class="control-group">
									<label>District</label>
									<select class="form-control aa-filter-s2" name="district" id="aa_district">
										<option value="">All</option>
										<?php foreach ($districts as $key => $value): ?>
											<?php if (empty($value->district)) { continue; } ?>
											<option value="<?php echo htmlspecialchars($value->district, ENT_QUOTES, 'UTF-8'); ?>">
												<?php echo htmlspecialchars($value->district, ENT_QUOTES, 'UTF-8'); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
						</div>

						<div class="row mt-2">
							<div class="col-md-4">
								<div class="control-group">
									<label><?php echo entity_label('facility'); ?></label>
									<select class="form-control aa-filter-s2" name="facility_name" id="aa_facility_name">
										<option value="">All (select district to narrow)</option>
									</select>
								</div>
							</div>

							<div class="col-md-4">
								<div class="control-group">
									<label>Institution Type</label>
									<select class="form-control aa-filter-s2" name="institution_type" id="aa_institution_type">
										<option value="">All</option>
										<?php
										$aa_institution_types = isset($institutiontypes) && is_array($institutiontypes) ? $institutiontypes : [];
										foreach ($aa_institution_types as $institution) {
											$instName = '';
											if (is_object($institution)) {
												$instName = isset($institution->institutiontype_name) ? trim((string) $institution->institutiontype_name) : '';
												if ($instName === '' && isset($institution->val)) {
													$instName = trim((string) $institution->val);
												}
											}
											if ($instName === '') {
												continue;
											}
										?>
											<option value="<?php echo htmlspecialchars($instName, ENT_QUOTES, 'UTF-8'); ?>">
												<?php echo htmlspecialchars($instName, ENT_QUOTES, 'UTF-8'); ?>
											</option>
										<?php } ?>
									</select>
								</div>
							</div>

							<div class="col-md-4">
								<div class="control-group">
									<label>Group By Column</label>
									<select class="form-control aa-filter-s2" name="group_by" id="aa_group_by">
										<?php foreach ($aggregations as $key => $value): ?>
											<option value="<?php echo $value; ?>" <?php echo ($grouped_by == $value) ? "selected" : ""; ?>>
												<?php echo htmlspecialchars(group_by_label($value), ENT_QUOTES, 'UTF-8'); ?>
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
								<a href="#" id="aa_pdf_link" style="font-size:12px;" class="btn bg-gray-dark color-pale" target="_blank">
									<i class="fa fa-file-pdf-o"></i> Export PDF
								</a>
					</div>
						</div>
					</form>
					</div>
				</div>
			</div>
	</div>

	<div class="panel-body">
			<div class="col-md-3" style="border-right: 0; border-left: 0; border-top: 0;">
				<img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px">
		</div>
		<div class="col-md-12" style="border-right: 0; border-left: 0; border-top: 0;">
				<p style="font-size: 16px; font-weight:bold; margin:0 auto;">
					AGGREGATED ATTENDANCE TO DUTY SUMMARY BY <span id="aa_group_by_label"><?php echo htmlspecialchars(group_by_label($grouped_by), ENT_QUOTES, 'UTF-8'); ?></span>
				</p>
			</div>

			<?php
			$aa_cache = isset($aggregate_cache) && is_array($aggregate_cache) ? $aggregate_cache : ['redis' => false, 'memcached' => false];
			$aa_redis_missing = empty($aa_cache['redis']);
			?>
			<div id="aa-cache-notice" class="alert <?php echo $aa_redis_missing ? 'aa-cache-warn' : 'aa-cache-info'; ?>" role="status" style="<?php echo $aa_redis_missing ? '' : 'display:none;'; ?>">
				<i class="fas fa-<?php echo $aa_redis_missing ? 'exclamation-triangle' : 'info-circle'; ?> mr-1"></i>
				<span id="aa-cache-notice-text">
					<?php if ($aa_redis_missing) { ?>
						Redis cache is not available. This report is loaded from the database and may take longer than usual.
					<?php } ?>
				</span>
			</div>

			<ul class="nav nav-tabs mb-3" id="aaViewTabs" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="aa-tab-table" data-toggle="tab" href="#aaTabTable" role="tab">Table</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" id="aa-tab-charts" data-toggle="tab" href="#aaTabCharts" role="tab">Charts</a>
				</li>
			</ul>

			<div class="tab-content" id="aaViewTabContent">
				<div class="tab-pane fade show active" id="aaTabTable" role="tabpanel">
			<div class="table-responsive mt-1">
				<table id="attendanceAggregateTable" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th>#</th>
							<th id="group_by_header"><?php echo htmlspecialchars(group_by_label($grouped_by), ENT_QUOTES, 'UTF-8'); ?></th>
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

				<div class="tab-pane fade" id="aaTabCharts" role="tabpanel">
					<div class="aa-chart-toolbar">
						<label class="mb-0 mr-2" for="aa_chart_type">Chart type</label>
						<select id="aa_chart_type" class="form-control form-control-sm" style="max-width:320px;">
							<option value="attendance_rates">% Accounted vs Absenteeism (by group)</option>
							<option value="schedule_breakdown">Schedule mix (stacked %)</option>
							<option value="staff_days">Staff-days worked vs scheduled</option>
							<option value="period_trend">Trend by period</option>
						</select>
						<button type="button" id="aa_chart_refresh" class="btn btn-sm btn-outline-secondary">
							<i class="fas fa-sync-alt"></i> Refresh chart
						</button>
						<span id="aa_chart_status" class="text-muted small"></span>
					</div>
					<div id="aa_chart_container"></div>
				</div>
			</div>
	</div>
	</div>
</section>

<script type="text/javascript">
<?php
$aa_group_by_labels = [];
foreach ($aggregations as $agg) {
	$aa_group_by_labels[$agg] = group_by_label($agg);
}
?>
$(document).ready(function() {
	var table;
	var aaChart = null;
	var aaChartLoaded = false;
	var csrfTokenName = '<?php echo $this->security->get_csrf_token_name(); ?>';
	var csrfTokenHash = '<?php echo $this->security->get_csrf_hash(); ?>';
	var baseUrl = '<?php echo base_url(); ?>';
	var groupByLabels = <?php echo json_encode($aa_group_by_labels); ?>;
	var redisAvailable = <?php echo !empty($aa_cache['redis']) ? 'true' : 'false'; ?>;

	function showCacheNotice(meta) {
		if (!meta || !meta.message || meta.redis_available) {
			return;
		}
		var $notice = $('#aa-cache-notice');
		$('#aa-cache-notice-text').text(meta.message);
		$notice.removeClass('aa-cache-info').addClass('aa-cache-warn').show();
		if (!redisAvailable && typeof $.notify === 'function') {
			$.notify(meta.message, 'warn');
		}
	}

	function nonEmptyList(vals) {
		return (vals || []).filter(function(v) { return v !== null && v !== undefined && String(v).trim() !== ''; });
	}

	function getFilters() {
		var dutyDates = $('#aa_duty_date').val() || [];
		if (dutyDates.length === 0) {
			dutyDates = ['<?php echo $period; ?>'];
		}
		return {
			duty_date: dutyDates,
			district: $('#aa_district').val() || '',
			facility_name: $('#aa_facility_name').val() || '',
			region: nonEmptyList($('#aa_region').val()),
			institution_type: $('#aa_institution_type').val() || '',
			group_by: $('#aa_group_by').val() || 'district'
		};
	}

	function populateAggregateFacilities(facilities) {
		var $facility = $('#aa_facility_name');
		var html = '<option value="">All</option>';
		(facilities || []).forEach(function(o) {
			var name = (o && o.value) ? o.value : (o && o.label ? o.label : '');
			if (!name) {
				return;
			}
			var safe = $('<div>').text(name).html();
			html += '<option value="' + safe + '">' + safe + '</option>';
		});
		$facility.html(html);
		if ($facility.data('select2')) {
			$facility.val('').trigger('change.select2');
		}
	}

	function loadAggregateFacilities(district) {
		if (!district) {
			populateAggregateFacilities([]);
			return;
		}
		$.getJSON(baseUrl + 'reports/attendance_aggregate', { facilities: '1', district: district })
			.done(function(data) {
				populateAggregateFacilities(data && data.facilities ? data.facilities : []);
			})
			.fail(function() {
				populateAggregateFacilities([]);
			});
	}

	function initAggregateSelect2() {
		$('.aa-filter-s2').each(function() {
			var $el = $(this);
			if ($el.data('select2')) {
				$el.select2('destroy');
			}
			var isMulti = !!$el.prop('multiple');
			if (isMulti && $el.find('option.aa-s2-placeholder').length === 0) {
				$el.prepend('<option class="aa-s2-placeholder" value=""></option>');
			}
			var opts = {
				theme: 'bootstrap4',
				width: '100%',
				dropdownParent: $('#aggregateFiltersForm'),
				minimumResultsForSearch: isMulti ? 0 : 6
			};
			if (isMulti) {
				opts.placeholder = $el.data('placeholder') || 'All';
				opts.allowClear = true;
				opts.closeOnSelect = false;
			}
			$el.select2(opts);
		});
	}

	function loadAggregateInstitutionTypes() {
		var $inst = $('#aa_institution_type');
		if ($inst.find('option').length > 1) {
			return;
		}
		$.getJSON(baseUrl + 'reports/attendance_aggregate', { institution_types: '1' })
			.done(function(data) {
				var html = '<option value="">All</option>';
				(data && data.institution_types ? data.institution_types : []).forEach(function(o) {
					var name = (o && o.value) ? o.value : (o && o.label ? o.label : '');
					if (!name) {
						return;
					}
					var safe = $('<div>').text(name).html();
					html += '<option value="' + safe + '">' + safe + '</option>';
				});
				$inst.html(html);
				if ($inst.data('select2')) {
					$inst.select2('destroy');
				}
				$inst.select2({
					theme: 'bootstrap4',
					width: '100%',
					dropdownParent: $('#aggregateFiltersForm'),
					minimumResultsForSearch: 6,
					allowClear: true,
					placeholder: 'All'
				});
			});
	}

	function buildExportParams(csvOrPdf) {
		var f = getFilters();
		var params = new URLSearchParams();
		params.append(csvOrPdf, '1');
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
		if (f.institution_type) {
			params.append('institution_type', f.institution_type);
		}
		return baseUrl + 'reports/attendance_aggregate?' + params.toString();
	}
	function updateExportLinks() {
		$('#aa_csv_link').attr('href', buildExportParams('csv'));
		$('#aa_pdf_link').attr('href', buildExportParams('pdf'));
	}

	function updateGroupByLabel() {
		var groupBy = $('#aa_group_by').val();
		var label = groupByLabels[groupBy] || groupBy.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
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
				dataSrc: function(json) {
					if (json && json.cache_meta) {
						showCacheNotice(json.cache_meta);
					}
					return json.data || [];
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

	function waitForHighcharts(cb) {
		if (typeof Highcharts !== 'undefined' && typeof Highcharts.chart === 'function') {
			cb();
			return;
		}
		setTimeout(function() { waitForHighcharts(cb); }, 80);
	}

	function renderAggregateChart(payload) {
		if (!payload || !payload.categories) {
			$('#aa_chart_status').text('No chart data for the current filters.');
			return;
		}

		waitForHighcharts(function() {
			if (aaChart) {
				aaChart.destroy();
				aaChart = null;
			}

			var kind = payload.chart_kind || 'column';
			var chartType = (kind === 'line') ? 'line' : 'column';
			var options = {
				chart: { type: chartType },
				title: { text: payload.title || 'Attendance aggregate' },
				xAxis: {
					categories: payload.categories,
					labels: { rotation: -45, style: { fontSize: '11px' } }
				},
				yAxis: {
					title: { text: (kind === 'stacked_column' || payload.categories.length > 0 && payload.series && payload.series[0] && String(payload.series[0].name).indexOf('%') >= 0) ? '% of staff-days' : 'Staff-days' },
					min: 0,
					max: (kind === 'stacked_column' || chartType === 'line') ? null : undefined
				},
				tooltip: {
					shared: true,
					valueSuffix: (kind === 'stacked_column' || chartType === 'line') ? '%' : ''
				},
				plotOptions: {
					column: {
						stacking: (kind === 'stacked_column') ? 'percent' : null,
						borderWidth: 0
					},
					series: { marker: { enabled: chartType === 'line' } }
				},
				series: payload.series || [],
				credits: { enabled: false },
				exporting: { enabled: true }
			};

			if (kind === 'stacked_column') {
				options.yAxis.max = 100;
			}

			aaChart = Highcharts.chart('aa_chart_container', options);
			$('#aa_chart_status').text('');
		});
	}

	function loadAggregateChart(force) {
		if (!$('#aaTabCharts').hasClass('active') && !force) {
			return;
		}

		var filters = getFilters();
		$('#aa_chart_status').html('<i class="fas fa-spinner fa-spin"></i> Loading chart…');

		$.ajax({
			url: baseUrl + 'reports/attendanceAggregateChartData',
			type: 'POST',
			dataType: 'json',
			timeout: 120000,
			data: {
				district: filters.district,
				facility_name: filters.facility_name,
				region: filters.region,
				institution_type: filters.institution_type,
				duty_date: filters.duty_date,
				group_by: filters.group_by,
				chart_type: $('#aa_chart_type').val() || 'attendance_rates',
				[csrfTokenName]: csrfTokenHash
			}
		}).done(function(json) {
			if (json && json.cache_meta) {
				showCacheNotice(json.cache_meta);
			}
			if (json && json.error) {
				$('#aa_chart_status').text(json.error);
				return;
			}
			aaChartLoaded = true;
			renderAggregateChart(json);
		}).fail(function(xhr) {
			console.error('Aggregate chart error', xhr.responseText);
			$('#aa_chart_status').text('Failed to load chart data.');
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
		updateExportLinks();
		table.ajax.reload();
		if ($('#aaTabCharts').hasClass('active')) {
			loadAggregateChart(true);
		} else {
			aaChartLoaded = false;
		}
	});

	// Group by change
	$('#aa_group_by').on('change', function() {
		updateGroupByLabel();
		table.ajax.reload();
		if ($('#aaTabCharts').hasClass('active')) {
			loadAggregateChart(true);
		} else {
			aaChartLoaded = false;
		}
	});

	$('#aa_chart_type').on('change', function() {
		loadAggregateChart(true);
	});

	$('#aa_chart_refresh').on('click', function() {
		loadAggregateChart(true);
	});

	$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
		if ($(e.target).attr('href') === '#aaTabCharts' && !aaChartLoaded) {
			loadAggregateChart(true);
		}
	});

	// Initialize (defer Select2 so it does not fight DataTable; multi-selects must not use value="" "All" options)
	updateGroupByLabel();
	updateExportLinks();
	initTable();
	window.setTimeout(function() {
		initAggregateSelect2();
		loadAggregateInstitutionTypes();
	}, 0);

	$('#aa_district').on('change', function() {
		loadAggregateFacilities($(this).val() || '');
		updateExportLinks();
	});

	// Update CSV link when filters change
	$('#aa_duty_date, #aa_facility_name, #aa_region, #aa_institution_type').on('change', function() {
		updateExportLinks();
	});
});
</script>
