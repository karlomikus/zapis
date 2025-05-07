<?php

declare(strict_types=1);

namespace Kami\Notes;

use Kami\Notes\Domain\Note;

final readonly class NoteViewModel
{
    public function __construct(
        public string $id,
        public string $title,
        public string $markdown,
        public string $path = '',
        public string $lastModified = '',
    ) {
    }

    public static function fromNote(Note $note): self
    {
        return new self(
            id: $note->id->value,
            title: $note->title,
            markdown: $note->content,
            path: $note->path,
            lastModified: $note->lastModified->format('Y-m-d H:i:s'),
        );
    }
}
