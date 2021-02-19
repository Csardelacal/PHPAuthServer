
<div class="row">
	<div class="span">
		<h1 class="text:grey-300">
			Server hero-logo
		</h1>
	</div>
</div>

<div class="row">
	<div class="span">
		<p style="font-size: .8em">
			The logo above the login and registration boxes. Not to be confused with
			the individual application logos that are shown when authorizing an app.
		</p>


		<div class="spacer small"></div>
		
		<p class="align-right">
			<a class="button outline" href="<?= url('admin', 'logo') ?>">Change server logo...</a>
		</p>
	</div>
</div>


<div class="spacer medium"></div>


<div class="row">
	<div class="span">
		<h1 class="text:grey-300">
			Webhook configuration
		</h1>
	</div>
</div>

<div class="row">
	<div class="span">
		<p style="font-size: .8em">
			This authentication server uses <strong>Cptn. H00k</strong> to provide
			webhooks. If Cptn H00k is not installed and properly configured webhooks
			will not work, applications that rely on this hook may stop working 
			properly.
		</p>

		<div class="spacer small"></div>

		<p class="align-right">
			<a class="button outline" href="<?= url('admin', 'hook') ?>">Hook settings...</a>
		</p>
	</div>
</div>
