<?php

namespace Application\Command;

class ShellCommandResult implements ShellCommandResultInterface
{
    /**
     * @var string[]
     */
    protected $output;

    /**
     * @var int
     */
    protected $returnCode;

    /**
     * @param array $output
     * @param int $returnCode
     */
    public function __construct(array $output, int $returnCode)
    {
        $this->output = $output;
        $this->returnCode = $returnCode;
    }

    /**
     * @inheritDoc
     */
    public function getOutput(): array
    {
        return $this->output;
    }

    /**
     * @inheritDoc
     */
    public function getReturnCode(): int
    {
        return $this->returnCode;
    }

    /**
     * @inheritDoc
     */
    public function isSuccessfull(): bool
    {
        return $this->returnCode === 0;
    }
}