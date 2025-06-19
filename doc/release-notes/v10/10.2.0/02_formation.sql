update unicaen_renderer_template set document_corps = '<p>Bonjour VAR[Doctorant#Denomination],</p>
<p>Nous espérons que la formation s’est bien déroulée.<br>Pour l’obtention de l’attestation de VAR[Session#Duree] heures de formation, il est nécessaire de remplir le questionnaire de satisfaction sur ESUP-SyGAL pour télécharger l’attestation de formation sous un délai de VAR[Parametre#DelaiEnquete] jours. À défaut, la validation des heures de suivi de formation ne pourra être prise en compte dans le cadre de votre CSI. </p>
<p>Les résultats de l''enquête sont anonymes.</p>
<p>Le Collège des Écoles Doctorales normandes ainsi que votre ED vous remercient d''évaluer les formations avec précision afin d''en effectuer un bilan annuel exploitable pour faire évoluer l''offre de formations avec l''objectif de mieux répondre à vos besoins.</p>
<p> </p>
<p>Bien cordialement,<br>VAR[Formation#Responsable]</p>'
WHERE code = 'FORMATION_SESSION_TERMINEE';