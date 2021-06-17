

<html>
<head>
    <title> M.O.H Staff Absence Requests -report</title>
<style>
body {font-family: Arial;
	font-size: 12pt;
	max-width:21cm;
	max-height:29.7cm;
}
p {	margin: 0pt; }
table.items {
	border: 0.1mm solid #000000;
}
td { vertical-align: top; }
.items td {
	border-left: 0.2mm solid #000000;
	border-right: 0.2mm solid #000000;
}
table thead th { background-color: #EEEEEE;
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
	text-align: "." center;
}
.logo{
margin-top:0em;
margin-left:20%;
margin-right:20%;
margin-bottom:0.5em;
}

.heading{
margin-top:0.6em;
margin-left:20%;
margin-right:10%;
margin-bottom:0.1em;
}

.title{
margin-top:0.0em;
margin-left:30%;
margin-right:10%;
margin-bottom:0.1em;
}
</style>
</head>
<body>

         
<?php

$requests=Modules::run('requests/getAll');

?>
<table class="items" style="font-size: 12pt; border-collapse: collapse; " cellpadding="8" width="100%">

 <thead>            
    <tr style="border-right: 0; border-left: 0; border-top: 0; text-decoration-color: blue">
		<td colspan=2 style="border-right: 0; border-left: 0; border-top: 0;"><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"></td>

		<td colspan=10 style="border-right: 0; border-left: 0; border-top: 0; text-align: center;">
			<h2 style="text-decoration-color: blue">

			  STAFF ABSENCE REQUESTS -REPORT

		  </h2>
		
		
		  <center><?php  echo "    ".date('F, Y'); ?></center>
		</td>

	</tr>
	
</thead>

	<tbody>
		<tr>
			<th>#</th>
		    <th>STAFF NAME</th>
		    <th>STAFF .ID</th>
		    <th>DEPARMENT</th>
		    <th>REQUEST DATE</th>
		    <th>REQUEST/REASON</th>
		    <th>RESPONSE</th>
		</tr>

		<?php 

		$i=1;

		foreach($requests as $request) {?>

		<tr>
			<td><?php echo $i ?></td>
			<td><?php echo $request->surname." ".$request->firstname." ".$request->othername; ?></td>
			<td><?php echo str_replace('person|','',$request->ihris_pid); ?></td>
			<td><?php echo $request->department; ?></td>
			<td><?php echo $request->date; ?></td>
			<td><?php echo "Requested  <b>From: </b>"." ".$request->dateFrom."<b> To: </b>"." ".$request->dateTo."<br><br><u>Reason:</u><br>".$request->reason;?></td>
			<td width="">
                              <label class="badge"><?php echo $request->status; ?><hr style="margin:0.5px;"> <?php echo Modules::run('requests/getApprover',$request->approver); ?></label>
            </td>
		</tr>

		<?php

		 $i++; 

		} ?>
		<tr>
			<td></td>
		</tr>
		<tr>
			<td colspan=8 style="border-right: 0; border-left: 0; ">Recieved by: </td>
		</tr>


	</tbody>

</table> 



</body>
</html>