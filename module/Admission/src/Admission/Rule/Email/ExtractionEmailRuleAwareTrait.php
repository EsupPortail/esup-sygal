<?php

namespace Admission\Rule\Email;

trait ExtractionEmailRuleAwareTrait
{
    protected ExtractionEmailRule $extractionMailRule;

    public function setExtractionEmailRule(ExtractionEmailRule $extractionMailRule): void
    {
        $this->extractionMailRule = $extractionMailRule;
    }
}