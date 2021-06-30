  
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

                   

                     

<section class="col-lg-12">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="card">
              <div class="card-header">

                <h4><?php echo (!empty($title)?$title:null) ?></h4>
              
              <div class="card-tools">
              </div><!-- /.card-header -->
              </div>
              </div>
              <div class="card-body">
              <div class="row">
              <p><?php if(!empty($_SESSION['msg'])) echo $this->session->flashdata('msg'); ?></p>
               
               <form method="post" class="requestForm" action="<?php echo base_url(); ?>requests/saveRequest"  enctype="multipart/form-data" autocomplete="off">
   
                <div class="col-md-12">
       

                  <div class="form-group">
                      <label>From:</label>
                      <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="far fa-calendar-alt"></i>
                        </span>
                
                            <input type="text" class="form-control datepicker" name="dateFrom" value="<?php echo date('Y-m-d'); ?>"  required>
                     </div>
                  </div>

                </div>

                
                

                <div class="col-md-12">
                  <div class="form-group">
                      <label>To:</label>
                      <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="far fa-calendar-alt"></i>
                        </span>
                      
                           <input type="text" class="form-control datepicker" value="<?php echo date('Y-m-d');?>" name="dateTo" class="form-control" required>            
                          </div>
                                        

                  </div>
                </div>
              
                <div class="col-md-12">
                   <div class="form-group">
                      <label>Reason:</label>
                       <select name="reason_id" class="form-control" required>
                        <option value="" disabled selected>Select Out of Station Reason</option>
                           <?php echo $reasons_opt; ?>
                       </select>
                  </div>
                 </div>

                 <div class="col-md-12">
                    <div class="form-group col-md-12">
                        <label>Remarks</label>
                        <textarea name="remarks" rows="5" class="form-control pull-left" required></textarea>
                    </div>

                    <div class="form-group col-md-12">
                        <label>Attach Supporting Files</label>
                        <input type="file" name="files" class="form-control">
                    </div>
                 </div>
               <div class="form-group">
                    <button class="btn bg-gray-dark color-pale pull-right" type="submit" style="margin-top:1.7em;">Submit</button>
                </div>

            </form>
          </div>

          </div><!-- /.card-body -->
          </div>
            <!-- /.card -->
</section>
           



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