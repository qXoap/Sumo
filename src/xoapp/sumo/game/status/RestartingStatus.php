<?php

namespace xoapp\sumo\game\status;

use pocketmine\Server;
use pocketmine\utils\TextFormat;
use xoapp\sumo\session\Session;

class RestartingStatus extends AbstractGameStatus
{
    protected int $time = 10;

    public function update(): void
    {
        $this->time--;
        foreach ([$this->game->getFirstSession(), $this->game->getSecondSession()] as $session) {
            /** @var Session $session */

            if ($this->time > 0) {
                $session->getPlayer()?->sendTitle(TextFormat::colorize("&aRestarting in &e" . $this->time . "s"));
                $session->makeSound('note.bass');
                return;
            }

            $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
            $session->getPlayer()?->teleport($world->getSafeSpawn());
            $session->makeSound('block.anvil.break');
        }

        $this->game->destroy();
    }
}