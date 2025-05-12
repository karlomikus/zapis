<?php

declare(strict_types=1);

namespace Kami\Notes\Tests\Integration;

use SplFileInfo;
use Kami\Notes\Domain\Note;
use Kami\Notes\Domain\Config;
use Kami\Notes\Domain\NoteId;
use Kami\Notes\FileNoteMapper;
use RecursiveIteratorIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use Kami\Notes\FileNoteRepository;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileNoteRepository::class)]
class FileNoteRepositoryTest extends TestCase
{
    const TEST_DIR = __DIR__ . '/../fixtures';

    protected function setUp(): void
    {
        parent::setUp();

        $files = [];
        $directory = new RecursiveDirectoryIterator(self::TEST_DIR);
        $iterator = new RecursiveIteratorIterator($directory);

        foreach ($iterator as $file) {
            assert($file instanceof SplFileInfo);
            if ($file->isFile() && $file->getExtension() === 'md') {
                $files[] = $file->getPathname();
            }
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $this->createNoteFixtures(self::TEST_DIR);
    }

    public function testFindAll(): void
    {
        $repo = $this->getRepository(self::TEST_DIR);
        $result = $repo->findAll();

        $this->assertCount(2, $result);
    }

    public function testFind(): void
    {
        $repo = $this->getRepository(self::TEST_DIR);
        $result = $repo->find(new NoteId('test-note.md'));

        $this->assertNotNull($result);
        $this->assertSame('test-note.md', $result->id->value);
        $this->assertSame('test-note', $result->title);
        $this->assertSame('This is a test note.', $result->content);
        $this->assertSame('test-note.md', $result->path);
        $this->assertSame('md', $result->extension);
    }

    public function testFindReturnsNull(): void
    {
        $repo = $this->getRepository(self::TEST_DIR);
        $result = $repo->find(new NoteId('non-existing.md'));

        $this->assertNull($result);
    }

    public function testSaveNote(): void
    {
        $repo = $this->getRepository(self::TEST_DIR);
        $note = new Note(
            new NoteId('new-note.md'),
            'new-note',
            'This is a new note.',
            'new-note.md',
            'md'
        );

        $saved = $repo->save($note);
        $this->assertTrue($saved);

        $savedNote = $repo->find(new NoteId('new-note.md'));
        $this->assertNotNull($savedNote);
        $this->assertSame('new-note.md', (string) $savedNote->id);
        $this->assertSame('new-note', $savedNote->title);
        $this->assertSame('This is a new note.', $savedNote->content);
    }

    public function testSaveNoteSubdir(): void
    {
        $repo = $this->getRepository(self::TEST_DIR);
        $note = new Note(
            new NoteId('sub/path/total.md'),
            'new-note',
            'This is a new note.',
            'sub/path/total.md',
            'md'
        );

        $saved = $repo->save($note);
        $this->assertTrue($saved);

        $savedNote = $repo->find(new NoteId('sub/path/total.md'));
        $this->assertNotNull($savedNote);
        $this->assertSame('sub/path/total.md', (string) $savedNote->id);
        $this->assertSame('total', $savedNote->title);
        $this->assertSame('This is a new note.', $savedNote->content);
    }

    public function testDeleteNote(): void
    {
        $repo = $this->getRepository(self::TEST_DIR);
        $note = new Note(
            new NoteId('to-delete.md'),
            'to-delete',
            'This is a new note.',
            'to-delete.md',
            'md'
        );

        $saved = $repo->save($note);
        $this->assertTrue($saved);

        $repo->delete(new NoteId('to-delete.md'));
        $this->assertNull($repo->find(new NoteId('to-delete.md')));
    }

    private function getRepository(string $path): FileNoteRepository
    {
        $config = new Config($path);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);

        return new FileNoteRepository(
            new FileNoteMapper($config),
            $config,
            $logger
        );
    }

    private function createNoteFixtures(string $path): void
    {
        $dataset = [
            'test-note.md' => [
                'title' => 'Test Note',
                'content' => 'This is a test note.',
            ],
            'Empty note.md' => [
                'title' => 'Empty Note',
                'content' => '',
            ],
        ];

        foreach ($dataset as $filename => $data) {
            $filePath = $path . '/' . $filename;
            file_put_contents($filePath, $data['content']);
        }
    }
}
