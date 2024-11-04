<?php

namespace Application\Service\TitreAcces;

use Application\Entity\Db\Repository\TitreAccesRepository;
use Application\Entity\Db\TitreAcces;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;

class TitreAccesService extends BaseService
{
    use SourceServiceAwareTrait;

    /**
     * @return TitreAccesRepository
     */
    public function getRepository(): TitreAccesRepository
    {
        /** @var TitreAccesRepository $repo */
        $repo = $this->entityManager->getRepository(TitreAcces::class);

        return $repo;
    }

    /**
     * @return TitreAcces
     */
    public function newTitreAcces(): TitreAcces
    {
        $titreAcces = new TitreAcces();
        $titreAcces->setSource($this->sourceService->fetchApplicationSource());
        $titreAcces->setSourceCode($this->sourceService->genereateSourceCode());

        return $titreAcces;
    }

    /**
     * Récupérer les types distincts pour les utiliser dans un formulaire select
     */
    public function getTypeEtabOptions(): array
    {
        $distinctTypes = $this->getRepository()->findDistinctTypeEtabTitreAcces();

        $normalizedResults = [];
        $filteredResults = [];

        foreach ($distinctTypes as $row) {
            $normalizedValue = strtolower($this->removeAccents($row['typeEtabTitreAcces']));

            if (!in_array($normalizedValue, $normalizedResults)) {
                $normalizedResults[] = $normalizedValue;
                $filteredResults[] = $row;
            }
        }

        $options = [];
        foreach ($filteredResults as $type) {
            $options[$type['typeEtabTitreAcces']] = $type['typeEtabTitreAcces'];
        }

        return $options;
    }

    private function removeAccents($string): array|string
    {
        $search = ['à', 'â', 'ä', 'á', 'ã', 'å', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ó', 'ò', 'ô', 'ö', 'õ', 'ú', 'ù', 'û', 'ü', 'ç', 'ñ'];
        $replace = ['a', 'a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'c', 'n'];

        return str_replace($search, $replace, $string);
    }
}