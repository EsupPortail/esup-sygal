<?php

namespace Application\Navigation;

use Application\Entity\Db\Role;
use Application\Service\UserContextServiceAwareTrait;
use Candidat\Entity\Db\Candidat;
use Doctorant\Entity\Db\Doctorant;
use HDR\Entity\Db\HDR;
use HDR\Service\HDRServiceAwareTrait;
use Individu\Entity\Db\Individu;
use Interop\Container\ContainerInterface;
use Laminas\Router\RouteMatch;
use Structure\Search\EcoleDoctorale\EcoleDoctoraleSearchFilter;
use Structure\Search\Etablissement\EtablissementSearchFilter;
use Structure\Search\UniteRecherche\UniteRechercheSearchFilter;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Util;

/**
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ApplicationNavigationFactory extends NavigationFactory
{
    use UserContextServiceAwareTrait;
    use TheseServiceAwareTrait;
    use HDRServiceAwareTrait;

    const THESE_SELECTIONNEE_PAGE_ID = 'THESE_SELECTIONNEE';
    const MA_THESE_PAGE_ID = 'MA_THESE';
    const MES_THESES_PAGE_ID = 'MES_THESES';
    const NOS_THESES_PAGE_ID = 'NOS_THESES';

    const HDR_SELECTIONNEE_PAGE_ID = 'HDR_SELECTIONNEE';
    const MA_HDR_PAGE_ID = 'MA_HDR';
    const MES_HDR_PAGE_ID = 'MES_HDR';
    const NOS_HDR_PAGE_ID = 'NOS_HDR';

    const FORMATIONS_PAGE_ID = 'FORMATIONS';
    const MES_FORMATIONS_DOCTORANT_PAGE_ID = 'MES_FORMATIONS_DOCTORANT';
    const MES_FORMATIONS_FORMATEUR_PAGE_ID = 'MES_FORMATIONS_FORMATEUR';

    const MON_ADMISSION_PAGE_ID = 'MON_ADMISSION';
    const MES_ADMISSIONS_PAGE_ID = 'MES_ADMISSIONS';
    const NOS_ADMISSIONS_PAGE_ID = 'NOS_ADMISSIONS';

    private ?Doctorant $doctorant = null;
    private ?Candidat $candidat = null;
    private ?Role $role = null;
    private ?Individu $individu = null;

    private bool $pageMaTheseCreated = false;
    private bool $pageMaHDRCreated = false;

    /**
     * @inheritDoc
     */
    protected function preparePages(ContainerInterface $container, $pages): ?array
    {
        $role = $this->userContextService->getSelectedIdentityRole();

        $this->doctorant = ($role !== null && $role->isDoctorant()) ? $this->userContextService->getIdentityDoctorant() : null;
        $this->candidat = ($role !== null && $role->isCandidatHDR()) ? $this->userContextService->getIdentityCandidatHDR() : null;
        $this->role = $role;
        $this->individu = $this->userContextService->getIdentityIndividu();

        return parent::preparePages($container, $pages);
    }

    /**
     * @inheritDoc
     */
    protected function handleParamsInjection(array &$page, RouteMatch $routeMatch = null)
    {
        parent::handleParamsInjection($page, $routeMatch);
        if (($page['visible'] ?? true) === false) {
            return $this;
        }

        /** @var \Application\RouteMatch $routeMatch */

        if (in_array('these', (array)$page['paramsInject'])) {
            if ($routeMatch === null) {
                $page['visible'] = false;
            } else {
                $these = $routeMatch->getThese();
                if ($these !== null) {
                    $this->injectThese($page, $these);
                } else {
                    $page['visible'] = false; // todo : pas vérifié si ça pose pb en cas de param de route facultatif
                }
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function processPage(array &$page, RouteMatch $routeMatch = null)
    {
        $this->handleDynamicPage($page);

        parent::processPage($page, $routeMatch);
    }

    protected function handleDynamicPage(array &$page): void
    {
        /**
         * Ma thèse
         */
        // Rôle Doctorant : génération d'une page "Ma thèse" pour chaque thèse du doctorant
        if ($protoPage = $page['pages'][$key = self::MA_THESE_PAGE_ID] ?? null) {
            if ($this->doctorant !== null) {
                $theses = $this->theseService->getRepository()->findThesesByDoctorant($this->doctorant, [These::ETAT_EN_COURS, These::ETAT_SOUTENUE]);
                /////////////////////////////////// LOUVRY Isabelle 33383 : 2 thèses E et S
                $newPages = $this->createPagesMaThese($protoPage, $theses);
                $page['pages'] = array_merge($page['pages'], $newPages);
                $page['visible'] = true;

                // si une page 'Ma thèse' est présente, la page 'Thèse sélectionnée' qui fait doublon sera supprimée
                $this->pageMaTheseCreated = true;
            }
            unset($page['pages'][$key]);
        }

        /**
         * Mes thèses
         */
        // Rôles acteurs de thèses (Dir, Codir, etc.) : génération d'une page "Mes thèses" contenant une page fille par thèse
        if ($protoPage = $page['pages'][$key = self::MES_THESES_PAGE_ID] ?? null) {
            if ($this->role !== null && $this->role->isActeurDeThese()) {
                $theses = $this->theseService->getRepository()->findThesesByActeur($this->individu, $this->role, [These::ETAT_EN_COURS, These::ETAT_SOUTENUE]);
                $newPages = $this->createPageMesTheses($protoPage, $theses);
                $page['pages'][$key]['pages'] = $newPages;
            } else {
                unset($page['pages'][$key]);
            }
        }

        /**
         * Nos thèses
         */
        // Rôles ED, UR, MDD, etc. : génération d'une page "Nos thèses" contenant une page fille indiquant la structure filtrante
        if ($protoPage = $page['pages'][$key = self::NOS_THESES_PAGE_ID] ?? null) {
            if ($this->role !== null && $this->role->isGestionnaireDeThese()) {
                $newPages = $this->createPageNosTheses($protoPage, $this->role);
                $page['pages'][$key]['pages'] = $newPages;
            } else {
                unset($page['pages'][$key]);
            }
        }

        /**
         * Thèse sélectionnée
         */
        if ($page['pages'][$key = self::THESE_SELECTIONNEE_PAGE_ID] ?? null) {
            if ($this->doctorant === null && $this->candidat !== null || $this->pageMaTheseCreated) {
                // si une page 'Ma thèse' est présente, la page 'Thèse sélectionnée' qui fait doublon est supprimée
                unset($page['pages'][$key]);
            }
        }

        /**
         * Mon HDR
         */
        // Rôle Candidat : génération d'une page "Mon HDR" pour chaque HDR du candidat
        if ($protoPage = $page['pages'][$key = self::MA_HDR_PAGE_ID] ?? null) {
            if ($this->candidat !== null) {
                $hdrs = $this->hdrService->getRepository()->findHDRByCandidat($this->candidat, [HDR::ETAT_EN_COURS, HDR::ETAT_SOUTENUE]);
                $newPages = $this->createPagesMaHDR($protoPage, $hdrs);
                $page['pages'] = array_merge($page['pages'], $newPages);
                $page['visible'] = true;

                // si une page 'Ma HDR' est présente, la page 'HDR sélectionnée' qui fait doublon sera supprimée
                $this->pageMaHDRCreated = true;
            }
            unset($page['pages'][$key]);
        }

        /**
         * Mes HDR
         */
        // Rôles acteurs de HDR (Garant...) : génération d'une page "Mes HDR" contenant une page fille par HDR
        if ($protoPage = $page['pages'][$key = self::MES_HDR_PAGE_ID] ?? null) {
            if ($this->role !== null && $this->role->isActeurDeHDR()) {
                $hdrs = $this->hdrService->getRepository()->findHDRByActeur($this->individu, $this->role, [HDR::ETAT_EN_COURS, HDR::ETAT_SOUTENUE]);
                $newPages = $this->createPageMesHDR($protoPage, $hdrs);
                if($newPages){
                    $page['pages'][$key]['pages'] = $newPages;
                }else{
                    unset($page['pages'][$key]);
                }
            } else {
                unset($page['pages'][$key]);
            }
        }

        /**
         * Nos HDR
         */
        // Rôles ED, UR, MDD, etc. : génération d'une page "Nos HDR" contenant une page fille indiquant la structure filtrante
        if ($protoPage = $page['pages'][$key = self::NOS_HDR_PAGE_ID] ?? null) {
            if ($this->role !== null && $this->role->isGestionnaireDeHDR()) {
                $newPages = $this->createPageNosHDR($protoPage, $this->role);
                $page['pages'][$key]['pages'] = $newPages;
            } else {
                unset($page['pages'][$key]);
            }
        }

        /**
         * HDR sélectionnée
         */
        if ($page['pages'][$key = self::HDR_SELECTIONNEE_PAGE_ID] ?? null) {
            if ($this->candidat === null && $this->doctorant !== null || $this->pageMaHDRCreated) {
                // si une page 'Mon HDR' est présente, la page 'HDR sélectionnée' qui fait doublon est supprimée
                unset($page['pages'][$key]);
            }
        }

        /**
         * Formations
         */
        // Rôle Doctorant : génération d'une page "Formations"
        if ($page['pages'][$key = self::FORMATIONS_PAGE_ID] ?? null) {
            if ($this->role !== null && ($this->role->getCode() === Role::CODE_ADMIN_TECH || $this->role->getCode() === Role::CODE_RESP_ED || $this->role->getCode() === Role::CODE_BDD)) {
                $page['visible'] = true;
            } else {
                unset($page['pages'][$key]);
            }
        }

        /**
         * Mes formations
         */
        // Rôle Doctorant : génération d'une page "Mes formations"
        if ($page['pages'][$key = self::MES_FORMATIONS_DOCTORANT_PAGE_ID] ?? null) {
            if ($this->role !== null && $this->role->isDoctorant()) {
                $page['visible'] = true;
            } else {
                unset($page['pages'][$key]);
            }
        }

        /**
         * Mes formations
         */
        // Rôle Formateur : génération d'une page "Mes formations"
        if ($page['pages'][$key = self::MES_FORMATIONS_FORMATEUR_PAGE_ID] ?? null) {
            if ($this->role !== null && $this->role->getCode() === Role::CODE_FORMATEUR) {
                $page['visible'] = true;
            } else {
                unset($page['pages'][$key]);
            }
        }

        /** ADMISSION */
        /**
         * Admission
         */
        // Rôle Admin Tech / Doctorant : génération d'une page "Admission"
        if ($page['pages'][$key = self::MON_ADMISSION_PAGE_ID] ?? null) {
            if ($this->role !== null && ($this->role->getCode() == Role::CODE_ADMIN_TECH || $this->role->getRoleId() == Role::ROLE_ID_ADMISSION_CANDIDAT || $this->role->getRoleId() == Role::ROLE_ID_USER)) {
                $page['visible'] = true;
            } else {
                unset($page['pages'][$key]);
            }
        }

        /**
         * Mes admissions
         */
        if ($page['pages'][$key = self::MES_ADMISSIONS_PAGE_ID] ?? null) {
            if ($this->role !== null && ($this->userContextService->getSelectedRoleDirecteurThese() || $this->userContextService->getSelectedRoleCodirecteurThese() || $this->role->getRoleId() == Role::ROLE_ID_ADMISSION_DIRECTEUR_THESE || $this->role->getRoleId() == Role::ROLE_ID_ADMISSION_CODIRECTEUR_THESE)) {
                $page['visible'] = true;
            } else {
                unset($page['pages'][$key]);
            }
        }

        /**
         * Nos admissions
         */
        if ($page['pages'][$key = self::NOS_ADMISSIONS_PAGE_ID] ?? null) {
            if ($this->role !== null && ($this->role->isEcoleDoctoraleDependant() || $this->role->isUniteRechercheDependant() || $this->role->isEtablissementDependant())) {
                $page['visible'] = true;
            } else {
                unset($page['pages'][$key]);
            }
        }
    }

    /**
     * Création d'une page "Ma thèse" pour chaque thèse du doctorant.
     *
     * @param array $protoPage
     * @param These[] $theses
     * @return array
     */
    private function createPagesMaThese(array $protoPage, array $theses): array
    {
        $newPages = [];
        foreach ($theses as $i => $these) {
            $newPage = $protoPage;
            // order
            if (isset($newPage['order'])) {
                $newPage['order'] = $newPage['order'] + $i;
            }
            // label
            if (count($theses) > 1) {
                $newPage['label'] = $newPage['label'] . ' ' . ($i + 1);
            }
            // title
            $newPage['title'] = $these->getTitre();
            // injection du paramètre de route 'these' dans la page et ses filles
            $this->setParamInPage($newPage, 'these', $these->getId());

            $newPages['ma-these-' . ($i + 1)] = $newPage;
        }

        return $newPages;
    }

    /**
     * Création d'une page fille pour chaque thèse spécifiée.
     *
     * @param array $parentPage
     * @param These[] $theses
     * @return array
     */
    private function createPageMesTheses(array $parentPage, array $theses): array
    {
        $protoPage = $parentPage['pages']['THESE'];

        $newPages = [];
        foreach ($theses as $i => $these) {
            $newPage = $protoPage;
            // order
            if (isset($newPage['order'])) {
                $newPage['order'] = $newPage['order'] + $i;
            }
            // label
            $newPage['label'] = Util::truncatedString($these->getDoctorant()->getIndividu()->getNomComplet(), 30);
            // title
            $newPage['title'] = $these->getTitre();
            // injection du paramètre de route 'these' dans la page et ses filles
            $this->setParamInPage($newPage, 'these', $these->getId());

            $newPages['these-' . ($i + 1)] = $newPage;
        }

        return $newPages;
    }

    /**
     * Création d'une page précisant la structure concernée.
     *
     * @param array $parentPage
     * @param Role $role
     * @return array
     */
    private function createPageNosTheses(array $parentPage, Role $role): array
    {
        switch (true) {
            case $role->isEcoleDoctoraleDependant():
                $ed = $role->getStructure()->getEcoleDoctorale();
                $label = $ed->getStructure()->getSigle();
                $query = [EcoleDoctoraleSearchFilter::NAME => $ed->getSourceCode()];
                break;
            case $role->isUniteRechercheDependant():
                $ur = $role->getStructure()->getUniteRecherche();
                $label = $ur->getStructure()->getCode();
                $query = [UniteRechercheSearchFilter::NAME => $ur->getSourceCode()];
                break;
            case $role->isEtablissementDependant():
                $etab = $role->getStructure()->getEtablissement();
                $label = $etab->getStructure()->getSourceCode();
                $query = [EtablissementSearchFilter::NAME => $etab->getSourceCode()];
                break;
            default:
                $label = (string) $role->getStructure();
                $query = [];
        }

        $newPages = [];

        // génération d'une page fille emmenant vers les thèses de la struture
        $protoPage = $parentPage['pages']['THESES'];
        $page = $protoPage;
        // label
        $page['label'] = "Thèses " . $label;
        // params
        $page['query'] = $page['query'] ?? [];
        $page['query'] = array_merge($page['query'], $query);
        $newPages[] = $page;

        // génération d'une page fille emmenant vers les rapports d'activité
        $protoPage = $parentPage['pages']['RAPPORTS_ACTIVITES'];
        $page = $protoPage;
        // label
        $page['label'] = "Rapports d'activité " . $label;
        // params
        $page['query'] = $page['query'] ?? [];
        $page['query'] = array_merge($page['query'], $query);
        $newPages[] = $page;

        // génération d'une page fille emmenant vers les rapports CSI
        $protoPage = $parentPage['pages']['RAPPORTS_CSI'];
        $page = $protoPage;
        // label
        $page['label'] = "Rapports CSI " . $label;
        // params
        $page['query'] = $page['query'] ?? [];
        $page['query'] = array_merge($page['query'], $query);
        $newPages[] = $page;

        // génération d'une page fille emmenant vers les soutenances
        $protoPage = $parentPage['pages']['SOUTENANCES'];
        $page = $protoPage;
        // label
        $page['label'] = "Soutenances " . $label;
        // params
        $page['query'] = $page['query'] ?? [];
        $page['query'] = array_merge($page['query'], $query);
        $newPages[] = $page;

        return $newPages;
    }

    /**
     * Création d'une page "Ma HDR" pour chaque HDR du candidat.
     *
     * @param array $protoPage
     * @param HDR[] $hdrs
     * @return array
     */
    private function createPagesMaHDR(array $protoPage, array $hdrs): array
    {
        $newPages = [];
        foreach ($hdrs as $i => $hdr) {
            $newPage = $protoPage;
            // order
            if (isset($newPage['order'])) {
                $newPage['order'] = $newPage['order'] + $i;
            }
            // label
            if (count($hdrs) > 1) {
                $newPage['label'] = $newPage['label'] . ' ' . ($i + 1);
            }

            // injection du paramètre de route 'hdr' dans la page et ses filles
            $this->setParamInPage($newPage, 'hdr', $hdr->getId());

            $newPages['ma-hdr-' . ($i + 1)] = $newPage;
        }

        return $newPages;
    }

    /**
     * Création d'une page fille pour chaque HDR spécifiée.
     *
     * @param array $parentPage
     * @param HDR[] $hdrs
     * @return array
     */
    private function createPageMesHDR(array $parentPage, array $hdrs): array
    {
        $protoPage = $parentPage['pages']['HDR'];

        $newPages = [];
        foreach ($hdrs as $i => $hdr) {
            $newPage = $protoPage;
            // order
            if (isset($newPage['order'])) {
                $newPage['order'] = $newPage['order'] + $i;
            }
            // label
            $newPage['label'] = Util::truncatedString($hdr->getCandidat()->getIndividu()->getNomComplet(), 30);
            // injection du paramètre de route 'hdr' dans la page et ses filles
            $this->setParamInPage($newPage, 'hdr', $hdr->getId());

            $newPages['hdr-' . ($i + 1)] = $newPage;
        }

        return $newPages;
    }

    /**
     * Création d'une page précisant la structure concernée.
     *
     * @param array $parentPage
     * @param Role $role
     * @return array
     */
    private function createPageNosHDR(array $parentPage, Role $role): array
    {
        switch (true) {
            case $role->isEcoleDoctoraleDependant():
                $ed = $role->getStructure()->getEcoleDoctorale();
                $label = $ed->getStructure()->getSigle();
                $query = [EcoleDoctoraleSearchFilter::NAME => $ed->getSourceCode()];
                break;
            case $role->isUniteRechercheDependant():
                $ur = $role->getStructure()->getUniteRecherche();
                $label = $ur->getStructure()->getCode();
                $query = [UniteRechercheSearchFilter::NAME => $ur->getSourceCode()];
                break;
            case $role->isEtablissementDependant():
                $etab = $role->getStructure()->getEtablissement();
                $label = $etab->getStructure()->getSourceCode();
                $query = [EtablissementSearchFilter::NAME => $etab->getSourceCode()];
                break;
            default:
                $label = (string) $role->getStructure();
                $query = [];
        }

        $newPages = [];

        // génération d'une page fille emmenant vers les HDR de la struture
        $protoPage = $parentPage['pages']['HDR'];
        $page = $protoPage;
        // label
        $page['label'] = "HDR " . $label;
        // params
        $page['query'] = $page['query'] ?? [];
        $page['query'] = array_merge($page['query'], $query);
        $newPages[] = $page;

        // génération d'une page fille emmenant vers les soutenances
        $protoPage = $parentPage['pages']['SOUTENANCES'];
        $page = $protoPage;
        // label
        $page['label'] = "Soutenances " . $label;
        // params
        $page['query'] = $page['query'] ?? [];
        $page['query'] = array_merge($page['query'], $query);
        $newPages[] = $page;

        return $newPages;
    }

    private function setParamInPage(array &$page, string $paramName, $paramValue)
    {
        /**
         * Il faut empêcher l'injection du paramètre à partir de l'URL courante.
         *
         * Exemple pour le cas du paramètre 'these' :
         *      Si on n'empêche pas l'injection, l'identifiant de la thèse (issue de l'URL) courante sera
         *      injectée dans les paramètres de la page, écrasant celui qu'on spécifie ici.
         */
        if (array_key_exists($key = 'paramsInject', $page)) {
            if (($index = array_search($paramName, $page[$key])) !== false) {
                unset($page[$key][$index]);
            }
        }

        /**
         * Spécification du paramètre dans la page.
         */
        $page['params'][$paramName] = $paramValue;

        /**
         * Traitement des pages filles.
         */
        if (array_key_exists('pages', $page)) {
            $pages = $page['pages'];
            foreach ($pages as &$p) {
                $this->setParamInPage($p, $paramName, $paramValue);
            }
            $page['pages'] = $pages;
        }
    }

    /**
     * @param array $page
     * @param These $these
     */
    private function injectThese(array &$page, These $these)
    {
        if (isset($page['label'])) {
            $page['label'] = $this->subtituteTargetAttributesPatterns($page['label'], $these);
        }
        if (isset($page['class'])) {
            $page['class'] = $this->subtituteTargetAttributesPatterns($page['class'], $these);
        }
    }

    /**
     * @param string $text
     * @param object $target
     * @return string
     */
    private function subtituteTargetAttributesPatterns(string $text, object $target): string
    {
        // recherche d'attributs entre accolades
        if (preg_match_all("!\{(.*)\}!Ui", $text, $matches)) {
            foreach ($matches[1] as $attr) {
                $method = 'get' . ucfirst($attr);
                /**
                 * Appel possible aux méthodes suivantes :
                 * - @see These::getCorrectionAutorisee()
                 */
                if (method_exists($target, $method)) {
                    $text = str_replace('{' . $attr . '}', strval($target->$method()), $text);
                }
            }
        }

        return $text;
    }
}