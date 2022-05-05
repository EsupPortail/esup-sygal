Journal des modifications
=========================

4.0.5
-----
- [FIX] Correction du plantage de la page de saisie du mail perso doctorant.

4.0.4
-----
- Correction de typos dans mail de feu vert de la soutenance
- Ajout de redirection de mail lorsque certains mails n'ont pas de destinataire "ATTENTION MAIL NON DÉLIVRÉ".
- Compléments d'individu : mise en place des éléments de base 
- Modification du texte de mail de réussite au doctorat
- [FIX] verification des assertions au niveau des actions de PropositionController

4.0.3
-----
- Corrections et améliorations de la doc d'install suite aux remarques de l'université de Montpellier (merci)
- [FIX] Correction de l'affichage de la date d'historisation dans Individu lié
- [FIX] Correction de la signature de Membre::setEtablissement(?string) qui faisait planter la création de proprosition (lorsqu'aucun établissement n'était fourni)
- Ajout de l'unité de recherche des acteurs (manuel pour le moment) pour améliorer les pages de couverture

4.0.2
-----
- [FIX] Affichage de la bonne adresse électronique institutionnelle sur la fiche thèse 
- [FIX] Correction de la classe css sur les card de la page des soutenances à venir

4.0.1
-----
- [FIX] signature des fonctions de StructureSubstitHelper string => ?string
- [FIX] les résumés pour la BU se chevauchaient p class='resume pre-scrollable' => p class='pre-scrollable'
- [FIX] ajustement des css de la page proposition

4.0.0
-----
- Migration vers Laminas (back-end PHP).
- Migration vers Bootstrap 5 (front-end JS & CSS).
- Réorganisation des infos affichées à propos de la connexion dans le menu principal.
- Cas de la connexion d'un utilisateur sans possibilité de trouver d'individu associé : plus de création automatique d'individu car peut bloquer un import ultétieur.
- Amélioration de la page "Contact Assistance" en cas d'établissement indéterminé et/ou d'adresse d'assistance indéterminé ou invalide.
- [FIX] Plantage de la page "Contact Assistance" en cas de connexion avec un compte local.
- [FIX] Activation de la mise en cache de la config lorsque le mode development est désactivé.
- [FIX] Lancement de la synchro des thèses pour prendre en compte la création/modification/suppression de substitution de structures.
- Ajout des unités de recherche fermées dans le filtre des thèses
- [FIX] correction du bug lié au typage de retour trop strict de l'entité Structure
- Mise en place de la déclaration de non plagiat dans la proposition de soutenance
- [FIX] Plantage lors de la création/modification/suppression d'une substitution de structure ("Synchro introuvable avec ce nom : these")

3.0.12
------
- Le bouton d'impression du document pour signature du président reste visible même après validation de l'ED.
- Corrections de typos
- [FIX] Vérification du dépôt de l'avis de soutenance avant notification pour rappel
- Amélioration de l'affichage des soutenances à venir
- Retrait des co-encadrants du PV de soutenances
- [FIX] Résolution du plantage lors du téléchargement d'un rapport (activité, CSI, mi-parcours)

3.0.11
-----
- [FIX] Passage à unicaen/auth 3.2.11 pour affichage correct du rôle lorsqu'on endosse/sélectionne un rôle lié à une structure substituée.
- [FIX] Changement de signatures de fonctions pour une meilleure compatibilité
- Ajout de la liste des soutenances à venir dans l'index de l'application + lien vers la fiche de la these
- [FIX] Changement du comptage des engagements et des avis dans le cas de rapporteurs historisés mais ayant déjà rendu quelque chose
- [FIX] Le menu 'Mes thèses' inclut désormais les thèses soutenues (indispensable pour le profil Président du jury).
- [FIX] Utilisation de l'état ETABLISSEMENT pour le feu vert des soutenances plutôt que VALIDE
- Ajout d'une mention de l'envoi à tous les responsables de structure lorsque aucun responsable de site n'a été trouvé.
- Ajout du rapport technique dans les documents de la présoutenance (s'il y a au moins une visio de déclarée)
- [FIX] QPDF peut retourner 3 en cas de warnings non bloquants : on ajoute --warning-exit-0 pour retourner 0 même en cas de warnings.
- Modification des conditions d'affichage des documents de présoutenance pour le cas des soutenances rejetées
- Modification du template d'avis de soutenance.

3.0.10
-----
- Ajout de la mention "La réservation du lieu de soutenance n'est pas faite automatiquement et reste à votre charge"
- Meilleure gestion des tokens des membres d'une soutenance
- Déclaration tardive de visoconférence ajouté aux interventions de soutenance
- Avis sur rapport d'activité de fin de thèse
- Ajout (au moment de leur téléchargement) d'une page de couverture aux rapports d'activité validés
- Navigation : pas besoin de page 'Thèse sélectionnée' s'il y a une page 'Ma thèse'
- Notification à l'issue de la validation de la PDC : ajout de la BU en copie du mail
- Suppression des paramètres 'cookie_lifetime'et 'gc_maxlifetime' de la config de l'appliccation
- Refactorisation : extraction d'un nouveau module 'Doctorant' à partir du module 'Application'
- [FIX] Création du menu 'Ma thèse' même si la thèse est soutenue
- [FIX] Correction du comportement lorsque qu'un rapporteur d'une thèse précédente est de nouveau rapporteur d'auntre autre thèse

3.0.9
-----
- Dépôt des rapports d'activité, CSI, mi-parcours : possibilité de sélectionner l'année univ précédente.

3.0.8
-----
- Changement pour récupération des logos sur la page de couvertures (les co-directions sont maintenant utilisée)
- Le manque de justificatifs de soutenances n'est plus bloquant il s'agit plus que d'un warning 
- [FIX] Retrait de l'appel à un css inexistant
- Filtrage des soutenances sans date dans la vue des soutenances pour les structures.
- Ajout des mentions Co-encadrant/Co-encadrante sur la page de couverture
- Complétion des données pour la page de couverture depuis le dossier de soutenance en cas de manque
- Ajout de la memtion de la date limite de rendu dans le tableau de bord des rapporteurs
- Completion des mails avec la table des utilisateurs pour les notifications

3.0.7
----- 
- Nouveau critère de bloquage des soutenances basé sur la validité des rapporteurs.
- Retrait de la mention qualité absente sur le justificatif de co-encadrement
- Ajustement de la date/heure de rendu des rapports
- Modification du lien dans les mails vers les rapporteurs (description complète : token, role, redirect)

3.0.6
-----
- [FIX] Non affichage des justificatifs historisés dans la proposition de soutenance

3.0.5
-----
- Notification à propos de la validation de la page de couverture : correction de texte.
- Script de lancement de l'import run-import.sh : utilisation de flock pour éviter 2 lancements en parallèle.
- Téléchargement de la version initiale/corrigée imprimable : intitulé de titre plus précis sur la version concernée.
- Adaptation de la date limite de rendu des pré-rapports de soutenance lors du changement de la date de soutenance dans le dossier
- Ajout des événements pour la tracabilité de certaines actions du module soutenance
- Utilisation de qpdf (+ rapide que gs et + respectueux des metadata que pdftk) pour la concaténation ou l'amputation PDF.
- [FIX] Correction du sujet erroné du mail envoyé lorsque l'ajout de la page de couverture est terminé.
- [FIX] Meilleure remontée à l'utilisateur des erreurs rencontrées lors du dépôt de la thèse.
- [FIX] Log de la ligne de commande de retraitement de fichier : la notification peut avoir plusieurs destinataires.
- [FIX] Ligne de commande de test d'archivabilité : injection nécessaire du créateur.
- [FIX] Correction affichage de la date de rendu dans le mail envoyé aux rapporteurs

3.0.4
-----
- Amélioration des temps de réponse des requêtes SQL en abandonnant pasHistorise().
- Proposition de soutenance : l'adresse de l'établissement de chaque membre du jury est désormais demandée au doctorant.
- [FIX] Durée de conservation en session des données d'authentification (màj unicaen/app et auth).

3.0.3
-----
- Ajout d'une valeur d'état aux soutenances "Validée par l'établissement" post validation d'une soutenance par la présidence de l'établissement
- [FIX] Plus de demande de justificatif pour la confidentialité si la demande est faite en amont de la soutenance
- [FIX] La notif de validation de la version corrigée par le Président du jury faisait mention à tort du Directeur de thèse.
- [FIX] Plantage de l'export CSV des thèses à cause d'un appel de méthode erroné (getMailContact).

3.0.2
-----
- [FIX] Correction du plantage lors du réimport ponctuel d'une thèse.

3.0.1
-----
- Renommage de l'application en ESUP-SyGAL.
- Modèle de page de couverture de thèse personnalisable.
- Substitution possible du favicon.
- Possibilité d'accorder un sursis pour le dépôt de la verison corrigée de la thèse.
- Page d'accueil : laïus en cas d'utilisateur ayant plusieurs rôles.
- Page Utilisateur : affichage aussi des rôles attribués automatiquement.
- Templatisation de la notification de confirmation d'adresse électronique
- Ajout des gardes pour les actions de la page de presoutenance (au cas où l'accés est donnée aux acteurs directs)
- [FIX] Bloquage de la signature multiple de l'engagement d'impartilité
- [FIX] Bloquage de la validation multiple des propositions lors de la validation acteur
- [FIX] Correction du tri des thèses par titre (plantage) et par date de soutenance (inopérant)
- [FIX] Changement du sujet associé à la validation finale de la proposition de soutenance
- [FIX] Correction de l'envoi multiple de notification vers le président de jury lors du dépôt d'une version corrigée.
- Modification de l'assertion ouvrant la validation de l'UR
- [FIX] Impossibilité de déposer un rapport d'activité de fin de thèse en plus d'un rapport annuel.

3.0.0 (oracle => postgres)
-----
- Base de données : abandon d'Oracle et passage à PostgreSQL.
  *Attention : aucun script SQL universel de migration d'une base Oracle existante vers une base PostgreSQL n'est fourni 
  avec cette version. Si vous êtes déjà utilisateur de SyGAL en production, prenez contact avec les développeurs de 
  SyGAL pour réaliser une telle migration.*
- Ajout de bouton pour simuler les remontés des SI pour le jeury de thèse
- [FIX] Meilleure gestion d'erreur en cas de demande d'usurpation d'un individu n'ayant pas de compte utilisateur.

2.2.3
-----
- Scission du rôle "École doctorale" en 2 : "Responsable École doctorale" et "Gestionnaire École doctorale".
- Scission du rôle "Unité de recherche" en 2 : "Responsable Unité de recherche" et "Gestionnaire Unité de recherche".
- Envoi automatique par mail des jetons d'authentification créés + possibilité de les renvoyer.
- Utilisation des dates et lieux des dossiers de soutenances plutôt que celles saisies dans les SIs pour la génération des documents du module soutenance.
- Précision de la date de rendu des rapports dès le premier mail des rapporteurs
- Recherche de rapports d'activité : nouveau filtre "Annuel ou fin de thèse".  
- Fiche d'identité de la thèse : la date prévisionnelle de soutenance n'est plus affichée car elle peut être erronée.
- Rapports d'activité, CSI, de fin de thèse : la date de bascule pour déterminer l'année universitaire est le 01/11. 
- [FIX] Dédoublonnage des origines de financement dans le filtres de la page des rapports.

2.2.2
-----
- Authentification simplifiée des rapporteurs à l'aide d'un token.
- Le rapporteur peut annuler son avis de soutenance.

2.2.1
-----
- Ajout de la possibilité de modifier une proposition de soutenance pour gestion (sans retrait de validation)
- Ajout d'un filtrage des soutenances par états
- [FIX] Vilain plantage des aides de vues de navigation lorsque la route demandée n'existe pas.  
- [FIX] Ligne de commande de lancement de toutes les synchros.
- [FIX] Correction du texte de la convocation s'attendant à avoir un individu (pas toujours le cas car le lien n'est pas fait systèmatiquement).
- [FIX] La recherche du doctorant lié à un individu doit écarter les individus historisés.
- [FIX] Warning lors de la génération de la PDC à cause d'un tableau non initialisé"

2.2.0
-----
- Refonte des menus (menu principal déroulant notamment)
- Validation des rapports d'activité.
- [FIX] Désormais impossible d'ursurper un individu dont on ne trouve pas l'utilisateur (risque d'erreur de doublon oracle)  
- [FIX] AJout du 'Dépôt du rapport'
- [FIX] Correction du filtre Origine de financement ; dédoublonnage des origines en 4 exemplaires (1 par établissement).
- [FIX] Correction erreur de variable.
- [FIX] Suppression de 'Et ensuite' de la Fiche thèse car elle a été sortie du menu Dépôt Bertrand GAUTHIER 04/06/2021 14:55

2.1.9
-----
- Ajout de demande de justificatif pour certaines qualités pour les soutenances (par exemple : membre étranger)
- Mention du caractère confidentiel sur la page de couverture
- Generalisation des documents attachés aux structures + exploitation dans les convocations

2.1.8
-----
- Utilisation de l'établissement de la thèse pour le routing des mails du module soutenance au site concerné.
- Retrait d'espace et renommage des fichiers pour signature (module Soutenance).
- Téléversement des rapports annuels : renommage en rapports d'activité (annuel ou de fin de thèse) ; création des privilèges *_TOUT et *_SIEN.
- [FIX] Import : le préfixe 'SyGAL' par défaut pour ORIGINE_FINANCEMENT_ID est incorrect depuis que les origines sont de nouveau importées de chaque établissement.
- [FIX] Corrections pour le cas où le supannId est null.
- [FIX] Un utilisateur authentifié était associé au mauvais utilisateur si les données shib ne fournisse aucun supann{Ref|Emp|Etu}Id.

2.1.7
-----
- Changement du fonctionnement de sursis pour les soutenances : seul la validation acteur vérifie le délai de deux mois et le sursis annule cette vérification.
- Ajout d'une interface pour lier/délier des utilisateurs sans individu à un individu.
- Le rôle de co-directeur est maintenant un rôle attribué automatiquement
 
2.1.6
-----
- Utilisation de l'id permanent CHARTE_DEPOT_DIFFUSION_THESE pour télécharger la charte de dépôt et diffusion de thèse.
- Abandon de la nature de fichier 'divers' au profit de 'commun'.
- Rétablissement de l'import des origines de financement pour être en phase avec les releases du web service d'import.
- Financement : masquage possible de certaines origines de financement (ex : handicap).
- Financement : la visibilité des origines masquées peut être forcée grâce à un privilège.
- Interdiction de supprimer un fichier possédant un id permanent.
- Modification du document de soutenance pour signature du président pour faire figurer : numero étudiant, nouveau titre et les rôles
- Possibilité de saisir une intervention permetant au directeur de déclarer le président du jury en distanciel 
- Masquage possible de certaines origines de financement (ex : handicap).
- Signature pour convocation : ajout dans établissement d'inscription + usage dans les convocations de soutenance
- [FIX] Usurpation d'un compte local en BDD
- [FIX] Plantage de la page Assistance en cas d'authentification locale BDD
- [FIX] Injection manquante de la source SYGAL dans la création manuelle d'un utilisateur local (identifiant = adresse mail)
- [FIX] Date de soutenance et de fin de confidentialité : changement de l'élément de formulaire pour pouvoir sélectionner une année

2.1.5
-----
- Validation des corrections de thèses desormais réalisée par le président du jury (anc. directeurs de thèses).
- Recherche textuelle de thèses : rétablissement du cochage par défaut de tous les critères.
- Résolution du problème de plantage lorsque le flux des actualités est erroné.
- Interface de gestion des comptes locaux pour les présidents de jury.
- Test d'archivabilité : possibilité de configurer le passage par un proxy.
- Correction de l'import forcé de thèse : utilisation du unicaen/db-import pour synchroniser après l'import des données.
- Ajout d'un filtre pour l'index des propositions de soutenance (index-structure)
- Page Assistance : affichage d'infos techniques utiles ; infos incluses automatiquement dans le corps du mail lorsqu'on clique sur l'adresse mail d'assistance.
- Refonte de la page des utilisateurs (liste filtrable, détails utilisateurs, individus liés)
  
2.1.4
-----
- Rétablissement du rafraîchissement de MV_RECHERCHE_THESE après chaque import.

2.1.3
-----
- Réparation de l'usurpation d'un individu.
- Validation RDV BU : l'individu n'était pas injecté dans la Validation.
- Correction bug dans le message 'aucune validation trouvée' : affichage de l'id de la thèse.
- Correction bug de rémanence du rôle précédemment sélectionné dans UserContextService.

2.1.2
-----
- Gestion des co-encadrants (ajout et retrait)
- Listing (et export) des co-encadrants sur les EDs et URs
- Ajout à l'export de l'annuaire des co-encadrants
- Onglets dans les pages d'information des structures
- Page de connexion scindée par type d'authentification activée.
- Import du témoin "corrections effectuées" de chaque thèse.
- Pages de dépôt de la version corrigée : visibles dès lors que le témoin "corrections effectuées" est à Oui.
- Amélioration du temps de réponse de la recherche textuelle de thèses.
- Retour du bouton d'import forcé de thèse qui avait disparu à cause d'une erreur de config.
- Mise en retrait des items de menus concernant le dépôt de la version initiale en cas de corrections attendues ou effectuées.
- Correction de l'affichage du message en cas d'erreur d'authentification locale. 
- Correction d'un dysfonctionnement dans la recherche textuelle de thèses.
- Correction d'un dysfonctionnement dans le tri des thèses par date de 1ère inscription.
- Correction d'un dysfonctionnement dans l'affichage des établissements de rattachement lorsque la thèse n'est liée à aucune UR.

2.1.1
-----
- Liste des thèses : tri par date de 1ere inscription décroissante
- Page Utilisateur : accélération du chargement et ajout d'un bouton pour usurper
- Accueil de la partie dépôt : le rôle sélectionné est null quand l'utilisateur n'a aucun rôle
- Correction mineure sur les traductions dans le module de soutenance
- Suppression d'un terme 'SoDoct' oublié

2.1.0
-----
- Listes de diffusion Sympa : page d'activation/désactivation des listes pour lesquelles SyGAL peut fournir
  les abonnés et les propriétaires via une URL. 
- Correction des ACL d'accès aux lignes de commandes du module unicaen/oracle.  

2.0.1
-----
- Changement de stratégie pour générer le fichier de config contenant les numéro et date de version
- Correction plantage lorsque les données d'identité sont de type Utilisateur
- Retour du fichier config/autoload/version.global.php abandonné (la version ne peut pas être déduite par git pour l'instant)

2.0.0
-----

- Passage à Zend Framework 3 et PHP 7.3 (gain de performances).
- Utilisation d'un nouveau package unicaen/db-import pour la synchro.
- Listes de diffusion Sympa : page d'activation/désactivation des listes pour lesquelles SyGAL peut fournir
  les abonnés et les proopriétaires via une URL. 
- Nouveau rôle 'Authentifié(e)' permettant d'ajuster les privilèges d'un simple utilisateur authentifié. 
- Ajout des champs Thèmes et Lien pour les offres de thèses des ED
- Ajout du menu secondaire offre de thèse
- Refonte de la gestion du menu secondaire principal pour tenir compte correctement de l'affichage
- Ajout de la gestion multilingue du menu secondaire principal

1.4.9 (08/09/2020)
------------------

- Changement de catégorie pour les privilèges associés aux pages d'information.
- Ajout d'une configuration pour le fil d'actualité.
- Ajout du champ IdREF pour toutes les structures et modification de l'affichage/saisie des informations.
- Changement de l'affichage des structures fermées dans le filtre des thèses.
- Nouveau message "Dépôt terminé" au doctorant sur la page Rendez-vous BU.

1.4.8 (01/09/2020)
------------------

- Correction du nom de colonne ID_PERMANENT erroné dans le mapping de l'entité Fichier.
- Correction du path manquant pour l'upload.

1.4.7 (04/06/2020)
------------------

- Lors du dépôt d'une version corrigée, l'autorisation de mise en ligne est reprise texto (dupliquée) du 1er dépôt,
sauf si l'utilisateur possède le privilège "Saisie du formulaire d'autorisation de diffusion de la version corrigée", 
auquel cas elle est redemandée à l'utilisateur.
Idem pour les attestations et le privilège "Modification des attestations concernant la version corrigée".
- Masquage du complément de financement dans la fiche d'identité de la thèse
- Optimisation de l'export CSV des thèses
- Pages de téléversement et de recherche des rapports annuels.
- Correction d'un bug dans la recherche de thèses par nom du doctorant.
- Correction d'un bug dans le package Oracle APP_IMPORT qui ne filtrait pas les thèses selon l'établissement spécifié.
- Possibilité d'attribuer un "identifiant permanent" à un fichier (ex: 'RAPPORT_ANNUEL_MODELE') facilitant l'intégration
  de lien de téléchargement de ce fichier dans une page.
- Listes de diffusion dynamique Sympa alimentées par SyGAL : pages de consultation des listes de diffusion déclarées
  dans la config ; une URL pour fournir les abonnés, une autre pour fournir les propriétaires.

1.4.6 (29/05/2020)
------------------

- Ajout du drapeau "établissement d'inscription" et ajout des visualisations et interfaces pour gérer ce nouveau drapeau.
- Restriction du filtre des établissements sur la partie annuaire aux établissements d'inscription.
- Ajout dans structures des champs adresse, tel, fax, site web, email qui sont utilisables pour l'édition de document.
- Utilisation des nouveaux champs dans la génération de la convention de MEL (requiert unicaen/app v1.3.19).
- Amélioration de la recherche textuelle de thèses : ajout d'une liste déroulante permettant de sélectionner 
  précisément sur quels critères porte la recherche : "Titre de la thèse", "Numéro étudiant de l'auteur", 
  "Nom de l'auteur", "Prénom de l'auteur", "Nom du directeur ou co-directeur de thèse", 
  "Code national de l'école doctorale concernée (ex: 181)", "Unité de recherche concernée (ex: umr6211)".
- Correction d'un dysfonctionnement de la recherche textuelle sur les critères "numéro étudiant", "unité de recherche"
  et "école doctorale".


1.4.5 (08/04/2020)
------------------

- Correction du dysfonctionnement des notifications envoyées lors de certains événements sur les données importées 
  (résultat d'une thèse passant à admis, corrections facultatives ou obligatoires attendues).


1.4.4 (03/04/2020)
------------------

- Correction des 2 délais à respecter pour le second dépôt qui étaient intervertis par erreur 
  (nouvelles valeurs : 2 mois en cas de corrections facultatives attendues, 3 mois en cas de corrections obligatoires).


1.4.3 (11/03/2020)
------------------

- Import des types de financement de thèse.


1.4.2 (14/02/2020)
------------------

- Extraction CSV des thèses : nouvelles colonnes concernant l'embargo et refus de diffusion ; 
  virgule plutôt que point dans la durée de la thèse.
- Page d'accueil : affichage des actualités issues du flux RSS fourni par la COMUE.
- Filtrage de la liste des thèses : correction de l'affichage du filtre "Unité de recherche".
- Corrections de textes sur la page RDV BU.


1.4.1 (24/01/2020)
------------------

- Les dates d'insertion des données dans les tables `SYGAL_*` de chaque établissement sont désormais retournées 
  par le web service ; cela permettra côté SyGAL de détecter un problème dans le CRONage du script de remplissage 
  de ces tables.


1.4.0 (23/01/2020)
------------------

- La remise d'un exemplaire papier de la thèse n'est requise que si la diffusion est acceptée avec embargo ou refusée.
- Inversion des étapes Diffusion et Attestations.
- Modification des textes liés à l'autorisation de diffusion dans le formulaire et dans la convention PDF générée.
- Convention de MEL : suppression du petit logo dans l'entête puisqu'il y en a déjà un sous le titre
- Nouvelle charte de diffusion téléchargeable.
- Ajout du flag "fermé" pour les structures et utilisations dans la recherche de thèses.
- Ajout d'un champ "Id HAL" dans le formulaire d'autorisation de diffusion.
- Ajout d'un menu dépôt pour séparer les action liés au dépôt de la partie annuaire
- La couverture est maintenant recto/verso lorsque la premiere page n'est pas retirée
- Ajout de la colonne durée des thèses dans l'export
- Ajout des dates d'abandon et de transfert des thèses.

1.3.3 (18/12/2019)
------------------

### Ajouts

- Gestion des directeurs sans SUPANN_ID
    - Création de compte associé aux individus
    - Réinitialisation de mot de passe
    - Changement de l'affichage des warning associés

1.3.2 (27/11/2019)
------------------

### Ajouts

- Amélioration de l'affichage des rôles liés aux établissements
- Rénommage de 'Bureau du doctorats' en 'Maison du doctorat'
- Ajout d'un privilège pour l'affichage de l'adresse de contact du doctorant
- Ajout du menu 'Guide d'utilisation' sur l'accueil de l'application

1.3.1 (25/11/2019)
------------------

### Corrections

- Correction d'un bug empêchant la suppression auto de fichier lorsqu'on dépose une version retraitée manuellement.

1.3.0 (22/11/2019)
------------------

### Ajouts

- Convention de mise en ligne : 
    - Le libellé du tribunal compétent mentionné est importé de chaque établissement.
    - Utilisation de la mention générique "Le chef d'établissement" plutôt que d'exploiter les libellés 
      importés des établissements.
- Nouvelle ligne de commande pour importer une thèse à la demande.

### Corrections

- Import : 
    - Vidage et remplissage de chaque table temporaire n'étaient pas faits dans une même transaction !
    - Améliorations pour utiliser moins de mémoire ; meilleurs logs. 
    - Correction des exceptions de type `ORA-00001: unique constraint (SYGAL.TMP_ACTEUR_UNIQ) violated` 
      par un changement de stratégie côté web service (interrogation de tables plutôt que des vues). 
- Le bouton d'import d'une thèse à la demande avait disparu (menu "Page de couverture") à cause d'une config erronée.

1.2.11 (13/11/2019)
------------------

### Correction

- Changement dans l'export pour récupérer les dates et la liste de fichiers (qui avait été oubliés précédemment) pour les infos
    - Date de dépôt version initiale
    - Date de dépôt version corrigée
    - Thèse format PDF
    - Annexes
    
1.2.10 (5/11/2019)
------------------

### Ajout

- Un message avertissant des formats d'image valide est maintenant ajouté dans les pages de modification des structures concertes
- Utilisation de convert (imagemagick) pour convertir les logos "automatiquement" au format png 

1.2.9 (24/10/2019)
------------------

### Correction

- Correction de l'assertion gérant la saisie de conformité de la version corrigée achivable.

1.2.8 (21/10/2019)
------------------

### Correction

- Déplacement de l'INE dans les données Doctorant.

1.2.7 (17/10/2019)
------------------

### Ajout

- L'export des thèses contient maintenant deux colonnes supplémentaires : adresse électronnique de contact et INE
    - l'adresse électronique de contact est récupérée de la saisie faite à la première connection à SyGAL
    - l'INE sera rappatrié via les Web Services

1.2.6 (15/10/2019)
------------------

### Corrections

- L'envoi de mail concernant les résultats de thèses modifiés échouait à cause d'une erreur dans le chemin de la vue.


1.2.5 (04/10/2019)
------------------

### Améliorations

- Ligne de commande d'import : nouvel argument --verbose pour obtenir plus de logs.


1.2.4 (27/09/2019)
------------------

### Corrections

- Persistance du logo si non renseigné et changement de la redirection et des flashMessenger.


1.2.3 (29/08/2019)
------------------

### Corrections

- Correction du formulaire d'upload : l'élément CSRF n'était pas POSTé comme les autres.


1.2.2 (28/08/2019)
------------------

### Corrections

- Suppression des usages résiduels du privilège `InformationPrivileges::INFORMATION_FICHIER` qui n'existe plus.    


1.2.1 (11/07/2019)
------------------

### Ajout

- Nouvelle page consacrée au dépôt de fichiers divers liés à une thèse (précédemment dans la page "Thèse").
- Possibilité de déposer des fichiers dits "communs" utiles aux gestionnaires, ex: modèle d'avenant à la convention 
  de mise en ligne.

### Améliorations

- Améliorations de la page "Privilèges", notamment le filtrage par rôle. 
- Déplacement des privilèges de la catégorie `fichier-divers` vers la catégorie `these`
  car ils concernent des fichiers liés à une thèse (ex: PV de soutenance).
  La catégorie `fichier-divers` désigne désormais les privilèges concernant des fichiers sans lien aux
  thèses (ex: fichiers déposés pour les pages d'informations).
- Refonte technique de la gestion des fichiers liés aux pages d'informations, prélable au travail sur les droits de 
  dépôt de fichiers "divers" et "communs".


1.2.0 (10/07/2019)
------------------

### Améliorations

- Amélioration des temps de réponse du moteur de workflow en base de données.
- Refonte technique de la gestion des fichiers liés aux thèses, prélable au travail sur les droits de dépôt de 
  fichiers divers.


1.1.9 (14/05/2019)
------------------

### Ajout

- Role Rapporteur non membre du jury pour les cas particuliers. Seulement sur les pages de couverture.


1.1.8 (30/04/2019)
------------------

### Corrections

- Correction du nom de fichier généré lors d'un téléversement : application du formatter à tous les types 
  de fichiers téléversés pour éviter les collisions.


1.1.7 (23/04/2019)
------------------

### Corrections

- Abandon du TitreFormatter redondant et rétablissement du TitreApogeeFilter corrigé (pour ne plus subtituer les 
  guillemets français).
- Correction du bug dans le module unicaen/auth empêchant de s'authentifier via la fédération d'identité Renater.   


1.1.6 (15/04/2019)
------------------

### Corrections

- Remplacement des caractères spéciaux d'apogée pour les guillements par le biais d'un TitreFormatter.


1.1.5 (09/04/2019)
------------------

### Corrections

- Correction du requêtage des années universitaires de 1ère inscription pour le pavé de filtrage des thèses.


1.1.4 (03/04/2019)
------------------

### Nouveautés

- Ouverture à toutes personnes identifiées de la consultation de la liste des thèses, à la recherche de thèse et à la 
  visualisation de la page d'information associée à une thèse. 

### Améliorations

- Suppression dans la table `THESE` de la redondance de l'année universitaire de 1ere inscription avec la table
  `THESE_ANNEE_UNIV`. La colonne `ANNEE_UNIV_1ERE_INSC` n'est plus utilisée, vaut toujours NULL et disparaîtra dans 
  une version ultérieure. 
- Possibilité de changer le lien/logo affiché dans le pied des pages de l'application.

### Corrections

- Résolution du problème de l'année universitaire de 1ere inscription erronée en cas de changement de discipline.
  Requiert la version 1.2.5 du web service d'import.


Versions antérieures
--------------------

Avant la version 1.1.4, le journal des modifications n'avait pas encore été découvert.
