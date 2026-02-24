document.addEventListener('DOMContentLoaded', () => {
    let selectedProfileId = null;

    const cards = document.querySelectorAll('.profile-card');
    const editBtn = document.getElementById('editProfileBtn');
    const deleteBtn = document.getElementById('deleteBtn'); 
    // Elemento nascosto del form di delete su profili.php
    const deleteInput = document.getElementById('deleteProfileId');
    const actionsBar = document.getElementById('profilesActions');

    // Per ogni profilo
    cards.forEach(card => {
        // Al click seleziona il singolo profilo graficamente
        card.addEventListener('click', () => {
            // Rimuovi la classe CSS "selected" da tutti gli altri e la mette nel profilo
            cards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');

            // Dati da attributo data-id di html
            selectedProfileId = card.dataset.id;
            deleteInput.value = selectedProfileId;

            // Attiva button
            deleteBtn.disabled = false;
            editBtn.disabled = false;

            actionsBar.classList.add('active');
        });

        // Doppio click => Pagina catalogo
        card.addEventListener('dblclick', () => {
            const id = card.dataset.id;
            if (id) 
                window.location.href = "/catalogo/catalogo.php?idProfilo=" + encodeURIComponent(id);
        });
    });

    // Click su modifica apre avatar.php
    editBtn.addEventListener('click', () => {
        if (!selectedProfileId) 
            return;

        window.location.href = "/profili/avatar.php?idProfilo=" + encodeURIComponent(selectedProfileId);
    });

    // Click su elimina manda un banner (gestione con PHP_SELF)
    document.getElementById('deleteForm').addEventListener('submit', (e) => {
        if (!deleteInput.value || !confirm('Sei sicuro di voler eliminare questo profilo?')) {
            e.preventDefault();
        }
    });
});