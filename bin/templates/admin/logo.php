
<div class="spacer" style="height: 30px;"></div>

<div class="row1">
	<div class="span1">
		<h1>Current logo</h1>
	</div>
</div>

<div class="row1 material">
	<div class="span1">
		<img src="<?= url('image', 'hero') ?>">
	</div>
</div>

<div class="row1">
	<div class="span1">
		<h1>Upload a new logo</h1>
	</div>
</div>

<div class="row1 material">
	<div class="span1">
		<p>Upload a new logo. Recommended dimensions are 722px x 450px</p>
		
		<form method="POST" enctype="multipart/form-data">
			<input type="file" name="file" id="file">
			<input type="submit" value="Upload">
		</form>
	</div>
</div>
