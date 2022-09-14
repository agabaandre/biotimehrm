<html>

<head>
	<title>Rota Report</title>
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
			border: 0.1mm solid #000000;
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
	<table width="100%" class="items" style="font-size: 12pt; border-collapse: collapse; " cellpadding="8">
		<thead>
			<tr style="border-right: 0; border-left: 0; border-top: 0;">
				<td colspan=3 style="border-right: 0; border-left: 0; border-top: 0;"><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"></td>
				<?php
				$allcols = cal_days_in_month(CAL_GREGORIAN, $month, $year); //days in a month
				?>
				<td colspan=<?php echo $allcols; ?> style="border-right: 0; border-left: 0; border-top: 0;">
					<h2>
						MONTHLY ATTENDANCE REPORT <br>
						<?php
						echo $_SESSION['facility_name'] . " ";
						echo "   " . date('F, Y', strtotime(date($dates)));
						?>
					</h2>
				</td>
			</tr>
			<tr>
				<th>#</th>
				<th>Name</th>
				<th>Position</th>
				<?php
				$monthdays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
				// get days in a month
				for ($i = 1; $i < ($monthdays + 1); $i++) {
					$dy = $i;
					if ($i < 10) {
						$dy = "0" . $i;
					}
					$wekday = $year . "-" . $month . "-" . $dy;
					if (isWeekend($wekday) == 'yes') {
						$color = "#7a0404; color:#FFFFFF";
					} else {
						$color = "";
					}
				?>
					<td class="cell" style="padding:0px; text-align: center; border: 1px solid; background-color: <?php echo $color; ?>"><?php echo $i; ?></td>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php
			$no = 0;
			//$nonworkables contains non duty days
			//$workeddays contains  worked days
			foreach ($duties as $singleduty) {
				$no++;
			?>
				<tr>
					<td class='cost'><?php echo $no; ?></td>
					<td class='cost'><?php echo $singleduty['fullname']; ?></td>
					<td class='cost'>
						<?php $words = explode(" ", $singleduty['job']);
						$letters = "";
						foreach ($words as $word) {
							$letters .= $word[0];
						}
						echo $letters;
						?>
					</td>
					<?php
					for ($i = 1; $i < ($monthdays + 1); $i++) {
						$state = "";
						$date_d = $year . "-" . $month . "-" . (($i < 10) ? "0" . $i : $i);
						$pid    = $singleduty['ihris_pid'];
						$entry_id = $year . "-" . $month . "-" . (($i < 10) ? "0" . $i : $i) . $singleduty['ihris_pid'];
						$duty_letter = retrieve_attendance_schedule($pid, $date_d);
						//determine whetehr to update or insert on ajax
						$record_type = (!empty($duty_letter)) ? "update actual" : "actual field";
					?>
						<td class="cost"><?php echo $duty_letter; ?>
						</td>
					<?php } // end for , one that loops tds 
					?>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</body>

</html>