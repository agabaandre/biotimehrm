<?php
include_once("includes/head.php");
include_once("includes/topbar.php");
include_once("includes/sidenav.php");
//include_once("");
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<!-- Main content -->
	<section class="content">
		<!-- Small boxes (Stat box) -->
		<div class="row">
			<div class="col-md-8" style="padding-bottom:0.5em;">
				<form class="form-horizontal" style="padding-bottom: 2em;" action="<?php echo base_url(); ?>rosta/presence" method="post">
					<div class="col-md-4">
						<div class="control-group">
							<input type="hidden" id="month" value="<?php echo $month; ?>">
							<select class="form-control" name="month" onchange="this.form.submit()">
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
					<div class="col-md-4">
						<div class="control-group">
							<input type="hidden" id="year" value="<?php echo $year; ?>">
							<select class="form-control" name="year" onchange="this.form.submit()">
								<option><?php echo date('Y'); ?></option>
								<option><?php echo date('Y') - 1; ?></option>
								<option><?php echo date('Y') + 1; ?></option>
							</select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="control-group">
							<input type="submit" name="" value="Load Month" class="btn btn-success">
						</div>
					</div>
				</form>
			</div>
			<div class="col-md-4">
				<?php
				if (count($duties) > 0) {
				?>
					<a href="<?php echo base_url() ?>rosta/print_presence/<?php echo $year . "-" . $month; ?>" style="" class="btn btn-success btn-sm" target="_blank"><i class="fa fa-print"></i>Print</a>
				<?php } ?>
			</div>
			<div class="col-md-12">
				<div class="panel">
					<div class="panel-header">
					</div>
					<div class="panel-body">
						<?php
						//print_r($duties);   //carries report data
						//print_r($matches);  //carries person's day's duty letter
						?>
						<table class="table table-striped table-bordered" width="100%">
							<tr style="border-right: 0; border-left: 0; border-top: 0;">
								<td colspan=4 style="border-right: 0; border-left: 0; border-top: 0;"><img src="<?php echo base_url(); ?>assets/images/MOH.png" width="100px"></td>
								<td colspan=30 style="border-right: 0; border-left: 0; border-top: 0;">
									<h4>
										<?php
										if (count($duties) < 1) {
											echo "<font color='red'> No Schedule Data</font>";
										} else {
										?>
											MONTHLY DAILY ATTENDANCE REPORT FOR HEALTH PERSONNEL
											<?php
											echo " - " . $duties[0]['facility'] . "<br>";
											echo "              " . date('F, Y', strtotime($year . "-" . $month));
											//print_r($nonworkables);
											?>
										<?php } ?>
									</h4>
								</td>
							</tr>
							<tr>
								<th>#</th>
								<th>Name</th>
								<th>Position</th>
								<th>01</th>
								<th>02</th>
								<th>03</th>
								<th>04</th>
								<th>05</th>
								<th>06</th>
								<th>07</th>
								<th>08</th>
								<th>09</th>
								<th>10</th>
								<th>11</th>
								<th>12</th>
								<th>13</th>
								<th>14</th>
								<th>15</th>
								<th>16</th>
								<th>17</th>
								<th>18</th>
								<th>19</th>
								<th>20</th>
								<th>21</th>
								<th>22</th>
								<th>23</th>
								<th>24</th>
								<th>25</th>
								<th>26</th>
								<th>27</th>
								<th>28</th>
								<th>29</th>
								<th>30</th>
								<th>31</th>
							</tr>
							<tbody>
								<?php
								//color the duty date according to whether one worked
								function dayColor($day, $dayletter, $leaves)
								{
									//if they did not work in past days
									if (strtotime($day) <= strtotime(date('Y-m-d'))) {
										if (in_array($dayletter, $leaves)) {
											$color = "#000";
										} else {
											$color = "red";
										}
									}
									//if they are scheduled to work
									if (strtotime($day) > strtotime(date('Y-m-d'))) {
										$color = "navy";
									}
									$font = "<font color='" . $color . "'><b>";
									return $font;
								} //color
								$no = 0;
								//$nonworkables contains non duty days
								//$workeddays contains  worked days
								foreach ($duties as $singleduty) {
									$no++;
								?>
									<tr>
										<td><?php echo $no; ?></td>
										<td><?php echo $singleduty['fullname']; ?></td>
										<td><?php $words = explode(" ", $singleduty['job']);
											$letters = "";
											foreach ($words as $word) {
												$letters .= $word[0];
											}
											echo $letters;
											?></td>
										<?php
										$month_days = cal_days_in_month(CAL_GREGORIAN, $month, $year); //days in a month
										for ($i = 1; $i <= $month_days; $i++) { // repeating td
											$day = "day" . $i;  //changing day
											$was_present = "<font color='green'><b>P</b></font>"; //employee worked
											$leave_taken = "<font color='green'><b>"; //employee took leave
										?>
											<td>
												<?php if ($singleduty[$day] != '') {
													$dayentry = $singleduty[$day] . $singleduty['ihris_pid'];  //duty letter for day
													if (!in_array($dayentry, $nonworkables) && in_array($dayentry, $workeddays)) {
														echo $was_present; //employee did not work  on a their duty day
													} else if ((!in_array($dayentry, $nonworkables) && !in_array($dayentry, $workeddays))) {
														$coloredday = dayColor($singleduty[$day], $dayentry, $nonworkables); //.coloring the letter comes with <font color="color"><b>
														echo $coloredday . $matches[$dayentry] . "</b></font>"; //employee worked/ is scheduled to work their duty day...coloring the letter
													} else if (in_array($dayentry, $nonworkables) && !in_array($dayentry, $workeddays)) {
														echo $leave_taken . $matches[$dayentry] . "</b></font>"; //employee took their leave day with green color
													} else if (in_array($dayentry, $nonworkables) && in_array($dayentry, $workeddays)) {
														echo $was_present; //user worked on a leave day
													}
												}; ?></td>
										<?php } //repeat days 
										?>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<!--col 12-->
		</div>
		<!-- /.content-row -->
	</section>
	<!-- /.section-->
</div>
<!-- /.content-wrapper -->
<?php
include_once("includes/footermain.php");
include_once("includes/rightsidebar.php");
include_once("includes/footer.php");
?>
<script type="text/javascript">
	var url = window.location.href;
	if (url == '<?php echo base_url(); ?>rosta/presence') {
		$('.sidebar-mini').addClass('sidebar-collapse');
	}
</script>