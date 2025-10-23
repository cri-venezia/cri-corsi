document.addEventListener('DOMContentLoaded', function () {
    const repeaterContainer = document.getElementById('repeater-container');
    if (!repeaterContainer) {
        return;
    }

    const addButton = document.getElementById('add-repeater-row');
    const repeaterBody = document.getElementById('repeater-body');
    const template = document.getElementById('repeater-row-template');

    // Aggiunge una nuova riga
    addButton.addEventListener('click', function () {
        const newIndex = repeaterBody.getElementsByClassName('repeater-row').length;
        const newRowHTML = template.innerHTML.replace(/{{index}}/g, newIndex);

        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = newRowHTML.trim();
        const newRow = tempDiv.firstChild;

        repeaterBody.appendChild(newRow);
    });

    // Gestisce la rimozione di una riga
    repeaterBody.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('remove-repeater-row')) {
            const rowToRemove = e.target.closest('.repeater-row');
            if (rowToRemove) {
                rowToRemove.remove();
            }
        }
    });
});
