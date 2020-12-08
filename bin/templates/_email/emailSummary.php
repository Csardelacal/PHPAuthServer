<body style="font-family: sans-serif; background: #FAFAFA; padding: 0; margin: 0">


	<div style="padding: 10px; font-weight: bold; color: #FFF; background: #5299cc">
		<div style="margin: 0 auto; max-width: 500px;">
			YCH - Email Summary
		</div>
	</div>

	<div style="text-align: center; background: #FAFAFA">
		<div style="max-width: 500px; width: 500px; display: inline-block; text-align: left;">

			<h1 style="font-size: 18px;">
				Your daily email-provider summary
			</h1>

			<div class="spacer" style="height: 5px"></div>

			<p style="color: #444">
				This overview shows the email-provider of all registered users in the last 24h.
			</p>

			<div class="spacer" style="height: 40px"></div>

			<table style="width: 100%; text-align: left;">
				<tr>
					<th>Provider</th>
					<th>Users</th>
				</tr>
				<?php foreach ($emails as $provider => $amt) : ?>
					<tr>
						<td>@<?= $provider ?></td>
						<td><?= $amt ?></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<div class="spacer" style="height: 70px"></div>

			<p style="text-align: center; font-size: 12px; color: #777; padding-top: 20px; border-top: solid 1px #CCC;">
				You received this notification because your email was used on YCH.Commishes.
				This email is only sent once per day.
			</p>
		</div>
	</div>
</body>
