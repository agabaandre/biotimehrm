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
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body" style="overflow-x: scroll;">
						<div class="callout callout-success">
							<form class="form-horizontal" style="padding-bottom: 2em;" action="<?php echo base_url(); ?>rosta/attfrom_report" method="post">
								<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
								<div class="row">
									<div class="col-md-3">
										<div class="control-group">
											<input type="hidden" id="month" value="<?php echo $month; ?>">
											<select class="form-control select2" name="month" onchange="this.form.submit()">
												<option value="<?php echo $month; ?>"><?php echo strtoupper(date('F', mktime(0, 0, 0, $month, 10))) . "(Showing below)"; ?></option>
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
									<div class="col-md-3">
										<div class="control-group">
											<input type="hidden" id="year" value="<?php echo $year; ?>">
											<select class="form-control select2" name="year" onchange="this.form.submit()">
												<option><?php echo $year; ?></option>
												<?php for ($i = -5; $i <= 25; $i++) {  ?>
													<option><?php echo 2017 + $i; ?></option>
												<?php }  ?>
											</select>
										</div>
									</div>
									<div class="col-md-3">
										<div class="control-group">
											<?php
											$facility = $this->session->userdata['facility'];
											//print_r($facility);
											$employees = Modules::run("employees/get_employees"); ?>
											<select class="form-control select2" name="empid" select2>
												<option value="" selected disabled>Select Employee</option>
												<?php foreach ($employees as $employee) {  ?>
													<option value="<?php echo $employee->ihris_pid ?>"><?php echo $employee->surname . ' ' . $employee->firstname . ' ' . $employee->othername; ?></option>
												<?php }  ?>
											</select>
										</div>
									</div>
									<div class="col-md-3">
										<div class="control-group">
											<button type="submit" name="" class="btn bg-gray-dark color-pale" style="font-size:12px;">Apply</button>
											<a id="attfrom_print" href="<?php echo base_url() ?>rosta/print_actuals/<?php echo $year . "/" . $month; ?>" class="btn bg-gray-dark color-pale" target="_blank">
												<i class="fa fa-print"></i> Print </a>
											<a id="attfrom_csv" href="<?php echo base_url() ?>rosta/actuals_csv/<?php echo $year . "/" . $month; ?>" class="btn bg-gray-dark color-pale" target="_blank">
												<i class="fa fa-file-excel-o"></i> CSV </a>
										</div>
									</div>
								</div>
							</form>
						</div>
						<div class="callout callout-success">
							<p class="" style="text-align: center; margin-top: 5px; font-weight:bold; font-size: 1rem;"> Attendance Key</p>
							<hr style="color:#15b178;">
							<?php $colors = Modules::run('schedules/getattSchedules'); ?>
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
								<p style="text-align:center; font-weight:bold; font-size:20px;">
									MONTHLY ATTENDANCE REPORT FOR
									<?php
									echo " - " . $_SESSION['facility_name'];
									echo "              " . date('F, Y', strtotime($year . "-" . $month));
									?></p>
							</div>
						
						</div>
						<table id="attfrom_table" class="table table-bordered table-striped table-condensed" style="width:100%; font-size:11px;"></table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	var url = window.location.href;
	if (url == '<?php echo base_url(); ?>rosta/actuals' || url == '<?php echo base_url(); ?>rosta/actuals#') {
		$('.fixed-top').addClass('mini-navbar');
	}

	$(document).ready(function() {
		var baseUrl = '<?php echo base_url(); ?>';
		var month = '<?php echo $month; ?>';
		var year = '<?php echo $year; ?>';
		var monthDays = <?php echo (int)cal_days_in_month(CAL_GREGORIAN, $month, $year); ?>;

		function buildColumns() {
			var cols = [];
			cols.push({
				data: 'rownum',
				title: '#',
				className: 'text-center',
				width: '40px'
			});
			cols.push({
				data: 'fullname',
				title: 'Name',
				className: 'text-left'
			});
			cols.push({
				data: 'job',
				title: 'Position',
				className: 'text-left'
			});

			for (var d = 1; d <= monthDays; d++) {
				cols.push({
					data: 'd' + d,
					title: d.toString(),
					className: 'text-center',
					width: '16px'
				});
			}
			return cols;
		}

		var attTable = $('#attfrom_table').DataTable({
			processing: true,
			serverSide: true,
			searching: false,
			ordering: false,
			pageLength: 50,
			lengthChange: true,
			lengthMenu: [[25, 50, 100, 200], [25, 50, 100, 200]],
			pagingType: 'simple_numbers',
			ajax: {
				url: baseUrl + 'rosta/attfrom_reportAjax',
				type: 'POST',
				data: function(d) {
					d.month = $('#month').val() || month;
					d.year = $('#year').val() || year;
					d.empid = $('select[name="empid"]').val() || '';
					d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
				}
			},
			columns: buildColumns(),
			// Pagination (p) shown both top and bottom, no search box
			dom: '<\"top\"lp>rt<\"bottom\"ip><\"clear\">',
			scrollX: true
		});

		// Apply button reloads the table with new filters
		function updateExportLinks() {
			var selMonth = $('#month').val() || month;
			var selYear = $('#year').val() || year;
			var empid = $('select[name="empid"]').val() || '';

			var printUrl = baseUrl + 'rosta/print_actuals/' + selYear + '/' + selMonth;
			var csvUrl = baseUrl + 'rosta/actuals_csv/' + selYear + '/' + selMonth;

			if (empid) {
				printUrl += '/' + encodeURIComponent(empid);
				csvUrl += '/' + encodeURIComponent(empid);
			}

			$('#attfrom_print').attr('href', printUrl);
			$('#attfrom_csv').attr('href', csvUrl);
		}

		updateExportLinks();

		$('button.btn.bg-gray-dark').on('click', function(e) {
			e.preventDefault();
			attTable.ajax.reload();
			updateExportLinks();
		});

		// When month/year change via dropdown, also refresh
		$('select[name="month"], select[name="year"], select[name="empid"]').on('change', function() {
			attTable.ajax.reload();
			updateExportLinks();
		});
	});
</script>