<!-- Modern Dashboard Design -->
 <style>
/* Modern Dashboard Styling */
:root {
  --primary-color:rgb(34, 39, 43);
  --secondary-color: #FEFFFF;
  --success-color: #28a745;
  --warning-color: #ffc107;
  --danger-color: #dc3545;
  --info-color: #17a2b8;
  --light-color: #f8f9fa;
  --dark-color: #343a40;
  --border-color: #dee2e6;
  --text-muted: #6c757d;
  --shadow-light: 0 2px 10px rgba(0,0,0,0.08);
  --shadow-medium: 0 8px 25px rgba(0,0,0,0.12);
  --shadow-heavy: 0 15px 35px rgba(0,0,0,0.15);
}

/* Page Header */
  .dashboard-header {
    background: #222d32;
    color: var(--secondary-color);
    padding: 2.5rem 2rem;
    border-radius: 4px;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-medium);
    position: relative;
    overflow: hidden;
  }

.dashboard-header::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 200px;
  height: 200px;
  background: rgba(255,255,255,0.1);
  border-radius: 50%;
  transform: translate(50%, -50%);
}

.dashboard-title {
  font-size: 1.8rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.dashboard-subtitle {
  font-size: 0.95rem;
  opacity: 0.75;
  margin-bottom: 0;
  color: #e9ecef;
  font-weight: 400;
}

/* Modern Statistics Cards */
.stat-card {
  background: var(--secondary-color);
  border-radius: 4px;
  padding: 1.25rem;
  box-shadow: var(--shadow-light);
  transition: all 0.3s ease;
  border: 1px solid var(--border-color);
  position: relative;
  overflow: hidden;
  min-height: 180px;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-medium);
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: var(--primary-color);
}

.stat-card.success::before { background: var(--success-color); }
.stat-card.warning::before { background: var(--warning-color); }
.stat-card.info::before { background: var(--info-color); }
.stat-card.danger::before { background: var(--danger-color); }

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  color: var(--secondary-color);
  margin-bottom: 0.75rem;
}

.stat-icon.primary { background: var(--primary-color); }
.stat-icon.success { background: var(--success-color); }
.stat-icon.warning { background: var(--warning-color); }
.stat-icon.info { background: var(--info-color); }
.stat-icon.danger { background: var(--danger-color); }

.stat-number {
    font-size: 1.2rem;
    font-weight: 500;
    color: #626567;
    margin-bottom: 0.25rem;
    line-height: 1;
}

.stat-label {
  font-size: 0.8rem;
  color: var(--text-muted);
  font-weight: 500;
  text-transform: capitalize;
  letter-spacing: 0.5px;
}

/* Status Cards */
.status-card {
  background: var(--secondary-color);
  border-radius: 4px;
  padding: 1.5rem;
  box-shadow: var(--shadow-light);
  transition: all 0.3s ease;
  border: 1px solid var(--border-color);
}

.status-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-medium);
}

.status-header {
  display: flex;
  align-items: center;
  margin-bottom: 1.5rem;
}

.status-icon {
  width: 50px;
  height: 50px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  color: var(--secondary-color);
  margin-right: 1rem;
}

.status-title {
  font-size: 1.2rem;
  font-weight: 400;
  color: var(--primary-color);
  margin: 0;
}

.status-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 0;
  border-bottom: 1px solid var(--border-color);
}

.status-item:last-child {
  border-bottom: none;
}

.status-info {
  display: flex;
  align-items: center;
}

.status-indicator {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  margin-right: 1rem;
}

.status-indicator.present { background: var(--success-color); }
.status-indicator.offduty { background: var(--warning-color); }
.status-indicator.leave { background: var(--danger-color); }
.status-indicator.request { background: var(--info-color); }

.status-text {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin: 0;
}

.status-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary-color);
}

.status-unit {
  font-size: 0.8rem;
  color: var(--text-muted);
  margin-left: 0.25rem;
}

/* Calendar Cards */
.calendar-card {
  background: var(--secondary-color);
  border-radius: 4px;
  box-shadow: var(--shadow-light);
  transition: all 0.3s ease;
  border: 1px solid var(--border-color);
  overflow: hidden;
}

.calendar-card:hover {
  box-shadow: var(--shadow-medium);
}

.calendar-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
  border-radius: 4px 4px 0 0;
}

.calendar-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #495057;
  margin: 0;
  display: flex;
  align-items: center;
}

.calendar-title i {
  margin-right: 0.5rem;
  color: #6c757d;
  font-size: 1rem;
}

.calendar-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  background: #f8f9fa;
  border-bottom: 1px solid var(--border-color);
}

.legend-item {
 		display: flex;
  align-items: center;
  gap: 0.5rem;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 2px;
  border: 1px solid #dee2e6;
}

.legend-text {
  font-size: 0.8rem;
  color: #6c757d;
  font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
  .dashboard-header {
    padding: 1.75rem 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  .dashboard-title {
    font-size: 1.6rem;
  }
  
  .dashboard-subtitle {
    font-size: 0.9rem;
  }
  
  .stat-card, .status-card, .calendar-card {
 		margin-bottom: 1rem;
  }
  
  .stat-number {
    font-size: 1.5rem;
  }
}

@media (max-width: 576px) {
  .dashboard-header {
    padding: 1.5rem 1rem;
  }
  
  .dashboard-title {
    font-size: 1.5rem;
  }
  
  .dashboard-subtitle {
    font-size: 0.85rem;
  }
  
  .stat-card, .status-card, .calendar-card {
    padding: 1rem;
  }
}

/* Loading Animations */
.loading-pulse {
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

/* Smooth Transitions */
.fade-in {
  animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
 	}
 </style>

<!-- Main Dashboard Content -->
 <section class="content">
 	<div class="container-fluid">
    
    <!-- Dashboard Header -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="callout callout-success">
          <h1 class="dashboard-title">
            <i class="fas fa-tachometer-alt mr-3"></i>Welcome back!
          </h1>
          <p class="dashboard-subtitle text-muted">Here's what's happening with your facility today.</p>
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
            <h3 class="status-title">Daily Attendance Status</h3>
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
              <div class="status-indicator request"></div>
              <p class="status-text">Workshop/Official Request</p>
            </div>
            <div class="status-value" id="request">
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

      <!-- Out of Station Requests -->
      <div class="col-xl-4 col-md-6 mb-3">
        <div class="status-card">
          <div class="status-header">
            <div class="status-icon info">
              <i class="fas fa-paper-plane"></i>
            </div>
            <h3 class="status-title">Out of Station Requests</h3>
          </div>
          
          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator warning"></div>
              <p class="status-text">Requests Submitted</p>
            </div>
            <div class="status-value" id="rsent">
              <i class="fas fa-spinner fa-spin loading-pulse"></i>
            </div>
          </div>
          
          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator present"></div>
              <p class="status-text">Requests Approved</p>
            </div>
            <div class="status-value" id="rapproved">
              <i class="fas fa-spinner fa-spin loading-pulse"></i>
            </div>
          </div>
          
          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator leave"></div>
              <p class="status-text">Requests Rejected</p>
 					</div>
            <div class="status-value" id="rrejected">
              <i class="fas fa-spinner fa-spin loading-pulse"></i>
 						</div>
 						</div>
          
          <div class="status-item">
            <div class="status-info">
              <div class="status-indicator info"></div>
              <p class="status-text">Total Requests</p>
 						</div>
            <div class="status-value" id="trequests">
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

      <!-- Duty Roster Calendar -->
      <div class="col-lg-12 mb-4">
        <div class="calendar-card">
          <div class="calendar-header">
            <h3 class="calendar-title">
              <i class="fas fa-calendar-alt mr-2"></i>Duty Roster Calendar
 						</h3>
          </div>
          
          <div class="calendar-legend">
 								<?php $colors = Modules::run('schedules/getrosterKey'); ?>
 									<?php foreach ($colors as $color) { ?>
              <div class="legend-item">
                <span class="legend-color" style="background-color:<?php echo $color->color; ?>;"></span>
                <span class="legend-text"><?php echo $color->schedule; ?></span>
              </div>
            <?php } ?>
          </div>
          
          <div class="card-body p-0">
            <div id="dutycalendar"></div>
          </div>
 								</div>
 						</div>
 						</div>

 				</div>
 			</section>

<!-- Scripts -->
 <script src="<?php echo base_url() ?>assets/plugins/moment/moment.min.js"></script>
 <script src="<?php echo base_url() ?>assets/bower_components/fullcalendar/dist/fullcalendar.min.js"></script>

 <script type="text/javascript">
 	$(document).ready(function() {
 		Highcharts.setOptions({
 			colors: ['#28a745', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4']
 		});

 		function knobgauge(gvalue) {
 			// Your knobgauge function code here
 		}

 		function loadDashboardData() {
 			return new Promise(function(resolve, reject) {
            // Show loading indicator
            $('.stat-number, .status-value').html('<i class="fas fa-spinner fa-spin loading-pulse"></i>');
            
 				$.ajax({
 					type: 'GET',
 					url: '<?php echo base_url('dashboard/dashboardData') ?>',
 					dataType: 'json',
 					data: '',
 					async: true,
                timeout: 30000,
 					success: function(data) {
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
                    updateDashboardValue('#request', data.request);
                    updateDashboardValue('#requesting', data.requesting);
                    
                    if (data.avg_hours) {
 						knobgauge(data.avg_hours);
                    }
                    
                    // Add fade-in animation
                    $('.stat-card, .status-card').addClass('fade-in');
 						resolve();
 					},
                error: function(xhr, status, error) {
                    console.error('Dashboard data error:', error);
                    $('.stat-number, .status-value').html('<span class="text-danger">Error loading data</span>');
 						reject(error);
 					}
 				});
 			});
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

 		function loadAttendanceCalendar() {
 			var base_url = $('.base_url').html();
 			$('#attcalendar').fullCalendar({
 				defaultView: 'basicWeek',
 				header: {
 					left: 'prev, next, today',
 					center: 'title',
 					right: 'month, basicWeek, basicDay'
 				},
            eventLimit: true,
 				events: base_url + 'calendar/getattEvents',
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
 		}

 		function loadRosterCalendar() {
 			var base_url = $('.base_url').html();
 			$('#dutycalendar').fullCalendar({
 				defaultView: 'basicWeek',
 				header: {
 					left: 'prev, next, today',
 					center: 'title',
 					right: 'month, basicWeek, basicDay'
 				},
            eventLimit: true,
 				events: base_url + 'calendar/getEvents',
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
 		}
 		
 		// Chain the functions in order
 		 loadDashboardData()
 			.then(function() {
 				return loadAttendanceCalendar();
 			})
 			.then(function() {
 				return loadRosterCalendar();
 			})
 			.catch(function(error) {
 				console.error('An error occurred:', error);
 			});
     
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
 	});
 </script>
