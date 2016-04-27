
<div class="spacer" style="height: 30px"></div>

<div class="row1">
	<div class="span1">
		<h1>Administration</h1>
	</div>
</div>

<div class="row5 material">
	<div class="span4">
		<div><strong>Attributes</strong></div>
		<p>
			Attributes allow you to add custom fields to your user profiles, allowing
			your apps to access this data seamlessly. You can set different names,
			ids, data types, and read and write settings for them.
		</p>
		<p>
			<strong>Note:</strong> Like any other data, this information is read only 
			for the applications that access your server.
		</p>
	</div>
	<div class="span1" style="text-align: right">
		<a class="button" href="<?= new URL('attribute') ?>">Edit</a>
	</div>
</div>

<div class="spacer" style="height: 10px"></div>

<div class="row5 material">
	<div class="span4">
		<div><strong>App logo</strong></div>
		<p>
			The logo above the login and registration boxes. Not to be confused with
			the individual application logos that are shown when authorizing an app.
		</p>
	</div>
	<div class="span1" style="text-align: right">
		<a class="button" href="<?= new URL('admin', 'logo') ?>">Edit</a>
	</div>
</div>