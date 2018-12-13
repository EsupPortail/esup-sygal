$(function () {

    $(document).ready(function() {
        // tinymce.remove();
        tinymce.init({
            selector: '.type2',
            toolbar: 'undo redo | styleselect | bold italic | bullist numlist | link',
            resize: true,
            style_formats: [
                {title: 'Titre 1', block: 'h2'},
                {title: 'Titre 2', block: 'h3'},
                {title: 'Titre 3', block: 'h4'}
            ],
            plugins: 'lists link',
            statusbar: true,
            browser_spellcheck: true,
            branding: false,
            // language: 'fr_FR',
            //menu: {},
            menubar : 'insert',
            body_id: 'contenu',
            setup: function (editor) {
                editor.on("focusout", function () {
                    console.log(tinymce.get('contenu').getContent());
                    $('textarea#specificite').val(tinymce.get('contenu').getContent());
                });
            }
        });
    });

    $('.info-icon').popover({
        html: true
    })
});