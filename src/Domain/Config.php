<?php

declare(strict_types=1);

namespace Kami\Notes\Domain;

final readonly class Config
{
    public function __construct(
        public string $contentFolderPath,
    ) {
    }
}
