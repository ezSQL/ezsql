<?php

declare(strict_types=1);

namespace ezsql;

use ezsql\ezQueryInterface;

/**
 * Used internally for needed **global** variables.
 *
 * @internal
 */
final class Db
{
    /**
     * @var ezQueryInterface[]
     */
    protected static $storage;

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set(string $key, $value): void
    {
        self::$storage[$key] = $value;
    }

    /**
     * @param string $key
     * @return ezQueryInterface
     */
    public static function get(string $key): ?ezQueryInterface
    {
        return self::$storage[$key] ?? null;
    }

    /**
     * @param string $tag
     * @return boolean
     */
    public static function has(string $tag): bool
    {
        return isset(self::$storage[$tag]);
    }

    /**
     * @param string $tag
     * @return void
     */
    public static function clear(string $tag): void
    {
        if (self::has($tag))
            unset(self::$storage[$tag]);
    }

    /**
     * @return void
     */
    public static function reset(): void
    {
        self::$storage = null;
    }
}
