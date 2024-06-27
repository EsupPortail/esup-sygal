<?php

namespace These\Fieldset\TitreAcces;

use Application\Entity\Db\TitreAcces;
use Application\Service\AnneeUniv\AnneeUnivServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Laminas\Hydrator\HydratorInterface;
use These\Entity\Db\These;

class TitreAccesHydrator implements HydratorInterface
{

    use AnneeUnivServiceAwareTrait;
    use SourceServiceAwareTrait;

    /**
     * @param These $object
     * @return array
     */
    public function extract(object $object): array
    {
        $data['titreAccesInterneExterne'] = $object->getTitreAcces() ? $object->getTitreAcces()->getCodeTitreAccesInterneExterne() : null;
        $data['libelleTitreAcces'] = $object->getTitreAcces() ? $object->getTitreAcces()->getLibelleTitreAcces() : null;
        $data["codePaysTitreAcces"] = $object->getTitreAcces() ? $object->getTitreAcces()->getCodePaysTitreAcces() : null;

        return $data;
    }

    /**
     * @param array $data
     * @param These $object
     * @return object
     */
    public function hydrate(array $data, object $object): object
    {
        $data["codePaysTitreAcces"] = !empty($data["codePaysTitreAcces"]) ? $data["codePaysTitreAcces"] : null;
        if(!empty($object->getTitreAcces())){
            $titreAcces = $object->getTitreAcces();
            $titreAcces->setLibelleTitreAcces($data['libelleTitreAcces']);
            $titreAcces->setTitreAccesInterneExterne($data['titreAccesInterneExterne']);
            $titreAcces->setCodePaysTitreAcces($data["codePaysTitreAcces"]);
        }else{
            $titreAcces = new TitreAcces();
            $titreAcces->setLibelleTitreAcces($data['libelleTitreAcces']);
            $titreAcces->setTitreAccesInterneExterne($data['titreAccesInterneExterne']);
            $titreAcces->setThese($object);
            $titreAcces->setCodePaysTitreAcces($data["codePaysTitreAcces"]);

            $titreAcces->setSource($this->sourceService->fetchApplicationSource());
            $titreAcces->setSourceCode($this->sourceService->genereateSourceCode());
            $object->addTitreAcce($titreAcces);
        }
        return $object;
    }
}