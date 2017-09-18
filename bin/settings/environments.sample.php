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

/*
 * This enables generous TTLs for sessions. This means that the session will be 
 * extended for an extra 15% over the requested TTL for a token. This setting is
 * specially helpful in applications that manage lots of tokens with a 
 * transaction enforcing database. Since otherwise the database will require
 * lots of write / read operations.
 */
$p->set('phpAuth.token.extraTTL', true);

$p->set('email.transport', new \mail\MailGunTransport('', ''));

spitfire\core\Environment::set_active_environment($_SERVER['SERVER_NAME'] === 'localhost' || Strings::startsWith($_SERVER['SERVER_NAME'], '192.')? $e : $p);