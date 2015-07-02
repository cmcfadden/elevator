<div class="row rowContainer">
	<div class="col-md-9">
		<table class="table table-striped">

				<tbody>

					<?php foreach ($collections as $collection) : ?>
					<tr>
						<td><?= $collection->getTitle(); ?></td>
						<td><a href="<?= instance_url("permissions/edit/collection/{$collection->getId()}"); ?>">Edit Permissions</a></td>
						<td><a href="<?= instance_url("collectionManager/edit/{$collection->getId()}"); ?>">Edit</a></td>
						<td><a href="<?= instance_url("collectionManager/share/{$collection->getId()}"); ?>">Share</a></td>
						<td><a onClick="return confirm('Are you sure you wish to delete this collection?')" href="<?= instance_url("collectionManager/delete/{$collection->getId()}"); ?>">Delete</a></td>
					</tr>
				<?php	endforeach; ?>

			</tbody>
		</table>

		<p><a href="<?= instance_url("collectionManager/edit"); ?>">Create New Collection</a></p>
	</div>
</div>

