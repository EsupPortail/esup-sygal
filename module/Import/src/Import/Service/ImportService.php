<?php

namespace Import\Service;

use Application\Entity\Db\These;
use Application\Filter\EtablissementPrefixFilter;
use Import\Model\TmpActeur;
use Import\Model\TmpDoctorant;
use Import\Model\TmpThese;
use Import\Service\Traits\FetcherServiceAwareTrait;
use Import\Service\Traits\SynchroServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

/**
 * Service d'import des données provenant d'un établissement.
 *
 * Les données sont d'abord obtenues en interrogeant le web service de l'établissement en question.
 * Elles sont stockées dans les tables TMP_*.
 *
 * Ensuite, la synchronisation entre les vues SRC_* (basées sur les tables TMP_*) et les tables finales
 * est demandée au package UnicaenImport.
 *
 * @author Unicaen
 */
class ImportService
{
    use FetcherServiceAwareTrait;
    use EntityManagerAwareTrait;
    use SynchroServiceAwareTrait;

    /**
     * Liste ORDONNÉE de tous les services proposés.
     */
    const SERVICES = [
        'individu',
        'doctorant',
        'these',
        'role',
        'acteur',
        'variable',
        'structure',
        'etablissement',
        'ecole-doctorale',
        'unite-recherche',
    ];

    /**
     * Filtres autorisés, par service.
     */
    const ALLOWED_FILTERS_BY_SERVICE = [
        'doctorant' => [
            'these_id',
        ],
        'acteur' => [
            'these_id',
        ],
    ];

    private $debug = false;

    /**
     * @var string[]
     */
    private $filters = [];

    /**
     * @var string
     */
    private $sqlFilters;

    /**
     * Lance l'import de données en provenance d'un seul service d'un établissement.
     *
     *  RMQ: 'service' et 'etablissement' sont pour le moment obligatoire.
     *  RMQ: si 'source_code' est non renseigné alors il faut récupérer toutes les données
     *
     * @param string $service Nom du web service qui sera appelé (p.e. these, doctorant, ...)
     * @param string $etablissement Code de l'établissement que l'on souhaite interroger (p.e. UCN, UCR, ...)
     * @param string $sourceCode Source code éventuel de l'entité à récupérer (p.e. '12047')
     * @param array  $queryParams Filtres éventuels à appliquer
     * @return array
     */
    public function import($service, $etablissement, $sourceCode, array $queryParams = [])
    {
        $this->computeFilters($service, $sourceCode, $queryParams);

        if ($this->debug) {
            echo "SERVICE: {$service}<br/>";
            echo "ETABLISSEMENT: {$etablissement}<br/>";
            echo "SOURCE_CODE: {$sourceCode}<br/>";
        }

        /** Paramétrage du service de récupération */
        $key = $this->fetcherService->getEtablissementKey($etablissement);
        $this->fetcherService->setConfigWithPosition($key);
        if ($this->debug) {
            $this->printDebug($key);
        }

        // appel du ws
        $logs = $this->fetcherService->fetch($service, $sourceCode, $this->filters);

        // synchro UnicaenImport
        $this->synchroService->addService($service, ['sql_filter' => $this->sqlFilters]);
        $this->synchroService->synchronize();

        return $logs;
    }

    /**
     * Lance l'import de données en provenance de tous les services d'un établissement.
     *
     *  RMQ: 'etablissement' est pour le moment obligatoire.
     *
     * @param string $etablissement Code de l'établissement que l'on souhaite interroger (p.e. UCN, UCR, ...)
     * @return array
     */
    public function importAll($etablissement)
    {
        $services = static::SERVICES;

        if ($this->debug) {
            echo "SERVICE: Tous<br/>";
            echo "ETABLISSEMENT: {$etablissement}<br/>";
        }

        /** Paramétrage du service de récupération */
        $key = $this->fetcherService->getEtablissementKey($etablissement);
        $this->fetcherService->setConfigWithPosition($key);
        if ($this->debug) {
            $this->printDebug($key);
        }

        $logs = [];
        foreach ($services as $service) {
            // appel du ws
            $logs[] = $this->fetcherService->fetch($service);

            $this->synchroService->addService($service);
        }

        // synchro UnicaenImport
        $this->synchroService->synchronize();

        return $logs;
    }

    /**
     * Lance l'import pour la mise à jour d'une thèse déjà présente dans la base de données, et de ses données liées.
     *
     * Pour l'instant, les données liées se limitent à celles concernées par la génération de la page de couverture.
     *
     * @param string $etablissement Code de l'établissement que l'on souhaite interroger (p.e. UCN, UCR, ...)
     * @param string $sourceCodeThese Source code de la thèse à mettre à jour
     * @return array
     */
    public function updateThese($etablissement, $sourceCodeThese)
    {
        $f = new EtablissementPrefixFilter();
        $sourceCodeThese = $f->addPrefixTo($sourceCodeThese, $etablissement);
        /** @var These $these */
        $these = $this->entityManager->getRepository(These::class)->findOneBy(['sourceCode' => $sourceCodeThese]);
        if (! $these) {
            throw new RuntimeException("Aucune thèse trouvée avec ce source code: $sourceCodeThese");
        }

        if ($this->debug) {
            echo "SERVICE: these + dépendances<br/>";
            echo "ETABLISSEMENT: {$etablissement}<br/>";
            echo "SOURCE_CODE: {$sourceCodeThese}<br/>";
        }

        /** Paramétrage du service de récupération */
        $key = $this->fetcherService->getEtablissementKey($etablissement);
        $this->fetcherService->setConfigWithPosition($key);
        if ($this->debug) {
            $this->printDebug($key);
        }

        $logs = [];

        /**
         * Appel du WS pour mettre à jour les tables TMP_*.
         */
        // these
        $sourceCodeThese = $these->getSourceCode();
        $logs[] = $this->fetcherService->fetch('these', $sourceCodeThese);
        /** @var TmpThese $tmpThese */
        $tmpThese = $this->entityManager->getRepository(TmpThese::class)->findOneBy(['sourceCode' => $sourceCodeThese]);
        // doctorant
        $sourceCodeDoctorant = $tmpThese->getDoctorantId();
        $logs[] = $this->fetcherService->fetch('doctorant', $sourceCodeDoctorant);
        /** @var TmpDoctorant $tmpDoctorant */
        $tmpDoctorant = $this->entityManager->getRepository(TmpDoctorant::class)->findOneBy(['sourceCode' => $sourceCodeDoctorant]);
        // individu doctorant
        $sourceCodeIndividu = $tmpDoctorant->getIndividuId();
        $logs[] = $this->fetcherService->fetch('individu', $sourceCodeIndividu);
        // acteurs
        $theseId = $these->getId();
        $logs[] = $this->fetcherService->fetch('acteur', null, ['these_id' => $theseId]);
        /** @var TmpActeur[] $tmpActeurs */
        $tmpActeurs = $this->entityManager->getRepository(TmpActeur::class)->findBy(['theseId' => $sourceCodeThese]);
        // individus acteurs
        $sourceCodeIndividus = [];
        foreach ($tmpActeurs as $tmpActeur) {
            $sourceCodeIndividus[] = $sourceCodeIndividu = $tmpActeur->getIndividuId();
            $logs[] = $this->fetcherService->fetch('individu', $sourceCodeIndividu);
        }
        // ed
        $sourceCodeEcoleDoct = $tmpThese->getEcoleDoctId();
        $logs[] = $this->fetcherService->fetch('ecole-doctorale', $sourceCodeEcoleDoct);
        // ur
        $sourceCodeUniteRech = $tmpThese->getUniteRechId();
        $logs[] = $this->fetcherService->fetch('unite-recherche', $sourceCodeUniteRech);

        /**
         * Synchro UnicaenImport pour mettre à jour les tables finales.
         */
        $quotifier = function($v) { return "'$v'"; };
        $sqlFilterIndividu = sprintf("SOURCE_CODE IN (%s)", implode(', ', array_map($quotifier, $sourceCodeIndividus)));
        $this->synchroService->addService('these',
            ['sql_filter' => "SOURCE_CODE = '$sourceCodeThese'"]
        );
        $this->synchroService->addService('doctorant',
            ['sql_filter' => "SOURCE_CODE = '$sourceCodeDoctorant'"]
        );
        $this->synchroService->addService('individu',
            ['sql_filter' => "SOURCE_CODE = '$sourceCodeIndividu'"]
        );
        $this->synchroService->addService('acteur',
            ['sql_filter' => "THESE_ID = '$theseId'"]
        );
        $this->synchroService->addService('individu',
            ['sql_filter' => $sqlFilterIndividu]
        );
        $this->synchroService->addService('ecole-doctorale',
            ['sql_filter' => "SOURCE_CODE = '$sourceCodeEcoleDoct'"]
        );
        $this->synchroService->addService('unite-recherche',
            ['sql_filter' => "SOURCE_CODE = '$sourceCodeUniteRech'"]
        );
        $this->synchroService->synchronize();

        return $logs;
    }

    /**
     * Préparation des filtres éventuels, utiles à...
     *  - l'interrogation du web service.
     *  - la synchro UnicaenImport.
     *
     * @param string $service
     * @param string $sourceCode
     * @param array  $queryParams
     */
    private function computeFilters($service, $sourceCode = null, array $queryParams = [])
    {
        $this->filters = [];
        $this->sqlFilters = null;

        if (empty($queryParams)) {
            return;
        }

        if ($sourceCode !== null && ! empty($queryParams)) {
            throw new LogicException("Aucun filtre ne peut être appliqué lorsqu'un source code est spécifié");
        }

        // normalisation des clés
        $queryParams = array_combine(array_map('strtolower', array_keys($queryParams)), $queryParams);

        $filters = [];
        $sqlFilters = '';

        // respect des seuls filtres autorisés
        if (! isset(self::ALLOWED_FILTERS_BY_SERVICE[$service])) {
            throw new RuntimeException("Le service '$service' n'accepte aucun filtre");
        }
        foreach ($queryParams as $name => $value) {
            if (! in_array($name, self::ALLOWED_FILTERS_BY_SERVICE[$service])) {
                throw new RuntimeException("Le service '$service' n'accepte pas le filtre '$name'");
            }
            $filters[$name] = $value;
        }

        // validation des valeurs des filtres
        foreach ($filters as $name => $value) {
            switch ($name) {
                case 'these_id':
                    if (! is_numeric($value)) {
                        throw new RuntimeException("Le filtre '$name' n'est pas valide: entier attendu");
                    }
                    break;
                default:
                    break;
            }
        }

        // fabrication des filtres SQL pour la synchro UnicaenImport
        if ($sourceCode) {
            $sqlFilters = "SOURCE_CODE = '$sourceCode'";
        } else {
            foreach ($filters as $name => $value) {
                switch ($name) {
                    case 'these_id':
                        $sqlFilters = "THESE_ID = '$value'";
                        break;
                    default:
                        break;
                }
            }
        }

        $this->filters = $filters;
        $this->sqlFilters = $sqlFilters ?: null;
    }

    private function printDebug($key)
    {
        echo "KEY: {$key}<br/>";
        echo $this->fetcherService->getCodeEtablissement() . " | ";
        echo $this->fetcherService->getUrl() . " | ";
        echo $this->fetcherService->getProxy() . " | ";
        echo (($this->fetcherService->getVerify()) ? "true" : "false") . " <br/> ";
        echo $this->fetcherService->getUser() . " | ";
        echo $this->fetcherService->getPassword() . " | ";
    }
}