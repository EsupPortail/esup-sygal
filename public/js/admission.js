//ajouter une div englobant les boutons de navigations du formulaire
function englob_nav(type_formulaire){
    var boutonSuivant = document.querySelector('input[name="'+type_formulaire+'[_nav][_next]"]');
    var boutonPrecedent = document.querySelector('input[name="'+type_formulaire+'[_nav][_previous]"]');
    var boutonAnnuler = document.querySelector('input[name="'+type_formulaire+'[_nav][_cancel]"]');
    var boutonEnvoyer = document.querySelector('input[name="'+type_formulaire+'[_nav][_submit]"]');

    var divParent = document.createElement('div');
    divParent.classList.add("nav_formulaire")

    if(boutonEnvoyer){
        divParent.appendChild(boutonEnvoyer);
    }
    if(boutonSuivant){
        divParent.appendChild(boutonSuivant);
    }
    if(boutonPrecedent){
        divParent.appendChild(boutonPrecedent);
    }
    if(boutonAnnuler){
        divParent.appendChild(boutonAnnuler);
    }

    var container = document.querySelector('form');
    container.appendChild(divParent);
}

document.addEventListener("DOMContentLoaded", function() {
    var currentUrl = window.location.href;

    if (currentUrl.indexOf("/etudiant") !== -1) {
        setTimeout(function () {
            englob_nav("etudiant"); // Appel de la fonction avec les arguments
        }, 100);
    } else if (currentUrl.indexOf("/inscription") !== -1) {
        setTimeout(function () {
            englob_nav("inscription"); // Appel de la fonction avec les arguments
        }, 100);
    }else if (currentUrl.indexOf("/financement") !== -1){
        setTimeout(function () {
            englob_nav("financement"); // Appel de la fonction avec les arguments
        }, 100);
    }else if (currentUrl.indexOf("/validation") !== -1){
        setTimeout(function () {
            englob_nav("validation"); // Appel de la fonction avec les arguments
        }, 100);
    }
});
//fonction affichant ou non les div en fonction de boutons radios
function showOrNotDiv(radiobutton, additionnalFields) {
    radiobutton.forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (radio.checked && radio.value == "1") {
                additionnalFields.style.display = 'block';
            } else {
                additionnalFields.style.display = 'none';
            }
        });
    });
}

function showOrNotDivStart(radiobutton, additionnalFields) {
    radiobutton.forEach(function (radio) {
        if (radio.checked && radio.value == "1") {
            additionnalFields.style.display = 'block'
        }
    });
}

var currentUrl = window.location.href;
setTimeout(function () {
    if (currentUrl.indexOf("/etudiant") !== -1) {
        var diplomeRadios = document.querySelectorAll('input[name="etudiant[niveauEtude]"]');
        var additionalFieldsDiplome = document.getElementById('additional_fields_diplome');
        var additionalFieldsAutre = document.getElementById('additional_fields_autre');

        // Cachez les champs supplémentaires au chargement de la page
        additionalFieldsDiplome.style.display = 'none';
        additionalFieldsAutre.style.display = 'none';

        //Parcourez tous les éléments radio et ajoutez un gestionnaire d'événement à chacun
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
            // Sélectionnez tous les éléments d'entrée de la page
            var inputElements = document.querySelectorAll('input');

            for (var i = 0; i < inputElements.length; i++) {
                var input = inputElements[i];

                // Vérifiez si l'élément d'entrée a une valeur non vide
                if (((input.type !== 'radio' && input.type !== 'submit') && input.value.trim() !== '') || input.type == 'radio' && input.checked) {
                    break; // Sortez de la boucle dès que vous en trouvez un
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
        var additionalFieldsConfidentialite = document.getElementById('additionalFieldsConfidentialite');
        var additionalFieldsCotutelle = document.getElementById('additionalFieldsCotutelle');

        // Cachez les champs supplémentaires au chargement de la page
        additionalFieldsConfidentialite.style.display = 'none';
        additionalFieldsCotutelle.style.display = 'none';

        document.addEventListener('DOMContentLoaded', function () {
            showOrNotDivStart(confidentialiteRadios, additionalFieldsConfidentialite)
            showOrNotDivStart(cotutelleRadios, additionalFieldsCotutelle)
        })

        showOrNotDiv(confidentialiteRadios, additionalFieldsConfidentialite)
        showOrNotDiv(cotutelleRadios, additionalFieldsCotutelle)
    }

    if (currentUrl.indexOf("/financement") !== -1) {
        var contratDoctoralRadios = document.querySelectorAll('input[name="financement[contratDoctoral]"]');
        var additionalFieldscontratDoctoral = document.getElementById('additional_fields_contrat_doctoral');

        // Cachez les champs supplémentaires au chargement de la page
        additionalFieldscontratDoctoral.style.display = 'none';

        document.addEventListener('DOMContentLoaded', function () {
            showOrNotDivStart(contratDoctoralRadios, additionalFieldscontratDoctoral)
        })

        showOrNotDiv(contratDoctoralRadios, additionalFieldscontratDoctoral)
    }

    if (currentUrl.indexOf("/validation") !== -1) {
        $(".upload_file > i").click(function () {
            $(this).siblings("input[type='file']").trigger('click');
        });

        // Sélectionnez toutes les divs avec la classe "date_televersement"
        const dateTeleversementDivs = document.querySelectorAll('.date_televersement');

        dateTeleversementDivs.forEach((dateDiv) => {
            // Vérifiez si la div "date_televersement" n'est pas vide
            if (dateDiv.children.length <= 0) {
                // Trouvez la div "action_file" au même niveau que "date_televersement"
                const actionFileDiv = dateDiv.nextElementSibling;
                // Vérifiez si la div "action_file" existe
                if (actionFileDiv && actionFileDiv.classList.contains('action_file')) {
                    actionFileDiv.style.display = 'none';
                }
            }
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
});
