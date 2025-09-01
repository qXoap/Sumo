<?php

namespace xoapp\sumo\commands\subCommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use xoapp\sumo\factory\MapFactory;

class DeleteMapSubCommand extends BaseSubCommand
{
    public function __construct(PluginBase $base)
    {
        parent::__construct($base, "delete");
        $this->setPermission("sumo.delete.command");
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument('mapName'));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        if ($args['mapName'] === null) {
            $sender->sendMessage(TextFormat::colorize("&cUse /sumo delete <mapName>"));
            return;
        }

        if (is_null(MapFactory::get($args['mapName']))) {
            $sender->sendMessage(TextFormat::colorize("&cThis map doesn't exist"));
            return;
        }

        MapFactory::remove($args['mapName']);
        $sender->sendMessage(TextFormat::colorize("&cMap successfully deleted"));
    }
}