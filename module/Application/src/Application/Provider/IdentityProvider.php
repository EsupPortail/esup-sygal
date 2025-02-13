<?php

namespace Application\Provider;

use Acteur\Entity\Db\ActeurHDR;
use Acteur\Service\ActeurHDR\ActeurHDRServiceAwareTrait;
use Application\Entity\Db\Role;
use Application\Entity\UserWrapper;
use Application\Entity\UserWrapperFactoryAwareTrait;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use BjyAuthorize\Provider\Identity\ProviderInterface;
use Candidat\Entity\Db\Candidat;
use Candidat\Service\CandidatServiceAwareTrait;
use Doctorant\Service\DoctorantServiceAwareTrait;
use HDR\Entity\Db\HDR;
use Individu\Entity\Db\IndividuRole;
use Laminas\Authentication\AuthenticationService;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Acteur\Entity\Db\ActeurThese;
use These\Entity\Db\These;
use Acteur\Service\ActeurThese\ActeurTheseServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenAuthentification\Provider\Identity\ChainableProvider;
use UnicaenAuthentification\Provider\Identity\ChainEvent;

/**
 * Service chargé de fournir tous les rôles que possède l'identité authentifiée.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class IdentityProvider implements ProviderInterface, ChainableProvider
{
    use ActeurTheseServiceAwareTrait;
    use ActeurHDRServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use UserWrapperFactoryAwareTrait;
    use CandidatServiceAwareTrait;

    private $roles;

    /**
     * @var UserWrapper
     */
    private $userWrapper;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @param AuthenticationService $authenticationService
     * @return IdentityProvider
     */
    public function setAuthenticationService(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function injectIdentityRoles(ChainEvent $event)
    {
        $event->addRoles($this->getIdentityRoles());
    }

    /**
     * Collecte tous les rôles de l'utilisateur authentifié.
     *
     * @return Role[]
     */
    public function getIdentityRoles()
    {
        if (! $this->authenticationService->hasIdentity()) {
            return [];
        }

        if ($this->roles !== null) {
            return $this->roles;
        }

        /** @var array $identity */
        $identity = $this->authenticationService->getIdentity();

        try {
            $this->userWrapper = $this->userWrapperFactory->createInstanceFromIdentity($identity);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            return [];
        }
        if ($this->userWrapper === null) {
            return [];
        }

        $roleAuthentifie = $this->applicationRoleService->getRepository()->findByCode('user');

        $this->roles = array_merge(
            [$roleAuthentifie],
            $this->getRolesFromActeur(),
            $this->getRolesFromIndividuRole(),
            $this->getRolesFromDoctorant(),
            $this->getRolesFromCandidatHDR()
        );

// Lignes mises en commentaire car revient à considérer le rôle "BdD UCN" identique au rôle "BdD URN" !
// La question est: pourquoi avoir fait ça ?
//        // suppression des doublons en comparant le __toString() de chaque Role
//        $this->roles = array_unique($this->roles, SORT_STRING);

        return $this->roles;
    }

    /**
     * Rôles découlant de la présence de l'utilisateur dans la table Acteur.
     *
     * @return Role[]
     */
    private function getRolesFromActeur(): array
    {
        $individu = $this->userWrapper->getIndividu();
        if ($individu === null) {
            return [];
        }

        $roles = [];

        $acteurs = $this->acteurTheseService->getRepository()->findActeursForIndividu($individu);
        if ($acteurs) {
            $acteursDirecteurThese = $this->acteurTheseService->filterActeursDirecteurThese($acteurs);
            $acteursCoDirecteurThese = $this->acteurTheseService->filterActeursCoDirecteurThese($acteurs);
            $acteursPresidentJury = $this->acteurTheseService->filterActeursPresidentJury($acteurs);
            $acteursRapporteurJury = $this->acteurTheseService->filterActeursRapporteurJury($acteurs);
            $roles = array_merge($roles, array_map(
                function (ActeurThese $a) {
                    return $a->getRole();
                },
                array_merge($acteursDirecteurThese, $acteursCoDirecteurThese, $acteursPresidentJury, $acteursRapporteurJury)
            ));
        }

        $acteursHDR = $this->acteurHDRService->getRepository()->findActeursForIndividu($individu);
        if ($acteursHDR) {
            $acteursGarants = $this->acteurHDRService->filterActeursGarantHDR($acteursHDR);
            $acteursPresidentJury = $this->acteurHDRService->filterActeursPresidentJury($acteursHDR);
            $acteursRapporteurJury = $this->acteurHDRService->filterActeursRapporteurJury($acteursHDR);
            $roles = array_merge($roles, array_map(
                function (ActeurHDR $a) {
                    return $a->getRole();
                },
                array_merge($acteursGarants, $acteursPresidentJury, $acteursRapporteurJury)
            ));
        }

        return array_unique($roles);
    }

    /**
     * Rôles découlant de la présence dans IndividuRole.
     *
     * @return Role[]
     */
    private function getRolesFromIndividuRole()
    {
        // peut-être disposons-nous de l'Individu (cas d'une authentification locale)
        $individu = $this->userWrapper->getIndividu();

        $individuRoles = [];
        if ($individu !== null) {
            $individuRoles = $this->applicationRoleService->findIndividuRolesByIndividu($individu);
        } else {
            $id = $this->userWrapper->getSupannId();
            if ($id) {
                $pattern = $this->sourceCodeStringHelper->generateSearchPatternForAnyPrefix($id);
                $individuRoles = $this->applicationRoleService->findIndividuRolesByIndividuSourceCodePattern($pattern);
            }
        }

        usort($individuRoles, IndividuRole::sorter());

        $roles = array_map(function(IndividuRole $role) {
            return $role->getRole();
        }, $individuRoles);

        return $roles;
    }

    /**
     * Rôles découlant de la présence de l'utilisateur dans la table Doctorant et de l'existence d'une thèse pour
     * ce doctorant.
     *
     * @return Role[]
     */
    private function getRolesFromDoctorant(): array
    {
        $doctorant = $this->doctorantService->findOneByUserWrapper($this->userWrapper);

        if (! $doctorant) {
            return [];
        }

        // le doctorant doit avoir une thèse en cours ou soutenue
        $theses = array_filter($doctorant->getTheses(), fn(These $these) => in_array($these->getEtatThese(), [These::ETAT_EN_COURS, These::ETAT_SOUTENUE]));
        if (! $theses) {
            return [];
        }

        $role = $this->applicationRoleService->getRepository()
            ->findOneByCodeAndStructureConcrete(Role::CODE_DOCTORANT, $doctorant->getEtablissement());

        return [$role];
    }

    /**
     * Rôles découlant de la présence de l'utilisateur dans la table Candidat_HDR et de l'existence d'une HDR pour
     * ce candidat.
     *
     * @return Role[]
     */
    private function getRolesFromCandidatHDR(): array
    {
        /** @var Candidat|null $candidat */
        $candidat = $this->candidatService->findOneByUserWrapper($this->userWrapper);

        if (! $candidat) {
            return [];
        }

        // le candidat doit avoir une HDR en cours ou soutenue
        $hdrs = array_filter($candidat->getHDRs(), fn(HDR $hdr) => in_array($hdr->getEtatHDR(), [HDR::ETAT_EN_COURS, HDR::ETAT_SOUTENUE]));
        if (! $hdrs) {
            return [];
        }

        $role = $this->applicationRoleService->getRepository()
            ->findOneByCodeAndStructureConcrete(Role::CODE_HDR_CANDIDAT, $candidat->getEtablissement());

        return [$role];
    }
}