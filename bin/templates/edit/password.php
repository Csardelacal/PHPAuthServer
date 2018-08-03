
<form class="regular" method="POST">
	
	<div class="row l5 s1">
		<div class="span l1"></div>
		<div class="span l3 s1">
			<div class="material unpadded">
				<div class="padded">
					
					
					<div class="topbar sticky">
						Change your password.
					</div>

					<p class="small secondary">
						Use this form to change your password, your are required to 
						re-enter your current password. If you cannot remember your 
						current password you'll need to 
						<a href="<?= url('user', 'recover') ?>">recover your current password</a>
					</p>
					
					<div class="field">
						<input name="password_old" type="password" placeholder="Your current password">
					</div>
					
					<p class="small secondary">
						Enter your new password
					</p>
					<div class="field">
						<input name="password" type="password" placeholder="Set your new password">
					</div>
					
					<p class="small secondary">
						Enter your new password <strong>again</strong>
					</p>
					<div class="field">
						<input name="password_verify" type="password" placeholder="Verify your password">
					</div>
				</div>
				
				<div class="inset padded">
					<p class="small unpadded">
						Your password is encrypted and nobody (not even an administrator)
						can read the password. It is obviously not transfered 
						to any third party or application.
					</p>
				</div>
				
				<div class="padded">
					
					<div class="spacer" style="height: 10px"></div>

					<div class="row1 fluid">
						<div class="span1" style="text-align: right">
							<input type="submit" class="button success" value="Store">
						</div>
					</div>
					
					<div class="spacer" style="height: 25px"></div>
				</div>
			</div>
		</div>
	</div>
</form>
