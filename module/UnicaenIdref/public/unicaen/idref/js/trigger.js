const UnicaenIdRef = {
        /**
         * Ecoute des clics sur le trigger pour afficher l'iframe de recherche de notices.
         *
         * @param jquery $trigger Lien cliquable
         * @param array params
         */
        initTrigger: function ($trigger, params) {
            $trigger.on('click', function (event) {
                const $sourceElement = UnicaenIdRef.getSourceElementFromTrigger($trigger);

                // prise en compte de la "valeur" éventuelle de l'élément source en tant que 'index1Value'
                var index1Value = UnicaenIdRef.getSourceElementValue($sourceElement);
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
                const $sourceElement = UnicaenIdRef.getSourceElementFromTrigger($trigger);
                const $destinationElement = UnicaenIdRef.getDestinationElementFromTrigger($trigger);
                UnicaenIdRef.setDestinationElementValue($destinationElement, idref);
                //console.log('idrefTriggerHandleResult', $trigger, $sourceElement, idref);
            }
        },

        getSourceElementFromTrigger: function ($trigger) {
            const sourceElementId = $trigger.data(k = 'source-element-id');
            if (!sourceElementId) {
                throw new Error("UnicaenIdRef : le trigger doit fournir l'id de l'élément source via l'attribut suivant : data-" + k);
            }
            const $sourceElement = $('#' + sourceElementId);
            if (!$sourceElement.length) {
                throw new Error("UnicaenIdRef : élément source introuvable avec cet id : " + sourceElementId);
            }
            return $sourceElement;
        },

        getSourceElementValue: function ($sourceElement) {
            if ($sourceElement.is(":input")) {
                return $sourceElement.val();
            } else {
                return $sourceElement.text();
            }
        },

        getDestinationElementFromTrigger: function ($trigger) {
            const destinationElementId = $trigger.data(k = 'destination-element-id');
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
