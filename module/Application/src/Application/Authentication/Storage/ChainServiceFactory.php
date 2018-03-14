<?php

namespace Application\Authentication\Storage;

use UnicaenAuth\Authentication\Storage\ChainServiceFactory as BaseChainServiceFactory;

/**
 * Description of ChainAuthenticationStorageServiceFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ChainServiceFactory extends BaseChainServiceFactory
{
    protected $storages = [
        200 => 'UnicaenAuth\Authentication\Storage\Ldap',
        100 => 'UnicaenAuth\Authentication\Storage\Db',
        75  => 'Application\Authentication\Storage\Shib',
        50  => 'Application\Authentication\Storage\AppStorage',
    ];
}