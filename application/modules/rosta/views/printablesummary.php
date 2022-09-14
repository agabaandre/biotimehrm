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
	</style>
</head>

<body>
	<table class="items" style="font-size: 12pt; border-collapse: collapse; " cellpadding="8" width="100%">
		<thead>
			<tr style="border-right: 0; border-left: 0; border-top: 0;">
				<td colspan=4 style="border-right: 0; border-left: 0; border-top: 0;"><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"></td>
				<td colspan=6 style="border-right: 0; border-left: 0; border-top: 0;">
					<h4>
						<?php
						if (count($sums) < 1) {
							echo "<font color='red'> No Usable Data</font>";
						} else {
						?>
							<p> MONTLY DUTY ROTA SUMMARY
								<?php
								echo " - " . $sums[0]['facility'] . " " . date('F, Y', strtotime($dates));
								?>
							</p>
						<?php } ?>
					</h4>
				</td>
			</tr>
		</thead>
		<tr>
			<th>#</th>
			<th>Name</th>
			<th>Job</th>
			<th>Day</th>
			<th>Evening</th>
			<th>Night</th>
			<th>Off</th>
			<th>Annual</th>
			<th>Study</th>
			<th>Maternity</th>
			<th>Other Leave</th>
			<th>Total Days</th>
		</tr>
		<tbody>
			<?php
			$no = 1;
			foreach ($sums as $sum) { ?>
				<tr>
					<td class="cost"><?php echo $no; ?></td>
					<td data-label="Name"><?php echo $sum['fullname'] . ' ' . $sum['othername']; ?></td>
					<td data-label="Name"><?php echo $sum['job']; ?></td>
					<td data-label="D"><?php $d = $sum['D'];
										if (!empty($d)) {
											echo $d;
										} else {
											echo 0;
										} ?></td>
					<td data-label="E"><?php $e = $sum['E'];
										if (!empty($e)) {
											echo $e;
										} else {
											echo 0;
										} ?></td>
					<td data-label="N"><?php $n = $sum['N'];
										if (!empty($n)) {
											echo $n;
										} else {
											echo 0;
										} ?></td>
					<td data-label="O"><?php $o = $sum['O'];
										if (!empty($o)) {
											echo $o;
										} else {
											echo 0;
										} ?></td>
					<td data-label="A"><?php $a = $sum['A'];
										if (!empty($a)) {
											echo $a;
										} else {
											echo 0;
										} ?></td>
					<td data-label="S"><?php $s = $sum['S'];
										if (!empty($s)) {
											echo $s;
										} else {
											echo 0;
										} ?></td>
					<td data-label="M"><?php $m = $sum['M'];
										if (!empty($m)) {
											echo $m;
										} else {
											echo 0;
										} ?></td>
					<td data-label="Z"><?php $z = $sum['Z'];
										if (!empty($z)) {
											echo $d;
										} else {
											echo 0;
										} ?></td>
					<td data-label="Z"><?php echo $sum['D'] + $sum['E'] + $sum['N'] + $sum['O'] + $sum['A'] + $sum['S'] + $sum['M'] + $sum['Z']; ?></td>
				</tr>
			<?php
				$no++;
			} ?>
		</tbody>
	</table>
</body>

</html>