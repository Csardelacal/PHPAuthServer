
<div class="spacer" style="height: 30px"></div>

<div class="row1">
	<div class="span1">
		<h1>
			Create new Application
		</h1>
	</div>
</div>
<div class="row1">
	<div class="span1 material unpadded">
		<div class="padded">
			<p>
				This page allows you to create an application
			</p>
		</div>
		
		<div class="separator"></div>

		<form method="POST" enctype="multipart/form-data" class="material-form">

			<div class="padded" style="padding-top: 0; padding-bottom: 0;">
				<div class="editable-property mid-aligned"><!--
					--><div class="property-name">Name</div><!--
					--><div class="property-value"><input type="text" name="name" required></div><!--
				--></div>
			</div>

			<div class="separator"></div>

			<div class="padded" style="padding-top: 0; padding-bottom: 0;">
				<div class="editable-property"><!--
					--><div class="property-name">Icon</div><!--
					--><div class="property-value">
						<div class="edit-field" id="icon-upload-wrap">
							<input type="file" name="icon" id="icon-input" accept="image/png,image/jpeg,image/gif" required>
						</div><!--
					</div><!--
				--></div>
			</div>

			<div class="spacer" style="height: 10px;"></div>

			<div class="padded">
				<input type="submit" class="button success" value="Create">
			</div>

		</form>
	</div>
</div>

<script src="<?= URL::asset('js/app-create.min.js')?>"></script>
