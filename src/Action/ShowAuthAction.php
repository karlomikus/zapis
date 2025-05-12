<?php

declare(strict_types=1);

namespace Kami\Notes\Action;

use Twig\Environment;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class ShowAuthAction
{
    public function __construct(private Environment $twig)
    {
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $this->twig->render('login.html.twig', [
            'title' => 'Authentication',
        ]);

        $response->getBody()->write($body);

        return $response;
    }
}
