<?php

namespace Application\View\Helper;

use Application\Filter\FinancementFormatter;
use Interop\Container\ContainerInterface;
use UnicaenAuth\Service\AuthorizeService;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FinancementFormatterHelperFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FinancementFormatterHelper
    {
        /** @var AuthorizeService $authorizeService */
        $authorizeService = $container->get('BjyAuthorize\Service\Authorize');

        $formatter = new FinancementFormatter();
        $formatter->setAuthorizeService($authorizeService);

        $helper = new FinancementFormatterHelper();
        $helper->setFormatter($formatter);

        return $helper;
    }
}