//Autorités
//Pour connaître les filtres disponibles et les valeurs retournées par IdRef
//Voir : https://documentation.abes.fr/aideidrefdeveloppeur/index.html#InterconnecterBaseEtIdref

/** Pointeur vers le lien ayant déclenché l'affichage de l'iframe */
var $sourceTrigger = null;

var proxy;
var idAutorite = "";
var remoteClientExist = false;
var oFrame;
var idrefinit = false;

var serializer = {

    stringify: function (data) {
        var message = "";
        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                message += key + "=" + escape(data[key]) + "&";
            }
        }
        return message.substring(0, message.length - 1);
    },

    parse: function (message) {
        var data = {};
        var d = message.split("&");
        var pair, key, value;
        for (var i = 0, len = d.length; i < len; i++) {
            pair = d[i];
            key = pair.substring(0, pair.indexOf("="));
            value = pair.substring(key.length + 1);
            data[key] = unescape(value);
        }
        return data;
    }
};

function envoiClient($trigger, fromApp, index1, index1Value, index2, index2Value, filtre1, filtre1Value, filtre2, filtre2Value, zones) {

    $sourceTrigger = $trigger;

    index1Value = index1Value.replace(/'/g, "\\\'");

    initClient();

    oFrame = document.getElementById("popupFrame");
    if (!idrefinit) {
        oFrame.contentWindow.postMessage(serializer.stringify({Init: "true"}), "*");
        idrefinit = false;
    }
    try {
        if (zones != null)
            eval('oFrame.contentWindow.postMessage(serializer.stringify({Index1:\'' + index1 + '\',Index1Value:\'' + index1Value + '\',Index2:\'' + index2 + '\',Index2Value:\'' + index2Value + '\',Filtre1:\'' + filtre1 + "/" + filtre1Value + '\',Filtre2:\'' + filtre2 + "/" + filtre2Value + '\',' + zones + '\',fromApp:\'' + fromApp + '\',AutoClick:\'false\'}), "*"); ');
        if (filtre2 != null)
            eval('oFrame.contentWindow.postMessage(serializer.stringify({Index1:\'' + index1 + '\',Index1Value:\'' + index1Value + '\',Index2:\'' + index2 + '\',Index2Value:\'' + index2Value + '\',Filtre1:\'' + filtre1 + "/" + filtre1Value + '\',Filtre2:\'' + filtre2 + "/" + filtre2Value + '\',fromApp:\'' + fromApp + '\',AutoClick:\'false\'}), "*"); ');
        else if (filtre1 != null)
            eval('oFrame.contentWindow.postMessage(serializer.stringify({Index1:\'' + index1 + '\',Index1Value:\'' + index1Value + '\',Index2:\'' + index2 + '\',Index2Value:\'' + index2Value + '\',Filtre1:\'' + filtre1 + "/" + filtre1Value + '\',fromApp:\'' + fromApp + '\',AutoClick:\'false\'}), "*"); ');
        else if (index2 != null)
            eval('oFrame.contentWindow.postMessage(serializer.stringify({Index1:\'' + index1 + '\',Index1Value:\'' + index1Value + '\',Index2:\'' + index2 + '\',Index2Value:\'' + index2Value + '\',fromApp:\'' + fromApp + '\',AutoClick:\'false\'}), "*"); ');
        else if (index1Value)
            eval('oFrame.contentWindow.postMessage(serializer.stringify({Index1:\'' + index1 + '\',Index1Value:\'' + index1Value + '\',fromApp:\'' + fromApp + '\',AutoClick:\'true\',End:\'true\'}), "*"); ');
        else
            eval('oFrame.contentWindow.postMessage(serializer.stringify({Index1:\'' + index1 + '\',AutoClick:\'false\',End:\'true\'}), "*"); ');
    } catch (e) {
        alert("oFrame.contentWindow Failed? " + e);
    }
}

function initClient() {

    //Rend la fenêtre deplacable
    $("#popupContainer").draggable();

    showPopWin("", screen.width * 0.75, screen.height * 0.65, null);
    if (remoteClientExist) {
        return 0;
    }
    remoteClientExist = true;

    if (document.addEventListener) {
        window.addEventListener("message", function (e) {
            traiteResultat(e);
        });
    } else {
        window.attachEvent('onmessage', function (e) {
            traiteResultat(e);
        });
    }
    return 0;
}

/**
 * Fonction appelée lorsque l'utilisateur clique sur "Lier la notice" dans l'iframe.
 */
function traiteResultat(e) {
    var data = serializer.parse(e.data);
    UnicaenIdRef.handleResult($sourceTrigger, data); // la fonction doit être définie par l'application !
    hidePopWin(null);
};


// function traiteResultat(e) {
// //partie à adapter pour votre client
//     var data = serializer.parse(e.data);
//     // console.log(data);
//
//     if (data["g"] != null) {
//         var resHtml = "<ul>";
//         resHtml += "<li>data['a'] : " + data['a'] + "</li>";
//         resHtml += "<li>data['b'] : " + data['b'] + "</li>";
//         resHtml += "<li>data['c'] : " + data['c'] + "</li>";
//         resHtml += "<li>data['d'] : " + data['d'] + "</li>";
//         resHtml += "<li>data['e'] : " + data['e'] + "</li>";
//         resHtml += "<li>data['f'] : " + escapeHtml(data['f']) + "</li>";
//         resHtml += "<li>data['g'] : " + data['g'] + "</li>";
//         resHtml += "</ul>";
//         $('#resultat').html(resHtml);
//         $('#resultat').show();
//         hidePopWin(null);
//     }
// }
//
// function escapeHtml(texte) {
//     return texte
//         .replace(/&/g, "&amp;")
//         .replace(/</g, "&lt;")
//         .replace(/>/g, "&gt;")
//         .replace(/"/g, "&quot;")
//         .replace(/'/g, "&#039;");
// }
