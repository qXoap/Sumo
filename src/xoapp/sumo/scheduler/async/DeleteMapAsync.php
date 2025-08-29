<?php

namespace xoapp\sumo\scheduler\async;

use pocketmine\scheduler\AsyncTask;
use Symfony\Component\Filesystem\Path;

final class DeleteMapAsync extends AsyncTask
{
    public function __construct(
        private readonly string $world,
        private readonly string $directory
    )
    {
    }

    public function onRun(): void
    {
        $world = $this->world;
        $directory = $this->directory;

        $path = Path::join($directory, $world);
        $this->deleteSource($path);
    }

    private function deleteSource(string $source): void
    {
        if (!is_dir($source)) {
            return;
        }

        if ($source[strlen($source) - 1] !== '/') {
            $source .= '/';
        }

        /** @var array $files */
        $files = glob($source . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (basename($file) === '.' || basename($file) === '..') {
                continue;
            }

            if (is_dir($file)) {
                $this->deleteSource($file);
            } else {
                unlink($file);
            }
        }

        rmdir($source);
    }
}