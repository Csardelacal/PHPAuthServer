<?php


if (php_sapi_name() === 'cli' || strpos($_SERVER['SERVER_NAME'], 'coverage') !== false) {
	return;
}

xdebug_start_code_coverage( XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE | XDEBUG_CC_BRANCH_CHECK);
