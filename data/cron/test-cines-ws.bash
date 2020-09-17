#!/usr/bin/env bash

export https_proxy=proxy.unicaen.fr:3128
export http_proxy=proxy.unicaen.fr:3128
export no_proxy="localhost,127.0.0.0/8,.local,.unicaen.fr"

email="bertrand.gauthier@unicaen.fr"

# NB: le pré-requis est d'être positionné dans le répertoire data/cron.
#
cd ../..

cmd="vendor/bin/phpunit -c phpunit.xml module/Application/tests/ApplicationFunctionalTest/Command/CheckWSValidationFichierCinesCommandTest.php"

echo "Lancement du test CheckWSValidationFichierCinesCommandTest..."
echo "$cmd"
result=$(bash -c "$cmd")

#vendor/bin/phpunit -c phpunit.xml module/Application/test/ApplicationTest/Command/CheckWSValidationFichierCinesCommandTest.php | mail -s "Test WS cines" "$email"
result="$(uname -a)

Résultat de l'exécution du test CheckWSValidationFichierCinesCommandTest :
--------------------------------------------------------------------------

$result"
echo "$result" | mail -s "Test WS cines" "$email"

echo "Un mail a été envoyé à $email".
echo
