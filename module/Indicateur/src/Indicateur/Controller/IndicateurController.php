<?php

namespace Indicateur\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeStructure;
use Application\Entity\Db\UniteRecherche;
use Application\Service\AnomalieServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
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
    use StructureServiceAwareTrait;

    /**
     * @return array|ViewModel
     * @throws \Exception
     */
    public function indexAction()
    {
        $resultats = [];

        $indicateurs = $this->getIndicateurService()->findAll();

        foreach ($indicateurs as $indicateur) {
            $id = $indicateur->getId();

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
        $idIndicateur = $this->params()->fromRoute('indicateur');
        $indicateur = $this->getIndicateurService()->find($idIndicateur);

        if (!$indicateur) throw new RuntimeException("Aucun indicateur identifié [".$idIndicateur."] n'as pu être récupéré.");

        $this->getIndicateurService()->refreshMaterializedView($indicateur);

        $this->redirect()->toRoute('indicateur/lister', [], [], true);
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
            'structureService' => $this->getStructureService(),
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
                'Établissement d\'inscription'         => 'ETABLISSEMENT_ID',
                'École doctorale'       => 'ECOLE_DOCT_ID',
                'Unité de recherche'    => 'UNITE_RECH_ID',
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
        if ($indicateur->getDisplayAs() == Indicateur::STRUCTURE) {
            $headers = [
                'id'                    => 'ID',
                'Sigle'                 => 'SIGLE',
                'Libelle'               => 'LIBELLE',
                'Type'                  => 'TYPE_STRUCTURE_ID',
            ];
        }


        $records = [];
        foreach ($data as $entry) {
            $record = [];
            foreach($headers as $key => $fct) {
                $value = '';
                switch($key) {
                    case 'Établissement d\'inscription' :
                        if ($entry[$fct]) {
                            /** @var Etablissement $etablissement */
                            $etablissement = $this->getStructureService()->getStructuresConcreteByTypeAndStructureConcreteId(TypeStructure::CODE_ETABLISSEMENT, $entry[$fct]);
                            $value = $etablissement->getLibelle();
                        } else $value .= "";
                        break;
                    case 'École doctorale' :
                        if ($entry[$fct]) {
                            /** @var EcoleDoctorale $ecole */
                            $ecole = $this->getStructureService()->getStructuresConcreteByTypeAndStructureConcreteId(TypeStructure::CODE_ECOLE_DOCTORALE, $entry[$fct]);
                            $value = $ecole->getLibelle();
                        } else $value .= "";
                        break;
                    case 'Unité de recherche' :
                        if ($entry[$fct]) {
                            /** @var UniteRecherche $unite */
                            $unite = $this->getStructureService()->getStructuresConcreteByTypeAndStructureConcreteId(TypeStructure::CODE_UNITE_RECHERCHE, $entry[$fct]);
                            $value = $unite->getLibelle();
                        } else $value .= "";
                        break;
                    default:
                        $value = $entry[$fct];
                        break;
                }

                $record[] = $value;
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

