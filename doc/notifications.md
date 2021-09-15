Notifications envoyées par l'application
========================================

```php
'notification' => [
    'templates' => [
        'NOTIF_VALID_PDC' => APPLICATION_DIR . '/module/Application/view/application/notification/mail/notif-validation-page-couverture.phtml'
        'NOTIF_FIN_RETRAIT' => APPLICATION_DIR . '/module/Application/view/application/these/mail/notif-retraitement-fini.phtml'
        'NOTIF_SAISIE_RDVBU' => APPLICATION_DIR . '/module/Application/view/application/these/mail/notif-modif-rdv-bu-doctorant.phtml'
        'NOTIF_VALID_RDVBU' => APPLICATION_DIR . '/module/Application/view/application/these/mail/notif-validation-rdv-bu.phtml'
        'NOTIF_DEVALID_RDVBU' => APPLICATION_DIR . '/module/Application/view/application/these/mail/notif-validation-rdv-bu.phtml'
        'NOTIF_RESULT_ADMIS' => APPLICATION_DIR . '/module/Application/view/application/these/mail/notif-evenement-import.phtml'
        'NOTIF_DOCT_RESULT_ADMIS' => APPLICATION_DIR . '/module/Application/view/application/these/mail/notif-resultat-admis-doctorant.phtml'
        'NOTIF_CORRECT_ATTEND' => APPLICATION_DIR . '/module/Application/view/application/these/mail/notif-depot-version-corrigee-attendu.phtml'
        'NOTIF_DATE_CORREC_DEPASS' => APPLICATION_DIR . '/module/Application/view/application/these/mail/notif-date-butoir-correction-depassee.phtml'
        'NOTIF_ATTENT_VALID_CORREC' => APPLICATION_DIR . '/module/Application/view/application/notification/mail/notif-validation-depot-these-corrigee.phtml'
        'NOTIF_MAIL_INCONNU_PRES_JURY' => APPLICATION_DIR . '/module/Application/view/application/notification/mail/notif-pas-de-mail-president-jury.phtml'
        'NOTIF_VALID_CORREC' => APPLICATION_DIR . '/module/Application/view/application/notification/mail/notif-validation-correction-these.phtml'
        'NOTIF_DEMAND_CONFIRM_MAIL' => APPLICATION_DIR . '/module/Application/view/application/doctorant/demande-confirmation-mail.phtml'
        'NOTIF_CHANG_ROLE' => APPLICATION_DIR . '/module/Application/view/application/utilisateur/changement-role.phtml'
        'NOTIF_CREA_COMPTE_LOCAL' => APPLICATION_DIR . '/module/Application/view/application/utilisateur/mail/init-compte.phtml'
        'NOTIF_REINIT_MOT_PASSE' => APPLICATION_DIR . '/module/Application/view/application/utilisateur/mail/reinit-compte.phtml'
        'NOTIF_LISTE_DIFF_MANQ_ADRESS' => APPLICATION_DIR . '/module/Application/view/application/liste-diffusion/mail/notif-abonnes-sans-adresse.phtml'
        'NOTIF_FIN_FUSION_PDC_THESE' => APPLICATION_DIR . '/module/Application/view/application/these/mail/notif-fusion-fini.phtml'
        'NOTIF_TELEVERS_THESE' => null, // en bdd
        'NOTIF_TELEVERS_RAPP_SOUTEN' => APPLICATION_DIR . '/module/Application/view/application/these/mail/notif-depot-rapport-soutenance.phtml'
    ],
];
```

Module Dépôt
------------

### Notification à propos de la validation de la page de couverture
- Quand :
  - Lorsque la page de couverture est validée.
- Déclenchement :
  - Auto
- Destinataires :
  - BU
- Code :
  - NOTIF_VALID_PDC
- Template :
  - module/Application/view/application/notification/mail/notif-validation-page-couverture.phtml

### Notification à propos de la fin du retraitement du fichier de thèse
- Quand :
  - Un utilisateur peut demander le retraitement automatique de la thèse PDF pour la rendre archivable. 
    Si le retraitement dure trop longtemps, il est relancé en ligne de commande sur le serveur de façon
    asynchrone et l'utilisateur est notifié lorsque le retraitement est terminé.
- Déclenchement :
  - Auto
- Destinataires :
  - Utilisateur ayant lancé le retraitement.
- Code :
  - NOTIF_FIN_RETRAIT
- Template :
  - module/Application/view/application/these/mail/notif-retraitement-fini.phtml

### Notification à propos de la saisie des informations pour le Rendez-vous avec la BU
- Quand :
  - Lorsqu'un doctorant renseigne/modifie les informations nécessaires au RDV avec la BU (disponibilités).
- Déclenchement :
  - Auto
- Destinataires :
  - BU
- Code :
  - NOTIF_SAISIE_RDVBU
- Template :
  - module/Application/view/application/these/mail/notif-modif-rdv-bu-doctorant.phtml

### Notification à propos de la validation Rendez-vous BU
- Quand : 
  - Une fois la validation du RDV BU enregistrée.
- Déclenchement :
  - Auto
- Destinataires : 
  - Doctorant (à la 1ere validation seulement).
  - MDD
  - BU
- Code :
  - NOTIF_VALID_RDVBU
- Template :
  - module/Application/view/application/these/mail/notif-validation-rdv-bu.phtml

### Notification à propos de la dévalidation Rendez-vous BU
- Quand : 
  - Lorsque la validation du RDV BU est annulée.
- Déclenchement :
  - Auto
- Destinataires : 
  - MDD
  - BU
- Code :
  - NOTIF_DEVALID_RDVBU
- Template :
  - module/Application/view/application/these/mail/notif-validation-rdv-bu.phtml

### Notification à propos de résultats de thèses passés à 'Admis'
- Quand : 
  - À l'issue de l'import, si des résultats de thèses sont passés à 'Admis'.
- Déclenchement :
  - Auto
- Destinataires : 
  - MDD
  - BU
- Code :
  - NOTIF_RESULT_ADMIS
- Template :
  - module/Application/view/application/these/mail/notif-evenement-import.phtml

### Notification du doctorant à propos de résultats de thèses passés à 'Admis'
- Quand :
  - À l'issue de l'import, si des résultats de thèses sont passés à 'Admis'.
- Déclenchement :
  - Auto
- Destinataires :
  - Doctorant dont le résultat de thèse est passé à 'Admis'.
- Code :
  - NOTIF_DOCT_RESULT_ADMIS
- Template :
  - module/Application/view/application/these/mail/notif-resultat-admis-doctorant.phtml

### Notification à propos de corrections attendues
- Quand :
  - À l'issue de l'import, si des témoins de corrections attendues passent à 'facultative' ou à 'obligatoire'.
  - Puis 1 mois avant la date butoir en cas de corrections obligatoires attendues.
- Déclenchement :
  - Auto
- Destinataires :
  - Doctorant
  - Directeur et co-directeur de thèse en copie en cas de corrections obligatoires
- Code :
  - NOTIF_CORRECT_ATTEND
- Template :
  - module/Application/view/application/these/mail/notif-depot-version-corrigee-attendu.phtml

### Notification à propos des thèses dont la date butoir pour le dépôt de la version corrigée est dépassée
- Quand :
  - JAMAIS ! À croner ? À supprimer ?
- Déclenchement :
  - Auto
- Destinataires :
  - MDD
- Code :
  - NOTIF_DATE_CORREC_DEPASS
- Template :
  - module/Application/view/application/these/mail/notif-date-butoir-correction-depassee.phtml

### Notification pour inviter à valider les corrections
- Quand :
  - Dans le cadre du dépôt d'une version corrigée :
    - soit après le dépôt de la version corrigée dans le cas où elle est testée/jugée archivable ;
    - soit après le dépôt de la version corrigée dans le cas où ell est testée/jugée non archivable mais retraitée 
      par l'application puis certifiée conforme par le doctorant.
- Déclenchement :
  - Auto
- Destinataires :
  - Président du jury
  - MDD en copie
- Code :
  - NOTIF_ATTENT_VALID_CORREC
- Template :
  - module/Application/view/application/notification/mail/notif-validation-depot-these-corrigee.phtml

### Notification à propos de l'absence d'adresse connue pour un président du jury
- Quand :
  - Lorsque la notification pour inviter à valider les corrections n'est pas possible car le PJ n'a pas d'adresse 
    connue.
- Déclenchement :
  - Auto
- Destinataires :
  - MDD
- Code :
  - NOTIF_MAIL_INCONNU_PRES_JURY
- Template :
  - module/Application/view/application/notification/mail/notif-pas-de-mail-president-jury.phtml

### Notification à propos de la validation des corrections attendues
- Quand :
  - Lorsque les corrections de la thèse ont été validées (par le président du jury). 
- Déclenchement :
  - Auto
- Destinataires :
  - MDD
  - Doctorant
- Code :
  - NOTIF_VALID_CORREC
- Template :
  - module/Application/view/application/notification/mail/notif-validation-correction-these.phtml

### Notification pour confirmation d'une adresse mail.
- Quand :
  - Pour finaliser la création d'un compte local.
  - Lors de la saisie de son adresse de contact par le doctorant.
- Déclenchement :
  - Auto
- Destinataires :
  - À l'adresse renseignée par un utilisateur et dont il faut confirmer la validité.
- Code :
  - NOTIF_DEMAND_CONFIRM_MAIL
- Template :
  - module/Application/view/application/doctorant/demande-confirmation-mail.phtml

### Modification dans vos rôles
- Quand :
  - Lorsque qu'un rôle est attribué/retiré à un utilisateur.
- Déclenchement :
  - Auto
- Destinataires :
  - L'utilisateur en question.
- Code :
  - NOTIF_CHANG_ROLE
- Template :
  - module/Application/view/application/utilisateur/changement-role.phtml

### Notification à propos de la création d'un compte local
- Quand :
  - Dès qu'un compte local est créé pour quelqu'un, pour l'informer de son identifiant de connexion et l'inviter 
    à initialiser son compte.
- Déclenchement :
  - Auto
- Destinataires :
  - Adresse de la personne spécifiée dans le formulaire de création d'un compte local. 
- Code :
  - NOTIF_CREA_COMPTE_LOCAL
- Template :
  - module/Application/view/application/utilisateur/mail/init-compte.phtml

### Notification à propos de la réinitialisation du mot de passe d'un compte local
- Quand :
  - Dès qu'une demande de réinitialisation du mot de passe d'un compte local est faite.
- Déclenchement :
  - Auto
- Destinataires :
  - Adresse mail associée au compte local en question.
- Code :
  - NOTIF_REINIT_MOT_PASSE
- Template :
  - module/Application/view/application/utilisateur/mail/reinit-compte.phtml

### Notification à propos d'abonnés de liste de diffusion sans adresse connue  
- Quand :
  - JAMAIS !
- Déclenchement :
  - Auto
- Destinataires :
  - Utilisateurs ayant le rôle "Administrateur technique".
- Code :
  - NOTIF_LISTE_DIFF_MANQ_ADRESS
- Template :
  - module/Application/view/application/liste-diffusion/mail/notif-abonnes-sans-adresse.phtml

### Notification que la fusion de la page de couverture avec la thèse PDF est terminée
- Quand :
  - Un utilisateur peut demander le téléchargement de la thèse PDF avec page de couverture. 
    Si la fusion de la thèse avec la page de couverture dure trop longtemps, la fusion est relancée en ligne de 
    commande sur le serveur de façon asynchrone et l'utilisateur est notifié lorsque la fusion est terminée.
- Déclenchement :
  - Auto
- Destinataires :
  - Utilisateur ayant demandé le téléchargement de la thèse PDF avec page de couverture.
- Code :
  - NOTIF_FIN_FUSION_PDC_THESE
- Template :
  - module/Application/view/application/these/mail/notif-fusion-fini.phtml

### Notification à propos du téléversement d'un fichier de thèse
- Quand :
  - Lorsqu'un fichier de thèse PDF est téléversé
- Déclenchement :
  - Auto
- Destinataires :
  - MDD
- Code :
  - NOTIF_TELEVERS_THESE
- Template :
  - En bdd (table NOTIF)

### Notification à propos du téléversement d'un rapport de soutenance
- Quand :
  - Lorsqu'un rapport de soutenance est téléversé
- Déclenchement :
  - Auto
- Destinataires :
  - MDD
- Code :
  - NOTIF_TELEVERS_RAPP_SOUTEN
- Template :
  - module/Application/view/application/these/mail/notif-depot-rapport-soutenance.phtml



Module Soutenance
-----------------

Doctorant => dir & codir => resp UR => resp ED => MDD (signature du pres).

### Notification à propos de la validation de la proposition de soutenance par un acteur direct
- Quand :
  - Lorsqu'une proposition de soutenance est validée par un acteur direct de la thèse (acteurs directs : doctorant, 
    directeur & codirecteur de thèse).
- Déclenchement :
  - Auto
- Destinataires :
  - Doctorant
  - Directeur et codirecteur de thèse
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/validation-acteur.phtml

### Notification à propos de la validation de la proposition de soutenance par une structure associée
- Quand :
  - Lorsqu'une proposition de soutenance est validée par une structure associée (ED ou UR ou MDD).
- Déclenchement :
  - Auto
- Destinataires :
  - ED ou UR ou MDD.
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/validation-structure.phtml

### Notification à propos de la dévalidation d'une proposition de soutenance
- Quand :
  - Lorsqu'une validation de proposition de soutenance est annulée.
- Déclenchement :
  - Auto
- Destinataires :
  - Auteur de la validation annulée.
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/devalidation.phtml

### Notification à propos de la totalité des validations obtenues par la proposition de soutenance
- Quand :
  - Lorsqu'une proposition de soutenance a obtenu toutes les validations attendues (acteurs directs & structures 
    associées).
- Déclenchement :
  - Auto
- Destinataires :
  - Doctorant
  - Directeur et codirecteur de thèse
  - ED
  - UR
  - MDD
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/validation-soutenance.phtml

### Notification à propos de la possibilité de procéder au renseignement des informations de soutenance
- Quand :
  - Lorsqu'une proposition de soutenance a obtenu toutes les validations attendues (acteurs directs & structures 
    associées).
- Déclenchement :
  - Auto
- Destinataires :
  - MDD
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/presoutenance.phtml
  
### Notification à propos du refus de la proposistion de soutenance
- Quand :
  - Lorsqu'une proposition de soutenance est refusée par un acteur direct ou une structure associée.
- Déclenchement :
  - Auto
- Destinataires :
  - MDD
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/refus.phtml
  
### Notification de demande de signature de l'engagement d'impartialité aux rapporteurs
- Quand :
  - ?
- Déclenchement :
  - ?
- Destinataires :
  - Chaque rapporteur du jury
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/engagement-impartialite-demande.phtml

### Notification à propos de la signature d'un engagement d'impartialité
- Quand :
  - Lorsqu'un engagement d'impartialité est signé par un rapporteur.
- Déclenchement :
  - Auto
- Destinataires :
  - MDD
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/engagement-impartialite-signature.phtml

### Notification à propos du refus de signature d'un engagement d'impartialité
- Quand :
  - Lorsqu'un rapporteur refuse de signer l'engagement d'impartialité.
- Déclenchement :
  - Auto
- Destinataires :
  - Doctorant
  - Directeur et codirecteur de thèse
  - MDD
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/engagement-impartialite-refus.phtml

### Notification à propos de l'annulation d'une signature d'un engagement d'impartialité
- Quand :
  - Lorsqu'une signature d'un engagement d'impartialité est annulée.
- Déclenchement :
  - Auto
- Destinataires :
  - Rapporteur ayant signé.
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/engagement-impartialite-annulation.phtml

### Notification de demande d'avis de soutenance aux rapporteurs
- Quand :
  - ?
- Déclenchement :
  - ?
- Destinataires :
  - Chaque rapporteur.
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/demande-avis-soutenance.phtml

### Notification d'invitation des rapporteurs à se connecter avec un token
- Quand :
  - Lorsqu'un rapporteur du jury est validé par la MDD à partir de la saisie du doctorant, et que l'appli lui crée
  un compte de connexion à l'aide d'un token.
- Déclenchement :
  - ?
- Destinataires :
  - Rapporteur
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/connexion-rapporteur.phtml

### Notification à propos du rendu de tous les avis de soutenance 
- Quand :
  - Lorsque le dernier avis de soutenance est saisi par un rapporteur.
- Déclenchement :
  - Auto
- Destinataires :
  - MDD
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/tous-avis-soutenance.phtml

### Notification à propos du rendu d'un avis de soutenance favorable
- Quand :
  - Lorsqu'un avis de soutenance favorable est saisi par un rapporteur.
- Déclenchement :
  - Auto
- Destinataires :
  - Doctorant
  - Directeur et codirecteur de thèse
  - MDD
  - ED
  - UR
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/avis-favorable.phtml
  
### Notification à propos du rendu d'un avis de soutenance défavorable
- Quand :
  - Lorsqu'un avis de soutenance défavorable est saisi par un rapporteur.
- Déclenchement :
  - Auto
- Destinataires :
  - Directeur et codirecteur de thèse
  - ED
  - UR
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/avis-defavorable.phtml
  
### Notification à propos de l'acceptation de la soutenance
- Quand :
  - ?
- Déclenchement :
  - Manuel ?
- Destinataires :
  - Doctorant
  - Directeur et codirecteur de thèse
  - ED
  - UR
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/feu-vert-soutenance.phtml

### Notification à propos de l'arrêt de la démarche de la soutenance
- Quand :
  - Lorsque la MDD déclenche l'arrêt de la démarche... ? Un mot du Pourquoi ?
- Déclenchement :
  - Auto
- Destinataires :
  - Doctorant
  - Directeur et codirecteur de thèse
  - ED
  - UR
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/stopper-demarche-soutenance.phtml

### Notification à propos de l'initialisation d'un compte
- Quand :
  - Lorsque la MDD déclenche l'arrêt de la démarche... ? Un mot du Pourquoi ?
- Déclenchement :
  - Auto
- Destinataires :
  - Doctorant
  - Directeur et codirecteur de thèse
  - ED
  - UR
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/stopper-demarche-soutenance.phtml

### Notification de retard dans le dépôt du rapport de présoutenance
- Quand :
  - ?
- Déclenchement :
  - Auto ?
- Destinataires :
  - Rapporteurs en retard dans le rendu du rapport de présoutenance
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/retard-rapporteur.phtml
  
### Notification de convocation du doctorant à la soutenance de thèse
- Quand :
  - ?
- Déclenchement :
  - Auto ?
- Destinataires :
  - Doctorant
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/convocation-doctorant.phtml

### Notification de convocation des membres du jury à la soutenance de thèse
- Quand :
  - ?
- Déclenchement :
  - Auto ?
- Destinataires :
  - Membres du jury sensés assister à la soutenance
- Code :
  - NOTIF_
- Template :
  - module/Soutenance/view/soutenance/notification/convocation-membre.phtml
  