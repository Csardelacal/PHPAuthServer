<?php

use spitfire\core\http\URL;
?>

<div id="email-address-change">
	<emailForm current-email="<?= $authUser->email ?>"></emailForm>
</div>

<script src="<?= URL::asset('js/edit/email.min.js') ?>"></script>
