
<div class="spacer" style="height: 50px"></div>

<form class="condensed standalone" method="POST" enctype="multipart/form-data">
	<div class="description">
		Click the button below to upload your avatar. It will be automatically resized once uploaded.

		<div id="imgDisplay" style="text-align:center"></div>
	</div>
	<input type="file" name="upload" id="imgInp" style="width: 100%; padding: 10px;">
	<input type="submit" value="Store" id="imgSubmit">
</form>

<script src="<?= URL::asset('js/avatar.min.js') ?>"></script>
