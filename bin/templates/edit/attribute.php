
<div class="spacer" style="height: 50px"></div>

<form class="condensed standalone" method="POST">
	<div class="description">
		Set your new <strong><?= $attribute->name ?></strong>.
	</div>
	<input type="text" name="value" placeholder="<?= $attribute->name ?>" value="<?= $value ?>">
	<input type="submit" value="Store">
</form>