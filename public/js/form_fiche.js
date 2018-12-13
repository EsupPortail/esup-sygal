$(function () {

    $(document).ready(function() {
        tinymce.init({
            selector: '.type2',
            toolbar: 'undo redo | styleselect | bold italic | bullist numlist | table link',
            plugins: 'lists link table',
            style_formats: [
                {title: 'Titre 1', block: 'h2'},
                {title: 'Titre 2', block: 'h3'},
                {title: 'Titre 3', block: 'h4'}
            ],
            table_toolbar: "tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol",

            statusbar: true,
            resize: true,
            browser_spellcheck: true,
            branding: false,
            // language: 'fr_FR',
            menu: {},

            body_id: 'contenu',
            link_context_toolbar: true,
            setup: function (editor) {
                editor.on("focusout", function () {
                    console.log(tinymce.get('contenu').getContent());
                    $('textarea#specificite').val(tinymce.get('contenu').getContent());
                });
                editor.addButton('lien', {
                    text: 'Lien',
                    icon: false,
                    onclick: function () {
                        editor.windowManager.open({
                            title: 'My html dialog',
                            url: 'mydialog.html',
                            width: 500,
                            height: 300
                        });

                    }
                });
            }
        });
    });

    $(document).on("focusin", function(e) {
        if ($(e.target).closest(".mce-window, .moxman-window").length) {
            e.stopImmediatePropagation();
        }
    });
});