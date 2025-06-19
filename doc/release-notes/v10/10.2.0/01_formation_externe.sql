update unicaen_renderer_template set document_corps = '<p>Bonjour,</p>
<p>De nouvelles déclarations de formations externes ont été enregistrées par <strong>VAR[Doctorant#Denomination] </strong>(VAR[UniteRecherche#Sigle], VAR[Etablissement#Sigle]).</p>
<p>Vous êtes invité·e à vous connecter à l''application pour les examiner et, si nécessaire, les valider : VAR[Url#IndexGestionFormation]</p>
<p>Cordialement, </p>
<p><em>L’équipe SyGAL</em></p>'
WHERE code = 'FORMATION_EXTERNE_AJOUTEE';