<?php

declare(strict_types=1);

namespace Kami\Notes\Action;

use Twig\Environment;
use Kami\Notes\Domain\NoteId;
use Kami\Notes\FileNoteRepository;
use Kami\Notes\NoteViewModel;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use League\CommonMark\ConverterInterface;

final readonly class ShowNoteAction
{
    public function __construct(private Environment $twig, private FileNoteRepository $repository, private ConverterInterface $converter)
    {
    }

    /**
     * @param array<string, string> $args
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $noteId = $args['file'] ?? 'index';
        $note = $this->repository->find(new NoteId($noteId));
        if ($note === null) {
            return $response->withStatus(404);
        }

        $dto = new NoteViewModel(
            title: $note->title,
            markdown: $note->content,
            html: (string) $this->converter->convert($note->content),
            path: $note->path,
        );

        $body = $this->twig->render('note.html.twig', [
            'note' => $dto,
        ]);

        $response->getBody()->write($body);

        return $response;
    }
}
