<?php

$e = new spitfire\environment('dev');

$e->set('db_user',         'root');
$e->set('db_pass',         '');
$e->set('db_database',     'commishes_users');
$e->set('db_table_prefix', 'dev_');

$e->set('email.transport', new \mail\MailGunTransport('domain', 'apikey'));