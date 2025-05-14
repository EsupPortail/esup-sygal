<?php
namespace Doctorant\Facade;

use Application\Entity\Db\TypeRapport;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Application\Service\Rapport\RapportServiceAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Inscription;
use RapportActivite\Rule\Creation\RapportActiviteCreationRuleAwareTrait;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseServiceAwareTrait;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;

class TableauDeBordDoctorantFacade
{
    use RapportActiviteServiceAwareTrait;
    use RapportActiviteCreationRuleAwareTrait;
    use RapportServiceAwareTrait;
    use TheseServiceAwareTrait;
    use PropositionTheseServiceAwareTrait;
    use AnneeUnivServiceAwareTrait;
    use EntityManagerAwareTrait;

    public function getVariablesForTableauDeBord(Doctorant $doctorant): array
    {
        $theses = $this->theseService->getRepository()->findThesesByDoctorant(
            $doctorant,
            [These::ETAT_EN_COURS, These::ETAT_SOUTENUE]
        );

        $enCours = $this->getInscriptionsEnCours($doctorant);

        $these = $theses[0] ?? null;
        $rapportsCSIParAnnee = [];
        $rapportsActiviteParAnnee = [];
        $canCreateRapportFinContrat = false;
        $anneeUnivProposition = null;
        $proposition = null;

        if ($these) {
            $typeRapport = $this->rapportService->findTypeRapportByCode(TypeRapport::RAPPORT_CSI);
            $rapportsCSIParAnnee = $this->rapportService->findRapportsParAnneesForThese($these, $typeRapport);

            $rapportsActivite = $this->rapportActiviteService->findRapportsForThese($these);
            $rapportsActiviteParAnnee = $this->rapportActiviteService->findRapportsParAnneesForThese($these);

            $this->rapportActiviteCreationRule->setRapportsExistants($rapportsActivite);
            $canCreateRapportFinContrat = $this->rapportActiviteCreationRule->canCreateRapportFinContrat();

            $proposition = $this->propositionTheseService->findOneForThese($these);
            if ($proposition && $proposition->getDate()) {
                $anneeUnivProposition = $this->anneeUnivService->fromDate($proposition->getDate());
            }
        }

        return [
            'doctorant' => $doctorant,
            'theses' => $theses,
            'enCours' => $enCours,
            'rapportsCSIParAnnee' => $rapportsCSIParAnnee,
            'rapportsActiviteParAnnee' => $rapportsActiviteParAnnee,
            'canCreateRapportFinContrat' => $canCreateRapportFinContrat,
            'anneeUnivProposition' => $anneeUnivProposition,
            'proposition' => $proposition,
        ];
    }

    private function getInscriptionsEnCours(Doctorant $doctorant): array
    {
        $inscriptions = $this->entityManager->getRepository(Inscription::class)
            ->findInscriptionsByDoctorant($doctorant);

        $enCours = [];

        foreach ($inscriptions as $inscription) {
            $session = $inscription->getSession();
            if (
                $inscription->estNonHistorise() &&
                $session &&
                !in_array($session->getEtat()->getCode(), [Etat::CODE_CLOTURER, Etat::CODE_ANNULEE], true)
            ) {
                $seances = array_filter($session->getSeances()->toArray(), fn($s) => $s->estNonHistorise());
                if (count($seances) > 0) {
                    $enCours[] = $inscription;
                }
            }
        }

        return $enCours;
    }
}