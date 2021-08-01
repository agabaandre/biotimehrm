
<?php  

$usergroups=Modules::run("auth/getUserGroups"); 

$departments=Modules::run("departments/getDepartments");  

$districts=Modules::run("auth/getDistricts");  

$facilities=Modules::run("auth/getFacilities"); 
$variables=Modules::run("svariables/getAll"); 


//print_r($departments);
?>

  <div class="container-fluid">
    <div class="row">

<div class="panel">
<div class="panel-heading">
    <h5>Add New User</h5>
</div>
<div class="panel-body">

    <form class="user_form" method="post" enctype="multipart/form-data">

              

   <table  style="border: none !important; padding:0px; ">

   
   <thead>
        <tr >
            <th style="width:60%;">Name</th>
            <th style="width:60%;">Username</th>
        </tr>
        <tr>
            <td data-label="Name:">
                <input type="text" name="name"  class="form-control" placeholder="Full Name" style="width: 60%" required/>
            </td>
            <td data-label="Username:">
                <input type="text" required name="username"  class="form-control" placeholder="Username" style="width: 80%" required/>
            </td>
        </tr>
        <tr>
            <th>User Group</th>
            <th>Department</th>
        </tr>
        <tr>
              <td data-label="UserGroup">
                <select name="role" onchange="$('.department').val(changeVal(this))"  class="form-control role select2" style="width: 60%" required>
                    <option value="" disabled selected>USER GROUP</option>
                    <?php  foreach($usergroups as $usergroup): 
                                  ?>
                    <option value="<?php echo $usergroup->group_id; ?>"><?php echo $usergroup->group_name; ?>
                        
                    </option>
                    <?php endforeach; ?>
                </select>
            </td>

            <td data-label="Department">
                <?php   ?>
                <select onchange="$('.department').val(changeVal(this))" name="department_id"  class="form-control select2" style="width: 80%">
                    <option value="" disabled selected>DEPARTMENT</option>
                    <?php  foreach($departments as $department): 
                                  ?>
                    <option value="<?php echo $department->department_id; ?>"><?php echo $department->department; ?></option>
                                <?php endforeach; ?>
                </select>
                <input type="hidden" name="department" class="department">
            </td>
        </tr>
        <tr>
           
            <th>Facility</th>
            <th>District</th>
        </tr>
        <tr>
            <td data-label="Facility">
                <select onchange="$('.facility').val(changeVal(this))" name="facility_id" class="form-control select2" style="width: 60%" >
                    
                    <option value="" disabled selected>FACILITY</option>
                    <?php  foreach($facilities as $facility): 
                                  ?>
                    <option value="<?php echo $facility->facility_id; ?>">
                        <?php echo $facility->facility; ?>
                        
                    </option>
                    <?php endforeach; ?>

                </select>
                <input type="hidden" name="facility" class="facility">

            </td>
            <td data-label="District">
                <?php   ?>
                <select onchange="$('.district').val(changeVal(this));" name="district_id"  class="form-control select2" style="width: 80%">
                    <option value="" disabled selected>DISTRICT</option>
                    <?php  foreach($districts as $district): 
                                  ?>
                    <option value="<?php echo $district->district_id; ?>"><?php echo $district->district; ?></option>
                                <?php endforeach; ?>
                </select>
                <input type="hidden" name="district" class="district">
            </td>

           
        </tr>
    </thead>
  
</table>
<button type="submit" class="btn btn-info btn-outline">Save</button>
<button type="reset" class="btn  btnkey bg-gray-dark color-pale ">Reset All</button>
     
</form>


      

<div>

<form class="form-horizontal" action="" method="post" style="margin-top: 4px !important;">

<div class="form-group col-md-6">
<label>Advanced User Search</label>
<input type="text" name="search_key" class="form-control" placeholder="Username">
</div>
<div class="form-group col-md-2">
<input type="submit" class="btn btn-default" value="Search">
</div>
</form>
<?php echo $links; ?>
<table id="mytab2" class="table table-bordered table-striped mytable">
    <thead>

        <tr>
            <th style="width:2%;">#</th>
            <th>Name</th>
            <th>Username</th>
            <th>User Group</th>
            <th>District</th>
            <th>Department</th>
            <th>Facility</th>
            <th>Actions</th>
        
            
        </tr>
     </thead>
        <?php 

        $no=1;

        foreach($users as $user): ?>
        <tbody>

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

            <a data-toggle="modal" data-target="#reset<?php echo $user->user_id; ?>" href="#">Reset</a>

          </td>
            
        </tr>


<!--small modal to show Image-->
        <div class="modal" id="img<?php echo $user->user_id; ?>">
            <div class="modal-dialog">
                <div class="modal-body">

                    <h1><a href="#" style="color: #FFF;" class="pull-right" data-dismiss="modal">&times;</a></h1>

                   <img class="img img-thumbnail" src="<?php echo base_url()."assets/images/sm/".$user->photo; ?>" alt="No Image"/>
                    
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






<script>

$('.role').change(function(){
    
    
    var role=$(this).val();
    
    if(role=="District-Officer"){
        
        $('.district').show();
        $('.facility').hide();
        
    }
    
    else{
        
        $('.district').hide();
        $('.facility').show();
        
        
    }
    
    
});



  

//get selected item
function changeVal(selTag) {
    var x = selTag.options[selTag.selectedIndex].text;
   return x;
}


$(document).ready(function () {


//Submit new user data

$(".user_form").submit(function(e){

    e.preventDefault();


    $('.status').html('<img style="max-height:50px" src="<?php echo base_url();?>assets/img/loading.gif">');



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

    $('.status').html('<img style="max-height:50px" src="<?php echo base_url();?>assets/img/loading.gif">');

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



$(".reset").submit(function(e){

    e.preventDefault();


    $('.status').html('<img style="max-height:50px" src="<?php echo base_url();?>assets/img/loading.gif">');



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


    $('.status').html('<img style="max-height:50px" src="<?php echo base_url();?>assets/img/loading.gif">');



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


    $('.status').html('<img style="max-height:50px" src="<?php echo base_url();?>assets/img/loading.gif">');



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