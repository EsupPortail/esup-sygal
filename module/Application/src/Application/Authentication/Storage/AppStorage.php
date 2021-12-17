<?php

namespace Application\Authentication\Storage;

use Doctorant\Entity\Db\Doctorant;
use Application\Entity\Db\Utilisateur;
use Application\Entity\UserWrapper;
use Application\Entity\UserWrapperFactory;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Authentication\Storage\ChainableStorage;
use UnicaenAuth\Authentication\Storage\ChainEvent;
use Zend\Authentication\Exception\ExceptionInterface;

/**
 * Ajout de données utiles concernant l'utilisateur authentifié.
 *
 * Valeur associée à la clé KEY_DOCTORANT :
 * - entité Doctorant si l'utilisateur authentifié est trouvé parmi les thésards,
 * - null sinon.
 *
  * @author Unicaen
 */
class AppStorage implements ChainableStorage
{
    use UtilisateurServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    const KEY_DB_UTILISATEUR = 'db';
    const KEY_DOCTORANT = 'doctorant';

    /**
     * @var UserWrapper
     */
    private $userWrapper;

    /**
     * @var Doctorant
     */
    protected $doctorant;

    /**
     * @param ChainEvent $e
     */
    public function read(ChainEvent $e)
    {
        $userWrapperFactory = new UserWrapperFactory();
        try {
            $this->userWrapper = $userWrapperFactory->createInstanceFromStorageChainEvent($e);
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

    public function write(ChainEvent $e)
    {
        // nop
    }

    public function clear(ChainEvent $e)
    {
        // nop
    }
}