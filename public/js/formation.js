if (currentUrl.indexOf("/session/afficher") !== -1) {
    document.addEventListener("DOMContentLoaded", function () {
        const cardLimit = 10; // Seuil pour activer le mécanisme d'affichage progressif
        // Fonction réutilisable pour gérer une liste de cartes
        const handleCardList = (listId, buttonId) => {
            const list = document.getElementById(listId);
            const cards = list.querySelectorAll(".doctorant-card");
            const button = document.getElementById(buttonId);

            if (cards.length <= cardLimit) {
                // Si le nombre de cartes est inférieur ou égal au seuil, tout afficher
                cards.forEach((card) => card.classList.add("visible"));
                return;
            }

            if (cards.length > 0 && button) {
                button.style.display = "block";
                let cardsToShow = 10; // Nombre initial de cartes affichées
                const totalCards = cards.length;

                // Fonction pour afficher un nombre donné de cartes et gérer l'aperçu
                const displayCards = (count) => {
                    cards.forEach((card, index) => {
                        if (index < count) {
                            card.classList.add("visible");
                            card.classList.remove("preview");
                        } else if (index === count) {
                            card.classList.add("preview"); // Carte suivante
                        } else {
                            card.classList.remove("visible", "preview");
                        }
                    });
                };

                // Afficher les premières cartes au démarrage
                displayCards(cardsToShow);

                // Écouter le clic sur le bouton "Afficher tout"
                button.addEventListener("click", function () {
                    cardsToShow = totalCards; // Mettre à jour le nombre de cards à afficher
                    displayCards(cardsToShow); // Afficher toutes les cards
                    button.style.display = "none"; // Masquer le bouton
                });
            }
        }
        // Appeler la fonction pour chaque liste
        handleCardList("inscrits-non-classes-container", "show-more-non-classes");
        handleCardList("inscrits-liste-principale-container", "show-more-principale");
        handleCardList("inscrits-liste-compl-container", "show-more-compl");
    });
}