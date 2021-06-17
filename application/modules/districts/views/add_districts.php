
<div class="container" style="width: 100%">

    <form class="district_form" method="post" action="<?php echo base_url(); ?>districts/save_district">
        <div style="text-align: right;">
            <b>DISTRICTS</b>
        </div>

        <table>

        <tr>
            <!--td colspan="7"><span class="status"></span></td -->
            
            <td colspan="1"><button type="submit" class="btn btn-sm btn-success">Save</button></td>
             <td colspan="1"><button type="reset" class="btn btn-sm btn-warning clear">Reset All</button></td>
        </tr>

        </table>
    <div>

    <table id="myTable" class="table" cellpadding="0" style="border-collapse: collapse;">

   
   <thead>
        <tr>
            <th>District ID</th>
            <th>Distrcit Name</th>
        </tr>
    </thead>
   
    <tbody class="tb">
        <tr>
            <td data-label="Distrcit id:">
                <input type="text" name="district_id"  class="form-control" placeholder=""  required/>
            </td>
            <td data-label="Distrcit Name:">
                <input type="text" required name="district"  class="form-control"  required/>
            </td>

        </tr>
    </tbody>
</table>
</div>
    </form>
</div>

      

<div>

<table class="table">
   
        <tr>
            <th style="width:2%;">#</th>
            <th>District Id</th>
            <th>District Name</th>
            <th>Date Entered</th>
            
            
        </tr>
        <?php 

            $districts=Modules::run("districts/getAll_Districts");
        $no=1;

        foreach($districts as $district): ?>

        <tr>
            <td data-label="#"><?php echo $no; ?>. </td>
            <td data-label="first Name:"><?php echo $district->district_id; ?></td>
            <td data-label="Username:"><?php echo $district->district; ?></td>
            <td data-label="Role:"><?php echo $district->date_added; ?></td>
            
            
            <td>
                <a data-toggle="modal" data-target="#eddit<?php echo $district->d_id; ?>" href="#">Edit</a>
                |
                <a data-toggle="modal" data-target="#delete<?php echo $district->d_id; ?>" href="#">Delete</a>
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

if(window.location.href=="<?php echo base_url(); ?>districts/add_Districts#" || window.location.href=="<?php echo base_url(); ?>districts/add_Districts"){


}




//delete a row from the form
    $("table.order-list").on("click", ".del_btn", function (event) {
        $(this).closest("tr").remove();       
        counter -= 1
    });



//Submit new user data

$(".district_form").submit(function(e){

    e.preventDefault();


    $('.status').html('<img style="max-height:50px" src="<?php echo base_url();?>assets/images/busy.gif">');



    var formData=$(this).serialize();
    //new FormData(this);

    console.log(formData);

    var url="<?php echo base_url(); ?>districts/save_district";

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

$(".update_district").submit(function(e){

    e.preventDefault();


    $('.status').html('<img style="max-height:50px" src="<?php echo base_url();?>assets/images/busy.gif">');



    var formData=new FormData(this);

    console.log(formData);

    var url="<?php echo base_url(); ?>districts/updateDistrict";

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