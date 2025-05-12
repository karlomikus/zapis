<?php

declare(strict_types=1);

namespace Kami\Notes\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final readonly class AuthAction
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        /**
         * @var array{username?: string|null, password?: string|null} $req
         */
        $req = (array) $request->getParsedBody();

        if (empty($req['username']) || empty($req['password'])) {
            $this->logger->error('Empty credentials provided');

            return $response->withStatus(401);
        }

        $u = $_SERVER['USERNAME'] ?? null;
        $p = $_SERVER['PASSWORD'] ?? null;

        $username = $req['username'];
        $password = $req['password'];

        if ($username === $u && $password === $p) {
            $_SESSION['username'] = $username;

            $this->logger->info('User logged in', [
                'username' => $username,
            ]);

            return $response->withHeader('Location', '/')->withStatus(302);
        }

        $this->logger->error('Invalid credentials', [
            'username' => $username,
        ]);

        return $response->withHeader('Location', '/auth')->withStatus(302);
    }
}
