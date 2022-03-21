###########################################################################################
#
#                             Image pour l'application SyGAL.
#
###########################################################################################

ARG PHP_VERSION

FROM registre.unicaen.fr:5000/open-source/docker/sygal-image:php${PHP_VERSION}

LABEL maintainer="Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>"

COPY . /app

WORKDIR /app

RUN composer install
