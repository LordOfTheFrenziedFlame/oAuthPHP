<?php

declare(strict_types=1);

namespace OAuth\Core;


interface SessionInterface
{
    /**
     * Устанавливает значение в сессию
     */
    public function set(string $key, $value): void;

    /**
     * Получает значение из сессии
     */
    public function get(string $key, $default = null);

    /**
     * Проверяет, существует ли значение в сессии
     */
    public function has(string $key): bool;

    /**
     * Удаляет значение из сессии
     */
    public function remove(string $key): void;

    /**
     * Очищает сессию
     */
    public function clear(): void;

    /**
     * Обновляет идентификатор сессии
     */
    public function regenerate(): void;
}