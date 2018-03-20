<?php

namespace Application\Authentication\Storage;

use Application\Entity\AuthUserWrapper;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Utilisateur;
use Application\Service\Doctorant\DoctorantServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
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

    const KEY_DB_UTILSATEUR = 'db';
    const KEY_DOCTORANT = 'doctorant';

    /**
     * @var array
     */
    private $contents;

    /**
     * @var AuthUserWrapper
     */
    private $userWrapper;

    /**
     * @var Doctorant
     */
    protected $doctorant;

    /**
     * @param ChainEvent $e
     * @throws \Zend\Authentication\Exception\ExceptionInterface
     */
    public function read(ChainEvent $e)
    {
        $this->contents = $e->getContents();

        if (null === $this->contents['ldap'] && null === $this->contents['shib']) {
            return;
        }

        $this->userWrapper = AuthUserWrapper::inst($this->contents['ldap'] ?: $this->contents['shib']);

        /**
         * Recherche de l'utilisateur connecté dans la table Utilisateur.
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
            $e->addContents(self::KEY_DB_UTILSATEUR, $this->fetchUtilisateur());
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

        /**
         * NB: Un doctorant a la possibilité de s'authentifier :
         * - avec son numéro étudiant (Doctorant::sourceCode),
         * - avec son persopass (DoctorantCompl::persopass), seulement après qu'il l'a saisi sur la page d'identité de la thèse.
         */
        $username = $this->userWrapper->getUsername();
        $domaineEtab = $this->userWrapper->getDomainFromEppn();

        /** @var Etablissement $etablissement */
        $etablissement = $this->etablissementService->getRepository()->findOneByDomaine($domaineEtab);

        try {
            $this->doctorant = $this->doctorantService->getRepository()->findOneByUsernameAndEtab($username, $etablissement);
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs doctorants ont été trouvés avec le même username: " . $username);
        }

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