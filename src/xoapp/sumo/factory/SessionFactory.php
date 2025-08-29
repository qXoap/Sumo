<?php

namespace xoapp\sumo\factory;

use pocketmine\player\Player;
use xoapp\sumo\session\Session;

class SessionFactory
{
    /** @var Session[] */
    private static array $sessions = [];

    public static function make(Player $player): void
    {
        self::$sessions[$player->getName()] = new Session($player->getName());
    }

    public static function get(string $name): ?Session
    {
        return self::$sessions[$name] ?? null;
    }

    public static function remove(string $name): void
    {
        unset(self::$sessions[$name]);
    }

    public static function getAll(): array
    {
        return self::$sessions;
    }

    public static function updateAll(): void
    {
        foreach (self::$sessions as $session) {
            $session->getQueue()?->update();
            $session->getCurrentGame()?->update();
        }
    }
}