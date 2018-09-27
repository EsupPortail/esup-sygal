<?php

namespace Soutenance\Controller;


use Application\Controller\AbstractController;
use Application\Entity\Db\Acteur;
use Application\Entity\Db\These;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Soutenance\Entity\Membre;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use Zend\Http\Request;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class PresoutenanceController extends AbstractController
{
    use TheseServiceAwareTrait;
    use MembreServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use ActeurServiceAwareTrait;

    public function associerMembreIndividuAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);


        $acteur = null;
        if ($membre->getIndividu()) {
            $acteur = $this->getActeurService()->getRepository()->findActeurByIndividu($membre->getIndividu()->getId());
        }


        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $acteur = $this->getActeurService()->getRepository()->findActeurByIndividu($data['individu']['id']);
        }



        return new ViewModel([
            'these' => $these,
            'membre' => $membre,
            'acteur' => $acteur,
            'title' => "Association d'un acteur SyGAL au membre [".$membre->getDenomination()."]",
        ]);
    }

    public function enregistrerAssociationMembreIndividuAction() {

        $idThese = $this->params()->fromRoute('these');

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);

        /** @var Acteur $acteur */
        $idActeur = $this->params()->fromRoute('acteur');
        $acteur = $this->getActeurService()->getRepository()->find($idActeur);

        $membre->setIndividu($acteur->getIndividu());
        $this->getMembreService()->update($membre);

        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $idThese], [], true);
    }


    /**
     * AJAX.
     *
     * Recherche d'un Individu.
     *
     * @param string $type => permet de spécifier un type d'acteur ...
     * @return JsonModel
     */
    public function rechercherActeurAction()
    {
        if (($term = $this->params()->fromQuery('term'))) {
            $rows = $this->getActeurService()->getRepository()->findByText($term);
            $result = [];
            foreach ($rows as $row) {
                $prenoms = implode(' ', array_filter([$row['PRENOM1'], $row['PRENOM2'], $row['PRENOM3']]));
                // mise en forme attendue par l'aide de vue FormSearchAndSelect
                $label = $row['NOM_USUEL'] . ' ' . $prenoms;
                $extra = $row['EMAIL'] ?: $row['SOURCE_CODE'];
                $result[] = array(
                    'id'    => $row['ID'], // identifiant unique de l'item
                    'label' => $label,     // libellé de l'item
                    'extra' => $extra,     // infos complémentaires (facultatives) sur l'item
                );
            }
            usort($result, function($a, $b) {
                return strcmp($a['label'], $b['label']);
            });

            return new JsonModel($result);
        }
        exit;
    }
}