<?php

namespace Fichier\Controller\Plugin\Uploader;

use Interop\Container\ContainerInterface;

class UploaderPluginFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var UploadForm $form */
        $form = $container->get('FormElementManager')->get('UploadForm');

        $plugin = new UploaderPlugin();
        $plugin->setForm($form);

        return $plugin;
    }
}