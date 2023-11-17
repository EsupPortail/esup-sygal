//ajouter une div englobant les boutons de navigations du formulaire
function englob_nav(type_formulaire){
    const boutons = [
        'next',
        'previous',
        'cancel',
        'submit'
    ];

    const divParent = document.createElement('div');
    divParent.classList.add("nav_formulaire");

    boutons.forEach(function(bouton) {
        const boutonElement = document.querySelector('input[name="' + type_formulaire + '[_nav][_' + bouton + ']"]');

        if (boutonElement) {
            divParent.appendChild(boutonElement);
        }
    });

    const container = document.querySelector('form');
    container.appendChild(divParent);
}

//fonction affichant ou non les div en fonction de boutons radios
function showOrNotDiv(radiobutton, additionnalFields, ifItsAtLoadingPage) {
    radiobutton.forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (radio.checked && radio.value === "1") {
                additionnalFields.style.display = 'block';
            } else {
                additionnalFields.style.display = 'none';
            }
        });

        if (ifItsAtLoadingPage && radio.checked && radio.value === "1") {
            additionnalFields.style.display = 'block';
        }
    });
}
const currentUrl = window.location.href;
document.addEventListener("DOMContentLoaded", function() {
    const parts = currentUrl.split("/");
    const typeFormulaire = parts[parts.length - 1];

    setTimeout(function () {
        englob_nav(typeFormulaire); // Appel de la fonction avec les arguments
    }, 100);

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

        // Sélectionnez le label parent et ajoutez la classe "selected"
        const label = radioButton.parentElement;
        if (radioButton.checked) {
            label.classList.add('selected');
        }
        if(radioButton.classList.contains('complet') && label.classList.contains('selected') || radioButton.classList.contains('incomplet') && !radioButton.checked){
            commentairesDiv.style.display = "none";
        }
    });
});


setTimeout(function () {
    if (currentUrl.indexOf("/etudiant") !== -1) {
        //désactive la possibilité de changer la civilité
        $('input:radio[name="etudiant[civilite]"]:not(:checked)').attr('disabled', true);
        const diplomeRadios = document.querySelectorAll('input[name="etudiant[niveauEtude]"]');
        const additionalFieldsDiplome = document.getElementById('additional_fields_diplome');
        const additionalFieldsAutre = document.getElementById('additional_fields_autre');

        additionalFieldsDiplome.style.display = 'none';
        additionalFieldsAutre.style.display = 'none';

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

        document.addEventListener('DOMContentLoaded', function () {
            let i;
            const inputElements = document.querySelectorAll('input');

            for (i = 0; i < inputElements.length; i++) {
                const input = inputElements[i];

                if (((input.type !== 'radio' && input.type !== 'submit') && input.value.trim() !== '') || input.type === 'radio' && input.checked) {
                    break;
                }
            }

            if (i === inputElements.length) {
                $('.modal').modal('show');
            }

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
        })
    }

    if (currentUrl.indexOf("/inscription") !== -1) {
        const confidentialiteRadios = document.querySelectorAll('input[name="inscription[confidentialite]"]');
        const cotutelleRadios = document.querySelectorAll('input[name="inscription[coTutelle]"]');
        const codirectionRadios = document.querySelectorAll('input[name="inscription[coDirection]"]');
        const additionalFieldsConfidentialite = document.getElementById('additionalFieldsConfidentialite');
        const additionalFieldsCotutelle = document.getElementById('additionalFieldsCotutelle');
        const additionalFieldsCodirection = document.getElementById('additionalFieldsCodirection');

        document.addEventListener('DOMContentLoaded', function () {
            additionalFieldsConfidentialite.style.display = 'none';
            additionalFieldsCotutelle.style.display = 'none';
            additionalFieldsCodirection.style.display = 'none';
            showOrNotDiv(confidentialiteRadios, additionalFieldsConfidentialite, true)
            showOrNotDiv(cotutelleRadios, additionalFieldsCotutelle, true)
            showOrNotDiv(codirectionRadios, additionalFieldsCodirection, true)
        })

        showOrNotDiv(confidentialiteRadios, additionalFieldsConfidentialite, false)
        showOrNotDiv(cotutelleRadios, additionalFieldsCotutelle, false)
        showOrNotDiv(codirectionRadios, additionalFieldsCodirection, false)
    }

    if (currentUrl.indexOf("/financement") !== -1) {
        const contratDoctoralRadios = document.querySelectorAll('input[name="financement[contratDoctoral]"]');
        const additionalFieldscontratDoctoral = document.getElementById('additional_fields_contrat_doctoral');

        additionalFieldscontratDoctoral.style.display = 'none';

        document.addEventListener('DOMContentLoaded', function () {
            showOrNotDiv(contratDoctoralRadios, additionalFieldscontratDoctoral, true)
        })

        showOrNotDiv(contratDoctoralRadios, additionalFieldscontratDoctoral, false)
    }

    if (currentUrl.indexOf("/document") !== -1) {
        $(document).ready(function () {
            FilePond.registerPlugin(FilePondPluginFileValidateType);

            let serverResponse = '';
            // Sélectionner tous les champs de fichier et les transformer en champs FilePond
            $('input[type="file"]').each(function () {
                const inputId = $(this).attr('id');
                const pond = FilePond.create(this, {
                    acceptedFileTypes: ['application/pdf', 'image/png', 'image/jpeg'],
                    server: {
                        url: '/admission',
                        process: {
                            url: '/enregistrer-document',
                            ondata: (formData) => {
                                formData.append('individu', individuId);
                                formData.append('codeNatureFichier', inputId);
                                return formData;
                            },
                            onerror: (response) =>
                                serverResponse = JSON.parse(response),
                        },
                        revert: {
                            url: '/supprimer-document?individu=' + individuId + '&codeNatureFichier=' + inputId,
                            onerror: (response) =>
                                serverResponse = JSON.parse(response),
                        },
                        load: {
                            url: '/telecharger-document?individu=' + individuId + '&codeNatureFichier=' + inputId + '&name=',
                        },
                        remove: (source, load, error) => {
                            fetch('/admission/supprimer-document?individu=' + individuId + '&codeNatureFichier=' + inputId, {
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
                    labelIdle: "Drag & Drop votre Document ou <span class='filepond--label-action'> Parcourir </span>",
                    forceRevert: true,
                    allowRemove: true,
                    allowMultiple: false,
                    allowReplace: false,
                    credits: false,
                    maxFiles: 1,
                });


                // Vérifier si l'ID d'input correspond à une entrée dans le tableau de documents
                if (documents.hasOwnProperty(inputId)) {
                    // Construire l'objet de fichier
                    var fichier = {
                        source: documents[inputId].libelle,
                        options: {
                            type: 'local', // Type de fichier local
                        }
                    };
                    // Ajouter le fichier à FilePond
                    pond.addFiles([fichier]);
                }
            });
        });
    }
}, 100)


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
            console.log(data.item.extras);
            setTimeout(function() {
                $("#nomDirecteurThese-autocomplete").val(data.item.extras.nom);
            }, 50);
            $("#prenomDirecteurThese-autocomplete").val(data.item.extras.prenoms);
            $("#emailDirecteurThese").val(data.item.extras.email);
        })

        $("#nomCodirecteurThese-autocomplete, #prenomCodirecteurThese-autocomplete").on('autocompleteselect', function(event, data) {
            console.log(data.item.extras);
            setTimeout(function() {
                $("#nomCodirecteurThese-autocomplete").val(data.item.extras.nom);
            }, 50);
            $("#prenomCodirecteurThese-autocomplete").val(data.item.extras.prenoms);
            $("#emailCodirecteurThese").val(data.item.extras.email);
        })
    })
});

