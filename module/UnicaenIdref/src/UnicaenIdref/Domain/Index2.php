<?php

namespace UnicaenIdref\Domain;

/**
 * "Index non utilisé aujourd'hui"
 * @see https://documentation.abes.fr/aideidrefdeveloppeur/index.html#ConstructionRequete
 */
class Index2 extends AbstractIndex
{
    protected string $name = 'Index2';
    protected string $valueName = 'Index2Value';

    protected string $index = '';
    protected string $indexValue = '';
}