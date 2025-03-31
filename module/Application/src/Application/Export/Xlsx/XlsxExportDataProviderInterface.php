<?php

namespace Application\Export\Xlsx;

interface XlsxExportDataProviderInterface
{
    public function setContext(array $context): void;

    public function fetchData(): array;
}