<?php

namespace xoapp\sumo\game\status;

class RunningStatus extends AbstractGameStatus
{
    protected int $time = 360;

    public function update(): void
    {
        $this->time--;
    }
}