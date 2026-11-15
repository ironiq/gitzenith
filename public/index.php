<?php

declare( strict_types=1 );

use GitZenith\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/../vendor/autoload.php';

if( is_readable( dirname( __DIR__ ) . '/.env' ) )
{
	( new Dotenv() )->loadEnv( dirname( __DIR__ ) . '/.env' );
}

$_SERVER += $_ENV;
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) ?: 'dev';
$_SERVER['APP_DEBUG'] = $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? 'prod' !== $_SERVER['APP_ENV'];
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = (int) $_SERVER['APP_DEBUG'] || filter_var($_SERVER['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN) ? '1' : '0';

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
