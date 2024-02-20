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

function showModal(modalId) {
    $(modalId).modal('show');
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

    //Gestion des commentaires du gestionnaire -> afin de pouvoir passer ou non à une autre étape
    const boutonGestionnaireIncomplet = document.querySelector('.bouton_gestionnaire.incomplet');
    const boutonGestionnaireComplet = document.querySelector('.bouton_gestionnaire.complet');
    const previousButton = document.querySelector('.multipage-nav.previous');
    const nextButton = document.querySelector('.multipage-nav.next');
    const submitButton = document.querySelector('.multipage-nav.submit');
    const commentairesGestionnaire = document.querySelector('textarea.commentaires_gestionnaire');

    function updateButtonsState() {
        // Vérifiez si l'input est cochée et que le textarea est vide
        const isButtonDisabled = boutonGestionnaireIncomplet.checked && commentairesGestionnaire.value.trim() === '';

        if(previousButton){
            previousButton.disabled = isButtonDisabled;
        }
        if(nextButton){
            nextButton.disabled = isButtonDisabled;
        }
        if(submitButton){
            submitButton.disabled = isButtonDisabled;
        }

        // Mettez à jour les classes et les infobulles
        [previousButton, nextButton, submitButton].forEach(button => {
            if(button){
                if (isButtonDisabled) {
                    button.classList.add('disabled');
                    button.setAttribute('title', 'Veuillez renseigner un message pour changer d\'étape');
                } else {
                    button.classList.remove('disabled');
                    button.removeAttribute('title');
                }
            }
        });
    }

    if(boutonGestionnaireComplet){
        boutonGestionnaireComplet.addEventListener('change', updateButtonsState);
        updateButtonsState();
    }
    if(boutonGestionnaireIncomplet){
        boutonGestionnaireIncomplet.addEventListener('change', updateButtonsState);
    }
    if(commentairesGestionnaire){
        commentairesGestionnaire.addEventListener('input', updateButtonsState);
    }

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
            $('select[name="inscription[composanteDoctorat]"]').attr('disabled', true);
            $('select[name="inscription[ecoleDoctorale]"]').attr('disabled', true);
            $('select[name="inscription[specialiteDoctorat]"]').attr('disabled', true);
            $('select[name="inscription[uniteRecherche]"]').attr('disabled', true);
            $('select[name="inscription[etablissementInscription]"]').attr('disabled', true);
            $('select[name="inscription[uniteRechercheCoDirecteur]"]').attr('disabled', true);
            $('select[name="inscription[etablissementRattachementCoDirecteur]"]').attr('disabled', true);
            $('select[name="inscription[fonctionDirecteurThese]"]').attr('disabled', true);
            $('select[name="inscription[fonctionCoDirecteurThese]"]').attr('disabled', true);
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
            $('input:radio[name="financement[tempsTravail]"]:not(:checked)').attr('disabled', true);
            $('input:radio[name="financement[estSalarie]"]:not(:checked)').attr('disabled', true);
            $('select[name="financement[financement]"]').attr('disabled', true);
        }
        const contratDoctoralRadios = document.querySelectorAll('input[name="financement[contratDoctoral]"]');
        const additionalFieldscontratDoctoral = document.getElementById('additional_fields_contrat_doctoral');
        additionalFieldscontratDoctoral.style.display = 'none';
        showOrNotDiv(contratDoctoralRadios, additionalFieldscontratDoctoral)

        const infosDoctorantSalarieRadios = document.querySelectorAll('input[name="financement[estSalarie]"]');
        const additionalFieldsInfosDoctorantSalarie = document.getElementById('additional_fields_infos_salaries');
        additionalFieldsInfosDoctorantSalarie.style.display = 'none';
        showOrNotDiv(infosDoctorantSalarieRadios, additionalFieldsInfosDoctorantSalarie)
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
                    load: (source, load, error) => {
                        fetch('/admission/telecharger-document/' + individuId + '/' + inputId + '?name='+documents[inputId].libelle, {
                            method: 'GET',
                        }).then(response => {
                            if (!response.ok) {
                                error("Erreur de chargement")
                                throw new Error("Erreur de chargement");
                            }
                            response.blob().then(function(myBlob) {
                                load(myBlob)
                            });
                            if(inputId === "ADMISSION_CHARTE_DOCTORAT") {
                                $('.charteDoctoraleOperations').show();
                            }
                        }).catch(error => {
                            console.log(error)
                        });
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
                labelIdle: "Glissez-déposez votre document ou <span class='filepond--label-action'> parcourir </span> <br>(pdf/jpg/jpeg acceptés)",
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

            var accessButtonCharteDoctorale = document.querySelector('.access_charte_doctorat');
            var fileCharteDoctoratDiv = document.querySelector('.file_charte_doctorat');

            accessButtonCharteDoctorale.addEventListener('click', function(event) {
                event.preventDefault();
                $('#modalShowCharteDoctorale').modal('show');
                fileCharteDoctoratDiv.style.display = 'block';
            });

            var conventionFormationDoctorale = document.getElementById("conventionFormationDoctoraleObject");
            if(conventionFormationDoctorale){
                conventionFormationDoctorale.setAttribute("height", "0px");
                conventionFormationDoctorale.addEventListener("load", function () {
                    loadingIndicator.style.display = "none";
                    fileConventionFormationDoctoraleDiv.style.height = "auto";
                    $('.conventionFormationDoctoraleOperations').show();
                    conventionFormationDoctorale.setAttribute("height", "4000px");
                });
            }
            var loadingIndicator = document.getElementById("loading-indicator");
            if(loadingIndicator){
                loadingIndicator.style.display = "block";
            }

            var fileConventionFormationDoctoraleDiv = document.querySelector('.file_convention_formation_doctorale');
            var accessButtonConventionFormationDoctorale = document.querySelector('.access_convention_formation_doctorale');

            if(accessButtonConventionFormationDoctorale){
                accessButtonConventionFormationDoctorale.addEventListener('click', function(event) {
                    event.preventDefault();
                    $('#modalShowConventionFormationDoctorale').modal('show');
                    fileConventionFormationDoctoraleDiv.style.display = 'block';
                });
            }
        });

        const buttons = document.querySelectorAll('.access_validation_operation, .access_devalidation_operation, .access_suppression_operation, .access_notification_dossier_incomplet');
        buttons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const modalId = '#modalShowConfirmation' + button.dataset.operation;

                //Gestion des modals lorsqu'il y a deux modals superposés (convention de formation doctorale et les opérations)
                var targetElement = document.querySelector('.access_convention_formation_doctorale') || document.querySelector('.access_charte_doctorat');
                var modal = document.getElementById('modalShowConfirmationDeValidationOperation') || document.getElementById('modalShowConfirmationValidationOperation');
                if(modal){
                    if(targetElement){
                        targetElement.insertAdjacentElement('afterend', modal);
                    }
                    $('#modalShowConventionFormationDoctorale').modal('hide');
                    $('#modalShowCharteDoctorale').modal('hide');
                }
                showModal(modalId);
            });
        });

        var validation_action_operation = document.querySelector('.validation_action_operation');
        if(validation_action_operation){
            validation_action_operation.addEventListener('click', function(event) {
                validation_action_operation.classList.add('loading');
            });
        }
    }
})

$(document).ready(function () {
    if (currentUrl.indexOf("/inscription") !== -1 || currentUrl.indexOf("/financement") !== -1) {
        $('select').selectpicker();
    }

    $('[data-toggle="tooltip"]').tooltip({
        placement: 'top',
    });

    //permet de split la paire nom/prénom dans chaque input correspondant
    $(function() {
        $("#nomDirecteurThese-autocomplete, #prenomDirecteurThese-autocomplete").on('input', function(){
            $("#nomDirecteurThese").val(null)
            $("#prenomDirecteurThese").val(null)
        });
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

        $("#nomCodirecteurThese-autocomplete, #prenomCodirecteurThese-autocomplete").on('input', function(){
            $("#nomCodirecteurThese").val(null)
            $("#prenomCodirecteurThese").val(null)
        });
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