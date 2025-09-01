<?php

namespace xoapp\sumo\commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use xoapp\sumo\forms\FormManager;

class SumoCommand extends BaseCommand
{
    public function __construct(private readonly PluginBase $base)
    {
        parent::__construct($this->base, "sumo");
        $this->setPermission("sumo.command");
    }

    protected function prepare(): void
    {
        // TODO: Implement prepare() method.
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            FormManager::getMapList($sender);
        }
    }
}