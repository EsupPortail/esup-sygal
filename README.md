# Application SoDoct

SOutenance, Doctorat et Organisation du Circuit des Thèses

## Utilisation de Docker

### Dev

En dev, on monte les sources (répertoire courant) dans un volume.

#### Avec docker-compose

    docker-compose up -d --build
    
Enlever le `-d` (detached) pour voir les logs en direct.

#### Sans docker-compose

Build à partir du fichier *Dockerfile.php7.dev* :

    docker build \
    --add-host="proxy.unicaen.fr:10.14.128.99" \
    --add-host="svn.unicaen.fr:10.14.129.44" \
    --build-arg http_proxy="http://proxy.unicaen.fr:3128" \
    --build-arg https_proxy="http://proxy.unicaen.fr:3128" \
    --build-arg no_proxy=".unicaen.fr,localhost" \
    -t unicaen/sygal-php7-dev-image \
    -f Dockerfile.php7.dev \
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


### Test

En test, on copie les sources (répertoire courant) dans l'image. 
NB: fichier *.gitignore*.

Fichier *apache.conf* requis dans le répertoire courant :

    <VirtualHost *:80>
         ServerName localhost
         DocumentRoot /webapp/public
         RewriteEngine On
         <Directory /webapp/public>
             DirectoryIndex index.php
             AllowOverride All
             Require all granted
         </Directory>
         ErrorLog ${APACHE_LOG_DIR}/error.log
         CustomLog ${APACHE_LOG_DIR}/access.log combined
         #LogLevel debug
     </VirtualHost>

Fichier *auth.json* requis dans le répertoire courant spécifiant le compte SVN autorisé 
en lecture pour l'installation des packages unicaen/* avec composer :

    {
      "http-basic": {
        "svn.unicaen.fr": {
          "username": "satis",
          "password": "xxxxxxxxx"
        }
      }
    }
    
Build à partir du fichier *Dockerfile.php7.test* :

    docker build \
    --add-host="proxy.unicaen.fr:10.14.128.99" \
    --add-host="svn.unicaen.fr:10.14.129.44" \
    --build-arg http_proxy="http://proxy.unicaen.fr:3128" \
    --build-arg https_proxy="http://proxy.unicaen.fr:3128" \
    --build-arg no_proxy=".unicaen.fr,localhost" \
    -t unicaen/sodoct-php7-test \
    -f Dockerfile.php7.test \
    .

Run :

    docker run -d \
    -p 8080:80 \
    --dns=10.14.128.125 \
    --dns-search=unicaen.fr \
    --name sodoct-docker \
    unicaen/sodoct-docker

NB: pas possible de spécifier le serveur DNS par "proxy.unicaen.fr".

