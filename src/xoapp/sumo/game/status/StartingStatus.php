<?php

namespace xoapp\sumo\game\status;

use pocketmine\utils\TextFormat;
use xoapp\sumo\session\Session;

class StartingStatus extends AbstractGameStatus
{
    protected int $time = 10;

    public function update(): void
    {
        $this->time--;
        foreach ([$this->game->getFirstSession(), $this->game->getSecondSession()] as $session) {
            /** @var Session $session */

            if ($this->time > 0) {
                $session->getPlayer()?->sendTitle(TextFormat::colorize("&aStarting in &e" . $this->time . "s"));
                $session->makeSound('random.orb');
                return;
            }

            $session->makeSound('block.anvil.break');
            $session->getPlayer()?->sendTitle(TextFormat::colorize(
                "&aBattle started!"
            ));
        }

        if ($this->time === 0) {
            $this->game->setGameStatus(new RunningStatus($this->game));
        }
    }
}