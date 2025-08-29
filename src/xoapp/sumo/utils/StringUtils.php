<?php

namespace xoapp\sumo\utils;

class StringUtils
{
    public static function time(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $seconds = $seconds % 60;

        if ($minutes < 0) {
            return $minutes . "m " . $seconds . "s";
        }

        return $seconds;
    }
}