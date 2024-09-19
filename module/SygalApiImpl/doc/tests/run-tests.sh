#!/bin/bash

JSON_DIR="/home/gauthierb/workspace/sygal/module/SygalApiImpl/doc/tests/json"

send() {
  curl --no-progress-meter --insecure \
  -H "Accept: application/json" -H "Content-Type: application/json" -H "Authorization: Basic cGVnYXNlOmF6ZXJ0eQ==" \
  -X POST --data-binary "@$1" \
  -b XDEBUG_SESSION=PHPSTORM \
  https://localhost.unicaen.fr:8003/api/v1/inscription-administrative
}

JSON_ORIG_FILE_NAME="inscr-admin-PEGASE_INSA-409145484"

#########################

JSON_FILE_NAME="${JSON_ORIG_FILE_NAME}"
send("${JSON_DIR}/${JSON_FILE_NAME}.json")

#########################

JSON_FILE_NAME="${JSON_ORIG_FILE_NAME}_modif_code_apprenant"
send("${JSON_DIR}/${JSON_FILE_NAME}.json")

#########################

JSON_FILE_NAME="${JSON_ORIG_FILE_NAME}_modif_ine"
send("${JSON_DIR}/${JSON_FILE_NAME}.json")

#########################

