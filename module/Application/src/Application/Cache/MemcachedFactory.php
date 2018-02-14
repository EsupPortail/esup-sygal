<?php

namespace Application\Cache;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MemcachedFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $memcached = new \Memcached();
        $memcached->addServer('memcache_host', 11211);

        return $memcached;
    }
}