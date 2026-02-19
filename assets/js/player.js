const video = document.getElementById("videoPlayer");
const overlay = document.getElementById("videoOverlay");
const divPage = document.getElementById("playerPage");

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

video.addEventListener("contextmenu", e => e.preventDefault());