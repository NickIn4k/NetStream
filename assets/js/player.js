const video = document.getElementById("videoPlayer");
const overlay = document.getElementById("videoOverlay");
const divPage = document.getElementById("playerPage");
const startTime = parseInt(video.dataset.startTime, 10);

// Se c'è un tempo di inizio specificato, aspetta che i metadati siano caricati per posizionare il video
if (startTime > 0){
    video.addEventListener("loadedmetadata", () => {
        video.pause();
        if (startTime < video.duration)
            video.currentTime = startTime;
        video.play();
    });
}

// Disabilita menù con tasto destro del player
video.addEventListener("contextmenu", e => e.preventDefault());

// Fine video => manda dati a Bet271
video.addEventListener("ended", () => {
    overlay.classList.remove("hidden");

    // Invia i dati a Bet271 con AJAX
    mandaDatiBet271();
});

// Meetti l'overlay nascosto al click e fai ripartire il video
function replay() {
    overlay.classList.add("hidden");
    video.currentTime = 0;
    video.play();
}

// Passa all'episodio successivo (pagina php con get a id successivo)
function nextEpisode(id) {
    window.location.href = "/player/player.php?id=" + id;
}

// Richiesta AJAX per salvare il cookie con php e POST
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

// Richista AJAX per inviare i dati a Bet271
function mandaDatiBet271(){
    const xmlhttp = new XMLHttpRequest();
    xmlhttp.open('GET', '/backend/sendToBet271.php', true);

    xmlhttp.onreadystatechange = function(){
        if(xmlhttp.readyState === 4 && xmlhttp.status === 200)
            console.log('Dati inviati a Bet271:', xmlhttp.responseText);
        else
            console.error('Errore invio dati a Bet271:', xmlhttp.status);
    };

    xmlhttp.send();
}

// Oggetto JS per salvare le info grazie all'attributo data-* di HTML
const contenuto = {
    idContenuto: parseInt(video.dataset.idContenuto), 
    idProfilo: parseInt(video.dataset.idProfilo),
    idEpisodio: parseInt(video.dataset.idEpisodio),
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