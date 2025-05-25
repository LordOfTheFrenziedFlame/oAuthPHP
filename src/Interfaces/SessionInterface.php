<?php

declare(strict_types=1);

namespace OAuth\Interfaces;

interface SessionInterface
{
    public function set(string $key, $value): void;
    public function get(string $key, $default = null);
    public function has(string $key): bool;
    public function remove(string $key): void;
    public function clear(): void;
} 