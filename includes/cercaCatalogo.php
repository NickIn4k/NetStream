<script>
    function showResultWithOption() {
    const input = document.getElementById("searchInput").value;
    const option = document.getElementById("searchOption").value;
    const liveSearchDiv = document.getElementById("livesearch");

    if (input.length == 0) {
        liveSearchDiv.innerHTML = "";
        liveSearchDiv.style.display = "none";
        return;
    }

    const xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            liveSearchDiv.innerHTML = this.responseText;
            liveSearchDiv.style.display = "block";
        }
    }
    // -- TBD: Gestione altra pagina e capire encodeURIComponent.
    xmlhttp.open("GET", `/includes/liveSearch.php?q=${encodeURIComponent(input)}&option=${option}`, true);
    xmlhttp.send();
}

</script>

<div class="search-wrapper">
    <input type="text" id="searchInput" placeholder="Cerca..." onkeyup="showResultWithOption()" autocomplete="off">

    <select id="searchOption">
        <option value="title">Titolo</option>
        <option value="director">Regista</option>
    </select>

    <div id="livesearch" class="livesearch"></div>
</div>
