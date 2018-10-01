<?php

namespace Soutenance\Controller;


use Application\Controller\AbstractController;
use Application\Entity\Db\Acteur;
use Application\Entity\Db\These;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use BjyAuthorize\Exception\UnAuthorizedException;
use DateInterval;
use Exception;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Form\SoutenanceDateRenduRapport\SoutenanceDateRenduRapportForm;
use Soutenance\Provider\Privilege\SoutenancePrivileges;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
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

    public function presoutenanceAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);
        $rapporteurs = $this->getPropositionService()->getRapporteurs($proposition);

        /** Si la proposition ne possède pas encore de date de rendu de rapport alors la valeur par défaut est donnée */
        $renduRapport = $proposition->getRenduRapport();
        if (!$renduRapport) {
            try {
                $renduRapport = $proposition->getDate();
                $renduRapport = $renduRapport->sub(new DateInterval('P21D'));
            } catch (Exception $e) {
                throw new RuntimeException("Un problème a été rencontré lors du calcul de la date de rendu des rapport.");
            }
            $proposition->setRenduRapport($renduRapport);
            $this->getPropositionService()->update($proposition);
        }

//        $engagements = [];
//        foreach ($rapporteurs as $rapporteur) {
//            if ($rapporteur->getIndividu()) {
//                $validations = $this->getValidationService()->getRepository()->findValidationByCodeAndIndividu(TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $rapporteur->getIndividu());
//                if ($validations) $engagements[$rapporteur->getIndividu()->getId()] = current($validations);
//            }
//        }

        return new ViewModel([
            'these' => $these,
            'proposition' => $proposition,
            'rapporteurs' => $rapporteurs,
//            'engagements' => $engagements,
        ]);
    }


    public function dateRenduRapportAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var SoutenanceDateRenduRapportForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(SoutenanceDateRenduRapportForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/presoutenance/date-rendu-rapport', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getPropositionService()->update($proposition);
            }
        }

        return new ViewModel([
                'form' => $form,
                'title' => 'Modification de la date de rendu des rapports',
            ]
        );
    }


    /**
     * @return ViewModel
     */
    public function associerMembreIndividuAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        $isAllowed = $this->isAllowed($these, SoutenancePrivileges::SOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU);
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à associer des individus aux membres de jury de cette thèse.");
        }

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

        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);

        $isAllowed = $this->isAllowed($these, SoutenancePrivileges::SOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU);
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à associer des individus aux membres de jury de cette thèse.");
        }

        /** @var Acteur $acteur */
        $idActeur = $this->params()->fromRoute('acteur');
        $acteur = $this->getActeurService()->getRepository()->find($idActeur);

        $membre->setIndividu($acteur->getIndividu());
        $this->getMembreService()->update($membre);

        $this->redirect()->toRoute('soutenance/presoutenance', ['these' => $these->getId()], [], true);
    }

    /**
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