document.addEventListener("DOMContentLoaded", () => {

    const container = document.querySelector(".contenuto-dynamic");
    const buttons = document.querySelectorAll(".nav-btn");

    if (!container || buttons.length === 0) return;

    const idContenuto = new URLSearchParams(window.location.search).get("idContenuto");

    //Carica la prima stagione automaticamente
    const first = buttons[0];
    loadSeason(first.dataset.stagione);

    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            loadSeason(btn.dataset.stagione);
        });
    });

    function loadSeason(id) {
        container.innerHTML = "Caricamento...";

        let url;

        if (id == -1)
            url = "/catalogo/ajaxExtra.php?idContenuto=" + encodeURIComponent(idContenuto);
        else
            url = "/catalogo/ajaxEpisodi.php?idStagione=" + encodeURIComponent(id);

        fetch(url)
            .then(res => res.text())
            .then(html => container.innerHTML = html)
            .catch(() => container.innerHTML = "Errore caricamento");
    }

});