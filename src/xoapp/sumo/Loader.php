<?php

namespace xoapp\sumo;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\snooze\SleeperHandler;
use pocketmine\utils\SingletonTrait;
use Symfony\Component\Filesystem\Path;
use xoapp\sumo\factory\SessionFactory;
use xoapp\sumo\utils\TaskUtils;

class Loader extends PluginBase
{
    use SingletonTrait {
        setInstance as private;
        reset as private;
    }

    protected function onEnable(): void
    {
        self::setInstance($this);

        TaskUtils::repeatingTask(fn () => SessionFactory::updateAll());

        $worldPath = Path::join($this->getDataFolder(), "maps");
        if (!is_dir($worldPath)) {
            mkdir($worldPath);
        }

        $storagePath = Path::join($this->getDataFolder(), "storage");
        if (!is_dir($storagePath)) {
            mkdir($storagePath);
        }

    }

    protected function onDisable(): void
    {

    }

    public static function sleeperHandler(): SleeperHandler
    {
        return Server::getInstance()->getTickSleeper();
    }
}