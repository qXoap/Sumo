<?php

namespace xoapp\sumo\map;

use pocketmine\world\Position;
use xoapp\sumo\Loader;
use xoapp\sumo\object\Map;
use pocketmine\utils\Config;

class MapFactory
{
    /** @var Map[] */
    private static array $maps = [];

    private static Config $config;

    public static function load(): void
    {
        self::$config = new Config(Loader::getInstance()->getDataFolder() . 'storage/maps.json', Config::JSON);

        foreach (self::$config->getAll() as $name => $data) {
            $d_data = Map::deserializeData($data);
            self::create($name, $d_data['firstPosition'], $d_data['secondPosition']);
        }
    }

    public static function create(string $name, ?Position $firstPosition = null, ?Position $secondPosition = null, bool $copy = false): void
    {
        self::$maps[$name] = new Map($name, $firstPosition, $secondPosition, $copy);
    }

    public static function remove(string $name): void
    {
        if (is_null(self::get($name))) {
            return;
        }

        unset(self::$maps[$name]);
    }

    public static function get(string $name): ?Map
    {
        return self::$maps[$name] ?? null;
    }

    public static function getRandom(): ?Map
    {
        return self::$maps[array_rand(self::$maps)];
    }

    public static function getAll(): array
    {
        return self::$maps;
    }

    public static function save(): void
    {
        $maps = [];

        foreach (self::getAll() as $name => $world) {
            $maps[$name] = $world->serializeData();
        }

        self::$config->setAll($maps);
        self::$config->save();
    }
}