<?php


if ($_SERVER['SERVER_NAME'] == 'coverage' || php_sapi_name() === 'cli') {
	return;
}

xdebug_start_code_coverage( XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE | XDEBUG_CC_BRANCH_CHECK);
