<?php

declare(strict_types=1);

namespace Kami\Notes\Action;

use Twig\Environment;
use Kami\Notes\NoteService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class AuthAction
{
    public function __construct(private Environment $twig, private NoteService $service)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        /**
         * @var array{username?: string|null, password?: string|null} $req
         */
        $req = (array) $request->getParsedBody();

        if (empty($req['username']) || empty($req['password'])) {
            $response->getBody()->write('Invalid credentials');
            return $response->withStatus(401);
        }

        $u = $_ENV['USERNAME'] ?? null;
        $p = $_ENV['PASSWORD'] ?? null;

        $username = $req['username'];
        $password = $req['password'];

        if ($username === $u && $password === $p) {
            $_SESSION['username'] = $username;

            return $response->withHeader('Location', '/')->withStatus(302);
        }

        return $response->withHeader('Location', '/auth')->withStatus(302);
    }
}
