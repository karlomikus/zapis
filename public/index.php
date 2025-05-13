<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Kami\Notes\Domain\Config;
use Kami\Notes\AuthMiddleware;
use Middlewares\TrailingSlash;
use Kami\Notes\Action\LogoutAction;
use Kami\Notes\Action\SearchAction;
use Kami\Notes\Action\SSOAuthAction;
use Kami\Notes\Action\ShowAuthAction;
use Kami\Notes\Action\ShowNoteAction;
use Kami\Notes\Action\DeleteNoteAction;
use Kami\Notes\Action\SyncSearchAction;
use Kami\Notes\Action\UpdateNoteAction;
use League\OAuth2\Client\Provider\Github;
use Psr\Http\Message\ResponseFactoryInterface;

require __DIR__ . '/../vendor/autoload.php';

$_SERVER = array_merge($_SERVER, $_ENV);
$appConfig = new Config(
    contentFolderPath: rtrim(__DIR__ . '/../content', '/'),
    environment: $_SERVER['APP_ENV'],
    authEmail: $_SERVER['AUTH_EMAIL'] ?? '',
    oauthClientId: $_SERVER['GITHUB_CLIENT_ID'] ?? '',
    oauthClientSecret: $_SERVER['GITHUB_CLIENT_SECRET'] ?? '',
    oauthRedirect: $_SERVER['GITHUB_REDIRECT'] ?? '/sso',
);

session_name('zapis');
session_start();

if (session_status() !== PHP_SESSION_ACTIVE) {
    throw new RuntimeException('Session not started');
}

$diConfig = require __DIR__ . '/../config/di.php';
$builder = new ContainerBuilder();
$builder->addDefinitions([
    Config::class => $appConfig,
]);
$builder->addDefinitions($diConfig);
if ($appConfig->environment === 'prod') {
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
$app->get('/logout', LogoutAction::class);
$app->get('/sso', SSOAuthAction::class);

$app->add(new TrailingSlash());
$app->add(new AuthMiddleware($container->get(ResponseFactoryInterface::class), $container->get(Github::class)));

$app->run();
