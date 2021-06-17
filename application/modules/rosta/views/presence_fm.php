
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
		    <form class="form-horizontal" style="padding-bottom: 2em;" action="<?php echo base_url(); ?>rosta/tracker" method="post">
				<div class="col-md-4">

				<div class="control-group">

					<input type="hidden" id="month" value="<?php echo $month; ?>">

				<select class="form-control" name="month" onchange="this.form.submit()" >

					<option value="<?php echo $month; ?>"><?php echo strtoupper(date('F', mktime(0, 0, 0, $month, 10)))."(Showing below)"; ?></option>
					
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
<option><?php echo date('Y')-1; ?></option>
<option><?php echo date('Y')+1; ?></option>

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
if(count($duties)>0)
{



?>
		


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

<table class="tabular" width="100%" cellpadding="0" width="100%">

               
                    <tr>
	<td colspan=4 style="border-right: 0; border-left: 0; border-top: 0;"><img src="<?php echo base_url(); ?>assets/images/MOH.png" width="100px"></td>

	<td colspan=30 style="border-right: 0; border-left: 0; border-top: 0;">
		<h4>
<?php 
if(count($duties)<1)
{

echo "<font color='red'> No Schedule Data</font>";
}
else{

?>
	MONTHLY DAILY ATTENDANCE FORM FOR HEALTH PERSONNEL

		<?php
		 echo " - ".$duties[0]['facility']."<br>"; 

		echo "              ".date('F, Y',strtotime($year."-".$month));


		//print_r($nonworkables);

		?>

<?php } ?>

	</h4></td>
</tr>
           



<tr  style="border-right: 0; border-left: 0; border-top: 0;">
	<th width="3%" style="border-bottom: 1px solid; border-left: 1px solid; border-top: 1px solid; text-align: center;">#</th>
	<th width="20%" style="border-bottom: 1px solid; border-left: 1px solid; border-top: 1px solid; text-align: center;" >Name</th>
	<th style="border-bottom: 1px solid; border-left: 1px solid; border-top: 1px solid; text-align: center;">Position</th>
	

	<?php 

	$month=date('m');

	$year=date('Y');

	$monthdays = cal_days_in_month(CAL_GREGORIAN, $month, $year); // get days in a month

	for($i=1;$i<($monthdays+1);$i++)
	{
		?>

	<th style="padding:0px; text-align: center; border: 1px solid;"><?php echo $i; ?></th>
	
	<?php } ?>

</tr>

<tbody>



	
<?php 

//color the duty date according to whether one worked

	function dayState($day) {

		//if they did not work in past days

		if(strtotime($day) <= strtotime(date('Y-m-d'))){
																		
					$state="";


				}

				//if they are scheduled to work

		if(strtotime($day) > strtotime(date('Y-m-d'))){
																		
					$state="disabled";
				}


				return $state;

			}//color
			



$no=0;

//$nonworkables contains non duty days
//$workeddays contains  worked days

foreach($duties as $singleduty) { 

	$no++;

	?>

<tr style=" border-left: 1px solid;">
	<td style="border-bottom: 1px solid; border-left: 1px solid; border-top: 1px solid; text-align: center;"><?php echo $no;?></td>
	<td style="border-bottom: 1px solid; border-left: 1px solid; border-top: 1px solid; text-align: center;"><?php echo $singleduty['fullname'];?></td>
	<td style="border-bottom: 1px solid; border-left: 1px solid; border-right: 1px solid; border-top: 1px solid; text-align: center;">
	
	<?php $words=explode(" ",$singleduty['job']);

	$letters="";

	foreach ($words as $word) {

		$letters.=$word[0];
	}

	echo $letters;

	?></td>

<?php 

$month_days=date('t');//days in a month

for($i=1;$i<=$month_days;$i++){// repeating td

$day="day".$i;  //changing day

$was_present="P"; //employee worked


?>

	<td style="border-bottom: 1px solid; border-left: 0px solid; border-top: 1px solid; border-right: 1px solid; text-align: center; ">

		<?php if($singleduty[$day]!='')
	{

		$d=$i;

if($d<10){

	$d="0".$d;
}
		

		$dayentry=$singleduty[$day].$singleduty['ihris_pid'];  //gets duty letter for day

		$ddate=date("Y-m")."-".$d;


		
			if(in_array($dayentry,$workeddays) ){

				$state="disabled"

				?>

		<input type="text" style="padding:0px; text-align:center; border:0px; text-transform:uppercase;" class="update tracker" did="<?php echo $ddate; ?>"  day="<?php echo $i; ?>" maxlength="1" size="1px" title="P only for present" value="<?php echo $was_present; ?>" <?php echo dayState($singleduty[$day]); ?> <?php echo $state; ?> >

	<?php } ?>

	<?php 

		if( (!in_array($dayentry,$workeddays)) ){

			//is scheduled to work their duty day...coloring the letter

			?>

<input type="text" style="padding:0px; text-align: center; border:0px; text-transform:uppercase; " class="update tracker" did="<?php echo $ddate; ?>"  day="<?php echo $i; ?>" pid="<?php echo $singleduty['ihris_pid']; ?>"

pattern="[A-Za-z]+" size="1px" maxlength="1" title="P only for present" value="" <?php echo dayState($singleduty[$day]); ?> >

 
	

	<?php }


	

	}
	
		
	else{
	    // not scheduled
	    
	    echo "N/A";
	    
	}


	?></td>


	<?php }//repeat days ?>

	

</tr>

<?php } ?>



</tbody>





</table>


</div></div>




</div><!--col 12-->

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
	

var url=window.location.href;

if(url=='<?php echo base_url(); ?>rosta/tracker'){


	$('.sidebar-mini').addClass('sidebar-collapse');
}



$('.tracker').keyup(function(event){


	if (event.keyCode == 13) {
        textboxes = $("input.tracker");
        currentBoxNumber = textboxes.index(this);
        if (textboxes[currentBoxNumber + 1] != null) {
            nextBox = textboxes[currentBoxNumber + 1];
            nextBox.focus();
            nextBox.select();
           
        }

 event.preventDefault();

            return false;
  
       
    } //if enter key is pressed



    else{ //if not enter key


 var hpid=$(this).attr('pid');
 var date=$(this).attr('did');

var letter=$(this).val(); //input letter

if(letter!==""){
//check if letter is a valid schedule

letter=letter.replace(/\s/g, '');//remove spaces

letter=letter.toUpperCase();//converte to upper case

if(letter!=="P"){  // if letter is not defined as presence tracker

	

	$.notify("Warning: Only P is allowed ", "warn");

	$(this).val('');
}//letter!==p


else{

	console.log(hpid+date);

            $.post('<?php echo base_url(); ?>rosta/saveTracker', {
                hpid: hpid,
                date: date},
                 function(result){
              

                 console.log(result);

                 $(this).val(letter);



$.notify("Tracker Saved", "success");



}
); //$.post

}//else for letter!==P

}//letter !=""


    


}//end if not enter key



        })





</script>