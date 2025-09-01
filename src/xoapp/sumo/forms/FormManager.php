<?php

namespace xoapp\sumo\forms;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use xoapp\sumo\factory\GameFactory;
use xoapp\sumo\factory\QueueFactory;
use xoapp\sumo\factory\SessionFactory;
use xoapp\sumo\factory\MapFactory;
use xoapp\sumo\session\queue\QueueProfile;

class FormManager
{
    public static function getMapList(Player $player): void
    {
        $buttons = [];
        $maps = MapFactory::getAll();

        $buttons[] = new MenuOption(TextFormat::colorize(
            "&l&5RANDOM MAP&r\nInQueue: &c" . count(QueueFactory::getQueuesByMap())
        ));

        foreach ($maps as $map) {
            $inQueue = count(QueueFactory::getQueuesByMap($map->getName()));
            $inGame = count(GameFactory::getGamesByMap($map->getName()));

            $buttons[] = new MenuOption(TextFormat::colorize(
                "Map: " . $map->getName() . "\nIn Queue: &c" . $inQueue . "&r InGame: &c" . $inGame
            ));
        }

        $form = new MenuForm(
            "Sumo Maps", "", $buttons,
            function (Player $player, int $index) use ($maps): void
            {
                $selectedMap = $index === 0 ? null : ($maps[$index - 1]?->getName() ?? null);

                if ($selectedMap !== null && is_null(MapFactory::get($selectedMap))) {
                    $player->sendMessage(TextFormat::colorize("&cError while selecting map"));
                    return;
                }

                if (!is_null(QueueFactory::get($player->getName()))) {
                    $player->sendMessage(TextFormat::colorize("&cYou are already in queue!"));
                    return;
                }

                $queueProfile = new QueueProfile($player->getName(), $selectedMap);

                QueueFactory::make($queueProfile);
                SessionFactory::get($player->getName())?->setQueue($queueProfile);

                $player->sendMessage(TextFormat::colorize(
                    "&aYou successfully entered in a sumo queue for map &e" . (is_null($selectedMap) ? "Random Map" : $selectedMap)
                ));
                $player->sendMessage(TextFormat::colorize("&aFor exit the queue use &e/sumo exit"));
            }
        );

        $player->sendForm($form);
    }
}