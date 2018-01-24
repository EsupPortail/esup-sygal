<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 28/04/16
 * Time: 16:56
 */

namespace Application\Form;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

interface DoctrineObjectHydratorAwareInterface
{
    public function setDoctrineObjectHydrator(DoctrineObject $doctrineObjectHydrator);
}