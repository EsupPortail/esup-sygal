//fonction affichant ou non les div en fonction de boutons radios
function showOrNotDiv(radiobutton, additionnalFields) {
    radiobutton.forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (radio.checked && radio.value === "1") {
                additionnalFields.style.display = 'block';
            } else {
                additionnalFields.style.display = 'none';
            }
        });

        if (radio.checked && radio.value === "1") {
            additionnalFields.style.display = 'block';
        }
    });
}
const currentUrl = window.location.href;
document.addEventListener("DOMContentLoaded", function() {
    //permet de afficher/cacher le textarea observations pour le gestionnaire
    const commentairesDiv = document.querySelector(".commentaires_gestionnaire");
    const radioButtons = document.querySelectorAll('.observations_gestionnaire .multicheckbox input[type="radio"]');

    radioButtons.forEach(radioButton => {
        radioButton.addEventListener('click', function () {
            if (this.classList.contains('incomplet')) {
                commentairesDiv.style.display = 'block';
            } else if (this.classList.contains('complet')) {
                commentairesDiv.style.display = 'none';
            }

            document.querySelectorAll('.multicheckbox label').forEach(label => {
                label.classList.remove('selected');
            });

            const label = this.parentElement;
            label.classList.add('selected');
        });

        const label = radioButton.parentElement;
        if (radioButton.checked) {
            label.classList.add('selected');
        }
        if(radioButton.classList.contains('complet') && label.classList.contains('selected') || radioButton.classList.contains('incomplet') && !radioButton.checked){
            commentairesDiv.style.display = "none";
        }
    });

    if (currentUrl.indexOf("/etudiant") !== -1) {
        //désactive la possibilité de changer la civilité
        if(readOnly){
            $('input:radio[name="etudiant[niveauEtude]"]:not(:checked)').attr('disabled', true);
            $('input:radio[name="etudiant[situationHandicap]"]:not(:checked)').attr('disabled', true);
            $('input:radio[name="etudiant[typeDiplomeAutre]"]:not(:checked)').attr('disabled', true);
            $('select[name="etudiant[anneeDobtentionDiplomeAutre]"]').attr('disabled', true);
            $('select[name="etudiant[anneeDobtentionDiplomeNational]"]').attr('disabled', true);
        }
        $('input:radio[name="etudiant[civilite]"]:not(:checked)').attr('disabled', true);

        const btn_infos_ine = document.querySelector('.info_ine');
        if (btn_infos_ine) {
            btn_infos_ine.addEventListener('click', function (e) {
                e.preventDefault()
                $('#modalInfosIne').modal('show');
            })
        }

        const diplomeRadios = document.querySelectorAll('input[name="etudiant[niveauEtude]"]');
        const additionalFieldsDiplome = document.getElementById('additional_fields_diplome');
        const additionalFieldsAutre = document.getElementById('additional_fields_autre');

        additionalFieldsDiplome.style.display = 'none';
        additionalFieldsAutre.style.display = 'none';

        diplomeRadios.forEach(function (radio) {
            if (radio.checked && radio.value === "1") {
                additionalFieldsDiplome.style.display = 'block';
                additionalFieldsAutre.style.display = 'none';
            }
            if (radio.checked && radio.value === "2") {
                additionalFieldsDiplome.style.display = 'none';
                additionalFieldsAutre.style.display = 'block';
            }
        });

        diplomeRadios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (radio.checked && radio.value === "1") {
                    additionalFieldsDiplome.style.display = 'block';
                    additionalFieldsAutre.style.display = 'none';
                } else {
                    additionalFieldsDiplome.style.display = 'none';
                    additionalFieldsAutre.style.display = 'block';
                }
            });
        });
    }

    if (currentUrl.indexOf("/inscription") !== -1) {
        if(readOnly){
            $('input:radio[name="inscription[confidentialite]"]:not(:checked)').attr('disabled', true);
            $('input:radio[name="inscription[coTutelle]"]:not(:checked)').attr('disabled', true);
            $('input:radio[name="inscription[coDirection]"]:not(:checked)').attr('disabled', true);
            $('input:radio[name="inscription[coEncadrement]"]:not(:checked)').attr('disabled', true);
            $('select[name="inscription[ecoleDoctorale]"]').attr('disabled', true);
            $('select[name="inscription[specialiteDoctorat]"]').attr('disabled', true);
            $('select[name="inscription[uniteRecherche]"]').attr('disabled', true);
        }

        const confidentialiteRadios = document.querySelectorAll('input[name="inscription[confidentialite]"]');
        const cotutelleRadios = document.querySelectorAll('input[name="inscription[coTutelle]"]');
        const codirectionRadios = document.querySelectorAll('input[name="inscription[coDirection]"]');
        const additionalFieldsConfidentialite = document.getElementById('additionalFieldsConfidentialite');
        const additionalFieldsCotutelle = document.getElementById('additionalFieldsCotutelle');
        const additionalFieldsCodirection = document.getElementById('additionalFieldsCodirection');

        additionalFieldsConfidentialite.style.display = 'none';
        additionalFieldsCotutelle.style.display = 'none';
        additionalFieldsCodirection.style.display = 'none';
        showOrNotDiv(confidentialiteRadios, additionalFieldsConfidentialite)
        showOrNotDiv(cotutelleRadios, additionalFieldsCotutelle)
        showOrNotDiv(codirectionRadios, additionalFieldsCodirection)
    }

    if (currentUrl.indexOf("/financement") !== -1) {
        if(readOnly){
            $('input:radio[name="financement[contratDoctoral]"]:not(:checked)').attr('disabled', true);
            $('input:radio[name="financement[employeurContrat]"]:not(:checked)').attr('disabled', true);
        }
        const contratDoctoralRadios = document.querySelectorAll('input[name="financement[contratDoctoral]"]');
        const additionalFieldscontratDoctoral = document.getElementById('additional_fields_contrat_doctoral');

        additionalFieldscontratDoctoral.style.display = 'none';

        showOrNotDiv(contratDoctoralRadios, additionalFieldscontratDoctoral)
    }

    if (currentUrl.indexOf("/document") !== -1) {
        FilePond.registerPlugin(FilePondPluginFileValidateType);
        FilePond.registerPlugin(FilePondPluginPdfPreview);

        let serverResponse = '';
        // Sélectionner tous les champs de fichier et les transformer en champs FilePond
        $('input[type="file"]').each(function () {
            const inputId = $(this).attr('id');
            const pond = FilePond.create(this, {
                acceptedFileTypes: ['application/pdf', 'image/png', 'image/jpeg'],
                server: {
                    url: '/admission',
                    process: {
                        url: '/enregistrer-document/' + individuId + '/' + inputId,
                        onerror: (response) =>
                            serverResponse = JSON.parse(response),
                    },
                    revert: {
                        url: '/supprimer-document/' + individuId + '/' + inputId,
                        onerror: (response) =>
                            serverResponse = JSON.parse(response),
                    },
                    load: {
                        url: '/telecharger-document/' + individuId + '/' + inputId + '?name=',
                    },
                    remove: (source, load, error) => {
                        fetch('/admission/supprimer-document/' + individuId + '/' + inputId, {
                            method: 'DELETE',
                        }).then(response => {
                            if (!response.ok) {
                                error("Erreur de suppression")
                                throw new Error("Erreur de suppression");
                            }
                            load();
                            const admissionFileDiv = document.getElementById(inputId);
                            if (admissionFileDiv) {
                                const uploadFileDiv = admissionFileDiv.parentElement;
                                if (uploadFileDiv) {
                                    const dateTeleversementDiv = uploadFileDiv.nextElementSibling;
                                    const actionFileDiv = dateTeleversementDiv.nextElementSibling;
                                    if (dateTeleversementDiv && actionFileDiv) {
                                        dateTeleversementDiv.style.display = 'none';
                                        actionFileDiv.style.display = 'none';
                                    }
                                }
                            }
                        }).catch(error => {
                            console.log(error)
                        });
                    }
                },
                beforeRemoveFile: function () {
                    return confirm("Êtes-vous sûr de vouloir supprimer ce fichier ?");
                },
                labelFileProcessingError: () => {
                    return serverResponse.errors;
                },
                labelFileProcessingRevertError: () => {
                    return serverResponse.errors;
                },
                labelFileRemoveError: () => {
                    return serverResponse.errors;
                },
                labelFileLoadError: "Erreur durant le chargement",
                labelFileProcessing: "En cours de téléversement",
                labelFileLoading: "Chargement",
                labelFileProcessingComplete: "Téléversement terminé",
                labelFileProcessingAborted: "Téléversement annulé",
                labelFileWaitingForSize: "En attente de la taille",
                labelFileSizeNotAvailable: "Taille non disponible",
                labelTapToUndo: "Appuyez pour revenir en arrière",
                labelTapToRetry: "Appuyez pour réessayer",
                labelTapToCancel: "Appuyez pour annuler",
                labelIdle: "Glissez-déposez votre document ou <span class='filepond--label-action'> parcourir </span>",
                forceRevert: true,
                allowRemove: inputId !== "ADMISSION_CHARTE_DOCTORAT",
                allowMultiple: false,
                allowReplace: false,
                disabled: !!readOnly,
                credits: false,
                maxFiles: 1,
                pdfPreviewHeight: inputId === "ADMISSION_CHARTE_DOCTORAT" ? "8000" : false,
                pdfComponentExtraParams: inputId === "ADMISSION_CHARTE_DOCTORAT" ? 'toolbar=0&page=1' : false,
                allowPdfPreview: inputId === "ADMISSION_CHARTE_DOCTORAT",
            });


            // Vérifier si l'ID d'input correspond à une entrée dans le tableau de documents
            if (documents.hasOwnProperty(inputId)) {
                // Construire l'objet de fichier
                var fichier = {
                    source: documents[inputId].libelle,
                    options: {
                        type: 'local', // Type de fichier local
                    }
                }
                // Ajouter le fichier à FilePond
                pond.addFiles([fichier]);
            }

            var accessButton = document.querySelector('.access_charte_doctorat');
            var fileDiv = document.querySelector('.file_charte_doctorat');

            accessButton.addEventListener('click', function(event) {
                event.preventDefault();
                $('#modalShowCharteDoctorale').modal('show');
                fileDiv.style.display = 'block';
            });

            //Si aucune école doctorale n'est renseignée
            const parentDiv = document.querySelector('.notifier_gestionnaires');
            const lien = parentDiv ? parentDiv.querySelector('a.btn.btn-primary') : null;
            if (lien && !ecoleDoctoraleRenseignee) {
                lien.dataset.message = 'Veuillez renseigner votre École doctorale';
                lien.style.opacity = '0.5';
                lien.removeAttribute('data-toggle');
                lien.addEventListener('click', function (e) {
                    e.preventDefault()
                    $('#modalErreurNotification').modal('show');
                })
            }
        });
    }
})

$(document).ready(function () {
    if (currentUrl.indexOf("/inscription") !== -1) {
        $('select').selectpicker();
    }

    $('[data-toggle="tooltip"]').tooltip({
        placement: 'top',
    });

    //permet de split la paire nom/prénom dans chaque input correspondant
    $(function() {
        $("#nomDirecteurThese-autocomplete, #prenomDirecteurThese-autocomplete").on('autocompleteselect', function(event, data) {
            setTimeout(function() {
                $("#nomDirecteurThese-autocomplete").val(data.item.extras.nom);
                $("#nomDirecteurThese").val(data.item.id);
            }, 50);
            setTimeout(function() {
                $("#prenomDirecteurThese-autocomplete").val(data.item.extras.prenoms);
                $("#prenomDirecteurThese").val(data.item.id);
            }, 50);
            $("#prenomDirecteurThese-autocomplete").val(data.item.extras.prenoms);
            $("#emailDirecteurThese").val(data.item.extras.email);
        })

        $("#nomCodirecteurThese-autocomplete, #prenomCodirecteurThese-autocomplete").on('autocompleteselect', function(event, data) {
            setTimeout(function() {
                $("#nomCodirecteurThese-autocomplete").val(data.item.extras.nom);
                $("#nomCodirecteurThese").val(data.item.id);
            }, 50);
            setTimeout(function() {
                $("#prenomCodirecteurThese-autocomplete").val(data.item.extras.prenoms);
                $("#prenomCodirecteurThese").val(data.item.id);
            }, 50);
            $("#prenomCodirecteurThese-autocomplete").val(data.item.extras.prenoms);
            $("#emailCodirecteurThese").val(data.item.extras.email);
        })
    })
});