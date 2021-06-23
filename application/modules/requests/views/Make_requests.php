  
   <?php  

$reasons=Modules::run("reasons/getAll");
$reasons_opt="";
  foreach($reasons as $reason): 
               
 $reasons_opt.="<option value='".$reason->r_id."'>".$reason->reason."</option>";

 endforeach; 

 //print_r(Modules::run("districts/getDistricts"));
 $district_opt="";
   foreach($districts as $district): 
                
  $district_opt.="<option value='".$district->district_id."'>".$district->district."</option>";

  endforeach; 

 ?>

<!-- Contains page content -->
<div class="dashtwo-order-area" style="padding-top: 10px; min-height: 35em;">
<div class="container-fluid">
   <div class="row">
       <div class="col-lg-12">
           <div class="panel panel-default">
             <div class="panel-heading">
                 <h3 class="panel-title"><?php echo $title; ?><h3>
                   
             </div>
             <div class="panel-body">
               <div>
                   <a class="btn btn-default  btn-sm " style="float:right; margin-right:10px;" href="<?php echo base_url(); ?>requests/viewMySubmittedRequests">My Requests</a>
               </div>
               <p style="padding: 5px; text-align:center; font-weight:bold;">
                   <span class="text-center text-danger">
                     <?php echo $this->session->flashdata('msg'); ?></span>
               </p>
                
               <form method="post" class="requestForm" action="<?php echo base_url(); ?>requests/saveRequest"  enctype="multipart/form-data" autocomplete="off">
   
                <div class="col-md-3">
       

                  <div class="form-group">
                      <label>From:</label>
                         <div class="input-group date">
                                       <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="text" class="form-control" name="dateFrom" value="<?php echo date('m/d/Y'); ?>" id="datepicker" required>
                     </div>
                  </div>

                </div>

                
                

                <div class="col-md-3">
                  <div class="form-group">
                      <label>To:</label>
                      <div class="input-group date" data-provide="datepicker">
                                             <span class="input-group-addon"><i class="fa fa-calendar" ></i></span>
                                             <input type="text" class="form-control" value="<?php echo date('m/d/Y');?>" name="dateTo" class="form-control" required>
                                          
                                           </div>
                                        

                  </div>
                </div>
                <div class="col-md-3">
                   <!-- <div class="form-group">
                      <label>Location(For Workshops/O. Requests):</label>
                       <select name="reason_id" class="form-control" required>
                        <option value="" disabled selected>Select District</option>
                           <?php echo $district_opt; ?>
                       </select>
                  </div> -->
                 </div>
                <div class="col-md-3">
                   <div class="form-group">
                      <label>Reason:</label>
                       <select name="reason_id" class="form-control" required>
                        <option value="" disabled selected>Select Out of Station Reason</option>
                           <?php echo $reasons_opt; ?>
                       </select>
                  </div>
                 </div>

                 <div class="col-md-12">
                    <div class="form-group col-md-8">
                        <label>Remarks</label>
                        <textarea name="remarks" rows="5" class="form-control pull-left" required></textarea>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Attach Supporting Files</label>
                        <input type="file" name="files" class="form-control">
                    </div>
                 </div>
               <div class="form-group">
                    <button class="btn btn-success pull-right" type="submit" style="margin-top:1.7em;">Submit</button>
                </div>

            </form>
           </div>
       </div>
   </div>
 </div>
</div>



<script type="text/javascript">
     

$('.datepick').datepicker({
       format:"yyyy-mm-dd",
       autoclose:true
     });
</script>
<script>


   

// });//doc

/*
 $('.requestForm').on('submit',function(e){

       e.preventDefault();
       var formData=$(this).serialize();
       var formUrl="<?php //echo base_url(); ?>requests/saveRequest";

       $.post({
         url:formUrl,
         data:formData,
         sucess:function(response){

           alert(response);
         }

       });

     });*/



</script>