<?php

namespace Application;

trait SourceCodeStringHelperAwareTrait
{
    /**
     * @var SourceCodeStringHelper
     */
    protected $sourceCodeStringHelper;

    /**
     * @param SourceCodeStringHelper $sourceCodeStringHelper
     */
    public function setSourceCodeStringHelper(SourceCodeStringHelper $sourceCodeStringHelper)
    {
        $this->sourceCodeStringHelper = $sourceCodeStringHelper;
    }
}