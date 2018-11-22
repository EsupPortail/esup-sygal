<?php

namespace Import\Service\Traits;

use Import\Service\DbService;

trait DbServiceAwareTrait
{
    /**
     * @var DbService
     */
    protected $dbService;

    /**
     * @param DbService $dbService
     * @return self
     */
    public function setDbService(DbService $dbService)
    {
        $this->dbService = $dbService;

        return $this;
    }
}