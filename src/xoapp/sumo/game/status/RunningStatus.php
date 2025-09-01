<?php

namespace xoapp\sumo\game\status;

use pocketmine\player\GameMode;
use pocketmine\utils\TextFormat;
use xoapp\sumo\session\Session;

class RunningStatus extends AbstractGameStatus
{
    protected int $time = 360;

    public function update(): void
    {
        $this->time--;

        if ($this->time > 0) {
            return;
        }

        $winner = $this->getWinnerByHits();
        $looser = $this->game->isFirstSession($winner->getName()) ?? $this->game->getSecondSession();

        foreach ($this->game->getWorld()->getPlayers() as $player) {
            $winner->getPlayer()?->setGamemode(GameMode::SPECTATOR());
            $this->game->finish($winner, $looser);

            $player->sendMessage(TextFormat::colorize(
                "&aPlayer &e" . $winner->getName() . "&a Win the game by timeout"
            ));
        }

        if ($this->time === 0) {
            $this->game->setGameStatus(new RestartingStatus($this->game));
        }
    }

    private function getWinnerByHits(): Session
    {
        $firstSession = $this->game->getFirstSession();
        $secondSession = $this->game->getSecondSession();

        $firstHits = $this->game->getFirstSessionHits();
        $secondHits = $this->game->getSecondSessionHits();

        if ($firstHits > $secondHits) {
            return $firstSession;
        }

        return $secondSession;
    }
}