document.addEventListener("DOMContentLoaded", function() {

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

    function updateCoTutelleRadio() {
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

    if(selects){
        selects.forEach(select => {
            if(select)  select.addEventListener('change', updateCoTutelleRadio);
        });

        updateCoTutelleRadio();
    }
})