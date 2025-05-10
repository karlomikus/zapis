<?php

declare(strict_types=1);

namespace Kami\Notes\Domain;

interface NoteRepository
{
    /**
     * @return Note[]
     */
    public function findAll(): array;

    public function find(NoteId $identifier): ?Note;

    public function save(Note $note): bool;

    public function delete(NoteId $identifier): void;
}
