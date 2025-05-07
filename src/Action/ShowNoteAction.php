<?php

declare(strict_types=1);

namespace Kami\Notes\Action;

use Twig\Environment;
use Kami\Notes\Domain\Note;
use Kami\Notes\Domain\NoteId;
use Kami\Notes\NoteViewModel;
use Kami\Notes\FileNoteRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class ShowNoteAction
{
    public function __construct(private Environment $twig, private FileNoteRepository $repository)
    {
    }

    /**
     * @param array<string, string> $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $noteId = $args['file'];

        if (empty($noteId) || !str_ends_with($noteId, '.md')) {
            return $response->withStatus(404);
        }

        $noteId = new NoteId($noteId);
        $note = $this->repository->find($noteId);
        if ($note === null) {
            $note = new Note(
                id: $noteId,
                title: basename($noteId->value),
                content: 'Start writing your note here',
                path: $noteId->value,
                extension: 'md',
            );
        }

        $dto = NoteViewModel::fromNote($note);

        $body = $this->twig->render('note.html.twig', [
            'note' => $dto,
        ]);

        $response->getBody()->write($body);

        return $response;
    }
}
