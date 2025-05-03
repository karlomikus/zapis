<?php

declare(strict_types=1);

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Symfony\Component\Finder\Finder;
use Psr\Container\ContainerInterface;
use League\CommonMark\ConverterInterface;
use League\CommonMark\GithubFlavoredMarkdownConverter;

return [
    Finder::class => function (ContainerInterface $c) {
        $finder = new Finder();
        $finder->files()
            ->in(__DIR__ . '/../content');

        return $finder;
    },

    ConverterInterface::class => function (ContainerInterface $c) {
        return new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    },

    Environment::class => function (ContainerInterface $c) {
        $loader = new FilesystemLoader(__DIR__ . '/../templates');

        return new Environment($loader, [
            //'cache' => __DIR__ . '/../cache',
            'debug' => true,
        ]);
    },
];
