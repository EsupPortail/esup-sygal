<?php

namespace Application;

trait SourceCodeStringHelperAwareTrait
{
    /**
     * @var SourceCodeStringHelper
     */
    protected $sourceCodeStringHelper;

    /**
     * @return SourceCodeStringHelper
     */
    public function getSourceCodeStringHelper()
    {
        if (null === $this->sourceCodeStringHelper) {
            $this->sourceCodeStringHelper = new SourceCodeStringHelper();
        }

        return $this->sourceCodeStringHelper;
    }
}