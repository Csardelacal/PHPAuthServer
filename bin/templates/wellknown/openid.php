<?php 

current_context()->response->getHeaders()->contentType('json');

/**
 * 
 * @see https://accounts.google.com/.well-known/openid-configuration
 * @todo Device authorization endpoint
 * @todo User info endpoint
 * @todo Revocation endpoint
 * @todo response+types_supported
 * @todo subject_types_supported
 * @todo id_token_signing_alg_values_supported
 * @todo scopes_supported
 * @todo token_endpoint_auth_methods_supported
 * @todo claims_supported
 * @todo code_challenge_methods_supported
 * @todo grant_types_supported
 */

echo json_encode([
	'issuer' => $_SERVER["SERVER_NAME"],
	'authorization_endpoint' => (string)(url('auth', 'oauth2')->absolute()),
	'token_endpoint' => (string)(url('token', 'access')->absolute()),
	'jwks_uri' => (string)(url('certificates', 'public_key')->setExtension('json')->absolute())
]);
