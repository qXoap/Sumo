<?php

namespace xoapp\sumo\game;

use pocketmine\block\Water;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use Symfony\Component\Filesystem\Path;
use xoapp\sumo\factory\GameFactory;
use xoapp\sumo\game\status\AbstractGameStatus;
use xoapp\sumo\game\status\RestartingStatus;
use xoapp\sumo\game\status\StartingStatus;
use xoapp\sumo\factory\MapFactory;
use xoapp\sumo\scheduler\async\DeleteMapAsync;
use xoapp\sumo\session\Session;
use xoapp\sumo\utils\TaskUtils;

class Game
{
    private int $firstSessionHits = 0;
    private int $secondSessionHits = 0;

    public function __construct(
        private readonly string $id,
        private readonly string $mapName,
        private readonly Session $firstSession,
        private readonly Session $secondSession,
        private readonly World $world,
        private ?AbstractGameStatus $gameStatus = null
    )
    {
        $this->gameStatus = new StartingStatus($this);
        $this->initialize();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMapName(): string
    {
        return $this->mapName;
    }

    public function getFirstSession(): Session
    {
        return $this->firstSession;
    }

    public function getSecondSession(): Session
    {
        return $this->secondSession;
    }

    public function getWorld(): World
    {
        return $this->world;
    }

    public function getFirstSessionHits(): int
    {
        return $this->firstSessionHits;
    }

    public function getSecondSessionHits(): int
    {
        return $this->secondSessionHits;
    }

    public function isFirstSession(string $name): bool
    {
        return $this->firstSession->getName() === $name;
    }

    public function setGameStatus(AbstractGameStatus $gameStatus): void
    {
        $this->gameStatus = $gameStatus;
    }

    private function initialize(): void
    {
        $this->world->setTime(World::TIME_DAY);
        $this->world->stopTime();

        $map = MapFactory::get($this->mapName);
        $positions = [$map->getFirstPosition(), $map->getSecondPosition()];

        foreach ([$this->firstSession, $this->secondSession] as $i => $session) {
            /** @var Session $session */

            $session->getPlayer()?->setGamemode(GameMode::SURVIVAL());
            $session->getPlayer()?->teleport(Position::fromObject(
                $positions[$i]->add(0.5, 0, 0.5), $this->world
            ));

            $session->clearInventory();
            $session->setCurrentGame($this);
        }
    }

    public function update(): void
    {
        $this->gameStatus->update();
        $sessions = [$this->firstSession, $this->secondSession];

        foreach ($sessions as $i => $session) {
            /** @var Session $session */

            if (($player = $session->getPlayer()) === null) {
                return;
            }

            $positionY = $player->getPosition()->getY();
            $floorBlock = $player->getWorld()->getBlock($player->getPosition());

            if ($positionY <= 0 || $floorBlock instanceof Water) {
                $this->finish($session[$i <= 0 ? 0 : 1], $sessions[$i <= 0 ? 1 : 0]);
            }
        }
    }

    public function handleMove(PlayerMoveEvent $event): void
    {
        if ($this->gameStatus instanceof StartingStatus) {
            $event->cancel();
        }
    }

    public function handleDamage(EntityDamageEvent $event): void
    {
        $player = $event->getEntity();

        if ($player instanceof Player) {
            $player->setHealth($player->getMaxHealth());
            $this->isFirstSession($player->getName()) ? $this->firstSessionHits++ : $this->secondSessionHits++;
        }
    }

    public function finish(Session $winner, Session $looser): void
    {
        $winner->getPlayer()?->sendTitle(
            TextFormat::colorize("&l&aWINNER!"),
            TextFormat::colorize("&7You Won Duel!")
        );
        $winner->makeSound('random.levelup');

        $looser->getPlayer()?->sendTitle(
            TextFormat::colorize("&l&cLOOSER!"),
            TextFormat::colorize("&7You Loose Duel!")
        );
        $looser->makeSound('mob.wither.death');

        $this->gameStatus = new RestartingStatus($this);
    }

    public function destroy(): void
    {
        Server::getInstance()->getWorldManager()->unloadWorld($this->world);

        TaskUtils::asyncTask(new DeleteMapAsync(
            "sumo-" . $this->id,
            Path::join(Server::getInstance()->getDataPath(), "worlds")
        ));

        GameFactory::remove($this->id);
    }
}