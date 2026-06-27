<!-- Modern Dashboard Design -->
<style>
.dash-page {
  --dash-teal: #005662;
  --dash-teal-dark: #00424d;
  --dash-mint: #20c198;
  --dash-mint-soft: #e8f6f3;
  --primary-color: #005662;
  --secondary-color: #fff;
  --success-color: #20c198;
  --warning-color: #f0ad4e;
  --danger-color: #e74c3c;
  --info-color: #17a2b8;
  --light-color: #f8fafb;
  --dark-color: #1a2e35;
  --border-color: #e3eaec;
  --text-muted: #6c7a80;
  --dash-radius: 12px;
  --shadow-light: 0 2px 12px rgba(0, 86, 98, 0.06);
  --shadow-medium: 0 8px 28px rgba(0, 86, 98, 0.12);
  --shadow-heavy: 0 16px 40px rgba(0, 86, 98, 0.14);
}

/* Hero */
.dash-page .dash-hero {
  background: linear-gradient(135deg, var(--dash-teal) 0%, var(--dash-mint) 100%);
  color: #fff;
  padding: 1.75rem 2rem;
  border-radius: var(--dash-radius);
  margin-bottom: 1.25rem;
  box-shadow: var(--shadow-medium);
  position: relative;
  overflow: hidden;
}
.dash-page .dash-hero::before {
  content: '';
  position: absolute;
  top: -40%;
  right: -5%;
  width: 220px;
  height: 220px;
  background: rgba(255,255,255,0.12);
  border-radius: 50%;
  pointer-events: none;
}
.dash-page .dash-hero::after {
  content: '';
  position: absolute;
  bottom: -60%;
  left: 10%;
  width: 160px;
  height: 160px;
  background: rgba(255,255,255,0.06);
  border-radius: 50%;
  pointer-events: none;
}
.dash-page .dash-hero-inner {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
}
.dash-page .dashboard-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0 0 0.35rem;
  color: #fff;
}
.dash-page .dashboard-subtitle {
  font-size: 0.92rem;
  margin: 0;
  color: rgba(255,255,255,0.9);
  font-weight: 400;
}
.dash-page .dash-hero-icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  background: rgba(255,255,255,0.18);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  flex-shrink: 0;
}
.dash-page .dash-hero-badge {
  background: rgba(255,255,255,0.15);
  border: 1px solid rgba(255,255,255,0.25);
  border-radius: 999px;
  padding: 0.35rem 0.85rem;
  font-size: 0.78rem;
  font-weight: 600;
  letter-spacing: 0.04em;
}

/* Sync stat cards */
.dash-page .stat-card {
  background: var(--secondary-color);
  border-radius: var(--dash-radius);
  padding: 1.25rem 1.35rem;
  box-shadow: var(--shadow-light);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  border: 1px solid var(--border-color);
  position: relative;
  overflow: hidden;
  min-height: 150px;
  display: flex;
  flex-direction: column;
}
.dash-page .stat-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-medium);
}
.dash-page .stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: var(--dash-teal);
  border-radius: var(--dash-radius) 0 0 var(--dash-radius);
}
.dash-page .stat-card.success::before { background: var(--dash-mint); }
.dash-page .stat-card.warning::before { background: var(--warning-color); }
.dash-page .stat-card.info::before { background: var(--info-color); }
.dash-page .stat-card.danger::before { background: var(--danger-color); }

.dash-page .stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  color: #fff;
  margin-bottom: 0.85rem;
}
.dash-page .stat-icon.primary { background: linear-gradient(135deg, var(--dash-teal), var(--dash-teal-dark)); }
.dash-page .stat-icon.success { background: linear-gradient(135deg, var(--dash-mint), #1aab82); }
.dash-page .stat-icon.warning { background: linear-gradient(135deg, #f0ad4e, #ec971f); }
.dash-page .stat-icon.info { background: linear-gradient(135deg, #17a2b8, #138496); }
.dash-page .stat-icon.danger { background: linear-gradient(135deg, #e74c3c, #c0392b); }

.dash-page .stat-number {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--dark-color);
  margin-bottom: 0.35rem;
  line-height: 1.35;
  word-break: break-word;
}
.dash-page .stat-label {
  font-size: 0.78rem;
  color: var(--text-muted);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-top: auto;
}

/* Status cards */
.dash-page .status-card {
  background: var(--secondary-color);
  border-radius: var(--dash-radius);
  padding: 1.35rem 1.5rem;
  box-shadow: var(--shadow-light);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  border: 1px solid var(--border-color);
  height: 100%;
}
.dash-page .status-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-medium);
}
.dash-page .status-header {
  display: flex;
  align-items: center;
  margin-bottom: 1.25rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid var(--dash-mint-soft);
}
.dash-page .status-icon {
  width: 46px;
  height: 46px;
  border-radius: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.15rem;
  color: #fff;
  margin-right: 0.85rem;
  flex-shrink: 0;
}
.dash-page .status-icon.primary { background: linear-gradient(135deg, var(--dash-teal), var(--dash-mint)); }
.dash-page .status-icon.success { background: linear-gradient(135deg, var(--dash-mint), #1aab82); }
.dash-page .status-icon.info { background: linear-gradient(135deg, #17a2b8, var(--dash-teal)); }
.dash-page .status-title {
  font-size: 1.05rem;
  font-weight: 600;
  color: var(--dash-teal);
  margin: 0;
  line-height: 1.3;
}
.dash-page .status-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 0.5rem;
  margin: 0 -0.5rem;
  border-radius: 8px;
  transition: background 0.15s ease;
}
.dash-page .status-item:hover {
  background: var(--dash-mint-soft);
}
.dash-page .status-item + .status-item {
  border-top: 1px solid var(--border-color);
}
.dash-page .status-info {
  display: flex;
  align-items: center;
  min-width: 0;
}
.dash-page .status-indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-right: 0.75rem;
  flex-shrink: 0;
  box-shadow: 0 0 0 3px rgba(0,0,0,0.04);
}
.dash-page .status-indicator.present { background: var(--dash-mint); }
.dash-page .status-indicator.offduty { background: var(--warning-color); }
.dash-page .status-indicator.leave { background: var(--danger-color); }
.dash-page .status-indicator.request { background: var(--info-color); }
.dash-page .status-indicator.absent { background: #95a5a6; }
.dash-page .status-indicator.info { background: var(--dash-teal); }
.dash-page .status-indicator.warning { background: var(--warning-color); }
.dash-page .status-text {
  font-size: 0.88rem;
  color: var(--text-muted);
  margin: 0;
  font-weight: 500;
}
.dash-page .status-value {
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--dash-teal);
  min-width: 2.5rem;
  text-align: right;
  padding: 0.15rem 0.5rem;
  border-radius: 8px;
}
.dash-page .status-unit {
  font-size: 0.8rem;
  color: var(--text-muted);
  margin-left: 0.25rem;
}

/* Filter card */
.dash-page .dash-filter-card {
  border: 1px solid var(--border-color);
  border-radius: var(--dash-radius);
  box-shadow: var(--shadow-light);
  overflow: hidden;
}
.dash-page .dash-filter-card > .card-header {
  background: var(--dash-mint-soft);
  border-bottom: 1px solid var(--border-color);
  padding: 0.9rem 1.25rem;
}
.dash-page .dash-filter-card .card-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--dash-teal);
  margin: 0;
}
.dash-page .dash-filter-card .card-title i {
  color: var(--dash-mint);
}
.dash-page .dash-filter-card .form-group label {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--dash-teal);
  text-transform: uppercase;
  letter-spacing: 0.03em;
}
.dash-page .dash-filter-card .form-control {
  border-radius: 8px;
  border-color: var(--border-color);
}
.dash-page .dash-filter-card .form-control:focus {
  border-color: var(--dash-mint);
  box-shadow: 0 0 0 0.2rem rgba(32, 193, 152, 0.2);
}
.dash-page .dash-btn-primary {
  background: linear-gradient(135deg, var(--dash-teal), var(--dash-mint));
  border: none;
  border-radius: 8px;
  font-weight: 600;
  padding: 0.45rem 1.25rem;
  box-shadow: 0 4px 12px rgba(0, 86, 98, 0.25);
}
.dash-page .dash-btn-primary:hover {
  background: linear-gradient(135deg, var(--dash-teal-dark), var(--dash-teal));
  box-shadow: 0 4px 16px rgba(0, 86, 98, 0.35);
}
.dash-page .dash-btn-outline {
  border-radius: 8px;
  font-weight: 600;
  color: var(--dash-teal);
  border-color: var(--border-color);
}
.dash-page .dash-btn-outline:hover {
  background: var(--dash-mint-soft);
  color: var(--dash-teal-dark);
  border-color: var(--dash-mint);
}

/* Section headings */
.dash-page .dash-section-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--dash-teal);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.dash-page .dash-section-title i {
  color: var(--dash-mint);
}

/* Calendar */
.dash-page .calendar-card {
  background: var(--secondary-color);
  border-radius: var(--dash-radius);
  box-shadow: var(--shadow-light);
  transition: box-shadow 0.25s ease;
  border: 1px solid var(--border-color);
  overflow: hidden;
}
.dash-page .calendar-card:hover {
  box-shadow: var(--shadow-medium);
}
.dash-page .calendar-header {
  background: linear-gradient(135deg, var(--dash-teal) 0%, var(--dash-mint) 100%);
  padding: 1.1rem 1.5rem;
  border-bottom: none;
}
.dash-page .calendar-title {
  font-size: 1.05rem;
  font-weight: 600;
  color: #fff;
  margin: 0;
  display: flex;
  align-items: center;
}
.dash-page .calendar-title i {
  margin-right: 0.5rem;
  opacity: 0.9;
}
.dash-page .calendar-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 0.65rem 1rem;
  padding: 0.85rem 1.5rem;
  background: var(--dash-mint-soft);
  border-bottom: 1px solid var(--border-color);
}
.dash-page .legend-item {
  display: flex;
  align-items: center;
  gap: 0.45rem;
}
.dash-page .legend-color {
  width: 12px;
  height: 12px;
  border-radius: 3px;
  border: 1px solid rgba(0,0,0,0.08);
}
.dash-page .legend-text {
  font-size: 0.78rem;
  color: var(--text-muted);
  font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
  .dash-page .dash-hero { padding: 1.35rem 1.25rem; }
  .dash-page .dashboard-title { font-size: 1.3rem; }
  .dash-page .stat-card, .dash-page .status-card, .dash-page .calendar-card { margin-bottom: 1rem; }
}
@media (max-width: 576px) {
  .dash-page .dash-hero { padding: 1.15rem 1rem; }
  .dash-page .dash-hero-icon { width: 44px; height: 44px; }
  .dash-page .stat-card, .dash-page .status-card { padding: 1rem; }
}

/* Loading Animations */
.loading-pulse {
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

/* Live panel */
.dash-page .dashboard-live-panel {
  background: #fff;
  border: 1px solid var(--border-color);
  border-radius: var(--dash-radius);
  box-shadow: var(--shadow-light);
  overflow: hidden;
}
.dash-page .dashboard-live-bar {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.7rem 1.15rem;
  background: linear-gradient(90deg, var(--dash-teal) 0%, var(--dash-mint) 100%);
  color: #fff;
  font-size: 0.85rem;
}

.live-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #20c198;
  box-shadow: 0 0 0 0 rgba(32, 193, 152, 0.7);
  animation: livePulseDot 1.8s ease-in-out infinite;
}

.live-dot.stale {
  background: #ffc107;
  animation: none;
}

.live-dot.offline {
  background: #adb5bd;
  animation: none;
}

@keyframes livePulseDot {
  0% { box-shadow: 0 0 0 0 rgba(32, 193, 152, 0.7); }
  70% { box-shadow: 0 0 0 8px rgba(32, 193, 152, 0); }
  100% { box-shadow: 0 0 0 0 rgba(32, 193, 152, 0); }
}

.live-label {
  font-weight: 700;
  letter-spacing: 0.08em;
  font-size: 0.75rem;
}

.live-updated {
  opacity: 0.9;
}

.dash-page .live-feed {
  list-style: none;
  margin: 0;
  padding: 0.5rem 1.15rem;
  max-height: 140px;
  overflow-y: auto;
  background: var(--light-color);
}
.dash-page .live-feed li {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.45rem 0.5rem;
  margin: 0 -0.5rem;
  border-radius: 6px;
  font-size: 0.85rem;
}
.dash-page .live-feed li + li {
  border-top: 1px solid var(--border-color);
}
.dash-page .live-feed-name {
  font-weight: 600;
  color: var(--dark-color);
}
.dash-page .live-feed-meta {
  color: var(--text-muted);
  font-size: 0.78rem;
  white-space: nowrap;
  margin-left: 1rem;
}
.dash-page .live-feed-empty {
  color: var(--text-muted);
  font-style: italic;
  justify-content: flex-start !important;
}
.dash-page .status-value.live-flash {
  animation: liveValueFlash 1.2s ease;
}

@keyframes liveValueFlash {
  0% { background: rgba(32, 193, 152, 0.35); }
  100% { background: transparent; }
}

/* Smooth Transitions */
.fade-in {
  animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- Main Dashboard Content -->
<section class="content dash-page">
  <div class="container-fluid">

    <?php
      $dash_user = trim((string) $this->session->userdata('firstname'));
      if ($dash_user === '') {
        $dash_user = trim((string) $this->session->userdata('username'));
      }
      $dash_greeting = $dash_user !== '' ? 'Welcome back, ' . htmlspecialchars($dash_user, ENT_QUOTES, 'UTF-8') . '!' : 'Welcome back!';
    ?>

    <!-- Dashboard Header -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="dash-hero">
          <div class="dash-hero-inner">
            <div class="d-flex align-items-center">
              <div class="dash-hero-icon mr-3">
                <i class="fas fa-tachometer-alt"></i>
              </div>
              <div>
                <h1 class="dashboard-title"><?php echo $dash_greeting; ?></h1>
                <p class="dashboard-subtitle">Here's what's happening with your <?php echo strtolower(entity_label('facility')); ?> today.</p>
              </div>
            </div>
            <span class="dash-hero-badge"><i class="fas fa-circle text-success mr-1" style="font-size:0.5rem;vertical-align:middle;"></i> Attendance Dashboard</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Live attendance pulse -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="dashboard-live-panel">
          <div class="dashboard-live-bar">
            <span class="live-dot" id="live-dot"></span>
            <span class="live-label">LIVE</span>
            <span class="live-updated" id="live-updated">Connecting…</span>
            <span class="live-cache-hint ml-auto small" id="live-cache-hint"></span>
          </div>
          <ul class="live-feed" id="live-feed">
            <li class="live-feed-empty">Waiting for check-ins today…</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Dashboard Filters (collapsed by default) -->
    <?php
      $sel_month = $this->session->userdata('month') ?: date('m');
      $sel_year = $this->session->userdata('year') ?: date('Y');
      $sel_empid = $this->session->userdata('dashboard_empid') ?: '';
      $sel_region = $this->session->userdata('dashboard_region') ?: '';
      $sel_district = $this->session->userdata('dashboard_district') ?: '';
      $sel_institution = $this->session->userdata('dashboard_institution_type') ?: '';
      $sel_cadre = $this->session->userdata('dashboard_cadre') ?: '';
      $sel_nat_facility = $this->session->userdata('dashboard_facility_filter') ?: '';
      $permissions_for_filters = $this->session->userdata('permissions') ?: [];
      $role_for_filters = (string) $this->session->userdata('role');
      $is_role10 = in_array('10', $permissions_for_filters) || ($role_for_filters === 'District Admin') || ($role_for_filters === 'Regional Admin');
      $sel_facility = $this->session->userdata('dashboard_facility') ?: ($this->session->userdata('facility') ?: '');
      $years = [];
      $cy = (int) date('Y');
      for ($y = $cy + 1; $y >= $cy - 5; $y--) { $years[] = $y; }
      $months = [
        '01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June',
        '07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'
      ];
    ?>
    <div class="row mb-3">
      <div class="col-12">
        <div class="card card-outline dash-filter-card collapsed-card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Dashboard Filters</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <p class="text-muted small mb-3">Scope national charts and rates. Leave filters empty for all Uganda.</p>
            <div class="form-row">
              <div class="form-group col-md-4 col-lg-2">
                <label for="dash_region">Region</label>
                <select id="dash_region" class="form-control dash-filter-s2">
                  <option value="">All Regions</option>
                </select>
              </div>
              <div class="form-group col-md-4 col-lg-2">
                <label for="dash_district">District</label>
                <select id="dash_district" class="form-control dash-filter-s2">
                  <option value="">All Districts</option>
                </select>
              </div>
              <div class="form-group col-md-4 col-lg-2">
                <label for="dash_nat_facility"><?php echo entity_label('facility'); ?></label>
                <select id="dash_nat_facility" class="form-control dash-filter-s2">
                  <option value="">All <?php echo entity_label('facility', true); ?></option>
                </select>
              </div>
              <div class="form-group col-md-4 col-lg-2">
                <label for="dash_institution_type">Institution Type</label>
                <select id="dash_institution_type" class="form-control dash-filter-s2">
                  <option value="">All</option>
                </select>
              </div>
              <div class="form-group col-md-4 col-lg-2">
                <label for="dash_cadre">Cadre</label>
                <select id="dash_cadre" class="form-control dash-filter-s2">
                  <option value="">All</option>
                </select>
              </div>
            </div>
            <hr class="my-2">
            <div class="form-row">
              <div class="form-group col-md-3">
                <label for="dash_month">Month</label>
                <select id="dash_month" class="form-control">
                  <?php foreach ($months as $m => $label): ?>
                    <option value="<?php echo $m; ?>" <?php echo ($sel_month == $m) ? 'selected' : ''; ?>>
                      <?php echo $label; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group col-md-3">
                <label for="dash_year">Year</label>
                <select id="dash_year" class="form-control">
                  <?php foreach ($years as $y): ?>
                    <option value="<?php echo $y; ?>" <?php echo ((string)$sel_year === (string)$y) ? 'selected' : ''; ?>>
                      <?php echo $y; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php if ($is_role10): ?>
              <div class="form-group col-md-3">
                <label for="dash_facility">Facility</label>
                <select id="dash_facility" class="form-control" style="width:100%;">
                  <option value="">All Facilities</option>
                  <?php if (!empty($sel_facility)): ?>
                    <option value="<?php echo $sel_facility; ?>" selected><?php echo $sel_facility; ?></option>
                  <?php endif; ?>
                </select>
              </div>
              <div class="form-group col-md-3">
                <label for="dash_empid">Name</label>
                <select id="dash_empid" class="form-control" style="width:100%;">
                  <option value="">All Staff</option>
                </select>
              </div>
              <?php else: ?>
              <div class="form-group col-md-6">
                <label for="dash_empid">Name</label>
                <select id="dash_empid" class="form-control" style="width:100%;">
                  <option value="">All Staff</option>
                </select>
              </div>
              <?php endif; ?>
            </div>
            <div class="d-flex justify-content-end">
              <button id="dash_apply" type="button" class="btn btn-primary dash-btn-primary mr-2">
                <i class="fas fa-check"></i> Apply
              </button>
              <button id="dash_reset" type="button" class="btn btn-outline-secondary dash-btn-outline">
                Reset
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- National attendance overview -->
    <div class="row mb-4">
      <div class="col-md-6 col-lg-3 mb-3">
        <div class="stat-card success">
          <div class="stat-icon success"><i class="fas fa-user-check"></i></div>
          <div class="stat-number" id="national_attendance_rate"><i class="fas fa-spinner fa-spin loading-pulse"></i></div>
          <div class="stat-label">National Attendance Rate</div>
          <small class="text-muted" id="national_rate_month"></small>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 mb-3">
        <div class="stat-card danger">
          <div class="stat-icon danger"><i class="fas fa-user-times"></i></div>
          <div class="stat-number" id="national_absenteeism_rate"><i class="fas fa-spinner fa-spin loading-pulse"></i></div>
          <div class="stat-label">National Absenteeism Rate</div>
          <small class="text-muted" id="national_absent_month"></small>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 mb-3">
        <div class="stat-card info">
          <div class="stat-icon info"><i class="fas fa-clock"></i></div>
          <div class="stat-number" id="national_avg_hours"><i class="fas fa-spinner fa-spin loading-pulse"></i></div>
          <div class="stat-label">Avg Hours Worked</div>
          <small class="text-muted" id="national_avg_hours_month"></small>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 mb-3">
        <div class="stat-card warning">
          <div class="stat-icon warning"><i class="fas fa-calendar-day"></i></div>
          <div class="stat-number" id="national_present_days"><i class="fas fa-spinner fa-spin loading-pulse"></i></div>
          <div class="stat-label">Staff-Days Present</div>
        </div>
      </div>
    </div>

    <!-- Statistics Row -->
    <div class="row mb-4">
 		<?php
			$permissions = $this->session->userdata('permissions');
			if (in_array('33', $permissions)) {
          $display = "block";
			} else {
				$display = "none";
			}
			?>
      
      <!-- iHRIS Sync -->
      <div class="col-12 col-sm-6 col-md-3 mb-3" style="display:<?php echo $display; ?>">
        <div class="stat-card info">
          <div class="stat-icon info">
            <i class="fas fa-sync-alt"></i>
          </div>
          <div class="stat-number" id="ihris_sync">
            <i class="fas fa-spinner fa-spin loading-pulse"></i>
          </div>
          <div class="stat-label">iHRIS Sync Status</div>
        </div>
 					</div>

      <!-- Last Attendance Sum -->
      <div class="col-12 col-sm-6 col-md-3 mb-3" style="display:<?php echo $display; ?>">
        <div class="stat-card success">
          <div class="stat-icon success">
            <i class="fas fa-clock"></i>
 				</div>
          <div class="stat-number" id="attendance">
            <i class="fas fa-spinner fa-spin loading-pulse"></i>
 			</div>
          <div class="stat-label">Last Attendance Sum</div>
 					</div>
 				</div>

      <!-- Last Roster Sum -->
      <div class="col-12 col-sm-6 col-md-3 mb-3" style="display:<?php echo $display; ?>">
        <div class="stat-card warning">
          <div class="stat-icon warning">
            <i class="fas fa-calendar"></i>
 			</div>
          <div class="stat-number" id="roster">
            <i class="fas fa-spinner fa-spin loading-pulse"></i>
 					</div>
          <div class="stat-label">Last Roster Sum</div>
 				</div>
 			</div>

      <!-- BioTime Last Sync -->
      <div class="col-12 col-sm-6 col-md-3 mb-3" style="display:<?php echo $display; ?>">
        <div class="stat-card danger">
          <div class="stat-icon danger">
            <i class="fas fa-fingerprint"></i>
 					</div>
          <div class="stat-number" id="biotime_last">
            <i class="fas fa-spinner fa-spin loading-pulse"></i>
 				</div>
          <div class="stat-label">BioTime Last Sync</div>
 			</div>
 		</div>
 					</div>

    <!-- Hidden base_url for calendar functionality -->
    <span class="base_url" style="display: none;"><?php echo base_url(); ?></span>
    
   

    <!-- Status Cards Row -->
    <div class="row mb-4">
      <!-- Daily Attendance Status -->
      <div class="col-xl-4 col-md-6 mb-3">
        <div class="status-card">
          <div class="status-header">
            <div class="status-icon primary">
 								<i class="fas fa-calendar-check"></i>
            </div>
            <h3 class="status-title">Daily Attendance Status <small class="text-muted" id="daily_status_date"></small></h3>
          </div>
          
          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator present"></div>
              <p class="status-text">Present</p>
            </div>
            <div class="status-value" id="present">
              <i class="fas fa-spinner fa-spin loading-pulse"></i>
            </div>
          </div>
          
          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator offduty"></div>
              <p class="status-text">Off Duty</p>
            </div>
            <div class="status-value" id="offduty">
              <i class="fas fa-spinner fa-spin loading-pulse"></i>
            </div>
          </div>
          
          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator absent"></div>
              <p class="status-text">Absent</p>
            </div>
            <div class="status-value" id="absent">
              <i class="fas fa-spinner fa-spin loading-pulse"></i>
						</div>
						</div>
          
          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator leave"></div>
              <p class="status-text">On Leave</p>
 						</div>
            <div class="status-value" id="leave">
              <i class="fas fa-spinner fa-spin loading-pulse"></i>
 						</div>
 					</div>
 				</div>
 			</div>

      <!-- Monthly Attendance Stats (replaces Out of Station Requests) -->
      <div class="col-xl-4 col-md-6 mb-3">
        <div class="status-card">
          <div class="status-header">
            <div class="status-icon info">
              <i class="fas fa-chart-pie"></i>
            </div>
            <h3 class="status-title">Monthly Attendance Stats</h3>
            <small class="text-muted ml-2" id="monthly_label"></small>
          </div>
          
          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator warning"></div>
              <p class="status-text">Present (Staff-days)</p>
            </div>
            <div class="status-value" id="monthly_present">
              <i class="fas fa-spinner fa-spin loading-pulse"></i>
            </div>
          </div>
          
          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator present"></div>
              <p class="status-text">Off Duty (Staff-days)</p>
            </div>
            <div class="status-value" id="monthly_offduty">
              <i class="fas fa-spinner fa-spin loading-pulse"></i>
            </div>
          </div>
          
          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator leave"></div>
              <p class="status-text">On Leave (Staff-days)</p>
 					</div>
            <div class="status-value" id="monthly_leave">
              <i class="fas fa-spinner fa-spin loading-pulse"></i>
 						</div>
 						</div>
          
          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator info"></div>
              <p class="status-text">Official/Workshop (Staff-days)</p>
 						</div>
            <div class="status-value" id="monthly_request">
              <i class="fas fa-spinner fa-spin loading-pulse"></i>
 						</div>
 					</div>

          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator primary"></div>
              <p class="status-text">Avg Hours Worked</p>
            </div>
            <div class="status-value" id="facility_avg_hours">
              <i class="fas fa-spinner fa-spin loading-pulse"></i>
            </div>
          </div>
 				</div>
 			</div>

      <!-- Facility Status -->
      <div class="col-xl-4 col-md-6 mb-3">
        <div class="status-card">
          <div class="status-header">
            <div class="status-icon success">
              <i class="fas fa-building"></i>
            </div>
            <h3 class="status-title">Facility Status</h3>
          </div>
          
          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator info"></div>
              <p class="status-text">My Staff</p>
            </div>
            <div class="status-value" id="mystaff">
              <i class="fas fa-spinner fa-spin loading-pulse"></i>
            </div>
          </div>
          
          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator warning"></div>
              <p class="status-text">Departments</p>
            </div>
            <div class="status-value" id="departments">
              <i class="fas fa-spinner fa-spin loading-pulse"></i>
            </div>
          </div>
          
          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator leave"></div>
              <p class="status-text">Jobs</p>
 					</div>
            <div class="status-value" id="jobs">
              <i class="fas fa-spinner fa-spin loading-pulse"></i>
 						</div>
 						</div>
          
          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator leave"></div>
              <p class="status-text">Cadres</p>
 						</div>
            <div class="status-value" id="cadreS">
              <i class="fas fa-spinner fa-spin loading-pulse"></i>
 						</div>
 					</div>
 				</div>
 			</div>
 		</div>

    <!-- Calendar Section -->
		<div class="row">
      <!-- Daily Attendance Calendar -->
      <div class="col-lg-12 mb-4">
        <div class="calendar-card">
          <div class="calendar-header">
            <h3 class="calendar-title">
              <i class="fas fa-calendar-check mr-2"></i>Daily Attendance Calendar
              <span id="calendar-loading" class="ml-2" style="display:none;">
                <i class="fas fa-spinner fa-spin text-primary"></i> Loading calendar...
              </span>
						</h3>
          </div>
          
          <div class="calendar-legend">
								<?php $colors = Modules::run('schedules/getattKey'); ?>
									<?php foreach ($colors as $color) { ?>
              <div class="legend-item">
                <span class="legend-color" style="background-color:<?php echo $color->color; ?>;"></span>
                <span class="legend-text"><?php echo $color->schedule; ?></span>
              </div>
            <?php } ?>
								</div>
          
          <div class="card-body p-0">
            <div id="attcalendar"></div>
						</div>
						</div>
				</div>
		</div>
		
		<!-- Attendance Graphs Section (loaded separately) -->
		<div id="attendance-graphs-section">
			<div class="row mb-3 mt-2">
				<div class="col-12">
					<h4 class="dash-section-title">
						<i class="fas fa-chart-line"></i>
						Attendance Analytics
						<span id="graphs-loading" class="ml-2 small font-weight-normal text-muted" style="display:none;">
							<i class="fas fa-spinner fa-spin"></i> Loading graphs…
						</span>
					</h4>
				</div>
			</div>
			<?php echo Modules::run('dashboard/attendance_graphs'); ?>
		</div>

				</div>
			</section>

<!-- Scripts -->
 <script src="<?php echo base_url() ?>assets/plugins/moment/moment.min.js"></script>
 <script src="<?php echo base_url() ?>assets/bower_components/fullcalendar/dist/fullcalendar.min.js"></script>

 <script type="text/javascript">
	// Wait for Highcharts to be fully loaded before using it
	function waitForHighcharts(callback) {
		if (typeof Highcharts !== 'undefined' && typeof Highcharts.setOptions === 'function') {
			callback();
		} else {
			setTimeout(function() {
				waitForHighcharts(callback);
			}, 50);
		}
	}
	
waitForHighcharts(function() {
		$(document).ready(function() {
			// Dashboard filter state (persisted via session; used by calendar + chart refresh)
			window.__dashFilters = window.__dashFilters || {
				month: '<?php echo $sel_month ?? date('m'); ?>',
				year: '<?php echo $sel_year ?? date('Y'); ?>',
				empid: '<?php echo $sel_empid ?? ''; ?>'
			};
			
			// Name filter (Select2) + national scope filters
			if ($.fn.select2) {
				$('.dash-filter-s2').select2({ theme: 'bootstrap4', width: '100%', allowClear: true });

				function populateDashSelect($el, items, placeholder, selected) {
					var html = '<option value="">' + placeholder + '</option>';
					(items || []).forEach(function(o) {
						var v = (o && o.value !== undefined) ? o.value : o;
						var l = (o && o.label !== undefined) ? o.label : v;
						if (!v && v !== 0) return;
						html += '<option value="' + $('<div>').text(v).html() + '">' + $('<div>').text(l).html() + '</option>';
					});
					$el.html(html);
					if (selected) { $el.val(selected).trigger('change.select2'); }
				}

				$.getJSON('<?php echo base_url('dashboard/filterOptions'); ?>')
					.done(function(data) {
						populateDashSelect($('#dash_region'), data.regions, 'All Regions', '<?php echo addslashes($sel_region); ?>');
						populateDashSelect($('#dash_district'), data.districts, 'All Districts', '<?php echo addslashes($sel_district); ?>');
						populateDashSelect($('#dash_institution_type'), data.institution_types, 'All', '<?php echo addslashes($sel_institution); ?>');
						populateDashSelect($('#dash_cadre'), data.cadres, 'All', '<?php echo addslashes($sel_cadre); ?>');
					});

				$('#dash_district').on('change', function() {
					$('#dash_nat_facility').val(null).trigger('change');
				});

				$('#dash_nat_facility').select2({
					placeholder: 'All <?php echo entity_label('facility', true); ?>',
					allowClear: true,
					width: '100%',
					minimumInputLength: 0,
					ajax: {
						url: '<?php echo base_url('dashboard/searchFacilities'); ?>',
						dataType: 'json',
						delay: 250,
						data: function(params) {
							return { term: params.term || '', district: $('#dash_district').val() || '' };
						},
						processResults: function(data) {
							return (data && Array.isArray(data.results)) ? data : { results: [] };
						},
						cache: true
					}
				});

				if ($('#dash_facility').length) {
					$('#dash_facility').select2({
						placeholder: 'All Facilities',
						allowClear: true,
						width: '100%',
						minimumInputLength: 0,
						ajax: {
							url: '<?php echo base_url('dashboard/searchFacilities'); ?>',
							dataType: 'json',
							delay: 250,
							data: function(params) { return { term: params.term || '' }; },
							processResults: function(data) {
								if (!data || !Array.isArray(data.results)) {
									return { results: [] };
								}
								return data;
							},
							cache: true
						}
					});
					
					// Ensure options show even before typing
					$('#dash_facility').on('select2:open', function() {
						var search = document.querySelector('.select2-container--open .select2-search__field');
						if (search) { search.value = ''; search.dispatchEvent(new Event('input')); }
					});
					
					// When facility changes, clear name selection and reload staff list
					$('#dash_facility').on('change', function() {
						$('#dash_empid').val(null).trigger('change');
						preloadDashStaff();
					});
				}

				$('#dash_empid').select2({
					placeholder: 'All Staff',
					allowClear: true,
					width: '100%',
					minimumInputLength: 0,
					dropdownParent: $('#dash_empid').closest('.card-body'),
					ajax: {
						url: '<?php echo base_url('dashboard/searchEmployees'); ?>',
						dataType: 'json',
						delay: 250,
						data: function(params) {
							return {
								term: params.term || '',
								facility_id: $('#dash_nat_facility').val() || ($('#dash_facility').length ? ($('#dash_facility').val() || '') : ''),
								district: $('#dash_district').val() || ''
							};
						},
						processResults: function(data) {
							if (!data || !Array.isArray(data.results)) {
								return { results: [] };
							}
							return data;
						},
						cache: true
					}
				});

				function preloadDashStaff() {
					$.getJSON('<?php echo base_url('dashboard/searchEmployees'); ?>', {
						term: '',
						facility_id: $('#dash_nat_facility').val() || ($('#dash_facility').length ? ($('#dash_facility').val() || '') : ''),
						district: $('#dash_district').val() || ''
					}).done(function(data) {
						if (!data || !Array.isArray(data.results) || !data.results.length) {
							return;
						}
						var $sel = $('#dash_empid');
						data.results.forEach(function(r) {
							if ($sel.find('option[value="' + r.id + '"]').length === 0) {
								$sel.append(new Option(r.text, r.id, false, false));
							}
						});
					});
				}
				preloadDashStaff();
				$('#dash_district, #dash_nat_facility').on('change', function() {
					$('#dash_empid').val(null).trigger('change');
					preloadDashStaff();
				});

				// Ensure Name dropdown shows results even before typing
				$('#dash_empid').on('select2:open', function() {
					var search = document.querySelector('.select2-container--open .select2-search__field');
					if (search) { search.value = ''; search.dispatchEvent(new Event('input')); }
				});
			}
			
			function applyDashboardFilters(month, year, empid) {
				var postData = {
					month: month,
					year: year,
					empid: empid || '',
					facility_id: $('#dash_facility').length ? ($('#dash_facility').val() || '') : '',
					region: $('#dash_region').val() || '',
					district: $('#dash_district').val() || '',
					national_facility_id: $('#dash_nat_facility').val() || '',
					institution_type: $('#dash_institution_type').val() || '',
					cadre: $('#dash_cadre').val() || '',
					'<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
				};
				
				return $.ajax({
					type: 'POST',
					url: '<?php echo base_url('dashboard/setDashboardFilters'); ?>',
					dataType: 'json',
					data: postData,
					timeout: 15000
				}).then(function(resp) {
					window.__dashFilters.month = resp.month || month;
					window.__dashFilters.year = resp.year || year;
					window.__dashFilters.empid = resp.empid || empid || '';
					return resp;
				});
			}
			
			$(document).on('click', '#dash_apply', function() {
				var month = $('#dash_month').val();
				var year = $('#dash_year').val();
				var empid = $('#dash_empid').val() || '';
				
				applyDashboardFilters(month, year, empid).then(function() {
					loadDashboardData(true).catch(function() {});
					loadNationalAnalytics();
					loadDashboardLivePulse();
					// Refresh chart (session-based)
					if (window.reloadAttendancePerMonth) {
						window.reloadAttendancePerMonth();
					}
					// Refresh calendar and jump to selected month
					if ($('#attcalendar').data('fullCalendar')) {
						$('#attcalendar').fullCalendar('gotoDate', year + '-' + month + '-01');
						$('#attcalendar').fullCalendar('refetchEvents');
					}
				});
			});
			
			$(document).on('click', '#dash_reset', function() {
				var m = '<?php echo date('m'); ?>';
				var y = '<?php echo date('Y'); ?>';
				$('#dash_month').val(m);
				$('#dash_year').val(y);
				if ($('#dash_facility').length) {
					$('#dash_facility').val(null).trigger('change');
				}
				$('#dash_empid').val(null).trigger('change');
				$('#dash_region, #dash_district, #dash_institution_type, #dash_cadre').val('').trigger('change');
				$('#dash_nat_facility').val(null).trigger('change');
				$('#dash_apply').trigger('click');
			});

			function loadNationalAnalytics() {
				return $.getJSON('<?php echo base_url('dashboard/nationalAnalytics'); ?>')
					.done(function(data) {
						if (!data || !data.national) return;
						var n = data.national;
						$('#national_attendance_rate').text((n.attendance_rate != null ? n.attendance_rate : 0) + '%');
						$('#national_absenteeism_rate').text((n.absenteeism_rate != null ? n.absenteeism_rate : 0) + '%');
						$('#national_avg_hours').text((n.avg_hours != null ? n.avg_hours : 0) + ' hrs');
						$('#national_present_days').text(n.present != null ? n.present : 0);
						$('#national_rate_month').text(n.month ? ('Month: ' + n.month) : '');
						$('#national_absent_month').text(n.month ? ('Month: ' + n.month) : '');
						$('#national_avg_hours_month').text(n.month ? ('Month: ' + n.month) : '');
					});
			}
			loadNationalAnalytics();
			// Set Highcharts options only if Highcharts is available
			if (typeof Highcharts !== 'undefined' && typeof Highcharts.setOptions === 'function') {
				Highcharts.setOptions({
					colors: ['#28a745', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4']
				});
			}

 		function knobgauge(gvalue) {
 			// Your knobgauge function code here
 		}

		function loadDashboardData(showLoader) {
			return new Promise(function(resolve, reject) {
            // Show loading indicator (only for the first attempt)
            if (showLoader !== false) {
              $('.stat-number, .status-value').html('<i class="fas fa-spinner fa-spin loading-pulse"></i>');
            }

				$.ajax({
					type: 'GET',
					url: '<?php echo base_url('dashboard/dashboardData') ?>',
					dataType: 'json',
					data: '',
					async: true,
                timeout: 60000,
					success: function(data) {
                    // Reset retry state on success
                    window.__dashboardDataRetry = { attempt: 0, timer: null };

                    // Update dashboard data with fade-in effect
                    updateDashboardValue('#workers', data.workers);
                    updateDashboardValue('#facilities', data.facilities);
                    updateDashboardValue('#departments', data.departments);
                    updateDashboardValue('#jobs', data.jobs);
                    updateDashboardValue('#mystaff', data.mystaff);
                    updateDashboardValue('#ihris_sync', data.ihris_sync);
                    updateDashboardValue('#biometrics', data.biometrics);
                    updateDashboardValue('#roster', data.roster);
                    updateDashboardValue('#attendance', data.attendance);
                    updateDashboardValue('#biotime_last', data.biotime_last);
                    updateDashboardValue('#present', data.present);
                    updateDashboardValue('#offduty', data.offduty);
                    updateDashboardValue('#leave', data.leave);
                    updateDashboardValue('#absent', data.absent);
                    updateDashboardValue('#monthly_present', data.monthly_present);
                    updateDashboardValue('#monthly_offduty', data.monthly_offduty);
                    updateDashboardValue('#monthly_leave', data.monthly_leave);
                    updateDashboardValue('#monthly_request', data.monthly_request);
                    updateDashboardValue('#facility_avg_hours', (data.avg_hours != null ? data.avg_hours : 0) + ' hrs');

                    if (data.dashboard_month && data.dashboard_year) {
                      var monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                      var mi = parseInt(data.dashboard_month, 10) - 1;
                      $('#monthly_label').text((monthNames[mi] || data.dashboard_month) + ' ' + data.dashboard_year);
                    }
                    if (data.status_date) {
                      $('#daily_status_date').text('(' + data.status_date + ')');
                    }
                    
                    // avg_hours shown in Monthly Attendance Stats
                    
                    // Add fade-in animation
                    $('.stat-card, .status-card').addClass('fade-in');
 						resolve();
 					},
                error: function(xhr, status, error) {
                    console.error('Dashboard data error:', status, error);

                    // If this was a background retry, don't blow away existing values
                    if (showLoader !== false) {
                      $('.stat-number, .status-value').html('<span class="text-danger">Error loading data</span>');
                    }

                    // Schedule a background retry with exponential backoff (non-blocking)
                    window.__dashboardDataRetry = window.__dashboardDataRetry || { attempt: 0, timer: null };
                    if (!window.__dashboardDataRetry.timer) {
                      var attempt = window.__dashboardDataRetry.attempt || 0;
                      var delay = Math.min(60000, 2000 * Math.pow(2, attempt)); // 2s, 4s, 8s, ... max 60s
                      window.__dashboardDataRetry.attempt = attempt + 1;

                      // Small user hint without spamming (only if we were showing loader)
                      if (showLoader !== false) {
                        $('.stat-number, .status-value').html('<span class="text-warning">Retrying...</span>');
                      }

                      window.__dashboardDataRetry.timer = setTimeout(function() {
                        window.__dashboardDataRetry.timer = null;
                        loadDashboardData(false).catch(function() { /* retry loop continues via backoff */ });
                      }, delay);
                    }

						reject(error || status);
 					}
 				});
 			});
 		}

    // Background refresh loop (keeps the dashboard up to date and self-heals after timeouts)
    function startDashboardAutoRefresh() {
      window.__dashboardAutoRefresh = window.__dashboardAutoRefresh || { timer: null };
      if (window.__dashboardAutoRefresh.timer) return;

      window.__dashboardAutoRefresh.timer = setInterval(function() {
        // quiet refresh (no loaders)
        loadDashboardData(false).catch(function() { /* retry handled in loadDashboardData */ });
      }, 300000); // every 5 minutes
    }

    var DASH_LIVE_POLL_MS = <?php
      $this->config->load('dashboard_cache', true, true);
      $dash_cfg = $this->config->item('dashboard_cache');
      echo (int) (is_array($dash_cfg) && isset($dash_cfg['live_poll_seconds']) ? $dash_cfg['live_poll_seconds'] : 15) * 1000;
    ?>;

    function formatLiveAgo(iso) {
      if (!iso) return 'just now';
      var ts = Date.parse(iso);
      if (isNaN(ts)) return 'just now';
      var sec = Math.max(0, Math.floor((Date.now() - ts) / 1000));
      if (sec < 10) return 'just now';
      if (sec < 60) return sec + 's ago';
      return Math.floor(sec / 60) + 'm ago';
    }

    function updateLiveValue(selector, value) {
      if (value === undefined || value === null) return;
      var $el = $(selector);
      var prev = $.trim($el.text());
      var next = String(value);
      if (prev === next || prev === '' || isNaN(parseInt(prev, 10))) {
        $el.text(next);
        return;
      }
      $el.text(next).addClass('live-flash');
      setTimeout(function() { $el.removeClass('live-flash'); }, 1200);
    }

    function renderLiveFeed(recent) {
      var $feed = $('#live-feed');
      if (!recent || !recent.length) {
        $feed.html('<li class="live-feed-empty">No check-ins yet today</li>');
        return;
      }
      var html = '';
      recent.forEach(function(item) {
        var source = item.source === 'mobile' ? 'Mobile' : (item.source === 'biotime' ? 'BioTime' : 'Device');
        html += '<li><span class="live-feed-name"><i class="fas fa-user-check text-success mr-1"></i>' +
          $('<div>').text(item.name || 'Staff').html() +
          '</span><span class="live-feed-meta">' + (item.time || '') + ' · ' + source + '</span></li>';
      });
      $feed.html(html);
    }

    function setLiveIndicator(state, message, cacheHint) {
      var $dot = $('#live-dot');
      $dot.removeClass('stale offline');
      if (state === 'offline') {
        $dot.addClass('offline');
      } else if (state === 'stale') {
        $dot.addClass('stale');
      }
      if (message) {
        $('#live-updated').text(message);
      }
      if (cacheHint !== undefined) {
        $('#live-cache-hint').text(cacheHint);
      }
    }

    function loadDashboardLivePulse() {
      return $.ajax({
        type: 'GET',
        url: '<?php echo base_url('dashboard/dashboardLivePulse'); ?>',
        dataType: 'json',
        timeout: 20000,
        cache: false,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      }).done(function(data) {
        if (!data || data.live === false) {
          if (data && data.reason === 'historical') {
            setLiveIndicator('stale', 'Live updates paused (historical month selected)', '');
            $('#live-feed').html('<li class="live-feed-empty">Switch to the current month for live check-ins.</li>');
          } else if (data && data.error === 'no_facility') {
            setLiveIndicator('offline', 'No facility in session — switch facility or log in again', '');
            $('#live-feed').html('<li class="live-feed-empty">Facility required for live check-ins.</li>');
          } else if (data && data.error === 'unauthorized') {
            setLiveIndicator('offline', 'Session expired — refresh the page', '');
          } else if (data && data.error === 'server_error' && data.message) {
            setLiveIndicator('offline', data.message, '');
            $('#live-feed').html('<li class="live-feed-empty">Live feed will retry automatically…</li>');
          } else {
            setLiveIndicator('stale', 'Live feed paused', '');
          }
          return;
        }

        updateLiveValue('#present', data.present);
        updateLiveValue('#offduty', data.offduty);
        updateLiveValue('#leave', data.leave);
        updateLiveValue('#absent', data.absent);
        if (data.status_date) {
          $('#daily_status_date').text('(' + data.status_date + ')');
        }
        renderLiveFeed(data.recent);

        var ago = formatLiveAgo(data.generated_at);
        var cacheHint = '';
        if (data.cache_layer) {
          if (data.cache_layer.redis) {
            cacheHint = data.cached ? 'Redis · ' + ago : 'Redis live · ' + ago;
          } else if (data.cache_layer.memcached) {
            cacheHint = data.cached ? 'Memcached · ' + ago : 'Memcached live · ' + ago;
          } else {
            cacheHint = 'Direct · ' + ago;
          }
        } else {
          cacheHint = 'Updated ' + ago;
        }
        setLiveIndicator('live', data.clock_ins_today + ' checked in today · updated ' + ago, cacheHint);
      }).fail(function(xhr, status) {
        var parsed = null;
        try {
          if (xhr && xhr.responseText) {
            parsed = JSON.parse(xhr.responseText);
          }
        } catch (e) { /* not JSON */ }
        if (parsed && parsed.live === false) {
          if (parsed.reason === 'historical') {
            setLiveIndicator('stale', 'Live updates paused (historical month selected)', '');
          } else if (parsed.error === 'no_facility') {
            setLiveIndicator('offline', 'No facility in session — switch facility or log in again', '');
          } else if (parsed.message) {
            setLiveIndicator('offline', parsed.message, '');
          }
          return;
        }
        var hint = status === 'timeout' ? 'Request timed out' : 'Live feed unavailable';
        if (xhr && xhr.status === 401) {
          hint = 'Session expired — refresh the page';
        }
        setLiveIndicator('offline', hint, '');
        console.error('dashboardLivePulse failed', status, xhr && xhr.status, xhr && xhr.responseText);
      });
    }

    function startDashboardLiveRefresh() {
      window.__dashboardLiveRefresh = window.__dashboardLiveRefresh || { timer: null };
      if (window.__dashboardLiveRefresh.timer) return;

      loadDashboardLivePulse();
      window.__dashboardLiveRefresh.timer = setInterval(function() {
        loadDashboardLivePulse();
      }, DASH_LIVE_POLL_MS);
    }
    
    function updateDashboardValue(selector, value) {
        if (value !== undefined && value !== null) {
            $(selector).fadeOut(200, function() {
                $(this).text(value).fadeIn(200);
            });
        }
    }
    
    function handleFacilitySwitch(newFacility) {
        $('.stat-number, .status-value').html('<i class="fas fa-spinner fa-spin loading-pulse"></i> Switching facility...');
        
        $.ajax({
            type: 'POST',
            url: '<?php echo base_url('dashboard/switchFacility') ?>',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    loadDashboardData().then(function() {
                        console.log('Dashboard reloaded for facility:', response.facility);
                    }).catch(function(error) {
                        console.error('Error reloading dashboard:', error);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error switching facility:', error);
                loadDashboardData();
            }
 			});
 		}

		/**
		 * Load attendance calendar separately and asynchronously
		 * This prevents the dashboard from hanging
		 */
		function loadAttendanceCalendar() {
			return new Promise(function(resolve, reject) {
				// Wait for fullCalendar to be loaded
				if (typeof $.fullCalendar === 'undefined') {
					setTimeout(function() {
						loadAttendanceCalendar().then(resolve).catch(reject);
					}, 100);
					return;
				}
				
				var base_url = $('.base_url').html();
				if (!base_url) {
					base_url = '<?php echo base_url(); ?>';
				}
				
				// Show loading indicator
				$('#calendar-loading').show();
				
				// Initialize calendar with fallback to original endpoint
				$('#attcalendar').fullCalendar({
					defaultView: 'basicWeek',
					header: {
						left: 'prev, next, today',
						center: 'title',
						right: 'month, basicWeek, basicDay'
					},
					eventLimit: true,
					loading: function(isLoading) {
						if (!isLoading) {
							$('#calendar-loading').hide();
							resolve();
						}
					},
					events: function(start, end, timezone, callback) {
						// Try optimized streaming endpoint first, fallback to original
						var streamUrl = base_url + 'calendar/getattEventsStream';
						var fallbackUrl = base_url + 'calendar/getattEvents';
						
						$.ajax({
							url: streamUrl,
							type: 'GET',
							dataType: 'json',
							data: {
								start: start.format('YYYY-MM-DD'),
								end: end.format('YYYY-MM-DD'),
								empid: (window.__dashFilters && window.__dashFilters.empid) ? window.__dashFilters.empid : ''
							},
							timeout: 60000,
							success: function(events) {
								if (Array.isArray(events)) {
									callback(events);
								} else {
									callback([]);
								}
							},
							error: function(xhr, status, error) {
								console.warn('Stream endpoint failed, trying fallback:', error);
								// Fallback to original endpoint
								$.ajax({
									url: fallbackUrl,
									type: 'GET',
									dataType: 'json',
									data: {
										start: start.format('YYYY-MM-DD'),
										end: end.format('YYYY-MM-DD'),
										empid: (window.__dashFilters && window.__dashFilters.empid) ? window.__dashFilters.empid : ''
									},
									timeout: 60000,
									success: function(events) {
										if (Array.isArray(events)) {
											callback(events);
										} else {
											callback([]);
										}
									},
									error: function(xhr2, status2, error2) {
										console.error('Calendar load error (both endpoints failed):', error2);
										callback([]);
										$('#calendar-loading').html('<span class="text-danger">Error loading calendar</span>');
										resolve(); // Resolve anyway to not block
									}
								});
							}
						});
					},
					selectable: false,
					selectHelper: true,
					editable: false,
					eventMouseover: function(calEvent, jsEvent, view) {
						var tooltip = '<div class="event-tooltip">' + calEvent.duty + '</div>';
						$("body").append(tooltip);
						$(this).mouseover(function(e) {
							$(this).css('z-index', 10000);
							$('.event-tooltip').fadeIn('500');
							$('.event-tooltip').fadeTo('10', 1.9);
						}).mousemove(function(e) {
							$('.event-tooltip').css('top', e.pageY + 10);
							$('.event-tooltip').css('left', e.pageX + 20);
						});
					},
					eventMouseout: function(calEvent, jsEvent) {
						$(this).css('z-index', 8);
						$('.event-tooltip').remove();
					},
				});
			});
		}
		
		/**
		 * Initialize attendance graphs after page load
		 * Graphs are already in the DOM, just need to ensure they initialize properly
		 */
		function initializeAttendanceGraphs() {
			// Graphs are already loaded in the DOM
			// Just ensure Highcharts is ready and graphs initialize
			waitForHighcharts(function() {
				// Graphs will initialize via their own script in attendance_graphs.php
				console.log('Attendance graphs ready');
			});
		}
		
		// Load dashboard stats first (non-blocking)
		loadDashboardData(true).catch(function(error) {
			console.error('Dashboard data error:', error);
		});
		
		// Load calendar separately and asynchronously (non-blocking)
		setTimeout(function() {
			loadAttendanceCalendar().then(function() {
				// After calendar loads, initialize graphs
				setTimeout(function() {
					initializeAttendanceGraphs();
				}, 1000);
			}).catch(function(error) {
				console.error('Calendar load error:', error);
			});
		}, 500); // Small delay to let dashboard stats start loading

     // Start background refresh after initial attempt (success or failure)
     startDashboardAutoRefresh();
     startDashboardLiveRefresh();
     
     // Session testing function (remove in production)
     window.testSessionEndpoints = function() {
       console.log('Testing session endpoints...');
       
       // Test checkSession
       fetch('<?php echo base_url("auth/checkSession"); ?>', {
         method: 'GET',
         credentials: 'same-origin',
         headers: {
           'X-Requested-With': 'XMLHttpRequest'
         }
       })
       .then(response => {
         console.log('checkSession response status:', response.status);
         return response.json();
       })
       .then(data => {
         console.log('checkSession response:', data);
       })
       .catch(error => {
         console.error('checkSession error:', error);
       });
       
       // Test extendSession
       fetch('<?php echo base_url("auth/extendSession"); ?>', {
         method: 'GET',
         credentials: 'same-origin',
         headers: {
           'X-Requested-With': 'XMLHttpRequest'
         }
       })
       .then(response => {
         console.log('extendSession response status:', response.status);
         return response.json();
       })
       .then(data => {
         console.log('extendSession response:', data);
       })
       .catch(error => {
         console.error('extendSession error:', error);
       });
     };
		}); // End of $(document).ready
	}); // End of waitForHighcharts
 </script>
