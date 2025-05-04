<?php

declare(strict_types=1);

namespace Kami\Notes;

final readonly class TwigNote
{
    public function __construct(
        public string $title,
        public string $markdown,
        public string $html,
        public string $path = '',
    ) {
    }
}
