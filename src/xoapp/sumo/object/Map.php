<?php

namespace xoapp\sumo\object;

use Closure;
use pocketmine\Server;
use xoapp\sumo\Loader;
use pocketmine\world\Position;
use xoapp\sumo\utils\TaskUtils;
use xoapp\sumo\scheduler\async\CopyMapAsync;

readonly class Map
{
    public function __construct(
        private string   $name,
        private Position $firstPosition,
        private Position $secondPosition,
        bool             $copy = false,
    )
    {
        if (!$copy) {
            return;
        }

        TaskUtils::asyncTask(new CopyMapAsync(
            $name,
            Server::getInstance()->getDataPath() . 'worlds',
            $this->name,
            Loader::getInstance()->getDataFolder() . 'maps'
        ));
    }

    public static function deserializeData(array $data): array
    {
        return [
            'firstPosition' => new Position(
                $data['firstPosition']['x'],
                $data['firstPosition']['y'],
                $data['firstPosition']['z'],
                null
            ),
            'secondPosition' => new Position(
                $data['secondPosition']['x'],
                $data['secondPosition']['y'],
                $data['secondPosition']['z'],
                null
            )
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFirstPosition(): Position
    {
        return $this->firstPosition;
    }

    public function getSecondPosition(): Position
    {
        return $this->secondPosition;
    }

    public function copyWorld(string $newName, string $newDirectory, ?Closure $callback = null): void
    {
        $sleeperEntry = Loader::sleeperHandler()->addNotifier(function () use ($newName, $callback): void
        {
            if (is_null($callback)) {
                return;
            }

            Server::getInstance()->getWorldManager()->loadWorld($newName);
            call_user_func($callback, Server::getInstance()->getWorldManager()->getWorldByName($newName));
        });

        TaskUtils::asyncTask(new CopyMapAsync(
            $this->name,
            Loader::getInstance()->getDataFolder() . 'maps',
            $newName,
            $newDirectory,
            $sleeperEntry
        ));
    }

    public function serializeData(): array
    {
        return [
            'firstPosition' => [
                'x' => $this->firstPosition->getX(),
                'y' => $this->firstPosition->getY(),
                'z' => $this->firstPosition->getZ()
            ],
            'secondPosition' => [
                'x' => $this->secondPosition->getX(),
                'y' => $this->secondPosition->getY(),
                'z' => $this->secondPosition->getZ()
            ]
        ];
    }
}