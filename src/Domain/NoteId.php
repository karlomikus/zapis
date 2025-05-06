<?php

declare(strict_types=1);

namespace Kami\Notes\Domain;

use Stringable;

final readonly class NoteId implements Stringable
{
    public function __construct(
        public string $value,
    ) {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
