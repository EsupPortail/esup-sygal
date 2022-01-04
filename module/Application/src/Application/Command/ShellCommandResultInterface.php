<?php

namespace Application\Command;

interface ShellCommandResultInterface
{
    /**
     * @param array
     */
    public function getOutput(): array;

    /**
     * @param int
     */
    public function getReturnCode(): int;

    /**
     * @return bool
     */
    public function isSuccessfull(): bool;
}