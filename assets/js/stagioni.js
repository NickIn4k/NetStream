document.addEventListener("DOMContentLoaded", () => {
    // Selezione tramite classi CSS
    const container = document.querySelector(".contenuto-dynamic");
    // Possono essere più di uno => più stagioni
    const buttons = document.querySelectorAll(".nav-btn");

    if (!container || buttons.length === 0) 
        return;

    // Ottieni l'id dall'url della get
    const idContenuto = new URLSearchParams(window.location.search).get("idContenuto");

    // Funzione AJAX unica per tutte le richieste
    function loadContent(url, loadingText = "Caricamento...") {
        container.innerHTML = loadingText;

        const xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4){
                if (xhr.status === 200)
                    container.innerHTML = xhr.responseText;
                else 
                    container.innerHTML = "Errore di caricamento";
            }            
        };

        xhr.open("GET", url, true);
        xhr.send();
    }

    // AJAX per caricare le stagioni
    function loadSeason(idStagione) {
        // id = -1: trailer (passa idContenuto)
        // id = n: stagione (passa idStagione)
        const url = (idStagione == -1) 
        ? "/backend/ajaxExtra.php?idContenuto=" + encodeURIComponent(idContenuto)
        : "/backend/ajaxEpisodi.php?idStagione=" + encodeURIComponent(idStagione);

        loadContent(url);
    }

    // AJAX per caricare il film
    function loadFilm(idFilm) {
        loadContent("/backend/ajaxFilm.php?idContenuto=" + encodeURIComponent(idFilm));
    }

    // AJAX per caricare recensioni
    function loadRecensioni() {
        loadContent("/backend/ajaxRecensioni.php?idContenuto=" + encodeURIComponent(idContenuto), "Caricamento recensioni...");
    }

    // Handler del click 
    buttons.forEach(btn => {
        btn.addEventListener("click", () => {

            // Rimuovi la classe active da tutti e poi mettila solo al button cliccato
            buttons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            if (btn.dataset.recensione)
                loadRecensioni();
            else if (btn.dataset.film)
                loadFilm(btn.dataset.film);
            else
                loadSeason(btn.dataset.stagione);
        });
    });

    // Caricamento automatico stagione 1
    const firstBtn = buttons[0];
    firstBtn.classList.add("active");

    // Edit susscessivo: Handling degli altri buttons
    if (firstBtn.dataset.film)
        loadFilm(firstBtn.dataset.film);
    else if (firstBtn.dataset.recensione)
        loadRecensioni();
    else
        loadSeason(firstBtn.dataset.stagione);
});