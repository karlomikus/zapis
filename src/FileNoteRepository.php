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
    private Finder $finder;

    public function __construct(private FileNoteMapper $mapper, private Config $config, private LoggerInterface $logger)
    {
        $finder = new Finder();
        $finder->ignoreUnreadableDirs()->in($config->contentFolderPath)->name(['*.md', '*.txt', '*.markdown']);

        $this->finder = $finder;
    }

    public function findAll(): array
    {
        $files = $this->finder->sortByModifiedTime();

        if (!$files->hasResults()) {
            return [];
        }

        $results = [];
        foreach ($files as $file) {
            $results[] = $this->mapper->map($file);
        }

        return $results;
    }

    public function find(NoteId $identifier): ?Note
    {
        $files = $this->finder->path($identifier->value);

        if (!$files->hasResults()) {
            return null;
        }

        foreach ($files as $file) {
            if ($file->getPathname() === $this->config->contentFolderPath . DIRECTORY_SEPARATOR . $identifier->value) {
                return $this->mapper->map($file);
            }
        }

        return null;
    }

    public function save(Note $note): bool
    {
        $notePath = $this->config->contentFolderPath . DIRECTORY_SEPARATOR . ltrim($note->path, DIRECTORY_SEPARATOR);

        if (is_dir($notePath)) {
            $this->logger->error('Cannot save file, path is a directory', [
                'path' => $notePath,
            ]);

            return false;
        }

        $dirname = dirname($notePath);
        if (!file_exists($dirname)) {
            if (!mkdir(directory: $dirname, recursive: true)) {
                $this->logger->error('Cannot create directory', [
                    'path' => $dirname,
                ]);

                return false;
            }
        }

        $file = file_put_contents($notePath, $note->content, LOCK_EX);
        if ($file === false) {
            return false;
        }

        $this->logger->info('File saved', [
            'path' => $note->path,
        ]);

        return true;
    }
}
