<?php

namespace Application\Cache;

use Interop\Container\ContainerInterface;

class MemcachedFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $memcached = new \Memcached();
        $memcached->addServer('memcache_host', 11211);

        return $memcached;
    }
}