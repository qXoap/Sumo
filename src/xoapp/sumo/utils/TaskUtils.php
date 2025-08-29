<?php

namespace xoapp\sumo\utils;

use pocketmine\Server;
use xoapp\sumo\Loader;
use pocketmine\scheduler\{Task, AsyncTask, ClosureTask};
use Closure;

class TaskUtils
{

    public static function repeatingTask(Closure|Task $task, int $ticks = 50): void
    {
        $task = $task instanceof Closure ? new ClosureTask($task) : $task;
        Loader::getInstance()->getScheduler()->scheduleRepeatingTask($task, $ticks);
    }

    public static function asyncTask(AsyncTask $task): void
    {
        Server::getInstance()->getAsyncPool()->submitTask($task);
    }

    public static function delayedTask(Closure|Task $task, int $delay = 20): void
    {
        $task = $task instanceof Closure ? new ClosureTask($task) : $task;
        Loader::getInstance()->getScheduler()->scheduleDelayedTask($task, $delay);
    }
}