<?php

namespace Application\Controller;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\SourceInterface;
use Application\Entity\Db\These;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

class TheseFiltersController extends AbstractController
{
    use VariableServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;

    /**
     * @return Response|ViewModel
     */
    public function filtersAction()
    {
        $etatThese = $this->params()->fromQuery('etatThese');
        $etablissement = $this->params()->fromQuery('etablissement');
        $ecoleDoctorale = $this->params()->fromQuery('ecoleDoctorale');
        $uniteRecherche = $this->params()->fromQuery('uniteRecherche');
        $anneePremiereInscription = $this->params()->fromQuery('anneePremiereInscription');

        $etatsThese = $this->fetchEtatsTheseOptions();
        $etablissements = $this->fetchEtablissementsOptions();
        $ecolesDoctorales = $this->fetchEcolesDoctorales();
        $unitesRecherches = $this->fetchUnitesRecherches();
        $anneesPremiereInscription = $this->fetchAnneesInscription();

        return new ViewModel([
            'etablissements'            => $etablissements,
            'etablissement'             => $etablissement,
            'etatsThese'                => $etatsThese,
            'etatThese'                 => $etatThese,
            'ecolesDoctorales'          => $ecolesDoctorales,
            'ecoleDoctorale'            => $ecoleDoctorale,
            'unitesRecherches'          => $unitesRecherches,
            'uniteRecherche'            => $uniteRecherche,
            'anneesPremiereInscription' => $anneesPremiereInscription,
            'anneePremiereInscription'  => $anneePremiereInscription,]);
    }

    private function fetchEtatsTheseOptions()
    {
        $etatsThese = [
            ['value' => '',                          'label' => _("Tous")],
            ['value' => $v = These::ETAT_EN_COURS,   'label' => _(These::$etatsLibelles[$v])],
            ['value' => $v = These::ETAT_ABANDONNEE, 'label' => _(These::$etatsLibelles[$v])],
            ['value' => $v = These::ETAT_SOUTENUE,   'label' => _(These::$etatsLibelles[$v])],
            ['value' => $v = These::ETAT_TRANSFEREE, 'label' => _(These::$etatsLibelles[$v])],];

        return $etatsThese;
    }

    private function fetchEtablissementsOptions()
    {
        /**
         * @var Etablissement[] $etablissements
         * $etablissements stocke la liste des établissements qui seront utilisés pour le filtrage
         * les critères sont les suivants:
         * - être un établissement crée par sygal (et ne pas liste les établissements de co-tutelles)
         * - ne pas être des établissements provenant de substitutions
         * - ne pas être la COMUE ... suite à l'interrogation obtenue en réunion
         */
        $etablissements = $this->etablissementService->getEtablissementsBySource(SourceInterface::CODE_SYGAL);
        $etablissements = array_filter($etablissements, function (Etablissement $etablissement) { return count($etablissement->getStructure()->getStructuresSubstituees())==0; });
        $etablissements = array_filter($etablissements, function (Etablissement $etablissement) { return $etablissement->getSigle() != "NU";});

        // mise en forme
        $options = [];
        foreach ($etablissements as $etablissement) {
            $options[] = ['value' => $etablissement->getCode(), 'label' => $etablissement->getSigle()];
        }

        return self::addEmptyOption($options, 'Tous');
    }

    private function fetchEcolesDoctorales()
    {
        $eds = $this->ecoleDoctoraleService->getEcolesDoctorales();

        // mise en forme
        $options = [];
        foreach ($eds as $ed) {
            $options[] = ['value' => $ed->getSourceCode(), 'label' => $ed->getSigle(), 'subtext' => $ed->getLibelle()];
        }
        usort($options, function($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        return self::addEmptyOption($options, "Toutes");
    }

    private function fetchUnitesRecherches()
    {
        $urs = $this->uniteRechercheService->getUnitesRecherches();

        // mise en forme
        $options = [];
        foreach ($urs as $ur) {
            $options[] = ['value' => $ur->getSourceCode(), 'label' => $ur->getSigle(), 'subtext' => $ur->getLibelle()];
        }
        usort($options, function($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        return self::addEmptyOption($options, "Toutes");
    }

    private function fetchAnneesInscription()
    {
        $annees = $this->theseService->getRepository()->fetchDistinctAnneesPremiereInscription();
        $annees = array_reverse(array_filter($annees));

        // mise en forme
        $options = [];
        foreach ($annees as $annee) {
            $options[] = ['value' => $annee, 'label' => $annee];
        }

        // ajout option spéciale pour année === null
        $options[] = ['value' => 'NULL', 'label' => "Inconnue"];

        return self::addEmptyOption($options, "Toutes");
    }

    static private function addEmptyOption(array $options, $label = "Tous")
    {
        $emptyOption = ['value' => '', 'label' => $label];

        return array_merge([$emptyOption], $options);
    }
}