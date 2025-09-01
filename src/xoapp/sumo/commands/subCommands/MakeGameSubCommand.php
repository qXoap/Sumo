<?php

namespace xoapp\sumo\commands\subCommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use xoapp\sumo\factory\SessionFactory;
use xoapp\sumo\factory\MapFactory;
use xoapp\sumo\session\process\MakeGameProcess;

class MakeGameSubCommand extends BaseSubCommand
{
    public function __construct(PluginBase $base)
    {
        parent::__construct($base, "make");
        $this->setPermission("sumo.make.command");
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

        if (is_null($session = SessionFactory::get($sender->getName()))) {
            return;
        }

        if ($args['mapName'] === null) {
            $sender->sendMessage(TextFormat::colorize("&cUse /sumo make <mapName>"));
            return;
        }

        if (!is_null(MapFactory::get($args['mapName']))) {
            $sender->sendMessage(TextFormat::colorize("&cThis map already exists"));
            return;
        }

        $makingProcess = new MakeGameProcess($args['mapName'], $session);
        $session->setMakingProcess($makingProcess);
    }
}