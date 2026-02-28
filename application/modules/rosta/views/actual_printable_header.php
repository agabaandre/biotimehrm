<html>
<head>
	<title>Rota Report</title>
	<style>
		body { font-family: Arial; font-size: 12pt; max-width: 21cm; max-height: 29.7cm; }
		p { margin: 0pt; }
		table.items { border: 0.1mm solid #000000; }
		td { vertical-align: top; }
		.items td { border-left: 0.2mm solid #000000; border-right: 0.2mm solid #000000; }
		table thead th { background-color: #EEEEEE; text-align: center; border: 0.1mm solid #000000; }
		.items tr td { border: 0.2mm solid #000000; }
		.items td.blanktotal { background-color: #EEEEEE; border: 0.1mm solid #000000; background-color: #FFFFFF; border: 0mm none #000000; border-top: 0.1mm solid #000000; border-right: 0.1mm solid #000000; }
		.items td.totals { text-align: right; border: 0.1mm solid #000000; }
		.items td.cost { text-align: "."center; }
		.logo { margin-top: 0em; margin-left: 20%; margin-right: 20%; margin-bottom: 0.5em; }
		.heading { margin-top: 0.4em; margin-left: 20%; margin-right: 10%; margin-bottom: 0.1em; }
		.title { margin-top: 0.0em; margin-left: 30%; margin-right: 10%; margin-bottom: 0.1em; }
		tr:nth-child(odd) { background-color: #e1f4f7; }
		td { padding: 5px; }
	</style>
</head>
<body>
	<?php
	if (!function_exists('_actual_print_is_weekend')) {
		function _actual_print_is_weekend($date) {
			$day = (int) date('N', strtotime($date));
			return $day >= 6 ? 'yes' : 'no';
		}
	}
	$monthdays = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
	$dates = isset($dates) ? $dates : $year . '-' . $month;
	?>
	<table width="100%" class="items" style="font-size: 12pt; border-collapse: collapse; " cellpadding="8">
		<thead>
			<tr style="border-right: 0; border-left: 0; border-top: 0;">
				<td colspan=3 style="border-right: 0; border-left: 0; border-top: 0;"><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"></td>
				<td colspan=<?php echo $monthdays; ?> style="border-right: 0; border-left: 0; border-top: 0;">
					<h2>MONTHLY ATTENDANCE REPORT <br><?php echo $_SESSION['facility_name'] . ' ' . date('F, Y', strtotime($dates . '-01')); ?></h2>
				</td>
			</tr>
			<tr>
				<th>#</th>
				<th>Name</th>
				<th>Position</th>
				<?php for ($i = 1; $i < $monthdays + 1; $i++) {
					$dy = $i < 10 ? '0' . $i : $i;
					$wekday = $year . '-' . $month . '-' . $dy;
					$color = (_actual_print_is_weekend($wekday) == 'yes') ? '#7a0404; color:#FFFFFF' : '';
				?>
				<td class="cell" style="padding:0px; text-align: center; border: 1px solid; background-color: <?php echo $color; ?>"><?php echo $i; ?></td>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
