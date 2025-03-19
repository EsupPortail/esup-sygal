Installation : ressources
=========================

Fichiers
--------

### Fichiers communs

SyGAL utilise des fichiers dits "communs" qui sont proposés au téléchargement à différents endroits dans l'appli.

Liste du moment :
  - CHARTE-DE-DEPOT-ET-DIFFUSION-DES-THESES-VALIDE
  - DELEGATION-DE-SIGNATURE-AU-PRESIDENT-DU-JURY-DE-SOUTENANCE-DE-THESE
  - DEMANDE-DELOCALISATION-SOUTENANCE-THESE
  - DEMANDE-DE-CONFIDENTIALITE-DE-SOUTENANCE-DE-THESE
  - FORMULAIRE-DEMANDE-LABEL-EUROPEEN
  - RAPPORT-ANNUEL-DOCTORANT-REGION
  - RAPPORT-D-ACTIVITE-ANNUEL
  - RAPPORT-DE-FIN-DE-CONTRAT-DE-THESE
  - CHARTE-DU-DOCTORAT

Ces fichiers communs en particulier possèdent un "id permanent" (fichier.permanent_id) qui permet de proposer 
une URL permanente (càd pérenne) de téléchargement.

Les scripts de création de la base de données se chargent d'insérer/déclarer l'existence de ces fichiers mais il
faut aussi les fournir physiquement à tout établissement qui voudrait installer l'appli. Charge ensuite à ce dernier
de les adapter à leur identité/contexte bien-sûr.

Voici une requête SQL permettant de générer les lignes de commandes bash permettant, à partir de la liste des 
**fichiers communs avec id permanent** existants en BDD de zipper dans /tmp/fichiers_communs_avec_id_permanent.tgz, et pouvoir ainsi
les mettre à disposition pour une install de l'appli :

```sql
-- requête SQL pour générer les cmd bash permettant des créer /tmp/fichiers_communs_avec_id_permanent.tgz :
select 'UPLOAD_DIR_PATH=upload/communs && \
DEST_DIR_NAME="fichiers_communs_avec_id_permanent" && \
DEST_DIR_PATH="/tmp/$DEST_DIR_NAME" && \
mkdir $DEST_DIR_PATH && \'
union all
select 'cp -v $UPLOAD_DIR_PATH/"'||nom||'" $DEST_DIR_PATH && \'
from fichier f, nature_fichier nf
where f.permanent_id is not null and nature_id = nf.id and nf.code = 'COMMUNS'
union all
select '( cd /tmp && tar cvzf $DEST_DIR_NAME.tgz $DEST_DIR_NAME )'
;
```

Exemple de résultat :

```bash
UPLOAD_DIR_PATH=upload/communs && \
DEST_DIR_NAME="fichiers_communs_avec_id_permanent" && \
DEST_DIR_PATH="/tmp/$DEST_DIR_NAME" && \
mkdir $DEST_DIR_PATH && \
cp -v $UPLOAD_DIR_PATH/"CHARTE-DE-DEPOT-ET-DIFFUSION-DES-THESES-VALIDE-20180921-b0a20575-COMMUNS.pdf" $DEST_DIR_PATH && \
cp -v $UPLOAD_DIR_PATH/"SYGAL---DELEGATION-DE-SIGNATURE-AU-PRESIDENT-DU-JURY-DE-SOUTENANCE-DE-THESE-d2d628ff-DIVERS.docx" $DEST_DIR_PATH && \
cp -v $UPLOAD_DIR_PATH/"SYGAL---DEMANDE-DELOCALISATION-SOUTENANCE-THESE-1b28305e-DIVERS.docx" $DEST_DIR_PATH && \
cp -v $UPLOAD_DIR_PATH/"SYGAL---DEMANDE-DE-CONFIDENTIALITE-DE-SOUTENANCE-DE-THESE-4e79c1fe-DIVERS.docx" $DEST_DIR_PATH && \
cp -v $UPLOAD_DIR_PATH/"SYGAL-FORMULAIRE-DEMANDE-LABEL-EUROPEEN-27-05-860ec254-DIVERS.rtf" $DEST_DIR_PATH && \
cp -v $UPLOAD_DIR_PATH/"RAPPORT-ANNUEL-DOCTORANT-REGION-2020-34df2aba-COMMUNS.docx" $DEST_DIR_PATH && \
cp -v $UPLOAD_DIR_PATH/"RAPPORT-D-ACTIVITE-ANNUEL-2022---V2-b754028a-COMMUNS.docx" $DEST_DIR_PATH && \
cp -v $UPLOAD_DIR_PATH/"RAPPORT-DE-FIN-DE-CONTRAT-DE-THESE-2022---V1-fcba3ff1-COMMUNS.docx" $DEST_DIR_PATH && \
cp -v $UPLOAD_DIR_PATH/"CHARTE-DU-DOCTORAT-fdb96856-COMMUNS.pdf" $DEST_DIR_PATH && \
( cd /tmp && tar cvzf $DEST_DIR_NAME.tgz $DEST_DIR_NAME )

```
