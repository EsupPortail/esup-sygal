#!/usr/bin/env bash

echo "Confirmez-vous la création d'une nouvelle branche 'test' à partir de la branche courante ? (Y/n) "
read doit

if [[ ${doit} != "Y" ]]; then
    echo "Abandon."
    exit 1
fi

git branch -d test && git push origin --delete test && \
git checkout -b test && git push --set-upstream origin test

echo ""
echo "/ATTENTION!\ Notez bien que vous êtes à présent sur la branche 'test'."
