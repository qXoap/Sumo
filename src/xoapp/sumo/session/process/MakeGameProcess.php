<?php

namespace xoapp\sumo\session\process;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use xoapp\sumo\factory\MapFactory;
use xoapp\sumo\session\Session;

class MakeGameProcess
{
    public function __construct(
        private readonly string  $name,
        private readonly Session $session,
        private ?Position        $firstPosition = null,
        private ?Position        $secondPosition = null
    )
    {
        $this->initialize();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function getFirstPosition(): ?Position
    {
        return $this->firstPosition;
    }

    public function getSecondPosition(): ?Position
    {
        return $this->secondPosition;
    }

    private function initialize(): void
    {
        if (is_null($player = $this->session->getPlayer())) {
            return;
        }

        $inventory = $player->getInventory();

        $firstPosition = VanillaItems::IRON_SWORD();
        $firstPosition->setCustomName(TextFormat::colorize("&eFirst Position"));
        $firstPosition->setNamedTag(CompoundTag::create()->setString("sumo", "firstPosition"));

        $secondPosition = VanillaItems::IRON_SWORD();
        $secondPosition->setCustomName(TextFormat::colorize("&bSecond Position"));
        $secondPosition->setNamedTag(CompoundTag::create()->setString("sumo", "secondPosition"));

        $saveProcess = VanillaItems::IRON_SWORD();
        $saveProcess->setCustomName(TextFormat::colorize("&aSave Process"));
        $saveProcess->setNamedTag(CompoundTag::create()->setString("sumo", "save"));

        $this->session->clearInventory();
        $inventory->setContents([
            0 => $firstPosition,
            1 => $secondPosition,
            8 => $saveProcess
        ]);

        $player->sendMessage(TextFormat::colorize(
            "&aYou successfully entered in making game process for Map &e" . $this->name
        ));
        $this->session->makeSound('random.levelup');
    }

    public function handleInteract(PlayerInteractEvent $event): void
    {
        $event->cancel();

        $block = $event->getBlock();
        $item = $event->getItem();

        $compoundTag = $item->getNamedTag()->getTag("sumo");
        $player = $this->session->getPlayer();

        if (is_null($compoundTag) || is_null($player)) {
            return;
        }

        switch ($compoundTag) {
            case 'firstPosition':
            {
                if ($player->getWorld()->getFolderName() !== $this->name) {
                    $player->sendMessage(TextFormat::colorize("&cYou can\'t set a first spawn in another world"));
                    return;
                }

                $this->firstPosition = $block->getPosition();
                $player->sendMessage(TextFormat::colorize("&aFirst spawn position successfully putted"));
                return;
            }

            case 'secondPosition':
            {
                if ($player->getWorld()->getFolderName() !== $this->name) {
                    $player->sendMessage(TextFormat::colorize("&cYou can\'t set a second spawn in another world"));
                    return;
                }

                if (is_null($this->firstPosition)) {
                    $player->sendMessage(TextFormat::colorize("&cPlease put the first position"));
                    return;
                }

                $this->secondPosition = $block->getPosition();
                $player->sendMessage(TextFormat::colorize("&aSecond spawn position successfully putted"));
                return;
            }

            case 'save':
            {
                if (is_null($this->firstPosition)) {
                    $player->sendMessage(TextFormat::colorize("&cPlease put the first position"));
                    return;
                }

                if (is_null($this->secondPosition)) {
                    $player->sendMessage(TextFormat::colorize("&cPlease put the second position"));
                    return;
                }

                $this->save();
                return;
            }
        }
    }

    private function save(): void
    {
        if (is_null($player = $this->session->getPlayer())) {
            return;
        }

        if (is_null($this->firstPosition)) {
            $player->sendMessage(TextFormat::colorize("&7Please set the first position"));
            return;
        }

        if (is_null($this->secondPosition)) {
            $player->sendMessage(TextFormat::colorize("&7Please set the second position"));
            return;
        }

        if (!is_null(MapFactory::get($this->name))) {
            $player->sendMessage(TextFormat::colorize("&cMap already registered"));
            return;
        }

        MapFactory::create($this->name, $this->firstPosition, $this->secondPosition, true);
        $player->sendMessage(TextFormat::colorize("&aSumo Map &e" . $this->name . "&a Successfully registered"));

        $this->session->clearInventory();
        $this->session->setMakingProcess(null);

        $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
    }
}