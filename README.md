# Application SyGAL

SYstème de Gestion et d'Accompagement doctoraL


## Documentation

Reportez-vous [ici](doc/README.md). 


## Lancement de l'application *pour le dévelopement*

```bash
docker-compose up -d --build
```
    
Enlever le `-d` (detached) pour voir les logs en direct.

Regardez le fichier [docker-compose.yml](./docker-compose.yml) pour les détails.

## Lancement d'une commande

```bash
docker-compose exec sygal composer install
```

### Debug d'une ligne de commande

```bash
docker-compose exec sygal \
bash -c "\
export XDEBUG_CONFIG='remote_host=172.17.0.1' && \
export PHP_IDE_CONFIG='serverName=docker' && \
php ./public/index.php process-observed-import-results"
```
        
- `172.17.0.1` est l'IP obtenue avec la commande `docker network inspect bridge | grep Gateway`
- `docker` est le nom du serveur correspondant au container Docker, à configurer dans PHPStorm.
