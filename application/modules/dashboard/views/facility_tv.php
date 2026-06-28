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
	--tv-surface-2: #1a2733;
	--tv-border: rgba(255,255,255,0.08);
	--tv-text: #eef4f7;
	--tv-muted: #8fa3ad;
	--tv-accent: #20c198;
	--tv-accent-2: #005662;
	--tv-warn: #f0ad4e;
	--tv-danger: #e74c3c;
	--tv-info: #17a2b8;
	--tv-present: #20c198;
	--tv-shadow: 0 12px 40px rgba(0,0,0,0.35);
}
[data-theme="light"] {
	--tv-bg: #eef3f6;
	--tv-surface: #ffffff;
	--tv-surface-2: #f7fafb;
	--tv-border: #d8e3e8;
	--tv-text: #1a2e35;
	--tv-muted: #6c7a80;
	--tv-shadow: 0 8px 28px rgba(0, 86, 98, 0.08);
}
html, body.tv-root {
	margin: 0;
	padding: 0;
	min-height: 100vh;
	background: var(--tv-bg);
	color: var(--tv-text);
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
	overflow-x: hidden;
}
.tv-screen {
	min-height: 100vh;
	display: flex;
	flex-direction: column;
	padding: 1.25rem 1.5rem 1rem;
	box-sizing: border-box;
}
.tv-topbar {
	display: flex;
	align-items: center;
	gap: 1rem;
	flex-wrap: wrap;
	margin-bottom: 1.25rem;
	padding-bottom: 1rem;
	border-bottom: 1px solid var(--tv-border);
}
.tv-brand {
	display: flex;
	align-items: center;
	gap: 0.85rem;
	min-width: 0;
	flex: 1 1 280px;
}
.tv-brand img {
	width: 52px;
	height: 52px;
	object-fit: contain;
}
.tv-brand h1 {
	margin: 0;
	font-size: clamp(1.2rem, 2.2vw, 1.75rem);
	font-weight: 700;
	line-height: 1.2;
}
.tv-brand p {
	margin: 0.2rem 0 0;
	color: var(--tv-muted);
	font-size: 0.92rem;
}
.tv-clock {
	font-size: clamp(1.4rem, 2.5vw, 2rem);
	font-weight: 700;
	font-variant-numeric: tabular-nums;
	letter-spacing: 0.02em;
}
.tv-live-pill {
	display: inline-flex;
	align-items: center;
	gap: 0.45rem;
	padding: 0.35rem 0.75rem;
	border-radius: 999px;
	background: rgba(32, 193, 152, 0.15);
	color: var(--tv-accent);
	font-size: 0.78rem;
	font-weight: 700;
	letter-spacing: 0.08em;
}
.tv-live-pill .dot {
	width: 9px;
	height: 9px;
	border-radius: 50%;
	background: var(--tv-accent);
	animation: tvPulse 1.8s ease-in-out infinite;
}
@keyframes tvPulse {
	0% { box-shadow: 0 0 0 0 rgba(32, 193, 152, 0.7); }
	70% { box-shadow: 0 0 0 8px rgba(32, 193, 152, 0); }
	100% { box-shadow: 0 0 0 0 rgba(32, 193, 152, 0); }
}
.tv-toolbar {
	display: flex;
	align-items: center;
	gap: 0.5rem;
	margin-left: auto;
}
.tv-btn {
	border: 1px solid var(--tv-border);
	background: var(--tv-surface);
	color: var(--tv-text);
	border-radius: 8px;
	padding: 0.45rem 0.75rem;
	font-size: 0.82rem;
	cursor: pointer;
}
.tv-btn:hover {
	border-color: var(--tv-accent);
	color: var(--tv-accent);
}
.tv-grid {
	display: grid;
	grid-template-columns: repeat(12, 1fr);
	gap: 1rem;
	flex: 1;
}
.tv-card {
	background: var(--tv-surface);
	border: 1px solid var(--tv-border);
	border-radius: 14px;
	padding: 1rem 1.1rem;
	box-shadow: var(--tv-shadow);
}
.tv-card.hero {
	grid-column: span 2;
	min-height: 110px;
	display: flex;
	flex-direction: column;
	justify-content: center;
}
.tv-card.hero .value {
	font-size: clamp(2rem, 4vw, 3rem);
	font-weight: 800;
	line-height: 1;
	font-variant-numeric: tabular-nums;
}
.tv-card.hero .label {
	margin-top: 0.35rem;
	color: var(--tv-muted);
	font-size: 0.88rem;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.05em;
}
.tv-card.hero.present .value { color: var(--tv-present); }
.tv-card.hero.absent .value { color: var(--tv-danger); }
.tv-card.hero.off .value { color: var(--tv-warn); }
.tv-card.hero.leave .value { color: var(--tv-info); }
.tv-card.hero.staff .value { color: var(--tv-accent-2); }
[data-theme="dark"] .tv-card.hero.staff .value { color: #7dd3fc; }
.tv-card.metric {
	grid-column: span 2;
}
.tv-card.metric .value {
	font-size: 1.65rem;
	font-weight: 700;
	font-variant-numeric: tabular-nums;
}
.tv-card.metric .label {
	color: var(--tv-muted);
	font-size: 0.8rem;
	margin-top: 0.25rem;
}
.tv-card.wide {
	grid-column: span 4;
}
.tv-card.full {
	grid-column: span 12;
}
.tv-feed {
	list-style: none;
	margin: 0;
	padding: 0;
	max-height: 220px;
	overflow: hidden;
}
.tv-feed li {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 0.55rem 0;
	border-bottom: 1px solid var(--tv-border);
	font-size: 0.95rem;
}
.tv-feed li:last-child { border-bottom: none; }
.tv-feed-name {
	font-weight: 600;
	min-width: 0;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	padding-right: 1rem;
}
.tv-feed-meta {
	color: var(--tv-muted);
	font-size: 0.82rem;
	white-space: nowrap;
}
.tv-feed li.tv-new {
	animation: tvSlideIn 0.55s cubic-bezier(0.22, 1, 0.36, 1);
}
@keyframes tvSlideIn {
	from { opacity: 0; transform: translateY(-100%); }
	to { opacity: 1; transform: translateY(0); }
}
.tv-section-title {
	margin: 0 0 0.75rem;
	font-size: 0.95rem;
	font-weight: 700;
	letter-spacing: 0.04em;
	text-transform: uppercase;
	color: var(--tv-muted);
}
.tv-footer {
	margin-top: 1rem;
	padding-top: 0.75rem;
	border-top: 1px solid var(--tv-border);
	display: flex;
	justify-content: space-between;
	gap: 1rem;
	flex-wrap: wrap;
	color: var(--tv-muted);
	font-size: 0.8rem;
}
.tv-error {
	grid-column: span 12;
	text-align: center;
	padding: 3rem 1rem;
	color: var(--tv-muted);
}
@media (max-width: 1200px) {
	.tv-card.hero, .tv-card.metric { grid-column: span 4; }
	.tv-card.wide { grid-column: span 6; }
}
@media (max-width: 768px) {
	.tv-card.hero, .tv-card.metric, .tv-card.wide { grid-column: span 12; }
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
			<button type="button" class="tv-btn" id="tv-theme-toggle" title="Toggle light/dark mode">
				<i class="fas fa-adjust"></i> Theme
			</button>
			<button type="button" class="tv-btn" id="tv-fullscreen" title="Fullscreen">
				<i class="fas fa-expand"></i>
			</button>
		</div>
	</header>

	<div class="tv-grid" id="tv-grid">
		<div class="tv-card hero present"><div class="value" id="tv-present">—</div><div class="label">Present Today</div></div>
		<div class="tv-card hero absent"><div class="value" id="tv-absent">—</div><div class="label">Absent</div></div>
		<div class="tv-card hero off"><div class="value" id="tv-offduty">—</div><div class="label">Off Duty</div></div>
		<div class="tv-card hero leave"><div class="value" id="tv-leave">—</div><div class="label">On Leave</div></div>
		<div class="tv-card hero staff"><div class="value" id="tv-mystaff">—</div><div class="label">My Staff</div></div>
		<div class="tv-card hero staff"><div class="value" id="tv-rate">—</div><div class="label">Attendance %</div></div>

		<div class="tv-card metric"><div class="value" id="tv-checkins">—</div><div class="label">Check-ins Today</div></div>
		<div class="tv-card metric"><div class="value" id="tv-checkouts">—</div><div class="label">Check-outs Today</div></div>
		<div class="tv-card metric"><div class="value" id="tv-daily-hours">—</div><div class="label">Avg Hours Today</div></div>
		<div class="tv-card metric"><div class="value" id="tv-period-hours">—</div><div class="label">Avg Hours (<span id="tv-period-label">Month</span>)</div></div>
		<div class="tv-card metric wide"><div class="value" id="tv-monthly-present">—</div><div class="label">Present Staff-days (<span id="tv-period-label-2">Month</span>)</div></div>
		<div class="tv-card metric wide"><div class="value" id="tv-request">—</div><div class="label">Official / Workshop Today</div></div>

		<div class="tv-card full">
			<h2 class="tv-section-title"><i class="fas fa-broadcast-tower mr-1"></i> Live Check-ins &amp; Check-outs</h2>
			<ul class="tv-feed" id="tv-feed">
				<li class="tv-feed-empty">Waiting for activity…</li>
			</ul>
		</div>

		<div class="tv-card metric"><div class="value" id="tv-departments">—</div><div class="label">Departments</div></div>
		<div class="tv-card metric"><div class="value" id="tv-jobs">—</div><div class="label">Jobs</div></div>
		<div class="tv-card metric"><div class="value" id="tv-cadres">—</div><div class="label">Cadres</div></div>
		<div class="tv-card metric"><div class="value" id="tv-bio">—</div><div class="label">BioTime Devices</div></div>
		<div class="tv-card metric wide"><div class="value" id="tv-biotime-sync" style="font-size:1rem;">—</div><div class="label">BioTime Last Sync</div></div>
		<div class="tv-card metric wide"><div class="value" id="tv-att-sum" style="font-size:1rem;">—</div><div class="label">Last Attendance Summary</div></div>
	</div>

	<footer class="tv-footer">
		<span id="tv-updated">Connecting…</span>
		<span>iHRIS Attendance · Facility display uses your logged-in session</span>
	</footer>
</div>

<script type="text/javascript">
(function() {
	var TV_POLL_MS = <?php echo (int) $tv_poll; ?> * 1000;
	var TV_FEED_MAX = 12;
	var tvSeen = {};
	var tvDataUrl = '<?php echo base_url('dashboard/facilityTvData'); ?>';

	function applyTheme(theme) {
		document.documentElement.setAttribute('data-theme', theme);
		try { localStorage.setItem('facility_tv_theme', theme); } catch (e) {}
	}

	function initTheme() {
		var saved = 'dark';
		try {
			saved = localStorage.getItem('facility_tv_theme') || 'dark';
		} catch (e) {}
		if (saved !== 'light') saved = 'dark';
		applyTheme(saved);
	}

	function tickClock() {
		var now = new Date();
		var opts = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
		$('#tv-clock').text(now.toLocaleTimeString('en-GB', opts));
		var dateOpts = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
		$('#tv-date').text(now.toLocaleDateString('en-GB', dateOpts));
	}

	function liveSourceLabel(source) {
		if (source === 'mobile') return 'Mobile';
		if (source === 'biotime') return 'BioTime';
		return 'Device';
	}

	function staffMeta(item) {
		var inT = item.time_in || '—';
		var outT = item.time_out || '—';
		return 'In ' + inT + ' · Out ' + outT + ' · ' + liveSourceLabel(item.source);
	}

	function buildFeedRow(item, animate) {
		var name = $('<div>').text(item.name || 'Staff').html();
		var badge = item.last_event === 'out'
			? '<span style="color:#f0ad4e;font-weight:700;font-size:0.72rem;margin-right:0.35rem;">OUT</span>'
			: '<span style="color:#20c198;font-weight:700;font-size:0.72rem;margin-right:0.35rem;">IN</span>';
		var $li = $('<li></li>').attr('data-activity-id', item.activity_id || '')
			.attr('data-staff-id', item.ihris_pid || '')
			.append(
				$('<span class="tv-feed-name"></span>').html(badge + name),
				$('<span class="tv-feed-meta"></span>').text(staffMeta(item))
			);
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
			$feed.empty();
			tvSeen = {};
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
				changed = true;
				$existing.remove();
				tvSeen[item.activity_id] = true;
				$feed.prepend(buildFeedRow(item, true));
				return;
			}
			if (tvSeen[item.activity_id]) return;
			changed = true;
			tvSeen[item.activity_id] = true;
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

		if (data.facility_name) $('#tv-facility-name').text(data.facility_name);
		if (data.district) $('#tv-district').text(data.district);
		if (data.period_label) {
			$('#tv-period-label, #tv-period-label-2').text(data.period_label);
		}

		setText('#tv-present', data.present);
		setText('#tv-absent', data.absent);
		setText('#tv-offduty', data.offduty);
		setText('#tv-leave', data.leave);
		setText('#tv-request', data.request);
		setText('#tv-mystaff', data.mystaff);
		setText('#tv-rate', (data.attendance_rate != null ? data.attendance_rate : 0) + '%');
		setText('#tv-checkins', data.clock_ins_today);
		setText('#tv-checkouts', data.clock_outs_today);
		setText('#tv-daily-hours', (data.daily_avg_hours != null ? data.daily_avg_hours : 0) + ' hrs');
		setText('#tv-period-hours', (data.avg_hours != null ? data.avg_hours : 0) + ' hrs');
		setText('#tv-monthly-present', data.monthly_present);
		setText('#tv-departments', data.departments);
		setText('#tv-jobs', data.jobs);
		setText('#tv-cadres', data.cadres);
		setText('#tv-bio', data.biometrics);
		setText('#tv-biotime-sync', data.biotime_last);
		setText('#tv-att-sum', data.attendance);

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
			url: tvDataUrl,
			type: 'GET',
			dataType: 'json',
			timeout: 30000,
			cache: false,
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		}).done(applyTvData).fail(function(xhr) {
			if (xhr && xhr.status === 401) {
				$('#tv-grid').html('<div class="tv-error"><p>Session expired. Re-open this page from the main dashboard while logged in.</p></div>');
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
			if (!document.fullscreenElement && el.requestFullscreen) {
				el.requestFullscreen();
			} else if (document.exitFullscreen) {
				document.exitFullscreen();
			}
		});

		loadTvData();
		setInterval(loadTvData, TV_POLL_MS);
	});
})();
</script>
