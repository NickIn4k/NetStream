document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reviewForm');
    const messageDiv = document.getElementById('reviewMessage');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function() {
            if(xhr.readyState === 4){
                if(xhr.status === 200){
                    try {
                        const result = JSON.parse(xhr.responseText);
                        messageDiv.textContent = result.message;
                        messageDiv.style.color = result.success ? 'green' : 'red';

                        if(result.success)
                            form.reset();
                        
                    } catch(e) {
                        messageDiv.textContent = 'Risposta non valida dal server';
                        messageDiv.style.color = 'red';
                    }
                } else {
                    messageDiv.textContent = 'Errore di connessione';
                    messageDiv.style.color = 'red';
                }
            }
        };

        xhr.open('POST', '/recensioni/recensione.php', true);
        xhr.send(formData);
    });
});