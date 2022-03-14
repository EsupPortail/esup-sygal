<?php

namespace Application\Entity;

trait UserWrapperFactoryAwareTrait
{
    /**
     * @var UserWrapperFactory
     */
    private $userWrapperFactory;

    /**
     * @param \Application\Entity\UserWrapperFactory $userWrapperFactory
     */
    public function setUserWrapperFactory(UserWrapperFactory $userWrapperFactory)
    {
        $this->userWrapperFactory = $userWrapperFactory;
    }
}