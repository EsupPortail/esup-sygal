/**
 * Source du widget Uploader, à copier-coller dans "app.js".
 *
 * NB: nécessite le plugin jQuery-File-Upload (https://github.com/blueimp/jQuery-File-Upload).
 */
$.widget("unicaen.widgetUploader", {

    uploadEventFilesListed:    "upload-event-files-listed",
    uploadEventFilePreDeleted: "upload-event-file-pre-deleted",
    uploadEventFileDeleted:    "upload-event-file-deleted",
    uploadEventFileUploaded:   "upload-event-file-uploaded",
    uploadEventFilesAdded:     "upload-event-files-added",

    deleteCancelled: false,

    _create: function () {
        if (! $.fn.fileupload) {
            console.error(
                "Impossible d'initialiser 'unicaen.widgetUploader' sur l'élément suivant " +
                "car le plugin jQuery-File-Upload (https://github.com/blueimp/jQuery-File-Upload) " +
                "est introuvable : ", this.element);
            return;
        }
        this.installUploader();
    },

    /**
     * Installe (si cela n'a pas déjà été fait) sur l'élément le nécessaire pour gérer l'upload de fichier en mode AJAX.
     */
    installUploader: function () {
        if (this.element.data('uploader-installed') === "1") {
            return;
        }

        var self = this;

        var choose = this.getBrowseButton();
        var fileInput = this.getFileInput();
        var filesDiv = this.getFilesDiv();
        var button = this.getUploadButton();
        // NB: "button" non exploité pour l'instant car l'upload est déclenché dès la sélection de fichiers.

        /**
         * Utilisation du plugin jQuery-File-Upload
         * (https://github.com/blueimp/jQuery-File-Upload)
         */
        fileInput.fileupload({
            dataType: 'json',
            start: function(e, data) {
                self.triggerUploadEvent(self.uploadEventFilesAdded);
            },
            submit: function (e, data) {
                // interdiction du bouton d'envoi
                button.button('loading');
                // ajout d'un témoin de chargement AJAX
                if (!filesDiv.find("ul").length) {
                    filesDiv.html("<ul/>");
                }
                filesDiv.find("ul").append($("<li/>").addClass("loading").html("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Patientez, svp..."));
                data.formData = self.element.find(".uploader-submitable").serializeArrayJson();
                // return false to cancel submit
            },
            done: function (e, data) {
                // détecte si des erreurs sont retournées
                if (data.result.errors) {
                    self.updateUploadContainer(false, null);
                    alert("Impossible de déposer le fichier!\n- " + data.result.errors.join('\n- '));
                }
                else {
                    self.updateUploadContainer(true, function () {
                        self.triggerUploadEvent(self.uploadEventFileUploaded);
                    });
                }
                // getProgressbar().parent().hide().end().css('width', '0%');
            },
            fail: function (e, data) {
                console.error(e, data);
                alert("Oups, une erreur s'est produite pendant l'envoi de fichier! Essayez à nouveau, svp.");
            }
            // progressall: function (e, data) {
            //     console.log(data);
            //     var progress = parseInt(data.loaded / data.total * 100, 10);
            //     var percents = progress + '%';
            //     getProgressbar().parent().show().end().css('width', percents).text(percents);
            // }
        });

        // chargement initial de la liste des fichiers
        filesDiv.addClass("loading").refresh([], function () {
            filesDiv.hide().removeClass("loading").fadeIn();
            self.triggerUploadEvent(self.uploadEventFilesListed);
        });

        // écoute clic sur suppression de fichier pour faire la requête AJAX et rafraîchir la liste des fichiers
        filesDiv.on("click", ".delete-file", function (event) {
            event.preventDefault();
            var a = $(this);
            self.triggerUploadEvent(self.uploadEventFilePreDeleted);
            if (self.deleteCancelled === true) {
                self.deleteCancelled = false;
                return;
            }
            a.button('loading');
            $.post(a.prop('href'), [], function (data, textStatus, jqXHR) {
                a.parent("li").fadeOut();
                filesDiv.refresh({}, function () {
                    self.triggerUploadEvent(self.uploadEventFileDeleted);
                });

            });
        });

        // affichage/masquage bouton d'envoi selon sélection de fichier
        choose.on("change", function () {
            self.updateUploadButton();
        });

        // masquage initial du bouton d'envoi
        self.updateUploadButton();

        this.element.data('uploader-installed', "1");
    },

    /**
     * @param event
     */
    triggerUploadEvent: function (event) {
        this.element.trigger(event, [this.element]);
    },

    /**
     * Rafraîchit la liste des fichiers déposés puis quand c'est fait :
     * - réinitialise le formulaire, si demandé ;
     * - autorise le bouton "Envoyer"
     * - affiche ou masque le bouton "Envoyer" en fonction de la situation.
     *
     * @param {boolean} resetInput
     * @param {function} trigFnc
     */
    updateUploadContainer: function (resetInput, trigFnc) {
        var self = this;
        this.getFilesDiv().refresh([], function () {
            if (resetInput) {
                self.getFileInput().val('');
            }
            if (trigFnc) {
                trigFnc();
            }
            self.getUploadButton().button('reset');
            self.updateUploadButton();
        });
    },

    /**
     * Affiche ou masque le bouton "Envoyer" selon qu'un fichier a été sélectionné ou non
     * via le bouton "Parcourir".
     */
    updateUploadButton: function () {
        var browseButton = this.getBrowseButton();
        // var sendBtn      = browseButton.siblings(".upload-file");
        var sendBtn = this.getUploadButton();

        browseButton.val() ? sendBtn.fadeIn() : sendBtn.hide();
    },

    uploadedFilesCount: function() {
        return this.element.find(".uploaded-files-div ul li").length;
    },

    cancelDelete: function() {
        this.deleteCancelled = true;
    },

    getFileInput: function () {
        return this.element.find("input[type='file']");
    },
    getFilesDiv: function () {
        return this.element.find(".uploaded-files-div");
    },
    getUploadButton: function () {
        return this.element.find("button.upload-file");
    },
    getBrowseButton: function () {
        return this.element.find(".choose-file");
    },
    getProgressbar: function () {
        return this.element.find('.progress .progress-bar');
    }
});
