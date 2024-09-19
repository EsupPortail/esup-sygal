SyGAL <=> Pégase : tests INSA
==============================

À tester :
- [A](#a)  : Tests de l'export manuel admissions/admis.csv dans SyGAL Admission, puis de son import manuel dans Pégase.
- [B](#b)  : Tests de l'envoi du flux Inscription Administrative par Pégase, puis de sa réception et traitement dans SyGAL.


A
-------

**Tests de l'export manuel admissions/admis.csv dans SyGAL-Admission, puis de son import manuel dans Pégase.**

- [A10](#a10) SyGAL Admission ==(admissions/admis.csv)==> [A11](#a11) Pégase
- [A20](#a20) SyGAL Admission Modif du dossier ==(admissions/admis.csv)==> [A21](#a21) Pégase (inscription non démarrée)
- [A30](#a30) SyGAL Admission Modif du dossier ==(admissions/admis.csv)==> [A31](#a31) Pégase (inscription démarrée)


### A10
- SyGAL : Saisie d'une admission complète
- SyGAL : paramétrer le code Période et le code Voeux (valable pour toute l'année mais change chaque année, demander à Elodie/Marina)
- SyGAL : Export manuel admissions/admis.csv (ssi dossier validé)

Remarques :
- Code période : ex 'P2024'.
- Code voeux = f(formation)

Questions :
- Importer un mapping Discipline - Code objet formation ? (demander un exemple géréré par le "Groupe INSA")
Ou maintenir dans sygal le mapping (puisque on a déjà les disciplines) ? => Pb : la disicpline est une saisie manuelle 
dans Sygal admission !  
- Ou importer depuis Pégase (API) ? Nécessite des connaissances en offre de formation, non ?

### A11
- Pégase : Import manuel de admissions/admis.csv
- Pégase : Vérifs

Remarques :
- Import CSV dans Pégase :
  - Si inscription non démarrée : pégase écrase
  - Si inscription entamé par etudiant ou gestionnaire : pegase refuse la ligne en erreur. Le reste du csv passe (à confirmer).
  - Si erreur : abandon inscr, soit corriger le csv.
- INE :
  - INE null possible dans csv car INE généré auprés de INES.
  - INE incorrect refusé par INES.
  - Idée : Abandonner l'INE dans SYGAL ? Parce que si saisi, nécessité de le vérifier avec INES. 
    Non, on le garde, c'est dommage de l'abandonner.

### A20
- SyGAL : Modif du dossier d'admission malgré admissions/admis.csv déjà importé par Pégase : on modifie quelle info ?
- SyGAL : Export manuel admissions/admis.csv (ssi dossier validé)

### A21
- Pégase : Import manuel de admissions/admis.csv
- Pégase : Vérifs
- Comment signaler un pb aux gestionnaires SyGAL ?

### A30
- Idem A20

### A31
- Pégase : Démarrer l'inscription
- Pégase : Import manuel de admissions/admis.csv
- Pégase : Vérifs
- Comment signaler un pb aux gestionnaires SyGAL ?


B
-------

**Tests de l'envoi du flux Inscription Administrative par Pégase, puis de sa réception et traitement dans SyGAL.**

- [B10](#b10) Pégase Inscription ==(flux Inscription Administrative)==> [B11](#b11) SyGAL Réception du flux
- ?

Process inscription :
- Saisie étudiant/gestion 
- Paiement
- Verif du dossier par un gestionnaire
- Validation de l'inscription 
- Envoi du flux IA.

Inscription :
- Validation
  - flux statut_inscr = VALIDEE
- Annulation
  - si avant validation (ex: initiative de l'etudiant) : RAS, pas de flux
  - annulation : un gestionnaire déclare l'annulation => inscription annulée : flux statut_inscr ANNULEE

A priori, pas de revalidation d'un insc annulée ! Tester quand même ? (NB : annuler une inscr est un sacré privilège dans Pégase)



### B10
- Pégase : saisie de l'inscription. Quel lien avec admissions/admis.csv ? Possible de ne pas respecter les infos de admissions/admis.csv ?
- Pégase : démarrage de l'inscription
- Pégase : test réimport de admissions/admis.csv ?
- ?

