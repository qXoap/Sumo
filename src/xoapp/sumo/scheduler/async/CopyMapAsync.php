<?php

namespace xoapp\sumo\scheduler\async;

use Directory;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\Binary;
use pocketmine\scheduler\AsyncTask;
use Symfony\Component\Filesystem\Path;
use pocketmine\snooze\SleeperHandlerEntry;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\world\format\io\data\BedrockWorldData;

final class CopyMapAsync extends AsyncTask
{
    public function __construct(
        private readonly string               $world,
        private readonly string               $directory,
        private readonly string               $newName,
        private readonly string               $newDirectory,
        private readonly ?SleeperHandlerEntry $sleeperEntry = null
    )
    {
    }

    public function onRun(): void
    {
        $directory = $this->directory;
        $world = $this->world;

        $newDirectory = $this->newDirectory;
        $newName = $this->newName;

        $path = Path::join($directory, $world);
        $newPath = Path::join($newDirectory, $newName);

        $this->copySource($path, $newPath);
        $this->serializeWorld($newDirectory, $newName);

        $this->sleeperEntry?->createNotifier()->wakeupSleeper();
    }

    private function copySource(string $source, string $target): void
    {
        if (!is_dir($source)) {
            @copy($source, $target);
            return;
        }

        @mkdir($target);

        /** @var Directory $dir */
        $dir = dir($source);

        while (($entry = $dir->read()) !== false) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $Entry = Path::join($source, $entry);
            if (is_dir($Entry)) {
                $this->copySource($Entry, Path::join($target, $entry));
                continue;
            }

            @copy($Entry, Path::join($target, $entry));
        }

        $dir->close();
    }

    private function serializeWorld(string $newDirectory, string $newName): void
    {
        $path = Path::join($newDirectory, $newName);

        /** @var string $rawLevelData */
        $rawLevelData = file_get_contents(Path::join($path, 'level.dat'));
        $nbt = new LittleEndianNbtSerializer;

        $worldData = $nbt->read(substr($rawLevelData, 8))->mustGetCompoundTag();
        $worldData->setString('LevelName', $newName);

        $newNbt = new LittleEndianNbtSerializer;
        $buffer = $newNbt->write(new TreeRoot($worldData));

        file_put_contents(Path::join($path, 'level.dat'), Binary::writeLInt(BedrockWorldData::CURRENT_STORAGE_VERSION) . Binary::writeLInt(strlen($buffer)) . $buffer);
    }
}