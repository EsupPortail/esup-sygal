<?php

namespace Retraitement\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Fichier;
use Application\EventRouterReplacerAwareTrait;
use Application\Service\Fichier\FichierServiceAwareInterface;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\Notification\NotificationServiceAwareInterface;
use Application\Service\Notification\NotificationServiceAwareTrait;
use Retraitement\Form\Retraitement;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Filter\BytesFormatter;
use UnicaenApp\ORM\Event\Listeners\HistoriqueListener;
use Zend\Console\Request as ConsoleRequest;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Log\Writer\Syslog;

class IndexController extends AbstractController
    implements FichierServiceAwareInterface, NotificationServiceAwareInterface
{
    use EventRouterReplacerAwareTrait;
    use FichierServiceAwareTrait;
    use NotificationServiceAwareTrait;

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

    /**
     * Console action.
     */
    public function retraiterConsoleAction()
    {
        ini_set('memory_limit', '500M');

        $request = $this->getRequest();

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest){
            throw new RuntimeException('You can only use this action from a console!');
        }

        $id  = $request->getParam('fichier');
        $notifier  = $request->getParam('notifier', false);
        $tester  = (bool) $request->getParam('tester-archivabilite', false);

        if (! $id) {
            throw new RuntimeException("Argument obligatoire manquant: fichier");
        }

        /** @var Fichier $fichier */
        $fichier = $this->fichierService->getRepository()->find($id);
        if (! $fichier) {
            throw new RuntimeException("Fichier introuvable: " . $id);
        }

        // recherche du listener de gestion de l'historique pour lui transmettre le pseudo-utilisateur correspondant à l'application
        foreach ($this->fichierService->getEntityManager()->getEventManager()->getListeners() as $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof HistoriqueListener) {
                    $listener->setIdentity(['db' => $this->utilisateurApplication]);
                }
            }
        }

        $this->eventRouterReplacer->replaceEventRouter($this->getEvent());

        $fichierRetraite = $this->fichierService->creerFichierRetraite($fichier);
        echo "Fichier créé avec succès: " . $fichierRetraite->getId();
        echo PHP_EOL;

        $validite = null;
        if ($tester) {
            $validite = $this->fichierService->validerFichier($fichierRetraite);
            echo "Résultat du test d'archivabilité: " . PHP_EOL . $validite;
            echo PHP_EOL;
        }

        if ($notifier) {
            $destinataires = $notifier;
            $notif = $this->notificationService->triggerRetraitementFini($destinataires, $fichierRetraite, $validite);
            echo "Destinataires du courriel envoyé: " . $notif->getTo();
            echo PHP_EOL;
        }

        $this->eventRouterReplacer->restoreEventRouter();

        exit(0);
    }
}