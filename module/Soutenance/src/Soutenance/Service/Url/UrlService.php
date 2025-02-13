<?php

namespace Soutenance\Service\Url;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Entity\Db\ActeurThese;
use Acteur\Service\ActeurHDR\ActeurHDRServiceAwareTrait;
use Acteur\Service\ActeurThese\ActeurTheseServiceAwareTrait;
use HDR\Entity\Db\HDR;
use RuntimeException;
use Soutenance\Controller\AvisController;
use Soutenance\Controller\PresoutenanceController;
use Soutenance\Controller\PropositionController;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use These\Entity\Db\These;

class UrlService extends \Application\Service\Url\UrlService
{
    use MembreServiceAwareTrait;
    use ActeurTheseServiceAwareTrait;
    use ActeurHDRServiceAwareTrait;

    protected string $type = Proposition::ROUTE_PARAM_PROPOSITION_THESE;

    protected ?array $allowedVariables = [
        'these',
        'hdr',
        'rapporteur',
        'avis',
        'membre',
        'soutenance',
    ];

    /**
     * @noinspection PhpUnused
     */
    public function getSoutenanceProposition() : string
    {
        $entity = $this->variables['these'] ?? $this->variables['hdr'];
        $type = $entity instanceof These ? Proposition::ROUTE_PARAM_PROPOSITION_THESE : Proposition::ROUTE_PARAM_PROPOSITION_HDR;
        /** @see PropositionTheseController::propositionAction() */
        /** @see PropositionHDRController::propositionAction() */
        return $this->fromRoute("soutenance_{$type}/proposition", [$type => $entity->getId()], ['force_canonical' => 'true'], true);
    }

    /**
     * @noinspection PhpUnused
     */
    public function getSoutenancePresoutenance() : string
    {
        $entity = $this->variables['these'] ?? $this->variables['hdr'];
        $type = $entity instanceof These ? Proposition::ROUTE_PARAM_PROPOSITION_THESE : Proposition::ROUTE_PARAM_PROPOSITION_HDR;
        /** @see PresoutenanceTheseController::presoutenanceAction() */
        /** @see PresoutenanceHDRController::presoutenanceAction() */
        return $this->fromRoute("soutenance_{$type}/presoutenance", [$type => $entity->getId()], ['force_canonical' => 'true'], true);
    }

    /**
     * @noinspection PhpUnused
     */
    public function getSermentDocteur() : string
    {
        $entity = $this->variables['these'] ?? $this->variables['hdr'];
        $type = $entity instanceof These ? Proposition::ROUTE_PARAM_PROPOSITION_THESE : Proposition::ROUTE_PARAM_PROPOSITION_HDR;
        /** @see PropositionController::genererSermentAction() */
        return $this->fromRoute("soutenance_{$type}/proposition/generer-serment", [$type => $entity->getId()], ['force_canonical' => 'true'], true);
    }

    /**
     * @noinspection PhpUnused
     */
    public function getProcesVerbal() : string
    {
        $entity = $this->variables['these'] ?? $this->variables['hdr'];
        $type = $entity instanceof These ? Proposition::ROUTE_PARAM_PROPOSITION_THESE : Proposition::ROUTE_PARAM_PROPOSITION_HDR;
        /** @see PresoutenanceController::procesVerbalSoutenanceAction */
        return $this->fromRoute("soutenance_{$type}/presoutenance/proces-verbal-soutenance", [$type => $entity->getId()], ['force_canonical' => 'true'], true);
    }

    /**
     * @noinspection PhpUnused
     */
    public function getRapportSoutenance() : string
    {
        $entity = $this->variables['these'] ?? $this->variables['hdr'];
        $type = $entity instanceof These ? Proposition::ROUTE_PARAM_PROPOSITION_THESE : Proposition::ROUTE_PARAM_PROPOSITION_HDR;
        /** @see PresoutenanceController::rapportSoutenanceAction */
        return $this->fromRoute("soutenance_{$type}/presoutenance/rapport-soutenance", [$type => $entity->getId()], ['force_canonical' => 'true'], true);
    }

    /**
     * @noinspection PhpUnused
     */
    public function getRapportTechnique() : string
    {
        $entity = $this->variables['these'] ?? $this->variables['hdr'];
        $type = $entity instanceof These ? Proposition::ROUTE_PARAM_PROPOSITION_THESE : Proposition::ROUTE_PARAM_PROPOSITION_HDR;
        /** @see PresoutenanceController::rapportTechniqueAction */
        return $this->fromRoute("soutenance_{$type}/presoutenance/rapport-technique", [$type => $entity->getId()], ['force_canonical' => 'true'], true);
    }

    /**
     * @noinspection PhpUnused
     */
    public function getUrlRapporteurDashboard() : string
    {
        /** @var These|HDR $entity */
        $entity = $this->variables['these'] ?? $this->variables['hdr'];
        $type = $entity instanceof These ? Proposition::ROUTE_PARAM_PROPOSITION_THESE : Proposition::ROUTE_PARAM_PROPOSITION_HDR;
        /** @var ActeurThese|ActeurHDR|Membre $rapporteur */
        $rapporteur = $this->variables['rapporteur'];
        $membre = $rapporteur instanceof Membre ? $rapporteur : $rapporteur->getMembre();
        $acteur = $entity instanceof These ? $this->acteurTheseService->getRepository()->findActeurForSoutenanceMembre($membre) :
            $this->acteurHDRService->getRepository()->findActeurForSoutenanceMembre($membre);
        if ($acteur) {
            $token = $this->getMembreService()->retrieveOrCreateToken($membre);
            $url_rapporteur = $this->fromRoute("soutenance_{$type}/index-rapporteur", [$type => $entity->getId()], ['force_canonical' => true], true);
            $url = $this->fromRoute('zfcuser/login', ['type' => 'token'], ['query' => ['token' => $token->getToken(), 'redirect' => $url_rapporteur, 'role' => $acteur->getRole()->getRoleId()], 'force_canonical' => true], true);
        } else {
            $url = $this->fromRoute('home');
        }
        return "<a href='".$url."' target='_blank'> Tableau de bord / Dashboard </a>";
    }

    /**
     * @noinspection PhpUnused
     */
    public function getPrerapportSoutenance() : string
    {
        $entity = $this->variables['these'] ?? $this->variables['hdr'];
        $type = $entity instanceof These ? Proposition::ROUTE_PARAM_PROPOSITION_THESE : Proposition::ROUTE_PARAM_PROPOSITION_HDR;
        $avis = $this->variables['avis'];
        $membre = $this->variables['membre'];
        /** @see AvisController::telechargerAction() */
        return $this->fromRoute("soutenance_{$type}/avis-soutenance/telecharger",
            [$type => $entity->getId(),'rapporteur' => $membre->getId(),'avis' => $avis->getId()], ['force_canonical' => 'true'], true);
    }

    /**
     * @noinspection PhpUnused
     */
    public function getSoutenanceConvocationDoctorant() : string
    {
        $entity = $this->variables['these'] ?? $this->variables['hdr'];
        $type = $entity instanceof These ? Proposition::ROUTE_PARAM_PROPOSITION_THESE : Proposition::ROUTE_PARAM_PROPOSITION_HDR;
        if ($entity === null) { throw new RuntimeException("Aucune thèse fournie"); }
        /** @see PresoutenanceTheseController::convocationDoctorantAction() */
        return $this->fromRoute("soutenance_{$type}/presoutenance/convocation-doctorant", [$type => $entity->getId()], ['force_canonical' => true], true);
    }

    /**
     * @noinspection PhpUnused
     */
    public function getSoutenanceConvocationCandidat() : string
    {
        $entity = $this->variables['these'] ?? $this->variables['hdr'];
        $type = $entity instanceof These ? Proposition::ROUTE_PARAM_PROPOSITION_THESE : Proposition::ROUTE_PARAM_PROPOSITION_HDR;
        if ($entity === null) { throw new RuntimeException("Aucune HDR fournie"); }
        /** @see PresoutenanceHDRController::convocationCandidatAction() */
        return $this->fromRoute("soutenance_{$type}/presoutenance/convocation-candidat", [$type => $entity->getId()], ['force_canonical' => true], true);
    }

    /**
     * @noinspection PhpUnused
     */
    public function getSoutenanceConvocationMembre() : string
    {
        $entity = $this->variables['these'] ?? $this->variables['hdr'];
        $type = $entity instanceof These ? Proposition::ROUTE_PARAM_PROPOSITION_THESE : Proposition::ROUTE_PARAM_PROPOSITION_HDR;
        $membre = $this->variables['membre'];
        /** @see PresoutenanceController::convocationMembreAction() */
        return $this->fromRoute("soutenance_{$type}/presoutenance/convocation-membre", [$type => $entity->getId(), 'membre' => $membre->getId()], ['force_canonical' => true], true);
    }

    /**
     * @noinspection PhpUnused
     */
    public function generateTablePrerapport() : string
    {
        $entity = $this->variables['these'] ?? $this->variables['hdr'];
        $type = $entity instanceof These ? Proposition::ROUTE_PARAM_PROPOSITION_THESE : Proposition::ROUTE_PARAM_PROPOSITION_HDR;
        $soutenance = $this->variables['soutenance'];
        $rapporteurs = $soutenance->getRapporteurs();

        $texte  = "<table>";
        $texte .= "<tr><th>Rapporteur·trice</th><th>Pré-rapport</th></tr>";
        foreach ($rapporteurs as $rapporteur) {
            $url = $this->fromRoute("soutenance_{$type}/avis-soutenance/telecharger", [$type => $entity->getId(), 'rapporteur' => $rapporteur->getId()],['force_canonical' => true], true);
            $texte .= "<tr><td>".$rapporteur->getDenomination()."</td>";
            $texte .= "<td><a href='".$url."'>Pré-rapport</a></td>";
        }
        $texte .= "</table>";
        return $texte;

    }
}