<?php

namespace xoapp\sumo\factory;

use pocketmine\Server;
use pocketmine\world\World;
use Symfony\Component\Filesystem\Path;
use xoapp\sumo\game\Game;
use xoapp\sumo\map\MapFactory;
use xoapp\sumo\session\Session;

class GameFactory
{
    /** @var Game[] */
    private static array $games = [];

    public static function make(Session $first, Session $second, ?string $map = null): void
    {
        $randomID = self::randomID();
        $mapData = is_null($map) ? MapFactory::getRandom() : MapFactory::get($map);

        if ($mapData === null) {
            return;
        }

        $mapData->copyWorld(
            "sumo-" . $randomID,
            Path::join(Server::getInstance()->getDataPath(), "worlds"),
            function (World $world) use ($first, $second, $mapData, $randomID)
            {
                $game = new Game($randomID, $mapData->getName(), $first, $second, $world);
                self::$games[$randomID] = $game;
            }
        );
    }

    public static function get(string $id): ?Game
    {
        return self::$games[$id] ?? null;
    }

    public static function remove(string $id): void
    {
        unset(self::$games[$id]);
    }

    public static function getAll(): array
    {
        return self::$games;
    }

    private static function randomID(): string
    {
        do {
            $randomID = bin2hex(random_bytes(2));
        } while (array_key_exists($randomID, self::$games));

        return $randomID;
    }
}