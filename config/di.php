<?php

declare(strict_types=1);

use Monolog\Level;
use Monolog\Logger;
use Twig\Environment;
use Loupe\Loupe\Loupe;
use Psr\Log\LoggerInterface;
use Kami\Notes\Domain\Config;
use Loupe\Loupe\LoupeFactory;
use Loupe\Loupe\Configuration;
use Twig\Loader\FilesystemLoader;
use Monolog\Handler\StreamHandler;
use Psr\Container\ContainerInterface;

return [
    Config::class => function () {
        return new Config(
            contentFolderPath: rtrim(__DIR__ . '/../content', '/'),
        );
    },

    LoggerInterface::class => function () {
        $log = new Logger('main');
        $log->pushHandler(new StreamHandler('php://stdout', Level::Info));

        return $log;
    },

    Environment::class => function () {
        $loader = new FilesystemLoader(__DIR__ . '/../templates');

        return new Environment($loader, [
            //'cache' => __DIR__ . '/../cache',
            'debug' => true,
        ]);
    },

    Loupe::class => function (ContainerInterface $c) {
        /** @var Config $config */
        $config = $c->get(Config::class);

        $configuration = Configuration::create()->withSortableAttributes(['path'])->withLanguages(['en', 'hr']);

        return (new LoupeFactory())->create($config->contentFolderPath, $configuration);
    },

    \Kami\Notes\Domain\NoteRepository::class => DI\autowire(\Kami\Notes\FileNoteRepository::class),
];
