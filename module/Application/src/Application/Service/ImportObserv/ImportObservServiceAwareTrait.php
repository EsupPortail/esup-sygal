<?php

namespace Application\Service\ImportObserv;

use RuntimeException;

/**
 * @author Unicaen
 */
trait ImportObservServiceAwareTrait
{
    /**
     * @var ImportObservService
     */
    private $serviceImportObserv;

    /**
     * @param ImportObservService $serviceImportObserv
     * @return static
     */
    public function setImportObservService(ImportObservService $serviceImportObserv)
    {
        $this->serviceImportObserv = $serviceImportObserv;

        return $this;
    }

    /**
     * @return ImportObservService
     * @throws RuntimeException
     */
    public function getImportObservService()
    {
        if (empty($this->serviceImportObserv)) {
            if (!method_exists($this, 'getServiceLocator')) {
                throw new RuntimeException('La classe ' . get_class($this) . ' n\'a pas accès au ServiceLocator.');
            }

            $serviceLocator = $this->getServiceLocator();
            if (method_exists($serviceLocator, 'getServiceLocator')) {
                $serviceLocator = $serviceLocator->getServiceLocator();
            }
            $this->serviceImportObserv = $serviceLocator->get('UnicaenImport\Service\Notification');
        }

        return $this->serviceImportObserv;
    }
}