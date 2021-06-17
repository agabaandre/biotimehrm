
<?php  

$usergroups=Modules::run("auth/getUserGroups"); 

$departments=Modules::run("departments/getDepartments");  

$districts=Modules::run("auth/getDistricts");  

$facilities=Modules::run("auth/getFacilities"); 
$variables=Modules::run("svariables/getAll"); 


//print_r($departments);
?>
<div class="dashtwo-order-area" style="padding-top: 10px;">
  <div class="container-fluid">
    <div class="row">
          <div class="col-lg-12">
<div class="panel" style="">
<div class="panel-heading">
<p>Manage Users</p>
</div>
<div class="panel-body">

    <form class="user_form" method="post" enctype="multipart/form-data">

        <table>

        <tr>
            <td colspan="7"><span></span></td>
            
            <td colspan="1"><button type="submit" class="btn btn-sm btn-success">Save</button></td>
             <td colspan="1"><button type="reset" class="btn btn-sm btn-warning clear">Reset All</button></td>
        </tr>

        </table>

    <table id="myTable" class="table" cellpadding="0" style="border-collapse: collapse;">

   
   <thead>
        <tr>
            <th>Name</th>
            <th>Username</th>
            <th>User Group</th>
            <th>Department</th>
            <th>District</th>
            <th>Facility</th>
        </tr>
    </thead>
   
    <tbody class="tb">
        <tr>
            <td data-label="Name:">
                <input type="text" name="name"  class="form-control" placeholder="Full Name" style="max-width: 200px" required/>
            </td>
            <td data-label="Username:">
                <input type="text" required name="username"  class="form-control" placeholder="Username" style="max-width: 150px" required/>
            </td>

              <td data-label="UserGroup">
                <select name="role"  class="form-control" style="max-width: 100px" required>
                    <option value="">_select_</option>
                    <option></option>
                    <?php  foreach($usergroups as $usergroup): 
                                  ?>
                    <option value="<?php echo $usergroup->group_id; ?>"><?php echo $usergroup->group_name; ?>
                        
                    </option>
                    <?php endforeach; ?>
                </select>
            </td>

            <td data-label="Department">
                <?php   ?>
                <select onchange="$('.department').val(changeVal(this))" name="department_id"  class="form-control" style="max-width: 150px" required>
                    <option value="" disabled selected>_select_</option>
                    <?php  foreach($departments as $department): 
                                  ?>
                    <option value="<?php echo $department->department_id; ?>"><?php echo $department->department; ?></option>
                                <?php endforeach; ?>
                </select>
                <input type="hidden" name="department" class="department">
            </td>
            <td data-label="District">
                <?php   ?>
                <select onchange="$('.district').val(changeVal(this));" name="district_id"  class="form-control" style="max-width: 100px" required>
                    <option value="">_select_</option>
                    <?php  foreach($districts as $district): 
                                  ?>
                    <option value="<?php echo $district->district_id; ?>"><?php echo $district->district; ?></option>
                                <?php endforeach; ?>
                </select>
                <input type="hidden" name="district" class="district">
            </td>

            <td data-label="Facility">
                <select onchange="$('.facility').val(changeVal(this))" name="facility_id" class="form-control" style="max-width: 100px" required>
                    
                    <option value="">_select_</option>
                    <?php  foreach($facilities as $facility): 
                                  ?>
                    <option value="<?php echo $facility->facility_id; ?>">
                        <?php echo $facility->facility; ?>
                        
                    </option>
                    <?php endforeach; ?>

                </select>
                <input type="hidden" name="facility" class="facility">

            </td>
        </tr>
    </tbody>
</table>
    </form>


      

<div>

<form class="form-horizontal" action="" method="post">

<div class="form-group col-md-6">
<input type="text" name="search_key" class="form-control" placeholder="Username">
</div>
<div class="form-group col-md-2">
<input type="submit" class="btn btn-default" value="Search">
</div>
</form>

<table class="table">
    <thead>

    </thead>
        <tr>
            <th style="width:2%;">#</th>
            <th>Name</th>
            <th>Username</th>
            <th>User Group</th>
            <th>District</th>
            <th>Department</th>
            <th>Facility</th>
            
        </tr>
        <?php 

        //$users=Modules::run("auth/getAll");

        $no=1;

        foreach($users as $user): ?>

        <tr>
            <td data-label="#"><?php echo $no; ?>. </td>
            <td data-label="first Name:"><?php echo $user->name; ?></td>
            <td data-label="Username:"><?php echo $user->username; ?></td>
            <td data-label="Role:" value="<?php echo $user->role; ?>"><?php echo $user->group_name; ?></td>
            <td data-label="district:"><?php echo $user->district; ?></td>
            <td data-label="department:"><?php echo $user->department; ?></td>
            <td data-label="Facility:"><?php echo $user->facility; ?></td>
            
            <td><a data-toggle="modal" data-target="#user<?php echo $user->user_id; ?>" href="#">Edit</a>
                |
            <?php if($user->status==1){ ?>

              <a data-toggle="modal" data-target="#block<?php echo $user->user_id; ?>" href="#">Block</a>
              <?php } else{ ?>
           
            <a data-toggle="modal" data-target="#unblock<?php echo $user->user_id; ?>" href="#">Activate</a>

              <?php } ?>

              |

            <a data-toggle="modal" data-target="#reset<?php echo $user->user_id; ?>" href="#">Reset Pass</a>

          </td>
            
        </tr>


<!--small modal to show Image-->
        <div class="modal" id="img<?php echo $user->user_id; ?>">
            <div class="modal-dialog">
                <div class="modal-body">

                    <h1><a href="#" style="color: #FFF;" class="pull-right" data-dismiss="modal">&times;</a></h1>

                    <center><img class="img img-thumbnail" src="<?php echo base_url()."assets/images/sm/".$user->photo; ?>" alt="No Image"/></center>
                    
                </div>
            </div>
        </div>
<!--/small modal to show Image-->

<!---include supporting modal-->

  <?php 

  include('user_details_modal.php');
  include('confirm_reset.php');
  include('confirm_block.php');

  if($user->status==0){
 
 include('confirm_unblock.php');

  }

    $no++;
    endforeach ?>

    </tbody>
   
</table>

  <?php echo $links; ?>

</div>
</div>

</div>
</div>
</div>

</div>
</div>




<script>

//get selected item
function changeVal(selTag) {
    var x = selTag.options[selTag.selectedIndex].text;
   return x;
}


$(document).ready(function () {

    //collapse menu on this page

if(window.location.href=="<?php echo base_url(); ?>auth/users#" || window.location.href=="<?php echo base_url(); ?>auth/users"){


}






//delete a row from the form
    $("table.order-list").on("click", ".del_btn", function (event) {
        $(this).closest("tr").remove();       
        counter -= 1
    });



//Submit new user data

$(".user_form").submit(function(e){

    e.preventDefault();


    $('.status').html('<img style="max-height:50px" src="<?php echo base_url();?>assets/images/busy.gif">');



    var formData=$(this).serialize();
    //new FormData(this);

    console.log(formData);

    var url="<?php echo base_url(); ?>auth/addUser";

    $.ajax({
        url: url,
        method:'post',
        // contentType:false,
        // processData:false,
        data:formData,
     success: function(result){

        console.log(result);

        setTimeout(function(){

            $('.status').html(result);

            $.notify(result,'info');

            $('.status').html('');

            $('.clear').click();

        },3000);
        
     
    }
    });//ajax


});//form submit



//Submit user update

$(".update_user").submit(function(e){

    e.preventDefault();


    $('.status').html('<img style="max-height:50px" src="<?php echo base_url();?>assets/images/busy.gif">');



    var formData=new FormData(this);

    console.log(formData);

    var url="<?php echo base_url(); ?>auth/updateUser";

    $.ajax({
        url: url,
        method:'post',
        contentType:false,
        processData:false,
        data:formData,
     success: function(result){

        console.log(result);

        setTimeout(function(){

            $('.status').html(result);

            $.notify(result,'info');

            $('.status').html('');

            $('.clear').click();

        },3000);
        
     
    }
    });//ajax


});//form submit




//reset user password

$(".reset").submit(function(e){

    e.preventDefault();


    $('.status').html('<img style="max-height:50px" src="<?php echo base_url();?>assets/images/busy.gif">');



    var formData=$(this).serialize();

    console.log(formData);

    var url="<?php echo base_url(); ?>auth/resetPass";

    $.ajax({
        url: url,
        method:'post',
        data:formData,
     success: function(result){

        console.log(result);

        setTimeout(function(){

            $('.status').html(result);

            $.notify(result,'info');

            $('.status').html('');

            $('.clear').click();

        },3000);
        
     
    }
    });//ajax


});//form submit


//block user

$(".block").submit(function(e){

    e.preventDefault();


    $('.status').html('<img style="max-height:50px" src="<?php echo base_url();?>assets/images/busy.gif">');



    var formData=$(this).serialize();

    console.log(formData);

    var url="<?php echo base_url(); ?>auth/blockUser";

    $.ajax({
        url: url,
        method:'post',
        data:formData,
     success: function(result){

        console.log(result);

        setTimeout(function(){

            $('.status').html(result);

            $.notify(result,'info');

            $('.status').html('');

            $('.clear').click();

        },3000);
        
     
    }
    });//ajax


});//form submit


//block user

$(".unblock").submit(function(e){

    e.preventDefault();


    $('.status').html('<img style="max-height:50px" src="<?php echo base_url();?>assets/images/busy.gif">');



    var formData=$(this).serialize();

    console.log(formData);

    var url="<?php echo base_url(); ?>auth/unblockUser";

    $.ajax({
        url: url,
        method:'post',
        data:formData,
     success: function(result){

        console.log(result);

        setTimeout(function(){

            $('.status').html(result);

            $.notify(result,'info');

            $('.status').html('');

            $('.clear').click();

        },3000);
        
     
    }
    });//ajax


});//form submit



});//doc ready






</script>