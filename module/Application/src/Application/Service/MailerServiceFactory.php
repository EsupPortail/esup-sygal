<?php

namespace Application\Service;

use UnicaenApp\Options\ModuleOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Transport\Smtp;
use Zend\ServiceManager\Exception\InvalidArgumentException;

/**
 *
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MailerServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return MailerService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $options     = $serviceLocator->get('unicaen-app_module_options'); /* @var $options ModuleOptions */
        $mailOptions = $options->getMail();
        
        if (!isset($mailOptions['transport_options'])) {
            throw new InvalidArgumentException("Options de transport de mail introuvables.");
        }
        
        $transport = new Smtp(new SmtpOptions($mailOptions['transport_options']));
        $service   = new MailerService($transport);
        
        if (!empty($mailOptions['redirect_to'])) {
            $service->setRedirectTo($mailOptions['redirect_to']);
        }
        if (isset($mailOptions['do_not_send'])) {
            $service->setDoNotSend($mailOptions['do_not_send']);
        }
        
        return $service;
    }
}
