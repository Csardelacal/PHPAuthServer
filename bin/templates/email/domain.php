<div class="row">
	<div class="span">
		<div class="row l4">
			<div class="span l3">
			</div>
			<div class="span l1" style="text-align: right">
				<a class="button" href="<?= url('email', 'rule') ?>">Add rule</a>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="span">
		<div class="material unpadded">
			<table>
				<thead>
					<tr>
						<th>Type</th>
						<th>Hostname / IP</th>
						<th>List</th>
						<th></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($records as $record): ?>
					<tr>
						<?php $isIp = $record->type == \mail\spam\domain\implementation\SpamDomainModelReader::TYPE_IP ?>
						<td><?= !$isIp? 'Hostname' : 'IP address' ?></td>
						<td><?= !$isIp? $record->host : inet_ntop(base64_decode($record->host)) ?></td>
						<td><?= $record->list?></td>
						<td><a href="<?= url('email', 'rule', $record->_id) ?>">Edit</a></td>
						<td><a href="<?= url('email', 'dropRule', $record->_id) ?>">Delete</a></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			
			<div class="padded">
				<?= $pages ?>
			</div>
		</div>
	</div>
</div>