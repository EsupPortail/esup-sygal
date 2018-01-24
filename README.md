# Application SoDoct

SOutenance, Doctorat et Organisation du Circuit des Thèses

## Utilisation de Docker

### Dev

En dev, on monte les sources (répertoire courant) dans un volume.

PHP5, build à partir du fichier *Dockerfile.dev* :

    docker build \
    --add-host="proxy.unicaen.fr:10.14.128.99" \
    --add-host="svn.unicaen.fr:10.14.129.44" \
    --build-arg http_proxy="http://proxy.unicaen.fr:3128" \
    --build-arg https_proxy="http://proxy.unicaen.fr:3128" \
    --build-arg no_proxy=".unicaen.fr,localhost" \
    -t unicaen/sodoct-dev \
    -f Dockerfile.dev \
    .

PHP7, build à partir du fichier *Dockerfile.php7.dev* :

    docker build \
    --add-host="proxy.unicaen.fr:10.14.128.99" \
    --add-host="svn.unicaen.fr:10.14.129.44" \
    --build-arg http_proxy="http://proxy.unicaen.fr:3128" \
    --build-arg https_proxy="http://proxy.unicaen.fr:3128" \
    --build-arg no_proxy=".unicaen.fr,localhost" \
    -t unicaen/sodoct-php7-dev \
    -f Dockerfile.php7.dev \
    .

Run :

    docker run -d \
    -p 8080:80 \
    -v "$PWD":/webapp \
    --dns=10.14.128.125 \
    --dns-search=unicaen.fr \
    --name sodoct-docker \
    unicaen/sodoct-docker

Use :

    http://localhost:8080


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
    
PHP5, build à partir du fichier *Dockerfile.test*:

    docker build \
    --add-host="proxy.unicaen.fr:10.14.128.99" \
    --add-host="svn.unicaen.fr:10.14.129.44" \
    --build-arg http_proxy="http://proxy.unicaen.fr:3128" \
    --build-arg https_proxy="http://proxy.unicaen.fr:3128" \
    --build-arg no_proxy=".unicaen.fr,localhost" \
    -t unicaen/sodoct-test \
    -f Dockerfile.test \
    .
    
PHP7, build à partir du fichier *Dockerfile.php7.test* :

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

Use :

    http://localhost:8080
