
<div class="spacer" style="height: 30px"></div>

<div class="row1">
	<div class="span1">
		<h1>Confirm application deletion</h1>
	</div>
</div>

<div class="row1">
	<div class="span1 material">
		<p>Do you wish to delete the application?</p>
		<p>
			<small>
			This will delete all related sessions and prevent this application from
			authorizing new users. This action cannot be undone.
			</small>
		</p>
		<p style="text-align: center">
			<a class="button error" href="<?= $confirm ?>">Delete App</a>
			<a class="button"       href="<?= new URL('app'); ?>">Cancel</a>
		</p>
	</div>
</div>