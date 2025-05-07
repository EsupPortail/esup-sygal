<?php

namespace ApplicationUnitTest\Test\Provider;

use \PHPUnit\Framework\TestCase;

trait MockProviderAwareTrait
{
    /**
     * @var MockProvider
     */
    protected $mockProvider;

    /**
     * @return MockProvider
     */
    public function mp()
    {
        if (null === $this->mockProvider) {
            if (! $this instanceof \PHPUnit\Framework\TestCase) {
                throw new \RuntimeException("La classe utilisant ce trait doit être du type " . \PHPUnit\Framework\TestCase::class);
            }
            /** @var \PHPUnit\Framework\TestCase $this */
            $this->mockProvider = new MockProvider($this);
        }

        return $this->mockProvider;
    }
}