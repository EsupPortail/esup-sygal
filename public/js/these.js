function updateCoTutelleRadio(selects, radioYes, radioNo, additionalFieldsInfosCoTutelle) {
    let isSelected = false;
    selects.forEach(select => {
        if (select && select.value !== "") {
            isSelected = true;
        }
    });
    if (isSelected && radioYes) {
        radioYes.checked = true;
        if(additionalFieldsInfosCoTutelle) additionalFieldsInfosCoTutelle.style.display = 'block';
    } else {
        if(radioNo) radioNo.checked = true;
        if(additionalFieldsInfosCoTutelle) additionalFieldsInfosCoTutelle.style.display = 'none';
    }

}

function updateFields(){
    const confidentialiteRadios = document.querySelectorAll('input[name="generalites[confidentialite]"]');
    const additionalFieldsDateFinDiplome = document.getElementById('date-fin-confidentialite');

    if(additionalFieldsDateFinDiplome) additionalFieldsDateFinDiplome.style.display = 'none';
    showOrNotDiv(confidentialiteRadios, additionalFieldsDateFinDiplome)

    const cotutelleRadios = document.querySelectorAll('input[name="generalites[cotutelle]"]');
    const additionalFieldsInfosCoTutelle = document.getElementById('informations-co-tutelle');

    showOrNotDiv(cotutelleRadios, additionalFieldsInfosCoTutelle)

    const radioYes = document.querySelector('input[name="generalites[cotutelle]"][value="1"]');
    const radioNo = document.querySelector('input[name="generalites[cotutelle]"][value="0"]');
    const selects = [
        document.getElementById('etablissement-cotutelle'),
        document.getElementById('pays-cotutelle')
    ];

    if(selects) {
        selects.forEach(select => {
            if (select) select.addEventListener('change', updateCoTutelleRadio);
        });
    }

    updateCoTutelleRadio(selects, radioYes, radioNo, additionalFieldsInfosCoTutelle);
}

var url = window.location.href;
$(document).ready(function() {
    updateFields()

    var codeDeptInput = '#codeDeptTitreAcces'
    var nomDeptInput = '#nomDeptTitreAcces'

    if (url.indexOf('these/ajouter') !== -1 || url.indexOf('these/modifier/') !== -1) {
        $('span.erase-acteur').click(function() {
            let id = $(this).attr('id');
            $('select[name=' + id + '-qualite]').val("");
            $('select[name=' + id + '-etablissement]').val("");
            $('input[name="' + id + '-individu[id]').val("");
            $('input[name="' + id + '-individu[label]').val("");
        });

        //Gestion de la sélection du département
        initializeAutoCompleteDepartement(codeDeptInput, nomDeptInput)
        $(nomDeptInput).on('input', function() {
            $(codeDeptInput).val('');
        });
        setupAutocompleteDepartement(nomDeptInput, codeDeptInput);
    }
});

function initializeAutoCompleteDepartement(codeDeptInput, nomDeptInput){
    var codeDept = $(codeDeptInput).val();  // Récupérer la valeur du code département
    if (codeDept !== '') {
        if (codeDept.length > 2) {
            // Permet d'afficher les départements provenant d'apogée qui possèdent un 0 en début de chaine
            if (codeDept.charAt(0) === '0') {
                codeDept = codeDept.substring(1);
            }
        }
        $.ajax({
            url: 'https://geo.api.gouv.fr/departements/' + codeDept,
            data: {
                fields: 'nom,code'
            },
            success: function(data) {
                $(nomDeptInput).val(data.nom);
            },
            error: function() {
                console.log('Erreur lors de la récupération des informations du département.');
            }
        });
    }
    setupAutocompleteDepartement(nomDeptInput, codeDeptInput);
}

function setupAutocompleteDepartement(inputNomId, inputCodeId) {
    $(inputNomId).autocomplete({
        source: function(request, response) {
            $.ajax({
                url: 'https://geo.api.gouv.fr/departements',
                data: {
                    nom: request.term,
                    fields: 'nom,code',
                    limit: 5,
                },
                success: function(data) {
                    const suggestions = [];
                    data.forEach(function(departement) {
                        suggestions.push({
                            label: departement.nom,
                            code: departement.code,
                        });
                    });
                    response(suggestions);
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $(inputCodeId).val(ui.item.code);
            $(inputNomId).val(ui.item.label);
            return false;
        }
    });
}