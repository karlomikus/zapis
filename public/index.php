<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Middlewares\TrailingSlash;
use Kami\Notes\Action\SearchAction;
use Kami\Notes\Action\ShowNoteAction;
use Kami\Notes\Action\UpdateNoteAction;

require __DIR__ . '/../vendor/autoload.php';

$builder = new ContainerBuilder();
$builder->addDefinitions(require __DIR__ . '/../config/di.php');
$container = $builder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->redirect('/', '/notes/index', 301);
$app->post('/search', SearchAction::class);
$app->get('/notes/{file}/render', ShowNoteAction::class);
$app->get('/notes[/{file}]', ShowNoteAction::class);
$app->post('/notes/[{file}]', UpdateNoteAction::class);

$app->add(new TrailingSlash());

$app->run();
