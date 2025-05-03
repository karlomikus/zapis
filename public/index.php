<?php

declare(strict_types=1);

use Kami\Notes\Action\ShowNoteAction;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$builder = new \DI\ContainerBuilder();
$builder->addDefinitions(require __DIR__ . '/../config/di.php');
$container = $builder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->get('/notes[/{file}]', ShowNoteAction::class);

$app->run();