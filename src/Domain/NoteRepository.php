<?php

declare(strict_types=1);

namespace Kami\Notes\Domain;

interface NoteRepository
{
    public function find(NoteId $identifier): ?Note;
}
