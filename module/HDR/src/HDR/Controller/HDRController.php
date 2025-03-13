<?php
namespace HDR\Controller;

use Acteur\Entity\Db\ActeurHDR;
use Application\Controller\AbstractController;
use Application\Entity\Db\Role;
use Depot\Service\Validation\DepotValidationServiceAwareTrait;
use HDR\Entity\Db\HDR;
use HDR\Service\HDRSearchServiceAwareTrait;
use HDR\Service\HDRServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Notification\Service\NotifierServiceAwareTrait;
use Soutenance\Entity\Proposition;
use Soutenance\Provider\Template\MailTemplates;
use Soutenance\Service\Notification\SoutenanceNotificationFactoryAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\View\Model\CsvModel;

class HDRController extends AbstractController
{
    use HDRServiceAwareTrait;
    use DepotValidationServiceAwareTrait;
    use HDRSearchServiceAwareTrait;
    use SoutenanceNotificationFactoryAwareTrait;
    use NotifierServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

    /**
     * @see HDRRechercheController::indexAction()
     */
    public function indexAction(): Response
    {
        return $this->redirect()->toRoute('hdr/recherche', [], [], true);
    }

    public function detailIdentiteAction(): ViewModel
    {
        $hdr = $this->requestedHDR();

        $unite = $hdr->getUniteRecherche();
        $rattachements = [];
        if ($unite !== null) {
            $rattachements = $this->uniteRechercheService->findEtablissementRattachement($unite);
        }

        $view = new ViewModel([
            'hdr' => $hdr,
            'modifierEmailContactUrl' => $this->urlCandidat()->modifierEmailContactUrl($hdr->getCandidat(), true),
            'modifierEmailContactConsentUrl' => $this->urlCandidat()->modifierEmailContactConsentUrl(
                $hdr->getCandidat(),
                $this->url()->fromRoute(null, [], [], true)),
            'rattachements' => $rattachements
        ]);
        $view->setTemplate('hdr/hdr/identite');

        return $view;
    }

    public function demanderSaisieInfosSoutenanceAction(): Response
    {
        $hdr = $this->requestedHDR();
        try {
            $notif = $this->soutenanceNotificationFactory->createNotificationDemandeSaisieInfosSoutenance($hdr);
            if (empty($notif->getTo())) {
                throw new RuntimeException(
                    "Aucun mail pour la notification [" . MailTemplates::SOUTENANCE_HDR_DEMANDE_SAISIE_INFOS_SOUTENANCE . "]");
            }
            $this->notifierService->trigger($notif);
        } catch (\Notification\Exception\RuntimeException $e) {
            // aucun destinataire , todo : cas à gérer !
        }

        $this->flashMessenger()->addSuccessMessage("Notification envoyée au candidat");

        return $this->redirect()->toRoute("hdr/identite", ['id' => $hdr->getId()], [], true);
    }

    public function genererExportCsvAction(): Response|CsvModel
    {
        $queryParams = array_filter($this->params()->fromQuery(), function($value) {
            return !is_null($value) && $value !== '';
        });

        $this->hdrSearchService->init();
        $this->hdrSearchService->processQueryParams($queryParams);
        $qb = $this->hdrSearchService->getQueryBuilder();
        $listing = $qb->getQuery()->getResult();
        //export
        $headers = ['id', 'Civilité', 'Nom usuel', 'Prénom',
            'Nom patronymique', 'Date de naissance', 'Nationalité',
            'Adresse électronique', 'Adresse électronique personnelle', 'Version de diplôme',
            'Garant', 'Établissement', 'Unité de Recherche Code', 'Unité de Recherche',
            'Date d\'abandon', 'Date de soutenance', 'Date de fin de confidentialité',
            'État de la HDR', 'Autorisation à soutenir', 'Est confidentielle', 'Résultat'
        ];
        $records = [];
        /** @var HDR $hdr */
        foreach ($listing as $hdr) {
            $individu = $hdr->getCandidat()->getIndividu();

            $garants = $hdr->getActeursByRoleCode(Role::CODE_HDR_GARANT)->toArray();
            $garant = null;
            /** @var ActeurHDR $garant */
            foreach ($garants as $garant) {
                $garant = $garant->getIndividu();
            }

            $proposition = $hdr->getCurrentProposition();

            $entry = [];
            $entry['id'] = $hdr->getId();
            $entry['Civilité'] = $individu ? $individu->getCivilite() : null;
            $entry['Nom usuel'] = $individu ? $individu->getNomUsuel() : null;
            $entry['Prénom'] = $individu ? $individu->getPrenom() : null;
            $entry['Nom patronymique'] = $individu ? $individu->getNomPatronymique() : null;
            $entry['Date de naissance'] = $individu ? $individu->getDateNaissanceToString() : null;
            $entry['Nationalité'] = $individu ? $individu->getPaysNationalite() : null;
            $entry["Adresse électronique"] = $individu ? $individu->getEmailPro() : null;
            $entry['Adresse électronique personnelle'] = $individu ? $individu->getEmailContact() : null;
            $entry['Version de diplôme'] = $hdr->getVersionDiplome()?->getLibelleLong();
            $entry['Garant'] = $garant ?: null;
            $entry['Établissement'] = $hdr->getEtablissement();
            $entry['Unité de Recherche Code'] = $hdr->getUniteRecherche() ? $hdr->getUniteRecherche()->getCode() : null;
            $entry['Unité de Recherche'] =$hdr->getUniteRecherche() ? $hdr->getUniteRecherche()->getStructure() : null;
            $entry['Date d\'abandon'] = $hdr->getDateAbandonToString();
            $entry['Date de soutenance'] = $proposition ? $proposition->getDate()?->format('d/m/Y') : null;
            $entry['Date de fin de confidentialité'] = $hdr->getDateFinConfidentialiteToString();
            $entry['État de la HDR'] = $hdr->getEtatHDRToString();
            $entry['Autorisation à soutenir'] = $proposition->getSoutenanceAutorisee();
            $entry['Est confidentielle'] = $hdr->estConfidentielle() ? "O" : "N";
            $entry['Résultat'] = $hdr->getResultatToString();

            $records[] = $entry;
        }
        $filename = ('export_hdr.csv');
        $CSV = new CsvModel();
        $CSV->setDelimiter(';');
        $CSV->setEnclosure('"');
        $CSV->setHeader($headers);
        $CSV->setData($records);
        $CSV->setFilename($filename);

        return $CSV;
    }
}