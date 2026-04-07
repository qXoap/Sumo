<?php

namespace xoapp\sumo\commands\subCommands;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use xoapp\sumo\factory\SessionFactory;

class ExitGameSubCommand extends BaseSubCommand
{
    public function __construct(PluginBase $base)
    {
        parent::__construct($base, "exit");
    }

    protected function prepare(): void
    {
        // TODO: Implement prepare() method.
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }

        if (is_null($session = SessionFactory::get($sender->getName()))) {
            return;
        }

        $session->close(function (array $result) use ($sender): void
        {
            if (empty($result)) {
                $sender->sendMessage(TextFormat::colorize("&cFailed to exit from sumo data"));
                return;
            }

            if (array_key_exists('queue', $result)) {
                $sender->sendMessage(TextFormat::colorize("&aSuccessfully exited the sumo queue"));
            }

            if (array_key_exists('game', $result)) {
                $sender->sendMessage(TextFormat::colorize("&aSuccessfully exited the sumo game"));
            }

            if (array_key_exists('process', $result)) {
                $sender->sendMessage(TextFormat::colorize("&aSuccessfully exited the sumo make process"));
            }
        });
    }
}