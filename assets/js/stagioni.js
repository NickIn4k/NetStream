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
            url = "/catalogo/ajaxExtra.php?idContenuto=" + encodeURIComponent(idContenuto);
        } else {
            url = "/catalogo/ajaxEpisodi.php?idStagione=" + encodeURIComponent(id);
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

    //Click sui bottoni stagione
    buttons.forEach(btn => {
        btn.addEventListener("click", () => {

            buttons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            loadSeason(btn.dataset.stagione);
        });
    });

    //Caricamento automatico stagione 1
    buttons[0].classList.add("active");
    loadSeason(buttons[0].dataset.stagione);

});