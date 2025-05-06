<?php

declare(strict_types=1);

namespace Kami\Notes\Domain;

use DateTimeImmutable;

final class Note
{
    public function __construct(
        public NoteId $id,
        public string $title,
        public string $content,
        public string $path,
        public string $extension,
        public DateTimeImmutable $lastModified = new DateTimeImmutable(),
    ) {
    }

    public function toSearchDocument(): array
    {
        return [
            'id' => $this->id->value,
            'title' => $this->title,
            'path' => $this->path,
        ];
    }

    public function getDirectoryPath(): string
    {
        return dirname($this->path);
    }
}
