<?php

namespace xoapp\sumo;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use xoapp\sumo\factory\QueueFactory;
use xoapp\sumo\factory\SessionFactory;

class EventHandler implements Listener
{
    public function onPlayerJoin(PlayerJoinEvent $event): void
    {
        SessionFactory::make($event->getPlayer());
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $session = SessionFactory::get($player->getName());

        if (!is_null($session->getQueue())) {
            QueueFactory::remove($player->getName());
            $session->setQueue(null);
        }

        if (!is_null($game = $session->getCurrentGame())) {
            $winner = $game->isFirstSession($player->getName()) ? $game->getSecondSession() : $game->getFirstSession();
            $game->finish($winner, $session);
        }

        if (!is_null($session->getMakingProcess())) {
            $session->clearInventory();
            $session->setMakingProcess(null);
        }

        SessionFactory::remove($player->getName());
    }

    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event): void
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

    public function onItemPickup(EntityItemPickupEvent $event): void
    {
        $player = $event->getEntity();

        if (!$player instanceof Player) {
            return;
        }

        if (is_null($session = SessionFactory::get($player->getName()))) {
            return;
        }

        if (!is_null($session->getCurrentGame())) {
            $event->cancel();
        }
    }

    public function onItemDrop(PlayerDropItemEvent $event): void
    {
        $player = $event->getPlayer();

        if (is_null($session = SessionFactory::get($player->getName()))) {
            return;
        }

        if (!is_null($session->getCurrentGame())) {
            $event->cancel();
        }
    }

    public function onEntityDamage(EntityDamageEvent $event): void
    {
        $player = $event->getEntity();

        if (!$player instanceof Player) {
            return;
        }

        if (is_null($session = SessionFactory::get($player->getName()))) {
            return;
        }

        $cause = $event->getCause();

        if (!is_null($session->getCurrentGame()) && $cause !== EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
            $event->cancel();
        }
    }
}