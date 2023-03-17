<?php

namespace Application\Authentication\Storage;

use UnicaenAuthentification\Authentication\Storage\ChainServiceFactory as BaseChainServiceFactory;

/**
 * Ajout d'un storage maison à ceux d'UnicaenAuthentification.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ChainServiceFactory extends BaseChainServiceFactory
{
    protected $storages = [
        50 => 'Application\Authentication\Storage\AppStorage',
    ];
}