Troubleshooting
===============

Utilisateur introuvable dans la page "Admin > Utilisateurs"
-----------

Ex: Marie LEGAY-MELEUX et Anne-Marie ROLLAND-LE CHEVREL
https://redmine.unicaen.fr/Etablissement/issues/26539

### Cause

L'individu référencé par l'utilisateur avait :
- pour source Apogée (id=3) car importé d'Apogée
- pour type 'acteur'
- était historisé car avait disparu ensuite des acteurs dans Apogée.
=> cela expliquait qu'il ne remontait pas dans la requête en BDD

### Solution

- Déhistoriser l'individu
- Mettre type à NULL
- Changer sa source à SYGAL (id=1)
- Renseigner le supannId si besoin
