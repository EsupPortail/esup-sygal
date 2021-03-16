#!/bin/bash

# DUMONTIER Rodolphe

THESE_ID=36421
XML_FILE=./module/StepStar/tmp/theses.xml
docker-compose exec sygal bash -c "rm -rf ${XML_FILE} ; php public/index.php step-star generer-xml --these ${THESE_ID} --to ${XML_FILE} --anonymize" && \
sudo chown -R gauthierb ${XML_FILE}

TEF_DIR=./module/StepStar/tmp/out_${RANDOM}
docker-compose exec sygal bash -c "rm -rf ${TEF_DIR} ; php public/index.php step-star generer-tef --from ${XML_FILE} --dir ${TEF_DIR}" && \
sudo chown -R gauthierb ${TEF_DIR}

TEF_FILE=${TEF_DIR}/NORM_21103757.xml
docker-compose exec sygal php public/index.php step-star deposer --tef ${TEF_FILE} && \
sudo chown -R gauthierb ${TEF_DIR}


# DAVID Alexia

THESE_ID=27488
XML_FILE=./module/StepStar/tmp/theses.xml
docker-compose exec sygal bash -c "rm -rf ${XML_FILE} ; php public/index.php step-star generer-xml --these ${THESE_ID} --to ${XML_FILE} --anonymize" && \
sudo chown -R gauthierb ${XML_FILE}

TEF_DIR=./module/StepStar/tmp/out_${RANDOM}
docker-compose exec sygal bash -c "rm -rf ${TEF_DIR} ; php public/index.php step-star generer-tef --from ${XML_FILE} --dir ${TEF_DIR}" && \
sudo chown -R gauthierb ${TEF_DIR}

TEF_FILE=/app/module/StepStar/tmp/out_8992/_35679665.xml
docker-compose exec sygal php public/index.php step-star deposer --tef ${TEF_FILE} && \
sudo chown -R gauthierb ${TEF_DIR}



# LAZAAR Nouhaila

THESE_ID=28719
XML_FILE=./module/StepStar/tmp/theses.xml
docker-compose exec sygal bash -c "rm -rf ${XML_FILE} ; php public/index.php step-star generer-xml --these ${THESE_ID} --to ${XML_FILE} --anonymize" && \
sudo chown -R gauthierb ${XML_FILE}

TEF_DIR=./module/StepStar/tmp/out_${RANDOM}
docker-compose exec sygal bash -c "rm -rf ${TEF_DIR} ; php public/index.php step-star generer-tef --from ${XML_FILE} --dir ${TEF_DIR}" && \
sudo chown -R gauthierb ${TEF_DIR}

docker-compose exec sygal bash -c "php public/index.php step-star generer-zip --these ${THESE_ID}"

TEF_FILE=${TEF_DIR}/NORM_21716219.xml
docker-compose exec sygal php public/index.php step-star deposer --tef ${TEF_FILE} && \
sudo chown -R gauthierb ${TEF_DIR}
