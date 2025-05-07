<?php

declare(strict_types=1);

namespace Kami\Notes\Action;

use Twig\Environment;
use Kami\Notes\NoteService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class ShowNoteAction
{
    public function __construct(private Environment $twig, private NoteService $service)
    {
    }

    /**
     * @param array<string, string> $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $noteId = $args['file'];

        $note = $this->service->getNoteViewModel($noteId);

        $body = $this->twig->render('note.html.twig', [
            'note' => $note,
        ]);

        $response->getBody()->write($body);

        return $response;
    }
}
