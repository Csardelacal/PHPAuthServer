<?php

$e = new spitfire\core\Environment('dev');

$e->set('db_user',         'root');
$e->set('db_pass',         '');
$e->set('db_database',     'commishes_users');
$e->set('db_table_prefix', 'dev_');

$e->set('email.transport', new \mail\MailGunTransport('', ''));

$p = new spitfire\core\Environment('dev');

$p->set('db_user',         '');
$p->set('db_pass',         '');
$p->set('db_database',     '');
$p->set('db_table_prefix', 'u_');

$p->set('email.transport', new \mail\MailGunTransport('', ''));

spitfire\core\Environment::set_active_environment($_SERVER['SERVER_NAME'] === 'localhost'? $e : $p);