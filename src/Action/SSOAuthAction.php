<?php

declare(strict_types=1);

namespace Kami\Notes\Action;

use Throwable;
use Psr\Log\LoggerInterface;
use Kami\Notes\Domain\Config;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\Github;
use Psr\Http\Message\ServerRequestInterface;

final readonly class SSOAuthAction
{
    public function __construct(private LoggerInterface $logger, private Config $config, private Github $provider) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $q = $request->getQueryParams();

        if (!isset($q['code'])) {
            $authUrl = $this->provider->getAuthorizationUrl(['user:email']);
            $_SESSION['oauth2state'] = $this->provider->getState();

            $this->logger->info('Redirecting to SSO for authorization');

            return $response->withHeader('Location', $authUrl)->withStatus(302);
        } elseif (empty($q['state']) || ($q['state'] !== $_SESSION['oauth2state'])) {
            session_unset();
            session_destroy();
            session_regenerate_id(true);

            $this->logger->error('Invalid state while trying to authenticate');

            return $response->withHeader('Location', '/auth')->withStatus(302);
        } else {
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $q['code']
            ]);

            try {
                /** @var \League\OAuth2\Client\Provider\GithubResourceOwner */
                $user = $this->provider->getResourceOwner($token);
                $email = $user->getEmail();

                if ($email === $this->config->authEmail) {
                    $_SESSION['email'] = $email;
                    $_SESSION['oauth_access_token'] = $token->getToken();
                    $_SESSION['oauth_refresh_token'] = $token->getRefreshToken();

                    $this->logger->info('User authenticated successfully', [
                        'email' => $email,
                    ]);

                    return $response->withHeader('Location', '/')->withStatus(302);
                }
            } catch (Throwable $e) {
                $this->logger->error('Error retrieving user data', [
                    'exception' => $e,
                ]);

                return $response->withHeader('Location', '/auth')->withStatus(302);
            }
        }

        return $response->withHeader('Location', '/')->withStatus(302);
    }
}
