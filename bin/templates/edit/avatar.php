
<div class="topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
	Upload a new profile picture
</div>

<div class="spacer" style="height: 25px"></div>

<p style="font-size: .8em; color: #555">
	Click the button below to upload your avatar. It will be automatically resized once uploaded.
</p>

<div class="spacer" style="height: 25px"></div>

<form class="regular" method="POST" enctype="multipart/form-data">
	
	<div id="imgDisplay" style="text-align:center; cursor: pointer;">
		<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48cGF0aCBmaWxsPSIjMDdjIiBkPSJNMTIgNWMzLjQ1MyAwIDUuODkxIDIuNzk3IDUuNTY3IDYuNzggMS43NDUtLjA0NiA0LjQzMy43NTEgNC40MzMgMy43MiAwIDEuOTMtMS41NyAzLjUtMy41IDMuNWgtMTNjLTEuOTMgMC0zLjUtMS41Ny0zLjUtMy41IDAtMi43OTcgMi40NzktMy44MzMgNC40MzMtMy43Mi0uMTY3LTQuMjE4IDIuMjA4LTYuNzggNS41NjctNi43OHptMC0yYy00LjAwNiAwLTcuMjY3IDMuMTQxLTcuNDc5IDcuMDkyLTIuNTcuNDYzLTQuNTIxIDIuNzA2LTQuNTIxIDUuNDA4IDAgMy4wMzcgMi40NjMgNS41IDUuNSA1LjVoMTNjMy4wMzcgMCA1LjUtMi40NjMgNS41LTUuNSAwLTIuNzAyLTEuOTUxLTQuOTQ1LTQuNTIxLTUuNDA4LS4yMTItMy45NTEtMy40NzMtNy4wOTItNy40NzktNy4wOTJ6bTQgMTBoLTN2NGgtMnYtNGgtM2w0LTQgNCA0eiIvPjwvc3ZnPgo=" style="height: 128px">
	</div>
	<input type="file" name="upload" id="imgInp" style="width: 100%; padding: 10px;">
	
	<div class="spacer" style="height: 25px"></div>
	
	<div class="row1 fluid">
		<div class="span1" style="text-align: right">
			<input type="submit" class="button success" value="Store" id="imgSubmit">
		</div>
	</div>
</form>

<div class="spacer" style="height: 250px"></div>

<script src="<?= spitfire\core\http\URL::asset('js/avatar.js') ?>"></script>
