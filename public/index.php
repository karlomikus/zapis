<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Middlewares\TrailingSlash;
use Kami\Notes\Action\SearchAction;
use Kami\Notes\Action\ShowNoteAction;
use Kami\Notes\Action\SyncSearchAction;
use Kami\Notes\Action\UpdateNoteAction;

require __DIR__ . '/../vendor/autoload.php';

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

$app->add(new TrailingSlash());

$app->run();
