<!DOCTYPE html>
<html>
	<head>
		<style>
			* {
				box-sizing: border-box;
			}
			
			html {
				font-family: System, sans-serif;
				background: #E6E8EA;
				color: #373737;
			}
			
			.outer {
				max-width: 700px;
				margin: 0 auto;
				background: #FFF;
				box-shadow: 2px 2px 6px #C1C8CD, 1px 1px 3px #C1C8CD;
			}
			
			.padded {
				padding: 1.5rem 2.5rem;
			}
			
			.separator {
				border-top: solid 1px #CCC;
			}
			
			.small-print {
				text-align: center;
				color: #74838E;
			}
			
			h1 {
				font-size: 1.2rem;
				color: #777;
			}
			
			p {
				font-size: .86rem;
				line-height: 1.4rem;
			}
			
			blockquote {
				margin: 2rem 0rem;
				padding: 1rem;
				background: #EEF0F1;
				border: solid 1px #C1C8CD;
				color: #5F6C75;
				border-radius: 5px;
				font-size: .86rem;
				line-height: 1.4rem;
				white-space: pre-wrap;
				font-family: monospace;
			}
		</style>
	</head>
	<body>
		<div class="spacer" style="height: 30px;"></div>

		<div style="max-width: 700px; margin: 0 auto; text-align: center;">
			<img src="<?= url('image', 'hero') ?>">
		</div>

		<div class="spacer" style="height: 30px;"></div>
		
		<div class="outer">
			<div class="padded">
				<p class="small-print" style="text-align: right">
					<?= $exception->getId() ?>
				</p>
				
				
				<p>Dear <?= $exception->getUser()->usernames[0]->name ?>,</p>
				<p>
					Your account was disabled by moderation as an administrative action.
					Log-in privileges have been revoked. Your suspension
					expires automatically. The reason the moderator gave was:
				</p>

				<blockquote><?= __($exception->getMessage()) ?></blockquote>
				
				<p>
					If you feel like this suspension is not appropriate, please feel
					free to contact moderation about your suspension. Include your 
					username in the email, this helps the moderator to locate your account.
				</p>
			</div>
			
			<div class="separator"></div>
			
			<div class="padded">
				<p class="small-print">Your suspension ends <?= Time::relative($exception->getExpiration()) ?></p>
			</div>

		</div>
		
		<div class="spacer" style="height: 30px;"></div>
		
		<p class="small-print">PHPAuth Server &centerdot; &copy; <?= date('Y') ?> Magic3W &centerdot; Licensed under MIT License</p>
	</body>
</html>