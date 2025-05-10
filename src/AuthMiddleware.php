<?php

declare(strict_types=1);

namespace Kami\Notes;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final readonly class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ResponseFactoryInterface $responseFactory)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getUri()->getPath() === '/auth' || isset($_SESSION['username'])) {
            return $handler->handle($request);
        }

        $response = $this->responseFactory->createResponse(301);
        $response = $response->withHeader('Location', '/auth');

        return $response;
    }
}
