<?php

namespace xoapp\sumo;

use CortexPE\Commando\PacketHooker;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\snooze\SleeperHandler;
use pocketmine\utils\SingletonTrait;
use Symfony\Component\Filesystem\Path;
use xoapp\sumo\commands\SumoCommand;
use xoapp\sumo\factory\GameFactory;
use xoapp\sumo\factory\MapFactory;
use xoapp\sumo\factory\QueueFactory;
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

        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }

        $this->getServer()->getPluginManager()->registerEvents(new EventHandler(), $this);
        $this->getServer()->getCommandMap()->register("sumo", new SumoCommand($this));

        MapFactory::load();

        TaskUtils::repeatingTask(fn () => GameFactory::update());
        TaskUtils::repeatingTask(fn () => QueueFactory::update());
    }

    protected function onDisable(): void
    {
        MapFactory::save();
    }

    public static function sleeperHandler(): SleeperHandler
    {
        return Server::getInstance()->getTickSleeper();
    }
}