<?php

declare(strict_types=1);

namespace Kami\Notes\Action;

use Throwable;
use Kami\Notes\NoteService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class DeleteNoteAction
{
    public function __construct(private NoteService $service)
    {
    }

    /**
     * @param array<string, string> $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $noteId = $args['file'] ?? '';

        try {
            $this->service->deleteNote(
                $noteId
            );
        } catch (Throwable $e) {
            $response->getBody()->write('Error: ' . $e->getMessage());

            return $response->withStatus(500);
        }

        return $response;
    }
}
