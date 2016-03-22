<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">


		<table class="table table-hover">
			<thead>
				<th>Instance Name</th>
				<th>Domain</th>
				<th>Owner Homepage</th>

				<th></th>
				<th></th>
				<th></th>
			</thead>
			<tbody>

				<?php foreach ($instances as $instance) : ?>
				<tr>
    			<td><?= $instance->getName(); ?></td>
    			<td><a href="/<?=$instance->getDomain()?>"><?= $instance->getDomain(); ?></a></td>

    			<td><?= $instance->getIntroText(); ?></td>
    			<td><a href="<?= instance_url("permissions/edit/instance/{$instance->getId()}"); ?>">Edit Permissions</a></td>
    			<td> <a href="<?= instance_url("instances/edit/{$instance->getId()}"); ?>">Edit</a></td>
    			<td> <a href="<?= instance_url("instances/delete/{$instance->getId()}"); ?>" onclick="return alert('Are you sure you want to delete this instance?');">Delete</a></td>
   			</tr>
			<?php	endforeach; ?>

			</tbody>
		</table>
		<p><a href="<?= instance_url("instances/edit"); ?>">Create New Instance</a></p>
	</div>
</div>
