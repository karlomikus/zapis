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
use Slim\Psr7\Factory\ResponseFactory;
use League\OAuth2\Client\Provider\Github;
use Psr\Http\Message\ResponseFactoryInterface;

return [
    Github::class => function (ContainerInterface $c) {
        /** @var Config $config */
        $config = $c->get(Config::class);

        return new Github([
            'clientId' => $config->oauthClientId,
            'clientSecret' => $config->oauthClientSecret,
            'redirectUri' => $config->oauthRedirect,
        ]);
    },

    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(ResponseFactory::class);
    },

    LoggerInterface::class => function () {
        $log = new Logger('main');
        $log->pushHandler(new StreamHandler('php://stdout', Level::Info));

        return $log;
    },

    Environment::class => function (ContainerInterface $c) {
        /** @var Config $config */
        $config = $c->get(Config::class);

        $loader = new FilesystemLoader(__DIR__ . '/../templates');

        $options = [];
        if ($config->environment === 'dev') {
            $options['debug'] = true;
        } else {
            $options['cache'] = __DIR__ . '/../var';
        }

        return new Environment($loader, $options);
    },

    Loupe::class => function (ContainerInterface $c) {
        /** @var Config $config */
        $config = $c->get(Config::class);

        $configuration = Configuration::create()->withSortableAttributes(['path'])->withLanguages(['en', 'hr']);

        return (new LoupeFactory())->create($config->contentFolderPath, $configuration);
    },

    \Kami\Notes\Domain\NoteRepository::class => DI\autowire(\Kami\Notes\FileNoteRepository::class),
];
