--
-- 9.0.0
--

--
-- Suppression de variables abandonnées.
--

delete from variable where code in ('EMAIL_BDD', 'EMAIL_BU', 'TRIBUNAL_COMPETENT');
delete from variable where code is null;


--
-- Suppression vue abandonnée.
--

drop view if exists v_these;


--
-- Amélioration de la procédure unicaen_indicateur_recreate_matviews() : voir quelle MV échoue, le cas échéant.
--

create or replace procedure unicaen_indicateur_recreate_matviews()
    language plpgsql
as $$declare
    v_result indicateur;
    v_name varchar;
    v_template varchar = 'create materialized view %s as %s';
begin
    raise notice '%', 'Création des vues matérialisées manquantes...';
    for v_result in
        select i.* from indicateur i
                            left join pg_matviews mv on schemaname = 'public' and matviewname = 'mv_indicateur_'||i.id
        where mv.matviewname is null
        order by i.id
        loop
            v_name = 'mv_indicateur_'||v_result.id;
            raise notice '%', format('- %s...', v_name);
            execute format(v_template, v_name, v_result.requete);
        end loop;
    raise notice '%', 'Terminé.';
end
$$;


--
-- On cascade delete => restrict sur des contraintes de référence.
--

alter table rapport drop constraint rapport_annuel_fichier_fk;
alter table rapport add constraint rapport_annuel_fichier_fk foreign key (fichier_id) references fichier on delete restrict;

alter table validite_fichier drop constraint validite_fichier_ffk;
alter table validite_fichier add constraint validite_fichier_ffk foreign key (fichier_id) references fichier on delete restrict;


--
-- Admission
--
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionFinancement#StatutProInfos',
        '<p>Retourne les informations concernant le statut professionnel si l''étudiant est salarié</p>',
        'admissionFinancement',
        'getStatutProfessionnelInfos');

UPDATE public.unicaen_renderer_template
SET document_corps = '<h1 style="text-align: center;">Convention de formation doctorale</h1>
<h1 style="text-align: center;">de VAR[Individu#Denomination]</h1>
<p> </p>
<p><strong>Entre</strong> VAR[AdmissionEtudiant#DenominationEtudiant]<br /><strong>né le</strong> VAR[AdmissionEtudiant#DateNaissance]<br /><strong>à</strong> VAR[AdmissionEtudiant#VilleNaissance], VAR[AdmissionEtudiant#PaysNaissance] (VAR[AdmissionEtudiant#Nationalite])<br /><strong>ayant pour adresse</strong> VAR[AdmissionEtudiant#Adresse], VAR[AdmissionEtudiant#CodePostal] VAR[AdmissionEtudiant#VilleEtudiant], VAR[AdmissionEtudiant#PaysEtudiant]  </p>
<p><strong>Mail</strong> : VAR[AdmissionEtudiant#MailEtudiant]</p>
<p><strong>ci-après dénommé le Doctorant</strong></p>
<p><strong>Et</strong> VAR[AdmissionInscription#DenominationDirecteurThese], directeur(-trice) de thèse<br /><strong>Fonction</strong> : VAR[AdmissionInscription#FonctionDirecteurThese]<br /><strong>Unité de recherche </strong>: VAR[AdmissionInscription#UniteRecherche]<br /><strong>Établissement de rattachement</strong> : VAR[AdmissionInscription#EtablissementInscription]<br /><strong>Mail</strong> : VAR[AdmissionInscription#MailDirecteurThese]</p>
<p>VAR[AdmissionConventionFormationDoctorale#InfosCoDirecteur]</p>
<p>- Vu l’article L612-7 du Code de l’éducation, Vu les articles L412-1 et L412-2 du Code de la recherche,</p>
<p>- Vu l’arrêté du 25 mai 2016 fixant le cadre national de la formation et les modalités conduisant à la délivrance du diplôme national de doctorat, modifié par l’arrêté du 26 août 2022,</p>
<p>- Vu la charte du doctorat dans le cadre de la délivrance conjointe du doctorat entre la ComUE Normandie Université et les établissements d’inscription co-accrédités en date du 14 mai 2024, </p>
<p>VAR[AdmissionConventionFormationDoctorale#InfosCoTutelle]</p>
<p>VAR[AdmissionConventionFormationDoctorale#InfosConventionCollaboration]</p>
<p>Il est établi la convention de formation doctorale suivante. Cette convention peut être modifiée par avenant autant de fois que nécessaire pendant le doctorat.</p>
<h3><strong>Article 1 - Objet de la convention de formation doctorale</strong></h3>
<p>La présente convention de formation, signée à la première inscription par le Doctorant, par le (ou les) (co)Directeur(s) de Thèse et, le cas échéant, par le(s) co-encadrant(s) de thèse, fixe les conditions de suivi et d''encadrement de la thèse, sous la responsabilité de l’VAR[AdmissionInscription#EtablissementInscription] (établissement d’inscription), établissement de préparation du doctorat. En cas de besoin, la convention de formation doctorale est révisable annuellement par un avenant à la convention initiale.</p>
<p>Les règles générales en matière de signature des travaux issus de la thèse, de confidentialité et de propriété intellectuelle sont précisées dans la Charte du doctorat adoptée par Normandie Université et l’VAR[AdmissionInscription#EtablissementInscription] (établissement d’inscription), également signée à la première inscription, par le Doctorant et le (ou les) Directeur(s) de Thèse.</p>
<p><strong>Titre de la thèse </strong></p>
<p>VAR[AdmissionInscription#TitreThese]</p>
<p><strong>Spécialité du diplôme</strong></p>
<p>VAR[AdmissionInscription#SpecialiteDoctorat]</p>
<p><strong>Financement</strong></p>
<p>VAR[AdmissionFinancement#ContratDoctoral]</p>
<p><strong>Établissement d''inscription du doctorant</strong></p>
<p>VAR[AdmissionInscription#EtablissementInscription]</p>
<p><strong>École doctorale</strong></p>
<p>VAR[AdmissionInscription#EcoleDoctorale]</p>
<p><strong>Temps de travail du Doctorat mené à</strong> : VAR[AdmissionFinancement#TempsTravail]</p>
<p><strong>Statut professionnel </strong>: VAR[AdmissionFinancement#StatutProInfos]</p>
<h3><strong>Article 2 – Laboratoire d’accueil</strong></h3>
<p>Le doctorant réalise sa thèse au sein de :</p>
<ul>
<li style="font-weight: 400; text-align: start;">Unité d''accueil : VAR[AdmissionInscription#UniteRecherche]</li>
<li style="font-weight: 400; text-align: start;">Directeur de l''unité : VAR[AdmissionConventionFormationDoctorale#ResponsablesURDirecteurThese]</li>
</ul>
<p>VAR[AdmissionConventionFormationDoctorale#InfosCoTutelleCoDirection]</p>
<h3><strong>Article 3 - Méthodes et Moyens</strong></h3>
<p><strong>3-1 Calendrier prévisionnel du projet de recherche</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#CalendrierPrevisionnel]</p>
<p><strong>3-2 Modalités d''encadrement, de suivi de la formation et d''avancement des recherches du doctorant</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#ModalitesEncadrement]</p>
<p><strong>3-3 Conditions matérielles de réalisation du projet de recherche et conditions de sécurité spécifiques si nécessaire</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#ConditionsProjRech]</p>
<p><strong>3-4 Modalités d''intégration dans l''unité ou l’équipe de recherche</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#ModalitesIntegrationUR]</p>
<p><strong>3-5 Partenariats impliqués par le projet de thèse</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#PartenariatsProjThese]</p>
<h3><strong>Article 4 - Confidentialité des travaux de recherche</strong></h3>
<p>Caractère confidentiel des travaux :</p>
<p>VAR[AdmissionInscription#ConfidentialiteSouhaitee]</p>
<p>Si oui, motivation de la demande de confidentialité par le doctorant et la direction de thèse:</p>
<p>VAR[AdmissionConventionFormationDoctorale#MotivationDemandeConfidentialite]</p>
<h3><strong>Article 5 - Projet professionnel du doctorant</strong></h3>
<p>VAR[AdmissionConventionFormationDoctorale#ProjetProDoctorant]</p>
<p> </p>
<h2>Validations accordées à la convention de formation doctorale</h2>
<p>VAR[AdmissionConventionFormationDoctorale#Operations]</p>'
WHERE code = 'ADMISSION_CONVENTION_FORMATION_DOCTORALE';

--
-- Formation
--
ALTER TABLE formation_session
    ADD COLUMN date_publication timestamp;