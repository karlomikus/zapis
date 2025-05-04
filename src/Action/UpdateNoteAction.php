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

        $extension = 'md'; // Pull from header
        $path = $noteId . '.' . $extension;

        $existingNote = $this->repository->find($noteId);
        if ($existingNote !== null) {
            $path = $existingNote->path;
            $extension = $existingNote->extension;
        }

        $updatedNote = new Note(
            id: $noteId,
            content: (string) $request->getBody()->getContents(),
            path: $path,
            extension: $extension,
        );

        $this->repository->save($updatedNote);

        return $response;
    }
}
