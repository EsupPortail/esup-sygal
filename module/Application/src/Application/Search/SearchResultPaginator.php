<?php

namespace Application\Search;

use Laminas\Paginator\Paginator;

class SearchResultPaginator extends Paginator
{
    /**
     * @var bool
     */
    protected $containsRealSearchResult = false;

    /**
     * @return bool
     */
    public function containsRealSearchResult(): bool
    {
        return $this->containsRealSearchResult;
    }

    /**
     * @param bool $containsRealSearchResult
     * @return self
     */
    public function setContainsRealSearchResult(bool $containsRealSearchResult = true): self
    {
        $this->containsRealSearchResult = $containsRealSearchResult;
        return $this;
    }
}