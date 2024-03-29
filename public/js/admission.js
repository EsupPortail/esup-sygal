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

function updateButtonsState(isButtonDisabled) {
    const previousButton = document.querySelector('.multipage-nav.previous');
    const nextButton = document.querySelector('.multipage-nav.next');
    const submitButton = document.querySelector('.multipage-nav.submit');

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

    tinymce.remove();
    tinymce.init({
        selector: '.description_commentaires_gestionnaire',
        toolbar: 'undo redo | bold italic | bullist numlist',
        plugins: 'lists link',
        statusbar: true,
        resize: true,
        browser_spellcheck: true,
        branding: false,
        language: 'fr_FR',
        menu: {},
        link_context_toolbar: true,
        setup: function (editor) {
            function handleEditorChange() {
                const isButtonDisabled = boutonGestionnaireIncomplet.checked && editor.getContent().trim() === '';
                updateButtonsState(isButtonDisabled);
            }
            if (boutonGestionnaireComplet) {
                boutonGestionnaireComplet.addEventListener('change',handleEditorChange)
            }
            if (boutonGestionnaireIncomplet) {
                boutonGestionnaireIncomplet.addEventListener('change', handleEditorChange)
            }
            // Réagir lors du changement de contenu
            editor.on('input', handleEditorChange)
            editor.on('NodeChange', handleEditorChange)
        }
    });

    if (currentUrl.indexOf("/etudiant") !== -1) {
        //désactive la possibilité de changer la civilité
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
                credits: false,
                maxFiles: 1,
                pdfPreviewHeight: inputId === "ADMISSION_CHARTE_DOCTORAT" ? "8000" : false,
                pdfComponentExtraParams: inputId === "ADMISSION_CHARTE_DOCTORAT" ? 'toolbar=0&page=1' : false,
                allowPdfPreview: inputId === "ADMISSION_CHARTE_DOCTORAT",
            });


            if (documents.hasOwnProperty(inputId)) {
                // Construire l'objet de fichier
                var fichier = {
                    source: documents[inputId].libelle,
                    options: {
                        type: 'local', // Type de fichier local
                    }
                }
                if(inputId !== "ADMISSION_CHARTE_DOCTORAT") {
                    fichier.options['file'] = {
                        name: documents[inputId].libelle,
                        size: documents[inputId].size,
                    };
                    // Ajouter le fichier à FilePond
                    pond.addFiles([fichier]);
                }
            }

            //GESTION DE LA CHARTE DOCTORALE
            var accessButtonCharteDoctorale = document.querySelector('.access_charte_doctorat');
            var fileCharteDoctoratDiv = document.querySelector('.file_charte_doctorat');

            if(accessButtonCharteDoctorale){
                accessButtonCharteDoctorale.addEventListener('click', function(event) {
                    event.preventDefault();
                    $('#modalShowCharteDoctorale').modal('show');
                    //Chargement de la charte doctorale lors de la première apparition de la popup, sinon le fichier peut ne pas apparaitre
                    if (documents.hasOwnProperty(inputId)) {
                        if(inputId === "ADMISSION_CHARTE_DOCTORAT") {
                            // Ajouter le fichier à FilePond
                            pond.addFiles([fichier]);
                        }
                    }
                    fileCharteDoctoratDiv.style.display = 'block';
                });
            }

            //GESTION DE LA CONVENTION DE FORMATION DOCTORALE
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
            var accessButtonConventionFormationDoctorale = document.querySelectorAll('.access_convention_formation_doctorale');

            if(accessButtonConventionFormationDoctorale){
                accessButtonConventionFormationDoctorale.forEach(function(button) {
                    button.addEventListener('click', function(event) {
                        event.preventDefault();
                        $('#modalShowConventionFormationDoctorale').modal('show');
                        fileConventionFormationDoctoraleDiv.style.display = 'block';
                    });
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
                var modal = document.getElementById('modalShowConfirmation' + button.dataset.operation);
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

        const validations_action_operation = document.querySelectorAll('.validation_action_operation');
        if(validations_action_operation){
            validations_action_operation.forEach(function(validation_action_operation) {
                validation_action_operation.addEventListener('click', function (event) {
                    validation_action_operation.classList.add('loading');
                    document.body.style.pointerEvents = 'none';
                    document.body.style.cursor = 'wait';
                });
            });
        }

        var lienRecapitulatif = document.querySelector('.access_recapitulatif_dossier_signe');
        var divRecapitulatif = document.getElementById('file_recapitulatif_dossier_signe');

        if(lienRecapitulatif){
            lienRecapitulatif.addEventListener('click', function(event) {
                event.preventDefault();

                // Toggle de la visibilité de la div
                if (divRecapitulatif.style.display === 'none' || divRecapitulatif.style.display === '') {
                    divRecapitulatif.style.display = 'block';
                } else {
                    divRecapitulatif.style.display = 'none';
                }
            });
        }
    }

    if (currentUrl.includes('convention-formation/modifier/') || currentUrl.includes('convention-formation/ajouter/')) {
        tinymce.remove();
        tinymce.init({
            selector: 'textarea',
            toolbar: 'undo redo | bold italic | bullist numlist | table link',
            plugins: 'lists link table',
            table_toolbar: "tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol",
            statusbar: true,
            resize: true,
            browser_spellcheck: true,
            branding: false,
            language: 'fr_FR',
            menu: {},

            body_id: 'contenu',
            link_context_toolbar: true,
        });
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