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

if (url.indexOf('these/ajouter') !== -1 || url.indexOf('these/modifier/') !== -1) {
    $(function () {
        var hash = window.location.hash;
        if (hash) {
            var tabButton = $('button[data-bs-target="' + hash + '"]');
            if (tabButton.length) {
                tabButton.tab('show');
            }
        }
    })
}
$(document).ready(function() {
    updateFields()

    if (url.indexOf('these/ajouter') !== -1 || url.indexOf('these/modifier/') !== -1) {
        $('span.erase-acteur').click(function() {
            let id = $(this).attr('id');
            $('select[name=' + id + '-qualite]').val("");
            $('select[name=' + id + '-etablissement]').val("");
            $('input[name="' + id + '-individu[id]').val("");
            $('input[name="' + id + '-individu[label]').val("");
        });
    }

    if (url.indexOf('these/identite/') !== -1) {
        $('.openModalModificationTheseBtn').on('click', function() {
            var url = $(this).data('url');
            $('#modalModificationThese').modal('show');
            $.ajax({
                url: url,
                method: 'GET',
                success: function(data) {
                    $('#modalModificationTheseContent').html(data);
                    $('select').selectpicker();
                    let hashIndex = url.indexOf('#');
                    let hash = hashIndex !== -1 ? url.substring(hashIndex + 1) : null;
                    if (hash) {
                        var tabButton = $('button[data-bs-target="#' + hash + '"]');
                        if (tabButton.length) {
                            tabButton.tab('show');
                        }
                    }
                    updateFields()
                },
                error: function() {
                    $('#modalModificationTheseContent').html('Erreur lors du chargement du contenu.');
                }
            });
        });

        $('#modalModificationThese').on('hidden.bs.modal', function() {
            $('#modalModificationTheseContent').html('Chargement...');
        });
    }
});