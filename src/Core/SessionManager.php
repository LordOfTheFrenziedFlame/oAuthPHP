<?php

declare(strict_types=1);

namespace OAuth\Core;

use OAuth\Core\SessionInterface;

class SessionManager implements SessionInterface
{

    public function __construct(
        private array $config = [],
        private bool $secure = true,
        private bool $httponly = true,
        private string $sameSite = 'Lax'
    ) {
        if (session_status() === PHP_SESSION_NONE) {
            $defaultParams = [
                'secure' => $this->secure,
                'httponly' => $this->httponly,
                'samesite' => $this->sameSite
            ];
            
            $cookieParams = array_merge($defaultParams, $this->config);
            session_set_cookie_params($cookieParams);
            session_start();
        }
    }

    private function validateKey(string $key): void
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Session key cannot be empty');
        }
    }

    public function set(string $key, $value): void
    {
        $this->validateKey($key);
        $_SESSION[$key] = is_object($value) ? serialize($value) : $value;
    }

    public function get(string $key, $default = null): mixed
    {
        $this->validateKey($key);
        $value = $_SESSION[$key] ?? $default;
        
        if (is_string($value) && $this->isSerialized($value)) {
            return unserialize($value);
        }
        
        return $value;
    }

    public function has(string $key): bool
    {
        $this->validateKey($key);
        return isset($_SESSION[$key]) && $_SESSION[$key] !== null;
    }

    public function remove(string $key): void
    {
        $this->validateKey($key);
        if ($this->has($key)) {
            unset($_SESSION[$key]);
        }
    }

    public function clear(bool $destroy = false): void
    {
        session_unset();
        if ($destroy) {
            session_destroy();
        }
    }

    public function regenerate(bool $deleteOldSession = true): void
    {
        session_regenerate_id($deleteOldSession);
    }

    private function isSerialized(string $value): bool
    {
        return @unserialize($value) !== false;
    }
}