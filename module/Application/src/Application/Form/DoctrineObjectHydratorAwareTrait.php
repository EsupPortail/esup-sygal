<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 28/04/16
 * Time: 16:56
 */

namespace Application\Form;

use Doctrine\Laminas\Hydrator\DoctrineObject;

trait DoctrineObjectHydratorAwareTrait
{
    /**
     * @var DoctrineObject
     */
    protected $doctrineObjectHydrator;

    /**
     * @param DoctrineObject $doctrineObjectHydrator
     */
    public function setDoctrineObjectHydrator(DoctrineObject $doctrineObjectHydrator)
    {
        $this->doctrineObjectHydrator = $doctrineObjectHydrator;
    }
}