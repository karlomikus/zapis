<?php

declare(strict_types=1);

namespace Kami\Notes\Action;

use Kami\Notes\Domain\Note;
use Kami\Notes\Domain\NoteId;
use Kami\Notes\FileNoteRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class UpdateNoteAction
{
    public function __construct(private FileNoteRepository $repository)
    {
    }

    /**
     * @param array<string, string> $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $noteId = $args['file'] ?? '';
        $noteId = new NoteId($noteId);

        $note = $this->repository->find($noteId);
        if ($note === null) {
            $extension = 'md';
            $note = new Note(
                id: $noteId,
                content: '',
                path: $noteId . '.' . $extension,
                extension: $extension, // TODO: Pull from header
            );
        }

        $note->content = (string) $request->getBody()->getContents();

        $this->repository->save($note);

        return $response;
    }
}
