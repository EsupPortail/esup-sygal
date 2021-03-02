<?php

namespace Application\View\Helper\Uploader;

use Application\Controller\Plugin\Uploader\UploaderPlugin;
use Interop\Container\ContainerInterface;

class UploaderHelperFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var UploaderPlugin $uploaderPlugin */
        $uploaderPlugin = $container->get('ControllerPluginManager')->get('Uploader');
        $form = $uploaderPlugin->getForm();

        $helper = new UploaderHelper();
        $helper->setForm($form);

        return $helper;
    }
}