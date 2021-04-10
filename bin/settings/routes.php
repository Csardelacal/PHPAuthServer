<?php

/* Use this file to add routes to your Spitfire app. Just use them like this
 *
 * router::route('/old/url', '/new/url');
 *
 * Or like this
 * 
 * router:route('old/url/*', 'new/$2/url');
 * 
 * Remember that routes are blocking. If one matches it'll stop the execution
 * of the following rules. So add them wisely.
 * It's really easy and fun!
 */

spitfire\core\router\Router::getInstance()->request('/app/permissions/:action?', ['controller' => ['permissions'], 'action' => ':action']);
spitfire\core\router\Router::getInstance()->request('/client/credential/:action?', ['controller' => ['client', 'credential'], 'action' => ':action']);
spitfire\core\router\Router::getInstance()->request('/mfa/backup-code/:action?', ['controller' => ['mfa', 'BackUpCode'], 'action' => ':action']);
spitfire\core\router\Router::getInstance()->request('/mfa/password/:action?', ['controller' => ['mfa', 'Password'], 'action' => ':action']);
spitfire\core\router\Router::getInstance()->request('/mfa/totp/:action?', ['controller' => ['mfa', 'TOTP'], 'action' => ':action']);
spitfire\core\router\Router::getInstance()->request('/mfa/rfc6238/:action?', ['controller' => ['mfa', 'TOTP'], 'action' => ':action']);
spitfire\core\router\Router::getInstance()->request('/mfa/phone/:action?', ['controller' => ['mfa', 'Phone'], 'action' => ':action']);
spitfire\core\router\Router::getInstance()->request('/mfa/email/:action?', ['controller' => ['mfa', 'Email'], 'action' => ':action']);
spitfire\core\router\Router::getInstance()->request('/session/:action?', ['controller' => ['Session'], 'action' => ':action']);