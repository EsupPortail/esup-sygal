<?php

namespace Depot\Rule;

use Application\Constants;
use Application\Rule\AbstractRule;
use DateTime;
use Depot\Entity\Db\Diffusion;
use Depot\Feature\FournirExemplairePapierTheseFeature;
use Depot\Rule\Result\RemiseExemplairePapierRequiredResult;
use Feature\FeatureAwareInterface;
use Feature\FeatureAwareTrait;

/**
 * Règle métier concernant la remise d'un exemplaire papier de la thèse.
 */
class RemiseExemplairePapierRule extends AbstractRule implements FeatureAwareInterface
{
    use FeatureAwareTrait;

    private Diffusion $diffusion;

    public function setDiffusion(Diffusion $diffusion): static
    {
        $this->diffusion = $diffusion;
        return $this;
    }

    public function execute(): static
    {
        // rien n'est fait ici
        return $this;
    }

    /**
     * Retourne un booléen indiquant si la remise d'un exemplaire papier est requise.
     *
     * Si aucune date d'observation n'est spécifée, c'est la date de création ou dernière modification de
     * l'autorisation de MEL qui est utilisée.
     */
    public function isRemiseExemplairePapierRequired(?DateTime $dateObservation = null): bool
    {
        $dateObservation = $dateObservation ?: $this->diffusion->getHistoModification() ?: $this->diffusion->getHistoCreation();

        // On teste si la feature était activée au moment de l'autorisation de MEL,
        // si la feature n'était pas activée, c'est niet !
        $isFeatureEnabled = $this->getFournirExemplairePapierTheseFeature()->isEnabled($dateObservation);
        if (!$isFeatureEnabled) {
            return false;
        }

        // d'après la réponse à l'autorisation, la remise d'un exemplaire papier est-elle requise ?
        return $this->autorisMelEntraineRemiseExemplairePapier($this->diffusion->getAutorisMel());
    }

    /**
     * Retourne une instance de {@see RemiseExemplairePapierRequiredResult} représentant le résultat d'interrogation
     * de la règle métier concernant la remise d'un exemplaire papier de la thèse.
     *
     * Si aucune date d'observation n'est spécifée, c'est la date de création ou dernière modification de
     * l'autorisation de MEL qui est utilisée.
     */
    public function getRemiseExemplairePapierRequired(?DateTime $dateObservation = null): RemiseExemplairePapierRequiredResult
    {
        $result = new RemiseExemplairePapierRequiredResult();

        if ($dateObservation !== null) {
            $dateObservationInfo = null;
        } else {
            $dateObservation = $this->diffusion->getHistoModification() ?: $this->diffusion->getHistoCreation();
            $dateObservationInfo = "date de dernière modification de l'autorisation de MEL";
        }

        $result->dateObservation = $dateObservation;
        $result->dateObservationDesc = $dateObservationInfo;

        $result->isRequired = $this->isRemiseExemplairePapierRequired($dateObservation);
        $result->isRequiredNow = $this->isRemiseExemplairePapierRequired($now = new DateTime());

        $result->message = null;

        if ($result->isRequired === false && $result->isRequiredNow === true) {
            if ($dateObservation < $now) {
                $result->message = sprintf(
                    "À la date du %s (%s), la remise d'un exemplaire papier n'était pas requise.",
                    $dateObservation->format(Constants::DATE_FORMAT),
                    $dateObservationInfo,
                );
            }
        } elseif ($result->isRequired === true && $result->isRequiredNow === false) {
            if ($dateObservation < $now) {
                $result->message = sprintf(
                    "À la date du %s (%s), la remise d'un exemplaire papier était requise.",
                    $dateObservation->format(Constants::DATE_FORMAT),
                    $dateObservationInfo,
                );
            }
        }

        return $result;
    }

    /**
     * Détermine si le changmenent de réponse à l'autorisation de MEL est tel que cela
     * provoque une modification de la nécessité ou non de fournir un exemplaire papier.
     *
     * @deprecated Pas utilisé ! Supprimer ? Cf. {@see \Depot\Service\These\DepotService::updateDiffusion()}
     */
    public function isChangementAutorisMelImpacteRemiseExemplairePapier(int $autorisMelOld, int $autorisMelNew): bool
    {
        // L'ancienne réponse entraînait-elle la remise d'un exemplaire papier ?
        $autorisMelOld_entraineRemiseExemplairePapier = $this->autorisMelEntraineRemiseExemplairePapier($autorisMelOld);
        // La nouvelle réponse entraîne-t-elle la remise d'un exemplaire papier ?
        $autorisMelNew_entraineRemiseExemplairePapier = $this->autorisMelEntraineRemiseExemplairePapier($autorisMelNew);

        // Si l'ancienne réponse entraînait la remise d'un exemplaire papier et que la nouvelle non, ou inversement,
        // c'est un changement "important".
        if ($autorisMelOld_entraineRemiseExemplairePapier && !$autorisMelNew_entraineRemiseExemplairePapier ||
            !$autorisMelOld_entraineRemiseExemplairePapier && $autorisMelNew_entraineRemiseExemplairePapier) {
            return true;
        }

        return false;
    }

    /**
     * Teste si l'autorisation de diffusion spécifiée entraîne ou non la nécessité de remettre
     * une version papier de la thèse.
     */
    private function autorisMelEntraineRemiseExemplairePapier(int $autorisMel): bool
    {
        return
            $autorisMel === Diffusion::AUTORISATION_OUI_EMBARGO ||
            $autorisMel === Diffusion::AUTORISATION_NON;
    }

    private function getFournirExemplairePapierTheseFeature(): FournirExemplairePapierTheseFeature
    {
        /** @var FournirExemplairePapierTheseFeature $dbFeature */
        $dbFeature = $this->getFeature(FournirExemplairePapierTheseFeature::CODE);

        return $dbFeature;
    }
}