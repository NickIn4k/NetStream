<script>
    function showResultWithOption(searchFlag = '') {
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
        };

        xmlhttp.open("GET", `/backend/liveSearch.php?c=${encodeURIComponent(input)}&option=${option}&searchFlag=${searchFlag}`, true);
        xmlhttp.send();
    }
</script>

<div class="search-wrapper">
    <input type="text" id="searchInput" placeholder="Cerca..." onkeyup="showResultWithOption('<?= $searchFlag ?? '' ?>')" autocomplete="off">

    <select id="searchOption">
        <option value="titoloContenuto">Titolo</option>
        <option value="regista">Regista</option>
    </select>

    <div id="livesearch" class="livesearch"></div>
</div>