--
-- 9.3.0
--

UPDATE unicaen_renderer_template SET document_corps = e'<p>Bonjour,</p>
<p>Par décision en date du VAR[Validation#Date], le chef de l\'établissement VAR[Etablissement#Libelle] vous a désigné·e pour participer au jury devant examiner les travaux de VAR[Doctorant#Denomination] en vue de l\'obtention du diplôme : Doctorat en VAR[These#Discipline].</p>
<p>Le titre des travaux est : VAR[These#Titre]<br /><br /><br />Les travaux sont dirigés par VAR[These#Encadrement]</p>
<p>La soutenance aura lieu le VAR[Soutenance#Date] à l\'adresse suivante :<br />VAR[Soutenance#Adresse]</p>
<p>La soutenance VAR[Soutenance#ModeSoutenance].</p>
<p>
    Pour information, seuls les membres du jury ci-dessous pourront être désignés président du jury de part leur statut de rang A :
    VAR[SoutenanceMembre#MembresPouvantEtrePresidentDuJuryAsUl]
</p>
<p>Vous pouvez accéder aux rapports de pré-soutenance grâce aux liens suivants :<br />VAR[Url#TableauPrerapports]<br /><br />Je vous prie d\'agréer, l\'expression de mes salutations distinguées.<br /><br />P.S.: Vous pouvez obtenir une version imprimable de cette convocation à l\'adresse suivante : VAR[Url#ConvocationMembre]<br /><br />
<em>-- Justification -----------------------------------------------------------------</em></p>
<p>Vous avez reçu ce mail car :</p>
<ul>
<li>la proposition de soutenance de VAR[Doctorant#Denomination] a été validée; </li>
<li>vous avez été désigné comme membre du jury pour la thèse de VAR[Doctorant#Denomination].</li>
</ul>', document_css = null, namespace = 'Soutenance\Provider\Template', engine = 'default'
WHERE code = 'SOUTENANCE_CONVOCATION_MEMBRE';
