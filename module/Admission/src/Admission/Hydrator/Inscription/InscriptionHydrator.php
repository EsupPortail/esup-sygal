<?php

namespace Admission\Hydrator\Inscription;

use Admission\Entity\Db\Inscription;
use Application\Entity\Db\Discipline;
use Application\Entity\Db\Pays;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Individu\Entity\Db\Individu;
use Soutenance\Entity\Qualite;
use Structure\Entity\Db\ComposanteEnseignement;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\UniteRecherche;

/**
 * @author Unicaen
 */
class InscriptionHydrator extends DoctrineObject
{
    public function extract(object $object): array
    {
        /** @var Inscription $object */
        $data = parent::extract($object); // TODO: Change the autogenerated stub

        if (array_key_exists($key = 'composanteDoctorat', $data) && $data[$key] instanceof ComposanteEnseignement) {
            $data["composanteDoctorat"] = $data["composanteDoctorat"]->getId();
        }

        if (array_key_exists($key = 'paysCoTutelle', $data) && $data[$key] instanceof Pays) {
            $data[$key] = array("id" => $object->getPaysCoTutelle()->getId(), "label" => $object->getPaysCoTutelle()->getLibelle());
        }else{
            $data['paysCoTutelle'] = array("id" => null, "label" => "");
        }

        if (array_key_exists($key = 'directeur', $data) && $data[$key] instanceof Individu) {
            $data["nomDirecteurThese"] = array("id" => $object->getDirecteur()->getId(), "label" => $object->getDirecteur()->getNomUsuel());
            $data["prenomDirecteurThese"] = array("id" => $object->getDirecteur()->getId(), "label" => $object->getDirecteur()->getPrenom());
        }else{
            $data["nomDirecteurThese"] = array("id" => null, "label" => $object->getNomDirecteurThese());
            $data["prenomDirecteurThese"] = array("id" => null, "label" => $object->getPrenomDirecteurThese());
        }

        if (array_key_exists($key = 'coDirecteur', $data) && $data[$key] instanceof Individu) {
            $data["nomCodirecteurThese"] = array("id" => $object->getCoDirecteur()->getId(), "label" => $object->getCoDirecteur()->getNomUsuel());
            $data["prenomCodirecteurThese"] = array("id" => $object->getCoDirecteur()->getId(), "label" => $object->getCoDirecteur()->getPrenom());
        }else{
            $data["nomCodirecteurThese"] = array("id" => null, "label" => $object->getNomCoDirecteurThese());
            $data["prenomCodirecteurThese"] = array("id" => null, "label" => $object->getPrenomCoDirecteurThese());
        }

        if (array_key_exists($key = 'uniteRecherche', $data) && $data[$key] instanceof UniteRecherche) {
            $data["uniteRecherche"] = $data["uniteRecherche"]->getId();
        }

        if (array_key_exists($key = 'etablissementInscription', $data) && $data[$key] instanceof Etablissement) {
            $data["etablissementInscription"] = $data["etablissementInscription"]->getId();
        }

        if (array_key_exists($key = 'ecoleDoctorale', $data) && $data[$key] instanceof EcoleDoctorale) {
            $data["ecoleDoctorale"] = $data["ecoleDoctorale"]->getId();
        }

        if (array_key_exists($key = 'etablissementRattachementCoDirecteur', $data) && $data[$key] instanceof Etablissement) {
            $data["etablissementRattachementCoDirecteur"] = $data["etablissementRattachementCoDirecteur"]->getId();
        }

        if (array_key_exists($key = 'fonctionDirecteurThese', $data) && $data[$key] instanceof Qualite) {
            $data["fonctionDirecteurThese"] = $data["fonctionDirecteurThese"]->getId();
        }

        if (array_key_exists($key = 'fonctionCoDirecteurThese', $data) && $data[$key] instanceof Qualite) {
            $data["fonctionCoDirecteurThese"] = $data["fonctionCoDirecteurThese"]->getId();
        }

        if (array_key_exists($key = 'uniteRechercheCoDirecteur', $data) && $data[$key] instanceof UniteRecherche) {
            $data["uniteRechercheCoDirecteur"] = $data["uniteRechercheCoDirecteur"]->getId();
        }

        if (array_key_exists($key = 'specialiteDoctorat', $data) && $data[$key] instanceof Discipline) {
            $data["specialiteDoctorat"] = $data["specialiteDoctorat"]->getId();
        }

        $data['verificationInscription'] = $object->getVerificationInscription()->first();

        return $data;
    }

    public function hydrate(array $data, object $object): object
    {
        $data["composanteDoctorat"] = !empty($data["composanteDoctorat"]) ? $data["composanteDoctorat"] : null;
        $data["ecoleDoctorale"] = !empty($data["ecoleDoctorale"]) ? $data["ecoleDoctorale"] : null;
        $data["uniteRecherche"] = !empty($data["uniteRecherche"]) ? $data["uniteRecherche"] : null;
        $data["etablissementInscription"] = !empty($data["etablissementInscription"]) ? $data["etablissementInscription"] : null;
        $data["uniteRechercheCoDirecteur"] = !empty($data["uniteRechercheCoDirecteur"]) ? $data["uniteRechercheCoDirecteur"] : null;
        $data["etablissementRattachementCoDirecteur"] = !empty($data["etablissementRattachementCoDirecteur"]) ? $data["etablissementRattachementCoDirecteur"] : null;
        $data["fonctionDirecteurThese"] = !empty($data["fonctionDirecteurThese"]) ? $data["fonctionDirecteurThese"] : null;
        $data["fonctionCoDirecteurThese"] = !empty($data["fonctionCoDirecteurThese"]) ? $data["fonctionCoDirecteurThese"] : null;
        $data["specialiteDoctorat"] = !empty($data["specialiteDoctorat"]) ? $data["specialiteDoctorat"] : null;

        if (!empty($data["nomCodirecteurThese"]["id"]) && !empty($data["nomCodirecteurThese"]["label"])) {
            $data["coDirecteur"] = $data["nomCodirecteurThese"]["id"];
        } elseif (!empty($data["prenomCodirecteurThese"]["id"]) && !empty($data["prenomCodirecteurThese"]["label"])) {
            $data["coDirecteur"] = $data["prenomCodirecteurThese"]["id"];
        } else {
            $data["coDirecteur"] = null;
        }

        if(empty($data["nomCodirecteurThese"]["id"]) || empty($data["prenomCodirecteurThese"]["id"])){
            $data["coDirecteur"] = null;
        }

        $data["prenomCodirecteurThese"] = (array_key_exists("prenomCodirecteurThese", $data) && empty($data["prenomCodirecteurThese"]["label"])) ? null : $data["prenomCodirecteurThese"]["label"];
        $data["nomCodirecteurThese"] = (array_key_exists("nomCodirecteurThese", $data) && empty($data["nomCodirecteurThese"]["label"])) ? null : $data["nomCodirecteurThese"]["label"];

        if (!empty($data["nomDirecteurThese"]["id"]) && !empty($data["nomDirecteurThese"]["label"])) {
            $data["directeur"] = $data["nomDirecteurThese"]["id"];
        } elseif (!empty($data["prenomDirecteurThese"]["id"]) && !empty($data["prenomDirecteurThese"]["label"])) {
            $data["directeur"] = $data["prenomDirecteurThese"]["id"];
        } else {
            $data["directeur"] = null;
        }

        if(empty($data["nomDirecteurThese"]["id"]) || empty($data["prenomDirecteurThese"]["id"])){
            $data["directeur"] = null;
        }

        $data["prenomDirecteurThese"] = (array_key_exists("prenomDirecteurThese", $data) && empty($data["prenomDirecteurThese"]["label"])) ? null : $data["prenomDirecteurThese"]["label"];
        $data["nomDirecteurThese"] = (array_key_exists("nomDirecteurThese", $data) && empty($data["nomDirecteurThese"]["label"])) ? null : $data["nomDirecteurThese"]["label"];
        $data["paysCoTutelle"] = (array_key_exists("paysCoTutelle", $data) && empty($data["paysCoTutelle"]["id"])) ? null : $data["paysCoTutelle"]["id"];

        //Si la case confidentialite est décochée, on met à null les valeurs des champs reliés
        if (array_key_exists("confidentialite", $data) && !$data["confidentialite"]) {
            $data["dateConfidentialite"] = null;
        }

        //Si la case cotutelle est décochée, on met à null les valeurs des champs reliés
        if (array_key_exists("coTutelle", $data) && !$data["coTutelle"]) {
            $data["paysCoTutelle"] = empty($data["paysCoTutelle"]["id"]) ? null : $data["paysCoTutelle"]["id"];
        }

        //Si la case codirection est décochée, on met à null les valeurs des champs reliés
        if (array_key_exists("coDirection", $data) &&!$data["coDirection"]) {
            $data["nomCodirecteurThese"] = null;
            $data['prenomCodirecteurThese'] = null;
            $data['emailCodirecteurThese'] = null;
        }

        if (isset($data['verificationInscription']) && !is_array($data['verificationInscription'])) {
            $data['verificationInscription'] = [$data['verificationInscription']];
        }

        return parent::hydrate($data, $object);
    }

}