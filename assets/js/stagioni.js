document.addEventListener("DOMContentLoaded", () => {

    const container = document.querySelector(".contenuto-dynamic");
    const buttons = document.querySelectorAll(".nav-btn");

    if (!container || buttons.length === 0) return;

    const idContenuto = new URLSearchParams(window.location.search).get("idContenuto");

    //Funzione principale AJAX
    function loadSeason(id) {
        container.innerHTML = "Caricamento...";
        let url;

        if (id == -1) {
            url = "/backend/ajaxExtra.php?idContenuto=" + encodeURIComponent(idContenuto);
        } else {
            url = "/backend/ajaxEpisodi.php?idStagione=" + encodeURIComponent(id);
        }

        const xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200)
                    container.innerHTML = xhr.responseText;
                else
                    container.innerHTML = "Errore di caricamento";
            }
        };

        xhr.open("GET", url, true);
        xhr.send();
    }

    function loadFilm(idContenuto) {
        container.innerHTML = "Caricamento...";

        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200)
                    container.innerHTML = xhr.responseText;
                else
                    container.innerHTML = "Errore di caricamento";
            }
        };

        xhr.open("GET","/backend/ajaxFilm.php?idContenuto=" + encodeURIComponent(idContenuto),true);
        xhr.send();
    }

    function loadRecensioni() {
        container.innerHTML = "Caricamento recensioni...";

        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200)
                    container.innerHTML = xhr.responseText;
                else
                    container.innerHTML = "Errore nel caricamento recensioni";
            }
        };

        xhr.open("GET","/backend/ajaxRecensioni.php?idContenuto=" + encodeURIComponent(idContenuto),true);
        xhr.send();
    }

    //Click sui bottoni stagione
    buttons.forEach(btn => {
        btn.addEventListener("click", () => {

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

    //Caricamento automatico stagione 1
    buttons[0].classList.add("active");
    if (buttons[0].dataset.film) {
        loadFilm(buttons[0].dataset.film);
    } else if (buttons[0].dataset.recensione) {
        loadRecensioni();
    } else {
        loadSeason(buttons[0].dataset.stagione);
    }
});