const UnicaenIdRef = {
        /**
         * Ecoute des clics sur le trigger pour afficher l'iframe de recherche de notices.
         *
         * @param jquery $trigger Lien cliquable
         * @param array params
         */
        initTrigger: function ($trigger, params) {
            $trigger.on('click', function (event) {
                const sourceElements = $trigger.data(k = 'source-elements');
                if (!sourceElements) {
                    throw new Error("UnicaenIdRef : le trigger doit fournir l'id de l'élément source via l'attribut suivant : data-" + k);
                }
                var index1 = null;
                var index1Value = null;
                $(sourceElements).each(function(i, item) {
                    if (index1Value) {
                        return; // on s'arrête à la première valeur trouvée
                    }
                    index1 = item["Index1"];
                    index1Value = $(item["Index1Value"]).map(function(i, val) {
                        var sel = '#' + val;
                        return $(sel).is(":input") ? $(sel).val() : $(sel).text();
                    }).get().join(' ').trim();
                });
                params["index1"] = index1;
                if (index1Value) {
                    params["index1Value"] = index1Value;
                }

                envoiClient(
                    $trigger,
                    params["fromApp"],
                    params["index1"],
                    params["index1Value"] || '',
                    params["index2"] || null,
                    params["index2Value"] || null,
                    params["filtre1"] || null,
                    params["filtre1Value"] || null,
                    params["filtre2"] || null,
                    params["filtre2Value"] || null,
                    params["zones"] || null,
                );

                event.preventDefault();
            });
        },

        /**
         * Retour des infos de la notice sélectionnée dans l'iframe.
         * @param array data
         */
        handleResult: function ($trigger, data) {
            if (data["g"] != null) {
                const idref = data['b'];
                const $destinationElement = UnicaenIdRef.getDestinationElementFromTrigger($trigger);
                UnicaenIdRef.setDestinationElementValue($destinationElement, idref);
            }
        },

        getDestinationElementFromTrigger: function ($trigger) {
            const destinationElementId = $trigger.data(k = 'destination-element');
            if (!destinationElementId) {
                throw new Error("UnicaenIdRef : le trigger doit fournir l'id de l'élément source via l'attribut suivant : data-" + k);
            }
            const $destinationElement = $('#' + destinationElementId);
            if (!$destinationElement.length) {
                throw new Error("UnicaenIdRef : élément source introuvable avec cet id : " + destinationElementId);
            }
            return $destinationElement;
        },

        setDestinationElementValue: function ($destinationElement, value) {
            if ($destinationElement.is(":input")) {
                $destinationElement.val(value);
            }
        }
    }
;
