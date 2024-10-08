<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// The check is to ensure we don't use .env in production
if (!isset($_SERVER['APP_ENV'])) {
    if (!class_exists(Dotenv::class)) {
        throw new RuntimeException('APP_ENV environment variable is not defined. You need to define environment variables for configuration or add "symfony/dotenv" as a Composer dependency to load variables from a .env file.');
    }
    (new Dotenv())->load(__DIR__.'/../.env');
}

$env = $_SERVER['APP_ENV'] ?? 'dev';
$debug = (bool) ($_SERVER['APP_DEBUG'] ?? ('prod' !== $env));

if ($debug) {
    umask(0000);
    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts(explode(',', $trustedHosts));
}

$policy = "default-src 'self' 'unsafe-inline';";
$kernel = new Kernel($env, $debug);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->setPublic();
$response->setMaxAge(290304000);
$response->headers->addCacheControlDirective('must-revalidate', true);
$response->headers->set('Content-Security-Policy', $policy);
$response->send();
$kernel->terminate($request, $response);
