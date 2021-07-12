
```bash

# installation
docker-compose exec sygal composer global require laminas/laminas-migration

# migration de l'appli
docker-compose exec sygal /root/.composer/vendor/bin/laminas-migration migrate \
--exclude data \
--exclude etabs \
--exclude migration \
--exclude public \
--exclude tmp \
--exclude upload \
--exclude vendor

# migration de unicaen/*
docker-compose exec sygal bash -c "cd vendor/unicaen/app           && /root/.composer/vendor/bin/laminas-migration migrate"
docker-compose exec sygal bash -c "cd vendor/unicaen/auth          && /root/.composer/vendor/bin/laminas-migration migrate"
docker-compose exec sygal bash -c "cd vendor/unicaen/auth-token    && /root/.composer/vendor/bin/laminas-migration migrate"
docker-compose exec sygal bash -c "cd vendor/unicaen/bjy-authorize && /root/.composer/vendor/bin/laminas-migration migrate"
docker-compose exec sygal bash -c "cd vendor/unicaen/code          && /root/.composer/vendor/bin/laminas-migration migrate"
docker-compose exec sygal bash -c "cd vendor/unicaen/db-import     && /root/.composer/vendor/bin/laminas-migration migrate"
docker-compose exec sygal bash -c "cd vendor/unicaen/faq           && /root/.composer/vendor/bin/laminas-migration migrate"
docker-compose exec sygal bash -c "cd vendor/unicaen/ldap          && /root/.composer/vendor/bin/laminas-migration migrate"
docker-compose exec sygal bash -c "cd vendor/unicaen/test          && /root/.composer/vendor/bin/laminas-migration migrate"
```

git checkout master && git pull && git checkout -b laminas_migration

git add . && git commit -m "Migration vers laminas"

cd vendor/unicaen/app           && git remote set-url origin git@git:lib/unicaen/app           && git push origin -u laminas_migration ; cd ../../..
cd vendor/unicaen/auth          && git remote set-url origin git@git:lib/unicaen/auth          && git push origin -u laminas_migration ; cd ../../..
cd vendor/unicaen/auth-token    && git remote set-url origin git@git:lib/unicaen/auth-token    && git push origin -u laminas_migration ; cd ../../..
cd vendor/unicaen/bjy-authorize && git remote set-url origin git@git:lib/unicaen/bjy-authorize && git push origin -u laminas_migration ; cd ../../..
cd vendor/unicaen/code          && git remote set-url origin git@git:lib/unicaen/code          && git push origin -u laminas_migration ; cd ../../..
cd vendor/unicaen/db-import     && git remote set-url origin git@git:lib/unicaen/db-import     && git push origin -u laminas_migration ; cd ../../..
cd vendor/unicaen/faq           && git remote set-url origin git@git:lib/unicaen/faq           && git push origin -u laminas_migration ; cd ../../..
cd vendor/unicaen/ldap          && git remote set-url origin git@git:lib/unicaen/ldap          && git push origin -u laminas_migration ; cd ../../..
cd vendor/unicaen/test          && git remote set-url origin git@git:lib/unicaen/test          && git push origin -u laminas_migration ; cd ../../..

cd vendor/unicaen/app           && git add . && git commit -m "Dépendance avec unicaen/* laminas_migration" && git push  ; cd ../../..
cd vendor/unicaen/auth          && git add . && git commit -m "Dépendance avec unicaen/* laminas_migration" && git push  ; cd ../../..
cd vendor/unicaen/auth-token    && git add . && git commit -m "Dépendance avec unicaen/* laminas_migration" && git push  ; cd ../../..
cd vendor/unicaen/bjy-authorize && git add . && git commit -m "Dépendance avec unicaen/* laminas_migration" && git push  ; cd ../../..
cd vendor/unicaen/code          && git add . && git commit -m "Dépendance avec unicaen/* laminas_migration" && git push  ; cd ../../..
cd vendor/unicaen/db-import     && git add . && git commit -m "Dépendance avec unicaen/* laminas_migration" && git push  ; cd ../../..
cd vendor/unicaen/faq           && git add . && git commit -m "Dépendance avec unicaen/* laminas_migration" && git push  ; cd ../../..
cd vendor/unicaen/ldap          && git add . && git commit -m "Dépendance avec unicaen/* laminas_migration" && git push  ; cd ../../..
cd vendor/unicaen/test          && git add . && git commit -m "Dépendance avec unicaen/* laminas_migration" && git push  ; cd ../../..

unicaen/app
unicaen/auth
unicaen/auth-token
unicaen/bjy-authorize
unicaen/code
unicaen/db-import
unicaen/exemple
unicaen/faq
unicaen/ldap
unicaen/leocarte
unicaen/oracle
unicaen/skeleton-application
unicaen/test

DIR=/tmp
libs=( "app" "auth" "auth-token" "bjy-authorize" "code" "db-import" "exemple" "faq" "ldap" "leocarte" "oracle" "skeleton-application" "test"  )
cd ${DIR}
for lib in ${libs[*]}
do
   git clone git@git.unicaen.fr:lib/unicaen/$lib.git ./unicaen/$lib
done

