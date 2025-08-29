<?php

namespace xoapp\sumo\session\profile;

use pocketmine\utils\TextFormat;
use xoapp\sumo\factory\QueueFactory;
use xoapp\sumo\factory\SessionFactory;
use xoapp\sumo\session\Session;
use xoapp\sumo\utils\StringUtils;

class QueueProfile
{
    private int $creationTime = 0;

    public function __construct(
        private readonly string $name,
        private readonly string $gameMode = "solo"
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSession(): ?Session
    {
        return SessionFactory::get($this->name);
    }

    public function getGameMode(): string
    {
        return $this->gameMode;
    }

    public function update(): void
    {
        $this->creationTime++;
        $this->getSession()?->getPlayer()?->sendTip(TextFormat::colorize(
            "&fMode: &e" . $this->gameMode . "&7|&f Queue Time: &e" . StringUtils::time($this->creationTime)
        ));

        if (($match = $this->search()) === null) {
            return;
        }

        foreach ([$this->getSession(), $match->getSession()] as $session) {
            /** @var Session $session */

            $session->getPlayer()?->sendTip(TextFormat::colorize("&aSumo Math Find!"));
            $session->makeSound('random.levelup');

            $session->setQueue(null);
            QueueFactory::remove($this->name);

            // TODO: Send To Sumo Arena
        }
    }

    public function search(): ?QueueProfile
    {
        $profiles = QueueFactory::getAll();
        $match = null;

        foreach ($profiles as $profile) {
            if ($profile->getGameMode() == $this->gameMode) {
                $match = $profile;
            }
        }

        return $match;
    }
}