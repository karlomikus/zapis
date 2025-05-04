<?php

declare(strict_types=1);

namespace Kami\Notes;

final readonly class SearchBody
{
    public function __construct(
        public string $query,
    ) {
    }
}
