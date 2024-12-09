--
-- 9.2.0
--

--
-- Emérites dans le jury de soutenance
--
alter table soutenance_qualite alter column rang drop not null;
update soutenance_qualite set rang = null where rang not in ('A', 'B');
update soutenance_qualite set rang = null where emeritat = 'O';
INSERT INTO unicaen_parametre_parametre (categorie_id, code, libelle, valeurs_possibles, valeur, ordre)
select cat.id, 'RATIO_MAX_EMERITES', 'Ratio maximal d''émérites', 'String', '0.25', 600
from unicaen_parametre_categorie cat where cat.code = 'SOUTENANCE';



--
-- Modification du template de mail SOUTENANCE_CONVOCATION_MEMBRE :
--      ajout de la liste des membres du jury pouvant être désignés président du jury.
--

INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('Membre#MembresPouvantEtrePresidentDuJuryAsUl',
        '<p>Retourne la liste à puce des membres pouvant être Président du jury</p>',
        'membre',
        'getMembresPouvantEtrePresidentDuJuryAsUl');

update unicaen_renderer_template set document_corps = $$<p>Bonjour,</p>
<p>Par décision en date du VAR[Validation#Date], le chef de l'établissement VAR[Etablissement#Libelle] vous a désigné·e pour participer au jury devant examiner les travaux de VAR[Doctorant#Denomination] en vue de l'obtention du diplôme : Doctorat en VAR[These#Discipline].</p>
<p>Le titre des travaux est : VAR[These#Titre]<br /><br /><br />Les travaux sont dirigés par VAR[These#Encadrement]</p>
<p>La soutenance aura lieu le VAR[Soutenance#Date] à l'adresse suivante :<br />VAR[Soutenance#Adresse]</p>
<p>La soutenance est encadrée par VAR[Soutenance#ModeSoutenance].</p>
<p>
    Pour information, seuls les membres du jury ci-dessous pourront être désignés président du jury de part leur statut de rang A :
    VAR[Membre#MembresPouvantEtrePresidentDuJuryAsUl]
</p>
<p>Vous pouvez accéder aux rapports de pré-soutenance grâce aux liens suivants :<br />VAR[Url#TableauPrerapports]<br /><br />Je vous prie d'agréer, l'expression de mes salutations distinguées.<br /><br />P.S.: Vous pouvez obtenir une version imprimable de cette convocation à l'adresse suivante : VAR[Url#ConvocationMembre]<br /><br />
<em>-- Justification -----------------------------------------------------------------</em></p>
<p>Vous avez reçu ce mail car :</p>
<ul>
<li>la proposition de soutenance de VAR[Doctorant#Denomination] a été validée; </li>
<li>vous avez été désigné comme membre du jury pour la thèse de VAR[Doctorant#Denomination].</li>
</ul>$$
WHERE code = 'SOUTENANCE_CONVOCATION_MEMBRE';
