<?php

namespace xoapp\sumo;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\Player;
use xoapp\sumo\factory\SessionFactory;

class EventHandler implements Listener
{
    public function onEntityDamage(EntityDamageByEntityEvent $event): void
    {
        $player = $event->getDamager();
        if (!$player instanceof Player) {
            return;
        }

        if (is_null($session = SessionFactory::get($player->getName()))) {
            return;
        }

        if (!is_null($session->getCurrentGame())) {
            $session->getCurrentGame()->handleDamage($event);
        }
    }

    public function onPlayerMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();

        if (is_null($session = SessionFactory::get($player->getName()))) {
            return;
        }

        if (!is_null($session->getCurrentGame())) {
            $session->getCurrentGame()->handleMove($event);
        }
    }

    public function onPlayerInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();

        if (is_null($session = SessionFactory::get($player->getName()))) {
            return;
        }

        if (!is_null($session->getMakingProcess())) {
            $session->getMakingProcess()->handleInteract($event);
        }
    }
}