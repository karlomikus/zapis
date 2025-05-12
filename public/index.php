<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Kami\Notes\AuthMiddleware;
use Middlewares\TrailingSlash;
use Kami\Notes\Action\AuthAction;
use Kami\Notes\Action\LogoutAction;
use Kami\Notes\Action\SearchAction;
use Kami\Notes\Action\ShowAuthAction;
use Kami\Notes\Action\ShowNoteAction;
use Kami\Notes\Action\DeleteNoteAction;
use Kami\Notes\Action\SyncSearchAction;
use Kami\Notes\Action\UpdateNoteAction;
use Psr\Http\Message\ResponseFactoryInterface;

require __DIR__ . '/../vendor/autoload.php';

assert(is_string($_ENV['APP_ENV'] ?? null) && !empty($_ENV['APP_ENV']), 'APP_ENV must be set');

$currentEnv = $_ENV['APP_ENV'];
// $host = $_SERVER['HTTP_HOST'] ?? null;

if ($currentEnv === 'prod') {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        // 'domain' => $host,
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}
session_name('zapis');
session_start([
    'use_strict_mode' => true,
    'use_only_cookies' => true,
]);
session_regenerate_id(true);
if (session_status() !== PHP_SESSION_ACTIVE) {
    throw new RuntimeException('Session not started');
}

$diConfig = require __DIR__ . '/../config/di.php';
assert(is_array($diConfig), 'DI config must return an array');

$builder = new ContainerBuilder();
$builder->addDefinitions($diConfig);
if ($currentEnv === 'prod') {
    $builder->enableCompilation(__DIR__ . '/../var');
}
$container = $builder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->redirect('/', '/notes/index.md', 301);
$app->post('/search', SearchAction::class);
$app->post('/sync-search', SyncSearchAction::class);
$app->get('/notes[/{file:.*}]', ShowNoteAction::class); // TODO regex match for valid files
$app->post('/notes/[{file:.*}]', UpdateNoteAction::class);
$app->delete('/notes/[{file:.*}]', DeleteNoteAction::class);
$app->get('/auth', ShowAuthAction::class);
$app->post('/auth', AuthAction::class);
$app->get('/logout', LogoutAction::class);

$app->add(new TrailingSlash());
$app->add(new AuthMiddleware($container->get(ResponseFactoryInterface::class)));

$app->run();
