<?php

declare(strict_types=1);

namespace Kami\Notes\Domain;

final readonly class Config
{
    public function __construct(
        public string $contentFolderPath,
        public string $environment = 'dev',
        public string $authEmail = '',
        public string $oauthClientId = '',
        public string $oauthClientSecret = '',
        public string $oauthRedirect = '/sso',
    ) {
    }
}
