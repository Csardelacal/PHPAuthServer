<?php

use spitfire\io\stp\SimpleStackTracePrinter;

?><!DOCTYPE html>
<html>
	<head>
		<title>Spitfire - User Banned</title>
		<style>
			body, html {
				margin: 0;
				padding: 0;
				font-family: arial;
				color: #555;
			}
			.errormsg {
				background-color: #2478C6;
				background-image: -moz-linear-gradient(90deg, #1E63B4 0%, #2478C6 10%, #2478C6 90%, #1E63B4);
				background-image: -webkit-linear-gradient(90deg, #1E63B4 0%, #2478C6 10%, #2478C6 90%, #1E63B4);
				color: #FFF;
				padding: 20px;
			}
			
			.errormsg p {
				color: #FFF;
			}

			.errormsg a {
				color: #f37a44;
			}
			
			.wrapper {
				margin: 0 auto;
				width: 960px;
			}
			
			h1 {
				margin: 8px 0 4px;
				font-size: 20px;
			}
			h2 {
				margin: 8px 0 3px;
				font-size: 17px;
			}
			
			p {
				font-size: 13px;
				color: #555;
			}
			
			small {
				font-size: 80%;
				color: #777;
			}
			
			.sfheader {
				margin: 20px 0 10px 0;
			}
			
			.errordescription pre,
			.errordescription .debugmessages {
				border: dashed 1px #cccccc;
				background: #f2f2f2;
				border-radius: 5px;
				padding: 5px;
				max-height: 300px;
				overflow: auto;
			}
		</style>
	</head>
	<body>
		<div class="wrapper">
			<h1 class="sfheader">Spitfire <small>//User Banned</small></h1>
		</div>
		<div class="errormsg">
			<div class="wrapper">
				<h1><?= $code ?>: <?= $message ?></h1>
				<p><?= $exception? $exception->getReason() : '' ?></p>
				<?php if($exception->getExpiry() > 0):?>
                <p>
                    Your suspension will automatically be lifted in
                    <?php
						echo (new DateTime('now'))->diff(new DateTime('@'.$exception->getExpiry()))->format('%a days, %h hours and %i minutes');
                    ?>
                </p>
                <?php endif; ?>
				<p>If you have any questions about this, please contact our support team. - <a href="https://wiki.commishes.com/en/contact">Contact Support</a></p>

			</div>
		</div>
        <div class="errordescription wrapper"><p>
                <small>
                    Technical Information - <?=str_pad($exception? $exception->getUserID() : '', 10, '0', STR_PAD_LEFT) ?>
                </small>
            </p>
		</div>
</body>
</html>