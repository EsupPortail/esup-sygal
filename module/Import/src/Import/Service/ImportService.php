<?php

namespace Import\Service;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\These;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Import\Exception\CallException;
use Import\Model\TmpActeur;
use Import\Model\TmpDoctorant;
use Import\Model\TmpThese;
use Import\Service\Traits\FetcherServiceAwareTrait;
use Import\Service\Traits\SynchroServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Log\LoggerAwareTrait;
use Zend\Log\LoggerInterface;

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
    use EtablissementServiceAwareTrait;
    use FetcherServiceAwareTrait;
    use EntityManagerAwareTrait;
    use SynchroServiceAwareTrait;
    use LoggerAwareTrait;

    /**
     * Liste ORDONNÉE de tous les services proposés.
     */
    const SERVICES = [
        'structure',
        'etablissement',
        'ecole-doctorale',
        'unite-recherche',
        'individu',
        'doctorant',
        'these',
        'these-annee-univ',
        'role',
        'acteur',
        'variable',
        // NB: 'origine-financement' n'est plus importé.
        'financement',
        'titre-acces',
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

    /**
     * @var string[]
     */
    private $filters = [];

    /**
     * @var string
     */
    private $sqlFilters;

    /**
     * @inheritdoc
     * @see EntityManagerAwareTrait
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        // propagation de l'EntityManager dans les services sous-traitants
        if ($this->fetcherService !== null) {
            $this->fetcherService->setEntityManagerForDbService($entityManager);
        }
        if ($this->synchroService !== null) {
            $this->synchroService->setEntityManager($entityManager);
        }

        return $this;
    }

    /**
     * Set logger object
     *
     * @param LoggerInterface $logger
     * @return self
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        $this->fetcherService->setLogger($this->logger);

        return $this;
    }

    /**
     * Interroge le WS d'un établissement pour obtenir sa version courante.
     *
     * @param Etablissement $etablissement Etablissement à interroger
     * @return string Ex: '1.1.0'
     */
    public function getApiVersion(Etablissement $etablissement)
    {
        $this->fetcherService->setEtablissement($etablissement);
        $json = $this->fetcherService->version();

        return $json->id;
    }

    /**
     * Lance l'import de données en provenance d'un seul service d'un établissement.
     *
     *  RMQ: 'service' et 'etablissement' sont pour le moment obligatoire.
     *  RMQ: si 'source_code' est non renseigné alors il faut récupérer toutes les données
     *
     * @param string        $service       Nom du web service qui sera appelé (p.e. these, doctorant, ...)
     * @param Etablissement $etablissement Etablissement que l'on souhaite interroger
     * @param string        $sourceCode    Source code éventuel de l'entité à récupérer (p.e. '12047')
     * @param array         $queryParams Filtres éventuels à appliquer
     * @param bool          $synchronize   Réaliser ou non la synchro SRC_XXX => XXX
     */
    public function import($service, Etablissement $etablissement, $sourceCode = null, array $queryParams = [], $synchronize = true)
    {
        $this->logger->info(sprintf("Import: service %s[%s] {", $service, $sourceCode));

        $this->computeFilters($service, $sourceCode, $queryParams);

        $this->fetcherService->setEtablissement($etablissement);
        try {
            $this->fetcherService->fetchRows($service, $this->filters);
        } catch (CallException $e) {
            if ($e->getCode() === 404) {
                throw new RuntimeException("Le service '$service' n'existe pas !", null, $e);
            } else {
                throw new RuntimeException("Erreur rencontrée lors de l'import (service: '$service')", null, $e);
            }
        }

        // synchro UnicaenImport
        if ($synchronize) {
            $this->synchroService->addService($service, ['sql_filter' => $this->sqlFilters]);
            $this->synchroService->synchronize();
        }
    }

    /**
     * Lance l'import de données en provenance de tous les services d'un établissement.
     *
     *  RMQ: 'etablissement' est pour le moment obligatoire.
     *
     * @param Etablissement $etablissement Etablissement que l'on souhaite interroger
     * @param bool          $synchronize   Réaliser ou non la synchro SRC_XXX => XXX
     * @param bool          $breakOnServiceNotFound Faut-il stopper si le service appelé n'existe pas
     */
    public function importAll(Etablissement $etablissement, $synchronize = true, $breakOnServiceNotFound = true)
    {
        $synchronizeNeeded = false;

        $services = static::SERVICES;
        foreach ($services as $service) {
            $this->fetcherService->setEtablissement($etablissement);
            try {
                $this->fetcherService->fetchRows($service);

                $this->synchroService->addService($service);
                $synchronizeNeeded = true;
            } catch (CallException $e) {
                if ($e->getCode() === 404) {
                    $message = "Le service '$service' n'existe pas !";
                    if ($breakOnServiceNotFound) {
                        throw new RuntimeException($message, null, $e);
                    } else {
                        $this->logger->alert("$message On continue tout de même.");
                    }
                } else {
                    throw new RuntimeException("Erreur rencontrée lors de l'import (service: tous)", null, $e);
                }
            }
        }

        // synchro UnicaenImport
        if ($synchronizeNeeded && $synchronize) {
            $this->synchroService->synchronize();
        }
    }

    /**
     * Lance la synchro UnicaenImport complète en base de données.
     *
     * @param string $service Nom du service correspondant à la table qui sera synchronisée (ex: these, doctorant, ...)
     */
    public function synchronize($service)
    {
        $this->synchroService->addService($service);
        $this->synchroService->synchronize();
    }

    /**
     * Lance la synchro UnicaenImport sur toutes les tables en base de données.
     */
    public function synchronizeAll()
    {
        $services = static::SERVICES;
        foreach ($services as $service) {
            $this->synchroService->addService($service);
        }

        $this->synchroService->synchronize();
    }

    /**
     * Lance l'import pour la mise à jour d'une thèse déjà présente dans la base de données, et de ses données liées.
     *
     * Pour l'instant, les données liées se limitent à celles concernées par la génération de la page de couverture.
     *
     * @param These $these
     */
    public function updateThese(These $these)
    {
        $this->fetcherService->setEtablissement($these->getEtablissement());

        /**
         * Appel du WS pour mettre à jour les tables TMP_*.
         */
        // these
        $sourceCodeThese = $these->getSourceCode();
        $this->fetcherService->fetchRows('these', ['source_code' => $sourceCodeThese]);
        /** @var TmpThese $tmpThese */
        $tmpThese = $this->entityManager->getRepository(TmpThese::class)->findOneBy(['sourceCode' => $sourceCodeThese]);
        // doctorant
        $sourceCodeDoctorant = $tmpThese->getDoctorantId();
        $this->fetcherService->fetchRows('doctorant', ['source_code' => $sourceCodeDoctorant]);
        /** @var TmpDoctorant $tmpDoctorant */
        $tmpDoctorant = $this->entityManager->getRepository(TmpDoctorant::class)->findOneBy(['sourceCode' => $sourceCodeDoctorant]);
        // individu doctorant
        $sourceCodeIndividu = $tmpDoctorant->getIndividuId();
        $this->fetcherService->fetchRows('individu', ['source_code' => $sourceCodeIndividu]);
        // acteurs
        $theseId = $these->getId();
        $this->fetcherService->fetchRows('acteur', ['these' => $these]);
        /** @var TmpActeur[] $tmpActeurs */
        $tmpActeurs = $this->entityManager->getRepository(TmpActeur::class)->findBy(['theseId' => $sourceCodeThese]);
        // individus acteurs
        $sourceCodeIndividus = [];
        foreach ($tmpActeurs as $tmpActeur) {
            $sourceCodeIndividus[] = $sourceCodeIndividu = $tmpActeur->getIndividuId();
            $this->fetcherService->fetchRows('individu', ['source_code' => $sourceCodeIndividu]);
        }
        // ed
        $sourceCodeEcoleDoct = $tmpThese->getEcoleDoctId();
        $this->fetcherService->fetchRows('ecole-doctorale', ['source_code' => $sourceCodeEcoleDoct]);
        // ur
        $sourceCodeUniteRech = $tmpThese->getUniteRechId();
        $this->fetcherService->fetchRows('unite-recherche', ['source_code' => $sourceCodeUniteRech]);

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

        // On met à jour le HISTO_MODIFICATION de la thèse pour mémoriser la date de l'import forcé qu'on vient de faire.
        // Pas super parce que normalement HISTO_MODIFICATION n'est modifiée que si l'import a mis à jour la thèse).
        try {
            $these->setHistoModification(new \DateTime());
        } catch (\Exception $e) {
            throw new RuntimeException("C'est le bouquet!");
        }
        try {
            $this->getEntityManager()->flush($these);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement en base de données", null, $e);
        }
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
        // traitement particulier pour le source code
        if ($sourceCode !== null) {
            if (! empty($queryParams)) {
                throw new LogicException("Aucun filtre ne peut être appliqué lorsqu'un source code est spécifié");
            }

            // si un source_code est spécifié, il remplace tout autre filtre
            $this->filters = ['source_code' => $sourceCode];
            $this->sqlFilters = "SOURCE_CODE = '$sourceCode'";

            return;
        }

        $this->filters = [];
        $this->sqlFilters = null;

        if (empty($queryParams)) {
            return;
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
        foreach ($filters as $name => $value) {
            switch ($name) {
                case 'these_id':
                    $sqlFilters = "THESE_ID = '$value'";
                    break;
                default:
                    break;
            }
        }

        $this->filters = $filters;
        $this->sqlFilters = $sqlFilters ?: null;
    }
}