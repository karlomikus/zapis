<?php

declare(strict_types=1);

namespace Kami\Notes\Domain;

use Stringable;

final readonly class NoteId implements Stringable
{
    public function __construct(
        public string $value,
    ) {
        if (empty($this->value)) {
            throw new \InvalidArgumentException('Note ID cannot be empty');
        }

        if (!str_ends_with($this->value, '.md')) {
            throw new \InvalidArgumentException('Note ID must end with .md');
        }

        if (str_contains($this->value, '\\')) {
            throw new \InvalidArgumentException('Note ID cannot contain backslashes');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
