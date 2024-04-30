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
	// echo $state;
} //color



echo $state;
if (count($duties) > 0) {
?>
<?php } ?>

<?php
$pv = $this->input->post('year').'-'.$this->input->post('month');

$posted_date = date('Y-m', strtotime($pv));
$current_value = date('Y-m');
$posted_timestamp = strtotime($posted_date);
$current_timestamp = strtotime(date('Y-m') . ' +1 day');

if ($posted_timestamp > $current_timestamp) {
	$state = "disabled";
}

//print_r($state);

?>
<div class="card">
	<div class="">
	</div>
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body" style="overflow-x: scroll;">
						<div class="callout callout-success">
							<form class="form-horizontal" style="padding-bottom: 2em;" action="<?php echo base_url(); ?>rosta/tabular" method="post">
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
															<?php
															$currentYear = date("Y");
															for ($i = -5; $i <= 0; $i++) {
																$yearToAdd = $currentYear + $i;
																?>
																<option><?php echo $yearToAdd; ?></option>
															<?php } ?>
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
								<p style="text-align:center; font-weight:bold; font-size:20px;">
									MONTHLY DUTY ROSTER FOR
									<?php
									echo " - " . $_SESSION['facility_name'];
									echo "              " . date('F, Y', strtotime($year . "-" . $month));
									?></p>
							</div>
							<div class="row pull-right" style="padding: 0.5rem;"> <?php echo $links; ?> </div>
						</div>
						<div id="table" class="tabtable" style="max-width: 100%;">
							<div class="header-row tbrow">
								<span class="cell tbprimary"># <b id="name"></b></span>
								<span class="cell name">Name</span>
								<span class="cell">Position</span>
								<?php
								$monthdays = cal_days_in_month(CAL_GREGORIAN, $month, $year); // get days in a month
								for ($i = 1; $i < ($monthdays + 1); $i++) {
									$dy = $i;
									if ($i < 10) {
										$dy = "0" . $i;
									}
									$wekday = $year . "-" . $month . "-" . $dy;
									if (isWeekend($wekday) == 'yes') {
										$color = "red";
									} else {
										$color = "";
									}
								?>
									<span class="cell" style="padding:0px; text-align: center; border: 1px solid; background-color: <?php echo $color; ?>"><?php echo $i; ?></span>
								<?php } ?>
							</div>
							<?php
							// if beyond tenth disable editing or for other month for non system admins
							$no = (!empty($this->uri->segment(3))) ? $this->uri->segment(3) : 0;
							foreach ($duties as $singleduty) {
								// print_r($singleduty);
								$no++;
							?>
								<div class="table-row tbrow">
									<input type="radio" name="expand" class="fa fa-angle-double-down trigger">
									<span class="cell tbprimary" style="cursor:pointer;" data-label="#"><?php echo $no; ?>
										<b id="name">. &nbsp;<span onclick="$('.trigger').click();"><?php echo $singleduty['fullname']; ?></span></b>
									</span>
									<span class="cell  text-left name" data-label="Name"><?php echo $singleduty['fullname']; ?></span>
									<span class="cell text-left" data-label="Position"><?php echo character_limiter($singleduty['job']); ?>
									</span>
									<?php
									for ($i = 1; $i < ($monthdays + 1); $i++) {
	
										$date_d = $year . "-" . $month . "-" . (($i < 10) ? "0" . $i : $i);
										$pid    = $singleduty['ihris_pid'];
										$entry_id = $year . "-" . $month . "-" . (($i < 10) ? "0" . $i : $i) . $singleduty['ihris_pid'];
										$duty_letter = retrieve_schedule($pid, $date_d);
										//determine whetehr to update or insert on ajax
										$record_type = (!empty($duty_letter)) ? "update duty" : "new duty";
									?>
										<span class="cell" data-label="Day<?php echo $i; ?>">
											<input type="text" style="padding:0px; text-align: center;" class="<?php echo $record_type ?>" id="<?php echo $entry_id; ?>" day="<?php echo $i; ?>" pid="<?php echo $pid; ?>" pattern="[A-Za-z]+" size="1px" maxlength="1" title="Letters only for Duty" value="<?php echo $duty_letter; ?>" <?php echo @$state; ?>>
										</span>
									<?php } // end for , one that loops tds 
									?>
								</div>
							<?php }
							?>
						</div>
						<div class="row pull-right" style="padding: 0.5rem;"> <?php echo $links; ?> </div>
						<?php if ($state != "" && $_SESSION['role'] !== "sadmin") {
							echo "<center><h4><font color='red'>  Editing is locked , please contact the Admin</font></h4></center>";
						}
						?>
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
	$('.new').keyup(function(event) {
		if (event.keyCode == 13) {
			textboxes = $("input.duty");
			currentBoxNumber = textboxes.index(this);
			if (textboxes[currentBoxNumber + 1] != null) {
				nextBox = textboxes[currentBoxNumber + 1];
				nextBox.focus();
				nextBox.select();
			}
			event.preventDefault();
			return false;
		} //if enter key is pressed
		else { //if not enter key
			var letter = $(this).val(); //input letter
			if (letter !== "") {
				var hpid = $(this).attr('pid'); // person_id
				var entry_id = $(this).attr('id'); // person_id
				var day = $(this).attr('day'); //day date e.g 3rd; 3
				var schedules = <?php echo json_encode($tab_schedules); ?>;
				//check if letter is a valid schedule
				letter = letter.replace(/\s/g, ''); //remove spaces
				letter = letter.toUpperCase(); //converte to upper case
				var duty = schedules["'" + letter + "'"]; // get the schedule id
				if (typeof duty == "undefined") { // if letter is not defined as shift lettter
					$.notify("Warning: That letter doesn't represent any schedule", "warn");
					$(this).val('');
				} else {
					var color = pickColor(duty); // get corresponding color for calendar rota
					if (day < 10) {
						day = "0" + day;
					}
					var month = $('#month').val();
					var year = $('#year').val();
					var start = year + "-" + month + "-" + day; //full duty date
					var entry = entry_id; //start + hpid; //entry id
					var startDate = new Date(start);
					// seconds * minutes * hours * milliseconds = 1 day 
					var day = 60 * 60 * 24 * 1000;
					var end = new Date(startDate.getTime() + day);
					end = "";
					$(this).val(letter);
					$.post('<?php echo base_url(); ?>rosta/addEvent', {
						hpid: hpid,
						duty: duty,
						color: color,
						start: start,
						end: end
					}, function(result) {
						console.log(result);
						$(this).prop('id', entry);
						$(this).addClass('update');
						$(this).removeClass('new');
						$.notify("Scheduled Saved", "success");
					})
				} // else for letter undefined
			} //if letter is not empty
		} //end if not enter key
	})
	$('.update').keyup(function(event) {
		if (event.keyCode == 13) {
			textboxes = $("input.duty");
			currentBoxNumber = textboxes.index(this);
			if (textboxes[currentBoxNumber + 1] != null) {
				nextBox = textboxes[currentBoxNumber + 1];
				nextBox.focus();
				nextBox.select();
			}
			event.preventDefault();
			return false;
		} //if enter key is pressed
		else { //if not enter key
			var letter = $(this).val(); //input letter
			if (letter !== "") {
				var id = $(this).attr('id'); // entry_id
				var hpid = $(this).attr('pid'); // person_id
				var schedules = <?php echo json_encode($tab_schedules); ?>;
				//check if letter is a valid schedule
				letter = letter.replace(/\s/g, ''); //remove spaces
				letter = letter.toUpperCase(); //converte to upper case
				var duty = schedules["'" + letter + "'"]; // get the schedule id
				if (typeof duty == "undefined") { // if letter is not defined as shift lettter
					$.notify("Warning: That letter doesn't represent any schedule", "warn");
					$(this).val('');
				} else {
					var color = pickColor(duty); // get corresponding color for calendar rota
					$(this).val(letter);
					$.post('<?php echo base_url(); ?>calendar/updateEvent', {
						id: id,
						hpid: hpid,
						duty: duty,
						color: color
					}, function(result) {
						console.log(result);
						$.notify("Update Finished", "info");
					}); //end post
				} // else for letter undefined
			} //if letter is not empty
		} //if not enter key
	})
	//color picking function
	function pickColor(duty) {
		if (duty == '14') {
			var kala = '#297bb2';
		} else
		if (duty == '15') {
			//even
			var kala = '#245270';
		} else
			//night
			if (duty == '16') {
				var kala = '#2f446b';
			}
		else
			//off
			if (duty == '17') {
				var kala = '#d1a110';
			}
		else
			//annual leave
			if (duty == '18') {
				var kala = '#B22222';
			}
		else
			//study leave
			if (duty == '19') {
				var kala = '#FF8C00';
			}
		else
			//maternity leave
			if (duty == '20') {
				var kala = '#9ACD32';
			}
		else
			//other
			if (duty == '21') {
				var kala = '#32CD32';
			}
		return kala;
	}
</script>