<?php

namespace xoapp\sumo\game\status;

use xoapp\sumo\game\Game;

abstract class AbstractGameStatus
{
    public function __construct(
        protected Game $game
    )
    {
    }

    protected int $time;

    abstract public function update(): void;
}