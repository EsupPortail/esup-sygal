<?php

namespace Import\Controller;

use Application\Controller\Plugin\Url\UrlThesePlugin;
use Import\Service\SchemaService;
use Import\Service\ImportObserv\ImportObservServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
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

    const NOTIF_UPDATE_THESE_RESULTAT = 'UPDATE--THESE--RESULTAT'; // format 'UPDATE--{NOM_DE_TABLE}--{NOM_DE_COLONNE}'

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
