<?php

namespace xoapp\sumo\session;

use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use xoapp\sumo\game\Game;
use xoapp\sumo\session\process\MakeGameProcess;
use xoapp\sumo\session\queue\QueueProfile;

class Session
{
    public function __construct(
        private readonly string $name,
        private ?QueueProfile $queue = null,
        private ?Game $currentGame = null,
        private ?MakeGameProcess $makeGameProcess = null
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPlayer(): ?Player
    {
        return Server::getInstance()->getPlayerExact($this->name);
    }

    public function getQueue(): ?QueueProfile
    {
        return $this->queue;
    }

    public function getCurrentGame(): ?Game
    {
        return $this->currentGame;
    }

    public function getMakingProcess(): ?MakeGameProcess
    {
        return $this->makeGameProcess;
    }

    public function setQueue(?QueueProfile $queue): void
    {
        $this->queue = $queue;
    }

    public function setCurrentGame(?Game $currentGame): void
    {
        $this->currentGame = $currentGame;
    }

    public function setMakingProcess(?MakeGameProcess $makeGameProcess): void
    {
        $this->makeGameProcess = $makeGameProcess;
    }

    public function clearInventory(): void
    {
        $this->getPlayer()?->getInventory()->clearAll();
        $this->getPlayer()?->getArmorInventory()->clearAll();
        $this->getPlayer()?->getOffHandInventory()->clearAll();
    }

    public function makeSound(string $name): void
    {
        if (($player = $this->getPlayer()) === null) {
            return;
        }

        $packet = new PlaySoundPacket();

        $packet->x = $player->getPosition()->getX();
        $packet->y = $player->getPosition()->getY();
        $packet->z = $player->getPosition()->getZ();

        $packet->volume = 1;
        $packet->pitch = 1;

        $packet->soundName = $name;

        $player->getNetworkSession()->sendDataPacket($packet);
    }
}