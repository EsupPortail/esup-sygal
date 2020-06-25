<?php

namespace Import\Controller;

use Application\Controller\Plugin\Url\UrlThesePlugin;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Doctrine\ORM\EntityManager;
use Import\Service\ImportService;
use Import\Service\SchemaService;
use Import\Service\Traits\SynchroServiceAwareTrait;
use Interop\Container\ContainerInterface;
use UnicaenDbImport\Entity\Db\Service\ImportObserv\ImportObservServiceAwareTrait;
use Zend\View\Model\ViewModel;

/**
 *
 * @method UrlThesePlugin urlThese()
 * @author Unicaen
 */
class SynchroController extends \UnicaenImport\Controller\ImportController
{
    use TheseServiceAwareTrait;
    use ImportObservServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use SynchroServiceAwareTrait;

    const NOTIF_UPDATE_THESE_RESULTAT = 'UPDATE--THESE--RESULTAT'; // format 'UPDATE--{NOM_DE_TABLE}--{NOM_DE_COLONNE}'

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function indexAction()
    {
        list($data, $mviews) = array_values(parent::indexAction());

        /** @var SchemaService $service */
        $service = $this->getServiceSchema();
        $data = $service->sortTablesData($data);

        $view = new ViewModel();
//        $view->setTemplate('unicaen-import/import/index');
        $view->setVariables(['tables' => $data, 'mviews' => $mviews]);

        return $view;
    }

    /**
     * CLI : lancement de la Synchronisation des données d'un seul service.
     */
    public function synchronizeConsoleAction()
    {
        $service = $this->params('service');
        $emName = $this->params('em', 'orm_default');

        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get("doctrine.entitymanager.$emName");

        $_deb = microtime(true);
        $this->synchroService->setEntityManager($entityManager);
        $this->synchroService->addService($service);
        $this->synchroService->synchronize();
        $_fin = microtime(true);

        echo sprintf(
                "Synchronisation des données du service '%s' effectuée en %.2f secondes.",
                $service,
                $_fin - $_deb
            ) . PHP_EOL;
        echo "NB: Vérifiez dans la table SYNC_LOG la présence éventuelle d'erreurs." . PHP_EOL;
    }

    /**
     * CLI : lancement de la Synchronisation des données de tous les services.
     */
    public function synchronizeAllConsoleAction()
    {
        $emName = $this->params('em', 'orm_default');

        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get("doctrine.entitymanager.$emName");

        $services = ImportService::SERVICES;
        foreach ($services as $service) {
            $this->synchroService->addService($service);
        }

        $_deb = microtime(true);
        $this->synchroService->setEntityManager($entityManager);
        $this->synchroService->synchronize();
        $_fin = microtime(true);

        echo sprintf(
                "Synchronisation des données de tous les services effectuée en %.2f secondes.",
                $_fin - $_deb
            ) . PHP_EOL;
    }

//    public function updateTablesAction()
//    {
//        list($message, $title) = array_values(parent::updateTablesAction());
//
//        /**
//         * Mises à jour complémentaires.
//         */
//
//        $tables = [
//            'MV_RECHERCHE_THESE', // vue matérialisée pour la recherche de thèses.
//        ];
//
//        $message .= '<hr>';
//        try {
//            foreach ($tables as $table) {
//                $message .= '<div>Table "' . $table . '" mise à jour.</div>';
//                $this->getServiceQueryGenerator()->execMaj(new Query($table));
//            }
//            $message .= 'Mise à jour des données terminée';
//        } catch (\Exception $e) {
//            throw new LogicException("mise à jour des données impossible", null, $e);
//        }
//
//        $view = new ViewModel();
//        $view->setTemplate('unicaen-import/import/update-views-and-packages');
//        $view->setVariables(compact('message', 'title'));
//
//        return $view;
//    }
}
