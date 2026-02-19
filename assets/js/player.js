const video = document.getElementById("videoPlayer");
const overlay = document.getElementById("videoOverlay");
const divPage = document.getElementById("playerPage");

video.addEventListener("contextmenu", e => e.preventDefault());

video.addEventListener("ended", () => {
    overlay.classList.remove("hidden");
});

function replay() {
    overlay.classList.add("hidden");
    video.currentTime = 0;
    video.play();
}

function nextEpisode(id) {
    window.location.href = "/player/player.php?id=" + id;
}

function salvaContinuaAGuardare(contenuto) {
    const xmlhttp = new XMLHttpRequest();
    xmlhttp.open('POST', '/backend/salvaContinua.php', true);
    xmlhttp.setRequestHeader('Content-Type', 'application/json');

    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState === 4) {
            if (xmlhttp.status === 200) {
                try {
                    var response = JSON.parse(xmlhttp.responseText);
                    if (!response.success)
                        console.error('Errore salvataggio:', response.message);
                } catch (e) {
                    console.error('Risposta non valida:', xmlhttp.responseText);
                }
            } else
                console.error('Errore richiesta AJAX:', xmlhttp.status);
        }
    };

    xmlhttp.send(JSON.stringify(contenuto));
}

// Oggetto JS per salvare le info grazie all'attributo data-* di HTML
const contenuto = {
    idContenuto: parseInt(video.dataset.idContenuto), 
    idProfilo: parseInt(video.dataset.idProfilo),
    tipo: video.dataset.tipoContenuto,               
    ultimoEpisodio: video.dataset.tipoContenuto === "serie" ? { stagione: parseInt(video.dataset.stagione), episodio: parseInt(video.dataset.episodio) } : null,
    tempo: 0
};

// ogni 60 secondi invia lo stato corrente
setInterval(() => {
    console.log(contenuto);
    contenuto.tempo = Math.floor(video.currentTime); // aggiorna il tempo corrente
    salvaContinuaAGuardare(contenuto);
}, 30000);

/*
    IDEA JSON

    {
    "contenuti": [
        {
            "idContenuto": 12, 
            "idProfilo": 1,     
            "tipo": "film",            "film" o "serie"
            "ultimoEpisodio": null,    
            "tempo": 123               
        },
        {
            "idContenuto": 5,
            "idProfilo": 2,
            "tipo": "serie",
            "ultimoEpisodio": {
                "idStagione": 2,
                "idEpisodio": 8
            },
            "tempo": 540               
        }
    ]
}
*/