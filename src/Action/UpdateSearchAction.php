<?php

declare(strict_types=1);

namespace Kami\Notes\Action;

use Loupe\Loupe\Loupe;
use Symfony\Component\Finder\Finder;

final readonly class UpdateSearchAction
{
    public function __construct(private Loupe $search, private Finder $finder)
    {
    }

    public function __invoke(): void
    {
        foreach ($this->finder as $file) {
            $this->search->addDocument([
                'id' => $file->getFilenameWithoutExtension(),
                'path' => $file->getRelativePathname(),
                'title' => $file->getFilenameWithoutExtension(),
            ]);
        }
    }
}
