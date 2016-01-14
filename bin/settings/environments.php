<?php

$e = new spitfire\environment('dev');

$e->set('db_user',         'root');
$e->set('db_pass',         'root');
$e->set('db_database',     'commishes_users');
$e->set('db_table_prefix', 'dev_');