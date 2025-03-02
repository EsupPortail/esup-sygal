Journal des modifications
=========================

9.3.0
-----
- Module Admission : Nouvelle pièce justificative : Attestation de responsabilité civile
- Module Admission : Création d'une page de configuration permettant de choisir les établissements d'inscription pouvant accéder au module
- Module Formation : Possibilité de déposer un fichier PDF pour la fiche de présentation d'une formation
- Module Formation : Ajout d'un tri pour les individus sur l'écran Inscriptions
- Module Formation : Ajout d'un bouton sur la fiche d'une session pour envoyer la feuille d'émargement aux formateurs
- Module Formation : Amélioration de l'UI concernant l'affichage des inscrits à une session de formation
- Passage unicaen/renderer 7.0.2 (et donc à TinyMCE 6.3.0).
- Filtre 'Catégorie de privilège' sur les pages 'Droits d'accès' : la liste est désormais issue d'une requête en bdd.
- Thèse : abandon des colonnes 'discipline_sise_code' et 'lib_disc' au profit de la jointure avec la table 'discipline_sise'.
- [FIX] Rapport d'activité : correction du filtrage selon le profil de l'utilisateur.
- [FIX] Module Admission : modification de la convention de formation doctorale impossible
- [FIX] Echec de l'envoi vers STEP-STAR 'Impossible d'extraire du nom de fichier... l'identifiant de la these' si l'individu/doctorant n'a pas de supannId : utilisation de l'INE ou de l'id doctorant.

9.2.1
-----
- [FIX] Bouton Forçage inaccessible (sur la fiche de la thèse) si la thèse n'était plus en cours
- [FIX] Module Admission : Notifications non reçues 
- [FIX] Modification d'une formation impossible 
- [FIX] Certaines méthodes n'étaient pas présentes dans les classes *TemplateVariable.php
- [FIX] Dysfonctionnements autour des comptes utilisateurs (accès à la page perturbé suite au passage à unicaen/utilisateur, bug dans la création de compte+individu et l'envoi de la notif).
- [FIX] Envoi de la notif 'Validation du dépôt de la thèse corrigée' à un président du jury via la page Admin > Présidents.
- [FIX] Utilisation de UrlService (plutôt que UrlHelper) pour générer l'URL d'usurpation d'identité à partir d'un individu.
- [FIX] Notifications Admission et Formation
- [FIX] Erreur lors de l'envoi de la notif 'Demande des pré-rapports de soutenance' (variable 'rapporteur' non transmise à UrlService).
- [FIX] Validation de la version corrigée de la thèse par le président du jury : améliorations (transaction bdd, remontée erreur, messages).
- [FIX] Fonction 'implode' manquante faisant planter l'envoi de la notif 'Validation des corrections de la thèse'.

9.2.0
-----
- Module Formation : impossibilité pour le doctorant de s'inscrire à plusieurs sessions de la même formation dans la même année universitaire
- Affichage de l'identité d'un individu : le format privilégé est désormais NOMPATRONYMIQUE Prénom.
- Amélioration de l'écran Inscriptions :
    - Ajout de plusieurs filtres : Site organisateur, Structure associée (ED), année universitaire
    - Ajout d'un sorter permettant de trier par les séances de l'inscription
- Module Formations : Si la séance est historisée, celle-ci n'est pas disponible dans le tableau (présent dans la fiche d'une session) pour renseigner les présences
- Module Admission : Rendre les informations à côté de certains champs du formulaire personnalisables en utilisant Renderer
- Ajout de l'UR et de l'établissement du doctorant dans certains templates du module rapport d'activité et soutenances
- Abandon de la bibliothèque unicaen/auth et migration vers unicaen/authentification, utilisateur, privilege.
- Dépôt d'une version corrigée de la thèse : possibilité de forcer pour une thèse en particulier les paramètres de config 'depot_version_corrigee.resaisir_autorisation_diffusion' et 'resaisir_attestations' (pas d'UI pour l'instant). 
- Réorganisation du menu Administration
- Nouveau document lié à la soutenance : Rapport technique de soutenance
- Formulaire de saisie d'une qualité de membre du jury : possibilité de spécifier 'aucun rang' (ex : émérites).
- Validité du jury de soutenance : nouvelle contrainte sur le nombre maximum d'émérites possibles (nouveau paramètre) ;
infobulle d'explication en cas de contrainte non respectée (ex: nb d'émérites > 25%).
- Soutenance : mention des personnes pouvant être Président du jury dans la notification 'Convocation des membres du jury'
- Génération de textes via unicaen/renderer : utilisation généralisée de classes *TemplateVariable pour ne pas polluer les classes métiers.
- Passage à la v1.6 de la bibliothèque apereo/phpCAS (correction de failles de sécurité de la v1.5).
- [FIX] Mise à jour d'une variable concernant un établissement impossible
- [FIX] Index de la gestion des co-encadrants inaccessible
- [FIX] Vue de synchronisation src_these erronée.
- [FIX] Module Admission inaccessible suite au bug de l'entité Discipline
- [FIX] Config : plus de fonctions directement dans des fichiers de config, ça vilain et ça complique les tests fonctionnels
- [FIX] Recherche des engagements d'impartialité existants : ajout de la jointure sur Individu il peut rester des engagements non supprimés !
- [FIX] Module Saisie de thèse : Enregistrement impossible des généralites si aucune discipline renseignée
- [FIX] $this->url(null,[],[],true) provoquait l'erreur 'No RouteMatch instance provided' : injections de UrlPlugin plutôt que UrlHelper car le RouteMatch n'est pas encore calculé et dispo dans ce dernier (cf. tâche #59885).
- [FIX] Orthographe de 'Arrêté' dans certains documents PDF générés.
- [FIX] Point médian dans un label du formulaire de création/modification d'une qualité de membre du jury.
- [FIX] Proposition de soutenance : bouton 'Valider la proposition de soutenance' visible à tort pour le doctorant.
- [FIX] Saisie du titre d'accès d'une thèse : exclusion des établissements historisés dans la liste déroulante.
- [FIX] Notification d'ouverture des inscriptions à une session de formation : correction pour le cas où la structure valide est de type établissement.
- [FIX] Création/modification de formation/session : intialisation et prise en compte correcte de l'url de recherche d'individu (responsable).
- [FIX] Notifications mails mentionnant le libellé d'une formation : 'Méthode [getLibelle] non trouvée'. 

9.1.1
-----
- [FIX] Bug "Entity of type 'Application\Entity\Db\Discipline' for IDs code(4100108) was not found"

9.1.0
-----
- Recherche de thèse : Remplacement de la vue matérialisée mv_recherche_these par une simple vue 'these_rech' pour ne plus avoir de pb de recherche de thèse infructueuse nécessitant un refresh.
- Etablissements : suppression du témoin 'est établissement membre' non pertnient en cas d'absence de COMUE et redondant avec 'est établissement d'inscription'.
- UI de l'application : 
  - uniformisation des boutons de l'application
  - amélioration du module soutenance
  - Sticky footer
- [FIX] Modification de la requête présente à l'origine dans 9.0.0/03_saisie_these.sql, afin d'associer le nouveau champ pays_id (table titre_acces) aux bons pays
- [FIX] .gitlab-ci : correction des URL générées dans la release.
- [FIX] Contrainte de référence ajoutée récemment à tort (empêche la suppression d'un manuscrit de thèse déposé).
- [FIX] Modification du mapping de Thèse : la relation "anneesUniv1ereInscription" ne pointait plus vers un VTheseAnneeUnivFirst
- [FIX] Lien d'une séance non visible pour un doctorant lors de l'affichage de la fiche d'une session de formation

9.0.1
-----
- Individu : remplacement des usages de getCiviliteToString() par getCivilite() qui faisait la même chose ; puis modif de getCiviliteToString() pour retourner la civilité au format long.
- [FIX] Génération de la page de couverture impossible : PageDeCouverturePdfExporter ne doit pas être un singleton afin d'éviter des erreurs de chemins de templates
- [FIX] Qualité d'un Acteur provenant de SI n'était plus affichée dans certains cas (page de couverture...)
- [FIX] Plantage de la génération des convocations sur la page Préparation de la soutenance : 'Call to undefined method These\Entity\Db\Acteur::getCivilite()'.

9.0.0
-----
- Nouveau module Saisie de thèse : 
  - Module permettant de saisir toutes les informations nécessaires (structures, acteurs, financements...) à la création d'une thèse dans SyGAL.
- Module Admission : Possibilité également pour un gestionnaire de déposer la charte doctorale signée par les parties prenantes
- Module Formation : Affichage pour le doctorant de l'objectif et du programme d'une formation (sur la fiche d'une session de formation) 
- Module Formation : Affichage des sessions (sur l'index des gestionnaires et doctorant) en fonction des séances les plus proches
- Module Formation : Possibilité de déclarer une date de publication pour afficher/cacher des sessions aux doctorants
- Module Formation : 1ère version d'un export des sessions depuis l'index, en fonction des filtres choisis
- Module Formation : Ajout sur l'index des sessions, d'un tri sur les séances
- Module Soutenances : Affichage de l'historique des actions effectuées (horodatages) en dessous de chacune d'elles 
- Amélioration de la page consacrée aux profils (filtrage, explications, esthétique) et harmonisation de la page Rôles et privilèges.
- Création/modif d'établissement d'inscription, d'ED/UR : obligation de fournir un logo ; harmonisation des formulaires de création/modif d'ED/UR.
- Variables d'établissement : améliorations de la création/modif de variable (notamment choix du 'code' parmi une liste fermée).
- [FIX] Notifications de demande d'engagement d'impartialité et d'avis de soutenance : utilisation en priorité du mail de l'acteur.
- [FIX] Impossible d'accèder aux pages de consultation/modification d'ED ou d'UR.
- [FIX] Coquille dans le procès verbal de soutenance (PDF).
- [FIX] Modification de l'adresse l'adresse institutionnelle/pro d'un individu non prise en compte
- [FIX] Avis concernant des propositions historisées qui pouvaient remonter
- [FIX] Certains justificatifs demandés associés à la proposition étaient affichés avec leur codes (et non leur label)

8.6.0
-----
- Possibilité de déclarer un individu apatride (i.e. sans nationalité) ; réorganisation du formulaire.
- Suppression de la contrainte vérifiant si le membre est un rapporteur lors de la liaison entre un acteur et un membre dans la prép. de la soutenance.
- Nouveau favicon, moche mais universel !
- Possibilité de spécifier le username dans l'URL de modification de mot de passe (paramètre GET).
- Formateur du nom de fichier pour la fusion PDC & manuscrit : séparateur 'underscore' car STEP-STAR n'aime pas le tiret du 6.
- Module Admission : Export vers Pégase -> concernant le sexe, M et Mme deviennent M et F
- Module Admission : Export vers Pégase -> export seulement des thèses dont l'individu n'est pas encore doctorant
- Module Admission : Ajout d'un deuxième champ pour saisir un financement supplémentaire
- Module Admission : Possibilité de télécharger la charte doctorale, puis de déposer (par le candidat) la charte doctorale signée 
- Module Admission : Possibilité de saisir le code voeu/période par la/le gestionnaire de l'ED, nécessaire pour l'export vers Pégase
- Possibilité pour le futur doctorant de se créer un compte utilisateur local pour saisir un dossier d'admission.
- Etablissements : 3 saisies dédiées selon le type d'étab (d'inscription, CED, autre) ; amélioration du listing des établissements.
- [FIX] Listing des rapports d'activité : plantage en cas de liste d'années universitaires vide.
- [FIX] Procédure privilege__update_role_privilege() en bdd : abandon des retraits de privilèges en fonction des profils car des rôles ne sont associés à aucun profil.
- [FIX] Recherche textuelle d'individu : la prise en compte de chacun des termes avait été sabotée !
- [FIX] La recherche des rôles de l'utilisateur connecté n'écartait pas ceux dont la structure était historisée.
- [FIX] Le bouton de recherche/liaison de notice IdRef ne fonctionnait pas dans une modale (unicaen/idref 1.0.0 requis).
- [FIX] Synchro : plantage lors de la génération du diff (unicaen/db-import 6.1.3 nécessaire car nouvelle plateforme Doctrine\DBAL\Platforms\PostgreSQL120Platform).
- [FIX] Le menu Nos Thèses s'affichait à tort pour le rôle Doctorant (du fait que ce dernier est désormais tagué établissement-dépendant).

8.5.0
-----
- Module Soutenance : modif de la qualité par défaut des membres lors de la création automatique de la proposition (lors du 1er accès à la page Proposition).
- Structures : la création des rôles par défaut à partir des profils existants oubliait de créer le lien role-->profil.
- Doc : exemple de config SMTP plus complet dans config/autoload/secret.local.php.dist.
- Doc : config ImageMagick en cas d'erreur 'attempt to perform an operation not allowed by the security policy PDF' lors de la création de l'aperçu d'une page de couverture.
- BDD : ajout de triggers pour créer automatiquement les rôles associés à toute nouvelle structure créée (Etab d'inscription / ED / UR).
- Module Admission : Ajout de la génération d'un fichier ZIP pour l'export des dossiers d'admission (fichiers dans l'archive : admis.csv, admissions.csv)
- Module Admission : Changement du format du numéro de candidat, nécessaire pour l'export vers Pégase : SYG+AnnéeUniversitaire1èreInscription+NuméroDeDossier
- Module Formations : Affichage systématique du nom patronymique pour le formateur
- Modification de la génération du menu formations en fonction du rôle de l'utilisateur connecté
- [FIX] Requête de recherche de thèses par acteur : les acteurs historisés n'étaient pas écartés.
- [FIX] Module Information : utilisation d'une langue par défaut (FR) pour permettre la création d'une page d'information.
- [FIX] Rapport d'activité : PHP Notice à propos d'une variable non définie.
- [FIX] Erreur 'L'alias ur est inconnu du QueryBuilder' en cliquant sur le bouton 'Générer rôles par défaut' pour une ED.
- [FIX] Module Formation : l'affichage du tableau des sessions de formation était buggé (à cause de l'affichage de la description d'une formation)

8.4.0
-----
- Filtre de recherche textuelle utilisant l'opérateur LIKE : amélioration pour qu'il soit insensible à la casse.
- Module Admissions : Export CSV des dossiers d'admissions validés (au format attendu pour l'export vers Pégase)
- Module Admissions : Appel API Insee afin de récupérer les codes/libellés des communes françaises nécessaires à l'export vers Pégase
- Module Admissions : Suppression du privilège/notification pour notifier lors de commentaires ajoutés (notifier-commentaires-ajoutes)
- Module Admissions : Suppression de la notification lors de la dévalidation de la lecture de la convention de formation doctorale
- Module Admissions : Modification des sujets/corps de certains templates
- Module Admissions : Changement du libellé du bouton à la dernière étape en fonction de l'état du dossier et de l'utilisateur connecté
- Module Admissions : Récupération de la charte doctorale à partir du fichier déposé sur la fiche de l'établissement
- Module Admissions : Ajout d'un récapitulatif des commentaires ajoutés sur la page d'accueil du module, ainsi qu'à la dernière page
- Module Admissions : Amélioration de l'UI lorsque la (co)direction de thèse est mal renseignée dans le formulaire
- Module Formations : Ajout de filtres sur l'écran Inscription : recherche nom/prenom du doctorant, état de la session
- Module Formations : Export CSV des inscriptions en fonction de la recherche effectuée
- Améliorations des doc et scripts d'install.
- Améliorations de la page d'initialisation de compte utilisateur local.
- Formulaire de création d'un individu à partir d'un utilisateur : initialisation manquante du nom patronymique.
- Module StepStar : mise à jour du script bash d'envoi quotidien des thèses vers STEP/STAR.
- Module StepStar : plus d'envoi de la date d'abandon (recommandation de l'ABES).
- Module StepStar : ajout dans le TEF des IdRef de tous les acteurs de la thèse (doctorant, directeur, codirecteur, etc.)
- Module StepStar : possibilité de télécharger les fichiers TEF générés sur la page 'Génération TEF'.
- Module StepStar : prise en compte des paramètres SOAP 'cache_wsdl' et 'keep_alive' pour la connexion au WS.
- Suppression du menu 'Administration > Compléments d'individu' devenu inutile.
- Amélioration visuelle des pages de création/modification de structure (Etab/ED/UR).
- Affichage des co-encadrants sur l'annuraire des thèses (au survol de la souris sur la direction de thèse)
- Rapport d'activité : le directeur de thèse doit désormais valider même si c'est lui (ou un gestionnaire) qui a fait la saisie.
- Rapport d'activité : modification du message à propos da la date limite, figurant sur la page listant les rapports d'activité.
- Pointeur vers la doc fonctionnelle dans doc/README.md.
- [FIX] Warning PHP sur la page de création/modification d'ED/UR.
- [FIX] Authentification LDAP/CAS : la recherche de l'utilisateur en bdd à partir du username n'utilisait pas le paramètre de config 'ldap_username'.
- [FIX] Authentification LDAP/CAS : les données d'identité de type \UnicaenApp\Entity\Ldap\People n'étaient pas bien traitées.
- [FIX] Utilisation du bon code structure pour les établissements (source_code).
- [FIX] : Ajout de conditions pour éviter que InscriptionAssertion retourne une erreur lors de l'accès à un écran présentant des formations (ayant des sessions sans séances) ou d'une désinscription
- [FIX] : Initialisation de compte : ajout du filtre 'StringTrim' sur les champs du formulaire.
- [FIX] Templates du mail de convocation à la soutenance pour les membres: correction de la méthode associée à la variable Soutenance#Adresse.
- [FIX] Modification d'un Individu : l'idRef est désormais null en cas de chaîne de caractères vide.

8.3.0
-----
- Module Step-Star > Ligne de commande : améliorations des logs (affichage des critères appliqués, notamment) ; factorisation.
- Module Step-Star : suppression du paramètre de config 'clean_after_work', remplacé par un paramètre de ligne de commande ou de formulaire.
- Documentation : mise à jour et amélioration des script et doc d'installation.
- [FIX] Génération de la PDC : suppression de la mention 'Co-encadrant' ou 'Co-encadrante' en trop dans le tableau du jury.

8.2.0
-----
- Page des privilèges accordés aux profils : cosmétiques + ancres de navigation sur les catégories et privilèges.
- Module Formations : Filtre sur l'année universitaire en cours pour le catalogue des formations
- Module Formations : ajout d'une contrainte pour se désinscrire d'une formation, jusqu'à 3 jours avant le début de celle-ci (paramètre modifiable directement dans l'application)
- Module Formations : lieu d'une séance visible seulement pour les inscrits à la session (Assertion)
- Module Formations : ajout d'un message dans le formulaire d'ajout/modification d'un rapport activité pour indiquer au doctorant qu'il peut sauvegarder régulierement pour éviter de tout perdre
- Refonte de la page soutenances à venir (présente sur la page d'accueil):
  - Changement du titre : Soutenance actuelles
  - Affichage des ED non fermées seulement
  - Affichage des soutenances jusqu'à 1 an après leur passage
- Module Formations : envoi d'un mail aux doctorants (appartenant aux structures valides déclarées d'une session) lors du passage à "Inscription ouverte" d'une session (<b>d'une formation spécifique</b>)
- Module Formations : refonte des écrans Formations/Sessions :
  - Ajout des filtres année universitaire, et type (seulement pour sessions)
  - tri par défaut en fonction de la date des séances (de la plus récente à la plus ancienne)
- Module Formations : Affichage du nom patronymique sur l'attestation et la fiche d'émargement d'une formation
- Proposition de soutenance : affichage de la direction de thèse amélioré, accès à la modif du complément d'individu (email) et de l'acteur (typiquement UR du codir étranger).
- Correction et mise à jour de la doc d'installation.
- Step-Star : envoi systématique d'une date de soutenance prévisionnelle null.
- Step-Star > Ligne de commande : nouveaux filtres de sélection des thèses 'date de soutenance nulle' et 'date de soutenance max' (+ amélioration de la syntaxe avec interval de temps).
- Step-Star > Génération TEF : mention de l'application 'SYGAL' dans l'attribut 'CONTENTIDS' de la balise 'mets:div' (à la demande de l'ABES).
- [FIX] Step-Star : requête de sélection des thèses à envoyer (colonne erronée pour le filtre établissement).
- [FIX] Page de couverture de thèse : l'établissement affiché pour le codir était l'établissement du dernier membre du jury affiché.
- [FIX] Page de couverture de thèse : l'établissement forcé de l'acteur n'était pas pris en compte dans le tableau.
- [FIX] ré-attribution du privilège d'accès à l'index des formations pour les formateurs
- [FIX] Le template de notification SOUTENANCE_FEU_VERT avait besoin de la variable 'soutenance' (proposition).
- [FIX] Import des domaines HAL : renommage du nom de la connexion dans la config et ajout de celle-ci dans local.php.dist.
- [FIX] Création d'individu : correction de l'erreur 'Call to a member function getSource() on null' lorsqu'on enregistre.
- [FIX] Authentification LDAP : message d'erreur sibyllin 'UnicaenApp\\Entity\\Ldap\\People'.

8.1.0
-----
- Refonte de la gestion des sites et structures des individus : ajout d'un périmètre supplémentaire à l'attribution
  des rôles (individu_role_etablissement), déplacement de l'établissement et de l'UR de individu_compl vers acteur.
- Amélioration de l'affichage des acteurs sur la fiche thèse.
- Proposition de soutenance : prise en compte dans l'assertion du profil Gestionnaire d'ED pour le privilège 'Modification d'une proposition de soutenance pour gestion'.
- Proposition de soutenance : possibilité de rendre facultative la saisie de la date et heure de soutenance si besoin.
- Ajout de la possibilité de saisie des domaines HAL à partir de la fiche de la thèse et/ou lors du formulaire de signalement
- Amélioration de la gestion d'une suppression de proposition de soutenance
  - Ajout d'un bouton directement à partir de la proposition de soutenance
  - Ajout d'un privilège 
  - Envoi d'un mail pour notifier les acteurs directs de la thèse
- Refonte du processus d'ajout d'un nouveau rapport d'activité : l'année du rapport est choisie en amont afin de de pouvoir filtrer les formations (affichées ensuite dans le formulaire)
- Menu principal : menus déroulants plus compacts ; amélioration du menu Administration ; 'charte.scss' avant 'app.scss'.
- Module Substitutions : possibilité de créer une substitution manuellement à partir de la fiche individu/structure.
- Recherche d'individus : affichage du nom patronymique ; affichage des 2e et 3e prénoms dans une colonne dédiée.
- Formulaire individu : modification limitée en cas d'individu importé.
- [FIX] Liste/recherche des utilisateurs : dysfonctionnement des filtres à cocher qui s'appliquait quelle qu'était leur valeur.
- [FIX] Ajout d'une mission d'enseignement : le bouton ne fonctionnait plus à cause d'une erreur de config de la route.
- [FIX] Proposition de soutenance > Fichiers associés > Téléversement d'un justificatif : la liste des membres était toujours vide.
- [FIX] Soutenances à venir par ED : éviction des ED historisées et tri par sigle ; cosmétique dans la page consacrée à une ED. 
- [FIX] Notification de feu vert pour la soutenances : mauvais template spécifié donc plantage du rendu donc pas de notif. 

8.0.0
-----
- Nouveau module Admission (Formulaire permettant de candidater à un parcours doctoral)
  - Saisie des informations concernant l'étudiant, son parcours, son inscription et ses spécifités et le financement
  - Dépôt de pièces justificatives
  - Remplissage et validation en ligne de la convention de formation doctorale
  - Validation en ligne de la charte doctorale
- Rapports d'activité : initialisation du formulaire de création d'un nouveau rapport avec les formations suivies (module Formation).
- Stockage de fichier S3/Filesystem : plus de copie sur disque dans le cas d'une demande d'un fichier issu du Filesystem.
- Inclusion au script de purge des fichiers temporaires ceux générés par le FichierStorageService.
- Module Substitutions : amélioration de fonctions pgsql du moteur de substitutions.
- Module Substitutions : possibilité de lancer la création d'une substitution depuis la page des doublons.
- Module Substitutions : nouvelles pages d'informations concernant les triggers utilisés en bdd, pour chaque thème.
- Structures : amélioration visuelle sur la page listant les structures (Etablissement, ED, UR).
- Listing/recherche d'individus : lien vers la substitution si individu substituant ; nouveau filtre 'Historisés inclus' décoché par défaut ; cosmétique.
- [FIX] Module Substitutions : plantage de la page consacrée aux doublons (substitutions possibles).
- [FIX] Page Assistance : plantage de la page lorsque le rôle de l'utilisateur est structure-dépendant.
- [FIX] Plantage lors de l'accès à la page de création d'une UR/ED.

7.0.1
-----
- [FIX] Module Substitutions : plantage de la page consacrée aux doublons (substitutions possibles).
- [FIX] Module Substitutions : plantage de la page de détails d'une substitution d'UR.
- [FIX] Services de recherche : la jointure avec la structure substituante n'est plus à faire.
- [FIX] Utilisation de Structure::getCode() par getSourceCode() pour les établissements d'inscription (correction d'effets de bord des substitutions).
- [FIX] Module formation : élimination des ED historisées dans la liste des structures complémentaires ajoutables à une session.
- [FIX] Module formation : changement du type de retour dans le trait StructureAwareTrait.
- [FIX] Recherche de thèses : amélioration de l'affichage et du tri des structures dans les filtres.
- [FIX] Recherche autocomplete établissement/ED/UR : bug empêchant la recherche (systématiquement aucun résultat).
- [FIX] Page de modification d'une UR : paramètres de route erronés ; suppression de warnings PHP de variables inconnues.
- [FIX] Module Substitutions : modification de la formule de caclul du NPD doctorant.
- [FIX] Recherche de doctorant par individu : plus d'exception si 2 doctorants liés au même individu, on prend le plus récent.
- [FIX] Affichage du formulaire de saisie par le doctorant des informations nécessaires au RDV BU.  

7.0.0
-----
- Nouveau module Substitutions (moteur de dédoublonnage automatique d'individus et de structures importés en doublons).
- [FIX] Création en bdd d'un privilège manquant.
- [FIX] Page de couverture générée pour la thèse : correction de l'établissement mentionné pour un codirecteur (exploitation de l'info acteur).
- [FIX] Génération PDF des convocation et attestation de formation : augmentation de 'pcre.backtrack_limit' pour éviter l'erreur 
  'The HTML code size is larger than pcre.backtrack_limit 1000000. You should use WriteHTML() with smaller string lengths. Pass your HTML in smaller chunks'. 

6.0.10
------
- Génération de la page de couverture de la thèse : refonte graphique dans le sens de la sobriété.
- Nouvelle entrée 'Rapports CSI' dans le menu 'Nos thèses' des gestionnaires/responsables d'ED/UR.
- Petites améliorations autour des notifs concernant les changements détectés lors de l'import (admission, corrections attendues).
- Financements sur la fiche Thèse : afficher la nature devant chaque info (type, origine, quotité).
- Page de connexion : amélioration du texte expliquant l'authentification via la fédération d'identité (NB : le texte est dans la config locale).
- Annuaire des thèses : initialisation du filtre ED/UR/Etab avec la structure du rôle utilisateur (avec possibilité de changer).
- [FIX] Observation des résultats d'import/synchro : correction du filtrage par source de données (anciennement 'etablissement') non pris en compte.
- [FIX] Upgrade d'unicaen/app pour corriger le lien Ajouter dysfonctionnel dans le formulaire Rapport d'activité.
- [FIX] Appel de fonction erroné faisant planter la notification 'correction attendue'.
- [FIX] Correction de l'appel de getNomComplet qui etait utilisée sur un acteur plus qu'un individu lors de l'envoi des convocations
- [FIX] Changement d'action pour la modification de l'adresse de soutenance dans la partie preparation de la soutenance
- [FIX] Correction de l'oubli de l'adresse (nouveau format) dans l'avis de soutenance
- [FIX] Formulaire RDV BU : impossible d'enregistrer si les attestations manquent (une case à cocher disabled le signale) ;
  corrige le bug des attestations manquantes bloquant le dépôt de la version corrigée. Correction d'un bug dans la notification 'RDV BU validé'.
- [Formation] Complétion de la case 'lieu' sur les convocations en fonction du contexte (mention "Distanciel" ou "Pas de lieu")

6.0.9
-----
- [FIX] Création en bdd d'un privilège manquant.
- [Soutenance] Saisi de l'adresse exacte par le doctorant 
- [Soutenance] Nouvelle notification pour la demande de saisi de l'adresse exacte

6.0.8
-----
- Ajout de logos sur le document des co-encadrements
- Possibilité de supprimer un compte utilisateur.
- Envoi vers Step-Star : le type de partenaire de recherche est désormais configurable.
- Page de recherche d'individus : suppression du bouton de modification qui ouvrait une modale n'enregistrant pas correctement 
  les modifications éventuelles de l'individu et dans laquelle l'iframe de recherche d'idref s'ouvrait dessous.
- Nouveau rôle 'Authentifié' permettant d'accorder des privilèges aux utilisateurs sans rôle particulier (ex : futur module Admission).
- [FIX] Rapports d'activité : le rôle Maison du doctorat ne pouvait pas dévalider un rapport d'activité (en cas de validation du Doctorant). 
- [FIX] Envoi vers Step-Star : faux positif pour le témoin 'contrat doctoral'. 

6.0.7
-----
- [FORMATION] Affichage des missions d'enseignement à coté des inscriptions dans les sessions de formation
- [FORMATION] Possibilité de renseigner la nécessité de mission d'enseignement sur un module et filtrage des formations en conséquent
- [FORMATION] Ajout des champs "objectif" et "programme" pour les actions de formation
- [FORMATION] Ajout des champs "lien" et "mot de passe" dans les séances
- [FORMATION] Un doctorant n'a plus accés à la desinscription si la session est imminente
- [SOUTENANCE] Mise en place des templates associés aux mails 
- [DEPOT] Retouche du texte de mail concernant la page de couverture générée.
- [FIX] Pour se voir attribuer par l'application le rôle Doctorant, il faut désormais avoir une thèse soit en cours soit soutenue.
- Amélioration de la fonction de transfert de thèse en bdd.
- Nouveau ConsoleController (extrait et supprimé d'unicaen/app) pour pouvoir lancer la requête SQL de refresh de la MV de recherche de thèses.

6.0.6
-----
- Passage dans UnicaenRenderer du template des historiques de co-encadrements
- Ajout d'un filtre sur l'année sur l'écran des formations pour masquer/afficher les sessions
- Extraction CSV des thèses : amélioration du temps de génération (création d'une vue en bdd) ; ajout des colonnes 
'Dernier rapport d'activité', 'Dernier rapport CSI', 'Date d'extraction', 'Discipline Code SISE', 'Autorisation de MEL', 
'Années financées' ; modification du séparateur de valeurs multiples ',' en ' ; ' ; correction de la colonne 
'Date de dépôt version corrigée' toujours vide.
- [FIX] Création d'un compte utilisateur local : vérif de l'email déjà utilisé remplacée par vérif de l'email déjà utilisé comme username.
- [FIX] Création d'un compte utilisateur local : redirection vers la fiche du nouveau compte après création.
- [FIX] Soutenance : message d'alerte affiché à tort systématiquement à propos de l'adresse Doctorat manquante.
- [FIX] Chargement de la navigation : plantage d'une assertion à cause d'une variable null (role).
- [FIX] Accès aux fiches individus.
- [FIX] Pages de couverture : réduction de la marge en haut de page.
- [FORMATION] Filtre selon les année pour les sessions
- [SOUTENANCE] Récupération de la date de fin de confidentialité depuis la thèse puis du dossier de soutenance pour le docuement de la présidence

6.0.5
-----
- [FIX] Correction d'un bug empêchant la création d'établissement ; améliorations des validateurs des formulaires de structures.
- [FIX] Correction de bugs en cas d'utilisateur ayant à la fois le rôle Doctorant et un autre rôle.
- [FIX] Correction du plantage survenant dans RapportActiviteAssertion (interrogée par la navigation) lorsque l'utilisateur n'est pas authentifié.
- [FIX] Destinataires de la notification de demande de validation d'une proposition de soutenance : adresse mail 'aspects doctorat'
  de l'établissement d'inscription, plutôt que la liste des individus ayant le rôle BDD obsolète.
- Onglet 'Rôles et membres' d'une ED/UR : renommage de 'Site' en 'Établissement d'inscription' (clarification).
- Complétion des qualités du jury sur la page de couverture avec le dossier de soutenance si manquante dans la donnée source
- Remise en place du menu de gestion des qualités
- Mise à jour de la doc et des scripts d'install de la base de données.

6.0.4
-----
- [FIX] Recherche de rapports d'Activité/CSI/Mi-parcours : les filtres Établissement/ED/UR géraient mal la subsitution de structures.
- Changement de la page de couverture pour tenir compte de la co-accréditation
- Module Soutenance : Possibilité pour les structures de révoquer leur validation (avec modal de confirmation)

6.0.3
-----
- [FIX] Signature de méthode setObject() modifiée en PHP8 dans Doctrine\Laminas\Hydrator\Strategy\AbstractCollectionStrategy.
- [FIX] Page de couverture : utilisation de mb_strtoupper pour mettre les noms en majuscules même si elles sont accentuées.
- [FIX] Rapports CSI : les filtres de recherche Etablissement, ED et UR ne tenaient pas compte des substitutions de structures.
- [FIX] Le menu 'Mes thèses' incluait les thèses historisées.
- [FIX] Rapport activité : bug empêchant la suppression d'un rapport non dématérialisé (ancien fonctionnement).
- [FIX] Dépot de thèse : historisation plutôt que suppression de l'attestation en cas de modif de l'autorisation de diffusion.
- Annuaire des thèses : nouveau filtre de recherche 'Année de financement'.
- Mise en place d'une zone de dépot du pv de soutenance sur la page de présoutenance
- Rapport d'activité : implémentation pour les rapports de fin de contrat du même circuit de validation/avis que les annuels.
- Affichage du nom complet d'un individu : choc de simplification en supprimant 2 options d'affichage dans le formatteur.
- Rapport activité : augmentation à 10 min du timeout CSRF dans le formulaire de création/modification.
- Rapport activité : ajout de Gestionnaire ED à la liste des rôles pouvant valider/dévalider un rapport (privilège requis).
- Possibilité de déclarer des missions d'enseignement
- Les avis de soutenance deviennent des FichierThese afin de pouvoir être afficher sur la page des fichiers divers
- Possibilité de saisir l'établissement du co-encadrant (si différent de l'établissement d'inscription)
- Validation du rapport d'activité par le doctorant : avertissement indiquant que le rapport n'est plus modifiable après validation.
- Nouveaux paramètres applicatifs et service AnneeUnivService concernant les années universitaires (spécif date de bascule N sur N+1, etc.).

6.0.2
-----
- [SQL] Amélioration des libellés des 2 paramètres du module Rapport d'activité.
- Module Soutenance : Masquage des avertissements sur l'état du dossier si la soutenance n'est plus à l'état EN_COURS
- Module Soutenance : Ajout d'une modale de confirmation lors de l'annulation d'une soutenance
- Module Rapport d'activité : utilisation des années universitaires d'inscription pour calculer la liste des années sélectionnables dans le formulaire.
- [FIX] Warning PHP à propos d'une variable indéfinie (typeValidation) sur la page des rapports d'activités d'un doctorant.

6.0.1
-----
- [FIX] Module Formation : mauvais éléments de formulaire pour la date et les horaires, empêchant de créer/modifier une séance.
- [FIX] Module StepStar : erreur dans l'extraction du mail du doctorant.

6.0.0
-----
- Passage à PHP 8.0

5.3.2
-----
- Module Soutenance : 2 nouvelles qualités possibles : 'Autre membre de rang B' et Associate Professor - Équivalent HDR'
- [FIX] Erreur dans le test de pertinence des étapes de dépôt d'une version corrigée (SQL).
- [FIX] Remise de la fonction getNbInscription effacée car sans d'usage (explicite)
- [FIX] Remise du controle de sursis pour les validations acteurs
- [FIX] Inversion de l'ordre de génération des avis de souteances et procés verbaux

5.3.1
-----
- [FIX] Donnée : ajout de garde lorsque le mail fourni par les données sources est ' '
- [FIX] hydration des justificatifs

5.3.0
-----
- Nouvelle version du module Rapports d'activité.

5.2.11
------
- Formation : Ajout d'un mail vers les formateurs lorsque la session est imminente
- Soutenance : Renommange de Parité en Équilibre (et ajustement des couleurs des barres de l'indicateur)
- Soutenance : Retravail du rapport de soutenance (Ajout d'une page blacnhe et d'une troisième page pour les signatures)
- Soutenance : Ajout du dépôt de l'autorisation de soutenance et du rapport de soutenance
- Soutenance : Dépôt de l'attestation de la formation 'Intégrité scientifique'
- Soutenance : Mise en place de l'horodatage 
- Soutenance : Ajout d'une étape intermédiaire avant feu vert pour soutenance
- Soutenance : [Fix] Echappement des caractères ' et encapsulation des réponses
- Dépôt de thèse : un dépôt existant de la version corrigée reste visible même si l'avis de reproduction Apogée revient à Non. 
- Menu Dépôt fichiers divers : remonté et affiché sans condition
- Page Dépôt fichiers divers : téléversement bloqué pour PV soutenance, Pré-rapport soutenance, Rapport soutenance
- Recherche/sélection du PPN IdRef dans les formulaires de modification de structure et d'individu

5.2.10
------
- Les adresses mail d'assistance, Bibliothèque et Doctorat sont désormais renseignées sur la fiche de l'établissement d'inscription.
- [FIX] Correction d'un oubli de la constante AVIS_DEADLINE

5.2.9
-----
- Masquage des membres du CSI et de leurs saisies
- Formation : Amélioration de l'affichage de la liste des inscrits (état de la session, état de la saisie de l'enquête)
- Formation : La génération du pdf d'attestion est maintenant bloquée si l'enquête est non saisie
- Formation : Ajout d'un interval pour la date butoir dans la Session et affichage sur l'index du doctorant
- Formation : Passage sous la forme de template des PDFs de convocation et d'attestation
- Soutenance : Passage des courriers électroniques liés à l'engagement d'impartialité dans UnicaenRenderer
- [FIX] Soutenance : La génération de rapport de soutenance n'est plus limitée au fait q'un membre soit en visio

5.2.8
-----
- Second dépôt (correction de thèse) : améliorations et nouvelle notification (MDD, BU, DT).
- Fiche Thèse : bouton usurper pour le rôle Président du jury
- Ajout du bouton de transmission des documents de soutenance à la direction de thèse
- Les co-encadrants sont maintenant des individus et non plus nécessairement des acteurs
- Notifications par mail : utilisation généralisée de factories de notifications
- [FIX] Module formation : récupération du doctorant via l'utilisateur si aucun n'est fourni
- [FIX] Filtres de recherche réutilisables : nouvelle stratégie de prise en compte ou non des filtres.
- [FIX] Changement des fonctions de récupération des emails dans la partie exportation
- [FIX] Correction De la taille des logos dans le serment du docteur
- [FIX] Correction de la fonction getDoctorantsByUser

5.2.7
-----
- Ajout d'une date de fermeture (indicative) pour les sessions de formation
- Dépôts de nouveaux fichiers divers : charte du doctorat et convention de formation doctorale.
- Extraction d'un module Depot (dépôt du manuscrit de thèse).
- Amélioration de l'index doctorant du module formation
- Ajout de la possibilité d'enregistrer et de valider l'enquête de retour de formation
- Doctorant : peut refuser de recevoir sur son adresse électronique de contact les messages des listes de diffusion
- Suppression du menu 'Mes données' : la modif de l'adresse de contact et du consentement associé est désormais sur la fiche Thèse
- Abandon (avant suppression) de la table obsolète doctorant_compl.
- Ajout d'un bloc dans l'écran de proposition de soutenance pour le téléchargement des pré-rapports et du serment
- Nouveau document 'Serment du docteur'
- Modification du pv de soutenance
- Ajout d'un nouvel mail intermediare à la clôture des inscriptions + deplacement du mail d'echec d'inscription
- [FIX] Module Formation : Ordonnancement des séances sur les index des formations et des sessions
- [FIX] Module Formation : Correction du bug de comptage d'année si pas de thèse
- [FIX] Module Soutenance : Correction paramètre de route erronée + suppression bouton inactif
- [FIX] Module Soutenance : Ouverture de l'acces aux gestionnaires d'UR sur les popositions de soutenance

5.2.6
-----
- Déclaration des membres du comite de suivi individuel
- Amélioration du message de blocage des modifications des structures
- Le texte de l'engagement d'impartialité est maintenant un 'contenu' éditable dans le module unicaen/renderer
- Ajout de l'année de thèse du doctorant (au moment de la session) dans l'export des sessions de formation et sur les émargements
- Heurodatage des changements d'etat des sessions de formation
- [FIX] Plus d'envoi de mail lors du classement des inscriptions si les inscriptions ne sont pas closes
- [FIX] Ajout de l'affichage de la liste des présences à une session si celle-ci est imminente.
- [FIX] Correction du calcul d'erreur de l'année de thèse du doctorant dans les sessions de formation.
- [FIX] Correction du problème de téléchargement des avis en mode non connecté (par exemple pour les membres du jury)
- [FIX] removeProp() ne fonctionnait plus dans le widget JS d'autorisation de mise en ligne : remplacé par prop().  

5.2.5
-----
- Filtrage des propositions de soutenances par Etab, ED, UR, état.
- Renommage de 'Utilisateurs' en 'Comptes utilisateurs'.
- [FIX] Accueil doctorant : corrections autour du lien 'Ma thèse' (+ cosmétique).
- [FIX] Erreur dans la recherche de doctroants par établissement et ED (plantage impactant les listes de diff).
- [FIX] Recherche de rapports d'activités : restrictions par ED/UR selon le rôle endossé et ses privilèges.
- [FIX] Doublons dans la recherche des établissements de rattachement d'une UR.
- [FIX] Module Formation : correction des routes des paginators.
- [FIX] Création/modification d'individu : erreur 'Adresse existante (Utilisateur)'.
- [FIX] Masquage du message abscons 'Cette personne ne pourra pas utiliser l'application...' sur la fiche Thèse.
- [FIX] Page Assistance : ne plante plus et affiche toutes les adresses possibles en cas d'adresse établissement introuvable.
- [FIX] Enregistrement de la date de fin de confidentialité dans la Diffusion.

5.2.4
-----
- [FIX] Correction du ViewHelper SelectHelper (ne suivant pas le lien de substitution)
- Ajout de la modalité de formation mixte
- Ajout d'une garde pour éviter les inscriptions répétées
- Changement des notifications envoyées à propos des listes des formations
- [FIX] priorisation de l'utilisateur pour les notifications au président depuis la page dédiée
- [FIX] correction export csv des inscrits
- [FIX] récupération des mappings manquants pour l'enquete des formations
- Remplacement de \n en <br> pour conserver les sauts de ligne dans les titres sur la page de couverture.
- [FIX] Corrections concernant la subsitution de structures.
- Retrait du readonly sur les libellés et sigles des URs et des EDs
- Calcul de la position sur la liste complémentaire + affichage + possibilité de macro pour les templates

5.2.3
-----
- Substitution de structures : réalisation des jointures dans les requêtes Doctrine et suppression dans les vues SRC_*.
- Module StepStar : envoi des thèses vers Step/Star.
- [FIX] Module Formation : mise en commentaire provisoire des paginators problématiques.

5.2.2
-----
- [FIX] exploitation de la récupération des chemins plutôt que des contenus pour la génération des convocations
- Réorganisation des ménus latéraux de l'accueil du site
- [FIX] Correction de bug dans les assertions du domaine Thèse.
- [FIX] Correction de chemins de scripts de vues erronés déplacés dans le module These.
- Renommage et réordonancement de menus.
- Changement de l'assertion pour l'accès des rapporteurs à la proposition de soutenance
- [FIX] Remise en place du menu de dépôt de rapport de pré-soutenance
- Changement du libellé 'Aucun Site' => 'Multi-site' (module de formation)
- Extension du mail 'échec d'inscription' aux personnes non classées (module de formation)
- Changements de libellés.

5.2.1
-----
- [FIX] Téléchargement de rapport d'activité : message d'erreur en bonne et dûe forme en cas de signature/logo absent.  
- [FIX] Passage à unicaen/db-import 5.2.2 pour corrections de dysfonctionnements dans l'import/synchro.
- [FIX] Plantage de la recherche textuelle d'individus en cas de résultat vide. 
- [FIX] Ajout de garde pour l'index des rapporteurs (cas où le membre est null qui bloquait les administrateurs)
- Changement du lien dans le mail pour la validation des présidents : redirect + selection du rôle
- [FIX] Amélioration de la robustesse de la proposition de soutenance pour les cas de th_se sans ED, sans UR ou sans ETAB
- Ajout d'une vérification de la date de soutenance dans le formulaire pour prévenir des erreurs de saisie (p.e. 12/09/0022)
- [FIX] Années universitaires d'inscription : les années historisées n'étaient pas écartées.
- Couleur noire par défaut pour les headers des cards.

5.2.0 
-----
- [FIX] Plantage du téléchargement d'un rapport d'activité validé dont la thèse n'est rattachée à aucune UR
- [FIX] La même page de validation était appliquée sur tous les rapports d'activité téléchargés au format zip.
- Fichiers liés aux thèses (manuscrit, pv, ect.) et rapports d'activité : en cas de fichier introuvable dans le storage, génération d'un fichier PDF temporaire de substitution.
- Abandon de la fonction tmpname() mal utilisée.
- Passage à unicaen/db-import 5.2.1
- Ménage dans le module Import du fait de l'utilisation de unicaen/db-import

5.1.1
-----
- [FIX] Corrections suite à l'intégration du nouveau module Fichier.
- [FIX] Suppression à tort des 'attestations' du 1er dépôt à la place de celles du 2nd dépôt.
- [FIX] Suppression physique de l'ancien fichier lors du changement de logo d'une structure.
- [FIX] Correction et amélioration du calcul du nom de fichier du logo (existant ou nouveau) d'une structure.
- Template de pagination : abandon du module/Application/view/paginator.phtml et généralisation du module/Application/view/application/paginator.phtml
- Suppression des injections inutiles de SourceCodeStringHelperAwareTrait.

5.1.0
-----
- Nouveau module 'technique' Fichier proposant 2 modes de stockage des fichiers téléversés : Filesystem ou S3 
  (cf. [releases notes](./doc/release-notes/v5.1.0.md)).
- [FIX] Correction du chemin de stockage des rapports CSI et de mi-parcours

5.0.1
-----
- [FIX] Abandon des 'data:image/*;base64,' dans les templates mPDF.
- [FIX] mPdf ne supporte plus les ' ' et ':' dans les noms de fichiers images (logos de structure).
- [FIX] Ajustement de la demande du justificatif de demande d'HDR pour les membres étranger de rang B ayant un HDR (ou équivalent)

5.0.0
-----
- Nouveau module Formation doctorale.
- Changement de séparateur de mots-clés libres et RAMEAU (; remplacé par *).
- Possibilité de saisir le Numéro National des Thèses (NNT) sur le formulaire du rendez-vous BU.

4.2.2
-----
- [FIX] Page de validation non ajoutée aux rapports d'activités en cas de téléchargement sous forme d'archive zip.

4.2.1
-----
- Affichage d'une alerte à propos des délais durant la fermeture estivale (lib unicaen/alerte)
- Retrait de `IntervenantEmailFormatter` et de `IntervenantTrouveFormatter` (classes inutilisées)
- Changement du terme Établissement par Site dans l'affichage des rôles des EDs et des URs pour éviter la confusion
- [FIX] retrait d'un début faisant planter la génération de convaocation de soutenance 
- [FIX] Correction du plantage lors de la création manuelle d'un utilisateur.
- [FIX] Plantage de RapportActiviteAssertion lorsque la route demandée n'existe pas.
- [FIX] Fourniture à Sympa des adresses institutionnelles en plus des adresses perso.
- [FIX] Problème de config de l'aide de vue IndividuUsurpation.

4.2.0
-----
- Nouveau module Individu : recherche, création (à partir de rien ou d'un utilisateur), modification.
- Possibilité de renseigner l'identifiant HAL d'une structure (nécessaire pour l'envoi vers STEP/STAR).
- Import du code Apogée de la discipline SISE de la thèse.
- Extraction d'un module Structure.
- [FIX] Plantage de la page détails d'une structure si fichier inexistant sur le serveur (typique en preprod).
- [FIX] Page des détails d'une structure : retour au bon onglet en cas d'action (ex : dépôt d'un document).
- [FIX] Chevauchement des résumés sur la page de signalement d'une thèse.
- [FIX] Correction du bug de la signature non trouvée pour la génération de la convocation de soutenance.
- [FIX] Correction d'une virgule isolée sur la page de couverture.
- [FIX] Les financements historisés n'étaient pas écartés.

4.1.3
-----
- [FIX] Balises <img> des logos/signatures dont la src est le contenu binaire : le format était systématiquement 
  'image/png' ce qui posait problème si l'image était d'un autre format.

4.1.2
-----
- [FIX] Plantage du formulaire de création d'un avis sur un rapport d'activité.

4.1.1
-----
- Rapports d'activité de fin de contrat : aucun avis Dir/UR nécessaire dans le rapport lui-meme donc on écarte les 
  compléments qui génèreraient une case à cocher permettant de signaler une absence d'avis.
- Module Rapports d'activité : corrections cosmétiques.
- [FIX] Passage à unicaen/db-import 5.1.2 pour corriger le plantage lors de la création d'une structure.

4.1.0
-----
- Refonte du module 'Rapports d'activité'.
- Nouveau document téléversable pour une structure : Signature figurant sur la page de validation d'un rapport d'activité.

4.0.6
-----
- Retour du logo ESUp-SyGAL dans la barre de menu principal. 
  Assombrissement du bleu dans la barre de menu principal et dans le pied de page.
- [FIX] Plantage lors de la création d'une structure (ED, UR, établissement).

4.0.5
-----
- [FIX] Correction du plantage de la page de saisie du mail perso doctorant.

4.0.4
-----
- Correction de typos dans mail de feu vert de la soutenance
- Ajout de redirection de mail lorsque certains mails n'ont pas de destinataire 'ATTENTION MAIL NON DÉLIVRÉ'.
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
- Amélioration de la page 'Contact Assistance' en cas d'établissement indéterminé et/ou d'adresse d'assistance indéterminé ou invalide.
- [FIX] Plantage de la page 'Contact Assistance' en cas de connexion avec un compte local.
- [FIX] Activation de la mise en cache de la config lorsque le mode development est désactivé.
- [FIX] Lancement de la synchro des thèses pour prendre en compte la création/modification/suppression de substitution de structures.
- Ajout des unités de recherche fermées dans le filtre des thèses
- [FIX] correction du bug lié au typage de retour trop strict de l'entité Structure
- Mise en place de la déclaration de non plagiat dans la proposition de soutenance
- [FIX] Plantage lors de la création/modification/suppression d'une substitution de structure ('Synchro introuvable avec ce nom : these')

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
- Ajout de la mention 'La réservation du lieu de soutenance n'est pas faite automatiquement et reste à votre charge'
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
- Ajout d'une valeur d'état aux soutenances 'Validée par l'établissement' post validation d'une soutenance par la présidence de l'établissement
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
- Scission du rôle 'École doctorale' en 2 : 'Responsable École doctorale' et 'Gestionnaire École doctorale'.
- Scission du rôle 'Unité de recherche' en 2 : 'Responsable Unité de recherche' et 'Gestionnaire Unité de recherche'.
- Envoi automatique par mail des jetons d'authentification créés + possibilité de les renvoyer.
- Utilisation des dates et lieux des dossiers de soutenances plutôt que celles saisies dans les SIs pour la génération des documents du module soutenance.
- Précision de la date de rendu des rapports dès le premier mail des rapporteurs
- Recherche de rapports d'activité : nouveau filtre 'Annuel ou fin de thèse'.  
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
- [FIX] Warning lors de la génération de la PDC à cause d'un tableau non initialisé'

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
- Import du témoin 'corrections effectuées' de chaque thèse.
- Pages de dépôt de la version corrigée : visibles dès lors que le témoin 'corrections effectuées' est à Oui.
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
- Nouveau message 'Dépôt terminé' au doctorant sur la page Rendez-vous BU.

1.4.8 (01/09/2020)
------------------

- Correction du nom de colonne ID_PERMANENT erroné dans le mapping de l'entité Fichier.
- Correction du path manquant pour l'upload.

1.4.7 (04/06/2020)
------------------

- Lors du dépôt d'une version corrigée, l'autorisation de mise en ligne est reprise texto (dupliquée) du 1er dépôt,
sauf si l'utilisateur possède le privilège 'Saisie du formulaire d'autorisation de diffusion de la version corrigée', 
auquel cas elle est redemandée à l'utilisateur.
Idem pour les attestations et le privilège 'Modification des attestations concernant la version corrigée'.
- Masquage du complément de financement dans la fiche d'identité de la thèse
- Optimisation de l'export CSV des thèses
- Pages de téléversement et de recherche des rapports annuels.
- Correction d'un bug dans la recherche de thèses par nom du doctorant.
- Correction d'un bug dans le package Oracle APP_IMPORT qui ne filtrait pas les thèses selon l'établissement spécifié.
- Possibilité d'attribuer un 'identifiant permanent' à un fichier (ex: 'RAPPORT_ANNUEL_MODELE') facilitant l'intégration
  de lien de téléchargement de ce fichier dans une page.
- Listes de diffusion dynamique Sympa alimentées par SyGAL : pages de consultation des listes de diffusion déclarées
  dans la config ; une URL pour fournir les abonnés, une autre pour fournir les propriétaires.

1.4.6 (29/05/2020)
------------------

- Ajout du drapeau 'établissement d'inscription' et ajout des visualisations et interfaces pour gérer ce nouveau drapeau.
- Restriction du filtre des établissements sur la partie annuaire aux établissements d'inscription.
- Ajout dans structures des champs adresse, tel, fax, site web, email qui sont utilisables pour l'édition de document.
- Utilisation des nouveaux champs dans la génération de la convention de MEL (requiert unicaen/app v1.3.19).
- Amélioration de la recherche textuelle de thèses : ajout d'une liste déroulante permettant de sélectionner 
  précisément sur quels critères porte la recherche : 'Titre de la thèse', 'Numéro étudiant de l'auteur', 
  'Nom de l'auteur', 'Prénom de l'auteur', 'Nom du directeur ou co-directeur de thèse', 
  'Code national de l'école doctorale concernée (ex: 181)', 'Unité de recherche concernée (ex: umr6211)'.
- Correction d'un dysfonctionnement de la recherche textuelle sur les critères 'numéro étudiant', 'unité de recherche'
  et 'école doctorale'.


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
- Filtrage de la liste des thèses : correction de l'affichage du filtre 'Unité de recherche'.
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
- Ajout du flag 'fermé' pour les structures et utilisations dans la recherche de thèses.
- Ajout d'un champ 'Id HAL' dans le formulaire d'autorisation de diffusion.
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
    - Utilisation de la mention générique 'Le chef d'établissement' plutôt que d'exploiter les libellés 
      importés des établissements.
- Nouvelle ligne de commande pour importer une thèse à la demande.

### Corrections

- Import : 
    - Vidage et remplissage de chaque table temporaire n'étaient pas faits dans une même transaction !
    - Améliorations pour utiliser moins de mémoire ; meilleurs logs. 
    - Correction des exceptions de type `ORA-00001: unique constraint (SYGAL.TMP_ACTEUR_UNIQ) violated` 
      par un changement de stratégie côté web service (interrogation de tables plutôt que des vues). 
- Le bouton d'import d'une thèse à la demande avait disparu (menu 'Page de couverture') à cause d'une config erronée.

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
- Utilisation de convert (imagemagick) pour convertir les logos 'automatiquement' au format png 

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

- Nouvelle page consacrée au dépôt de fichiers divers liés à une thèse (précédemment dans la page 'Thèse').
- Possibilité de déposer des fichiers dits 'communs' utiles aux gestionnaires, ex: modèle d'avenant à la convention 
  de mise en ligne.

### Améliorations

- Améliorations de la page 'Privilèges', notamment le filtrage par rôle. 
- Déplacement des privilèges de la catégorie `fichier-divers` vers la catégorie `these`
  car ils concernent des fichiers liés à une thèse (ex: PV de soutenance).
  La catégorie `fichier-divers` désigne désormais les privilèges concernant des fichiers sans lien aux
  thèses (ex: fichiers déposés pour les pages d'informations).
- Refonte technique de la gestion des fichiers liés aux pages d'informations, prélable au travail sur les droits de 
  dépôt de fichiers 'divers' et 'communs'.


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
