

<?php 
//  $timelogs=Modules::run('employees/getTimeLogs');
?>



<section class="col-lg-12">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
               
                <div class="card-tools">
                
                  <form class="form-horizontal" action="<?php echo base_url() ?>employees/viewTimeLogs" method="post">
                
                  <div class="row">
                    <div class="form-group col-md-3">
                    <label>Date From:</label>

                    <div class="input-group">
                        <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="far fa-calendar-alt"></i>
                        </span>
                        </div>
                        <input type="text"  name="date_from" class="form-control datepicker" value="<?php echo date("Y-m-d",strtotime("-1 month")); ?>" autocomplete="off">
                    </div>
                    <!-- /.input group -->
                    </div>
                    <div class="form-group col-md-3">
                    <label>Date To:</label>

                    <div class="input-group">
                        <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="far fa-calendar-alt"></i>
                        </span>
                        </div>
                        <input type="text"  name="date_to" class="form-control datepicker "  value="<?php echo date('Y-m-d'); ?>" autocomplete="off">
                    </div>
                    <!-- /.input group -->
                    </div>
                            
                  <div class="form-group col-md-3">
                       
                       <label for="aw_description">
                         Name </label>
                        
                    <input class="form-control" type="text" name="name" placeholder="Name">
                     
                    </div>
                    <div class="form-group col-md-3">
                       
                       <label for="aw_description">
                         Position </label>
                        
                         <select name="job" class="form-control select2">
                         <option value="" >ALL</option>
                         <?php $jobs=Modules::run("jobs/getJobs");
                         foreach ($jobs as $element) {
                             
                         ?>
                       
                        <option value="<?php echo $element->job;?>" <?php if($this->input->post('job')==$element->job){ echo "selected"; } ?> ><?php echo $element->job;?></option>
                        <?php } ?>
                       </select>  
                     
                    </div>
                        
    
                <div class="form-group col-md-2">
            
                <button type="submit" class="btn bt-sm bg-gray-dark color-pale"  style="width:100px;"><i class="fa fa-tasks" aria-hidden="true"></i>Apply</button>
               
              </div>
       
            
              </form>

               
                        
              </div><!-- /.card-header -->
              <?php if($this->input->post('date_from')) { ?>
              <a href="<?php echo base_url()?>employees/attCsv/<?php echo $this->input->post('date_from').'/'.$this->input->post('date_to').'/'.'person'.$this->input->post('name').'/'.'position-'.urlencode(str_replace(' ','_',$this->input->post('job'))); ?>" target="" class="btn bt-sm bg-gray-dark color-pale"  style="width:100px; margin-right:2px;" ><i class="fa fa-file-excel"></i>CSV</a>
              <a href="<?php echo base_url()?>employees/print_timelogs/<?php echo $this->input->post('date_from').'/'.$this->input->post('date_to').'/'.'person'.$this->input->post('name').'/'.'position-'.urlencode(str_replace(' ','_',$this->input->post('job'))); ?>" target="" class="btn bt-sm bg-gray-dark color-pale"  style="width:100px;" traget="_blank" ><i class="fa fa-file-pdf"></i>PDF</a>

              <?php } ?>
              <p class="pagination"><?php echo $links;
              
              ?></p>
              </div>
              </div>
              <div class="card-body">
              <div class="panel-title" style="font-weight:bold; font-size:16px; text-align:center;">	ATTENDANCE LOG FOR

                        <?php
                        echo " - ".$_SESSION['facility_name']." BEWTWEEN "; 

                        if(!empty($this->input->post('date_from'))){
                          echo $_SESSION['date_from'] ." AND ";
                        }
                        else{
                    
                        }
                        if(!empty($this->input->post('date_from'))){
                          echo $_SESSION['date_to'] ;
                        }
                        


                        ?>
              </div>

                          <table class="table table-striped thistbl" id="timelogs">
                            
                            <thead>
                              <tr>
                                  <th>#</th>
                                  <th>NAME</th>
                                  <th>POSITION</th>
                                
                                  <th style="width:20%;">DATE</th>
                                  <th>TIME IN</th>
                                  <th>TIME OUT</th>
                                  <th width="10%;">HOURS WORKED</th>
                                
                              </tr>
                            </thead>

                            <tbody>

                              <?php 

                            $no=(!empty($this->uri->segment(3)))?$this->uri->segment(3):0;

                              foreach($timelogs as $timelog) {
                                $no++;
                               ?>
                              <tr>
                                <td><?php echo $no; ?></td>
                                <td><?php echo $timelog->surname." ".$timelog->firstname; ?></td>
                                <td><?php echo $timelog->job; ?></td>
                               
                          
                                <td><?php echo date('j F,Y', strtotime($timelog->date)); ?></td>
                                <td><?php echo date('H:i:s', strtotime($time_in=$timelog->time_in)); ?></td>
`                               <td><?php  if (!empty($time_out=$timelog->time_out)) { echo date('H:i:s', strtotime($time_out=$timelog->time_out));} ?></td>
                                <td><?php 
                                 
                                  
                                    $initial_time = strtotime($time_in)/ 3600;
                                    $final_time = strtotime($time_out)/ 3600;
              
                                  if(($initial_time)==0 || ($final_time)==0){ 
                                    $hours_worked=0; 
                                  } 
                                  elseif($initial_time==$final_time){ 
                                    $hours_worked=0; 
                                  } 
                                  else{
                                   
                                    $hours_worked = round(($final_time - $initial_time), 1);   
                                    
                                  }
                
                                  if ($hours_worked<0){ 
                                    echo $hours_worked=($hours_worked*-1) .'hr(s)'; 
                                  } 
                                  else { 
                                    echo $hours_worked=$hours_worked.'hr(s)'; 
                                  } 
                                  

                                
                                
                                ?>
                                
                                
                                 </td>
                              
                               </tr>
                                <?php  } ?>
                            </tbody>
                          </table>

                          </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
    </section>
<script type="text/javascript">

  $(document).ready(function(){

$('#thistbl').slimscroll({
  height: '400px',
  size: '5px'
});

});




</script>