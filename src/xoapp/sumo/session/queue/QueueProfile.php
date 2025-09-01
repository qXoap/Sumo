<?php

namespace xoapp\sumo\session\queue;

use pocketmine\utils\TextFormat;
use xoapp\sumo\factory\GameFactory;
use xoapp\sumo\factory\QueueFactory;
use xoapp\sumo\factory\SessionFactory;
use xoapp\sumo\session\Session;
use xoapp\sumo\utils\StringUtils;

class QueueProfile
{
    private int $creationTime = 0;

    public function __construct(
        private readonly string  $name,
        private readonly ?string $map = null
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

    public function getMap(): ?string
    {
        return $this->map;
    }

    public function update(): void
    {
        $this->creationTime++;
        $this->getSession()?->getPlayer()?->sendTip(TextFormat::colorize(
            "&f Queue Time: &e" . StringUtils::time($this->creationTime)
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
        }

        GameFactory::make($this->getSession(), $match->getSession(), $this->map);
    }

    public function search(): ?QueueProfile
    {
        $queues = QueueFactory::getAll();
        $match = null;

        foreach ($queues as $profile) {
            if (is_null($this->map) && is_null($profile->getMap())) {
                $match = $profile;
                break;
            }

            if ($this->map !== $profile->getMap()) {
                continue;
            }

            $match = $profile;
        }

        return $match;
    }
}