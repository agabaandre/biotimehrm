<style>
	.cnumber {
		width: 3%;
	}

	.cname {
		text-align: left;
		padding-left: 1.5em;
		width: 30%;
	}

	@media only screen and (max-width: 980px) {
		.cnumber {
			width: 100%;
		}

		.cname {
			padding-left: 0em;
			text-align: left;
			width: 100%;
		}

		.print {
			display: none;
		}
	}
</style>
<section class="content">
	<div class="container-fluid">
		<!-- Main row -->
		<div class="row">
			<div class="col-md-12">
				<div class="callout callout-success">


					<form class="form-horizontal" style="padding-bottom: 2em;" action="<?php echo base_url(); ?>reports/attendance_aggregate" method="post">
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
									<select class="form-control select2" name="group_by" onchange="this.form.submit()">
										<?php foreach ($aggregations as $key => $value): ?>
											<option value="<?php echo $value; ?>"><?php echo ucwords(str_replace("_"," ",$value)); ?></option>
									    <?php endforeach; ?>
									</select>

								</div>
							</div>

							<div class="col-md-3">

								<div class="control-group">

									<button type="submit" name="" class="btn bg-gray-dark color-pale" style="font-size:12px;">Apply</button>
									<?php
									if (count($records) > 0) {
									?>
										<a href="<?php echo base_url() ?>attendance/print_attrowmary/<?php echo $year . "-" . $month; ?>" style="font-size:12px;" class="btn bg-gray-dark color-pale" target="_blank"><i class="fa fa-print"></i>Print</a>
									
										<a href="<?php echo base_url(); ?>attendance/attrows_csv/<?php echo $year . "-" . $month; ?>" style="font-size:12px;" class="btn bg-gray-dark color-pale"><i class="fa fa-file"></i> Export CSV</a>
									<?php } ?>
								</div>

							</div>

						</div>
				</div>
				</form>
			</div>

		</div>

		<div class="panel-body">
			<div class="row pull-right" style="padding: 0.5rem;"> <?php echo $links; ?> </div>
			
			<div class="col-md-3" style="border-right: 0; border-left: 0; border-top: 0;"><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"></div>
			<div class="col-md-12" style="border-right: 0; border-left: 0; border-top: 0;">
				<p style="font-size: 16px; font-weight:bold; margin:0 auto; ">
					<?php
					if (count($records) < 1) {
						echo "<font color='red'> No Schedule Data</font>";
					} else {
					?>
						MONTHLY ATTENDANCE TO DUTY SUMMARY
					<?php echo " - "  . date('F, Y', strtotime($year . "-" . $month));
					}
					 
					?>
				</p>
			</div>
			<div id="table">
				<div class="header-row tbrow">
					<span class="cell stcell  tbprimary cnumber"># <b id="name"></b></span>
					<span class="cell stcell"><?php echo  ucwords(str_replace("_"," ",$grouped_by));; ?></span>
					<span class="cell stcell ">Present</span>
					<span class="cell stcell ">Off Duty</span>
					<span class="cell stcell ">Official Request</span>
					<span class="cell stcell ">Leave</span>
					<span class="cell stcell ">Holiday</span>
					<span class="cell stcell ">Absent</span>
					<span class="cell stcell ">% Worked</span>
					<span class="cell stcell ">% Absent</span>
					</div>
				<?php 

				$mydate = $year . "-" . $month;				
				$no = (!empty($this->uri->segment(3))) ? $this->uri->segment(3) : 1;

				foreach ($records as $row) {


				$days_worked   = $row->present + $row->off + $row->official + $row->holiday + $row->own_leave;
				$supposed_days = aggregate_att_count($grouped_by,$row->{$grouped_by},$period);

				$attendance_rate = ($days_worked/$supposed_days)*100;
				$absentism_rate = ( ($supposed_days-$days_worked)/$supposed_days)*100;

				 ?>
					<div class="table-row tbrow strow">
						<input type="radio" name="expand" class="fa fa-angle-double-down trigger">
						<span class="cell stcell" data-label="#">
							<?php echo $no; ?>
						</span>
						<span class="cell stcell  cname" data-label="Aggregated By">
							<?php echo $row->{$grouped_by}; ?>
						</span>
						<span class="cell stcell " data-label="Present">
							<?php echo $row->present; ?>
						</span>
						<span class="cell stcell " data-label="Off Duty">
						<?php echo $row->off; ?>
						</span>
						<span class="cell stcell " data-label="Official Request">
							<?php echo $row->official; ?>
						</span>
						<span class="cell stcell " data-label="Leave">
							<?php echo $row->own_leave; ?>	
						</span>
						<span class="cell stcell " data-label="Holiday">
							<?php echo $row->holiday; ?>	
						</span>
						<span class="cell stcell " data-label="Absent">
							<?php echo $row->absent; ?>	
						</span>
						<span class="cell stcell " data-label="% Present"><?php  echo number_format($attendance_rate,1) ?>%</span>
						<span class="cell stcell "  data-label="% Absent"><?php  echo number_format($absentism_rate,1) ?>%</span>
			
					</div>
				<?php
					$no++;
				} ?>
			</div>
		</div>
	</div>
	<div class="row pull-right" style="padding: 0.5rem;"> <?php echo $links; ?> </div>
	</div>
	</div>
	</div>
</section>


<script type="text/javascript">
	var url = window.location.href;

	if (url == '<?php echo base_url(); ?>reports/attendance_aggregate') {
		$('.sidebar-mini').addClass('sidebar-collapse');
	}

	$('.csv').click(function(e) {
		e.preventDefault();
		$.ajax({
			url: '<?php echo base_url(); ?>attendance/attrows_csv',
			success: function(res) {
				console.log(res);
			}
		})
	})
</script>