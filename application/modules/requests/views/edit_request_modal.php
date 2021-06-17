<style>
  .speech-bubble {
	position: relative;
	background: #919191;
	border-radius: .4em;
}

.speech-bubble:after {
	content: '';
	position: absolute;
	right: 0;
	top: 50%;
	width: 0;
	height: 0;
	border: 1.688em solid transparent;
	border-left-color: #919191;
	border-right: 0;
	border-bottom: 0;
	margin-top: -0.844em;
	margin-right: -1.687em;
}
.speech-bubble2 {
	position: relative;
	background: #5e5e5e;
	border-radius: .4em;
}

.speech-bubble2:after {
	content: '';
	position: absolute;
	right: 0;
	top: 50%;
	width: 0;
	height: 0;
	border: 1.688em solid transparent;
	border-left-color: #5e5e5e;
	border-right: 0;
	border-bottom: 0;
	margin-top: -0.844em;
	margin-right: -1.687em;
}
</style>

<?php
$uri = $_SERVER['REQUEST_URI'];
 $uri; // Outputs: URI
 
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
 
$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
 $linkquery=$url; // Outputs: Full URL
 // Outputs: Query String
?>
<!-- Modal -->
<div id="editModal<?php echo str_replace('|','_',$request->entry_id); ?>" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <div class="modal-content">
      
        <form action="<?php echo base_url(); ?>requests/updateRequest" method="post">
          
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" style="text-align: center;"> Request</h4>
        <center><span></span></center>
      </div>
      <div class="modal-body" style="padding-left:3em;">
     
         <div class="col-md-4">
         <div class="form-group">
             <label>From:</label>
             <input type="text" name="dateFrom" class="form-control datepicker" value="<?php echo 	$dateFrom=date('Y-m-d', strtotime($request->dateFrom)); ?>" readonly />

             <input type="hidden" value="<?php echo $request->entry_id; ?>" name="entry_id" />
             <input type="hidden" value="0" name="clarification" />
             <input type="hidden" value="employee" name="employee" />
             <input type="hidden" name="direct" value="<?php echo $linkquery; ?>" >
         </div>
       </div>

       <div class="col-md-4">
         <div class="form-group">
             <label>To:</label>
             <input type="text" value="<?php echo 	$dateFrom=date('Y-m-d', strtotime($request->dateTo)); ?>" name="dateTo" class="form-control datepicker" readonly />
             
         </div>
       </div>
       <div class="col-md-4">
          <div class="form-group">
             <label>Reason:</label>
              <select name="reason_id" class="form-control" required>
               <option value="<?php echo $request->reason_id; ?>"><?php echo ucwords($request->reason); ?></option>
                <?php  echo $reasons_opt; ?>
              </select>
         </div>
        </div>

        <div class="col-md-12">
        <div class="form-group col-md-6" style="max-height:300px; overflow:auto;">
               <label>Remarks/Clarification Trail</label>
               <p>
               <?php echo $request->remarks; ?>
        </p>
           </div>
           <div class="form-group col-md-6">
               <label>Send Clarification</label>
               <textarea  class="form-control" name="remarks" rows="10" cols='5' ></textarea>
           </div>



           <!-- <div class="form-group col-md-4">
               <label>Attach  File</label>
               <input type="file" name="attachment" >

           </div> -->
        </div>
        <?php if($request->attachment==""){ ?>
              <small><center><span style="color: red;">No Attachments</span></center></small>
          <?php }else{ ?>
          <center> 
              <small><a class="" href="<?php echo base_url(); ?>assets/files/<?php echo $request->attachment; ?>" target="_blank"><i class="fa fa-download"></i>View attached File
              </a></small>
          </center>
         <?php } ?>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-info">Send</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>

        </form>

    </div>

  </div>
</div>


<!-- Clarifiaction -->
<div id="clarify<?php echo $request->entry_id; ?>" class="modal fade" tabindex="-1"  role="dialog">
  <div class="modal-dialog modal-lg">

    <div class="modal-content">
      
        <form action="<?php echo base_url(); ?>requests/updateRequest" method="post">
          
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" style="text-align: center;"> Request</h4>
        <center><span></span></center>
      </div>
      <div class="modal-body" style="padding-left:3em;">
     
         <div class="col-md-4">
         <div class="form-group">
             <label>From:</label>
             <input type="text" name="dateFrom" class="form-control datepicker" value="<?php echo 	$dateFrom=date('Y-m-d', strtotime($request->dateFrom)); ?>" readonly />

             <input type="hidden" value="<?php echo $request->entry_id; ?>" name="entry_id" />
             <input type="hidden" value="1" name="clarification" />
         </div>
       </div>

       <div class="col-md-4">
         <div class="form-group">
             <label>To:</label>
             <input type="text" value="<?php echo 	$dateFrom=date('Y-m-d', strtotime($request->dateTo)); ?>" name="dateTo" class="form-control datepicker" readonly />
             
         </div>
       </div>
       <div class="col-md-4">
          <div class="form-group">
             <label>Reason:</label>
              <select name="reason_id" class="form-control" readonly>
               <option value="<?php echo $request->reason_id; ?>"><?php echo ucwords($request->reason); ?></option>
                <?php  echo $reasons_opt; ?>
              </select>
         </div>
        </div>

        <div class="col-md-12">
           <div class="form-group col-md-6" style="max-height:300px; overflow:auto;">
               <label>Remarks/Clarification Trail</label>
               <p>
               <?php echo $request->remarks; ?>
              </p>
           </div>
           <input type="hidden" value="admin" name="employee" />
           <input type="hidden" name="direct" value="<?php echo $linkquery; ?>" >
           <div class="form-group col-md-6">
               <label> Clarification Request</label>
               <textarea  class="form-control" name="remarks" rows="10" cols='5' ></textarea>
           </div>



           <!-- <div class="form-group col-md-4">
               <label>Attach  File</label>
               <input type="file" name="attachment" >

           </div> -->
        </div>


        <?php if($request->attachment==""){ ?>
              <small><center><span style="color: red;">No Attachments</span></center></small>
          <?php }else{ ?>
          <center> 
              <small><a class="" href="<?php echo base_url(); ?>assets/files/<?php echo $request->attachment; ?>" target="_blank"><i class="fa fa-download"></i>View attached File
              </a></small>
          </center>
         <?php } ?>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-info">Send</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>

        </form>

    </div>

  </div>
</div>



<!-- Modal -->
<div class="modal  fade" id="cancelr<?php echo $request->entry_id; ?>"  role="dialog"  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Cancel Request</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you Sure you Want to Cancel this Request ?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <a href="<?php echo base_url()?>requests/cancelRequest/<?php echo $request->entry_id; ?> " style="" class="btn btn-success btn-danger"><i class="fa fa-times" aria-hidden="true">Cancel</i>
          </a>
      </div>
    </div>
  </div>
</div>

<!-- Accept Modal -->
<div class="modal  fade" id="acceptr<?php echo $request->entry_id; ?>"  role="dialog"  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Cancel Request</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Do you wan't to accept this request ?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <a href="<?php echo base_url(); ?>requests/acceptRequest/<?php echo $request->entry_id; ?>" 
                                       class="print btn btn-success btn-sm btn-outline">Accept Request</a>
      </div>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal  fade" id="rejectr<?php echo $request->entry_id;?>"  role="dialog"  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Cancel Request</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Do you wan't to reject this request ?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <a href="<?php echo base_url(); ?>requests/rejectRequest/<?php echo $request->entry_id; ?>" style="" class="print btn btn-danger btn-sm btn-outline" >Reject Request
        </a>
      </div>
    </div>
  </div>
</div>



