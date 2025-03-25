<?php

namespace SygalApiImpl\V1\Extractor;

use Laminas\Hydrator\ExtractionInterface;
use stdClass;
use Webmozart\Assert\Assert;

abstract class AbstractExtractor implements ExtractionInterface
{
    public function extract(object $object): array
    {
        Assert::isInstanceOf($object, stdClass::class);

        return [
            'source_id' => $this->extractSourceId($object),
            'source_code' => $this->extractSourceCode($object),
        ];
    }

    public function extractSourceId(stdClass $object)
    {
        return $object->instancePegase;
    }

    abstract public function extractSourceCode(stdClass $object): string;
}