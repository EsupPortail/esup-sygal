<?php

namespace Application\Search;

use Laminas\Paginator\Paginator;

class SearchResultPaginator extends Paginator
{
    protected bool $containsRealSearchResult = false;

    /**
     * Indique si le paginator contient des données ou non.
     */
    public function containsRealSearchResult(): bool
    {
        return $this->containsRealSearchResult;
    }

    /**
     * Spécifie si le paginator contient des données ou non.
     */
    public function setContainsRealSearchResult(bool $containsRealSearchResult = true): static
    {
        $this->containsRealSearchResult = $containsRealSearchResult;
        return $this;
    }
}