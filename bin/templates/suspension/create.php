

<form class="regular" method="POST" action="">
	<input type="hidden" name="xsrf" value="<?= $xsrf ?>">
	<!--Reason for the suspension-->
	<div class="row l2">
		<div class="span l1">
			<div style="font-size: .75em; color: #555">
				Reason given to the user
			</div>
			
			<div class="spacer" style="height: 5px"></div>

			<div class="field">
				<textarea name="reason" placeholder="Enter a reason that explains to the user why they were suspended..." style="height: 50px"><?= $_GET['reason']?? '' ?></textarea>
			</div>
		</div>
	
		<!--Administrative notes-->
		<div class="span l1">
			<div style="font-size: .75em; color: #555">
				Administrative notes
			</div>
			
			<div class="spacer" style="height: 5px"></div>

			<div class="field">
				<textarea name="notes" placeholder="Notes here won't be shown to the user, just administrators..." style="height: 50px"><?= $_GET['notes']?? '' ?></textarea>
			</div>
		</div>
	</div>
	
	<div class="row l10 m5 s3">
		<div class="span l5 desktop-only"></div>
		<div class="span l2 m2 s1" style="text-align: right">
			<div class="styled-select">
				<select name="blockLogin">
					<option value="n">Restrict the user's functionality</option>
					<option value="y" <?= ($_GET['login']?? 'true') === 'false'? 'selected' : '' ?>>Prevent the user from logging in</option>
				</select>
			</div>
		</div>
		<div class="span l2 m2 s1" style="text-align: right">
			<div class="styled-select">
				<select name="duration">
					<?php $durations = [
						'0h' => 'Immediate',
						'6h' => '6 hours',
						'12h' => '12 hours',
						'1d' => '1 day',
						'3d' => '3 days',
						'1w' => '1 week',
						'2w' => '2 weeks',
						'1m' => '1 month',
						'3m' => '3 months',
						'6m' => '6 months',
						'1y' => '1 year',
						'10y' => '10 years'
					]; ?>
					<?php foreach ($durations as $duration => $caption) : ?>
					<option value="<?= $duration ?>"  <?= ($_GET['duration']?? '1w') === $duration? 'selected' : '' ?>><?= $caption ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="span l1 m1 s1" style="text-align: right">
			<input type="submit" class="button error small" value="Suspend">
		</div>
	</div>
</form>