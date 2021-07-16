
cd app              && git remote set-url origin git@git:/lib/unicaen/app           ; cd ..
cd auth             && git remote set-url origin git@git:/lib/unicaen/auth          ; cd ..
cd auth-token       && git remote set-url origin git@git:/lib/unicaen/auth-token    ; cd ..
cd code             && git remote set-url origin git@git:/lib/unicaen/code          ; cd ..
cd db-import        && git remote set-url origin git@git:/lib/unicaen/db-import     ; cd ..
cd faq              && git remote set-url origin git@git:/lib/unicaen/faq           ; cd ..

cd app              && git fetch && git checkout bootstrap4_migration ; cd ..
cd auth             && git fetch && git checkout bootstrap4_migration ; cd ..
cd auth-token       && git fetch && git checkout bootstrap4_migration ; cd ..
cd code             && git fetch && git checkout bootstrap4_migration ; cd ..
cd faq              && git fetch && git checkout bootstrap4_migration ; cd ..

cd app              && git add . && git commit -m "WIP bootstrap 3 => 4" && git push --set-upstream origin bootstrap4_migration ; cd ..
cd auth             && git add . && git commit -m "WIP bootstrap 3 => 4" && git push --set-upstream origin bootstrap4_migration ; cd ..
cd auth-token       && git add . && git commit -m "WIP bootstrap 3 => 4" && git push --set-upstream origin bootstrap4_migration ; cd ..
cd code             && git add . && git commit -m "WIP bootstrap 3 => 4" && git push --set-upstream origin bootstrap4_migration ; cd ..
cd faq              && git add . && git commit -m "WIP bootstrap 3 => 4" && git push --set-upstream origin bootstrap4_migration ; cd ..

cd app              && git add . && git branch ; cd ..
cd auth             && git add . && git branch ; cd ..
cd auth-token       && git add . && git branch ; cd ..
cd code             && git add . && git branch ; cd ..
cd faq              && git add . && git branch ; cd ..

cd app              && git add . && git log -1 --oneline ; cd ..
cd auth             && git add . && git log -1 --oneline ; cd ..
cd auth-token       && git add . && git log -1 --oneline ; cd ..
cd code             && git add . && git log -1 --oneline ; cd ..
cd faq              && git add . && git log -1 --oneline ; cd ..

cd app        && echo && echo "========= $PWD =========" && git add . && git commit -m "WIP bootstrap 3 => 4" && git push ; cd .. ; \
cd auth       && echo && echo "========= $PWD =========" && git add . && git commit -m "WIP bootstrap 3 => 4" && git push ; cd .. ; \
cd auth-token && echo && echo "========= $PWD =========" && git add . && git commit -m "WIP bootstrap 3 => 4" && git push ; cd .. ; \
cd code       && echo && echo "========= $PWD =========" && git add . && git commit -m "WIP bootstrap 3 => 4" && git push ; cd .. ; \
cd faq        && echo && echo "========= $PWD =========" && git add . && git commit -m "WIP bootstrap 3 => 4" && git push ; cd ..

