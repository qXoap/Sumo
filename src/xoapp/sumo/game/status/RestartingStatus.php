<?php

namespace xoapp\sumo\game\status;

class RestartingStatus extends AbstractGameStatus
{
    protected int $time = 10;

    public function update(): void
    {
        $this->time--;
    }
}