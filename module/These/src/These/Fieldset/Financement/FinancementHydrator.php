<?php

namespace These\Fieldset\Financement;

use Application\Entity\Db\Financement;
use Application\Entity\Db\OrigineFinancement;
use Application\Entity\Db\Source;
use Application\Service\Financement\FinancementServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Laminas\Hydrator\HydratorInterface;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use These\Entity\Db\These;

class FinancementHydrator implements HydratorInterface //extends DoctrineObject
{
    use EtablissementServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use FinancementServiceAwareTrait;
    use SourceServiceAwareTrait;

    /**
     * @param These|object $object
     * @return array
     */
    public function extract(object $object): array
    {
        $data = [];

        /** @var Financement  $financement */
        foreach ($object->getFinancements() as $financement) {
            $data["origineFinancement"][] = $financement->getOrigineFinancement()->getId();
        }

        return $data;
    }

    /**
     * @param array $data
     * @param These $object
     * @return These
     */
    public function hydrate(array $data, object $object): These
    {
        $source = $this->sourceService->getEntityManager()->getRepository(Source::class)->find(1);
        if(isset($data["origineFinancement"])){
            //Si aucun financement n'est sélectionné, on retire les potentiels financements déjà enregistrés
            if(count($data["origineFinancement"]) === 1 && $data["origineFinancement"][0] === ""){
                foreach ($object->getFinancements() as $financement) {
                    $object->removeFinancement($financement);
//                    $this->financementService->delete($financement);
                }
            }else{
                foreach ($data["origineFinancement"] as $idOrigineFinancement) {
                    $origineFinancement = $this->financementService->getEntityManager()->getRepository(OrigineFinancement::class)->find($idOrigineFinancement);
                    $financement = $this->financementService->findFinancementByTheseAndOrigineFinancement($object, $origineFinancement);

                    // Si le financement est déjà associé à la thèse, le mettre à jour
                    if ($financement) {
                        $this->financementService->update($financement);
                    }else{
                        $financement = new Financement();
                        $financement->setThese($object);
                        $financement->setAnnee(2023);
                        $financement->setSource($source);
                        $financement->setSourceCode($source->getCode().rand(1, 1000));
                        $financement->setOrigineFinancement($origineFinancement);
                        $financement->setComplementFinancement($data["complementFinancement"]);
                        $financement->setQuotiteFinancement($data["quotiteFinancement"]);
                        if($data["annee"]) $financement->setAnnee($data["annee"]);
                        $this->financementService->create($financement);
                    }
                }
                /** @var Financement $financement */
                foreach ($object->getFinancements() as $financement) {
                    $idOrigineFinancement = $financement->getOrigineFinancement()->getId();

                    // Vérifier si l'identifiant de l'origine du financement associé est présent dans les identifiants des origines de financements sélectionnés dans le formulaire
                    if (!in_array($idOrigineFinancement, $data["origineFinancement"])) {
                        // L'origine de financement n'est pas sélectionné dans le formulaire, donc le supprimer de l'entité These
                        $object->removeFinancement($financement);

                        $this->financementService->delete($financement);
                    }
                }
            }
        }
        return $object; //parent::hydrate($data,$object);
    }
}