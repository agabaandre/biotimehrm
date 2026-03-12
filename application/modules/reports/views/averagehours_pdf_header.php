<html>
<head>
	<meta charset="UTF-8">
	<title>Average Monthly Hours</title>
	<style>
		body { font-family: Arial; font-size: 11pt; }
		table.items { border-collapse: collapse; width: 100%; border: 0.1mm solid #000; }
		th, td { border: 0.2mm solid #000; padding: 5px; vertical-align: top; }
		th { background: #EEEEEE; text-align: center; }
		tr:nth-child(odd) { background: #e1f4f7; }
		table.header-tbl { border: none; }
		table.header-tbl td { border: none; padding: 4px 8px; vertical-align: middle; }
	</style>
</head>
<body>
	<table class="header-tbl" style="width: 100%; margin-bottom: 8px;">
		<tr>
			<td style="width: 100px;"><?php if (!empty($moh_logo_path) && is_file($moh_logo_path)) { ?><img src="<?php echo $moh_logo_path; ?>" width="100px"><?php } else { ?><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"><?php } ?></td>
			<td>
				<h4>MONTHLY STAFF AVERAGE WORKING HOURS — <?php echo isset($facility_name) ? htmlspecialchars($facility_name) : ''; ?></h4>
			</td>
		</tr>
	</table>
	<table class="items" style="font-size: 12pt;" cellpadding="8" width="100%">
		<tr>
			<th>#</th>
			<th style="width: 50%;">Month and Year</th>
			<th style="width: 50%;">Average Hours</th>
		</tr>
		<tbody>
