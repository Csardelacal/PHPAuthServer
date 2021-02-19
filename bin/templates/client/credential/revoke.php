<?php if (isset($expired) && $expired) { current_context()->response->getHeaders()->redirect(url('app', 'detail', $credential->client->_id)); } ?>

<div class="spacer medium"></div>

<div class="row l1">
	<div class="span l1">
		<h1 class="text:grey-600">Revoke credential for <?= $credential->client->name ?></h1>
		<div class="spacer small"></div>
		<p class="text:grey-800">
			Use this form to revoke an application secret for your application. Your
			application will no longer be able to issue new tokens using these credentials.
		</p>
		<div class="spacer small"></div>
		<p class="text:grey-800">
			<strong>
				If your application was compromised and tokens were leaked, please also
				revoke the tokens separately.
			</strong>
			This endpoint does not terminate tokens, since credentials should be renewed 
			regularly to prevent the secret from being exposed.
		</p>
		
		<div class="spacer large"></div>
		
		<form method="POST" action="">
			<div class="row l4 ng-lr">
				<div class="span l3">
					<div class="frm-ctrl-outer">
						<select class="frm-ctrl" name="expires">
							<option value="0">Expire immediately</option>
							<option value="86400">Expire after 24 hours</option>
							<option value="604800">Expire after 1 week</option>
							<option value="2592000">Expire after 30 days</option>
							<option value="7776000">Expire after 90 days</option>
							<option value="31536000">Expire after 1 year</option>
							<option value="63072000">Expire after 2 years</option>
						</select>
						<label>Expire</label>
					</div>
				</div>
				<div class="span l1">
					<div class="align-right">
						<button type="submit" class="button" style="width: 100%">Expire</button>
					</div>
				</div>
			</div>

			<div class="spacer large"></div>
		</form>
	</div>
</div>
