//Préparation de la soutenance
//MembreForm HDR

function initMembreForm() {
    const pathname = window.location.pathname;
    const regex1 = /^\/soutenance\/hdr\/[^\/]+\/proposition\/modifier-membre\/?$/;
    const regex2 = /^\/soutenance\/hdr\/[^\/]+\/proposition\/?$/;

    if (!regex1.test(pathname) && !regex2.test(pathname)) {
        return;
    }

    const exterieurRadios = document.querySelectorAll('input[name="exterieur"]');
    const roleRadios = document.querySelectorAll('input[name="role"]');
    const warningBox = document.getElementById('role-warning');
    const wrapper = document.getElementById('form-role-wrapper');

    //Lecture de l'attribut data-hdr-roles-to-disable
    let rolesToDisable = [];
    try {
        const data = wrapper.getAttribute('data-hdr-roles-to-disable');
        rolesToDisable = JSON.parse(data);
    } catch (e) {
        console.error('Impossible de lire data-hdr-roles-to-disable :', e);
    }

    function toggleRoleOptions() {
        const selected = document.querySelector('input[name="exterieur"]:checked');
        const isExterieurOui = selected && selected.value === 'non';

        roleRadios.forEach(function (radio) {
            if (rolesToDisable.includes(radio.value)) {
                // Décocher la case si elle doit être désactivée et qu'elle était déjà cochée
                if (radio.checked) {
                    radio.checked = false;
                }

                radio.disabled = isExterieurOui;
                if (isExterieurOui) {
                    radio.closest('label').classList.add('text-muted');
                } else {
                    radio.closest('label').classList.remove('text-muted');
                }
            }
        });
        if (warningBox) {
            warningBox.style.display = isExterieurOui ? 'block' : 'none';
        }
    }

    toggleRoleOptions();

    exterieurRadios.forEach(function (radio) {
        radio.addEventListener('change', toggleRoleOptions);
    });
}
document.addEventListener('DOMContentLoaded', function () {

});