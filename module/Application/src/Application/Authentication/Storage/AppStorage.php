<?php

namespace Application\Authentication\Storage;

use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Utilisateur;
use Application\Entity\UserWrapper;
use Application\Service\Doctorant\DoctorantServiceAwareTrait;
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
        $this->userWrapper = UserWrapper::instFromStorageChainEvent($e);
        if ($this->userWrapper === null) {
            return;
        }

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