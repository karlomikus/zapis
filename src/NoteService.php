<?php

declare(strict_types=1);

namespace Kami\Notes;

use Kami\Notes\Domain\Note;
use Kami\Notes\Domain\NoteId;
use Kami\Notes\Domain\NoteRepository;
use Loupe\Loupe\Loupe;

final readonly class NoteService
{
    public function __construct(private NoteRepository $noteRepository, private Loupe $loupe)
    {
    }

    public function getNoteViewModel(string $noteId): NoteViewModel
    {
        $noteId = new NoteId($noteId);
        $note = $this->noteRepository->find($noteId);
        if ($note === null) {
            $note = new Note(
                id: $noteId,
                title: basename($noteId->value),
                content: 'Start writing your note here',
                path: $noteId->value,
                extension: 'md',
            );
        }

        return NoteViewModel::fromNote($note);
    }

    public function putNote(string $noteId, string $content): void
    {
        $noteId = new NoteId($noteId);

        $note = $this->noteRepository->find($noteId);

        if ($note === null) {
            $path = $noteId->value;

            $note = new Note(
                id: $noteId,
                title: 'New note',
                content: $content,
                path: $path,
                extension: 'md',
            );
        }

        $note->content = $content;

        $this->noteRepository->save($note);
    }

    public function deleteNote(string $noteId): void
    {
        $noteId = new NoteId($noteId);

        $this->noteRepository->delete($noteId);

        $this->loupe->deleteDocument($noteId->value);
    }
}
