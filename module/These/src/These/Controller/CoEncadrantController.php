<?php

namespace These\Controller;

use Application\Controller\UtilisateurController;
use DateTime;
use Fichier\Service\Fichier\FichierStorageServiceAwareTrait;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\UniteRecherche;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Entity\Db\Acteur;
use These\Entity\Db\These;
use These\Form\CoEncadrant\RechercherCoEncadrantFormAwareTrait;
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\CoEncadrant\CoEncadrantServiceAwareTrait;
use These\Service\CoEncadrant\Exporter\JustificatifCoencadrements\JustificatifCoencadrementPdfExporter;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\View\Model\CsvModel;

class CoEncadrantController extends AbstractActionController
{
    use ActeurServiceAwareTrait;
    use CoEncadrantServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use FichierStorageServiceAwareTrait;
    use TheseServiceAwareTrait;
    use RechercherCoEncadrantFormAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;


    /** @var PhpRenderer */
    private $renderer;

    /**
     * @param PhpRenderer $renderer
     */
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
    }

    public function indexAction(): ViewModel
    {
        $form = $this->getRechercherCoEncadrantForm();
        $form->setAttribute('action', $this->url()->fromRoute('co-encadrant', [], [], true));
        //todo !doit remonter un acteur
        $form->setUrlCoEncadrant($this->url()->fromRoute('co-encadrant/rechercher-co-encadrant', [], [], true));
        $form->get('bouton')->setLabel("Afficher l'historique de co-encadrement");

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data['co-encadrant']['id'] !== "") {
                $this->redirect()->toRoute('co-encadrant/historique', ['co-encadrant' => $data['co-encadrant']['id']]);
            }
        }

        return new ViewModel([
            'form' => $form,
        ]);
    }

    public function rechercherCoEncadrantAction(): JsonModel
    {
        if (($term = $this->params()->fromQuery('term'))) {
            $acteurs = $this->getCoEncadrantService()->findByText($term);
            $result = [];
            foreach ($acteurs as $acteur) {
                // mise en forme attendue par l'aide de vue FormSearchAndSelect
                $label = $acteur->getIndividu()->getPrenom() . ' ' . $acteur->getIndividu()->getNomUsuel();
                $extra = ($acteur->getIndividu()->getEmailPro()) ?: $acteur->getIndividu()->getSourceCode();
                $result[$acteur->getIndividu()->getId()] = array(
                    'id' => $acteur->getId(),   // identifiant unique de l'item
                    'label' => $label,          // libellé de l'item
                    'extra' => $extra,          // infos complémentaires (facultatives) sur l'item
                );
            }
            usort($result, function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            });

            return new JsonModel($result);
        }
        exit;
    }

    public function ajouterCoEncadrantAction(): ViewModel
    {
        /** @var These $these */
        $theseId = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);

        $form = $this->getRechercherCoEncadrantForm();
        $form->setAttribute('action', $this->url()->fromRoute('co-encadrant/ajouter-co-encadrant', [], [], true));
        /** @see UtilisateurController::rechercherIndividuAction() */
        $form->setUrlCoEncadrant($this->url()->fromRoute('utilisateur/rechercher-individu', [], ["query" => []], true));
        $form->setUrlEtablisssement($this->url()->fromRoute('etablissement/rechercher', [], ["query" => []], true));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();

            if (isset($data['co-encadrant']['id'])) {
                /** @var Individu $individu */
                $individu = $this->getIndividuService()->getRepository()->find($data['co-encadrant']['id']);
                $etablissement = (isset($data['etablissement']['id']) && $data['etablissement']['id'] !== '')?$this->getEtablissementService()->getRepository()->find($data['etablissement']['id']):null;
                $this->getActeurService()->ajouterCoEncradrant($these, $individu, $etablissement);
            }
        }

        return new ViewModel([
            'title' => "Ajout d'un co-encadrant pour la thèse de " . $these->getDoctorant()->getIndividu()->getPrenom() . " " . $these->getDoctorant()->getIndividu()->getNomUsuel(),
            'form' => $form,
        ]);
    }

    public function retirerCoEncadrantAction(): Response
    {
        /** @var These $these */
        $theseId = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);
        $acteurId = $this->params()->fromRoute('co-encadrant');

        /** @var Acteur $acteur */
        $acteur = $this->getActeurService()->getRepository()->find($acteurId);
        if ($acteur !== null and $acteur->getThese() === $these) $this->getActeurService()->delete($acteur);

        return $this->redirect()->toRoute('these/identite', ['these' => $these->getId()], [], true);
    }

    public function historiqueAction(): ViewModel|Response
    {
        $coencadrant = $this->getCoEncadrantService()->getRequestedCoEncadrant($this);
        if ($coencadrant === null) {
            return $this->redirect()->toRoute('co-encadrant', [], [], true);
        }

        $theses = $this->getTheseService()->getRepository()->fetchThesesByCoEncadrant($coencadrant->getIndividu());

        $encours = [];
        $closes = [];
        foreach ($theses as $these) {
            if ($these->getEtatThese() === These::ETAT_EN_COURS) {
                $encours[] = $these;
            } else {
                $closes[] = $these;
            }
        }

        return new ViewModel([
            'coencadrant' => $coencadrant,
            'encours' => $encours,
            'closes' => $closes,
        ]);
    }

    public function genererJustificatifCoencadrementsAction(): void
    {
        $coencadrant = $this->getCoEncadrantService()->getRequestedCoEncadrant($this);
        $theses = $this->getTheseService()->getRepository()->fetchThesesByCoEncadrant($coencadrant->getIndividu());

        $logos = [];
        try {
            $logos['etablissement'] = $this->fichierStorageService->getFileForLogoStructure($coencadrant->getEtablissement()->getStructure());
        } catch (StorageAdapterException $e) {
            $logos['etablissement'] = null;
        }

        //exporter
        $export = new JustificatifCoencadrementPdfExporter($this->renderer, 'A4');
        $export->setVars([
            'coencadrant' => $coencadrant,
            'theses' => $theses,
            'logos' => $logos,
        ]);
        $export->export('justificatif_coencadrement_' . $coencadrant->getIndividu()->getId() . ".pdf");
    }

    public function genererExportCsvAction(): CsvModel
    {
        $structureType = $this->params()->fromRoute('structure-type');
        $structureId = $this->params()->fromRoute('structure-id');

        /** @var EcoleDoctorale|UniteRecherche $structure */
        $structure = null;
        if ($structureType === 'ecole-doctorale') $structure = $this->getEcoleDoctoraleService()->getRepository()->find($structureId);
        if ($structureType === 'unite-recherche') $structure = $this->getUniteRechercheService()->getRepository()->find($structureId);

        $listing = $this->getCoEncadrantService()->findCoEncadrantsByStructureConcrete($structure);

        //export
        $headers = ['Co-endrants', 'Nombre d\'encadrements actuels', 'Listing'];
        $records = [];
        foreach ($listing as $item) {
            $entry = [];
            $entry['Co-endrant'] = $item['co-encadrant']->getIndividu()->getPrenom1() . " " . $item['co-encadrant']->getIndividu()->getNomUsuel();
            $entry['Nombre d\'encadrements actuels'] = count($item['theses']);
            $entry['Listing'] = implode(';',
                array_map(function (These $t) {
                    return $t->getDoctorant()->getIndividu()->getPrenom1() . " " . $t->getDoctorant()->getIndividu()->getNomUsuel();
                }, $item['theses'])
            );
            $records[] = $entry;
        }
        $filename = (new DateTime())->format('Ymd-His') . '_coencadrants-' . str_replace(' ', '_', $structure->getStructure()->getSigle()) . '.csv';
        $CSV = new CsvModel();
        $CSV->setDelimiter(';');
        $CSV->setEnclosure('"');
        $CSV->setHeader($headers);
        $CSV->setData($records);
        $CSV->setFilename($filename);

        return $CSV;
    }
}