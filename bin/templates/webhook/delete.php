
<div class="topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
	Confirm webhook deletion
</div>

<div class="spacer" style="height: 25px"></div>

<p style="font-size: .8em; color: #555">
	Please confirm that you wish to delete this webhook. The application will no
	longer receive notifications. History will be deleted too.
</p>

<div class="spacer" style="height: 25px"></div>

<p style="text-align: center">
	<a class="button error" href="<?= $confirmURL ?>">Delete</a>
	<a class="button" href="<?= $cancelURL ?>">Keep webhook</a>
</p>

<div class="spacer" style="height: 250px"></div>