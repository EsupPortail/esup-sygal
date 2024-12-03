<?php

namespace Application\Entity\Db;

class Discipline {

    private int $id;
    private string $code;
    private string $libelle;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Convertit la collection d'entités spécifiée en un tableau d'options injectable dans un <select>.
     *
     * @param \Application\Entity\Db\Discipline[] $entities
     * @param string $attributeForKeys Attribut d'entité à utiliser pour générer les clés du tableau d'options
     * @return string[] id => libelle
     */
    static public function toValueOptions(iterable $entities, string $attributeForKeys = 'libelle'): array
    {
        $options = [];
        foreach ($entities as $e) {
            if ($attributeForKeys === 'libelle') {
                $options[$e->getLibelle()] = $e->getLibelle();
            } elseif ($attributeForKeys === 'code') {
                $options[$e->getCode()] = $e->getLibelle();
            } elseif ($attributeForKeys === 'id') {
                $options[$e->getId()] = $e->getLibelle();
            }
        }

        return $options;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function __toString(): string
    {
        return $this->libelle;
    }

}
