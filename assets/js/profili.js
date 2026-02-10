document.addEventListener('DOMContentLoaded', () => {
    let selectedProfileId = null;

    const cards = document.querySelectorAll('.profile-card');
    const editBtn = document.getElementById('editProfileBtn');
    const deleteBtn = document.getElementById('deleteBtn'); 
    const deleteInput = document.getElementById('deleteProfileId');
    const actionsBar = document.getElementById('profilesActions');

    cards.forEach(card => {
        card.addEventListener('click', () => {
            cards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');

            selectedProfileId = card.dataset.id;
            deleteInput.value = selectedProfileId;

            deleteBtn.disabled = false;
            editBtn.disabled = false;

            actionsBar.classList.add('active');
        });

        card.addEventListener('dblclick', () => {
            const id = card.dataset.id;
            if (id) {
                window.location.href = "/catalogo/catalogo.php?idProfilo=" + encodeURIComponent(id);
            }
        });
    });

    editBtn.addEventListener('click', () => {
        if (!selectedProfileId) return;

        window.location.href = "/profili/avatar.php?idProfilo=" + encodeURIComponent(selectedProfileId);
    });

    document.getElementById('deleteForm').addEventListener('submit', (e) => {
        if (!deleteInput.value || !confirm('Sei sicuro di voler eliminare questo profilo?')) {
            e.preventDefault();
        }
    });
});
