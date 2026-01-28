<style>
	@media only screen and (max-width: 980px) {
		.field {
			height: 2em;
			width: 5em;
			margin-top: 1em;
		}
	}
</style>
<!-- Contains page content -->
<div class="dashtwo-order-area" style="padding-top: 10px;">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<?php
				function isWeekend($date)
				{
					$day = intval(date('N', strtotime($date)));
					if ($day >= 6) {
						return 'yes';
					};
					return 'no';
				}
				?>
			</div>
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body" style="overflow-x: scroll;">
						<div class="callout callout-success">
							<form id="timesheetFiltersForm" class="form-horizontal" style="padding-bottom: 2em;" action="javascript:void(0);" method="post">
								<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
								<div class="row">
									<div class="col-md-2">
										<div class="control-group">
											<select class="form-control select2" name="month" id="ts_month">
												<option value="<?php echo $month; ?>"><?php echo strtoupper(date('F', mktime(0, 0, 0, $month, 10))) . " (Showing below)"; ?></option>
												<option value="01">JANUARY</option>
												<option value="02">FEBRUARY</option>
												<option value="03">MARCH</option>
												<option value="04">APRIL</option>
												<option value="05">MAY</option>
												<option value="06">JUNE</option>
												<option value="07">JULY</option>
												<option value="08">AUGUST</option>
												<option value="09">SEPTEMBER</option>
												<option value="10">OCTOBER</option>
												<option value="11">NOVEMBER</option>
												<option value="12">DECEMBER</option>
											</select>
										</div>
									</div>
									<div class="col-md-2">
										<div class="control-group">
											<select class="form-control select2" name="year" id="ts_year">
												<option><?php echo $year; ?></option>
												<?php for ($i = -5; $i <= 25; $i++) {  ?>
													<option><?php echo 2017 + $i; ?></option>
												<?php }  ?>
											</select>
										</div>
									</div>
									<div class="col-md-2">
										<div class="control-group">
											<?php
											$facility = $this->session->userdata['facility'];
											//print_r($facility);
											$employees = Modules::run("employees/get_employees"); ?>
											<select class="form-control select2" name="empid" id="ts_empid" select2>
												<option value="" selected disabled>SELECT EMPLOYEE</option>
												<?php foreach ($employees as $employee) {  ?>
													<option value="<?php echo $employee->ihris_pid ?>"><?php echo $employee->surname . ' ' . $employee->firstname . ' ' . $employee->othername; ?></option>
												<?php }  ?>
											</select>
										</div>
									</div>
									<div class="col-md-2">
										<select name="job" id="ts_job" class="form-control select2">
											<option value="">SELECT JOB</option>
											<?php $jobs = Modules::run("jobs/getJobs");
											foreach ($jobs as $element) {
											?>
												<option value="<?php echo $element->job; ?>" <?php if ($this->input->post('job') == $element->job) {
																									echo "selected";
																								} ?>><?php echo $element->job; ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="col-md-4">
										<div class="control-group">
											<button type="button" id="ts_apply" class="btn bg-gray-dark color-pale" style="font-size:12px;"><i class="fa fa-tasks" aria-hidden="true"></i>Apply</button>
											<a target="_blank" id="ts_print" href="<?php echo base_url(); ?>employees/print_timesheet/<?php echo $month . '/' . $year . '/emp/job'; ?>" class="btn bg-gray-dark color-pale" style="font-size:12px;"><i class="fa fa-print" aria-hidden="true"></i>Print</a>
											<a target="_blank" id="ts_excel" href="<?php echo base_url(); ?>employees/csv_timesheet/<?php echo $month . '/' . $year . '/emp/job'; ?>" class="btn bg-gray-dark color-pale" style="font-size:12px;"><i class="fa fa-file-excel" aria-hidden="true"></i>Excel</a>
										</div>
										<?php //echo $this->uri->segment(2); 
										?>
									</div>
								</div>
							</form>
						</div>
						<span class="pull-left"><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"></span>
						<h4 class="panel-title"> MONTHLY TIMESHEET
							<?php
							echo " - " . $_SESSION['facility_name'] . " ";
							if (empty($month)) {
								$month = date('m');
								$year = date('Y');
							}
							echo "              " . date('F, Y', strtotime($year . "-" . $month));
							?>
						</h4>
						<br>
						<div class="table-responsive">
							<table id="timesheetTable" class="table table-bordered table-striped table-sm" style="width:100%">
								<thead>
									<tr id="ts_header_row"></tr>
								</thead>
								<tbody></tbody>
							</table>
						</div>

						<script>
						$(document).ready(function() {
							var table = null;

							function getFilters() {
								return {
									month: $('#ts_month').val() || '<?php echo $month; ?>',
									year: $('#ts_year').val() || '<?php echo $year; ?>',
									empid: $('#ts_empid').val() || '',
									job: $('#ts_job').val() || ''
								};
							}

							function isWeekendDate(dt) {
								var d = new Date(dt.getFullYear(), dt.getMonth(), dt.getDate());
								var dow = d.getDay(); // 0=Sun,6=Sat
								return (dow === 0 || dow === 6);
							}

							function updatePrintLinks() {
								var f = getFilters();
								var empPart = 'emp' + encodeURIComponent(f.empid || '');
								var jobPart = 'job' + encodeURIComponent(f.job || '');
								$('#ts_print').attr('href', '<?php echo base_url(); ?>employees/print_timesheet/' + f.month + '/' + f.year + '/' + empPart + '/' + jobPart);
								$('#ts_excel').attr('href', '<?php echo base_url(); ?>employees/csv_timesheet/' + f.month + '/' + f.year + '/' + empPart + '/' + jobPart);
							}

							function buildHeader(month, year) {
								var $row = $('#ts_header_row');
								$row.empty();
								$row.append('<th style="width:50px;">#</th>');
								$row.append('<th style="min-width:220px;">Name</th>');
								$row.append('<th style="min-width:160px;">Position</th>');
								var y = parseInt(year, 10);
								var m = parseInt(month, 10);
								if (!y || !m) {
									y = new Date().getFullYear();
									m = new Date().getMonth() + 1;
								}
								var daysInMonth = new Date(y, m, 0).getDate();
								for (var i = 1; i <= daysInMonth; i++) {
									var dt = new Date(y, m - 1, i);
									var weekend = isWeekendDate(dt);
									var label = dt.getDate();
									var style = 'min-width:28px; text-align:center;' + (weekend ? ' background-color:#ffcccc;' : '');
									$row.append('<th style="' + style + '">' + label + '</th>');
								}
								$row.append('<th style="min-width:70px;">Hrs</th>');
								$row.append('<th style="min-width:90px;">Days</th>');
								$row.append('<th style="min-width:90px;">% Present</th>');
							}

							function initOrReinitTable() {
								var f = getFilters();
								buildHeader(f.month, f.year);

								// 3 fixed columns + daysInMonth + 3 summary columns
								var y = parseInt(f.year, 10);
								var m = parseInt(f.month, 10);
								if (!y || !m) {
									y = new Date().getFullYear();
									m = new Date().getMonth() + 1;
								}
								var daysInMonth = new Date(y, m, 0).getDate();
								var columns = [];
								for (var i = 0; i < (3 + daysInMonth + 3); i++) {
									columns.push({ data: i });
								}

								if (table) {
									table.destroy();
									$('#timesheetTable tbody').empty();
								}

								table = $('#timesheetTable').DataTable({
									processing: true,
									serverSide: true,
									searching: false,
									pageLength: 20,
									scrollX: true,
									ordering: false,
									ajax: {
										url: '<?php echo base_url("employees/timesheetAjax"); ?>',
										type: 'POST',
										data: function(d) {
											var f2 = getFilters();
											d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
											d.month = f2.month;
											d.year = f2.year;
											d.empid = f2.empid;
											d.job = f2.job;
										},
										error: function(xhr, error, thrown) {
											console.error('Timesheet DataTables error', { xhr: xhr, error: error, thrown: thrown, responseText: xhr.responseText });
										}
									},
									columns: columns,
									dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6">>' +
										 '<"row"<"col-sm-12"tr>>' +
										 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
								});
							}

							// Prevent any form submit (all AJAX now)
							$('#timesheetFiltersForm').on('submit', function(e) {
								e.preventDefault();
								return false;
							});

							$('#ts_apply').on('click', function() {
								updatePrintLinks();
								if (table) table.ajax.reload();
							});

							// Rebuild table when month/year changes (days columns change)
							$('#ts_month, #ts_year').on('change', function() {
								updatePrintLinks();
								initOrReinitTable();
							});

							// Simple reload when employee/job changes
							$('#ts_empid, #ts_job').on('change', function() {
								updatePrintLinks();
								if (table) table.ajax.reload();
							});

							updatePrintLinks();
							initOrReinitTable();
						});
						</script>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>