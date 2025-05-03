<?php

declare(strict_types=1);

namespace Kami\Notes;

use Kami\Notes\Domain\Note;
use Kami\Notes\Domain\NoteId;
use Symfony\Component\Finder\SplFileInfo;

final readonly class FileNoteMapper
{
    public function map(SplFileInfo $source): Note
    {
        return new Note(
            id: NoteId::create(),
            title: $source->getFilenameWithoutExtension(),
            content: $source->getContents(),
            path: $source->getPath(),
            filename: $source->getFilename(),
            extension: $source->getExtension(),
        );
    }
}
