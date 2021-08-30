   

<?php 

$groups=Modules::run('auth/getUserGroups');
$permissions=Modules::run('auth/getPermissions');
$gpermissions=Modules::run('auth/groupPermissions',$this->session->flashdata('group'));


$this->load->view('auth/add_perm_modal');
//include('add_perm_modal.php');

//print_r($groups);

//print_r($permissions);


?>

<style type="">
   .modal{
    clear:both;
    position: fixed;
    margin-left:100px;
    margin-top:120px;
    margin-bottom: :0;
    z-index: 10040;
    overflow-x: auto;
    overflow-y: auto;
}
</style>

<div class="dashtwo-order-area " style="padding-top: 10px; min-height: 35em">
  <div class="container-fluid">
      <div class="row">
<div class="row col-lg-12">
          <div class="col-md-6 pull-left" >
              <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Select Group <h3>
                      
                </div>
                <?php //print_r($gpermissions); ?>
                <div class="panel-body">

                  <a href="#newgrp" class="btn btn-info btn-sm" data-toggle="modal" >Create a group</a>

                  <a href="#permsModal" class="btn btn-info btn-sm pull-right" data-toggle="modal" >Add Permission</a>

                  <?php  echo $this->session->flashdata("msg"); ?><hr>


                  <form id="group_form" method="post" action="<?php echo base_url(); ?>auth/assignPermissions">
                    
                  <div class="form-group">
                  <?php   $selgroup=$this->session->flashdata('group');?>
                    <div class="input-group">

                  <select  class="form-control" name="group" style="min-width:300px; text-transform:capitalize;"  onchange="this.form.submit()">
                  <?php


                  foreach($groups as $group){  ?>

                  <option value="<?php echo $group->group_id; ?>" <?php if($group->group_id==$selgroup){ echo "selected";} ?>><?php echo $group->group_name; ?></option>

                  <?php } ?>
                  </select>
                  </div>

                  </div>

                  <table border="0" class="table">

                    <?php foreach ($permissions as $perm): ?>
                    
                      <tr>
                      <td><?php echo $perm->definition; ?></td><td><input style="display: block; "  name="permissions[]" value="<?php echo $perm->id; ?>" type="checkbox" <?php if (in_array($perm->id,$gpermissions)) echo "checked"; ?>></td>
                      
                      </tr>

                    <?php endforeach; ?>

                       
                  </table>



                  <div class="form-group">

                    <div class="input-group">

                    <input type="submit" class="btn btn-info" value="Save Group">
                      
                    </div>

                  </div>


                  </form>

              </div>
          </div>
      </div>

          <div class="col-md-6 pull-right">
              <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $title; ?> <h3>
                      
                </div>
                <div class="panel-body">

                  <table class="table">

                    <?php 

                    foreach($groups as $group){  
                    ?>

                    <tr><td><?php echo ucwords($group->group_name); ?></td>
                    <td>
                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#myModal<?php echo $group->group_id; ?>">Permissions</button>
                    </td>
                    </tr>

                    <!-- Modal -->
                    <div id="myModal<?php echo $group->group_id; ?>" class="modal fade" role="dialog">
                      <div class="modal-dialog modal-sm">

                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Permissions for <?php echo ucwords($group->group_name); ?></h4>
                          </div>
                          <div class="modal-body" style="padding-left:3em;">
                           <?php  

                          $group_perms=Modules::run('auth/getGroupPerms',$group->group_id);

                            //print_r($group_perms);
                           
                           foreach($group_perms as $perm){
                               echo "<li>".ucwords($perm->name)."</li>";

                             }

                             if(count($group_perms)<1){

                                echo "<h3 class='text-danger text-center'> No permissions assigned</h3>";
                             }

                           ?>
                        
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                          </div>
                        </div>

                      </div>
                    </div>


                    <?php } ?>

                    </table>
              </div>
          </div>
      </div>
    </div>
</div>
</div>
</div>


<!-- new grp Modal -->
<div id="newgrp" class="modal fade" role="dialog">
  <div class="modal-dialog">
<form method="post" action="<?php echo base_url('admin/addGroup'); ?>">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Add group</h4>
      </div>
      <div class="modal-body" style="padding-left:3em;">
    
    <div class="form-group">
        <input type="text" placeholder="Group Name" name="group_name" class="form-control">
    </div>
      
      </div>
      <div class="modal-footer">
          <button type="submit" class="btn btn-default" >Save Group</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
      </form>
    </div>
  </div>


</div>




<script type="text/javascript">

  $(document).ready(function(){

$('#scheduletbl').slimscroll({
  height: '400px',
  size: '5px'
});


$('.timepicker').timepicker({showInputs:false});




$('#group_form').submit(function(e){

  e.preventDefault();

  var data=$(this).serialize();
  var url='<?php echo base_url(); ?>admin/groupAllow'
  
  
  /* var allperms = [];

     $('input[type="checkbox"]:checked').each(function () {
     
       allperms.push($(this).val());
     });
     
   
     
     var group=  $('input[name="group"]').val();
    
    
      alert(allperms+group);
      */
      

  $.ajax({url:url,
method:"post",
data:data,
success:function(res){

  if(res=="OK"){
      
     $('.suc').html("<center><font color='green'>Group Permissions configured</font></center>");
  }
  
  else{
      
      $('.suc').html("<center><font color='red'>Error Occured, Failed</font></center>");
  }



}//success

}); // ajax



});//form submit





$('.user_edit').submit(function(e){

  e.preventDefault();

 var fac=$('#facility').val();

 if(fac==''){

$('.suc2').html("<center><font color='red'> Select New Facility</font></center>");

 }

 else

 {

  var data=$(this).serialize();
  var url='<?php echo base_url(); ?>admin/edit_user'

  $.ajax({url:url,
method:"post",
data:data,
dataType:'json',
success:function(res){

  console.log(res.status);

  if(res.status==="success")
  {

  $('.suc2').html("<center><font color='green'>User Details Updated</font></center>");

  $('#reset2').click();

}
else  if(res.status==="fail"){

$('.suc2').html("<center><font color='red'> Update Failed</font></center>");

}

}//success

}); // ajax


}//check


});//form submit


$('.block').click(function(e){

  e.preventDefault();

  var id=$(this).attr('id');
  var url='<?php echo base_url(); ?>admin/deactivate_user/'+id;

  console.log(id);
console.log(url);

  $.ajax({url:url,
success:function(res){

  console.log(res);

  $(".suc").html("<font color='green'>"+res+"</font>");


 setTimeout(function(){

    window.location.reload();


  },3000);


}//success

}); // ajax



});//btn click




//activate

$('.activate').click(function(e){

  e.preventDefault();

  var id=$(this).attr('id');
  var url='<?php echo base_url(); ?>admin/activate_user/'+id;

  console.log(id);
console.log(url);

  $.ajax({url:url,
success:function(res){

  console.log(res);

  $(".suc").html("<font color='green'>"+res+"</font>");

  setTimeout(function(){

    window.location.reload();


  },3000);



}//success

}); // ajax



});//btn click





  });//doc
  



</script>
