<?php

namespace xoapp\sumo\factory;

use xoapp\sumo\session\queue\QueueProfile;

class QueueFactory
{
    /** @var QueueProfile[] */
    private static array $profiles = [];

    public static function make(QueueProfile $profile): void
    {
        self::$profiles[$profile->getName()] = $profile;
    }

    public static function get(string $name): ?QueueProfile
    {
        return self::$profiles[$name] ?? null;
    }

    public static function remove(string $name): void
    {
        unset(self::$profiles[$name]);
    }

    public static function getAll(): array
    {
        return self::$profiles;
    }
}