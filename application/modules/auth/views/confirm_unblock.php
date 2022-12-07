<div class="modal fade" id="unblock<?php echo $user->user_id; ?>">
<form class="unblock" action="<?php echo base_url(); ?>auth/blockUser" method="post">
	<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-body">

					<h4>Activate user <b><?php echo $user->firstname." ".$user->lastname; ?></b> ?</h4>

					
					<span class="status" style="margin:0 auto;"></span>

						<input type="hidden" name="user_id" value="<?php echo $user->user_id; ?>">
						

		
	</div>

	<div class="modal-footer">

<input type="submit" class="btn btn-success" value="Yes, Activate">

<a href="#" data-dismiss="modal" class="btn">Close</a>
		
	</div>



		
	</div>

	</div>

</form>

</div>