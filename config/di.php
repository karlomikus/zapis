<?php

declare(strict_types=1);

use Twig\Environment;
use Loupe\Loupe\Loupe;
use Kami\Notes\Domain\Config;
use Loupe\Loupe\LoupeFactory;
use Loupe\Loupe\Configuration;
use Twig\Loader\FilesystemLoader;
use Symfony\Component\Finder\Finder;
use Loupe\Loupe\Config\TypoTolerance;
use Psr\Container\ContainerInterface;
use League\CommonMark\ConverterInterface;
use League\CommonMark\GithubFlavoredMarkdownConverter;

return [
    Config::class => function () {
        return new Config(
            contentFolderPath: rtrim(__DIR__ . '/../content', '/'),
        );
    },

    Finder::class => function (ContainerInterface $c) {
        /** @var Config $config */
        $config = $c->get(Config::class);
        $finder = new Finder();
        $finder->files()->in($config->contentFolderPath);

        return $finder;
    },

    ConverterInterface::class => function () {
        return new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
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

        $configuration = Configuration::create()
            ->withFilterableAttributes(['title'])
            ->withTypoTolerance(TypoTolerance::create()->withFirstCharTypoCountsDouble(false));

        return (new LoupeFactory())->create($config->contentFolderPath, $configuration);
    }
];
