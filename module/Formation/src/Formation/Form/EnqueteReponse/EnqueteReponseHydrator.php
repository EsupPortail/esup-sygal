<?php

namespace Formation\Form\EnqueteReponse;

use Formation\Entity\Db\EnqueteQuestion;
use Formation\Entity\Db\EnqueteReponse;
use Formation\Entity\Db\Inscription;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Hydrator\HydratorInterface;

class EnqueteReponseHydrator implements HydratorInterface {
    use EntityManagerAwareTrait;

    /**
     * @param Inscription $object
     * @return array|void
     */
    public function extract($object)
    {
        /** @var EnqueteReponse[] $reponses */
        $reponses = $this->getEntityManager()->getRepository(EnqueteReponse::class)->findEnqueteReponseByInscription($object);
        $reponses =array_filter($reponses, function (EnqueteReponse $a) { return $a->getQuestion()->estNonHistorise() AND $a->estNonHistorise(); });

        $data = [];
        foreach ($reponses as $reponse) {
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
        /** @var EnqueteQuestion[] $question */
        $questions = $this->getEntityManager()->getRepository(EnqueteQuestion::class)->findAll();
        $array = [];
        foreach($questions as $question) {
            $array[$question->getId()] = $question;
        }

        foreach ($data as $name => $value) {
            [$type, $id] = explode("_", $name);
            if ($type === "select") $array[$id]->setNiveau($value);
            if ($type === "textarea") $array[$id]->setDescription($value);
        }

        return $object;
    }


}