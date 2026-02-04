<?php
function isWeekend($date)
{
	$day = intval(date('N', strtotime($date)));
	if ($day >= 6) {
		return 'yes';
	};
	return 'no';
}
function dayState($day, $scheduled)
{
	$user = $_SESSION['role'];
	//its today or day in the past
	if (strtotime($day) < strtotime(date('Y-m-d')) && !empty($scheduled) && $user !== 'sadmin') {
		$state = "disabled";
	} else if (strtotime($day) < strtotime(date('Y-m-d')) && empty($scheduled) && $user !== 'sadmin') {
		$state = "";
	}
	//if they are scheduled to work
	if (strtotime($day) > strtotime(date('Y-m-d'))) {
		$state = "disabled";
	}
	echo $state;
} //color
if (count($duties) > 0) {
?>
<?php } ?>
<div class="card">
	<div class="">
	</div>
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body" style="overflow-x: scroll;">
						<div class="callout callout-success">
							<form id="rosterFiltersForm" class="form-horizontal" style="padding-bottom: 2em;" action="javascript:void(0);" method="post">
								<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
								<div class="row">
									<div class="col-md-2">
										<div class="control-group">
											<input type="hidden" id="month" value="<?php echo $month; ?>">
											<select class="form-control select2" name="month" id="roster_month">
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
											<input type="hidden" id="year" value="<?php echo $year; ?>">
											<select class="form-control select2" name="year" id="roster_year">
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
											$employees = Modules::run("employees/get_employees"); ?>
											<select class="form-control select2" name="empid" id="roster_empid" select2>
												<option value="" selected disabled>Select Employee</option>
												<?php foreach ($employees as $employee) {  ?>
													<option value="<?php echo $employee->ihris_pid ?>"><?php echo $employee->surname . ' ' . $employee->firstname . ' ' . $employee->othername; ?></option>
												<?php }  ?>
											</select>
										</div>
									</div>
									<div class="col-md-6">
										<div class="control-group">
											<button type="button" id="roster_apply" class="btn bg-gray-dark color-pale" style="font-size:12px;"><i class="fa fa-tasks" aria-hidden="true"></i>Apply</button>
											<a id="roster_print" href="<?php echo base_url() ?>rosta/print_roster/<?php echo $year . "/" . $month; ?>" class="btn bg-gray-dark color-pale" target="_blank" style="font-size:12px;"><i class="fa fa-print"></i>Print</a>
											<a id="roster_csv" href="<?php echo base_url() ?>rosta/roster_csv/<?php echo $year . "/" . $month; ?>" class="btn bg-gray-dark color-pale" style="font-size:12px;"><i class="fa fa-file-excel"></i>CSV</a>
										</div>
									</div>
								</div>
							</form>
						</div>
						<div class="callout callout-success">
							<p class="" style="text-align: center; margin-top: 5px; font-weight:bold; font-size: 1rem;"> Duty Roster Key</p>
							<hr style="color:#15b178;">
							<?php $colors = Modules::run('schedules/getrosterKey'); ?>
							<div class="col-lg-12" style="text-align:center;">
								<p style="text-align:center; font-weight:bold; font:14rem;"></p>
								<?php foreach ($colors as $color) { ?>
									<button type="button" class="btn btn-sm btnkey bg-gray-dark color-pale"><?php echo $color->schedule; ?> (<?php echo $color->letter; ?>)
									</button>
								<?php  } ?>
							</div>
						</div>
						<style>
							.btnkey {
								min-width: 100px;
								;
								color: #fff;
								margin: 2px;
								font-size: 11px;
								overflow: hidden;
							}

							.tabtable {
								zoom: 85%;
							}

							@media only screen and (max-width: 600px) {
								.btnkey {
									width: 100%;
								}
							}
						</style>
						<?php
						?>
						<div class="row">
							<div class="col-md-12">
								<img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px" style="float:left;">
							</div>
							<div class="col-md-12" style="border-right: 0; border-left: 0; border-top: 0; margin:0 auto;">
								<p id="roster_report_title" style="text-align:center; font-weight:bold; font-size:20px;" data-facility-name="<?php echo htmlspecialchars(isset($_SESSION['facility_name']) ? $_SESSION['facility_name'] : 'Ministry of Health'); ?>">
									MONTHLY DUTY ROSTER REPORT FOR - <?php echo htmlspecialchars(isset($_SESSION['facility_name']) ? $_SESSION['facility_name'] : 'Ministry of Health'); ?> <?php echo date('F, Y', strtotime($year . "-" . $month)); ?>
								</p>
							</div>
						</div>
						<table id="roster_table" class="table table-bordered table-striped table-condensed" style="width:100%; font-size:11px;"></table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
<script type="text/javascript">
	var url = window.location.href;
	if (url == '<?php echo base_url(); ?>rosta/tabular') {
		$('.fixed-top').addClass('mini-navbar');
	}

	$(document).ready(function() {
		var baseUrl = '<?php echo base_url(); ?>';
		var month = '<?php echo $month; ?>';
		var year = '<?php echo $year; ?>';
		var monthDays = <?php echo (int)cal_days_in_month(CAL_GREGORIAN, $month, $year); ?>;
		var rosterTable = null;
		var tableColumns = null;

		function getDaysInMonth(monthNum, yearNum) {
			var m = parseInt(monthNum, 10);
			var y = parseInt(yearNum, 10);
			return new Date(y, m, 0).getDate();
		}

		function isWeekend(dateStr) {
			var date = new Date(dateStr);
			var day = date.getDay();
			return (day === 0 || day === 6);
		}

		function updateRosterReportTitle(monthVal, yearVal) {
			var facilityName = $('#roster_report_title').attr('data-facility-name') || 'Ministry of Health';
			var dateObj = new Date(parseInt(yearVal, 10), parseInt(monthVal, 10) - 1, 1);
			var monthName = dateObj.toLocaleString('en-US', { month: 'long' });
			$('#roster_report_title').text('MONTHLY DUTY ROSTER REPORT FOR - ' + facilityName + ' ' + monthName + ', ' + yearVal);
		}

		function buildColumns(monthVal, yearVal, daysInMonth) {
			var cols = [];
			cols.push({ data: 'rownum', title: '#', className: 'text-center', width: '40px', orderable: false });
			cols.push({ data: 'fullname', title: 'Name', className: 'text-left', width: '120px', orderable: false });
			cols.push({ data: 'job', title: 'Position', className: 'text-left', width: '120px', orderable: false });

			for (var d = 1; d <= daysInMonth; d++) {
				var dayStr = (d < 10) ? '0' + d : d.toString();
				var ymd = yearVal + '-' + monthVal + '-' + dayStr;
				var isWeekendDay = isWeekend(ymd);
				var headerClass = isWeekendDay ? 'text-center weekend-header' : 'text-center';
				var headerStyle = isWeekendDay ? 'background-color:red; color:#FFFFFF;' : '';
				cols.push({
					data: 'd' + d,
					title: '<span style="' + headerStyle + '">' + d + '</span>',
					className: headerClass,
					width: '22px',
					orderable: false
				});
			}
			return cols;
		}

		function updateExportLinks() {
			var selMonth = $('#roster_month').val() || month;
			var selYear = $('#roster_year').val() || year;
			var empid = $('#roster_empid').val() || '';

			var printUrl = baseUrl + 'rosta/print_roster/' + selYear + '/' + selMonth;
			var csvUrl = baseUrl + 'rosta/roster_csv/' + selYear + '/' + selMonth;

			if (empid) {
				printUrl += '/' + encodeURIComponent(empid);
				csvUrl += '/' + encodeURIComponent(empid);
			}

			$('#roster_print').attr('href', printUrl);
			$('#roster_csv').attr('href', csvUrl);
		}

		function applyRosterFilter() {
			var selMonth = $('#roster_month').val() || month;
			var selYear = $('#roster_year').val() || year;
			updateRosterReportTitle(selMonth, selYear);
			updateExportLinks();
			var newDays = getDaysInMonth(selMonth, selYear);
			var monthOrYearChanged = (selMonth !== month || selYear !== year);
			if (monthOrYearChanged) {
				month = selMonth;
				year = selYear;
				monthDays = newDays;
				if (rosterTable && $.fn.DataTable.isDataTable('#roster_table')) {
					rosterTable.destroy();
					$('#roster_table').empty();
				}
				tableColumns = buildColumns(month, year, monthDays);
				rosterTable = $('#roster_table').DataTable({
					processing: true,
					serverSide: true,
					searching: false,
					ordering: false,
					pageLength: 50,
					lengthChange: true,
					lengthMenu: [[25, 50, 100, 200], [25, 50, 100, 200]],
					pagingType: 'simple_numbers',
					dom: '<"top"lp>rt<"bottom"ip><"clear">',
					ajax: {
						url: baseUrl + 'rosta/fetch_reportAjax',
						type: 'POST',
						data: function(d) {
							d.month = $('#roster_month').val() || month;
							d.year = $('#roster_year').val() || year;
							d.empid = $('#roster_empid').val() || '';
							d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
						}
					},
					columns: tableColumns,
					scrollX: true
				});
			} else {
				month = selMonth;
				year = selYear;
				if (rosterTable) {
					rosterTable.ajax.reload();
				}
			}
		}

		// Initial build
		tableColumns = buildColumns(month, year, monthDays);
		rosterTable = $('#roster_table').DataTable({
			processing: true,
			serverSide: true,
			searching: false,
			ordering: false,
			pageLength: 50,
			lengthChange: true,
			lengthMenu: [[25, 50, 100, 200], [25, 50, 100, 200]],
			pagingType: 'simple_numbers',
			dom: '<"top"lp>rt<"bottom"ip><"clear">',
			ajax: {
				url: baseUrl + 'rosta/fetch_reportAjax',
				type: 'POST',
				data: function(d) {
					d.month = $('#roster_month').val() || month;
					d.year = $('#roster_year').val() || year;
					d.empid = $('#roster_empid').val() || '';
					d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
				}
			},
			columns: tableColumns,
			scrollX: true
		});

		updateExportLinks();

		$('#roster_apply').on('click', function(e) {
			e.preventDefault();
			applyRosterFilter();
		});

		$('#roster_month, #roster_year').on('change', function() {
			applyRosterFilter();
		});

		$('#roster_empid').on('change', function() {
			updateExportLinks();
			if (rosterTable) {
				rosterTable.ajax.reload();
			}
		});
	});
</script>