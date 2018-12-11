$(function () {
    tinymce.init({
        selector: '.type1',
        toolbar: 'newdocument undo redo | bold italic',
        language: 'fr_FR',
        resize: true,
        statusbar: true,
        browser_spellcheck : true,
        branding: false,
        menu: {},
        setup: function (editor) {
            editor.on("focusout", function () {
                if (this.inError) {
                    alertFlash("Le champ n'a PAS ETE enregistré !", 'error', 3000);
                    return;
                }

                var id = this.id;
                var tmce = tinyMCE.get(id);
                var content = tmce.getContent();
                var elt = $('#'+id);

                $.post(elt.data('url'), {contenu: content, type: 'type1'}).done(function (res) {
                    console.log(res.displayContent);
                    tmce.setContent(res.displayContent);
                    alertFlash('Le champ a bien été enregistré', 'success', 1000);
                });
            })
        }
    });
    tinymce.init({
        selector: '.type2',
        toolbar: 'newdocument undo redo | bold italic | bullist',
        language: 'fr_FR',
        plugins: 'lists',
        resize: true,
        statusbar: true,
        browser_spellcheck : true,
        branding: false,
        menu: {},
        setup: function (editor) {
            editor.on("focusout", function () {
                if (this.inError) {
                    alertFlash("Le champ n'a PAS ETE enregistré !", 'error', 3000);
                    return;
                }

                var id = this.id;
                var tmce = tinyMCE.get(id);
                var content = tmce.getContent();
                var elt = $('#'+id);

                $.post(elt.data('url'), {contenu: content, type: 'type2'}).done(function (res) {
                    console.log(res.displayContent);
                    tmce.setContent(res.displayContent);
                    alertFlash('Le champ a bien été enregistré', 'success', 1000);
                });
            })
        }
    });
    tinymce.init({
        selector: '.type3',
        toolbar: 'newdocument undo redo | bold italic | bullist numlist | link',
        language: 'fr_FR',
        plugins: 'lists link',
        resize: true,
        statusbar: true,
        browser_spellcheck : true,
        branding: false,
        menu: {},
        setup: function (editor) {
            editor.on("focusout", function () {
                if (this.inError) {
                    alertFlash("Le champ n'a PAS ETE enregistré !", 'error', 3000);
                    return;
                }

                var id = this.id;
                var tmce = tinyMCE.get(id);
                var content = tmce.getContent();
                var elt = $('#'+id);

                $.post(elt.data('url'), {contenu: content, type: 'type3'}).done(function (res) {
                    tmce.setContent(res.displayContent);
                    alertFlash('Le champ a bien été enregistré', 'success', 1000);
                });
            })

        }
    });

    tinymce.init({
        selector: '.assistance',
        toolbar: 'newdocument undo redo | bold italic | bullist numlist | link |  ecrire contacter',
        language: 'fr_FR',
        plugins: 'lists link',
        resize: true,
        statusbar: true,
        browser_spellcheck : true,
        branding: false,
        menu: {},
        setup: function (editor) {
            editor.on("focusout", function () {
                if (this.inError) {
                    alertFlash("Le champ n'a PAS ETE enregistré !", 'error', 3000);
                    return;
                }

                var id = this.id;
                var tmce = tinyMCE.get(id);
                var content = tmce.getContent();
                var elt = $('#'+id);

                $.post(elt.data('url'), {contenu: content, type: 'type3'}).done(function (res) {
                    tmce.setContent(res.displayContent);
                    alertFlash('Le champ a bien été enregistré', 'success', 1000);
                });
            });
            editor.addButton('ecrire', {
                text: 'Écrire',
                icon: false,
                onclick: function () {
                    editor.insertContent('<p>Écrire à : </p>');
                }
            });
            editor.addButton('contacter', {
                text: 'Contacter',
                icon: false,
                onclick: function () {
                    editor.insertContent('<p>Contacter le <a href="http://www.unicaen.fr/intranet/systeme-d-information/demande-d-assistance/support-informatique-de-proximite-630132.kjsp?RH=1423472431175">support informatique de proximité</a>.</p>');
                }
            });
        }
    });

    tinymce.init({
        selector: '.condition',
        toolbar: 'newdocument undo redo | bold italic | bullist numlist | link |  etupass persopass',
        language: 'fr_FR',
        plugins: 'lists link',
        resize: true,
        statusbar: true,
        browser_spellcheck : true,
        branding: false,
        menu: {},
        setup: function (editor) {
            editor.on("focusout", function () {
                if (this.inError) {
                    alertFlash("Le champ n'a PAS ETE enregistré !", 'error', 3000);
                    return;
                }

                var id = this.id;
                var tmce = tinyMCE.get(id);
                var content = tmce.getContent();
                var elt = $('#'+id);

                $.post(elt.data('url'), {contenu: content, type: 'type3'}).done(function (res) {
                    tmce.setContent(res.displayContent);
                    alertFlash('Le champ a bien été enregistré', 'success', 1000);
                });
            });
            editor.addButton('etupass', {
                text: 'Etupass',
                icon: false,
                onclick: function () {
                    editor.insertContent('<p>Disposer d\'un Etupass.</p>');
                }
            });
            editor.addButton('persopass', {
                text: 'Persopass',
                icon: false,
                onclick: function () {
                    editor.insertContent('<p>Disposer d\'un Persopass.</p>');
                }
            });
        }
    });
    tinymce.init({
        selector: '.type4',
        toolbar: 'newdocument undo redo | bold | link',
        language: 'fr_FR',
        plugins: 'link',
        resize: true,
        statusbar: true,
        browser_spellcheck : true,
        branding: false,
        menu: {},
        setup: function (editor) {
            editor.on("focusout", function () {
                if (this.inError) {
                    alertFlash("Le champ n'a PAS ETE enregistré !", 'error', 3000);
                    return;
                }

                var id = this.id;
                var tmce = tinyMCE.get(id);
                var content = tmce.getContent();
                var elt = $('#'+id);

                $.post(elt.data('url'), {contenu: content, type: 'type4'}).done(function (res) {
                    tmce.setContent(res.displayContent);
                    alertFlash('Le champ a bien été enregistré', 'success', 1000);
                });
            })
        }
    });
    tinymce.init({
        selector: '.type5',
        toolbar: 'newdocument undo redo | bold italic | bullist numlist | link charte',
        language: 'fr_FR',
        plugins: 'lists link',
        resize: true,
        statusbar: true,
        browser_spellcheck : true,
        branding: false,
        skin: "lightgray",
        menu: {},
        setup: function (editor) {
            editor.on("focusout", function () {
                if (this.inError) {
                    alertFlash("Le champ n'a PAS ETE enregistré !", 'error', 3000);
                    return;
                }

                var id = this.id;
                var tmce = tinyMCE.get(id);
                var content = tmce.getContent();
                var elt = $('#'+id);

                $.post(elt.data('url'), {contenu: content, type: 'type5'}).done(function (res) {
                    tmce.setContent(res.displayContent);
                    alertFlash('Le champ a bien été enregistré', 'success', 1000);
                });
            });
            editor.addButton('charte', {
                text: 'charte UNICAEN',
                icon: false,
                onclick: function () {
                    editor.insertContent('Vous devez respecter la <a href="http://vie-etudiante.unicaen.fr/vie-numerique/documents-de-reference/">charte Unicaen</a> relative à l’usage du système d’information et des technologies de l’information et de la communication à l’université de Caen Normandie.');
                }
            });

        }
    });

    $('.info-icon').popover({
        html: true
    })

});