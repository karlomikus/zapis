<?php

declare(strict_types=1);

namespace Kami\Notes;

use League\OAuth2\Client\Provider\Github;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final readonly class AuthMiddleware implements MiddlewareInterface
{
    /**
     * @param array<string> $whitelist
     */
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private Github $provider,
        private array $whitelist = [
            '/auth',
            '/sso',
        ]
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getUri()->getPath(), $this->whitelist) || isset($_SESSION['email'])) {
            return $handler->handle($request);
        }

        $response = $this->responseFactory->createResponse(301);
        $response = $response->withHeader('Location', '/auth');

        return $response;
    }
}
