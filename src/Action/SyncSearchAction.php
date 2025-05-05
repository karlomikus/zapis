<?php

declare(strict_types=1);

namespace Kami\Notes\Action;

use Loupe\Loupe\Loupe;
use Kami\Notes\Domain\NoteRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class SyncSearchAction
{
    public function __construct(private Loupe $search, private NoteRepository $noteRepository)
    {
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->search->deleteAllDocuments();

        $notes = $this->noteRepository->findAll();
        foreach ($notes as $note) {
            $this->search->addDocument($note->toSearchDocument());
        }

        return $response;
    }
}
