
<div class="topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
	Change your password
</div>

<div class="spacer" style="height: 25px"></div>

<p style="font-size: .8em; color: #555">
	Select your new password. You need to enter your old password to prevent 
	your account from being stolen..
</p>

<div class="spacer" style="height: 25px"></div>

<form class="regular" method="POST">
	
	<div class="row1 fluid">
		<div class="span1">
			<div style="font-size: .75em; color: #555">
				Enter your current password.
			</div>
			
			<div class="field" style="border-left: solid 2px #2a912e; padding: 8px 0px 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<input name="password_old" type="password" placeholder="Your current password">
			</div>
		</div>
	</div>
	
	<div class="spacer" style="height: 25px"></div>
	
	<div class="row1 fluid">
		<div class="span1">
			<div style="font-size: .75em; color: #555">
				Enter your new password
			</div>
			
			<div class="field" style="border-left: solid 2px #2a912e; padding: 8px 0px 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<input name="password" type="password" placeholder="Set your new password">
			</div>
		</div>
	</div>
	
	<div class="spacer" style="height: 25px"></div>
	
	<div class="row1 fluid">
		<div class="span1">
			<div style="font-size: .75em; color: #555">
				Enter your new password <strong>again</strong>
			</div>
			
			<div class="field" style="border-left: solid 2px #2a912e; padding: 8px 0px 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<input name="password_verify" type="password" placeholder="Verify your password">
			</div>
		</div>
	</div>
	
	<div class="spacer" style="height: 25px"></div>
	
	<div class="row1 fluid">
		<div class="span1" style="text-align: right">
			<input type="submit" class="button success" value="Store">
		</div>
	</div>
</form>

<div class="spacer" style="height: 250px"></div>