<?php

namespace These\Form\TheseSaisie;

use Application\Entity\Db\Role;
use DateTime;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Laminas\Hydrator\HydratorInterface;
use PhpParser\Comment\Doc;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Entity\Db\Acteur;
use These\Entity\Db\These;
use These\Service\Acteur\ActeurServiceAwareTrait;

class TheseSaisieHydrator  implements HydratorInterface //extends DoctrineObject
{
    use ActeurServiceAwareTrait;
    use QualiteServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;

    /**
     * @param These $object
     * @return array
     */
    public function extract(object $object): array
    {

        $data = [
            'generalites' => $object,
            'structures' => $object,
            'encadrements' => $object,
            'direction' => $object,
//            'financements' => $object
        ];

        return $data;
    }

    /**
     * @param array $data
     * @param These $object
     * @return These|void
     */
    public function hydrate(array $data, object $object): object
    {
        return $object;
    }
}