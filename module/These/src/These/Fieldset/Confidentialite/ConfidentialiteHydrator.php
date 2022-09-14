<?php

namespace These\Fieldset\Confidentialite;

use DateTime;
use Laminas\Hydrator\HydratorInterface;
use These\Entity\Db\These;

class ConfidentialiteHydrator implements HydratorInterface
{
    /**
     * @param These|object $object
     * @return array
     */
    public function extract(object $object): array
    {
        $data['confidentialite'] = ($object->getDateFinConfidentialite() !== null) ? 1 : 0;
        $data['fin-confidentialite'] = ($object->getDateFinConfidentialite()) ? $object->getDateFinConfidentialite()->format('Y-m-d') : null;

        return $data;
    }

    /**
     * @param array $data
     * @param These|object $object
     * @return \These\Entity\Db\These
     */
    public function hydrate(array $data, object $object): These
    {
        $conf = (isset($data['confidentialite']) and $data['confidentialite'] == true);
        $date = (isset($data['fin-confidentialite'])) ? DateTime::createFromFormat('Y-m-d', $data['fin-confidentialite']) : null;

        $object->setDateFinConfidentialite($conf ? $date : null);

        return $object;
    }


}