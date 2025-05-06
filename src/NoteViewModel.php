<?php

declare(strict_types=1);

namespace Kami\Notes;

final readonly class NoteViewModel
{
    public function __construct(
        public string $title,
        public string $markdown,
        public string $html,
        public string $path = '',
        public string $lastModified = '',
    ) {
    }
}
