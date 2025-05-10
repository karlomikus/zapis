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
use Kami\Notes\Action\SyncSearchAction;
use Kami\Notes\Action\UpdateNoteAction;
use Psr\Http\Message\ResponseFactoryInterface;

require __DIR__ . '/../vendor/autoload.php';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    // 'domain' => 'localhost:8380',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_name('zapis');
session_start([
    'use_strict_mode' => true,
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    throw new RuntimeException('Session not started');
}

$builder = new ContainerBuilder();
$builder->addDefinitions(require __DIR__ . '/../config/di.php');
if (($_ENV['APP_ENV'] ?? 'dev') === 'prod') {
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
$app->get('/auth', ShowAuthAction::class);
$app->post('/auth', AuthAction::class);
$app->get('/logout', LogoutAction::class);

$app->add(new TrailingSlash());
$app->add(new AuthMiddleware($container->get(ResponseFactoryInterface::class)));

$app->run();
