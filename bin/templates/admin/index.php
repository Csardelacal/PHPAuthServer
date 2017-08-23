
<div class="topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
	User attributes
</div>

<div class="spacer" style="height: 30px"></div>

<div class="row1 fluid">
	<div class="span1">
		<p style="font-size: .8em">
			Attributes allow you to add custom fields to your user profiles, allowing
			your apps to access this data seamlessly. You can set different names,
			ids, data types, and read and write settings for them.
		</p>
		<p style="font-size: .8em">
			<strong>Note:</strong> Like any other data, this information is read only 
			for the applications that access your server.
		</p>

		<div class="spacer" style="height: 10px"></div>

		<p style="text-align: right">
			<a class="button success"  href="<?= url('attribute') ?>">Edit attributes</a>
		</p>
	</div>
</div>

<div class="spacer" style="height: 30px"></div>


<div class="topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
	Server hero-logo
</div>

<div class="spacer" style="height: 10px"></div>

<div class="row1 fluid">
	<div class="span1">
		<p style="font-size: .8em">
			The logo above the login and registration boxes. Not to be confused with
			the individual application logos that are shown when authorizing an app.
		</p>

		<p style="text-align: right">
			<a class="button success" href="<?= url('admin', 'logo') ?>">Edit hero</a>
		</p>
	</div>
</div>
