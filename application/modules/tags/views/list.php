<div class="row">

	<div class="card col-lg-12">
		<div class="card-header text-left">
			<h3 class="card-title float-left"><?php echo $title; ?></h3>
			<a href="#create-modal" data-toggle="modal" class="btn btn-outline-success float-right"><i class="fa fa-plus"></i> Add New Search</a>
		</div>

		<?php include 'includes/create-modal.php'; ?>

		<div class="card-body text-left">
			<table class="table table-striped">
				<thead>
					<tr>
						<th colspan="2">Tag</th>
						<th colspan="2"></th>
					</tr>
				</thead>
				<?php foreach ($tags as $row) : ?>
					<tr>
						<td width="5%"><i class="fa fa-map-pin text-muted"></i></td>
						<td><?php echo $row->tag_text; ?></td>
						<td><a href="#edit<?php echo $row->id; ?>"><i class="fa fa-edit"></i> Edit</td>
						<td><a href="javascript:void(0);" onclick="openDeleteModal(<?php echo $row->id; ?>)" class="text-danger"><i class="fa fa-trash"></i> Delete</td>
					</tr>
				<?php endforeach; ?>
			</table>

			<?php include 'includes/delete-modal.php'; ?>

		</div>
	</div>

</div>