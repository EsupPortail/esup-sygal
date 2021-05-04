<?php

namespace Application\Navigation;

use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Search\EcoleDoctorale\EcoleDoctoraleSearchFilter;
use Application\Search\Etablissement\EtablissementSearchFilter;
use Application\Search\UniteRecherche\UniteRechercheSearchFilter;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use UnicaenApp\Util;
use Zend\Router\RouteMatch;

/**
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ApplicationNavigationFactory extends NavigationFactory
{
    use UserContextServiceAwareTrait;
    use TheseServiceAwareTrait;

    const MA_THESE_PAGE_PLACEHOLDER_ID = 'MA_THESE_PLACEHOLDER';
    const MES_THESES_PAGE_PLACEHOLDER_ID = 'MES_THESES_PLACEHOLDER';
    const NOS_THESES_PAGE_PLACEHOLDER_ID = 'NOS_THESES_PLACEHOLDER';

    /**
     * @inheritDoc
     */
    protected function handleParamsInjection(array &$page, RouteMatch $routeMatch = null)
    {
        parent::handleParamsInjection($page, $routeMatch);

        /** @var \Application\RouteMatch $routeMatch */

        if (in_array('these', (array)$page['paramsInject'])) {
            $these = $routeMatch->getThese();
            if ($these !== null) {
                $this->injectThese($page, $these);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function processPage(array &$page, RouteMatch $routeMatch = null)
    {
        $this->generateDynamicChildrenPages($page);

        parent::processPage($page, $routeMatch);
    }

    /**
     * @param array $page
     */
    protected function generateDynamicChildrenPages(array &$page)
    {
        /**
         * Ma thèse
         */
        // Rôle Doctorant : génération d'une page "Ma thèse" pour chaque thèse du doctorant
        if ($protoPage = $page['pages'][$key = self::MA_THESE_PAGE_PLACEHOLDER_ID] ?? null) {
            if ($doctorant = $this->userContextService->getIdentityDoctorant()) {
                $theses = $this->theseService->getRepository()->findThesesByDoctorant($doctorant, [These::ETAT_EN_COURS]);
                /////////////////////////////////// LOUVRY Isabelle 33383 : 2 thèses E et S
                $newPages = $this->createPagesMaThese($protoPage, $theses);
                $page['pages'] = array_merge($page['pages'], $newPages);
            }
            unset($page['pages'][$key]);
        }

        /**
         * Mes thèses
         */
        // Rôles acteurs de thèses (Dir, Codir, etc.) : génération d'une page "Mes thèses" contenant une page fille par thèse
        if ($protoPage = $page['pages'][$key = self::MES_THESES_PAGE_PLACEHOLDER_ID] ?? null) {
            /** @var Role $role */
            $role = $this->userContextService->getSelectedIdentityRole();
            $individu = $this->userContextService->getIdentityIndividu();
            if ($role !== null && $role->isActeurDeThese()) {
                $theses = $this->theseService->getRepository()->findThesesByActeur($individu, $role, [These::ETAT_EN_COURS]);
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
        if ($protoPage = $page['pages'][$key = self::NOS_THESES_PAGE_PLACEHOLDER_ID] ?? null) {
            /** @var Role $role */
            $role = $this->userContextService->getSelectedIdentityRole();
            if ($role !== null && ($role->isEcoleDoctoraleDependant() || $role->isUniteRechercheDependant() || $role->isEtablissementDependant())) {
                $newPages = $this->createPageNosTheses($protoPage, $role);
                $page['pages'][$key]['pages'] = $newPages;
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
//            $newPage['params']['these'] = $these->getId();
//            // injection du paramètre de route 'these' dans les pages filles (profondeur 1)
//            if ($childrenPages = $newPage['pages'] ?? []) {
//                foreach ($childrenPages as $key => $childPage) {
//                    if (isset($childPage['params']['these'])) {
//                        $childrenPages[$key]['params']['these'] = $these->getId();
//                    }
//                }
//                $newPage['pages'] = $childrenPages;
//            }

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
                $label = $ed->getSigle();
                $query = [EcoleDoctoraleSearchFilter::NAME => $ed->getSourceCode()];
                break;
            case $role->isUniteRechercheDependant():
                $ur = $role->getStructure()->getUniteRecherche();
                $label = $ur->getStructure()->getCode();
                $query = [UniteRechercheSearchFilter::NAME => $ur->getSourceCode()];
                break;
            case $role->isEtablissementDependant():
                $etab = $role->getStructure()->getEtablissement();
                $label = $etab->getLibelle();
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
        $page['label'] = $label;
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