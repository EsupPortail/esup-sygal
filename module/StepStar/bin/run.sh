#!/bin/bash

THESE_ID=36421
XML_FILE=/tmp/theses.xml
sudo rm -f ${XML_FILE}
docker-compose exec sygal php public/index.php step-star generer-xml --these ${THESE_ID} --to ${XML_FILE}

TEF_DIR=/tmp/test_${RANDOM}
docker-compose exec sygal php public/index.php step-star generer-tef --from ${XML_FILE} --dir ${TEF_DIR}

TEF_FILE=${TEF_DIR}/NORM_21103757.xml
docker-compose exec sygal php public/index.php step-star deposer --tef /tmp/test/NORM_21103757.xml
