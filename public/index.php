<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Kami\Notes\Action\SearchAction;
use Kami\Notes\Action\ShowNoteAction;
use Kami\Notes\Action\UpdateNoteAction;

require __DIR__ . '/../vendor/autoload.php';

$builder = new ContainerBuilder();
$builder->addDefinitions(require __DIR__ . '/../config/di.php');
$container = $builder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->post('/search', SearchAction::class);
$app->get('/[{file}]', ShowNoteAction::class);
$app->get('/{file}/render', ShowNoteAction::class);
$app->post('/[{file}]', UpdateNoteAction::class);

$app->run();
