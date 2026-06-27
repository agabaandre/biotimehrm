<!-- Attendance Analytics Charts -->
<style>
.attendance-graphs-container .chart-card .card-body { min-height: 340px; }
.attendance-graphs-container .chart-box { width: 100%; height: 320px; min-height: 280px; }
</style>

<div class="attendance-graphs-container">
	<div class="row mb-2">
		<div class="col-12">
			<small class="text-muted" id="graph_fy_hint">Charts show the full financial year (Jun–May) for the selected year filter.</small>
		</div>
	</div>

	<div class="row">
		<div class="col-12 mb-4">
			<div class="chart-card">
				<div class="card card-outline card-success">
					<div class="card-header"><h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Average Daily Working Staff (Monthly)</h3></div>
					<div class="card-body"><div id="chart_avg_daily" class="chart-box"></div></div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-6 mb-4">
			<div class="chart-card">
				<div class="card card-outline card-primary">
					<div class="card-header">
						<h3 class="card-title"><i class="fas fa-chart-column mr-2"></i>Monthly Attendance Rate (%)</h3>
					</div>
					<div class="card-body"><div id="chart_att_rate" class="chart-box"></div></div>
					<div class="card-footer py-2">
						<small class="text-muted">Line = present on working days. Stacked columns = schedule mix (% of calendar staff-days).</small>
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-6 mb-4">
			<div class="chart-card">
				<div class="card card-outline card-danger">
					<div class="card-header">
						<h3 class="card-title"><i class="fas fa-chart-column mr-2"></i>Monthly Absenteeism Rate (%)</h3>
					</div>
					<div class="card-body"><div id="chart_abs_rate" class="chart-box"></div></div>
					<div class="card-footer py-2">
						<small class="text-muted">Line = unaccounted absent on working days. Columns = scheduled away and unaccounted (% of calendar staff-days).</small>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
(function() {
	var charts = {};

	function waitForHighcharts(cb) {
		if (typeof Highcharts !== 'undefined' && typeof Highcharts.chart === 'function') {
			cb();
		} else {
			setTimeout(function() { waitForHighcharts(cb); }, 80);
		}
	}

	function scheduleSeries(data, prefix) {
		return [
			{ name: 'Present (Schedule)', data: data[prefix + '_present'] || [], color: '#20c198' },
			{ name: 'Off Duty (Schedule)', data: data[prefix + '_off'] || [], color: '#f0ad4e' },
			{ name: 'On Leave (Schedule)', data: data[prefix + '_leave'] || [], color: '#17a2b8' },
			{ name: 'Official (Schedule)', data: data[prefix + '_official'] || [], color: '#6f42c1' },
			{ name: 'Holiday (Schedule)', data: data[prefix + '_holiday'] || [], color: '#adb5bd' }
		];
	}

	function renderCharts(data) {
		if (!data || !data.graph) return;
		var period = data.graph.period || [];
		var fyLabel = data.period_label || data.fy_label || '';

		if (charts.avg) charts.avg.destroy();
		charts.avg = Highcharts.chart('chart_avg_daily', {
			chart: { type: 'line' },
			title: { text: 'Average Daily Working Staff' },
			subtitle: { text: fyLabel },
			xAxis: { categories: period },
			yAxis: { title: { text: 'Avg staff per working day' }, min: 0 },
			series: [{ name: 'Staff', data: data.graph.data || [], color: '#20c198' }],
			credits: { enabled: false }
		});

		var attSchedule = scheduleSeries(data, 'schedule');

		if (charts.att) charts.att.destroy();
		charts.att = Highcharts.chart('chart_att_rate', {
			chart: { type: 'column' },
			title: { text: null },
			xAxis: { categories: period },
			yAxis: {
				title: { text: '% of staff-days' },
				max: 100,
				stackLabels: { enabled: false }
			},
			tooltip: {
				shared: true,
				valueSuffix: '%'
			},
			plotOptions: {
				column: { stacking: 'normal', borderWidth: 0 },
				series: { marker: { enabled: true, radius: 3 } }
			},
			series: attSchedule.concat([{
				type: 'spline',
				name: 'Attendance rate',
				data: data.attendance_rate || [],
				color: '#005662',
				lineWidth: 3,
				marker: { lineWidth: 2, lineColor: '#005662', fillColor: '#fff' },
				zIndex: 5
			}]),
			credits: { enabled: false }
		});

		if (charts.abs) charts.abs.destroy();
		charts.abs = Highcharts.chart('chart_abs_rate', {
			chart: { type: 'column' },
			title: { text: null },
			xAxis: { categories: period },
			yAxis: {
				title: { text: '% of staff-days' },
				max: 100
			},
			tooltip: {
				shared: true,
				valueSuffix: '%'
			},
			plotOptions: {
				column: { stacking: 'normal', borderWidth: 0 },
				series: { marker: { enabled: true, radius: 3 } }
			},
			series: [
				{ name: 'Off Duty (Schedule)', data: data.schedule_off || [], color: '#f0ad4e' },
				{ name: 'On Leave (Schedule)', data: data.schedule_leave || [], color: '#17a2b8' },
				{ name: 'Official (Schedule)', data: data.schedule_official || [], color: '#6f42c1' },
				{ name: 'Holiday (Schedule)', data: data.schedule_holiday || [], color: '#adb5bd' },
				{ name: 'Unaccounted Absent', data: data.schedule_unaccounted || [], color: '#e74c3c' },
				{
					type: 'spline',
					name: 'Absenteeism rate',
					data: data.absenteeism_rate || [],
					color: '#c0392b',
					lineWidth: 3,
					marker: { lineWidth: 2, lineColor: '#c0392b', fillColor: '#fff' },
					zIndex: 5
				}
			],
			credits: { enabled: false }
		});

		$('#graph_fy_hint').text('Showing ' + fyLabel + (data.cached ? ' (cached)' : ''));
	}

	function refreshAttendanceGraph() {
		return $.ajax({
			type: 'GET',
			url: '<?php echo base_url('dashboard/graphsData'); ?>',
			dataType: 'json',
			timeout: 120000,
			cache: false
		}).done(function(data) {
			renderCharts(data);
		}).fail(function(xhr, status, err) {
			console.error('Graphs data load error:', err || status);
		});
	}

	$(document).ready(function() {
		waitForHighcharts(function() {
			window.reloadAttendancePerMonth = refreshAttendanceGraph;
			refreshAttendanceGraph();
		});
	});
})();
</script>
