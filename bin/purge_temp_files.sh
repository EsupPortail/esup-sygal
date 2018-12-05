#!/usr/bin/env bash

TMPDIR=/tmp

# motifs des fichiers à supprimer s'ils datent de plus de 7j :
patterns7j=(
    'sygal_fusion_*.pdf' # résultat de fusion de la page de couverture et de la thèse
)
# motifs des fichiers à supprimer s'ils datent de plus de 1j :
patterns1j=(
    'sygal_preview_*.png'      # génération de l'aperçu de la page de couverture
    'sygal_couverture_*.pdf'   # génération de la page de couverture
    'sygal_trunc_*.pdf'        # fichier de thèse sans la 1ere page
    'sygal_notif_template_*'   # rendu d'un script de vue stocké en bdd pour une notification par mail
    'gs_*'                     # fichiers temporaires ghostscript
)

echo "Suppression des fichiers de plus de 7j..."
for pattern in ${patterns7j[*]}
do
    find ${TMPDIR}/${pattern} -maxdepth 1 -type f -mtime +7 -exec rm {} \;
done

echo "Suppression des fichiers de plus de 1j..."
for pattern in ${patterns1j[*]}
do
    find ${TMPDIR}/${pattern} -maxdepth 1 -type f -mtime +1 -exec rm {} \;
done
