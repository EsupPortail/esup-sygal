<?php

namespace Application\Controller;

use Application\Controller\Plugin\Url\UrlThesePlugin;
use Application\Service\Import\SchemaService;
use Application\Service\ImportObserv\ImportObservServiceAwareTrait;
use Application\Service\Notification\NotificationServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenImport\Entity\Differentiel\Query;
use Zend\View\Model\ViewModel;

/**
 *
 * @method UrlThesePlugin urlThese()
 * @author Unicaen
 */
class ImportController extends \UnicaenImport\Controller\ImportController
{
    use TheseServiceAwareTrait;
    use ImportObservServiceAwareTrait;
    use NotificationServiceAwareTrait;

    const NOTIF_UPDATE_THESE_RESULTAT = 'UPDATE--THESE--RESULTAT'; // format 'UPDATE--{NOM_DE_TABLE}--{NOM_DE_COLONNE}'

    public function showDiffAction()
    {
        list($data, $mviews) = array_values(parent::showDiffAction());

        /** @var SchemaService $service */
        $service = $this->getServiceSchema();
        $data = $service->sortTablesData($data);

        $view = new ViewModel();
        $view->setTemplate('unicaen-import/import/show-diff');
        $view->setVariables(compact('data', 'mviews'));

        return $view;
    }

    public function updateTablesAction()
    {
        list($message, $title) = array_values(parent::updateTablesAction());

        /**
         * Mises à jour complémentaires.
         */

        $tables = [
            'MV_RECHERCHE_THESE', // vue matérialisée pour la recherche de thèses.
        ];

        $message .= '<hr>';
        try {
            foreach ($tables as $table) {
                $message .= '<div>Table "' . $table . '" mise à jour.</div>';
                $this->getServiceQueryGenerator()->execMaj(new Query($table));
            }
            $message .= 'Mise à jour des données terminée';
        } catch (\Exception $e) {
            throw new LogicException("mise à jour des données impossible", null, $e);
        }

        $view = new ViewModel();
        $view->setTemplate('unicaen-import/import/update-tables');
        $view->setVariables(compact('message', 'title'));

        return $view;
    }
}
