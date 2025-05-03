<?php

declare(strict_types=1);

namespace Kami\Notes;

use Kami\Notes\Domain\Note;
use Kami\Notes\Domain\NoteId;
use Symfony\Component\Finder\Finder;
use Kami\Notes\Domain\NoteRepository;

final readonly class FileNoteRepository implements NoteRepository
{
    public function __construct(private Finder $finder, private FileNoteMapper $mapper)
    {
    }

    public function find(NoteId $identifier): ?Note
    {
        $files = $this->finder->sortByModifiedTime();

        if (!$files->hasResults()) {
            return null;
        }

        foreach ($files as $file) {
            if ($file->getFilenameWithoutExtension() === $identifier->value) {
                return $this->mapper->map($file);
            }
        }

        return null;
    }
}
