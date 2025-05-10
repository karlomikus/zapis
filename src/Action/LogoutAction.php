<?php

declare(strict_types=1);

namespace Kami\Notes\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class LogoutAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        session_unset();
        session_destroy();

        return $response->withHeader('Location', '/auth')->withStatus(302);
    }
}
