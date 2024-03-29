<?php

namespace Indicateur\Controller;

use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\TypeStructure;
use Structure\Entity\Db\UniteRecherche;
use Application\Service\AnomalieServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Individu\Service\IndividuServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use DateTime;
use Indicateur\Form\IndicateurForm;
use Indicateur\Model\Indicateur;
use Indicateur\Service\IndicateurServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\View\Model\CsvModel;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class IndicateurController extends AbstractActionController {
    use IndividuServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use AnomalieServiceAwareTrait;
    use IndicateurServiceAwareTrait;
    use StructureServiceAwareTrait;

    /**
     * @var IndicateurForm
     */
    private $indicateurForm;

    /**
     * @param IndicateurForm $indicateurForm
     */
    public function setIndicateurForm(IndicateurForm $indicateurForm)
    {
        $this->indicateurForm = $indicateurForm;
    }

    /**
     * @return ViewModel
     * @throws \Exception
     */
    public function indexAction() : ViewModel
    {
        $resultats = [];

        $indicateurs = $this->getIndicateurService()->findAll();
        $erreurs = '';

        foreach ($indicateurs as $indicateur) {
            $id = $indicateur->getId();

            if ($indicateur->isActif()) {
                $resultats[$indicateur->getId()] = null;
                try {
                    $resultats[$indicateur->getId()] = $this->getIndicateurService()->fetch($indicateur->getId());
                } catch (RuntimeException $e) {
                    $erreurs .= "Problème de récupération de l'indicateur #".$indicateur->getId() . "<br/>";
                }
            }
        }

        return new ViewModel([
                'indicateurs' => $indicateurs,
                'resultats'   => $resultats,
                'erreurs' => $erreurs,
            ]
        );
    }

    public function listerIndicateurAction() : ViewModel
    {
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
        $form = $this->indicateurForm;
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
                            $etablissement = $this->getStructureService()->findStructureConcreteByTypeAndStructureConcreteId(TypeStructure::CODE_ETABLISSEMENT, $entry[$fct]);
                            $value = $etablissement->getStructure()->getLibelle();
                        } else $value .= "";
                        break;
                    case 'École doctorale' :
                        if ($entry[$fct]) {
                            /** @var EcoleDoctorale $ecole */
                            $ecole = $this->getStructureService()->findStructureConcreteByTypeAndStructureConcreteId(TypeStructure::CODE_ECOLE_DOCTORALE, $entry[$fct]);
                            $value = $ecole->getStructure()->getLibelle();
                        } else $value .= "";
                        break;
                    case 'Unité de recherche' :
                        if ($entry[$fct]) {
                            /** @var UniteRecherche $unite */
                            $unite = $this->getStructureService()->findStructureConcreteByTypeAndStructureConcreteId(TypeStructure::CODE_UNITE_RECHERCHE, $entry[$fct]);
                            $value = $unite->getStructure()->getLibelle();
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

