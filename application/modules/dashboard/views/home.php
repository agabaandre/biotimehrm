 <!-- Main content -->
 <style>
 	.info-box-main {
 		box-shadow: rgba(110, 68, 68, 0.2);
 		background: #00838f;
 		text-align: center;
 		display: -ms-flexbox;
 		display: flex;
 		margin-bottom: 1rem;
 		min-height: 90px;
 		padding: .5rem;
 		position: relative;
 		color: #FFF;
 	}
 </style>
 <section class="content">
 	<div class="container-fluid">
 		<!-- Main row -->
 		<?php
			$permissions = $this->session->userdata('permissions');
			//  print_r($permissions);
			if (in_array('33', $permissions)) {
				$display = "active";
			} else {
				$display = "none";
			}
			?>
 		<div class="row" style="display:<?php echo $display; ?>">
 			<div class="col-12 col-sm-6 col-md-3">
 				<div class="info-box info-box-main">
 					<span class="info-box-icon  elevation-1"><i class="fas fa-sync"></i></span>
 					<span class="base_url" style="display: none;"><?php echo base_url(); ?></span>
 					<div class="info-box-content">
 						<span class="info-box-text">iHRIS Sync</span>
 						<span class="info-box-number" id="ihris_sync">
 						</span>
 					</div>
 					<!-- /.info-box-content -->
 				</div>
 				<!-- /.info-box -->
 			</div>
 			<!-- /.col -->
 			<div class="col-12 col-sm-6 col-md-3">
 				<div class="info-box info-box-main mb-3">
 					<span class="info-box-icon  elevation-1"><i class="fas fa-clock"></i></span>
 					<div class="info-box-content">
 						<span class="info-box-text">Last Attendance Sum </span>
 						<span class="info-box-number" id="attendance"></span>
 					</div>
 					<!-- /.info-box-content -->
 				</div>
 				<!-- /.info-box -->
 			</div>
 			<!-- /.col -->
 			<!-- fix for small devices only -->
 			<div class="clearfix hidden-md-up"></div>
 			<div class="col-12 col-sm-6 col-md-3">
 				<div class="info-box info-box-main mb-3">
 					<span class="info-box-icon  elevation-1"><i class="fas fa-calendar"></i></span>
 					<div class="info-box-content">
 						<span class="info-box-text"> Last Roster Sum </span>
 						<span class="info-box-number" id="roster"></span>
 					</div>
 					<!-- /.info-box-content -->
 				</div>
 				<!-- /.info-box -->
 			</div>
 			<!-- /.col -->
 			<div class="col-12 col-sm-6 col-md-3">
 				<div class="info-box info-box-main mb-3">
 					<span class="info-box-icon  elevation-1"><i class="fas fa-fingerprint"></i></span>
 					<div class="info-box-content">
 						<span class="info-box-text">BioTime Last Sync</span>
 						<span class="info-box-number" id="biotime_last"></span>
 					</div>
 					<!-- /.info-box-content -->
 				</div>
 				<!-- /.info-box -->
 			</div>
 			<!-- /.col -->
 		</div>
 		<!-- End of Admin section -->
 		<div class="row">
 			<div class="col-xl-4 col-md-12">
 				<div class="card card-">
 					<div class="card-header border-0">
 						<h3 class="card-title">Daily Attendance Status</h3>
 					</div>
 					<div class="card-body">
 						<div class="d-flex justify-content-between align-items-center border-bottom mb-3 border-info">
 							<p class="text-xl" style="color:#4169E1;">
 								<i class="fas fa-calendar-check"></i>
 							</p>
 							<p class="h6 d-flex flex-column text-right">
 								<span class="info-box-text h6 text-muted">Present</span>
 								<span class="info-box-number font-weight-bold" id="present"></span>
 								<small>Staff</small>
 							</p>
 						</div>
 						<div class="d-flex justify-content-between align-items-center border-bottom mb-3 border-info">
 							<p class="text-warning text-xl">
 								<i class="fa fa-home warning"></i>
 							</p>
 							<p class=" h6 d-flex flex-column text-right">
 								<span class="info-box-text h6 text-muted">Off Duty</span>
 								<span class="info-box-number font-weight-bold" id="offduty"></span>
 								<small>Staff</small>
 							</p>
 						</div>
 						<div class="d-flex justify-content-between align-items-center mb-3 border-bottom mb-3 border-info">
 							<p class=" text-info text-xl">
 								<i class="fa fa-paper-plane"></i>
 							</p>
 							<p class="h6 d-flex flex-column text-right">
 								<span class="info-box-text h6 text-muted">Workshop/ Official Request</span>
 								<span class="info-box-number font-weight-bold" id="request"></span>
 								<small>Staff</small>
 							</p>
 						</div>
 						<div class="d-flex justify-content-between align-items-center mb-2">
 							<p class=" text-xl" style="color:#c2e258;">
 								<i class="fas fa-bed"></i>
 							</p>
 							<p class="h6 d-flex flex-column text-right">
 								<span class="info-box-text h6 text-muted">On Leave</span>
 								<span class="info-box-number font-weight-bold" id="leave"></span>
 								<small>Staff</small>
 							</p>
 						</div>
 					</div>
 				</div>
 			</div>
 			<div class="col-xl-4 col-md-12">
 				<div class="card">
 					<div class="card-header border-0">
 						<h3 class="card-title">Out of Station Requests</h3>
 					</div>
 					<div class="card-body">
 						<div class="d-flex justify-content-between align-items-center border-bottom mb-3 border-info">
 							<p class="text-warning text-xl">
 								<i class="ion ion-ios-book text-warning"></i>
 							</p>
 							<p class="h6 d-flex flex-column text-right">
 								<span class="info-box-text h6 text-muted">Requests Submitted</span>
 								<span class="info-box-number font-weight-bold" id="rsent"></span>
 								<small>Requests</small>
 							</p>
 						</div>
 						<div class="d-flex justify-content-between align-items-center border-bottom mb-3 border-info">
 							<p class="text-success text-xl">
 								<i class="fa fa-check"></i>
 							</p>
 							<p class=" h6 d-flex flex-column text-right">
 								<span class="info-box-text h6 text-muted">Requests Approved</span>
 								<span class="info-box-number font-weight-bold" id="rapproved"></span>
 								<small>Requests</small>
 							</p>
 						</div>
 						<div class="d-flex justify-content-between align-items-center mb-3 border-bottom mb-3 border-info">
 							<p class=" text-danger text-xl">
 								<i class="fa fa-times"></i>
 							</p>
 							<p class="h6 d-flex flex-column text-right">
 								<span class="info-box-text h6 text-muted">Requests Rejected</span>
 								<span class="info-box-number font-weight-bold" id="rrejected"></span>
 								<small>Requests</small>
 							</p>
 						</div>
 						<div class="d-flex justify-content-between align-items-center mb-2">
 							<p class="text-info text-xl">
 								<i class="ion ion-ios-briefcase"></i>
 							</p>
 							<p class="h6 d-flex flex-column text-right">
 								<span class="info-box-text h6 text-muted">Total Requests</span>
 								<span class="info-box-number font-weight-bold" id="trequests"></span>
 								<small>Total Requests</small>
 							</p>
 						</div>
 					</div>
 				</div>
 			</div>
 			<div class="col-xl-4 col-md-12">
 				<div class="card card-">
 					<div class="card-header border-0">
 						<h3 class="card-title">Facility Status</h3>
 					</div>
 					<div class="card-body">
 						<div class="d-flex justify-content-between align-items-center border-bottom mb-3 border-info">
 							<p class="text-success text-xl">
 								<i class="ion ion-ios-people text-info"></i>
 							</p>
 							<p class="h6 d-flex flex-column text-right">
 								<span class="info-box-text h6 text-muted">My Staff</span>
 								<span class="info-box-number font-weight-bold" id="mystaff"></span>
 								<small>Staff</small>
 							</p>
 						</div>
 						<div class="d-flex justify-content-between align-items-center border-bottom mb-3 border-info">
 							<p class="text-warning text-xl">
 								<i class="fa fa-building"></i>
 							</p>
 							<p class=" h6 d-flex flex-column text-right">
 								<span class="info-box-text h6 text-muted">Departments</span>
 								<span class="info-box-number font-weight-bold" id="departments"></span>
 								<small>department(s)</small>
 							</p>
 						</div>
 						<div class="d-flex justify-content-between align-items-center mb-3 border-bottom mb-3 border-info">
 							<p class=" text-danger text-xl">
 								<i class="fa fa-tasks"></i>
 							</p>
 							<p class="h6 d-flex flex-column text-right">
 								<span class="info-box-text h6 text-muted">Jobs</span>
 								<span class="info-box-number font-weight-bold" id="jobs"></span>
 								<small>Jobs</small>
 							</p>
 						</div>
 						<div class="d-flex justify-content-between align-items-center mb-2">
 							<p class="text-danger text-xl">
 								<i class="ion ion-ios-people"></i>
 							</p>
 							<p class="h6 d-flex flex-column text-right">
 								<span class="info-box-text h6 text-muted">Cadres</span>
 								<span class="info-box-number font-weight-bold" id="cadreS"></span>
 								<small>Cadres</small>
 							</p>
 						</div>
 					</div>
 				</div>
 			</div>
 		</div>
 		<!-- </div> endrow -->

 		<div class="row">
 			<!-- Left col -->
 			<section class="col-lg-12 connectedSortable">
 				<!-- Custom tabs (Charts with tabs)-->
 				<div class="card">
 					<div class="card-header">
 						<h3 class="card-title">
 							Daily Attendance Calendar
 						</h3>
 						<div class="card-body">
 							<ul class="nav nav-pills" style="margin: 0 auto; margin-top:4px;">
 								<p></p>
 								<?php $colors = Modules::run('schedules/getattKey'); ?>
 								<div class="row">
 									<?php foreach ($colors as $color) { ?>
 										<li class="nav-item">
 											<a class="btn btn-flat btnkey" style="background-color:<?php echo $color->color;  ?>;"><?php echo $color->schedule; ?></a>
 										</li>
 									<?php  } ?>
 								</div>
 							</ul>
 						</div>
 						<div id="attcalendar">
 						</div>
 					</div><!-- /.card-body -->
 				</div>
 				<!-- /.card -->
 				<!-- calender key -->
 			</section>

 			<section class="col-lg-12 connectedSortable">
 				<!-- Custom tabs (Charts with tabs)-->
 				<div class="card">
 					<div class="card-header">
 						<h3 class="card-title">
 							Duty Roster Calendar
 						</h3>
 						<div class="card-body">
 							<ul class="nav nav-pills" style="margin: 0 auto; margin-top:4px;">
 								<p></p>
 								<?php $colors = Modules::run('schedules/getrosterKey'); ?>
 								<div class="row">
 									<?php foreach ($colors as $color) { ?>
 										<li class="nav-item">
 											<a class="btn btn-flat rbtnkey" style="background-color:<?php echo $color->color;  ?>;" data-toggle="tab"><?php echo $color->schedule; ?></a>
 										</li>
 									<?php  } ?>
 								</div>
 							</ul>
 						</div>
 						<div id="dutycalendar">
 						</div>
 					</div><!-- /.card-body -->
 				</div>
 			</section>

 		</div>
 		<!-- /.row (main row) -->
 	</div><!-- /.container-fluid -->
 </section>
 <!-- /.content -->
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
 				$.ajax({
 					type: 'GET',
 					url: '<?php echo base_url('dashboard/dashboardData') ?>',
 					dataType: 'json',
 					data: '',
 					async: true,
 					success: function(data) {
 						// Update dashboard data
 						$('#workers').text(data.workers);
 						$('#facilities').text(data.facilities);
 						$('#departments').text(data.departments);
 						$('#jobs').text(data.jobs);
 						$('#mystaff').text(data.mystaff);
 						$('#ihris_sync').text(data.ihris_sync);
 						$('#biometrics').text(data.biometrics);
 						$('#roster').text(data.roster);
 						$('#attendance').text(data.attendance);
 						$('#biotime_last').text(data.biotime_last);
 						$('#present').text(data.present);
 						$('#offduty').text(data.offduty);
 						$('#leave').text(data.leave);
 						$('#request').text(data.request);
 						$('#requesting').text(data.requesting);
 						knobgauge(data.avg_hours);
 						resolve();
 					},
 					error: function(error) {
 						reject(error);
 					}
 				});
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
 				// Get all events stored in database
 				eventLimit: true, // allow "more" link when too many events
 				events: base_url + 'calendar/getattEvents',
 				selectable: false,
 				selectHelper: true,
 				editable: false,
 				// Mouse over
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
 				// Get all events stored in database
 				eventLimit: true, // allow "more" link when too many events
 				events: base_url + 'calendar/getEvents',
 				selectable: false,
 				selectHelper: true,
 				editable: false,

 				// Mouse over
 				eventMouseover: function(calEvent, jsEvent, view) {
 					// console.log(calEvent);
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
 				// H
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
 	});
 </script>
