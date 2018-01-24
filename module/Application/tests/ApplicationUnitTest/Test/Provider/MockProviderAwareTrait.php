<?php

namespace ApplicationUnitTest\Test\Provider;

use PHPUnit_Framework_TestCase;

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
            if (! $this instanceof PHPUnit_Framework_TestCase) {
                throw new \RuntimeException("La classe utilisant ce trait doit être du type " . PHPUnit_Framework_TestCase::class);
            }
            /** @var PHPUnit_Framework_TestCase $this */
            $this->mockProvider = new MockProvider($this);
        }

        return $this->mockProvider;
    }
}