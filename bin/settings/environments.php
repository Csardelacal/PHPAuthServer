<?php

$e = new spitfire\environment('dev');

$e->set('db_user',         'root');
$e->set('db_pass',         '');
$e->set('db_database',     'commishes_users');
$e->set('db_table_prefix', 'dev_');

$e->set('email.transport', new \mail\MailGunTransport('sandbox60014ff3bfdf494195015894b3b72ff3.mailgun.org', 'key-fb4bd765e5a9f0a24fd96b15a19795d9'));