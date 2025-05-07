<?php

declare(strict_types=1);

namespace Kami\Notes;

use Kami\Notes\Domain\Note;
use Kami\Notes\Domain\NoteId;
use Kami\Notes\Domain\NoteRepository;

final readonly class NoteService
{
    public function __construct(private NoteRepository $noteRepository)
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
}
