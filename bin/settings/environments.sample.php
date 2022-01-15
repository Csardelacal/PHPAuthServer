<?php

$p = new spitfire\core\Environment('dev');

$p->set('db', 'mysqlpdo://www:test@mysql:3306/testdb');

/*
 * This enables generous TTLs for sessions. This means that the session will be
 * extended for an extra 15% over the requested TTL for a token. This setting is
 * specially helpful in applications that manage lots of tokens with a
 * transaction enforcing database. Since otherwise the database will require
 * lots of write / read operations.
 */
$p->set('phpAuth.token.extraTTL', true);

$p->set('email.transport', new \mail\MailGunTransport('', ''));