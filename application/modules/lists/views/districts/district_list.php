
<div class="container" style="width: 100%">


        <div style="text-align: left;">
            <b>DISTRICTS</b>
        </div>

        <table>
        
        <tr>
            <td colspan="7"><button class="btn btn-sm btn-success"  
            data-toggle="modal" data-target="#add_district">New District</button></td>

             <?php include('add_districts_modal.php');?>
             
        </tr>

        </table>


        <table class="table">
   
            <tr>
                <th style="width:2%;">#</th>
                <th>District</th>
                <th>Region</th>
                <th></th>
            </tr>

            <?php  $no=1;   foreach($districts as $district): ?>

                <tr>
                    <td data-label="#"><?php echo $no; ?>. </td>
                    <td><?php echo $district->name; ?></td>
                    <td><?php echo $district->region; ?></td>
                    <td>
                        <a data-toggle="modal" data-target="#eddit<?php echo $district->id; ?>" href="#">Edit</a>
                        |
                        <a data-toggle="modal" data-target="#delete<?php echo $district->id; ?>" href="#">Delete</a>
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