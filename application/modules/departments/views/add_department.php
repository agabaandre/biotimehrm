
<div class="container" style="width: 100%">

    <form class="department_form" method="post" action="<?php echo base_url(); ?>departments/save_department">
        <div style="text-align: right;">
            <b>DEPARTMENTS</b>
        </div>
        <table>

        <tr style="text-align: right;">
            <!--td colspan="7"><span class="status"></span></td -->
            
            <td colspan="1"><button type="submit" class="btn btn-sm btn-success">Save</button></td>
             <td colspan="1"><button type="reset" class="btn btn-sm btn-warning clear">Reset All</button></td>
        </tr>

        </table>

    <table id="myTable" class="table" cellpadding="0" style="border-collapse: collapse;">

   
   <thead>
        <tr>
            <th>Department ID</th>
            <th>Department Name</th>
        </tr>
    </thead>
   
    <tbody class="tb">
        <tr>
            <td data-label="Department id:">
                <input type="text" name="department_id"  class="form-control" placeholder=""  required/>
            </td>
            <td data-label="Department Name:">
                <input type="text" required name="department"  class="form-control"  required/>
            </td>

        </tr>
    </tbody>
</table>
    </form>
</div>

      

<div>

<table class="table">
   
        <tr>
            <th style="width:2%;">#</th>
            <th>Department Id</th>
            <th>Department Name</th>
            <th>Date Entered</th>
            
            
        </tr>
        <?php 

            $departments=Modules::run("departments/getAll_departments");
        $no=1;

        foreach($departments as $department): ?>

        <tr>
            <td data-label="#"><?php echo $no; ?>. </td>
            <td data-label="first Name:"><?php echo $department->department_id; ?></td>
            <td data-label="Username:"><?php echo $department->department; ?></td>
            <td data-label="Role:"><?php echo $department->date_added; ?></td>
            
            
            <td>
                <a data-toggle="modal" data-target="#eddit<?php echo $department->dprt_id; ?>" href="#">Edit</a>
                |
                <a data-toggle="modal" data-target="#delete<?php echo $department->dprt_id; ?>" href="#">Delete</a>
            </td>
            
        </tr>


  <?php 

  include('eddit_modal.php');
  include('delete_modal.php');

    $no++;
    endforeach ?>

   
</table>

</div>




<!--
<script>

$(document).ready(function () {

    //collapse menu on this page

if(window.location.href=="<?php echo base_url(); ?>departments/add_departments#" || window.location.href=="<?php echo base_url(); ?>departments/add_departments"){


}




//delete a row from the form
    $("table.order-list").on("click", ".del_btn", function (event) {
        $(this).closest("tr").remove();       
        counter -= 1
    });



//Submit new user data

$(".department_form").submit(function(e){

    e.preventDefault();


    $('.status').html('<img style="max-height:50px" src="<?php echo base_url();?>assets/images/busy.gif">');



    var formData=$(this).serialize();
    //new FormData(this);

    console.log(formData);

    var url="<?php echo base_url(); ?>departments/save_department";

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

$(".update_department").submit(function(e){

    e.preventDefault();


    $('.status').html('<img style="max-height:50px" src="<?php echo base_url();?>assets/images/busy.gif">');



    var formData=new FormData(this);

    console.log(formData);

    var url="<?php echo base_url(); ?>departments/updatedepartment";

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





});//form submit 




//block user







</script> -->