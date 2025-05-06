<?php

declare(strict_types=1);

namespace Kami\Notes;

use DateTimeImmutable;
use Kami\Notes\Domain\Note;
use Kami\Notes\Domain\Config;
use Kami\Notes\Domain\NoteId;
use Symfony\Component\Finder\SplFileInfo;

final readonly class FileNoteMapper
{
    public function __construct(private Config $config)
    {
    }

    public function map(SplFileInfo $source): Note
    {
        // Path should be relative to the content folder
        $path = rtrim(str_replace($this->config->contentFolderPath, '', $source->getPath()), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $source->getFilename();
        $path = ltrim($path, DIRECTORY_SEPARATOR);

        return new Note(
            id: new NoteId($path), // For now we use the path as the ID
            title: $source->getFilenameWithoutExtension(),
            content: $source->getContents(),
            path: $path,
            extension: $source->getExtension(),
            lastModified: $source->getMTime() !== false ? new DateTimeImmutable('@' . $source->getMTime()) : new DateTimeImmutable(),
        );
    }
}
