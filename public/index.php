<?php

declare( strict_types=1 );

use GitZenith\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';
require dirname( __DIR__ ) . '/config/bootstrap.php';

$env = $_SERVER['APP_ENV'];
$debug = (bool) ( $_SERVER['APP_DEBUG'] ?? ( $env !== 'prod' ) );

if( $debug )
{
	umask( 0000 );
	Debug::enable();
}

if( $trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false )
{
	Request::setTrustedProxies( explode( ',', $trustedProxies ), Request::HEADER_X_FORWARDED_FOR ^ Request::HEADER_X_FORWARDED_HOST );
}

if( $trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false )
{
	Request::setTrustedHosts(explode(',', $trustedHosts));
}

$kernel = new Kernel( $env, $debug );
$request = Request::createFromGlobals();
$response = $kernel->handle( $request );
$response->send();
$kernel->terminate( $request, $response );
