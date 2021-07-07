<?php

namespace Formation\Form\EnqueteReponse;

use Doctrine\Common\Collections\ArrayCollection;
use Formation\Entity\Db\EnqueteQuestion;
use Formation\Entity\Db\EnqueteReponse;
use Formation\Entity\Db\Inscription;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Hydrator\HydratorInterface;

class EnqueteReponseHydrator implements HydratorInterface {
    use EntityManagerAwareTrait;

    /**
     * @param ArrayCollection $object
     * @return array|void
     */
    public function extract($object)
    {
        $data = [];
        foreach ($object as $item) {
            [$question, $reponse] = $item;

            $data["select_".$reponse->getQuestion()->getId()] = $reponse->getNiveau();
            $data["textarea_".$reponse->getQuestion()->getId()] = $reponse->getDescription();
        }
        return $data;

    }

    /**
     * @param array $data
     * @param Inscription $object
     * @return Inscription|void
     */
    public function hydrate(array $data, $object)
    {
        foreach($object as $item) {
            [$question, $reponse] = $item;

            $select_id = "select_" . $question->getId();
            $reponse->setNiveau($data[$select_id]);
            $textarea_id = "textarea_" . $question->getId();
            $reponse->setDescription($data[$textarea_id]);
        }

        return $object;
    }


}