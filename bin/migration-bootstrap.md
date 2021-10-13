```bash

cd app              && git remote set-url origin git@git.unicaen.fr:/lib/unicaen/app           ; cd ..
cd auth             && git remote set-url origin git@git.unicaen.fr:/lib/unicaen/auth          ; cd ..
cd auth-token       && git remote set-url origin git@git.unicaen.fr:/lib/unicaen/auth-token    ; cd ..
cd faq              && git remote set-url origin git@git.unicaen.fr:/lib/unicaen/faq           ; cd ..

```


B3 => B4
========

```bash

git fetch && git checkout bootstrap4_migration

cd vendor/unicaen

sudo chown -R gauthierb .
 
cd app              && git fetch && git checkout bootstrap4_migration ; cd ..
cd auth             && git fetch && git checkout bootstrap4_migration ; cd ..
cd auth-token       && git fetch && git checkout bootstrap4_migration ; cd ..
cd faq              && git fetch && git checkout bootstrap4_migration ; cd ..

cd app              && git add . && git commit -m "WIP bootstrap 3 => 4" && git push --set-upstream origin bootstrap4_migration ; cd ..
cd auth             && git add . && git commit -m "WIP bootstrap 3 => 4" && git push --set-upstream origin bootstrap4_migration ; cd ..
cd auth-token       && git add . && git commit -m "WIP bootstrap 3 => 4" && git push --set-upstream origin bootstrap4_migration ; cd ..
cd faq              && git add . && git commit -m "WIP bootstrap 3 => 4" && git push --set-upstream origin bootstrap4_migration ; cd ..

cd app              && git add . && git branch ; cd ..
cd auth             && git add . && git branch ; cd ..
cd auth-token       && git add . && git branch ; cd ..
cd faq              && git add . && git branch ; cd ..

cd app              && git add . && git log -1 --oneline ; cd ..
cd auth             && git add . && git log -1 --oneline ; cd ..
cd auth-token       && git add . && git log -1 --oneline ; cd ..
cd faq              && git add . && git log -1 --oneline ; cd ..

cd app        && echo && echo "========= $PWD =========" && git add . && git commit -m "WIP bootstrap 3 => 4" && git push ; cd .. ; \
cd auth       && echo && echo "========= $PWD =========" && git add . && git commit -m "WIP bootstrap 3 => 4" && git push ; cd .. ; \
cd auth-token && echo && echo "========= $PWD =========" && git add . && git commit -m "WIP bootstrap 3 => 4" && git push ; cd .. ; \
cd faq        && echo && echo "========= $PWD =========" && git add . && git commit -m "WIP bootstrap 3 => 4" && git push ; cd ..

```


B4 => B5
========

```bash

git fetch && git checkout bootstrap5_migration

cd vendor/unicaen

sudo chown -R gauthierb .
 
cd app              && git fetch && git checkout bootstrap5_migration ; cd ..
cd auth             && git fetch && git checkout bootstrap5_migration ; cd ..
cd auth-token       && git fetch && git checkout bootstrap5_migration ; cd ..
cd faq              && git fetch && git checkout bootstrap5_migration ; cd ..

cd app              && git add . && git checkout -b bootstrap5_migration && git push --set-upstream origin bootstrap5_migration ; cd ..
cd auth             && git add . && git checkout -b bootstrap5_migration && git push --set-upstream origin bootstrap5_migration ; cd ..
cd auth-token       && git add . && git checkout -b bootstrap5_migration && git push --set-upstream origin bootstrap5_migration ; cd ..
cd faq              && git add . && git checkout -b bootstrap5_migration && git push --set-upstream origin bootstrap5_migration ; cd ..

cd app        && echo && echo "========= $PWD =========" && git add . && git commit -m "WIP bootstrap 4 => 5" ; git push ; cd .. ; \
cd auth       && echo && echo "========= $PWD =========" && git add . && git commit -m "WIP bootstrap 4 => 5" ; git push ; cd .. ; \
cd auth-token && echo && echo "========= $PWD =========" && git add . && git commit -m "WIP bootstrap 4 => 5" ; git push ; cd .. ; \
cd faq        && echo && echo "========= $PWD =========" && git add . && git commit -m "WIP bootstrap 4 => 5" ; git push ; cd ..

```


Retour au master
===============
```bash

cd app        && git checkout master && git pull ; cd ..
cd auth       && git checkout master && git pull ; cd ..
cd auth-token && git checkout master && git pull ; cd ..
cd faq        && git checkout master && git pull ; cd ..

```
