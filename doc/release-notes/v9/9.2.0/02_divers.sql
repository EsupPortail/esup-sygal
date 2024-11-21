INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('UniteRecherche#Sigle',
        '<p>Retourne le sigle d''une unité de recherche</p>',
        'unite-recherche',
        'getSigle');

INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('Etablissement#Sigle',
        '<p>Retourne le sigle d''un établissement</p>',
        'etablissement',
        'getSigle');

UPDATE public.unicaen_renderer_template
SET document_corps = '<p>Bonjour,</p>
<p>Le rapporteur VAR[Acteur#Denomination] vient de rendre un avis défavorable pour la thèse de VAR[Doctorant#Denomination] (VAR[UniteRecherche#Sigle], VAR[Etablissement#Sigle]) intitulée VAR[These#Titre].<br />VAR[Acteur#Denomination] motive ce refus avec le justification suivante : VAR[Avis#Justification]</p>
<p> </p>
<p>Vous pouvez consulter le rapport de pré-soutenance en allant sur la page de gestion de la soutenance VAR[Url#SoutenanceProposition] ou en utilisante le lien suivant : VAR[Url#PrerapportSoutenance]</p>
<p>Vous avez reçu ce courrier électronique car :</p>
<ul>
<li>un avis défavorable a été rendu par un rapporteur</li>
<li>vous êtes soit :
<ul>
<li>un·e gestionnaire de la maison du doctorat de l''établissement d''inscription du doctorant</li>
<li>un·e responsable de l''école doctorale gérant la thèse de VAR[Doctorant#Denomination]</li>
<li>un·e responsable de l''unité de recherche accueillant la thèse de VAR[Doctorant#Denomination]</li>
<li>une personne participant à la direction de la thèse de VAR[Doctorant#Denomination]</li>
</ul>
</li>
</ul>'
WHERE code = 'SOUTENANCE_AVIS_DEFAVORABLE';

UPDATE public.unicaen_renderer_template
SET document_corps = '<p>Bonjour,</p>
<p>Le rapporteur VAR[Acteur#Denomination] vient de rendre un avis favorable pour la thèse de VAR[Doctorant#Denomination] (VAR[UniteRecherche#Sigle], VAR[Etablissement#Sigle]) intitulée VAR[These#Titre].</p>
<p>Vous pouvez consulter le rapport de pré-soutenance en allant sur la page de gestion de la soutenance VAR[Url#SoutenanceProposition] ou en utilisante le lien suivant : VAR[Url#PrerapportSoutenance]</p>
<p>Vous avez reçu ce courrier électronique car :</p>
<ul>
<li>un avis favorable a été rendu par un rapporteur</li>
<li>vous êtes soit :
<ul>
<li>un·e gestionnaire de la maison du doctorat de l''établissement d''inscription du doctorant</li>
<li>un·e responsable de l''école doctorale gérant la thèse de VAR[Doctorant#Denomination]</li>
<li>un·e responsable de l''unité de recherche accueillant la thèse de VAR[Doctorant#Denomination]</li>
<li>une personne participant à la direction de la thèse de VAR[Doctorant#Denomination]</li>
</ul>
</li>
</ul>'
WHERE code = 'SOUTENANCE_AVIS_FAVORABLE';

UPDATE public.unicaen_renderer_template
SET document_corps = '<p>Bonjour,</p>
<p>La soutenance de VAR[Doctorant#Denomination] (VAR[UniteRecherche#Sigle], VAR[Etablissement#Sigle]) a été acceptée par votre établissement.<br />Conformément aux informations que vous avez fournies celle-ci se tiendra le VAR[Soutenance#Date] dans VAR[Soutenance#Lieu].</p>
<p>Vous pouvez consulter les rapports de pré-soutenance en bas de la page de la proposition de soutenance : VAR[Url#SoutenanceProposition]</p>
<p><em>-- Justification ---------------------------------------------------------------------------</em></p>
<p>Vous avez reçu ce mail car :</p>
<ul>
<li>la maison du doctorat vient de donner son feu vert pour la soutenance</li>
<li>vous êtes soit :<br />
<ul>
<li>un·e acteur·trice direct·e de la thèse de  VAR[Doctorant#Denomination] ;</li>
<li>un·e responsable de l''école de doctorale gérant la thèse ;</li>
<li>un·e responsable de l''unité de recherche encadrant la thèse.</li>
</ul>
</li>
</ul>'
WHERE code = 'SOUTENANCE_FEU_VERT';

UPDATE public.unicaen_renderer_template
SET document_corps = '<p>Bonjour,</p><p>Ceci est un mail envoyé automatiquement par l''application ESUP SyGAL.</p><p>Une proposition de soutenance vient d''être faite pour la thèse suivante :</p><table style="width: 473.433px;"><tbody><tr><td style="width: 547px;"><strong>Titre</strong></td><td style="width: 467.433px;">VAR[These#Titre]</td></tr><tr><td style="width: 547px;"><strong>Doctorant·e</strong></td><td style="width: 467.433px;">VAR[Doctorant#Denomination] (VAR[UniteRecherche#Sigle], VAR[Etablissement#Sigle])</td></tr></tbody></table><p>Pour examiner cette proposition et statuer sur celle-ci merci de vous rendre dans l''application ESUP SyGAL : VAR[Url#SoutenanceProposition].<br /><br />-- Justification ----------------------------------------------------------------------</p><p> Vous avez reçu ce mail car :</p><ul><li>l''unité de recherche de la thèse de VAR[Doctorant#Denomination] ont validé la proposition de soutenance ;</li><li>vous êtes un·e responsable de l''école doctorale encadrant la thèse.</li></ul>'
WHERE code = 'SOUTENANCE_VALIDATION_DEMANDE_ED';

UPDATE public.unicaen_renderer_template
SET document_corps = '<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.<br /><br />La proposition de soutenance de thèse suivante a été validée par tous les acteurs et structures associées :</p>
<table>
<tbody>
<tr>
<th>Titre :</th>
<td>VAR[These#Titre]</td>
</tr>
<tr>
<th>Doctorant :</th>
<td>VAR[Doctorant#Denomination] (VAR[UniteRecherche#Sigle], VAR[Etablissement#Sigle])</td>
</tr>
</tbody>
</table>
<p>Pour examiner cette proposition merci de vous rendre dans l''application ESUP-SyGAL : VAR[Url#SoutenanceProposition].</p>
<p>-----------------------</p>
<p>Vous avez reçu ce mail car :</p>
<ul>
<li>la proposition de soutenance vient d''être validée par tous les acteurs directs et toutes les structures concernées ;</li>
<li>vous êtes soit :
<ul>
<li>un des acteurs directs de la thèse de VAR[Doctorant#Denomination]</li>
<li>un·e responsable de l''école de doctorale gérant la thèse,</li>
<li>un·e responsable de l''unité de recherche encadrant la thèse,</li>
<li>un·e gestionnaire du bureau des doctorat de l''établissement d''inscription du doctorant. <br /><br /></li>
</ul>
</li>
</ul>'
WHERE code = 'VALIDATION_SOUTENANCE_AVANT_PRESOUTENANCE';

-- Mise à jour de la vue src_domaine_hal
create or replace view src_domaine_hal(id, source_code, source_id, docid, havenext_bool, code_s, fr_domain_s, en_domain_s, level_i, parent_id) as
SELECT NULL::bigint AS id,
        tmp.source_code,
       src.id AS source_id,
       tmp.docid,
       tmp.havenext_bool,
       tmp.code_s,
       tmp.fr_domain_s,
       tmp.en_domain_s,
       tmp.level_i,
       tmp_parent.id as parent_id
FROM tmp_domaine_hal tmp
         JOIN source src ON src.id = tmp.source_id
         left join tmp_domaine_hal tmp_parent on tmp_parent.docid = tmp.parent_id
order by tmp.level_i;


--
-- Nouvelles colonnes dans THESE.
--

alter table these add resaisir_autorisation_diffusion_depot_version_corrigee boolean;
alter table these add resaisir_attestations_depot_version_corrigee boolean;
