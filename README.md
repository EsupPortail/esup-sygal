# Application SyGAL

SYstème de Gestion et d'Accompagement doctoraL

## Docker

En dev, on monte les sources (répertoire courant) dans un volume.

### Avec docker-compose

    docker-compose up -d --build
    
Enlever le `-d` (detached) pour voir les logs en direct.

### Sans docker-compose

    docker build \
    --add-host="proxy.unicaen.fr:10.14.128.99" \
    --add-host="svn.unicaen.fr:10.14.129.44" \
    --build-arg http_proxy="http://proxy.unicaen.fr:3128" \
    --build-arg https_proxy="http://proxy.unicaen.fr:3128" \
    --build-arg no_proxy=".unicaen.fr,localhost" \
    -t unicaen/sygal-php7-dev-image \
    .

NB: Il est possible de spécifier le proxy par son adresse IP mais 
*il ne faut pas oublier* de mettre `http://` devant 
(càd: `--build-arg http_proxy="http://10.14.128.99:3128"`).

Run :

    sudo docker run -d \
    -p 8080:80 \
    -v "$PWD":/webapp \
    --dns=10.14.128.125 \
    --dns-search=unicaen.fr \
    --name sygal-php7-dev-container \
    unicaen/sygal-php7-dev-image
