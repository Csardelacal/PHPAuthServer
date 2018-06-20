
<div class="row">
	<div class="span">
		<div class="heading" data-sticky="top">
			<h1 class="unpadded">Message preview</h1>
		</div>
	</div>
</div>

<div class="row">
	<div class="span">
		<iframe src="data:text/html;base64,<?= base64_encode($msg->body) ?>" style="width: 100%; height: 700px; border: none"></iframe>
	</div>
</div>

<div class="spacer" style="height: 30px"></div>

<div class="row">
	<div class="span">
		<div class="heading" data-sticky="top">
			<h1 class="unpadded">Metadata</h1>
		</div>
	</div>
</div>

<div class="row">
	<div class="span">
		<table>
			<thead>
				<tr>
					<th>Key</th>
					<th>Value</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>To</td>
					<td><?= $msg->to ?></td>
				</tr>
				<tr>
					<td>Scheduled</td>
					<td><?= date('m/d/Y', $msg->scheduled) ?></td>
				</tr>
				<tr>
					<td>Sent</td>
					<td><?= $msg->delivered? date('m/d/Y', $msg->delivered) : 'Not yet sent'?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<div class="spacer" style="height: 30px"></div>
