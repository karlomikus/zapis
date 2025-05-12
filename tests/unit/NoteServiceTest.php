<?php

declare(strict_types=1);

namespace Kami\Notes\Tests\Unit;

use DateTimeImmutable;
use Kami\Notes\Domain\Note;
use Kami\Notes\NoteService;
use Kami\Notes\Domain\NoteId;
use Loupe\Loupe\LoupeFactory;
use Loupe\Loupe\Configuration;
use PHPUnit\Framework\TestCase;
use Kami\Notes\Domain\NoteRepository;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NoteService::class)]
final class NoteServiceTest extends TestCase
{
    public function testGetNoteViewModel(): void
    {
        $noteRepositoryMock = $this->createMock(NoteRepository::class);
        $noteRepositoryMock->method('find')->willReturn($this->createMockNote());
        $loupe = (new LoupeFactory())->createInMemory(Configuration::create());

        $noteService = new NoteService($noteRepositoryMock, $loupe);

        $viewModel = $noteService->getNoteViewModel('test-note.md');

        $this->assertSame('test-note.md', $viewModel->id);
        $this->assertSame('Test Note', $viewModel->title);
        $this->assertSame('This is a test note.', $viewModel->markdown);
        $this->assertSame('path/to/test-note.md', $viewModel->path);
        $this->assertSame('2023-10-01 12:00:00', (string) $viewModel->lastModified);
    }

    public function testGetNoteViewModelReturnsDefault(): void
    {
        $noteRepositoryMock = $this->createMock(NoteRepository::class);
        $noteRepositoryMock->method('find')->willReturn(null);
        $loupe = (new LoupeFactory())->createInMemory(Configuration::create());

        $noteService = new NoteService($noteRepositoryMock, $loupe);

        $viewModel = $noteService->getNoteViewModel('index.md');

        $this->assertSame('index.md', $viewModel->id);
        $this->assertSame('index.md', $viewModel->title);
        $this->assertSame('Start writing your note here', $viewModel->markdown);
        $this->assertSame('index.md', $viewModel->path);
    }

    public function testPutNoteCreatesNewNote(): void
    {
        $noteRepositoryMock = $this->createMock(NoteRepository::class);
        $noteRepositoryMock->method('find')->willReturn(null);
        $noteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Note $note) {
                return $note->id->value === 'test-note.md' && $note->content === 'This is a test note.';
            }));
        $loupe = (new LoupeFactory())->createInMemory(Configuration::create());

        $noteService = new NoteService($noteRepositoryMock, $loupe);

        $noteService->putNote('test-note.md', 'This is a test note.');
        $this->assertSame(1, $loupe->countDocuments());
    }

    public function testPutNoteUpdatesExistingNote(): void
    {
        $noteRepositoryMock = $this->createMock(NoteRepository::class);
        $noteRepositoryMock->method('find')->willReturn($this->createMockNote());
        $noteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Note $note) {
                return $note->id->value === 'test-note.md' && $note->content === 'This is a test note.';
            }));
        $loupe = (new LoupeFactory())->createInMemory(Configuration::create());

        $noteService = new NoteService($noteRepositoryMock, $loupe);

        $noteService->putNote('test-note.md', 'This is a test note.');
        $this->assertSame(1, $loupe->countDocuments());
    }

    public function testDeleteNote(): void
    {
        $noteRepositoryMock = $this->createMock(NoteRepository::class);
        $noteRepositoryMock->expects($this->once())->method('delete');
        $loupe = (new LoupeFactory())->createInMemory(Configuration::create());

        $noteService = new NoteService($noteRepositoryMock, $loupe);

        $noteService->deleteNote('test-note.md');
        $this->assertSame(0, $loupe->countDocuments());
    }

    private function createMockNote(): Note
    {
        return new Note(
            id: new NoteId('test-note.md'),
            title: 'Test Note',
            content: 'This is a test note.',
            path: 'path/to/test-note.md',
            extension: 'md',
            lastModified: new DateTimeImmutable('2023-10-01 12:00:00'),
        );
    }
}
