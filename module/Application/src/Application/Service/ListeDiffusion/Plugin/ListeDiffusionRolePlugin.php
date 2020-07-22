<?php

namespace Application\Service\ListeDiffusion\Plugin;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;

class ListeDiffusionRolePlugin extends ListeDiffusionAbstractPlugin
{
    /**
     * Rôle concerné.
     *
     * @var Role
     */
    protected $role;

    /**
     * @inheritDoc
     */
    public function canHandleListe()
    {
        $this->parser
            ->setListe($this->liste)
            ->parse();

        return $this->parser->isTypeListeRole();
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->parser
            ->setListe($this->liste)
            ->parse();

        $this->role = strtoupper($this->parser->getRole());
    }

    /**
     * Génération du contenu du fichier attendu par Sympa pour obtenir les ABONNÉS d'une liste de diffusion.
     *
     * Le contenu retourné contient une adresse électronique par ligne.
     *
     * @return string
     */
    public function createMemberIncludeFileContent()
    {
        $entities = $this->fetchIndividuWithRole();
        $this->extractEmailsFromEntities($entities);

        return $this->createFileContent();
    }

    /**
     * Génération du contenu du fichier attendu par Sympa pour obtenir les PROPRIÉTAIRES d'une liste de diffusion.
     *
     * Le contenu retourné contient une adresse électronique par ligne.
     *
     * @return string
     */
    public function createOwnerIncludeFileContent()
    {
        $entities = $this->fetchProprietaires();
        $this->extractEmailsFromEntities($entities);

        return $this->createFileContent();
    }

    /**
     * @return Individu[]
     */
    private function fetchIndividuWithRole()
    {
        return $this->individuService->getRepository()->findByRole($this->role);
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function generateResultFileName($prefix)
    {
        return sprintf('%sinclude_%s.inc',
            $prefix,
            $this->role->getCode()
        );
    }
}