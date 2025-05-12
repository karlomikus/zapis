<?php

declare(strict_types=1);

namespace Kami\Notes\Tests\Unit;

use InvalidArgumentException;
use Kami\Notes\Domain\NoteId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoteId::class)]
final class NoteIdTest extends TestCase
{
    public function testNoteIdCreation(): void
    {
        $noteId = new NoteId('test-note-id.md');
        $this->assertSame('test-note-id.md', $noteId->value);
    }

    public function testNoteIdString(): void
    {
        $noteId = new NoteId('markdown test.md');
        $this->assertSame('markdown test.md', (string) $noteId);
    }

    public function testNoteIdInvalidCreation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new NoteId('');
    }

    public function testNoteIdInvalidCreationWrongExtension(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new NoteId('test.xml');
    }

    public function testNoteIdInvalidCreationSlashes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new NoteId('tes\\t.md');
    }
}
