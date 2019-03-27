# Répertoire `entrypoint.d`

Lors du build, tous les fichiers situés dans `./entrypoint.d` sont copiés dans le dossier `/entrypoint.d` de l'image.

Au démarrage du container, seuls les scripts exécutables parmi ces fichiers seront lancés (cf. `./entrypoint.sh`) par 
la commande [`run-pats`](https://manpages.debian.org/stretch/debianutils/run-parts.8.fr.html).

**Attention** :
- Le nom d'un script ne doit être constitué que de lettres minuscules ou majuscules,
  de chiffres, de tirets bas (underscore) ou de tirets. 
  *Extension interdite, donc.*
- Les fichiers sont exécutés dans l'ordre lexicographique de leur nom.
 