<?php

declare(strict_types=1);

namespace Kami\Notes\Domain;

final class Note
{
    public function __construct(
        public NoteId $id,
        public string $title,
        public string $content,
        public string $path,
        public string $extension,
    ) {
    }

    public function move(string $newPath, ?string $extension = null): void
    {
        $this->path = $newPath;
        if ($extension !== null) {
            $this->extension = $extension;
        }
    }

    public function toSearchDocument(): array
    {
        return [
            'id' => $this->id->value,
            'title' => $this->title,
            'path' => $this->path,
        ];
    }
}
