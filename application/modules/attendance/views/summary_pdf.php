<html>

<head>
	<title>Attendance Summary</title>
	<style>
		body {
			font-family: Arial;
			font-size: 12pt;
			max-width: 21cm;
			max-height: 29.7cm;
		}

		p {
			margin: 0pt;
		}

		table.items {
			border: 0.1mm solid #000000;
		}

		td {
			vertical-align: top;
		}

		.items td {
			border-left: 0.2mm solid #000000;
			border-right: 0.2mm solid #000000;
		}

		table thead th {
			background-color: #EEEEEE;
			text-align: center;
			border: 0.2mm solid #000000;
			/*font-variant: small-caps;*/
		}

		.items tr td {
			border: 0.2mm solid #000000;

		}

		.items td.blanktotal {
			background-color: #EEEEEE;
			border: 0.1mm solid #000000;
			background-color: #FFFFFF;
			border: 0mm none #000000;
			border-top: 0.1mm solid #000000;
			border-right: 0.1mm solid #000000;
		}

		.items td.totals {
			text-align: right;
			border: 0.1mm solid #000000;
		}

		.items td.cost {
			text-align: "."center;
		}

		.logo {
			margin-top: 0em;
			margin-left: 20%;
			margin-right: 20%;
			margin-bottom: 0.5em;
		}

		.heading {
			margin-top: 0.4em;
			margin-left: 20%;
			margin-right: 10%;
			margin-bottom: 0.1em;
		}

		.title {
			margin-top: 0.0em;
			margin-left: 30%;
			margin-right: 10%;
			margin-bottom: 0.1em;
		}


		tr:nth-child(odd) {

			background-color: #e1f4f7;
		}


		td {
			padding: 5px;
		}
	</style>
</head>

<body>


	<table class="items" style="font-size: 12pt; border-collapse: collapse; " cellpadding="8" width="100%">


		<tr style="border-right: 0; border-left: 0; border-top: 0;">
			<td colspan=2 style="border-right: 0; border-left: 0; border-top: 0;"><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"></td>

			<td colspan=12 style="border-right: 0; border-left: 0; border-top: 0;">
				<h4>
					<?php
					if (count($sums) < 1) {

						echo "<font color='red'> No Usable Data</font>";
					} else {

					?>
						<p style="text-align:left;">MONTHLY ATTENDANCE TO DUTY SUMMARY FOR

							<?php
							echo $_SESSION['facility_name'] . " " . date('F, Y', strtotime($dates));

							?>
						</p>

					<?php } ?>

				</h4>
			</td>
		</tr>

		<!-- b -->

		<tr>
			<th>#</th>
			<th>Name</th>
			<th>Job</th>
			<th>Department</th>
			<th>Off Duty</th>
			<th>Official Request</th>
			<th>Leave</th>
			<th>Holiday</th>
			<th>Total Days Expected</th>
			<th>Total Days Worked</th>
			<th>Total Days Absent</th>
			<th> Present %</th>



		</tr>

		<tbody>


			<?php

			$no = 1;
			foreach ($sums as $sum) { ?>


				<tr>
					<td data-label="no"><?php echo $no; ?></td>
					<td data-label="Name"><?php echo $sum['fullname'] . ' ' . $sum['othername']; ?></td>
					<td data-label="Job"><?php echo $sum['job'] ?></td>
					<td data-label="Job"><?php echo $sum['department_id'] ?></td>
					<?php if (!empty($present = $sum['P'])) {
						$present;
					} else {
						$present = 0;
					} ?>
					<td data-label="O"><?php if (!empty($O = $sum['O'])) {
											echo $O;
										} else {
											echo 0;
										} ?></td>
					<td data-label="R"><?php if (!empty($R = $sum['R'])) {
											echo $R;
										} else {
											echo 0;
										} ?></td>
					<td data-label="L"><?php if (!empty($L = $sum['L'])) {
											echo $L;
										} else {
											echo 0;
										} ?></td>
					<td data-label="H"><?php if (!empty($H = $sum['H'])) {
											echo $H;
										} else {
											echo 0;
										} ?></td>

					<?php $roster = Modules::run('attendance/attrosta', $dates, urlencode($sum['ihris_pid']));

					$day = $roster['Day'][0]->days;
					$eve = $roster['Evening'][0]->days;
					$night = $roster['Night'][0]->days;
					$r_days = $day + $eve + $night;
					if ($r_days == 0) {
						$r_days = 22;
					}


					?>


					<td><?php echo  $r_days; ?></td>
					<td><?php echo  $present; ?></td>
					<td><?php $ab = days_absent_helper($present, $r_days); if($ab<=0){ echo 0;} else{ echo $ab; 

						};?></td>
					<td data-label="Percentage Pr"><?php
													echo  per_present_helper($present, $r_days);
													?>
					</td>
				</tr>

			<?php

				$no++;
			} ?>



		</tbody>





	</table>

</body>

</html>