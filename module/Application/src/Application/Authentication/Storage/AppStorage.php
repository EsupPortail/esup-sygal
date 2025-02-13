<?php

namespace Application\Authentication\Storage;

use Application\Entity\Db\Utilisateur;
use Application\Entity\UserWrapper;
use Application\Entity\UserWrapperFactoryAwareTrait;
use Candidat\Entity\Db\Candidat;
use Candidat\Service\CandidatServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Laminas\Authentication\Exception\ExceptionInterface;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuthentification\Authentication\Storage\ChainableStorage;
use UnicaenAuthentification\Authentication\Storage\ChainEvent;

/**
 * Ajout de données utiles concernant l'utilisateur authentifié.
 *
 * Valeur associée à la clé KEY_DOCTORANT :
 * - entité Doctorant si l'utilisateur authentifié est trouvé parmi les thésards,
 * - null sinon.
 *
 *  Valeur associée à la clé KEY_CANDIDAT_HDR :
 *  - entité Candidat si l'utilisateur authentifié est trouvé parmi les candidats HDR,
 *  - null sinon.
 *
  * @author Unicaen
 */
class AppStorage implements ChainableStorage
{
    use UtilisateurServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use CandidatServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UserWrapperFactoryAwareTrait;

    const KEY_DB_UTILISATEUR = 'db';
    const KEY_DOCTORANT = 'doctorant';
    const KEY_CANDIDAT_HDR = 'candidat_hdr';

    /**
     * @var UserWrapper
     */
    private $userWrapper;

    /**
     * @var Doctorant
     */
    protected $doctorant;

    /**
     * @var Candidat
     */
    protected $candidatHDR;

    /**
     * @param ChainEvent $e
     */
    public function read(ChainEvent $e)
    {
        try {
            $this->userWrapper = $this->userWrapperFactory->createInstanceFromStorageChainEvent($e);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            return;
        }
        if ($this->userWrapper === null) {
            return;
        }

//        // pas la peine d'aller plus loin si l'établissement correspondant au domaine n'existe pas
//        $domaineEtab = $this->userWrapper->getDomainFromEppn();
//        $etablissement = $this->getEtablissementService()->getRepository()->findOneByDomaine($domaineEtab);
//        if (! $etablissement) {
//            throw new RuntimeException(
//                "Les données concernant l'utilisateur authentifié font référence au domaine '$domaineEtab' " .
//                "mais aucun établissement n'a été trouvé avec ce domaine.");
//        }

        /**
         * Collecte des données issues de la table Utilisateur.
         */
        $this->addDbUtilisateurContents($e);

        /**
         * Collecte des données au cas où l'utilisateur connecté est trouvé dans la table Doctorant.
         */
        $this->addDoctorantContents($e);

        /**
         * Collecte des données au cas où l'utilisateur connecté est trouvé dans la table Candidat.
         */
        $this->addCandidatHDRContents($e);
    }

    /**
     * @param ChainEvent $e
     */
    protected function addDbUtilisateurContents(ChainEvent $e)
    {
        try {
            $e->addContents(self::KEY_DB_UTILISATEUR, $this->fetchUtilisateur());
        } catch (ExceptionInterface $e) {
            throw new RuntimeException("Erreur imprévue rencontrée.", 0, $e);
        }
    }

    /**
     * @return null|Utilisateur
     */
    private function fetchUtilisateur()
    {
        $username = $this->userWrapper->getUsername();

        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->utilisateurService->getRepository()->findOneBy(['username' => $username]);

        return $utilisateur;
    }

    /**
     * @param ChainEvent $e
     */
    protected function addDoctorantContents(ChainEvent $e)
    {
        try {
            $e->addContents(self::KEY_DOCTORANT, $this->fetchDoctorant());
        } catch (ExceptionInterface $e) {
            throw new RuntimeException("Erreur imprévue rencontrée.", 0, $e);
        }
    }

    protected function fetchDoctorant()
    {
        if (null !== $this->doctorant) {
            return $this->doctorant;
        }

        $this->doctorant = $this->doctorantService->findOneByUserWrapper($this->userWrapper);

        return $this->doctorant;
    }

    /**
     * @param ChainEvent $e
     */
    protected function addCandidatHDRContents(ChainEvent $e)
    {
        try {
            $e->addContents(self::KEY_CANDIDAT_HDR, $this->fetchCandidatHDR());
        } catch (ExceptionInterface $e) {
            throw new RuntimeException("Erreur imprévue rencontrée.", 0, $e);
        }
    }

    protected function fetchCandidatHDR()
    {
        if (null !== $this->candidatHDR) {
            return $this->candidatHDR;
        }

        $this->candidatHDR = $this->candidatService->findOneByUserWrapper($this->userWrapper);

        return $this->candidatHDR;
    }

    public function write(ChainEvent $e)
    {
        // nop
    }

    public function clear(ChainEvent $e)
    {
        // nop
    }
}