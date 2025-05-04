<?php

declare(strict_types=1);

namespace Kami\Notes;

use Kami\Notes\Domain\Note;
use Kami\Notes\Domain\Config;
use Kami\Notes\Domain\NoteId;
use Symfony\Component\Finder\Finder;
use Kami\Notes\Domain\NoteRepository;
use Psr\Log\LoggerInterface;

final readonly class FileNoteRepository implements NoteRepository
{
    public function __construct(private Finder $finder, private FileNoteMapper $mapper, private Config $config, private LoggerInterface $logger)
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

    public function save(Note $note): bool
    {
        $file = file_put_contents($this->config->contentFolderPath . DIRECTORY_SEPARATOR . $note->path, $note->content, LOCK_EX);
        if ($file === false) {
            return false;
        }

        $this->logger->info('File saved', [
            'path' => $note->path,
        ]);

        return true;
    }
}
