<?php

declare(strict_types=1);

namespace Kami\Notes\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final readonly class AuthAction
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 300; // 5 minutes in seconds

    public function __construct(private LoggerInterface $logger) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $clientIp = $this->getClientIp();

        // Check if the IP is currently locked out
        if ($this->isLockedOut($clientIp)) {
            $this->logger->warning('Rate limited login attempt', [
                'ip' => $clientIp,
            ]);

            return $response->withStatus(429)
                ->withHeader('Content-Type', 'text/html')
                ->withBody((function () use ($response) {
                    $stream = $response->getBody();
                    $stream->write('<h1>Too many failed login attempts</h1><p>Please try again later.</p>');
                    return $stream;
                })());
        }

        /**
         * @var array{username?: string|null, password?: string|null} $req
         */
        $req = (array) $request->getParsedBody();

        if (empty($req['username']) || empty($req['password'])) {
            $this->logger->error('Empty credentials provided');
            $this->recordFailedAttempt($clientIp);

            return $response->withStatus(401);
        }

        $u = $_SERVER['USERNAME'] ?? null;
        $p = $_SERVER['PASSWORD'] ?? null;

        $username = $req['username'];
        $password = $req['password'];

        if ($username === $u && $password === $p) {
            // Reset failed attempts on successful login
            $this->resetFailedAttempts($clientIp);
            $_SESSION['username'] = $username;

            $this->logger->info('User logged in', [
                'username' => $username,
            ]);

            return $response->withHeader('Location', '/')->withStatus(302);
        }

        // Record failed login attempt
        $this->recordFailedAttempt($clientIp);

        $this->logger->error('Invalid credentials', [
            'username' => $username,
            'ip' => $clientIp,
        ]);

        return $response->withHeader('Location', '/auth')->withStatus(302);
    }

    /**
     * Get client IP address.
     */
    private function getClientIp(): string
    {
        // Try to get IP from various headers (for proxies)
        $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];

        foreach ($headers as $header) {
            /** @var string|null */
            $ip = $_SERVER[$header] ?? null;
            if ($ip) {
                // If X-Forwarded-For contains multiple IPs, take the first one
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                return $ip;
            }
        }

        return 'unknown';
    }

    /**
     * Check if the IP is currently locked out.
     */
    private function isLockedOut(string $ip): bool
    {
        $session = $_SESSION;
        if (!isset($session['login_attempts'][$ip])) {
            return false;
        }

        $attempts = $session['login_attempts'][$ip];

        if ($attempts['count'] < self::MAX_ATTEMPTS) {
            return false;
        }

        if (time() - $attempts['last_attempt'] > self::LOCKOUT_TIME) {
            $this->resetFailedAttempts($ip);

            return false;
        }

        return true;
    }

    /**
     * Record a failed login attempt.
     */
    private function recordFailedAttempt(string $ip): void
    {
        if (!isset($_SESSION['login_attempts'][$ip])) {
            $_SESSION['login_attempts'][$ip] = [
                'count' => 0,
                'last_attempt' => 0,
            ];
        }

        $_SESSION['login_attempts'][$ip]['count']++;
        $_SESSION['login_attempts'][$ip]['last_attempt'] = time();
    }

    /**
     * Reset failed attempts for an IP.
     */
    private function resetFailedAttempts(string $ip): void
    {
        if (isset($_SESSION['login_attempts'][$ip])) {
            unset($_SESSION['login_attempts'][$ip]);
        }
    }
}
