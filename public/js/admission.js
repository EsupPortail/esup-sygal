//ajouter une div englobant les boutons de navigations du formulaire
function englob_nav(type_formulaire){
    var boutons = [
        'next',
        'previous',
        'cancel',
        'submit'
    ];

    var divParent = document.createElement('div');
    divParent.classList.add("nav_formulaire");

    boutons.forEach(function(bouton) {
        var boutonElement = document.querySelector('input[name="' + type_formulaire + '[_nav][_'+ bouton + ']"]');

        if (boutonElement) {
            divParent.appendChild(boutonElement);
        }
    });

    var container = document.querySelector('form');
    container.appendChild(divParent);
}

//fonction affichant ou non les div en fonction de boutons radios
function showOrNotDiv(radiobutton, additionnalFields, ifItsAtLoadingPage) {
    radiobutton.forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (radio.checked && radio.value == "1") {
                additionnalFields.style.display = 'block';
            } else {
                additionnalFields.style.display = 'none';
            }
        });

        if (ifItsAtLoadingPage && radio.checked && radio.value == "1") {
            additionnalFields.style.display = 'block';
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    var currentUrl = window.location.href;
    var parts = currentUrl.split("/");
    var typeFormulaire = parts[parts.length - 1];

    setTimeout(function () {
        englob_nav(typeFormulaire); // Appel de la fonction avec les arguments
    }, 100);

    //permet de afficher/cacher le textarea observations pour le gestionnaire
    var observationsDiv = document.querySelector(".observations_gestionnaire");
    var boutonGestionnaire = document.querySelector(".bouton_gestionnaire.incomplet");
    var textObservationsGestionnaire = document.querySelector(".text_observations_gestionnaire");

    // Cache la div au chargement de la page si le textarea est vide
    if (textObservationsGestionnaire.value.trim() === "") {
        observationsDiv.style.display = "none";
    } else {
        observationsDiv.style.display = "block";
    }

    boutonGestionnaire.addEventListener("click", function(event) {
        event.preventDefault();

        if (observationsDiv.style.display === "none") {
            observationsDiv.style.display = "block";
        } else {
            observationsDiv.style.display = "none";
        }
    });
});

var currentUrl = window.location.href;
setTimeout(function () {
    if (currentUrl.indexOf("/individu") !== -1) {
        var diplomeRadios = document.querySelectorAll('input[name="individu[niveauEtude]"]');
        var additionalFieldsDiplome = document.getElementById('additional_fields_diplome');
        var additionalFieldsAutre = document.getElementById('additional_fields_autre');

        additionalFieldsDiplome.style.display = 'none';
        additionalFieldsAutre.style.display = 'none';

        diplomeRadios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (radio.checked && radio.value == "1") {
                    additionalFieldsDiplome.style.display = 'block';
                    additionalFieldsAutre.style.display = 'none';
                } else {
                    additionalFieldsDiplome.style.display = 'none';
                    additionalFieldsAutre.style.display = 'block';
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            var inputElements = document.querySelectorAll('input');

            for (var i = 0; i < inputElements.length; i++) {
                var input = inputElements[i];

                if (((input.type !== 'radio' && input.type !== 'submit') && input.value.trim() !== '') || input.type == 'radio' && input.checked) {
                    break;
                }
            }

            if (i === inputElements.length) {
                $('.modal').modal('show');
            }

            diplomeRadios.forEach(function (radio) {
                if (radio.checked && radio.value == "1") {
                    additionalFieldsDiplome.style.display = 'block';
                    additionalFieldsAutre.style.display = 'none';
                }
                if (radio.checked && radio.value == "2") {
                    additionalFieldsDiplome.style.display = 'none';
                    additionalFieldsAutre.style.display = 'block';
                }
            });
        })
    }

    if (currentUrl.indexOf("/inscription") !== -1) {
        var confidentialiteRadios = document.querySelectorAll('input[name="inscription[confidentialite]"]');
        var cotutelleRadios = document.querySelectorAll('input[name="inscription[coTutelle]"]');
        var codirectionRadios = document.querySelectorAll('input[name="inscription[coDirection]"]');
        var additionalFieldsConfidentialite = document.getElementById('additionalFieldsConfidentialite');
        var additionalFieldsCotutelle = document.getElementById('additionalFieldsCotutelle');
        var additionalFieldsCodirection = document.getElementById('additionalFieldsCodirection');

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
        var contratDoctoralRadios = document.querySelectorAll('input[name="financement[contratDoctoral]"]');
        var additionalFieldscontratDoctoral = document.getElementById('additional_fields_contrat_doctoral');

        additionalFieldscontratDoctoral.style.display = 'none';

        document.addEventListener('DOMContentLoaded', function () {
            showOrNotDiv(contratDoctoralRadios, additionalFieldscontratDoctoral, true)
        })

        showOrNotDiv(contratDoctoralRadios, additionalFieldscontratDoctoral, false)
    }

    if (currentUrl.indexOf("/validation") !== -1) {
        $(".upload_file > i").click(function () {
            $(this).siblings("input[type='file']").trigger('click');
        });

        // Sélectionne toutes les divs avec la classe "date_televersement"
        const dateTeleversementDivs = document.querySelectorAll('.date_televersement');

        // dateTeleversementDivs.forEach((dateDiv) => {
        //     // Vérifiez si la div "date_televersement" n'est pas vide
        //     if (dateDiv.children.length <= 0) {
        //         // Trouvez la div "action_file" au même niveau que "date_televersement"
        //         const actionFileDiv = dateDiv.nextElementSibling;
        //         // Vérifiez si la div "action_file" existe
        //         if (actionFileDiv && actionFileDiv.classList.contains('action_file')) {
        //             actionFileDiv.style.display = 'none';
        //         }
        //     }
        // });
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

