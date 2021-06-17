

<html>
<head>
    <title>Attendance Summary</title>
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
	text-align: "." center;
}
.logo{
margin-top:0em;
margin-left:20%;
margin-right:20%;
margin-bottom:0.5em;
}

.heading{
margin-top:0.4em;
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


tr:nth-child(odd){

    background-color: #e1f4f7;
}


td{
    padding: 5px;
}


</style>
</head>
<body>


<table class="items" style="font-size: 12pt; border-collapse: collapse; " cellpadding="8" width="100%">

           
                    <tr style="border-right: 0; border-left: 0; border-top: 0;">
	<td colspan=2 style="border-right: 0; border-left: 0; border-top: 0;"><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"></td>

	<td colspan=4 style="border-right: 0; border-left: 0; border-top: 0;">
		<h4>
<?php 
if(count($sums)<1)
{

echo "<font color='red'> No Usable Data</font>";


}
else{

?>
	MONTHLY ATTENDANCE TO DUTY SUMMARY

		<?php
		 echo " - ".$sums[0]['facility']."<br>"; 

		echo "              ".date('F, Y',strtotime($dates));

		?>

<?php } ?>

	</h4></td>
</tr>
           
  <!-- b -->

<tr>
	<th>#</th>
	<th>Name</th>
	<th>R</th>
	<th>O</th>
	<th>P</th>
	<th>L</th>


</tr>

<tbody>


<?php 

$no=1;

foreach($sums as $sum) {?>


<tr>
	<td class="cost"><?php echo $no;?></td>
	<td class="cost"><?php echo $sum['person'];?></td>
	<td class="cost"><?php echo $sum['R'];?></td>
	<td class="cost"><?php echo $sum['O'];?></td>
	<td class="cost"><?php echo $sum['P'];?></td>
	<td class="cost"><?php echo $sum['L'];?></td>
	



	

</tr>

<?php

$no++; 

} ?>



</tbody>





</table>

</body>
</html>