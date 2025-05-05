<?php

declare(strict_types=1);

namespace Kami\Notes;

use Psr\Http\Message\RequestInterface;

final readonly class SearchRequest
{
    public function __construct(
        public string $query,
    ) {
    }

    public static function fromRequest(RequestInterface $request): ?self
    {
        $payload = json_decode((string) $request->getBody()->getContents(), true);
        if ($payload === null) {
            return null;
        }

        return new self(
            query: $payload['query'] ?? '',
        );
    }
}
