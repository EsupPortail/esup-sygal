<?php

namespace Indicateur\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\These;
use Application\Service\AnomalieServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use DateTime;
use Indicateur\Form\IndicateurForm;
use Indicateur\Model\Indicateur;
use Indicateur\Service\IndicateurServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\View\Model\CsvModel;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndicateurController extends AbstractActionController {
    use IndividuServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use AnomalieServiceAwareTrait;
    use IndicateurServiceAwareTrait;

    /**
     * @return array|ViewModel
     * @throws \Exception
     */
    public function indexAction()
    {
        $resultats = [];

        $indicateurs = $this->getIndicateurService()->findAll();

        foreach ($indicateurs as $indicateur) {
            if ($indicateur->isActif()) {
                $resultats[$indicateur->getId()] = $this->getIndicateurService()->fetch($indicateur->getId());
            }
        }

        return new ViewModel([
                'indicateurs' => $indicateurs,
                'resultats'   => $resultats,
            ]
        );
    }

    public function listerIndicateurAction() {
        $indicateurs = $this->getIndicateurService()->findAll();

        return new ViewModel([
                'indicateurs' => $indicateurs,
            ]
        );
    }

    public function editerIndicateurAction() {
        $idIndicateur = $this->params()->fromRoute('indicateur');

        $indicateur = null;
        if ($idIndicateur) {
            $indicateur = $this->getIndicateurService()->find($idIndicateur);
        } else {
            $indicateur = new Indicateur();
        }

        /** @var  IndicateurForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(IndicateurForm::class);
        $form->bind($indicateur);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);

            if ($form->isValid()) {
                if ($idIndicateur) {
                    $this->getIndicateurService()->updateIndicateur($indicateur);
                    $this->getIndicateurService()->dropMaterialzedView($indicateur);
                } else {
                    $this->getIndicateurService()->createIndicateur($indicateur);
                    $this->getIndicateurService()->toggleActivite($indicateur);
                }
                $this->getIndicateurService()->createMaterialzedView($indicateur);

                $this->redirect()->toRoute('indicateur/lister',[],[], true);
            }
        }

        return new ViewModel([
            'form' => $form,
        ]);


    }

    public function rafraichirIndicateurAction() {
    }

    /**
     * Cette fonction permet d'activer ou de désactiver un indicateur
     */
    public function toggleIndicateurAction() {
        $idIndicateur = $this->params()->fromRoute('indicateur');
        $indicateur = $this->getIndicateurService()->find($idIndicateur);

        if (!$indicateur) throw new RuntimeException("Aucun indicateur identifié [".$idIndicateur."] n'as pu être récupéré.");

        $this->getIndicateurService()->toggleActivite($indicateur);

        $this->redirect()->toRoute('indicateur/lister', [], [], true);

    }

    public function effacerIndicateurAction() {
        $idIndicateur = $this->params()->fromRoute('indicateur');
        $indicateur = $this->getIndicateurService()->find($idIndicateur);

        if (!$indicateur) throw new RuntimeException("Aucun indicateur identifié [".$idIndicateur."] n'as pu être récupéré.");

        $this->getIndicateurService()->destroyIndicateur($indicateur);

        $this->redirect()->toRoute('indicateur/lister', [], [], true);
    }

    public function viewAction()
    {
        $idIndicateur = $this->params()->fromRoute('indicateur');
        $indicateur = $this->getIndicateurService()->find($idIndicateur);
        $data = $this->getIndicateurService()->fetch($idIndicateur);

        return new ViewModel([
            'indicateur' => $indicateur,
            'data' => $data,
        ]);
    }

    public function exportAction()
    {
        $idIndicateur = $this->params()->fromRoute('indicateur');
        $indicateur = $this->getIndicateurService()->find($idIndicateur);
        $data = $this->getIndicateurService()->fetch($idIndicateur);

        $headers = [];
        if ($indicateur->getDisplayAs() == Indicateur::THESE) {
            $headers = [
                'id'                    => 'ID',
                'Source Code'           => 'SOURCE_CODE',
                'Titre'                 => 'TITRE',
                'Première inscription'  => 'DATE_PREM_INSC',
                'Date de soutenance'    => 'DATE_SOUTENANCE',
            ];
        }
        if ($indicateur->getDisplayAs() == Indicateur::INDIVIDU) {
            $headers = [
                'id'                    => 'ID',
                'Source Code'           => 'SOURCE_CODE',
                'Nom usuel'             => 'NOM_USUEL',
                'Nom Patronymique'      => 'NOM_PATRONYMIQUE',
                'Prénom 1'              => 'PRENOM1',
                'Prénom 2'              => 'PRENOM2',
                'Prénom 3'              => 'PRENOM3',
                'Email'                 => 'EMAIL',
            ];
        }


        $records = [];
        foreach ($data as $entry) {
            $record = [];
            foreach($headers as $key => $fct) {
                $record[] = $entry[$fct];
            }
            $records[] = $record;
        }

        $filename = (new DateTime('now'))->format("Ymd-His").'_'.str_replace(" ","-",$indicateur->getLibelle()).'.csv';

        $CSV = new CsvModel();
        $CSV->setDelimiter(';');
        $CSV->setEnclosure('"');
        $CSV->setHeader(array_keys($headers));
        $CSV->setData($records);
        $CSV->setFilename($filename);

        return $CSV;
    }

}

