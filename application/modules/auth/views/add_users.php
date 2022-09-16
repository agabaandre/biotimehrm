<?php

$usergroups = Modules::run("auth/getUserGroups");

$departments = Modules::run("departments/getAll_departments");

$districts = Modules::run("auth/getDistricts");

$facilities = Modules::run("auth/getFacilities");
$variables = Modules::run("svariables/getSettings");

//print_r($variables);
?>

<div class="row">
  <div class="col-md-12">
    <!-- general form elements disabled -->
    <div class="card card-default">
      <div class="card-header">
        <h3 class="card-title">Add User</h3>
      </div>
      <!-- /.card-header -->
      <div class="card-body">
        <form class="user_form" method="post" enctype="multipart/form-data">
          <div class="row">
            <div class="col-md-12">
              <button type="submit" class="btn btn-info btn-outline">Save</button>
              <button type="reset" class="btn  btnkey bg-gray-dark color-pale ">Reset All</button>
            </div>
            <div class="col-md-12" style="margin:0 auto;">
              <span class="status"></span>
            </div>
            <div class="col-sm-4">
              <!-- text input -->
              <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" autocomplete="off" class="form-control" placeholder="Full Name" required />
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>User Group</label>
                <select name="role" style="width:100%;" class="role form-control select2" required>
                  <option value="" disabled selected>USER GROUP</option>
                  <?php foreach ($usergroups as $usergroup) :
                  ?>
                    <option value="<?php echo $usergroup->group_id; ?>"><?php echo $usergroup->group_name; ?>

                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="col-sm-4">
              <!-- textarea -->
              <div class="form-group">
                <label>Username</label>
                <input type="text" required name="username" autocomplete="off" class="form-control" placeholder="Username" required />
              </div>
            </div>
            <div class="col-sm-4">
              <!-- textarea -->
              <div class="form-group">
                <label>Default Password</label>
                <input type="text" required name="password" value="<?php echo $variables = Modules::run("svariables/getSettings")->default_password; ?> " class="form-control" readonly />
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>Email</label>
                <input type="email" required name="email" class="form-control" placeholder="Email" required />
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>District</label>
                <select onChange="getFacs($(this).val());" name="district_id" class="form-control select2 sdistrict" style="width:100%;">
                  <option value="" disabled selected>DISTRICT</option>
                  <?php foreach ($districts as $district) :
                  ?>
                    <option value="<?php echo $district->district_id; ?>"><?php echo $district->district; ?></option>
                  <?php endforeach; ?>
                </select>

              </div>
            </div>

            <div class="col-sm-4">
              <div class="form-group">
                <label>Facility</label>
                <select id="facility" onChange="getDeps($(this).val());" name="facility_id" class="form-control select2 sfacility" style="width:100%;">

                  <option value="" disabled selected>FACILITY</option>


                </select>


              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>Department</label>
                <select id="department" name="department_id" class="form-control select2 sdepartment" style="width:100%;">
                  <option value="" disabled selected>DEPARTMENT</option>

                </select>

              </div>
            </div>


          </div>
      </div>
      </form>

    </div>
    <!-- /.card-body -->
  </div>



  <div class="col-md-12">
    <!-- general form elements disabled -->
    <div class="card card-default">
      <div class="card-header">
        <h3 class="card-title">User List</h3><br>
        <form class="form-horizontal" action="<?php echo base_url() ?>auth/users" method="post" style="margin-top: 4px !important;">

          <div class="form-group col-md-5">
            <label>Advanced User Search</label>
            <div class="input-group mb-3">
              <input type="text" name="search_key" class="form-control" placeholder="Username or Name">
              <div class="input-group-append">
                <button class="btn btn-default" type="submit">Search</button>
              </div>
            </div>



        </form>

      </div>
      <!-- /.card-header -->
      <div class="card-body">

        <?php echo $links; ?>

        <table id="mytab2" class="table table-striped ">
          <thead>

            <tr>
              <th style="width:2%;">#</th>
              <th>Name</th>
              <th>Username</th>
              <th>User Group</th>
              <th>District</th>
              <th>Facility</th>
              <th>Department</th>
              <th>Actions</th>


            </tr>
          </thead>
          <?php

          $no = 1;

          foreach ($users as $user) : ?>
            <tbody>

              <tr>
                <td><?php echo $no; ?>. </td>
                <td><?php echo $user->name; ?></td>
                <td><?php echo $user->username; ?></td>
                <td><?php echo $user->group_name; ?></td>
                <td><?php echo $user->district; ?></td>
                <td><?php echo $user->facility; ?></td>
                <td><?php echo $user->department; ?></td>
                <td><a data-toggle="modal" data-target="#user<?php echo $user->user_id; ?>" href="#">Edit</a>

                  <?php if ($user->status == 1) { ?>

                    <a data-toggle="modal" data-target="#block<?php echo $user->user_id; ?>" href="#">Block</a>
                  <?php } else { ?>

                    <a data-toggle="modal" data-target="#unblock<?php echo $user->user_id; ?>" href="#">Activate</a>

                  <?php } ?>



                  <a data-toggle="modal" data-target="#reset<?php echo $user->user_id; ?>" href="#">Reset</a>

                </td>

              </tr>


              <!--small modal to show Image-->
              <div class="modal" id="img<?php echo $user->user_id; ?>">
                <div class="modal-dialog">
                  <div class="modal-body">

                    <h1><a href="#" style="color: #FFF;" class="pull-right" data-dismiss="modal">&times;</a></h1>

                    <img class="img img-thumbnail" src="<?php echo base_url() . "assets/images/sm/" . $user->photo; ?>" alt="No Image" />

                  </div>
                </div>
              </div>
              <!--/small modal to show Image-->

              <!---include supporting modal-->

            <?php

            include('user_details_modal.php');
            include('confirm_reset.php');
            include('confirm_block.php');

            if ($user->status == 0) {

              include('confirm_unblock.php');
            }

            $no++;
          endforeach ?>

            </tbody>

        </table>

        <?php echo $links; ?>

      </div>
      <!-- /.card-body -->
    </div>
  </div>



  <script>
    //get selected item
    function changeVal(selTag) {
      var x = selTag.options[selTag.selectedIndex].text;
      return x;
    }


    $(document).ready(function() {


      //Submit new user data

      $(".user_form").submit(function(e) {

        e.preventDefault();

        $('.status').html('<img style="max-height:50px" src="<?php echo base_url(); ?>assets/img/loading.gif">');
        var formData = $(this).serialize();
        // console.log(formData);
        var url = "<?php echo base_url(); ?>auth/addUser";
        $.ajax({
          url: url,
          method: 'post',
          data: formData,
          success: function(result) {
            console.log(result);
            setTimeout(function() {
              $('.status').html(result);
              $.notify(result, 'info');
              $('.status').html('');
              $('.clear').click();
            }, 1000);


          }
        }); //ajax

      }); //form submit


      //Submit user update
      $(".update_user").submit(function(e) {
        e.preventDefault();
        $('.status').html('<img style="max-height:50px" src="<?php echo base_url(); ?>assets/img/loading.gif">');
        var formData = new FormData(this);
        console.log(formData);
        var url = "<?php echo base_url(); ?>auth/updateUser";
        $.ajax({
          url: url,
          method: 'post',
          contentType: false,
          processData: false,
          data: formData,
          success: function(result) {

            console.log(result);

            setTimeout(function() {

              $('.status').html(result);

              $.notify(result, 'info');

              $('.status').html('');

              $('.clear').click();

            }, 3000);


          }
        }); //ajax


      }); //form submit



      $(".reset").submit(function(e) {
        e.preventDefault();
        $('.status').html('<img style="max-height:50px" src="<?php echo base_url(); ?>assets/img/loading.gif">');
        var formData = $(this).serialize();
        console.log(formData);
        var url = "<?php echo base_url(); ?>auth/resetPass";
        $.ajax({
          url: url,
          method: 'post',
          data: formData,
          success: function(result) {
            // console.log(result);
            setTimeout(function() {
              $('.status').html(result);
              $.notify(result, 'info');
              $('.status').html('');

              $('.clear').click();

            }, 3000);


          }
        }); //ajax


      }); //form submit


      //block user

      $(".block").submit(function(e) {

        e.preventDefault();


        $('.status').html('<img style="max-height:50px" src="<?php echo base_url(); ?>assets/img/loading.gif">');



        var formData = $(this).serialize();

        console.log(formData);

        var url = "<?php echo base_url(); ?>auth/blockUser";

        $.ajax({
          url: url,
          method: 'post',
          data: formData,
          success: function(result) {

            console.log(result);

            setTimeout(function() {

              $('.status').html(result);

              $.notify(result, 'info');

              $('.status').html('');

              $('.clear').click();

            }, 3000);


          }
        }); //ajax


      }); //form submit


      //block user

      $(".unblock").submit(function(e) {

        e.preventDefault();


        $('.status').html('<img style="max-height:50px" src="<?php echo base_url(); ?>assets/img/loading.gif">');



        var formData = $(this).serialize();

        console.log(formData);

        var url = "<?php echo base_url(); ?>auth/unblockUser";

        $.ajax({
          url: url,
          method: 'post',
          data: formData,
          success: function(result) {

            console.log(result);

            setTimeout(function() {

              $('.status').html(result);

              $.notify(result, 'info');

              $('.status').html('');

              $('.clear').click();

            }, 3000);


          }
        }); //ajax


      }); //form submit


    }); //doc ready
  </script>