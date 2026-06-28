<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$tv_facility_name = isset($facility_name) ? trim((string) $facility_name) : '';
$tv_poll = isset($tv_poll_seconds) ? max(8, (int) $tv_poll_seconds) : 15;
$tv_facility_label = entity_label('facility');
?>
<style>
:root,
[data-theme="dark"] {
	--tv-bg: #0b1218;
	--tv-surface: #141e28;
	--tv-border: rgba(255,255,255,0.08);
	--tv-text: #eef4f7;
	--tv-muted: #8fa3ad;
	--tv-accent: #20c198;
	--tv-accent-2: #005662;
	--tv-warn: #f0ad4e;
	--tv-danger: #e74c3c;
	--tv-info: #17a2b8;
	--tv-purple: #6f42c1;
	--tv-shadow: 0 12px 40px rgba(0,0,0,0.35);
	--tv-chart-text: #eef4f7;
}
[data-theme="light"] {
	--tv-bg: #eef3f6;
	--tv-surface: #ffffff;
	--tv-border: #d8e3e8;
	--tv-text: #1a2e35;
	--tv-muted: #6c7a80;
	--tv-shadow: 0 8px 28px rgba(0, 86, 98, 0.08);
	--tv-chart-text: #1a2e35;
}
html, body.tv-root {
	margin: 0; padding: 0; min-height: 100vh;
	background: var(--tv-bg); color: var(--tv-text);
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
	overflow-x: hidden;
}
.tv-screen { min-height: 100vh; display: flex; flex-direction: column; padding: 1rem 1.25rem 0.75rem; box-sizing: border-box; }
.tv-topbar {
	display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
	margin-bottom: 1rem; padding-bottom: 0.85rem; border-bottom: 1px solid var(--tv-border);
}
.tv-brand { display: flex; align-items: center; gap: 0.75rem; flex: 1 1 240px; min-width: 0; }
.tv-brand img { width: 48px; height: 48px; object-fit: contain; }
.tv-brand h1 { margin: 0; font-size: clamp(1.1rem, 2vw, 1.6rem); font-weight: 700; }
.tv-brand p { margin: 0.15rem 0 0; color: var(--tv-muted); font-size: 0.88rem; }
.tv-clock { font-size: clamp(1.3rem, 2.2vw, 1.85rem); font-weight: 700; font-variant-numeric: tabular-nums; }
.tv-live-pill {
	display: inline-flex; align-items: center; gap: 0.4rem;
	padding: 0.3rem 0.7rem; border-radius: 999px;
	background: rgba(32, 193, 152, 0.15); color: var(--tv-accent);
	font-size: 0.75rem; font-weight: 700; letter-spacing: 0.08em;
}
.tv-live-pill .dot {
	width: 8px; height: 8px; border-radius: 50%; background: var(--tv-accent);
	animation: tvPulse 1.8s ease-in-out infinite;
}
@keyframes tvPulse {
	0% { box-shadow: 0 0 0 0 rgba(32, 193, 152, 0.7); }
	70% { box-shadow: 0 0 0 8px rgba(32, 193, 152, 0); }
	100% { box-shadow: 0 0 0 0 rgba(32, 193, 152, 0); }
}
.tv-toolbar { display: flex; gap: 0.45rem; margin-left: auto; }
.tv-btn {
	border: 1px solid var(--tv-border); background: var(--tv-surface); color: var(--tv-text);
	border-radius: 8px; padding: 0.4rem 0.7rem; font-size: 0.8rem; cursor: pointer;
}
.tv-btn:hover { border-color: var(--tv-accent); color: var(--tv-accent); }

.tv-grid {
	display: grid; grid-template-columns: repeat(12, 1fr); gap: 0.85rem; flex: 1;
}
.tv-card {
	background: var(--tv-surface); border: 1px solid var(--tv-border);
	border-radius: 12px; padding: 0.85rem 1rem; box-shadow: var(--tv-shadow);
}
.tv-card.chart { grid-column: span 4; padding: 0.5rem 0.65rem 0.25rem; min-height: 220px; }
.tv-card.chart-half { grid-column: span 6; padding: 0.5rem 0.65rem 0.25rem; min-height: 220px; }
.tv-card.feed-panel { grid-column: span 12; padding: 0.85rem 1rem 0.65rem; }
.tv-card.compact { grid-column: span 2; display: flex; flex-direction: column; justify-content: center; min-height: 72px; }
.tv-card.compact .val { font-size: 1.45rem; font-weight: 800; font-variant-numeric: tabular-nums; line-height: 1; }
.tv-card.compact .lbl { color: var(--tv-muted); font-size: 0.72rem; margin-top: 0.3rem; text-transform: uppercase; letter-spacing: 0.04em; font-weight: 600; }
.tv-card.sync-row { grid-column: span 4; min-height: 88px; display: flex; flex-direction: column; justify-content: center; }
.tv-card.sync-row .val { font-size: 1.05rem; font-weight: 700; line-height: 1.35; word-break: break-word; }
.tv-card.sync-row .val.big { font-size: 1.75rem; font-weight: 800; font-variant-numeric: tabular-nums; color: var(--tv-accent); }
.tv-card.sync-row .lbl { color: var(--tv-muted); font-size: 0.72rem; margin-top: 0.35rem; text-transform: uppercase; letter-spacing: 0.04em; font-weight: 600; }
.tv-card.full { grid-column: span 12; }
.tv-chart-box { width: 100%; height: 200px; }
.tv-chart-box.tall { height: 210px; }
.tv-section-title {
	margin: 0 0 0.5rem; font-size: 0.82rem; font-weight: 700;
	letter-spacing: 0.05em; text-transform: uppercase; color: var(--tv-muted);
}
.tv-feed { list-style: none; margin: 0; padding: 0; max-height: 240px; overflow: hidden; }
@media (min-width: 900px) {
	.tv-feed.feed-cols-2 {
		display: grid;
		grid-template-columns: 1fr 1fr;
		column-gap: 1.5rem;
		align-content: start;
	}
	.tv-feed.feed-cols-2 li:nth-child(odd) { border-right: none; }
}
.tv-feed li {
	display: flex; align-items: center; justify-content: space-between;
	padding: 0.5rem 0; border-bottom: 1px solid var(--tv-border); font-size: 0.9rem;
}
.tv-feed li:last-child { border-bottom: none; }
.tv-feed-name { font-weight: 600; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding-right: 0.75rem; }
.tv-feed-meta { color: var(--tv-muted); font-size: 0.8rem; white-space: nowrap; }
.tv-feed li.tv-new { animation: tvSlideIn 0.55s cubic-bezier(0.22, 1, 0.36, 1); }
@keyframes tvSlideIn {
	from { opacity: 0; transform: translateY(-100%); }
	to { opacity: 1; transform: translateY(0); }
}
.tv-footer {
	margin-top: 0.75rem; padding-top: 0.65rem; border-top: 1px solid var(--tv-border);
	display: flex; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;
	color: var(--tv-muted); font-size: 0.78rem;
}
.tv-error { grid-column: span 12; text-align: center; padding: 2.5rem 1rem; color: var(--tv-muted); }
@media (max-width: 1100px) {
	.tv-card.chart, .tv-card.chart-half, .tv-card.sync-row { grid-column: span 6; }
	.tv-card.compact { grid-column: span 3; }
}
@media (max-width: 640px) {
	.tv-card.chart, .tv-card.chart-half,
	.tv-card.sync-row, .tv-card.compact { grid-column: span 12; }
}
</style>

<div class="tv-screen" id="tv-screen">
	<header class="tv-topbar">
		<div class="tv-brand">
			<img src="<?php echo base_url('assets/img/MOH.png'); ?>" alt="Logo">
			<div>
				<h1 id="tv-facility-name"><?php echo $tv_facility_name !== '' ? htmlspecialchars($tv_facility_name, ENT_QUOTES, 'UTF-8') : htmlspecialchars($tv_facility_label, ENT_QUOTES, 'UTF-8'); ?></h1>
				<p id="tv-facility-meta"><?php echo htmlspecialchars($tv_facility_label, ENT_QUOTES, 'UTF-8'); ?> TV · <span id="tv-district">—</span> · <span id="tv-date">—</span></p>
			</div>
		</div>
		<div class="tv-live-pill"><span class="dot"></span> LIVE</div>
		<div class="tv-clock" id="tv-clock">--:--</div>
		<div class="tv-toolbar">
			<button type="button" class="tv-btn" id="tv-theme-toggle" title="Toggle theme"><i class="fas fa-adjust"></i></button>
			<button type="button" class="tv-btn" id="tv-fullscreen" title="Fullscreen"><i class="fas fa-expand"></i></button>
		</div>
	</header>

	<div class="tv-grid" id="tv-grid">
		<!-- Top charts (4+4+4 = full row) -->
		<div class="tv-card chart">
			<h2 class="tv-section-title">Today's Duty Status</h2>
			<div id="tv-chart-daily" class="tv-chart-box tall"></div>
		</div>
		<div class="tv-card chart">
			<h2 class="tv-section-title">Attendance Rate</h2>
			<div id="tv-chart-gauge" class="tv-chart-box tall"></div>
		</div>
		<div class="tv-card chart">
			<h2 class="tv-section-title">Clock Activity Today</h2>
			<div id="tv-chart-clock" class="tv-chart-box tall"></div>
		</div>

		<!-- Compact KPIs (6×2 = 12) -->
		<div class="tv-card compact"><div class="val" id="tv-checkins">—</div><div class="lbl">Check-ins</div></div>
		<div class="tv-card compact"><div class="val" id="tv-checkouts">—</div><div class="lbl">Check-outs</div></div>
		<div class="tv-card compact"><div class="val" id="tv-daily-hours">—</div><div class="lbl">Avg Hrs Today</div></div>
		<div class="tv-card compact"><div class="val" id="tv-period-hours">—</div><div class="lbl">Avg Hrs (<span id="tv-period-label">Mo</span>)</div></div>
		<div class="tv-card compact"><div class="val" id="tv-mystaff">—</div><div class="lbl">My Staff</div></div>
		<div class="tv-card compact"><div class="val" id="tv-request">—</div><div class="lbl">Workshop</div></div>

		<!-- Sync row (3×4 = 12) -->
		<div class="tv-card sync-row">
			<div class="val big" id="tv-monthly-present">—</div>
			<div class="lbl">Present Staff-days (<span id="tv-period-label-2">Month</span>)</div>
		</div>
		<div class="tv-card sync-row">
			<div class="val" id="tv-biotime-sync">—</div>
			<div class="lbl"><i class="fas fa-fingerprint mr-1"></i>BioTime Last Sync</div>
		</div>
		<div class="tv-card sync-row">
			<div class="val" id="tv-att-sum">—</div>
			<div class="lbl"><i class="fas fa-database mr-1"></i>Last Attendance Summary</div>
		</div>

		<!-- Live feed (before monthly charts) -->
		<div class="tv-card feed-panel">
			<h2 class="tv-section-title"><i class="fas fa-broadcast-tower mr-1"></i> Live Check-ins &amp; Check-outs</h2>
			<ul class="tv-feed feed-cols-2" id="tv-feed">
				<li class="tv-feed-empty">Waiting for activity…</li>
			</ul>
		</div>

		<!-- Month + structure + accounted (4+4+4 = full row, no gaps) -->
		<div class="tv-card chart">
			<h2 class="tv-section-title">Month Staff-days (<span id="tv-period-label-3">Month</span>)</h2>
			<div id="tv-chart-monthly" class="tv-chart-box tall"></div>
		</div>
		<div class="tv-card chart">
			<h2 class="tv-section-title"><?php echo htmlspecialchars($tv_facility_label, ENT_QUOTES, 'UTF-8'); ?> Structure</h2>
			<div id="tv-chart-structure" class="tv-chart-box tall"></div>
		</div>
		<div class="tv-card chart">
			<h2 class="tv-section-title">Staff Accounted vs Absent</h2>
			<div id="tv-chart-accounted" class="tv-chart-box tall"></div>
		</div>

		<!-- Secondary charts (6+6) -->
		<div class="tv-card chart-half">
			<h2 class="tv-section-title">Avg Hours Comparison</h2>
			<div id="tv-chart-hours" class="tv-chart-box tall"></div>
		</div>
		<div class="tv-card chart-half">
			<h2 class="tv-section-title">Today's Headcount</h2>
			<div id="tv-chart-headcount" class="tv-chart-box tall"></div>
		</div>
	</div>

	<footer class="tv-footer">
		<span id="tv-updated">Connecting…</span>
		<span id="tv-sync-extra">BioTime devices: <strong id="tv-bio">—</strong></span>
	</footer>
</div>

<script type="text/javascript">
(function() {
	var TV_POLL_MS = <?php echo (int) $tv_poll; ?> * 1000;
	var TV_FEED_MAX = 12;
	var tvSeen = {};
	var tvDataUrl = '<?php echo base_url('dashboard/facilityTvData'); ?>';
	var tvCharts = {};
	var TV_COLORS = {
		present: '#20c198', absent: '#e74c3c', off: '#f0ad4e',
		leave: '#17a2b8', request: '#6f42c1', staff: '#005662'
	};

	function isDarkTheme() {
		return document.documentElement.getAttribute('data-theme') !== 'light';
	}

	function chartTextColor() {
		return isDarkTheme() ? '#eef4f7' : '#1a2e35';
	}

	function chartMutedColor() {
		return isDarkTheme() ? '#8fa3ad' : '#6c7a80';
	}

	function applyTheme(theme) {
		document.documentElement.setAttribute('data-theme', theme);
		try { localStorage.setItem('facility_tv_theme', theme); } catch (e) {}
		if (window.__tvLastData) {
			updateTvCharts(window.__tvLastData);
		}
	}

	function initTheme() {
		var saved = 'dark';
		try { saved = localStorage.getItem('facility_tv_theme') || 'dark'; } catch (e) {}
		if (saved !== 'light') saved = 'dark';
		applyTheme(saved);
	}

	function waitForHighcharts(cb) {
		if (typeof Highcharts !== 'undefined' && typeof Highcharts.chart === 'function') {
			cb();
			return;
		}
		setTimeout(function() { waitForHighcharts(cb); }, 80);
	}

	function baseChartOpts() {
		return {
			chart: { backgroundColor: 'transparent', style: { fontFamily: 'inherit' } },
			credits: { enabled: false },
			exporting: { enabled: false },
			title: { text: null },
			legend: {
				itemStyle: { color: chartTextColor(), fontWeight: '500', fontSize: '11px' },
				itemHoverStyle: { color: chartTextColor() }
			},
			tooltip: {
				backgroundColor: isDarkTheme() ? '#1a2733' : '#fff',
				borderColor: isDarkTheme() ? '#334455' : '#d8e3e8',
				style: { color: chartTextColor() }
			}
		};
	}

	function updateTvCharts(data) {
		if (!data || !data.ok || typeof Highcharts === 'undefined') return;
		if (!document.getElementById('tv-chart-daily')) return;

		var present = parseInt(data.present, 10) || 0;
		var absent = parseInt(data.absent, 10) || 0;
		var off = parseInt(data.offduty, 10) || 0;
		var leave = parseInt(data.leave, 10) || 0;
		var request = parseInt(data.request, 10) || 0;
		var rate = parseFloat(data.attendance_rate) || 0;
		var checkins = parseInt(data.clock_ins_today, 10) || 0;
		var checkouts = parseInt(data.clock_outs_today, 10) || 0;

		var dailySeries = [
			{ name: 'Present', y: present, color: TV_COLORS.present },
			{ name: 'Absent', y: absent, color: TV_COLORS.absent },
			{ name: 'Off Duty', y: off, color: TV_COLORS.off },
			{ name: 'On Leave', y: leave, color: TV_COLORS.leave },
			{ name: 'Workshop', y: request, color: TV_COLORS.request }
		].filter(function(p) { return p.y > 0; });
		if (!dailySeries.length) {
			dailySeries = [{ name: 'No data', y: 1, color: '#334455' }];
		}

		if (tvCharts.daily) tvCharts.daily.destroy();
		tvCharts.daily = Highcharts.chart('tv-chart-daily', $.extend(true, {}, baseChartOpts(), {
			chart: { type: 'pie', height: 210 },
			plotOptions: {
				pie: {
					innerSize: '58%',
					borderWidth: 0,
					dataLabels: {
						enabled: true,
						format: '{point.name}: {point.y}',
						style: { color: chartTextColor(), fontSize: '10px', fontWeight: '500', textOutline: 'none' },
						distance: 12
					}
				}
			},
			series: [{ name: 'Staff', data: dailySeries }]
		}));

		if (tvCharts.gauge) tvCharts.gauge.destroy();
		tvCharts.gauge = Highcharts.chart('tv-chart-gauge', $.extend(true, {}, baseChartOpts(), {
			chart: { type: 'solidgauge', height: 210 },
			pane: {
				center: ['50%', '72%'], size: '110%', startAngle: -90, endAngle: 90,
				background: { backgroundColor: isDarkTheme() ? '#1a2733' : '#eef3f6', innerRadius: '60%', outerRadius: '100%', shape: 'arc', borderWidth: 0 }
			},
			yAxis: {
				min: 0, max: 100, lineWidth: 0, tickWidth: 0, minorTickInterval: null,
				labels: { enabled: false }
			},
			plotOptions: {
				solidgauge: {
					dataLabels: {
						y: -22, borderWidth: 0, useHTML: false,
						format: '<span style="font-size:1.6rem;font-weight:800;color:' + chartTextColor() + '">{y}%</span>',
						style: { fontSize: '1.6rem' }
					}
				}
			},
			series: [{
				name: 'Attendance',
				data: [rate],
				dataLabels: { format: '<div style="text-align:center"><span style="font-size:1.75rem;font-weight:800;color:' + chartTextColor() + '">{y:.1f}%</span></div>' },
				tooltip: { valueSuffix: '%' },
				color: rate >= 80 ? TV_COLORS.present : (rate >= 50 ? TV_COLORS.off : TV_COLORS.absent)
			}]
		}));

		if (tvCharts.clock) tvCharts.clock.destroy();
		tvCharts.clock = Highcharts.chart('tv-chart-clock', $.extend(true, {}, baseChartOpts(), {
			chart: { type: 'column', height: 210 },
			xAxis: {
				categories: ['Check-ins', 'Check-outs'],
				labels: { style: { color: chartMutedColor(), fontSize: '11px' } },
				lineColor: isDarkTheme() ? '#334455' : '#d8e3e8'
			},
			yAxis: {
				min: 0, title: { text: null },
				gridLineColor: isDarkTheme() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)',
				labels: { style: { color: chartMutedColor() } }
			},
			plotOptions: { column: { borderRadius: 6, borderWidth: 0, colorByPoint: true } },
			colors: [TV_COLORS.present, TV_COLORS.off],
			series: [{ name: 'Count', data: [checkins, checkouts], showInLegend: false }]
		}));

		var mp = parseInt(data.monthly_present, 10) || 0;
		var mo = parseInt(data.monthly_offduty, 10) || 0;
		var ml = parseInt(data.monthly_leave, 10) || 0;
		var mr = parseInt(data.monthly_request, 10) || 0;
		var monthlySeries = [
			{ name: 'Present', y: mp, color: TV_COLORS.present },
			{ name: 'Off Duty', y: mo, color: TV_COLORS.off },
			{ name: 'Leave', y: ml, color: TV_COLORS.leave },
			{ name: 'Workshop', y: mr, color: TV_COLORS.request }
		].filter(function(p) { return p.y > 0; });
		if (!monthlySeries.length) {
			monthlySeries = [{ name: 'No data', y: 1, color: '#334455' }];
		}

		if (tvCharts.monthly) tvCharts.monthly.destroy();
		tvCharts.monthly = Highcharts.chart('tv-chart-monthly', $.extend(true, {}, baseChartOpts(), {
			chart: { type: 'pie', height: 210 },
			plotOptions: {
				pie: {
					innerSize: '55%',
					borderWidth: 0,
					dataLabels: {
						enabled: true,
						format: '{point.name}<br>{point.y}',
						style: { color: chartTextColor(), fontSize: '10px', textOutline: 'none' }
					}
				}
			},
			series: [{ name: 'Staff-days', data: monthlySeries }]
		}));

		var depts = parseInt(data.departments, 10) || 0;
		var jobs = parseInt(data.jobs, 10) || 0;
		var cadres = parseInt(data.cadres, 10) || 0;
		var bio = parseInt(data.biometrics, 10) || 0;

		if (tvCharts.structure) tvCharts.structure.destroy();
		tvCharts.structure = Highcharts.chart('tv-chart-structure', $.extend(true, {}, baseChartOpts(), {
			chart: { type: 'bar', height: 210 },
			xAxis: {
				categories: ['Departments', 'Jobs', 'Cadres', 'BioTime'],
				labels: { style: { color: chartMutedColor(), fontSize: '11px' } },
				lineColor: isDarkTheme() ? '#334455' : '#d8e3e8'
			},
			yAxis: {
				min: 0, title: { text: null },
				gridLineColor: isDarkTheme() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)',
				labels: { style: { color: chartMutedColor() } }
			},
			plotOptions: { bar: { borderRadius: 4, borderWidth: 0, colorByPoint: true } },
			colors: [TV_COLORS.staff, TV_COLORS.leave, TV_COLORS.request, TV_COLORS.present],
			series: [{ name: 'Count', data: [depts, jobs, cadres, bio], showInLegend: false }]
		}));

		var dailyHrs = parseFloat(data.daily_avg_hours) || 0;
		var periodHrs = parseFloat(data.avg_hours) || 0;

		if (tvCharts.hours) tvCharts.hours.destroy();
		tvCharts.hours = Highcharts.chart('tv-chart-hours', $.extend(true, {}, baseChartOpts(), {
			chart: { type: 'column', height: 210 },
			xAxis: {
				categories: ['Today', 'Month avg'],
				labels: { style: { color: chartMutedColor(), fontSize: '11px' } },
				lineColor: isDarkTheme() ? '#334455' : '#d8e3e8'
			},
			yAxis: {
				min: 0, title: { text: 'Hours', style: { color: chartMutedColor() } },
				gridLineColor: isDarkTheme() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)',
				labels: { style: { color: chartMutedColor() } }
			},
			tooltip: { valueSuffix: ' hrs' },
			plotOptions: { column: { borderRadius: 6, borderWidth: 0, colorByPoint: true } },
			colors: [TV_COLORS.present, TV_COLORS.staff],
			series: [{ name: 'Avg hours', data: [dailyHrs, periodHrs], showInLegend: false }]
		}));

		if (tvCharts.headcount) tvCharts.headcount.destroy();
		tvCharts.headcount = Highcharts.chart('tv-chart-headcount', $.extend(true, {}, baseChartOpts(), {
			chart: { type: 'bar', height: 210 },
			xAxis: {
				categories: ['Present', 'Absent', 'Off', 'Leave', 'Workshop'],
				labels: { style: { color: chartMutedColor(), fontSize: '10px' } },
				lineColor: isDarkTheme() ? '#334455' : '#d8e3e8'
			},
			yAxis: {
				min: 0, title: { text: null },
				gridLineColor: isDarkTheme() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)',
				labels: { style: { color: chartMutedColor() } }
			},
			plotOptions: { bar: { borderRadius: 4, borderWidth: 0, colorByPoint: true } },
			colors: [TV_COLORS.present, TV_COLORS.absent, TV_COLORS.off, TV_COLORS.leave, TV_COLORS.request],
			series: [{ name: 'Staff', data: [present, absent, off, leave, request], showInLegend: false }]
		}));

		var mystaff = parseInt(data.mystaff, 10) || 0;
		var accounted = present + off + leave + request;
		var unaccounted = Math.max(0, mystaff - accounted);
		var accountedSeries = [
			{ name: 'Present', y: present, color: TV_COLORS.present },
			{ name: 'Off / Leave / Workshop', y: off + leave + request, color: TV_COLORS.off },
			{ name: 'Absent', y: unaccounted, color: TV_COLORS.absent }
		].filter(function(p) { return p.y > 0; });
		if (!accountedSeries.length) {
			accountedSeries = [{ name: 'No staff', y: 1, color: '#334455' }];
		}

		if (tvCharts.accounted) tvCharts.accounted.destroy();
		tvCharts.accounted = Highcharts.chart('tv-chart-accounted', $.extend(true, {}, baseChartOpts(), {
			chart: { type: 'pie', height: 210 },
			plotOptions: {
				pie: {
					innerSize: '52%',
					borderWidth: 0,
					dataLabels: {
						enabled: true,
						format: '{point.name}: {point.y}',
						style: { color: chartTextColor(), fontSize: '10px', textOutline: 'none' }
					}
				}
			},
			series: [{ name: 'Staff', data: accountedSeries }]
		}));
	}

	function tickClock() {
		var now = new Date();
		$('#tv-clock').text(now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }));
		$('#tv-date').text(now.toLocaleDateString('en-GB', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));
	}

	function liveSourceLabel(source) {
		if (source === 'mobile') return 'Mobile';
		if (source === 'biotime') return 'BioTime';
		return 'Device';
	}

	function staffMeta(item) {
		return 'In ' + (item.time_in || '—') + ' · Out ' + (item.time_out || '—') + ' · ' + liveSourceLabel(item.source);
	}

	function buildFeedRow(item, animate) {
		var name = $('<div>').text(item.name || 'Staff').html();
		var badge = item.last_event === 'out'
			? '<span style="color:#f0ad4e;font-weight:700;font-size:0.7rem;margin-right:0.3rem;">OUT</span>'
			: '<span style="color:#20c198;font-weight:700;font-size:0.7rem;margin-right:0.3rem;">IN</span>';
		var $li = $('<li></li>').attr('data-activity-id', item.activity_id || '').attr('data-staff-id', item.ihris_pid || '')
			.append($('<span class="tv-feed-name"></span>').html(badge + name), $('<span class="tv-feed-meta"></span>').text(staffMeta(item)));
		if (animate) $li.addClass('tv-new');
		return $li;
	}

	function renderTvFeed(recent) {
		var $feed = $('#tv-feed');
		if (!recent || !recent.length) {
			$feed.html('<li class="tv-feed-empty">No check-ins yet today</li>');
			tvSeen = {};
			return;
		}
		if ($feed.find('.tv-feed-empty').length) {
			$feed.empty(); tvSeen = {};
			recent.slice(0, TV_FEED_MAX).forEach(function(item) {
				if (item.activity_id) tvSeen[item.activity_id] = true;
				$feed.append(buildFeedRow(item, false));
			});
			return;
		}
		var changed = false;
		recent.forEach(function(item) {
			if (!item.activity_id || !item.ihris_pid) return;
			var $existing = $feed.find('li[data-staff-id="' + item.ihris_pid + '"]');
			if ($existing.length) {
				if ($existing.attr('data-activity-id') === item.activity_id) return;
				changed = true; $existing.remove();
				tvSeen[item.activity_id] = true;
				$feed.prepend(buildFeedRow(item, true));
				return;
			}
			if (tvSeen[item.activity_id]) return;
			changed = true; tvSeen[item.activity_id] = true;
			$feed.prepend(buildFeedRow(item, true));
		});
		if (!changed) return;
		var $rows = $feed.find('li[data-staff-id]');
		if ($rows.length > TV_FEED_MAX) $rows.slice(TV_FEED_MAX).remove();
		setTimeout(function() { $feed.find('.tv-new').removeClass('tv-new'); }, 600);
	}

	function setText(id, val) {
		var $el = $(id);
		if ($el.length && val !== undefined && val !== null) $el.text(val);
	}

	function applyTvData(data) {
		if (!data || !data.ok) {
			var msg = (data && data.message) ? data.message : 'Unable to load facility data.';
			$('#tv-grid').html('<div class="tv-error"><i class="fas fa-exclamation-triangle fa-2x mb-3"></i><p>' + $('<div>').text(msg).html() + '</p></div>');
			$('#tv-updated').text('Error');
			return;
		}

		window.__tvLastData = data;

		if (data.facility_name) $('#tv-facility-name').text(data.facility_name);
		if (data.district) $('#tv-district').text(data.district);
		if (data.period_label) {
			$('#tv-period-label, #tv-period-label-2, #tv-period-label-3').text(data.period_label);
		}

		setText('#tv-checkins', data.clock_ins_today);
		setText('#tv-checkouts', data.clock_outs_today);
		setText('#tv-daily-hours', (data.daily_avg_hours != null ? data.daily_avg_hours : 0) + ' hrs');
		setText('#tv-period-hours', (data.avg_hours != null ? data.avg_hours : 0) + ' hrs');
		setText('#tv-mystaff', data.mystaff);
		setText('#tv-request', data.request);
		setText('#tv-monthly-present', data.monthly_present);
		setText('#tv-biotime-sync', data.biotime_last);
		setText('#tv-att-sum', data.attendance);
		setText('#tv-bio', data.biometrics);

		updateTvCharts(data);
		renderTvFeed(data.recent);

		var ago = 'just now';
		if (data.generated_at) {
			var sec = Math.max(0, Math.floor((Date.now() - Date.parse(data.generated_at)) / 1000));
			ago = sec < 12 ? 'just now' : (sec < 60 ? sec + 's ago' : Math.floor(sec / 60) + 'm ago');
		}
		var summary = (data.clock_ins_today || 0) + ' check-ins';
		if (data.clock_outs_today > 0) summary += ' · ' + data.clock_outs_today + ' check-outs';
		summary += ' · updated ' + ago;
		if (data.cached) summary += ' (cached)';
		$('#tv-updated').text(summary);
	}

	function loadTvData() {
		return $.ajax({
			url: tvDataUrl, type: 'GET', dataType: 'json', timeout: 30000, cache: false,
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		}).done(applyTvData).fail(function(xhr) {
			if (xhr && xhr.status === 401) {
				$('#tv-grid').html('<div class="tv-error"><p>Session expired. Re-open from the main dashboard.</p></div>');
			}
		});
	}

	$(document).ready(function() {
		initTheme();
		tickClock();
		setInterval(tickClock, 1000);

		$('#tv-theme-toggle').on('click', function() {
			var next = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
			applyTheme(next);
		});

		$('#tv-fullscreen').on('click', function() {
			var el = document.documentElement;
			if (!document.fullscreenElement && el.requestFullscreen) el.requestFullscreen();
			else if (document.exitFullscreen) document.exitFullscreen();
		});

		waitForHighcharts(function() {
			loadTvData();
			setInterval(loadTvData, TV_POLL_MS);
		});
	});
})();
</script>
