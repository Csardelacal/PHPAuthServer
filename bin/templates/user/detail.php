
<div class="spacer" style="height: 40px"></div>

<div class="row1">
	<div class="span1">
		<h1><?= __($profile->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch()->name) ?></h1>
	</div>
</div>

<div class="row4">
	<div class="span3 material">
		<table>
			<tr>
				<td>
					Registered since
				</td>
				<td>
					<?= date('m/d/Y', $profile->created) ?>
				</td>
			</tr>
		</table>
	</div>
	<div class="span1 help-panel">
		<p>
			This data is only the data needed for the user to log into applications
			that use this server as authentication method, and data that all these
			share. Additional data can be found on the apps relying on this.
		</p>
	</div>
</div>