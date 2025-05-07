<?php

declare(strict_types=1);

namespace Kami\Notes\Action;

use Loupe\Loupe\Loupe;
use Kami\Notes\SearchRequest;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

final readonly class SearchAction
{
    public function __construct(private Loupe $search, private LoggerInterface $logger)
    {
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $searchbody = SearchRequest::fromRequest($request);

        if ($searchbody === null) {
            $this->logger->error('Invalid search request', [
                'request' => (string) $request->getBody(),
            ]);

            return $response->withStatus(400);
        }

        $searchResults = $this->search->search($searchbody->toSearchParameters());
        $bodyContent = json_encode($searchResults->getHits(), JSON_PRETTY_PRINT);
        if ($bodyContent === false) {
            $this->logger->error('Failed to encode search results', [
                'error' => json_last_error_msg(),
                'searchResults' => $searchResults,
            ]);

            return $response->withStatus(500);
        }

        $response->getBody()->write($bodyContent);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->withStatus(200);

        return $response;
    }
}
