<?php

namespace Retraitement\Controller;

use Retraitement\Form\Retraitement;
use UnicaenApp\Filter\BytesFormatter;
use UnicaenAuth\Service\Traits\UserContextServiceAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    use UserContextServiceAwareTrait;

    public function indexAction()
    {
//        var_dump($this->getServiceUserContext()->getLdapUser());

        $iterator = new \RecursiveDirectoryIterator("/opt/theses/data");
        //$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
         // could use CHILD_FIRST if you so wish

        $f = new BytesFormatter();
        $files = [];
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() == "pdf") {
                $files[md5($file->getFilename())] = $file->getFilename().' ('.$f->filter($file->getSize()).')';
            }
        }

        asort($files);

        $form = new Retraitement('retraitement', ['files' => $files, 'commands' => ['cines','mines']]);

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
//            var_dump($data);
            $form->setData($data);
            if ($form->isValid()) {
                if (array_key_exists('files', $data)) {
                    foreach ($data['files'] as $id) {
                        var_dump("Le fichier " . $files[$id] . " a été coché !");
                    }
                }
            }

        }

        return ['form' => $form];

    }

}