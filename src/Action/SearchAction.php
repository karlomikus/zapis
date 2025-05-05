<?php

declare(strict_types=1);

namespace Kami\Notes\Action;

use Loupe\Loupe\Loupe;
use Kami\Notes\SearchRequest;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class SearchAction
{
    public function __construct(private Loupe $search)
    {
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $searchbody = SearchRequest::fromRequest($request);

        if ($searchbody === null) {
            return $response->withStatus(400);
        }

        $searchResults = $this->search->search($searchbody->toSearchParameters());

        $response->getBody()->write(json_encode($searchResults->getHits(), JSON_PRETTY_PRINT));
        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->withStatus(200);

        return $response;
    }
}
