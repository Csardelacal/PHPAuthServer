
<div class="row">
	<div class="span">
		<div class="heading">
			User attributes
		</div>
	</div>
</div>

<div class="spacer" style="height: 30px"></div>

<div class="row">
	<div class="span">
		<p style="font-size: .8em">
			Attributes allow you to add custom fields to your user profiles, allowing
			your apps to access this data seamlessly. You can set different names,
			ids, data types, and read and write settings for them.
			
			PHPAuthServer provides granular control to the user to allow or prevent
			applications from accessing the data they attach to their profile.
		</p>
		<p style="font-size: .8em">
			<strong>Note:</strong> Applications are not allowed to create any attributes.
			Including system applications.
		</p>

		<div class="spacer" style="height: 10px"></div>

		<p style="text-align: right">
			<a class="highlighted" href="<?= url('attribute') ?>">Edit user attributes...</a>
		</p>
	</div>
</div>

<div class="spacer" style="height: 30px"></div>


<div class="row">
	<div class="span">
		<div class="heading">
			Server hero-logo
		</div>
	</div>
</div>

<div class="spacer" style="height: 10px"></div>

<div class="row">
	<div class="span">
		<p style="font-size: .8em">
			The logo above the login and registration boxes. Not to be confused with
			the individual application logos that are shown when authorizing an app.
		</p>

		<p style="text-align: right">
			<a class="highlighted" href="<?= url('admin', 'logo') ?>">Change server logo...</a>
		</p>
	</div>
</div>


<div class="spacer" style="height: 30px"></div>


<div class="row">
	<div class="span">
		<div class="heading">
			Webhook configuration
		</div>
	</div>
</div>

<div class="spacer" style="height: 10px"></div>

<div class="row">
	<div class="span">
		<p style="font-size: .8em">
			This authentication server uses <strong>Cptn. H00k</strong> to provide
			webhooks. If Cptn H00k is not installed and properly configured webhooks
			will not work, applications that rely on this hook may stop working 
			properly.
		</p>

		<p style="text-align: right">
			<a class="highlighted" href="<?= url('admin', 'hook') ?>">Hook settings...</a>
		</p>
	</div>
</div>
