<?php

declare(strict_types=1);

namespace Kami\Notes\Domain;

final readonly class Note
{
    public function __construct(
        public NoteId $id,
        public string $title,
        public string $content,
        public string $path,
        public string $filename,
        public string $extension,
    ) {
    }
}
