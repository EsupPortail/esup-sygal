<?php

namespace Structure\Form\Hydrator;

use Structure\Entity\Db\EcoleDoctorale;

class EcoleDoctoraleHydrator extends StructureConcreteHydrator
{
    /**
     * @param EcoleDoctorale $ed
     * @return array
     */
    public function extract($ed): array
    {
        $data = parent::extract($ed);

        $data['theme'] = $ed->getTheme();
        $data['offre-these'] = $ed->getOffreThese();

        return $data;
    }

    /**
     * @param array $data
     * @param EcoleDoctorale $ed
     * @return EcoleDoctorale
     */
    public function hydrate(array $data, $ed): EcoleDoctorale
    {
        $theme = (isset($data['theme']) and trim($data['theme']) !== '') ? trim($data['theme']) : null;
        $offreThese = (isset($data['offre-these']) and trim($data['offre-these']) !== '') ? trim($data['offre-these']) : null;

        if (!isset($data['id']) || $data['id'] === "") $data['id'] = null;

        /** @var EcoleDoctorale $object */
        $object = parent::hydrate($data, $ed);

        $object->setTheme($theme);
        $object->setOffreThese($offreThese);

        return $object;
    }
}