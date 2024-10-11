<?php
namespace Admission\Controller\Transmission;

use Admission\Form\Transmission\TransmissionForm;
use Admission\Service\Admission\AdmissionService;
use Admission\Service\Transmission\TransmissionService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TransmissionControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return TransmissionController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransmissionController
    {
        $admissionService = $container->get(AdmissionService::class);
        $transmissionService = $container->get(TransmissionService::class);
        /**
         * @var TransmissionForm $conventionFormationDoctorale
         */
        $transmissionForm = $container->get('FormElementManager')->get(TransmissionForm::class);

        $controller = new TransmissionController();
        $controller->setAdmissionService($admissionService);
        $controller->setTransmissionService($transmissionService);
        $controller->setTransmissionForm($transmissionForm);

        return $controller;
    }
}