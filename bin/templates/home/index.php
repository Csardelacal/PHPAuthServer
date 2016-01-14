<html>
	<head>
		<title><?= $message ?></title>
	</head>
	<body>
		<div class="wrapper">
			<h1><?= $message ?></h1>
			<?php foreach ($users as $user): ?>
			<?= $user->username ?>
			<?php endforeach; ?>
			
		</div>
	</body>
</html>