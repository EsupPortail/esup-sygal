/**
 * Exécution d'actions en cascade.
 * Equivalent à par exemple: refreshingThese(refreshingAnnexes(refreshingDiffusion(function() {})))();
 *
 * @param actions Liste de fonctions. Format attendu:
 * [
 *    refreshingThese, // équivalent à { func: refreshingThese, effect: undefined },
 *    { func: refreshingAnnexes, effect: false },
 *    { func: refreshingDiffusion, effect: true },
 * ]
 * Avec :
 * var refreshingThese =
 *    function(done, effect) {
         *       return function() {
         *           // ...
         *           if (effect) { ... } else { ... }
         *           if (done) done();
         *       };
         *    };
 * var refreshingAnnexes =
 *    function(done, effect) {
         *       return function() {
         *           // ...
         *           if (effect) { ... } else { ... }
         *           if (done) done();
         *       };
         *    };
 * var refreshingDiffusion =
 *    function(done, effect) {
         *       return function() {
         *           // ...
         *           if (effect) { ... } else { ... }
         *           if (done) done();
         *       };
         *    };
 * @param thisArg
 */
function runInCascade(actions, thisArg) {
    if (!__runInX_assertActionsAreCallable(actions)) {
        console.error("runInCascade(): stop, les actions spécifiées ne sont pas valides!");
        return null;
    }
    var current, done, effect, action = function () {};
    for (var i = actions.length - 1; i >= 0; i--) {
        current = actions[i];
        done = action;
        if (typeof current === "object") {
            effect = current.effect;
            current = current.func;
        }
        action = current.call(thisArg, done, effect);
    }
    action.call(thisArg);
}

/**
 * Exécution d'actions en parallèle.
 * Equivalent à par exemple: refreshingThese()(); refreshingAnnexes()(); refreshingDiffusion()();
 *
 * @param actions Liste de fonctions. Format attendu:
 * [
 *    refreshingThese, // équivalent à { func: refreshingThese, effect: undefined },
 *    { func: refreshingAnnexes, effect: false },
 *    { func: refreshingDiffusion, effect: true },
 * ]
 * Avec :
 * var refreshingThese =
 *    function(done, effect) {
         *       return function() {
         *           // ...
         *           if (effect) { ... } else { ... }
         *           if (done) done();
         *       };
         *    };
 * var refreshingAnnexes =
 *    function(done, effect) {
         *       return function() {
         *           // ...
         *           if (effect) { ... } else { ... }
         *           if (done) done();
         *       };
         *    };
 * var refreshingDiffusion =
 *    function(done, effect) {
         *       return function() {
         *           // ...
         *           if (effect) { ... } else { ... }
         *           if (done) done();
         *       };
         *    };
 * @param thisArg
 */
function runInParalell(actions, thisArg) {
    if (! __runInX_assertActionsAreCallable(actions)) {
        console.error("runInParalell(): stop, les actions spécifiées ne sont pas valides!");
        return null;
    }
    var current, effect;
    for (var i = actions.length - 1; i >= 0; i--) {
        current = actions[i];
        if (typeof current === "object") {
            effect = current.effect;
            current = current.func;
        }
        current.call(thisArg, function () {
        }, effect).call(thisArg);
    }
}


function __runInX_assertActionsAreCallable(actions) {
    var action;
    for (var i = 0; i < actions.length; i++) {
        action = actions[i];
        if (typeof action === "object") {
            if (! action.func) {
                console.error("Action n°" + i + " non valide car 'action.func' introuvable");
                return false;
            }
            // if (action.done && typeof action.done !== "function") {
            //     console.error("Action n°" + i + " non valide car 'action.done' n'est pas une fonction");
            //     return false;
            // }
            action = action.func;
        }
        if (typeof action !== "function") {
            console.error("Action n°" + i + " non valide car ce n'est pas une fonction");
            return false;
        }
        if (action.length !== 2) {
            console.error("Action n°" + i + " (" + action.name + ") non valide car la fonction doit avoir les arguments suivants: 'done', 'effect'");
            return false;
        }
    }
    return true;
}




$.widget("unicaen.widgetTitreLangueThese", {

    options: {
        valueForLangueFrancais: "fr",
        labelForLangueFrancais: "Titre en anglais",
        labelForLangueAutre:    "Titre en français"
    },

    _create: function () {
        var self = this;

        // On installe les callbacks
        this.getLangueInput().on('change', function () {
            self.langueDidChange($(this).val());
        });

        // Affichage initial
        this.langueDidChange(this.getLangueInput().val());
    },

    langueDidChange: function (value) {
        var titreAutreLangueLabel = this.getTitreAutreLangueLabel();
        if (value === this.options.valueForLangueFrancais) {
            titreAutreLangueLabel.html(this.options.labelForLangueFrancais);
        } else {
            titreAutreLangueLabel.html(this.options.labelForLangueAutre);
        }
    },

    getLangueInput: function () {
        return this.element.find("select[name='langue']");
    },
    getTitreAutreLangueLabel: function() {
        return this.element.find("label[for='titreAutreLangue']");
    }
});


$.widget("unicaen.widgetDroitAuteurThese", {

    _create: function () {
        var self = this;

        // On installe les callbacks
        this.getInput().on('change', function () {
            self.didRespond($(this).val(), true);
        });

        // Init selon réponse
        var elem = this.getInput('checked');
        var response = elem ? elem.val() : null;
        this.didRespond(response, false);
    },

    didRespond: function (value, effect) {
        effect = effect ? "fade" : null;
        switch (value) {
            case "1":
                var nbFichiersThese = this.getTheseUploaderDiv().widgetUploader("uploadedFilesCount");
                var nbFichiersAnnexes = this.getAnnexesUploaderDiv().widgetUploader("uploadedFilesCount");
                if (nbFichiersThese > 0 || nbFichiersAnnexes > 0) {
                    alert("Les fichiers expurgés déposés seront supprimés automatiquement une fois que vous aurez validé ce formulaire.");
                }
                this.getTheseDiv().hide(effect, {direction: "left"}).find(':input').prop('disabled', 'disabled');
                this.getAnnexesDiv().hide(effect, {direction: "left"}).find(':input').prop('disabled', 'disabled');
                break;
            case "0":
                this.getTheseDiv().refresh({}, function () {
                    $(this).show();
                });
                this.getAnnexesDiv().refresh({}, function () {
                    $(this).show();
                });
                this.getTheseDiv().show(effect, {direction: "left"}).find(':input').prop('disabled', false);
                this.getAnnexesDiv().show(effect, {direction: "left"}).find(':input').prop('disabled', false);
                break;
            default:
                this.getDivFichiersExpurges().hide();
                this.getTheseDiv().hide();
                this.getAnnexesDiv().hide();
        }
        this.element.trigger('didRespond', value);
    },

    getInput: function (checked) {
        var elements = this.element.find("input.droitAuteur");
        if (checked) {
            elements = elements.filter(":checked").first();
            return elements.length ? elements.first() : null;
        }
        return elements
    },
    getDivFichiersExpurges: function () {
        return this.element.find(".fichiersExpurges");
    },
    getTheseDiv: function () {
        return this.element.find("#theseDiv");
    },
    getAnnexesDiv: function () {
        return this.element.find("#annexesDiv");
    },
    getTheseUploaderDiv: function() {
        return this.element.find("#uploader-div-these");
    },
    getAnnexesUploaderDiv: function() {
        return this.element.find("#uploader-div-autres");
    }

});

$.widget("unicaen.widgetAutorisationMiseEnLigne", {

    _create: function () {
        var self = this;

        // On installe les callbacks
        this.getInputAutoris().on('change', function () {
            self.didRespond($(this).val(), true);
        });

        // Init selon réponse
        var elem = this.getInputAutoris('checked');
        var response = elem ? elem.val() : null;
        this.didRespond(response, false);
    },

    didRespond: function (value, effect) {
        effect = effect ? "slide" : null;
        switch (value) {
            case "2": // oui immédiatement
                this.getInputDivEmbargoDuree().hide(effect, {direction: "left"}).find(':input').prop('disabled', 'disabled');
                this.getInputDivMotif().hide(effect, {direction: "up"}).find(':input').prop('disabled', 'disabled');
                this.getInputDivAuteur().show(effect, {direction: "up"}).find(':input').prop('disabled', false);
                this.getExplicOuiImmediat().show();
                this.getExplicOuiEmbargo().hide();
                this.getExplicNon().hide();
                break;
            case "1": // oui avec embargo
                this.getInputDivEmbargoDuree().show(effect, {direction: "left"}).find(':input').prop('disabled', false);
                this.getInputDivMotif().show(effect, {direction: "up"}).find(':input').prop('disabled', false);
                this.getInputDivAuteur().show(effect, {direction: "up"}).find(':input').prop('disabled', false);
                this.getExplicOuiImmediat().hide();
                this.getExplicOuiEmbargo().show();
                this.getExplicNon().hide();
                break;
            case "0": // non
                this.getInputDivEmbargoDuree().hide(effect, {direction: "left"}).find(':input').prop('disabled', 'disabled');
                this.getInputDivMotif().show(effect, {direction: "up"}).find(':input').prop('disabled', false);
                this.getInputDivAuteur().hide(effect, {direction: "up"}).find(':input').prop('disabled', 'disabled');
                this.getExplicOuiImmediat().hide();
                this.getExplicOuiEmbargo().hide();
                this.getExplicNon().show();
                break;
            default:
                this.getInputDivEmbargoDuree().hide();
                this.getInputDivMotif().hide();
                this.getInputDivAuteur().hide();
                this.getExplicOuiImmediat().hide();
                this.getExplicOuiEmbargo().hide();
                this.getExplicNon().hide();
        }
    },

    getInputAutoris: function (checked) {
        var elements = this.element.find("input.autoris");
        if (checked) {
            elements = elements.filter(":checked").first();
            return elements.length ? elements.first() : null;
        }
        return elements
    },
    getInputDivEmbargoDuree: function () {
        return this.element.find(".embargoDuree");
    },
    getInputDivMotif: function () {
        return this.element.find(".motif");
    },
    getInputDivAuteur: function () {
        return this.element.find(".auteur");
    },
    getExplicOuiImmediat: function () {
        return this.element.find("#explicOuiImmediat");
    },
    getExplicOuiEmbargo: function () {
        return this.element.find("#explicOuiEmbargo");
    },
    getExplicNon: function () {
        return this.element.find("#explicNon");
    }
});

$.widget("unicaen.widgetConfidentialiteThese", {

    _create: function () {
        var self = this;

        // On installe les callbacks
        this.getInputConfident().on('change', function () {
            self.didRespond($(this).val(), true);
        });

        // Init selon réponse
        var elem = this.getInputConfident('checked');
        var response = elem ? elem.val() : null;
        this.didRespond(response, false);
    },

    didRespond: function (value, effect) {
        effect = effect ? "slide" : null;
        switch (value) {
            case "1":
                this.getDivDateFin().show(effect, {direction: "left"}).find(':input').prop('disabled', false);
                this.getExplicConfidentialiteOui().show(effect, {direction: "left"});
                this.getExplicConfidentialiteNon().hide(effect, {direction: "left"});
                break;
            case "0":
                this.getDivDateFin().hide(effect, {direction: "left"}).find(':input').prop('disabled', 'disabled');
                this.getExplicConfidentialiteOui().hide(effect, {direction: "left"});
                this.getExplicConfidentialiteNon().show(effect, {direction: "left"});
                break;
            default:
                this.getDivDateFin().hide();
                this.getExplicConfidentialiteOui().hide();
                this.getExplicConfidentialiteNon().hide();
                break;
        }
    },

    getInputConfident: function (checked) {
        var elements = this.element.find("input.confident");
        if (checked) {
            elements = elements.filter(":checked").first();
            return elements.length ? elements.first() : null;
        }
        return elements
    },
    getDivDateFin: function () {
        return this.element.find(".confidentDateFin");
    },
    getExplicConfidentialiteOui: function () {
        return this.element.find("#explicConfidentialiteOui");
    },
    getExplicConfidentialiteNon: function () {
        return this.element.find("#explicConfidentialiteNon");
    }
});

/**
 *
 */
$.widget("unicaen.widgetImageLoader", {

    _create: function () {
        var self = this;

        self.getImagePlaceholders().each(function() {
            self.loadPhoto($(this));
        });
    },

    loadPhoto: function (imagePlaceholder) {
        var url = imagePlaceholder.data('src');

        //create image to preload:
        var image = new Image();
        $(image).on('load', function() {
            insertResult();
            $("body").trigger('image-loaded-event', { imagePlaceholder: imagePlaceholder });
        }).on('error', function() {
            image = null;
            insertResult();
        });
        $(image).attr({
            src: url,
            width: imagePlaceholder.css('width')
        });

        function insertResult() {
            var content = image ? $(image) : $("<span />").css('color','red').html("Une erreur est survenue !");
            imagePlaceholder.removeClass('loading').replaceWith(content);
        }
    },

    getImagePlaceholders: function () {
        return this.element.find(".image-placeholder");
    }
});


$(function () {
    WidgetInitializer.add('widget-autorisation-mise-en-ligne', 'widgetAutorisationMiseEnLigne');
    WidgetInitializer.add('widget-confidentialite-these', 'widgetConfidentialiteThese');
    WidgetInitializer.add('widget-droit-auteur-these', 'widgetDroitAuteurThese');
    WidgetInitializer.add('widget-uploader', 'widgetUploader');
    WidgetInitializer.add('widget-titre-langue-these', 'widgetTitreLangueThese');
    WidgetInitializer.add('widget-image-loader', 'widgetImageLoader');

    $('[data-bs-toggle="popover"]').popover({
        sanitize: false,
    });
    $('[data-bs-toggle="tooltip"]').tooltip();

    // le plugin bootstrap-confirmation n'est pas compatible avec Bootstrap 5 donc on l'a remplacé par ça !
    $('[data-toggle="confirmationx"]').each(function () {
        $(this).on("click", function () {
            $(this).addClass("loading");
            const ok = confirm($(this).data('message') || "Êtes-vous sûr·e ?");
            if (!ok) {
                $(this).removeClass("loading");
            }
            return ok;
        });
    });
});

// /**
//  *
//  * @constructor
//  */
// $.widget("sygal.theseRecherche", {
//
//     rechercher: function (text) {
//         var self = this;
//
//         if (text.length > 1) {
//             self.getElementLoading().show();
//             self.getElementRecherche().refresh({text: text}, function (response, status, xhr) {
//                 if (status == "error") {
//                     var msg = "Désolé mais une erreur est survenue: ";
//                     self.getElementRecherche().html(msg + xhr.status + " " + xhr.statusText + xhr.responseText);
//                 }
//                 self.getElementLoading().hide();
//             });
//         }
//     },
//
//     _create: function () {
//         var self = this;
//
//         this.getElementCritere().autocomplete({
//             source: function (event, ui) {
//                 self.rechercher(event.term);
//                 return {};
//             }
//         });
//
//         this.getElementCritere().focus();
//     },
//
//     getElementCritere: function () {
//         return this.element.find("#text");
//     },
//     getElementRecherche: function () {
//         return this.element.find('.recherche');
//     },
//     getElementLoading: function () {
//         return this.element.find('#these-recherche-loading');
//     }
// });
//
// $(function () {
//     WidgetInitializer.add('these-recherche', 'theseRecherche');
// });
