--
-- 10.3.0
--

--
-- Table pour pouvoir configurer certaines fonctionnalités/features (activation ou non, etc.)
--
create table feature
(
  code               varchar(100)                        not null
    constraint feature_pk primary key,
  libelle            varchar(200)                        not null,
  enabled_start_date timestamp default current_timestamp not null,
  enabled_end_date   timestamp,
  config             json,
  ordre              smallserial
);
comment on column feature.code is 'Code unique identifiant la feature';
comment on column feature.libelle is 'Libellé de la feature';
comment on column feature.enabled_start_date is 'Si la feature est activée, date à partir de laquelle c''est le cas';
comment on column feature.enabled_end_date is 'Si la feature est activée, date jusqu''à laquelle c''est le cas';
comment on column feature.config is 'Config éventuelle de la feature, au format JSON';

insert into feature(code, libelle) 
values ('FournirExemplairePapierTheseFeature', 'Fournir un exemplaire papier de la thèse');



--
-- La pertinence de l'étape "REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE" dépend de l'activation ou non
-- de la feature "dépôt version papier" *à la date de la soutenance*.
--
create or replace view v_wf_etape_pertin(these_id, etape_id, code, ordre, id) as
SELECT alias38.these_id::numeric                                                   AS these_id,
       alias38.etape_id::numeric                                                   AS etape_id,
       alias38.code,
       alias38.ordre,
       row_number() OVER (ORDER BY 1::integer, 2::integer, 3::integer, 4::integer) AS id
FROM (SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'VALIDATION_PAGE_DE_COUVERTURE'::text
      WHERE t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text])
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'DEPOT_VERSION_ORIGINALE'::text
      WHERE t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text])
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'AUTORISATION_DIFFUSION_THESE'::text
      WHERE t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text])
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'ATTESTATIONS'::text
      WHERE t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text])
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'SIGNALEMENT_THESE'::text
      WHERE t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text])
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'ARCHIVABILITE_VERSION_ORIGINALE'::text
      WHERE t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text])
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'DEPOT_VERSION_ARCHIVAGE'::text
               JOIN v_situ_archivab_vo situ ON situ.these_id = t.id AND situ.est_valide = false
      WHERE t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text])
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'ARCHIVABILITE_VERSION_ARCHIVAGE'::text
               JOIN v_situ_archivab_vo situ ON situ.these_id = t.id AND situ.est_valide = false
      WHERE t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text])
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'VERIFICATION_VERSION_ARCHIVAGE'::text
               JOIN v_situ_archivab_va situ ON situ.these_id = t.id AND situ.est_valide = true
      WHERE t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text])
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'RDV_BU_SAISIE_DOCTORANT'::text
      WHERE t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text])
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'RDV_BU_SAISIE_BU'::text
      WHERE t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text])
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'RDV_BU_VALIDATION_BU'::text
      WHERE t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text])
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'DEPOT_VERSION_ORIGINALE_CORRIGEE'::text
      WHERE (t.correc_autorisee IS NOT NULL OR
             t.correc_autorisee_forcee IS NOT NULL AND t.correc_autorisee_forcee::text <> 'aucune'::text OR
             t.correc_effectuee::text = 'O'::text)
        AND (t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text]))
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE'::text
      WHERE (t.correc_autorisee IS NOT NULL OR
             t.correc_autorisee_forcee IS NOT NULL AND t.correc_autorisee_forcee::text <> 'aucune'::text OR
             t.correc_effectuee::text = 'O'::text)
        AND (t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text]))
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'ATTESTATIONS_VERSION_CORRIGEE'::text
      WHERE (t.correc_autorisee IS NOT NULL OR
             t.correc_autorisee_forcee IS NOT NULL AND t.correc_autorisee_forcee::text <> 'aucune'::text OR
             t.correc_effectuee::text = 'O'::text)
        AND (t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text]))
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE'::text
      WHERE (t.correc_autorisee IS NOT NULL OR
             t.correc_autorisee_forcee IS NOT NULL AND t.correc_autorisee_forcee::text <> 'aucune'::text OR
             t.correc_effectuee::text = 'O'::text)
        AND (t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text]))
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE'::text
               JOIN v_situ_archivab_voc situ ON situ.these_id = t.id AND situ.est_valide = false
      WHERE (t.correc_autorisee IS NOT NULL OR
             t.correc_autorisee_forcee IS NOT NULL AND t.correc_autorisee_forcee::text <> 'aucune'::text OR
             t.correc_effectuee::text = 'O'::text)
        AND (t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text]))
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE'::text
               JOIN v_situ_archivab_voc situ ON situ.these_id = t.id AND situ.est_valide = false
      WHERE (t.correc_autorisee IS NOT NULL OR
             t.correc_autorisee_forcee IS NOT NULL AND t.correc_autorisee_forcee::text <> 'aucune'::text OR
             t.correc_effectuee::text = 'O'::text)
        AND (t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text]))
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE'::text
               JOIN v_situ_archivab_vac situ ON situ.these_id = t.id AND situ.est_valide = true
      WHERE (t.correc_autorisee IS NOT NULL OR
             t.correc_autorisee_forcee IS NOT NULL AND t.correc_autorisee_forcee::text <> 'aucune'::text OR
             t.correc_effectuee::text = 'O'::text)
        AND (t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text]))
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT'::text
      WHERE (t.correc_autorisee IS NOT NULL OR
             t.correc_autorisee_forcee IS NOT NULL AND t.correc_autorisee_forcee::text <> 'aucune'::text OR
             t.correc_effectuee::text = 'O'::text)
        AND (t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text]))
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR'::text
      WHERE (t.correc_autorisee IS NOT NULL OR
             t.correc_autorisee_forcee IS NOT NULL AND t.correc_autorisee_forcee::text <> 'aucune'::text OR
             t.correc_effectuee::text = 'O'::text)
        AND (t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text]))
      UNION ALL
      SELECT t.id AS these_id,
             e.id AS etape_id,
             e.code,
             e.ordre
      FROM these t
               JOIN wf_etape e ON e.code::text = 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE'::text
      WHERE (t.correc_autorisee IS NOT NULL OR
             t.correc_autorisee_forcee IS NOT NULL AND t.correc_autorisee_forcee::text <> 'aucune'::text OR
             t.correc_effectuee::text = 'O'::text)
        AND (t.etat_these::text = ANY (ARRAY ['E'::character varying::text, 'S'::character varying::text]))
        and exists (select *
                    from feature f
                    where f.code = 'FournirExemplairePapierTheseFeature'
                      and t.date_soutenance between f.enabled_start_date and coalesce(f.enabled_end_date, current_timestamp))
        AND (EXISTS (SELECT d.id
                     FROM diffusion d
                     WHERE d.these_id = t.id
                       AND d.version_corrigee = true
                       AND (d.autoris_mel = ANY (ARRAY [0, 1]))))) alias38;



--
-- Table Structure
--
alter table structure add article_defini varchar(5) default 'l''' not null;

--
-- Table Diffusion
--
alter table diffusion add autoris_pret_papier bool;

--
-- Template pour Convention de MEL
-- 
select setval('unicaen_renderer_macro_id_seq', coalesce(max(id),1)) from unicaen_renderer_macro;
select setval('unicaen_renderer_template_id_seq', coalesce(max(id),1)) from unicaen_renderer_template;

INSERT INTO unicaen_renderer_macro (id, code, description, variable_name, methode_name)
VALUES (
            nextval('unicaen_renderer_macro_id_seq'),
            'UrlFichier#LogoEtablissementComue',
            '<p>Retourne le chemin absolu sur disque du logo (fichier) de la COMUE éventuelle.</p>',
            'UrlFichier',
            'getLogoEtablissementComue'
       );
INSERT INTO unicaen_renderer_macro (id, code, description, variable_name, methode_name)
VALUES (
           nextval('unicaen_renderer_macro_id_seq'),
           'UrlFichier#LogoEtablissementCed',
           '<p>Retourne le chemin absolu sur disque du logo (fichier) du Collège des Ecoles Doctorales éventuel.</p>',
            'UrlFichier',
           'getLogoEtablissementCed'
       );
INSERT INTO unicaen_renderer_macro (id, code, description, variable_name, methode_name)
VALUES (
           nextval('unicaen_renderer_macro_id_seq'),
           'UrlFichier#LogoEtablissementInscr',
           '<p>Retourne le chemin absolu sur disque du logo (fichier) de l''établissement d''inscription pour la thèse.</p>',
            'UrlFichier',
           'getLogoEtablissementInscr'
       );

INSERT INTO unicaen_renderer_macro (id, code, description, variable_name, methode_name)
VALUES (
           nextval('unicaen_renderer_macro_id_seq'),
           'Doctorant#prenom',
           '<p>Retourne le 1er prénom du doctorant.</p>',
           'doctorant',
           'getPrenom'
       );
INSERT INTO unicaen_renderer_macro (id, code, description, variable_name, methode_name)
VALUES (
           nextval('unicaen_renderer_macro_id_seq'),
           'Doctorant#nomPatronymique',
           '<p>Retourne le nom patronymique du doctorant.</p>',
           'doctorant',
           'getNomPatronymique'
       );
INSERT INTO unicaen_renderer_macro (id, code, description, variable_name, methode_name)
VALUES (
           nextval('unicaen_renderer_macro_id_seq'),
           'Doctorant#nomUsuel',
           '<p>Retourne le nom d''usage du doctorant.</p>',
           'doctorant',
           'getNomUsuel'
       );

INSERT INTO unicaen_renderer_macro (id, code, description, variable_name, methode_name)
VALUES (
           nextval('unicaen_renderer_macro_id_seq'),
           'These#DateFinConfidentialite',
           '<p>Retourne la date de fin de confidentialité éventuelle de la thèse.</p>',
           'these',
           'getDateFinConfidentialite'
       );
INSERT INTO unicaen_renderer_macro (id, code, description, variable_name, methode_name)
VALUES (
           nextval('unicaen_renderer_macro_id_seq'),
           'These#DateSoutenanceReelleOuPrevisionnelle',
           '<p>Retourne la date réelle de soutenance ou sinon la date prévisionnelle, attachée à la thèse. Inclut l''intitulé de la date.</p>',
           'these',
           'getDateSoutenanceReelleOuPrevisionnelle'
       );

INSERT INTO unicaen_renderer_template (code, description, document_type, document_sujet, document_corps,
                                       document_css, namespace, engine)
VALUES ('DEPOT_CONVENTION_DE_MISE_EN_LIGNE', '<p>Convention de mise en ligne d''une thèse</p>', 'pdf',
        'Convention de mise en ligne de la thèse', e'<div style="text-align: center; margin-bottom: 30px;">
    <h1>CONVENTION DE MISE EN LIGNE<br>D\'UNE THÈSE SOUTENUE</h1>

    {% if comue %}
        <img src="{{ UrlFichier.getLogoEtablissementComue }}" height="3cm" alt="Logo de Normandie Université"/>
    {% endif %}
    <img src="{{ UrlFichier.getLogoEtablissementInscr }}" height="3cm" alt="Logo de l\'établissement d\'inscription"/>
</div>

<p>
    En application de la Charte de dépôt et de diffusion des thèses votée par le·s Conseil·s Académique·s
    {% if comue %}
        de {{ comue.libelle }} et des établissements co-accrédités, il est convenu ce qui suit entre {{ comue.libelle }} et
        l’{{ these.etablissement.libelle }} d’une part, représenté·e par les chefs d\'établissements co-accrédités,
    {% else %}
        du ou des établissements co-accrédités, il est convenu ce qui suit entre
        {# [le ou les établissement accrédité ou co-accrédités] #} {{ these.etablissement.libelle }} d’une part,
        représenté·e par les chefs d\'établissements accrédités ou co-accrédités,
    {% endif %}
    et l’auteur identifié ci-dessous d’autre part :
</p>
<p>
    <strong>NOM Prénom du docteur
        :</strong> {{ these.doctorant.individu.nomPatronymique|upper }} {{ these.doctorant.individu.prenom }}<br>
    <strong>Etablissement de préparation du Doctorat :</strong> {{ these.etablissement.libelle }}<br>
    <strong>École doctorale :</strong> {{ these.ecoleDoctorale }}<br>
    <strong>Titre de la thèse :</strong> {{ these.titre ?: "(Aucun titre)" }} <br>
    {% if these.dateSoutenance %}
        <strong>Date de soutenance :</strong> {{ these.dateSoutenance|date("m/d/Y") }}<br>
    {% endif %}
    {% if these.getDateFinConfidentialite %}
        <strong>Date de fin de confidentialité :</strong> {{ these.getDateFinConfidentialite|date("m/d/Y") }}<br>
    {% endif %}
</p>

<div class="attestations">
    <h2>Attestations</h2>
    <div class="attestations-item">
        <div class="attestations-item-value">
            ☑ &nbsp;&nbsp;L’auteur certifie avoir pris connaissance de la Charte de dépôt et de diffusion des thèses de
            {% if comue %}
                {{ comue.libelle }} et des établissements co-accrédités.
            {% else %}
                l\'{{ these.etablissement.libelle }}.
            {% endif %}
        </div>
    </div>
    <div class="attestations-item">
        <div class="attestations-item-value">
            ☑ &nbsp;&nbsp;L’auteur certifie que la version déposée sous forme numérique constitue la version de
            référence de la thèse.
        </div>
    </div>
    {# <div class="attestations-item">
        <div class="attestations-item-value">
            ☑ &nbsp;&nbsp;L’auteur certifie que l’exemplaire imprimé fourni est conforme à la version numérique déposée
        </div>
    </div> #}
</div>

<div class="diffusion">
    <h2>Autorisation de mise en ligne</h2>

    <div class="diffusion-item">
        {% if diffusion.getAutorisMel == constant(\'\\\\Depot\\\\Entity\\\\Db\\\\Diffusion::AUTORISATION_OUI_IMMEDIAT\')
            or diffusion.getAutorisMel == constant(\'\\\\Depot\\\\Entity\\\\Db\\\\Diffusion::AUTORISATION_OUI_EMBARGO\') %}
            L’auteur autorise la mise en ligne de la version de diffusion de la thèse sur Internet (après, le cas
            échéant, la fin de la période de confidentialité décidée par l’établissement) :
        {% else %}
            L\'auteur n\'autorise pas la mise en ligne de la version de diffusion de la thèse sur Internet.
        {% endif %}

        <div class="diffusion-item-value">
            {% if diffusion.getAutorisMel == constant(\'\\\\Depot\\\\Entity\\\\Db\\\\Diffusion::AUTORISATION_OUI_IMMEDIAT\') %}
                ☑ &nbsp;&nbsp; Oui, immédiatement
            {% elseif diffusion.getAutorisMel == constant(\'\\\\Depot\\\\Entity\\\\Db\\\\Diffusion::AUTORISATION_OUI_EMBARGO\') %}
                ☑ &nbsp;&nbsp; Oui, avec embargo après soutenance, pour une durée de : {{ diffusion.getAutorisEmbargoDuree }}
                <div class=\'autoris-diffusion-motif-div\'>Motif :</div>
                <div class=\'autoris-diffusion-motif-div\'>
                    <p class=\'autoris-diffusion-motif\'>
                        {{ diffusion.getAutorisMotif }}
                    </p>
                </div>
            {% else %}
                <div class=\'autoris-diffusion-motif-div\'>
                    <p>Motif :</p>
                    <p class="autoris-diffusion-motif">
                        {{ diffusion.getAutorisMotif }}
                    </p>
                </div>
            {% endif %}
        </div>

        <div class="laius">
            {% if diffusion.getAutorisMel is same as(null) %}
                <!-- pas possible théoriquement -->
            {% elseif diffusion.getAutorisMel == constant(\'\\\\Depot\\\\Entity\\\\Db\\\\Diffusion::AUTORISATION_OUI_IMMEDIAT\') %}
                <p>
                    La thèse est consultable sur le réseau international internet via le portail national des thèses
                    (www.theses.fr), sans authentification. La thèse peut également être accessible depuis des
                    plateformes de diffusion choisies par
                    {% if comue %}
                        {{ comue.libelle }} et les établissements co-accrédités
                    {% else %}
                        l\'{{ these.etablissement.libelle }}.
                    {% endif %}
                    dans le cadre de sa politique de valorisation scientifique
                    (exemple : <a href="https://theses.hal.science">https://theses.hal.science</a>).
                </p>
            {% elseif diffusion.getAutorisMel == constant(\'\\\\Depot\\\\Entity\\\\Db\\\\Diffusion::AUTORISATION_OUI_EMBARGO\') %}
                <p>
                    Pendant cette période, conformément à l’Arrêté du 25 mai 2016 modifié le 26 août 2022, le
                    manuscrit de thèse est obligatoirement accessible à l’ensemble de la communauté universitaire
                    française.
                    {% if comue %}
                        {{ comue.libelle }} et les établissements co-accrédités remplissent
                    {% else %}
                        l\'{{ these.etablissement.libelle }} remplit
                    {% endif %}
                    cette obligation en rendant accessible le manuscrit au format PDF sur l’intranet de
                    l’Agence bibliographique de l’enseignement supérieur
                    (Abes, <a href="https://abes.fr">https://abes.fr</a>) disponible depuis le site theses.fr.
                    L’accès à cet intranet est possible uniquement après une authentification Renater
                    (<a href="https://www.renater.fr">https://www.renater.fr</a>).
                </p>
                <p>
                    La diffusion en ligne sur le réseau international internet de la thèse est ensuite effectuée
                    automatiquement le lendemain de l’expiration du délai, sans préavis. Une demande de prolongation
                    de l’embargo auprès du service de documentation concerné est possible, mais doit anticiper
                    le délai de traitement de la demande (un mois, hors périodes de fermeture).
                </p>
            {% elseif diffusion.getAutorisMel == constant(\'\\\\Depot\\\\Entity\\\\Db\\\\Diffusion::AUTORISATION_NON\') %}
                <p>
                    En cas de refus, la thèse n’est pas diffusée sur le réseau international internet. Cependant,
                    conformément à l’Arrêté du 25 mai 2016 modifié le modifié le 26 août 2022, le manuscrit de thèse
                    est obligatoirement accessible à l’ensemble de la communauté universitaire française.
                    {% if comue %}
                        {{ comue.libelle }} et les établissements co-accrédités remplissent
                    {% else %}
                        l\'{{ these.etablissement.libelle }} remplit
                    {% endif %}
                    cette obligation en rendant accessible le manuscrit au format PDF sur l’intranet de
                    l’Agence bibliographique de l’enseignement supérieur
                    (Abes, <a href="https://abes.fr">https://abes.fr</a>) disponible depuis le site theses.fr.
                    L’accès à cet intranet est possible uniquement après une authentification Renater
                    (<a href="https://www.renater.fr">https://www.renater.fr</a>).
                </p>
            {% endif %}
        </div>

        <div>
            {% if diffusion.getAutorisMel is same as(null) %}
                <!-- pas possible théoriquement -->
            {% elseif diffusion.getAutorisMel == constant(\'\\\\Depot\\\\Entity\\\\Db\\\\Diffusion::AUTORISATION_OUI_EMBARGO\')
                or diffusion.getAutorisMel == constant(\'\\\\Depot\\\\Entity\\\\Db\\\\Diffusion::AUTORISATION_NON\') %}
                {% if diffusion.getAutorisPretPapier is not same as(null) %}
                    <p>
                        {% if diffusion.getAutorisPretPapier == true %}
                            ☑ &nbsp;&nbsp;L\'auteur autorise le prêt du fichier papier de sa thèse dans les établissements d\'enseignement supérieur en
                            dehors de la France et également que la BU imprime un exemplaire papier pour permettre le prêt.
                        {% else %}
                            L\'auteur n\'autorise pas le prêt de sa thèse dans les établissements d\'enseignement supérieur
                            en dehors de la France.
                        {% endif %}
                    </p>
                {% endif %}
            {% endif %}
        </div>
    </div>
</div>

<pagebreak/>

    <div class="droits">
        <h2>Droits d\'auteur <small>(si autorisation de mise en ligne)</small></h2>

        <div class="droits-item">
            <div class="droits-item-value">
                {% if diffusion.getDroitAuteurOk == true %}
                    {% set checkbox1 = \'☑\' %}
                    {% set checkbox2 = \'☐\' %}
                {% else %}
                    {% set checkbox1 = \'☐\' %}
                    {% set checkbox2 = \'☑\' %}
                {% endif %}
                <p>{{ checkbox1 }} &nbsp;&nbsp; L\'auteur garantit que tous les documents de la thèse sont libres de
                    droits ou qu\'il a acquis les droits afférents pour la reproduction et la représentation
                    sur tous supports.</p>
                <p>{{ checkbox2 }} &nbsp;&nbsp; À défaut, l\'auteur déclare fournir en outre lors du dépôt une version
                    numérique spécifique excluant ces oeuvres tierces (version de diffusion).</p>
            </div>

            <div class="laius">
                <p>
                    La loi applicable à cette présente convention est la loi française. Le tribunal compétent
                    pour juger de tout contentieux lié au présent contrat est le tribunal administratif dans le
                    ressort duquel l\'établissement de préparation du doctorat a son siège.
                </p>
            </div>
        </div>
    </div>

    <hr>

    <table class="signatures">
        <tr>
            <td>Date,</td>
            <td>Date,</td>
        </tr>
        <tr>
            <td>L\'auteur</td>
            <td>Le chef d\'établissement</td>
        </tr>
    </table>', e'body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 13px;
}

h1 {
    font-size: 22px;
}

h2 {
    font-size: 18px;
    margin-top: 15px;
    font-variant: small-caps;
}

hr {
    margin: 30px 0;
}

dl dt {
    font-weight: bold;
    margin-top: 10px;
}

.attestations {
    margin: 0;
}

.diffusion {
    margin: 0;
}

.signatures {
    margin: 30px auto;
    width: 90%;
}

.signatures td {
    width: 50%;
    height: 30px;
}

address {
    font-style: normal;
    font-size: 8px;
}

.attestations-item {
}

.attestations-item .attestations-item-value {
    margin-bottom: 5px;
}

.diffusion-item {
    margin-top: 10px;
    margin-bottom: 15px;
}

.diffusion-item .diffusion-item-value {
    margin-top: 10px;
}

.diffusion-item .autorisation-laius {
    margin-top: 0;
    font-size: 80%;
}

.autoris-diffusion-motif-div {
    margin-left: 30px;
    margin-top: 3px;
}

p.autoris-diffusion-motif {
    margin: 0 0 0 10px;
}

.droits-item .droits-item-value {
    margin-bottom: 10px;
}

.laius {
    font-size: 85%;
    font-style: italic;
}', 'Depot\Provider\Template', 'twig');
