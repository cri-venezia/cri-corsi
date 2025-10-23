document.addEventListener('DOMContentLoaded', function() {

    // Controlla se L (Leaflet) è definito
    if (typeof L === 'undefined') {
        console.warn('Leaflet non è stato caricato. Impossibile inizializzare le mappe.');
        return;
    }

    // Seleziona tutte le mappe nel DOM
    const maps = document.querySelectorAll('.cri-corso-map');

    if (maps.length === 0) {
        return;
    }

    maps.forEach(mapElement => {
        const mapId = mapElement.id;
        const address = mapElement.dataset.address;

        if (!mapId || !address) {
            console.warn('Elemento mappa senza ID o data-address.');
            return;
        }

        // Controlla se la mappa è già stata inizializzata
        if (mapElement && !mapElement._leaflet_id) {

            // Default: coordinate della sede (Via Porto di Cavergnago)
            let defaultCoords = [45.4854, 12.2354]; // Sede CRI Venezia
            let defaultZoom = 13;
            let defaultAddress = 'Via Porto di Cavergnago, 38/B';

            var map = L.map(mapId).setView(defaultCoords, defaultZoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; CRI Venezia'
            }).addTo(map);

            // Geocodifica l'indirizzo (usando Nominatim)
            fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(address) + '&format=json&limit=1')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Errore di rete nella richiesta a Nominatim');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.length > 0) {
                        var lat = data[0].lat;
                        var lon = data[0].lon;
                        map.setView([lat, lon], 15);
                        L.marker([lat, lon]).addTo(map)
                            .bindPopup(address);
                    } else {
                        console.warn('Indirizzo non trovato su Nominatim: ' + address);
                        // Opzionale: mostra un marker sulla sede di Venezia se l'indirizzo non è trovato
                        L.marker(defaultCoords).addTo(map)
                            .bindPopup(defaultAddress + '.<br>Indirizzo non trovato.');
                    }
                })
                .catch(error => {
                    console.error('Errore durante la geocodifica:', error);
                    // Mostra un marker sulla sede di Venezia in caso di errore fetch
                    L.marker(defaultCoords).addTo(map)
                        .bindPopup(defaultAddress + '.<br>Errore di geocodifica.');
                });
        }
    });
});

