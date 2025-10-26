// Aspetta che l'intera pagina (incluse immagini e CSS) sia caricata
window.addEventListener('load', function() {

    // Seleziona tutti gli elementi mappa presenti nella pagina
    const mapElements = document.querySelectorAll('.cri-corso-map');

    mapElements.forEach(mapElement => {
        const address = mapElement.dataset.address;
        const mapId = mapElement.id;

        // Coordinate di default (Sede CRI Venezia - approssimative) se l'indirizzo manca o la geocodifica fallisce
        const defaultCoords = [45.4381, 12.2882]; // Coordinate per Via Porto di Cavergnago, 38/B
        const defaultAddress = 'Via Porto di Cavergnago, 38/B, 30173 Venezia VE';

        // Controlla se l'oggetto L (Leaflet) è disponibile
        if (typeof L === 'undefined') {
            console.error('Libreria Leaflet non caricata.');
            mapElement.innerHTML = '<p style="color: red;">Errore: Libreria mappa non caricata.</p>';
            return; // Interrompi l'esecuzione per questo elemento
        }

        let map; // Dichiara la variabile mappa qui

        // Funzione per inizializzare o aggiornare la mappa
        const initializeMap = (coords, popupText) => {
            try {
                if (!map) {
                    map = L.map(mapId).setView(coords, 15); // Inizializza solo la prima volta
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | CRI Venezia'
                    }).addTo(map);
                } else {
                    map.setView(coords, 16); // Altrimenti centra solo la vista
                }
                L.marker(coords).addTo(map)
                    .bindPopup(popupText)
                    .openPopup();

                // Forza Leaflet a ricalcolare le dimensioni dopo un ritardo maggiore
                setTimeout(() => {
                    if (map) {
                        map.invalidateSize();
                        console.log("Leaflet map invalidated size for:", mapId); // Log di debug
                    }
                }, 250); // Ritardo aumentato a 250ms
            } catch (e) {
                console.error("Errore durante l'inizializzazione della mappa Leaflet:", e);
                mapElement.innerHTML = '<p style="color: red;">Errore durante l\'inizializzazione della mappa.</p>';
            }
        };

        if (address && mapId) {
            // Prova a ottenere le coordinate dall'indirizzo
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const coords = [parseFloat(data[0].lat), parseFloat(data[0].lon)];
                        initializeMap(coords, `<b>${address}</b>`);
                    } else {
                        console.warn(`Geocodifica fallita per: ${address}. Uso coordinate di default.`);
                        initializeMap(defaultCoords, `<b>Sede Principale (Indirizzo non trovato):</b><br>${defaultAddress}`);
                    }
                })
                .catch(error => {
                    console.error('Errore durante la geocodifica:', error);
                    initializeMap(defaultCoords, `<b>Sede Principale (Errore geocodifica):</b><br>${defaultAddress}`);
                });

        } else if (mapId) {
            console.warn('Indirizzo non fornito per:', mapId + ". Uso coordinate di default.");
            initializeMap(defaultCoords, `<b>Sede Principale:</b><br>${defaultAddress}`);
        }
    });

}); // Fine window.onload
