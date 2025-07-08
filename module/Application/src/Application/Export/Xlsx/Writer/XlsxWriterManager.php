<?php

namespace Application\Export\Xlsx\Writer;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @method XlsxWriterInterface get($name, ?array $options = null)
 */
class XlsxWriterManager extends AbstractPluginManager
{
    protected $instanceOf = XlsxWriterInterface::class;
}