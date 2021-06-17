   <?php

$user=$this->session->get_userdata();
$userifo=Modules::run('auth/getuserInfo',$user['ihris_pid']);

   ?>

<div class="modal fade" id="profile" tabindex="-1" role="dialog" aria-labelledby="modalLabelSmall" aria-hidden="true">
<div class="modal-dialog modal-sm">
<div class="modal-content">

<div class="modal-header">
<h4 class="modal-title" id="modalLabelSmall">Personal Details</h4>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>

<div class="modal-body">


</div>

</div>
</div>
</div>