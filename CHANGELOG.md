Journal des modifications
=========================

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
MISSING

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
